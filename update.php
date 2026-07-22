<?php
$dsn = "mysql:host=".getenv('DB_HOST').";port=".getenv('DB_PORT')
     .";dbname=".getenv('DB_NAME').";charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

$id = (int)($_GET['id'] ?? 0);   // read the id from the URL, force it to an integer
$error = '';

try {
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), $options);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Save the edited values
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$_POST['name'], $_POST['email'], $id]);
        header('Location: index.php');
        exit;
    }

    // Not a POST: load the current row to pre-fill the form
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Member not found. <a href="index.php">Back to list</a>');
    }
} catch (PDOException $e) {
    if ($e->getCode() === '23000') {                 // duplicate email
        $error = 'That email already exists. Please use a different one.';
    } else {
        die('Database error: ' . htmlspecialchars($e->getMessage()));
    }
}
?>
<h1>Edit Member</h1>
<?php if ($error): ?>
  <p style="color:red;"><?= $error ?></p>
<?php endif; ?>
<form method="post">
  Name: <input name="name" value="<?= htmlspecialchars($user['name']) ?>" required><br>
  Email: <input name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
  <button type="submit">Update</button>
</form>
<p><a href="index.php">Back to list</a></p>
