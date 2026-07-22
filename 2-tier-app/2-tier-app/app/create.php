<?php
require 'db.php';
$conn = db();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];

    try {
        $stmt = $conn->prepare('INSERT INTO users (name, email) VALUES (?, ?)');
        $stmt->bind_param('ss', $name, $email);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit;
    } catch (\mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {          // duplicate UNIQUE email
            $error = 'That email already exists. Please use a different one.';
        } else {
            error_log('Insert failed: ' . $e->getMessage());
            $error = 'Database error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html><body>
<h2>Create New Team Member</h2>
<?php if ($error): ?>
  <p style="color:red;"><?= e($error) ?></p>
<?php endif; ?>
<form method="POST">
    Name: <input name="name" required><br><br>
    Email: <input name="email" required><br><br>
    <button type="submit">Create</button>
</form>
<a href="index.php">Back</a>
</body></html>
