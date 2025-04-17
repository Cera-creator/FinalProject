<?php
session_start();
require('connect.php');
include('navbar.php');

$game_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$game = null;
$image = null;
$title = $description = $genre_name = $release_date = "";

if ($game_id > 0) {
    $query = "SELECT games.*, genre.name AS genre_name FROM games
              LEFT JOIN genre ON games.genre_id = genre.id
              WHERE games.id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $game_id, PDO::PARAM_INT);
    $statement->execute();
    $game = $statement->fetch(PDO::FETCH_ASSOC);

    if ($game) {
        $query_image = "SELECT * FROM images WHERE game_id = :game_id ORDER BY created_at DESC LIMIT 1";
        $statement_image = $db->prepare($query_image);
        $statement_image->bindValue(':game_id', $game_id, PDO::PARAM_INT);
        $statement_image->execute();
        $image = $statement_image->fetch(PDO::FETCH_ASSOC);

        $title = $game['title'];
        $description = nl2br(htmlspecialchars($game['description']));
        $genre_name = htmlspecialchars($game['genre_name']);
        $release_date = htmlspecialchars($game['release_date']);
    }
}
$comments_stmt = $db->prepare("SELECT * FROM comments WHERE game_id = ? ORDER BY created_at DESC");
$comments_stmt->execute([$game_id]);
$comments = $comments_stmt->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['captcha']) && $_POST['captcha'] === $_SESSION['captcha_code']) {
        $comment = htmlspecialchars($_POST['comment']);
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
        $game_id = (int)$_POST['game_id'];
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Anonymous';
        $insert_query = "INSERT INTO comments (game_id, name, user_id, content, rating) 
                         VALUES (:game_id, :name, :user_id, :content, :rating)";
        $stmt = $db->prepare($insert_query);
        $stmt->bindValue(':game_id', $game_id, PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':content', $comment, PDO::PARAM_STR);
        $stmt->bindValue(':rating', $rating, PDO::PARAM_INT);
        if ($stmt->execute()) {
            header("Location: fullgame.php?id=" . $_POST['game_id']);
            exit; 
        } else {
            echo "Error in inserting comment!";
        }
    } else {
        $_SESSION['captcha_error'] = "Invalid CAPTCHA, please try again!";
        header("Location: fullgame.php?id=" . $_POST['game_id']); 
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Top Games</title>
    <link rel="stylesheet" href="cool.css">
</head>
<body>
<div id="wrapper">
    <div id="header">
        <h1><a href="index.php">SuperCoolTwitchName's Top Games</a></h1>
    </div>
    <div class="game-details">
        <?php if ($game): ?>
            <h2><?= htmlspecialchars($title) ?></h2>
            <div class="description">
                <strong>Description:</strong> <?= $description ?>
            </div><br>
            <div class="genre">
                <strong>Genre: </strong><?= $genre_name ?>
            </div><br>
            <div class="release">
                <strong>Release Date: </strong><?= $release_date ?>
            </div>
            <?php if ($image): ?>
                <div class="current-image">
                    <?php
                        $medium_image_path = 'uploads/' . pathinfo($image['image_path'], PATHINFO_FILENAME) . '_medium.' . pathinfo($image['image_path'], PATHINFO_EXTENSION);
                    ?>
                    <img src="<?= $image['image_path'] ?>" alt="Current Image" class="medium-image">
                </div>
            <?php else: ?>
                <p>No image available.</p>
            <?php endif; ?>
            <div id="edit">
                <p>Updated On:
                    <?php
                        $formattedDate = date("F d, Y, h:i a", strtotime($game['updated_at']));
                        echo $formattedDate;
                    ?>
                </p>
                <a href="edit.php?id=<?= $game['id'] ?>">Edit</a>
                <?php
                    $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php';
                ?>
                <button class="back-btn" onclick="window.location.href='<?= $previousPage ?>'">Go Back</button>
            </div>
        <?php else: ?>
            <p><strong>Game not found!</strong></p>
        <?php endif; ?>
        <div class="comments-section">
            <h3>Comments</h3>
            <?php if (count($comments) === 0): ?>
                <p>No comments yet. Be the first to comment!</p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="comment">
                        <strong>
                            <?php
                                if (!empty($comment['name'])) {
                                    echo htmlspecialchars($comment['name']);
                                } elseif (!empty($comment['user_id'])) {
                                    echo "User #" . $comment['user_id'];
                                } else {
                                    echo "Anonymous";
                                }
                            ?>
                        </strong>
                        <em><?= date('F j, Y, g:i a', strtotime($comment['created_at'])) ?></em>
                        <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
                        <?php if (!empty($comment['rating'])): ?>
                            <p>Rating: <?= (int)$comment['rating'] ?>/5</p>
                        <?php endif; ?>
                    </div>
                    <hr>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
<?php if ($game && isset($_SESSION['role']) && in_array($_SESSION['role'], ['user', 'admin'])): ?>
    <div class="comment-form">
        <h3>Leave a Comment</h3>
        <?php if (isset($_SESSION['captcha_error'])): ?>
            <div class="error-message" style="color: red;">
                <?= $_SESSION['captcha_error']; ?>
            </div>
            <?php unset($_SESSION['captcha_error']); ?>
        <?php endif; ?>
        <form method="POST" action="comment.php?id=<?= $game_id ?>">
            <input type="hidden" name="game_id" value="<?= $game_id ?>">
            <p>
                <label for="comment">Your Comment:</label><br>
                <textarea name="comment" id="comment" required rows="4" cols="50"><?= isset($_SESSION['saved_comment']) ? htmlspecialchars($_SESSION['saved_comment']) : '' ?></textarea>
            </p>
            <p>
                <label for="rating">Rating (1-5):</label><br>
                <input type="number" name="rating" id="rating" min="1" max="5" value="<?= isset($_SESSION['saved_rating']) ? $_SESSION['saved_rating'] : '' ?>">
            </p>
            <p>
                <img src="captcha.php" id="captcha-img" alt="CAPTCHA">
                <button type="button" onclick="refreshCaptcha()">Refresh CAPTCHA</button><br><br>
                <label for="captcha">Enter the code shown above:</label><br>
                <input type="text" name="captcha" id="captcha" required>
            </p>
            <button type="submit">Post Comment</button>
        </form>
    </div>
<?php elseif ($game): ?>
    <p><em>You must be logged in as a user or admin to leave a comment.</em></p>
    <a href="login.php">Log in</a> or <a href="register.php">register</a> to join the conversation!
<?php endif; ?>
    </div>
</div>
<script>
function refreshCaptcha() {
    document.getElementById('captcha-img').src = 'captcha.php?rand=' + Math.random();
}
</script>
</body>
</html>