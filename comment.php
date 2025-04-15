<?php
include('connect.php');

$game_id = (int) ($_POST['game_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$content = trim($_POST['comment'] ?? '');
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : null;

session_start();
$user_id = $_SESSION['user_id'] ?? null;

if (!$content || !$game_id || (!$user_id && !$name)) {
    header("Location: fullgame.php?id=" . $game_id . "&error=missing_fields");
    exit;
}

$stmt = $db->prepare("SELECT id FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if (!$game) {
    header("Location: fullgame.php?id=" . $game_id . "&error=game_not_found");
    exit;
}

$user_id_to_insert = $user_id ? $user_id : null;

try {
    $stmt = $db->prepare("INSERT INTO comments (game_id, user_id, name, content, rating) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$game_id, $user_id_to_insert, $name, $content, $rating]);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); 
    exit;
}

header("Location: fullgame.php?id=" . $game_id);
exit;
?>