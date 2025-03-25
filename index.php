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
                <li><a href="index.php">Home</a></li>
                <li><a href="create.php">Create</a></li>
             </div>
        <ul>
            <?php while ($row = $statement->fetch(PDO::FETCH_ASSOC)): ?>
                <li>
                    <h2>
                        <a href="fullgame.php?id=<?= $row['id'] ?>"><?= $row['title'] ?></a>
                    </h2>
                    <br>
                    <div id="edit">
                    </div>
                    <br>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
