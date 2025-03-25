<?php

require('connect.php');

$query = "SELECT * FROM games ORDER BY id DESC";
$statement = $db->prepare($query);
$statement->execute();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
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
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="create.php">Create</a></li>
            </ul>
        </div>

        <h2>Available Games</h2>
        <ul>
            <?php while ($row = $statement->fetch(PDO::FETCH_ASSOC)): ?>
                <li>
                    <h3>
                        <a href="fullgame.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a>
                    </h3>
                    <p><strong>Genre: </strong><?= htmlspecialchars($row['genre']) ?></p>
                    <p><strong>Release Date: </strong><?= htmlspecialchars($row['release_date']) ?></p>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
