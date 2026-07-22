<?php
$dsn = "mysql:host=".getenv('DB_HOST').";port=".getenv('DB_PORT')
     .";dbname=".getenv('DB_NAME').";charset=utf8mb4";
$options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), $options);
        $stmt = $pdo->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
        $stmt->execute([$_POST['name'], $_POST['email']]);   // input sent separately = injection-safe
        header('Location: index.php');
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() === '23000') {                     // duplicate UNIQUE email
            $error = 'That email already exists. Please use a different one.';
        } else {
            $error = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<h1>Add Member</h1>
<?php if ($error): ?>
  <p style="color:red;"><?= $error ?></p>
<?php endif; ?>
<form method="post">
  Name: <input name="name" required><br>
  Email: <input name="email" type="email" required><br>
  <button type="submit">Save</button>
</form>
<p><a href="index.php">Back to list</a></p>


