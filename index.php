<?php

$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];


try {

    // Connect to database
    $pdo = new PDO($dsn, $user, $pass, $options);
    // Get users from table
    $users = $pdo->query('SELECT *FROM users');
} catch (PDOException $e) {

    die("Database connection failed: " . htmlspecialchars( $e->getMessage()));
}

?>
<h1>CNAS Assignment -Team Members</h1>
<p>CLASS: T01 &nbsp; TEAM: 5</p>
<a href="create.php">Add New Member</a>
<table border="1">
  <tr><th>ID</th><th>Name</th><th>Email</th><th>Actions</th></tr>
  <?php foreach ($users as $row): ?>
  <tr>
    <td><?= (int)$row['id'] ?></td>
    <td><?= htmlspecialchars($row['name']) ?></td>
    <td><?= htmlspecialchars($row['email']) ?></td>
    <td>
      <a href="update.php?id=<?= (int)$row['id'] ?>">Edit</a>
      <a href="delete.php?id=<?= (int)$row['id'] ?>">Delete</a>
    </td>
  </tr>
  <?php endforeach; ?>
</table>


