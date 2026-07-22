<?php
require 'db.php';
$conn = db();
?>
<!DOCTYPE html>
<html>
<head><title>CNAS Assignment - Team Members List</title></head>
<body>
<h2>CNAS Assignment - Team Members</h2>
<p>CLASS: T01 &nbsp; TEAM: 5</p>
<a href="create.php">Add New Team Member</a>
<table border="1" cellpadding="8" cellspacing="0">
<tr><th>ID</th><th>Student Name</th><th>Email</th><th>Actions</th></tr>
<?php
$result = $conn->query('SELECT * FROM users');
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>" . (int)$row['id'] . "</td>
            <td>" . e($row['name']) . "</td>
            <td>" . e($row['email']) . "</td>
            <td>
                <a href='update.php?id=" . (int)$row['id'] . "'>Edit</a> |
                <a href='delete.php?id=" . (int)$row['id'] . "'>Delete</a>
            </td>
          </tr>";
}
$conn->close();
?>
</table>
</body>
</html>
