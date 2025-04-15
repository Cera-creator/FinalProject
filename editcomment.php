<?php
session_start();
include('connect.php');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'user'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$comment_id = (int) ($_GET['id'] ?? 0);

$stmt = $db->prepare("SELECT * FROM comments WHERE id = ?");
$stmt->execute([$comment_id]);
$comment = $stmt->fetch();

if (!$comment) {
    exit("Comment not found.");
}

if ($role !== 'admin' && $comment['user_id'] != $user_id) {
    exit("You are not authorized to edit this comment.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_content = trim($_POST['content'] ?? '');
    $new_rating = isset($_POST['rating']) ? (int) $_POST['rating'] : null;

    if (!$new_content) {
        echo "<p style='color:red;'>Content cannot be empty.</p>";
    } else {
        $stmt = $db->prepare("UPDATE comments SET content = ?, rating = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$new_content, $new_rating, $comment_id]);

        header("Location: moderate_comments.php?msg=edited");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="cool.css">
    <title>Edit Comment</title>
</head>
<body>

    <h2>Edit Comment</h2>
    <form method="POST">
        <p>
            <label>Comment:</label><br>
            <textarea name="content" rows="5" cols="50"><?= htmlspecialchars($comment['content']) ?></textarea>
        </p>
        <p>
            <label>Rating (optional):</label><br>
            <select name="rating">
                <option value="">No rating</option>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <option value="<?= $i ?>" <?= $comment['rating'] == $i ? 'selected' : '' ?>><?= $i ?></option>
                <?php endfor; ?>
            </select>
        </p>
        <button type="submit">Save Changes</button>
        <a href="moderate_comments.php">Cancel</a>
    </form>
</body>
</html>
