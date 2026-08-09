<?php
/**
 * Database connection (PDO) for the Food Spoilage Detection System.
 * Default Laragon MySQL credentials: user "root", empty password.
 * Adjust if your Laragon setup differs.
 */

$DB_HOST = "127.0.0.1";
$DB_NAME = "food_spoilage";
$DB_USER = "root";
$DB_PASS = "";

try {
    $pdo = new PDO(
        "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "error"   => "Database connection failed: " . $e->getMessage()
    ]);
    exit;
}
