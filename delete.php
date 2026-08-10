<?php
// strict_types: PHP won't silently convert types (e.g. "5" -> 5) for us,
// so type mistakes fail loudly instead of hiding as bugs.
declare(strict_types=1);

// Shared database connection/helper logic lives in one place (db.php)
// instead of being copy-pasted into every page - separation of concerns.
require_once __DIR__ . '/db.php';

// Which team member are we deleting? The id comes from the URL, e.g. ?id=3
$id = id_from_query();
$conn = db();
$errors = [];

// Look up the user first (prepared statement: the "?" placeholder is filled
// in safely via bind_param, instead of gluing $id into the SQL string -
// this is how you avoid SQL injection).
$stmt = $conn->prepare('SELECT id, name, email FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// No matching row? Respond with the standard "not found" HTTP status.
if (!$user) {
    http_response_code(404);
    exit('Team member not found.');
}

// Only actually delete when the confirmation form below is SUBMITTED
// (a POST request). Just visiting this page (a GET request, e.g. a link
// or a search engine crawling it) must never trigger a destructive action.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        // Redirect back to the list after deleting, so refreshing this
        // page doesn't accidentally try to delete the same row again.
        header('Location: index.php');
        exit();
    } catch (mysqli_sql_exception $exception) {
        // Log the real error for developers, but show the user a generic
        // message - don't leak internal database details to them.
        error_log('Delete member failed: ' . $exception->getMessage());
        $errors[] = 'Unable to delete the member.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delete Team Member</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container narrow">
    <section class="panel">
        <h1>Delete Member</h1>

        <?php if ($errors !== []): ?>
            <div class="alert" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <p>Confirm deletion of this team member:</p>
        <!-- e() escapes the text before printing it, so if a name/email ever
             contained HTML/script characters, the browser shows them as
             plain text instead of running them - this prevents XSS. -->
        <dl class="member-summary">
            <dt>Name</dt>
            <dd><?= e($user['name']) ?></dd>
            <dt>Email</dt>
            <dd><?= e($user['email']) ?></dd>
        </dl>

        <!-- Submitting this form is what sends the POST request that
             actually triggers the delete above. -->
        <form method="post">
            <div class="form-actions">
                <button class="button danger-button" type="submit">Delete</button>
                <a href="index.php">Cancel</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>

