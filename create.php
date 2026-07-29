<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$name = '';
$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string) ($_POST['name'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));

    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Name is required and must be 100 characters or fewer.';
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 191) {
        $errors[] = 'Enter a valid email address of 191 characters or fewer.';
    }

    if ($errors === []) {
        $conn = db();

        try {
            $stmt = $conn->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
            $stmt->bind_param('ss', $name, $email);
            $stmt->execute();
            $stmt->close();
            header('Location: index.php');
            exit();
        } catch (mysqli_sql_exception $exception) {
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

        <?php if ($errors !== []): ?>
            <div class="alert" role="alert">
                <?php foreach ($errors as $error): ?>
                    <p><?= e($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

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

