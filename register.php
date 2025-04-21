<?php
session_start();
require('connect.php');
include('navbar.php');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($email) || empty($password) || empty($confirm)) {
        $errors[] = "All fields are required.";
    } elseif ($password !== $confirm) {
        $errors[] = "Passwords do not match.";
    } else {

        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Username already taken.";
        }
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (email, password, role) VALUES (:email, :password, 'user')");
        $stmt->execute([
            'email' => $email,
            'password' => $hashedPassword
        ]);

        $_SESSION['user_id'] = $db->lastInsertId();
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'user';

        header("Location: index.php");
        exit;
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
    <title>Register</title>
</head>
<body>
<div id="login">
<h2>Register</h2>
<?php if ($errors): ?>
    <div class="error">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<form method="POST">
    <input type="text" name="email" placeholder="Email@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    <input type="password" name="password" placeholder="Password" required>
    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
    <button type="submit">Register</button>
</form>

<p>Already have an account? <a href="login.php">Log in</a></p>
</div>
</body>
</html>
