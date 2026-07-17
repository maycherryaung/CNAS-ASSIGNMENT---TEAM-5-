<?php
declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function env_required(string $name): string
{
    $value = getenv($name);

    if ($value === false || $value === '') {
        throw new RuntimeException($name . ' is not configured.');
    }

    return $value;
}

function db(bool $failClosed = true): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    try {
        $host = env_required('DB_HOST');
        $user = env_required('DB_USER');
        $password = env_required('DB_PASSWORD');
        $database = env_required('DB_NAME');
        $portValue = env_required('DB_PORT');
        $port = filter_var($portValue, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1, 'max_range' => 65535],
        ]);

        if ($port === false) {
            throw new RuntimeException('DB_PORT must be an integer between 1 and 65535.');
        }

        $connection = new mysqli($host, $user, $password, $database, $port);
        $connection->set_charset('utf8mb4');
        return $connection;
    } catch (Throwable $exception) {
        error_log('Database connection failed: ' . $exception->getMessage());

        if ($failClosed) {
            http_response_code(500);
            exit('Database connection failed. Check the application logs and DB_* environment variables.');
        }

        throw $exception;
    }
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

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
