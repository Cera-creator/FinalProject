<?php
session_start();
require('connect.php');
include('navbar.php');

$game_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$comments_stmt = $db->prepare("SELECT * FROM comments WHERE game_id = ? ORDER BY created_at DESC");
$comments_stmt->execute([$game_id]);
$comments = $comments_stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cool.css">
    <title>Top Games</title>
</head>
<body>
    <div id="wrapper">
        <div id="header">
            <h1>
                <a href="index.php">SuperCoolTwitchName's Top Games</a>
            </h1>
        </div>
        <div class="game-details">
            <?php
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $game_id = $_GET['id'];

                $query = "SELECT games.*, genre.name AS genre_name FROM games
                          LEFT JOIN genre ON games.genre_id = genre.id
                          WHERE games.id = :id";
                $statement = $db->prepare($query);
                $statement->bindValue(':id', $game_id, PDO::PARAM_INT);
                $statement->execute();
                $game = $statement->fetch(PDO::FETCH_ASSOC);

                if (!$game) {
                    echo "Game not found!";
                    exit;
                }

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
            ?>

            <h2><?= htmlspecialchars($title) ?></h2>
            <div class="description">
                <strong>Description:</strong> <?= $description ?>
            </div>
            <br>
            <div class="genre">
                <strong>Genre: </strong><?= $genre_name ?>
            </div>
            <br>
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
                    $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'default-page.php';
                ?>
                <button class="back-btn" onclick="window.location.href='<?= $previousPage ?>'">Go Back</button>
            </div>
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

<?php if (isset($_SESSION['role']) && in_array($_SESSION['role'], ['user', 'admin'])): ?>
    <div class="comment-form">
        <h3>Leave a Comment</h3>
        <form method="post" action="comment.php">
            <input type="hidden" name="game_id" value="<?= $game_id ?>">

            <p>
                <label for="comment">Your Comment:</label><br>
                <textarea name="comment" id="comment" required rows="4" cols="50"></textarea>
            </p>

            <p>
                <label for="rating">Rating (1-5):</label><br>
                <input type="number" name="rating" id="rating" min="1" max="5">
            </p>

            <button type="submit">Post Comment</button>
        </form>
    </div>
<?php else: ?>
    <p><em>You must be logged in as a user or admin to leave a comment.</em></p>
    <a href="login.php">Log in</a> or <a href="register.php">register</a> to join the conversation!
<?php endif; ?>


        </div>
    </div>

    <div id="didYouKnow" class="did-you-know-box"></div>
    <script src="script.js"></script>
</body>
</html>
