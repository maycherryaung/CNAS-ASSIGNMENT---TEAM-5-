<?php
$dsn = "mysql:host=".getenv('DB_HOST').";port=".getenv('DB_PORT')
     .";dbname=".getenv('DB_NAME').";charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

$id = (int)($_GET['id'] ?? 0);   // read the id from the URL, force it to an integer

try {
    $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), $options);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Confirmed: delete the row
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
        $stmt->execute([$id]);
        header('Location: index.php');
        exit;
    }

    // Not a POST: load the row so we can show who is about to be deleted
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die('Member not found. <a href="index.php">Back to list</a>');
    }
} catch (PDOException $e) {
    die('Database error: ' . htmlspecialchars($e->getMessage()));
}
?>
<h1>Delete Member</h1>
<p>Are you sure you want to delete
   <strong><?= htmlspecialchars($user['name']) ?></strong>
   (<?= htmlspecialchars($user['email']) ?>)?</p>
<form method="post">
  <button type="submit">Yes, delete</button>
</form>
<p><a href="index.php">Cancel</a></p>

