<?php
session_start();
require('authenticate.php');

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['is_admin'] = $user['is_admin']; 
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login to Your Account</h2>

    <?php
    if (isset($error_message)) {
        echo "<p style='color: red;'>$error_message</p>";
    }
    ?>

    <form action="index.php" method="POST">
        <div>
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div>
            <button type="submit" name="login">Login</button>
        </div>
    </form>
</body>
</html>
