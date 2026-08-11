<?php
// strict_types: PHP won't silently convert types (e.g. "5" -> 5) for us,
// so type mistakes fail loudly instead of hiding as bugs.
declare(strict_types=1);

// Make mysqli throw an exception on any database error instead of just
// returning false. This means a failed query can never be accidentally
// ignored - it forces the calling code to notice and handle it.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Reads one setting from the environment (variables injected from outside
// the code - e.g. by Docker/Kubernetes - instead of being hardcoded here).
// This is what lets the same app run against different databases in dev,
// staging, and production without changing any code.
function env_required(string $name): string
{
    $value = getenv($name);

    // Missing or empty config is a setup mistake - fail immediately with a
    // clear message instead of continuing with a blank value.
    if ($value === false || $value === '') {
        throw new RuntimeException($name . ' is not configured.');
    }

    return $value;
}

// Opens the database connection using DB_* environment variables, and
// reuses the same connection for the rest of this request.
// failClosed = true means that if the database connection fails, the entire request will be stopped with a 500 error.
// failClosed = false means that if the database connection fails, the request will continue and the caller will need to handle the failure themselves.
// all php files call db() not db(false) so the default is to stop when there are errors
function db(bool $failClosed = true): mysqli
{
    // "static" means this variable keeps its value between calls to db()
    // within the same request, so we only connect once, not every call.
    /* 1. A browser visits, say, delete.php.
2. delete.php calls db().
3. First time db() runs in this request, $connection is still null, so it goes ahead and opens a real connection to MySQL, saves it into $connection, and returns it.
4. If anything else on that same page calls db() again (some pages call it more than once), $connection is no longer null — it already holds the open connection — so it just hands back that same one instead of opening a second connection.*/
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
        // Confirm DB_PORT is actually a valid port number before using it,
        // so a typo in config fails clearly here instead of confusingly later.
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
        // Log the real error for developers, but don't leak connection
        // details (host, credentials) to whoever is using the site.
        error_log('Database connection failed: ' . $exception->getMessage());

        if ($failClosed) {
            // Default behavior: stop the whole request with a 500 ("server
            // error") since nothing in the app works without a database.
            http_response_code(500);
            exit('Database connection failed. Check the application logs and DB_* environment variables.');
        }

        // Callers that pass failClosed=false get to handle the failure
        // themselves instead of the request being stopped here.
        throw $exception;
    }
}

// Escapes a value before it's printed into HTML (converts characters like
// < and > into safe equivalents), so untrusted text can never be
// interpreted as HTML/script by the browser - this prevents XSS.
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Reads and validates the ?id= value from the URL. This is the boundary
// where untrusted input enters the app, so it's checked here once and
// every caller can then trust $id is a valid positive integer.
function id_from_query(): int
{
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    // Missing or non-numeric id -> stop with a 400 ("bad request") instead
    // of letting a garbage value flow into a database query.
    if ($id === false || $id === null) {
        http_response_code(400);
        exit('Invalid member id.');
    }

    return $id;
}
