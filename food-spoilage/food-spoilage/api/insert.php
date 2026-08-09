<?php
/**
 * POST endpoint used by the ESP32 to submit a sensor reading.
 * Expects form-urlencoded (or JSON) body with: gas, ph, temperature, humidity
 * Computes the Fresh / Warning / Spoiled status and stores everything.
 */

header("Content-Type: application/json");
require_once __DIR__ . "/../config/database.php";

function respond($success, $data = [], $code = 200) {
    http_response_code($code);
    echo json_encode(array_merge(["success" => $success], $data));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    respond(false, ["error" => "Only POST requests are allowed"], 405);
}

// Support both form-urlencoded and raw JSON bodies
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents("php://input");
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

$required = ["gas", "ph", "temperature", "humidity"];
foreach ($required as $field) {
    if (!isset($input[$field]) || !is_numeric($input[$field])) {
        respond(false, ["error" => "Missing or invalid field: {$field}"], 400);
    }
}

$gas         = (float) $input["gas"];
$ph          = (float) $input["ph"];
$temperature = (float) $input["temperature"];
$humidity    = (float) $input["humidity"];

/**
 * Status logic (mirrors ai/predict.py rule-based fallback and the ESP32 sketch):
 *   Spoiled: gas > 400 AND pH < 5.5
 *   Warning: gas > 250
 *   Fresh:   otherwise
 */
if ($gas > 400 && $ph < 5.5) {
    $status = "Spoiled";
} elseif ($gas > 250) {
    $status = "Warning";
} else {
    $status = "Fresh";
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO sensor_data (gas_value, ph_value, temperature, humidity, status)
         VALUES (:gas, :ph, :temperature, :humidity, :status)"
    );
    $stmt->execute([
        ":gas"         => $gas,
        ":ph"          => $ph,
        ":temperature" => $temperature,
        ":humidity"    => $humidity,
        ":status"      => $status,
    ]);

    $insertId = $pdo->lastInsertId();

    // Log an alert row if status isn't Fresh (useful for a future alerts feed)
    if ($status !== "Fresh") {
        $msg = $status === "Spoiled"
            ? "Food has spoiled! Immediate attention required."
            : "Food quality declining — check soon.";
        $alertStmt = $pdo->prepare(
            "INSERT INTO alerts (sensor_data_id, message) VALUES (:id, :msg)"
        );
        $alertStmt->execute([":id" => $insertId, ":msg" => $msg]);
    }

    respond(true, [
        "id"          => (int) $insertId,
        "status"      => $status,
        "gas_value"   => $gas,
        "ph_value"    => $ph,
        "temperature" => $temperature,
        "humidity"    => $humidity,
    ]);
} catch (PDOException $e) {
    respond(false, ["error" => "Insert failed: " . $e->getMessage()], 500);
}
