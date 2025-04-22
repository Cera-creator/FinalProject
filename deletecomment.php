<?php
session_start();
require('connect.php');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$comment_id = (int) ($_POST['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) {
    exit("Comment not found.");
}

if ($role !== 'admin' && $comment['user_id'] != $user_id) {
    exit("You are not authorized to delete this comment.");
}

$delete = $db->prepare("DELETE FROM comments WHERE id = ?");
$delete->execute([$comment_id]);

header("Location: modcomments.php?msg=deleted");
exit;
