<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// This page returns JSON, not HTML - it's meant to be read by a machine
// (e.g. Kubernetes), not viewed by a person.
header('Content-Type: application/json');

// ?deep=1 (or true/yes) switches on the database check below.
$deep = isset($_GET['deep']) && in_array(strtolower((string) $_GET['deep']), ['1', 'true', 'yes'], true);

// Default response: app is up, database not checked yet.
$payload = [
    'status' => 'ok',
    'service' => 'cnas-php-mysql',
    'database' => $deep ? 'unchecked' : 'not_checked',
];

if ($deep) {
    try {
        // false = don't kill the request if this fails; let us catch it
        // below and reply with proper JSON instead of a crash page.
        $conn = db(false);
        // Throwaway query - just checks the database actually responds.
        $conn->query('SELECT 1');
        $payload['database'] = 'ok';
    } catch (Throwable $exception) {
        // Log the real error for developers.
        error_log('Health database check failed: ' . $exception->getMessage());
        // 503 = "up, but not able to serve requests right now".
        http_response_code(503);
        $payload['status'] = 'degraded';
        $payload['database'] = 'unavailable';
    }
}

// Send the result back as JSON.
echo json_encode($payload, JSON_UNESCAPED_SLASHES);

