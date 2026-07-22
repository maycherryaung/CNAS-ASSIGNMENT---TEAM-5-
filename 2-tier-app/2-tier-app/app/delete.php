<?php
require 'db.php';
$conn = db();

$id = id_from_query();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();
        header('Location: index.php');
        exit;
    } catch (\mysqli_sql_exception $e) {
        error_log('Delete failed: ' . $e->getMessage());
        die('Database error. Please try again later.');
    }
}

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
?>
<!DOCTYPE html>
<html><body>
<h2>Delete Member</h2>
<p>Are you sure you want to delete
   <strong><?= e($user['name']) ?></strong>
   (<?= e($user['email']) ?>)?</p>
<form method="POST">
  <button type="submit">Yes, delete</button>
</form>
<p><a href="index.php">Cancel</a></p>
</body></html>
