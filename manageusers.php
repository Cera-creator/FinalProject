<?php
require('authadmin.php');
require('connect.php');
include('navbar.php');

$stmt = $db->query("SELECT id, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="cool.css">
</head>
<body>
    <div class="admin-panel">
        <h1>Manage Users</h1>
        <a href="adduser.php"> Add New User</a>
        <table border="1" cellpadding="8" cellspacing="0">
            <tr>
                <th>ID</th><th>Email</th><th>Role</th><th>Created</th>
            </tr>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <a href="edituser.php?id=<?= $user['id'] ?>">Edit</a>
                        <a href="deleteuser.php?id=<?= $user['id'] ?>" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
