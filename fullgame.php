<?php

require('connect.php');

$query = "SELECT * FROM games";
$statement = $db->prepare($query);
$statement->execute();

$games = $statement->fetchAll(PDO::FETCH_ASSOC);

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

        <div id="menu">    
            <a href="index.php">Home</a>
            <a href="create.php">New Game</a>
            
    
        <div class="game-details">
            <?php
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $game_id = $_GET['id'];

                $query = "SELECT * FROM games WHERE id = :id";
                $statement = $db->prepare($query);
                $statement->bindValue(':id', $game_id, PDO::PARAM_INT);
                $statement->execute();

                $row = $statement->fetch(PDO::FETCH_ASSOC);

                if (!$row) {
                    echo "Post not found!";
                    exit;
                }

                $title = $row['title'];
            }
            ?>
            
            <h2><?= htmlspecialchars($row['title']) ?></h2> 
            <div class="description">
                <strong>Description:</strong><?= nl2br(htmlspecialchars($row['description'])) ?>
            </div>
            <br>
            <div class="genre">
                <strong>Genre: </strong><?= htmlspecialchars($row['genre']) ?>
            </div>
            <br>
            <div class="release">
                <strong>Release Date: </strong><?= htmlspecialchars($row['release_date']) ?>
            </div>

            <div id="edit">
                <p>Updated On: 
                    <?php
                        $formattedDate = date("F d, Y, h:i a", strtotime($row['updated_at']));
                        echo $formattedDate;
                    ?>
                </p>
                <a href="edit.php?id=<?= $row['id'] ?>">Edit</a>
                <?php
                    $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'default-page.php';?>
                <button class="back-btn" onclick="window.location.href='<?php echo $previousPage; ?>'">Go Back</button>
            </div>
        </div>
    </div>
</body>
</html>
