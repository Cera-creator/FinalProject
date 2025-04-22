<?php
session_start();
require('connect.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role']; 
    if ($user['role'] === 'admin') {
        header("Location: index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}
 else {
            $error = "Invalid email or password.";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cool.css">
    <title>Login</title>
</head>
<body>
<?php include('navbar.php'); ?>
<div id='login'>
<h2>Login</h2>
<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST">
    <input type="text" name="email" placeholder="Email@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit" name="login">Login</button>
</form>

<p>Don't have an account? <a href="register.php">Register here</a></p> 
</div>
    <footer>
    <p>Check out more: 
        <a href="https://yourlink1.com">Link 1</a> |
        <a href="https://yourlink2.com">Link 2</a>
    </p>
</footer>
</body>
</html>
