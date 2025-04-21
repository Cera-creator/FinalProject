<?php
require('authadmin.php');
require('connect.php');
include('navbar.php');

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    $query = "UPDATE users SET email = ?, role = ? WHERE id = ?";
    $params = [$email, $role, $id];

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $query = "UPDATE users SET  email = ?, role = ?, password = ? WHERE id = ?";
        $params = [$email, $role, $password, $id];
    }

    $stmt = $db->prepare($query);
    $stmt->execute($params);

    header("Location: manageusers.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cool.css">
    <title>Edit User</title>
</head>
<body>
<h2>Edit User</h2>
<form method="post">
    Email: <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>
    Password (leave blank to keep current): <input type="password" name="password"><br>
    Role:
    <select name="role">
        <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
    </select><br>
    <button type="submit">Update User</button>
</form>
</body>
</html>
