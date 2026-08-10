<?php
// Enforce strict types so PHP won't silently coerce mismatched types (e.g. string vs int).
declare(strict_types=1);

/* This instructs the script to include another file named db.php. This external file contains the complex logic required to connect to the database. By placing the connection logic in a separate file, the application maintains a clean separation of concerns.
*/
require_once __DIR__ . '/db.php';

/*
prepare('SELECT ...'): The script asks the database to prepare a search command (a query) to retrieve the id, name, and email of all users, sorted by their ID number. Using prepare instead of directly running a query is a security measure called a "prepared statement." It separates the query instructions from any user input, preventing a common attack called SQL Injection.execute() and get_result()->fetch_all(...): The script runs the prepared query and pulls all the retrieved rows into a variable named $users. The data is formatted as an associative array, which is a list where each piece of data is labeled with its column name (e.g., 'name', 'email').close(): This closes the connection to the database. Cloud native applications must aggressively manage network resources to ensure the system does not run out of available connections when scaling.
*/

$conn = db();
$stmt = $conn->prepare('SELECT id, name, email FROM users ORDER BY id ASC');
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CNAS Assignment - Team Members</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="container">
    <!-- Page header: title, class/team info, and a button to add a new member -->
    <section class="header">
        <div>
            <p class="eyebrow">Cloud Native Application and Security</p>
            <h1>CNAS Assignment - Team Members</h1>
            <p class="meta">
    		 <strong>CLASS:</strong> T01<br>
   		 <strong>TEAM:</strong> 5<br>
   		 <strong>MEMBERS:</strong> May Cherry Aung, Aw Ming Jie, Abin Aneesh, Gandhimathi Murugavel Dhushyanth
	    </p> 
        </div>
        <a class="button" href="create.php">Add New Team Member</a>
    </section>

    <!-- Team list: shows a fallback message if empty, otherwise a table of all users -->
    <section class="panel">
        <h2>Current Team List</h2>
        <?php if (count($users) === 0): ?>
            <p>No team members have been added yet.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <!-- (int) cast + e() (escape) prevent XSS / type issues when printing DB values -->
                            <td><?= (int) $user['id'] ?></td>
                            <td><?= e($user['name']) ?></td>
                            <td><?= e($user['email']) ?></td>
                            <td class="actions">
                                <a href="update.php?id=<?= (int) $user['id'] ?>">Edit</a>
                                <a class="danger" href="delete.php?id=<?= (int) $user['id'] ?>">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</main>
</body>
</html>

