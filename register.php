<?php
session_start();
require('connect.php');

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
<html>
<head>
    <title>Register</title>
    <style>
        form { max-width: 300px; margin: 50px auto; }
        input { width: 100%; padding: 8px; margin: 8px 0; }
        .error { color: red; margin-bottom: 10px; }
    </style>
</head>
<body>

<h2 style="text-align: center;">Register</h2>

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

<p style="text-align: center;">Already have an account? <a href="login.php">Log in</a></p>

</body>
</html>
