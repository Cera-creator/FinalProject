<?php
require('authadmin.php');
require('connect.php');

$stmt = $db->query("SELECT id, email, role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cool.css">
    <title>Manage Users</title>
</head>
<body>
     <?php include('navbar.php'); ?>
    <div id="wrapper">
        <h2>Manage Users</h2>
        <?php if (!empty($users)): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td style="white-space: nowrap;">
                            <form method="get" action="edituser.php" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="button">Edit</button>
                            </form>

                            <form method="post" action="deleteuser.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="button delete-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <form action="adduser.php" method="get" style="margin-bottom: 20px;">
    <button type="submit" class="button">Add New User</button>
</form>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </div>
</body>
</html>

