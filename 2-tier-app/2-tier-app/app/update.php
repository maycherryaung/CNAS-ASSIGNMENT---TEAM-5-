<?php
require 'db.php';
$conn = db();

$id = id_from_query();
$error = '';
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = $_POST['name'];
    $email = $_POST['email'];

    try {
        $stmt = $conn->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->bind_param('ssi', $name, $email, $id);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit;
    } catch (\mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            $error = 'That email already exists. Please use a different one.';
            $user = ['name' => $name, 'email' => $email];   // keep what they typed
        } else {
            error_log('Update failed: ' . $e->getMessage());
            die('Database error. Please try again later.');
        }
    }
} else {
    try {
        $stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } catch (\mysqli_sql_exception $e) {
        error_log('Lookup failed: ' . $e->getMessage());
        die('Database error. Please try again later.');
    }

    if (!$user) {
        die('Member not found. <a href="index.php">Back to list</a>');
    }
}
?>
<!DOCTYPE html>
<html><body>
<h2>Edit Member</h2>
<?php if ($error): ?>
  <p style="color:red;"><?= e($error) ?></p>
<?php endif; ?>
<form method="POST">
    Member Name: <input name="name" value="<?= e($user['name']) ?>" required><br><br>
    Email: <input name="email" value="<?= e($user['email']) ?>" required><br><br>
    <button type="submit">Update</button>
</form>
<a href="index.php">Back</a>
</body></html>
