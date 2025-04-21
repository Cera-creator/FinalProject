<?php
session_start();
include('connect.php');
include('navbar.php');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['user', 'admin'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$msg = $_GET['msg'] ?? null;

if ($role === 'admin') {
    $stmt = $db->query("SELECT comments.*, games.title AS game_title FROM comments JOIN games ON comments.game_id = games.id ORDER BY comments.created_at DESC");
} else {
    $stmt = $db->prepare("SELECT comments.*, games.title AS game_title FROM comments JOIN games ON comments.game_id = games.id WHERE comments.user_id = ? ORDER BY comments.created_at DESC");
    $stmt->execute([$user_id]);
}

$comments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cool.css">
    <title>Moderate Comments</title>
</head>
<body>

<h2>All Comments</h2>

<?php if ($msg === 'edited'): ?>
    <p class="msg">Comment updated successfully!</p>
<?php elseif ($msg === 'deleted'): ?>
    <p class="msg">Comment deleted.</p>
<?php endif; ?>

<?php if (count($comments) > 0): ?>
    <table>
        <tr>
            <th>Game</th>
            <th>Comment</th>
            <th>Rating</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($comments as $comment): ?>
            <tr>
                <td><?= htmlspecialchars($comment['game_title']) ?></td>
                <td><?= nl2br(htmlspecialchars($comment['content'])) ?></td>
                <td><?= $comment['rating'] ?? '-' ?></td>
                <td><?= htmlspecialchars($comment['created_at']) ?></td>
                <td class="actions">
                    <a href="editcomment.php?id=<?= $comment['id'] ?>">Edit</a>
                    <a href="deletecomment.php?id=<?= $comment['id'] ?>" onclick="return confirm('Are you sure you want to delete this comment?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php else: ?>
    <p>You haven’t posted any comments yet.</p>
<?php endif; ?>
</body>
</html>