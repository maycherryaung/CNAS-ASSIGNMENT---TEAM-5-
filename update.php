<?php
// strict_types: PHP won't silently convert types (e.g. "5" -> 5) for us,
// so type mistakes fail loudly instead of hiding as bugs.
declare(strict_types=1);

// Shared database connection/helper logic lives in one place (db.php)
// instead of being copy-pasted into every page - separation of concerns.
require_once __DIR__ . '/db.php';

// id_from_query() (in db.php) reads ?id= from the URL, validates it's a positive
// integer, and hard-exits with a 400 if not — so $id is guaranteed safe from here on.
$id = id_from_query();
// Opens (or reuses) the shared database connection.
$conn = db();
// Collects any validation/DB error messages to show in the page below.
$errors = [];

// Look up the existing member so the form can be pre-filled with their current data.
/* prepare and bind_param: Instead of dropping the ID directly into the database command, it uses a placeholder (?) and securely binds the ID as an integer (i). This prevents malicious users from tricking the database into executing unauthorized commands. */

$stmt = $conn->prepare('SELECT id, name, email FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// No row matched that id (e.g. bad/old link) -> stop with a 404.
if (!$user) {
    http_response_code(404);
    exit('Team member not found.');
}

// Default the form fields to the member's current values (shown on a plain GET request).
$name = (string) $user['name'];
$email = (string) $user['email'];

// On submit, overwrite $name/$email with the posted values and validate them,
// same rules as create.php (required, max length, valid email format).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the submitted fields. '?? ""' means "if not set, use an empty
    // string" so a missing field can't cause a crash; trim() strips
    // leading/trailing whitespace the user may have typed.
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    // Name must actually be filled in and fit the database column size.
    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Name is required and must be 100 characters or fewer.';
    }

    // Email must look like a real email address and fit the column size.
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191) {
        $errors[] = 'Enter a valid email address of 191 characters or fewer.';
    }

    // Only touch the DB once validation passes.
    if ($errors === []) {
        try {
            // Prepared statement updates this specific row by id; 'ssi' = string, string, int params.
            $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
            $stmt->bind_param('ssi', $name, $email, $id);
            $stmt->execute();
            $stmt->close();
            // Success: back to the list (avoids re-submitting the form on page refresh).
            header('Location: index.php');
            exit();
        } catch (mysqli_sql_exception $exception) {
            // Most likely cause: new email collides with another existing member (UNIQUE constraint).
            error_log('Update member failed: ' . $exception->getMessage());
            $errors[] = 'Unable to update the member. Check whether the email already exists.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Team Member</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container narrow">
    <section class="panel">
        <h1>Edit Member</h1>

        <!-- Show any validation/DB errors from the POST handling above -->
        <?php if ($errors !== []): ?>
            <div class="alert" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Submits back to this same page (POST, same ?id= in the URL); fields start pre-filled with the current name/email -->
        <form method="post" novalidate>
            <label for="name">Member Name</label>
            <input id="name" name="name" value="<?= e($name) ?>" maxlength="100" required>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= e($email) ?>" maxlength="191" required>

            <div class="form-actions">
                <button class="button" type="submit">Update</button>
                <a href="index.php">Back</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>

