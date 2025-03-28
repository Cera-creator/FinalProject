<?php

session_start();

require('authenticate.php');

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id']; 
        $_SESSION['is_admin'] = $user['is_admin']; 
        header("Location: index.php");
        exit(); 
    } else {
        echo "Invalid credentials!";
    }
}