<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

// Defaults for the form fields and validation error messages.
// $name/$email get re-used to refill the form if validation fails.
$name = '';
$email = '';
$errors = [];

// Only run the validation/insert logic when the form has been submitted (POST).
// A plain GET request just shows the empty form below.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the submitted values, defaulting to '' if missing, and trim whitespace.
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    // Validate name: required, max 100 chars (matches the DB column limit).
    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Name is required and must be 100 characters or fewer.';
    }

    // Validate email: must be a real email format and max 191 chars (matches the DB column limit).
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191) {
        $errors[] = 'Enter a valid email address of 191 characters or fewer.';
    }

    // Only attempt the insert if both fields passed validation.
    if ($errors === []) {
        $conn = db();

        try {
            // Prepared statement with bound params ('ss' = two strings) protects against SQL injection.
            $stmt = $conn->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
            $stmt->bind_param('ss', $name, $email);
            $stmt->execute();
            $stmt->close();
            // Success: redirect back to the list page (prevents duplicate submits on refresh).
            header('Location: index.php');
            exit();
        } catch (mysqli_sql_exception $exception) {
            // Most likely cause: duplicate email (UNIQUE constraint). Log details, show a generic message.
            error_log('Create member failed: ' . $exception->getMessage());
            $errors[] = 'Unable to create the member. Check whether the email already exists.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Team Member</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container narrow">
    <section class="panel">
        <h1>Create New Team Member</h1>

        <!-- Show any validation/DB errors from the POST handling above -->
        <?php if ($errors !== []): ?>
            <div class="alert" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Submits back to this same page (POST); values are re-filled with e() to escape output safely -->
        <form method="post" novalidate>
            <label for="name">Member Name</label>
            <input id="name" name="name" value="<?= e($name) ?>" maxlength="100" required>

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="<?= e($email) ?>" maxlength="191" required>

            <div class="form-actions">
                <button class="button" type="submit">Create</button>
                <a href="index.php">Back</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>

