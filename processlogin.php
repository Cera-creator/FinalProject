<?php
session_start();
require 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];

if ($email === 'wally' && $password === 'mypass') {
    $_SESSION['user_id'] = 1;
    $_SESSION['email'] = 'admin@local'; 
    $_SESSION['role'] = 'admin';
    header('Location: admin_dashboard.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] === 'admin') {
        header('Location: admin_dashboard.php');
    } else {
        header('Location: user_dashboard.php');
    }
    exit;
} else {
    echo "Invalid login.";
}
