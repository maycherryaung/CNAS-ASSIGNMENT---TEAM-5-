<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$id = id_from_query();
$conn = db();
$errors = [];

$stmt = $conn->prepare('SELECT id, name, email FROM users WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    http_response_code(404);
    exit('Team member not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit();
    } catch (mysqli_sql_exception $exception) {
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
        <dl class="member-summary">
            <dt>Name</dt>
            <dd><?= e($user['name']) ?></dd>
            <dt>Email</dt>
            <dd><?= e($user['email']) ?></dd>
        </dl>

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

