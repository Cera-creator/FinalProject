<?php
require('authadmin.php');
require('connect.php');

$id = $_GET['id'];

if ($_SESSION['user_id'] == $id) {
    die("You cannot delete your own account.");
}

$stmt = $db->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$id]);

header("Location: manageusers.php");
exit;
