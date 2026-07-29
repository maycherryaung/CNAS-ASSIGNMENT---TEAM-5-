<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

$deep = isset($_GET['deep']) && in_array(strtolower((string) $_GET['deep']), ['1', 'true', 'yes'], true);
$payload = [
    'status' => 'ok',
    'service' => 'cnas-php-mysql',
    'database' => $deep ? 'unchecked' : 'not_checked',
];

if ($deep) {
    try {
        $conn = db(false);
        $conn->query('SELECT 1');
        $payload['database'] = 'ok';
    } catch (Throwable $exception) {
        error_log('Health database check failed: ' . $exception->getMessage());
        http_response_code(503);
        $payload['status'] = 'degraded';
        $payload['database'] = 'unavailable';
    }
}

echo json_encode($payload, JSON_UNESCAPED_SLASHES);

