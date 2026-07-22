<?php
header('Content-Type: application/json');
if (!isset($_GET['deep'])) { echo json_encode(['status' => 'ok']); exit; }
try {
  $pdo = new PDO(
    "mysql:host=".getenv('DB_HOST').";port=".getenv('DB_PORT').";dbname=".getenv('DB_NAME'),
    getenv('DB_USER'), getenv('DB_PASSWORD'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
   $pdo->query('SELECT 1');
   echo json_encode(['status' => 'ok', 'database' => 'ok']);
} catch (Throwable $e) {
  http_response_code(503);
  echo json_encode(['status' => 'degraded', 'database' => 'unavailable']);
}
