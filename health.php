<?php
require 'db.php';
header('Content-Type: application/json');

if (!isset($_GET['deep'])) {
    echo json_encode(['status' => 'ok']);
    exit;
}

$conn = db();   // already exits with 500 if the connection itself fails

try {
    $conn->query('SELECT 1');
    echo json_encode(['status' => 'ok', 'database' => 'ok']);
} catch (\mysqli_sql_exception $e) {
    error_log('Health check query failed: ' . $e->getMessage());
    http_response_code(503);
    echo json_encode(['status' => 'degraded', 'database' => 'unavailable']);
}