<?php
/**
 * GET endpoint consumed by the dashboard (script.js via AJAX/fetch).
 *
 * Usage:
 *   get_data.php?type=latest          -> single most recent reading
 *   get_data.php?type=history&limit=30 -> most recent N readings (default 30)
 *   get_data.php?type=alerts&limit=10  -> most recent N alerts
 */

header("Content-Type: application/json");
require_once __DIR__ . "/../config/database.php";

$type  = $_GET["type"]  ?? "latest";
$limit = isset($_GET["limit"]) ? max(1, min(200, (int) $_GET["limit"])) : 30;

try {
    if ($type === "latest") {
        $stmt = $pdo->query(
            "SELECT * FROM sensor_data ORDER BY created_at DESC LIMIT 1"
        );
        $row = $stmt->fetch();
        echo json_encode(["success" => true, "data" => $row ?: null]);

    } elseif ($type === "history") {
        $stmt = $pdo->prepare(
            "SELECT gas_value, ph_value, temperature, humidity, status, created_at
             FROM sensor_data ORDER BY created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = array_reverse($stmt->fetchAll()); // chronological order for charts
        echo json_encode(["success" => true, "data" => $rows]);

    } elseif ($type === "alerts") {
        $stmt = $pdo->prepare(
            "SELECT a.id, a.message, a.created_at, s.status
             FROM alerts a
             JOIN sensor_data s ON s.id = a.sensor_data_id
             ORDER BY a.created_at DESC LIMIT :limit"
        );
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        echo json_encode(["success" => true, "data" => $stmt->fetchAll()]);

    } else {
        http_response_code(400);
        echo json_encode(["success" => false, "error" => "Unknown type: {$type}"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
