<?php
// db.php — shared connection + small helpers used by every page

function db(): mysqli
{
    $host = getenv('DB_HOST');
    $user = getenv('DB_USER');
    $pass = getenv('DB_PASSWORD');
    $name = getenv('DB_NAME');
    $port = getenv('DB_PORT');

    if (!$host || !$user || !$pass || !$name || !$port) {
        error_log('Missing one or more DB_* environment variables.');
        http_response_code(500);
        exit('Server configuration error. Please contact the administrator.');
    }

    try {
        return new mysqli($host, $user, $pass, $name, (int)$port);
    } catch (\mysqli_sql_exception $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        http_response_code(500);
        exit('Database connection failed. Please try again later.');
    }
}

// Escape output consistently, so we don't forget on any one page
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Read and validate the id from the URL; reject anything that isn't
// a positive whole number instead of silently using 0
function id_from_query(): int
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($id === false || $id === null) {
        http_response_code(400);
        exit('Invalid member id.');
    }

    return $id;
}
