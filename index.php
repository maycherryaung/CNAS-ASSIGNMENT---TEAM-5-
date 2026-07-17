<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

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
    <section class="header">
        <div>
            <p class="eyebrow">Cloud Native Application and Security</p>
            <h1>CNAS Assignment - Team Members</h1>
            <p class="meta">
                <strong>CLASS:</strong> T01 <br>
                <strong>TEAM:</strong> TEAM_5<br>
                <strong>MEMBERS:</strong> 
                May Cherry Aung
                Aw Ming Jie
                Abin Aneesh
                Gandhimathi Murugavel Dhushyanth
            </p>
        </div>
        <a class="button" href="create.php">Add New Team Member</a>
    </section>

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

