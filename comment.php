<?php
session_start();
include('connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_POST); 
    echo "</pre>";
}

$game_id = (int) ($_POST['game_id'] ?? 0);
$name = trim($_POST['name'] ?? '');
$content = trim($_POST['comment'] ?? '');
$rating = isset($_POST['rating']) ? (int) $_POST['rating'] : null;
$user_captcha = trim($_POST['captcha'] ?? '');

if (strcasecmp($user_captcha, $_SESSION['captcha']) !== 0) {
    $_SESSION['captcha_error'] = 'Incorrect CAPTCHA. Please try again.';
    $_SESSION['saved_comment'] = $content;
    $_SESSION['saved_rating'] = $rating;  
    header('Location: fullgame.php?id=' . $game_id); 
    exit;
}

if (!$content || !$game_id || (!isset($_SESSION['user_id']) && !$name)) {
    $_SESSION['form_error'] = 'Please fill in all the required fields.';
    header("Location: fullgame.php?id=" . $game_id . "&error=missing_fields");
    exit;
}

$stmt = $db->prepare("SELECT id FROM games WHERE id = ?");
$stmt->execute([$game_id]);
$game = $stmt->fetch();

if (!$game) {
    $_SESSION['form_error'] = 'Game not found.';
    header("Location: fullgame.php?id=" . $game_id . "&error=game_not_found");
    exit;
}

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

try {
    $stmt = $db->prepare("INSERT INTO comments (game_id, user_id, name, content, rating) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$game_id, $user_id, $name, $content, $rating]);
} catch (PDOException $e) {
    $_SESSION['form_error'] = 'Error inserting comment: ' . $e->getMessage();
    header("Location: fullgame.php?id=" . $game_id);
    exit;
}
unset($_SESSION['captcha']);
unset($_SESSION['saved_comment']);
unset($_SESSION['saved_rating']);

header("Location: fullgame.php?id=" . $game_id);
exit;
?>
