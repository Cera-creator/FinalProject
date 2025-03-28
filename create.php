<?php

require('connect.php');
require('authenticate.php');

$query_genre = "SELECT DISTINCT genre FROM games";  
$statement_genre = $db->prepare($query_genre);
$statement_genre->execute();
$genres = $statement_genre->fetchAll(PDO::FETCH_ASSOC);

$genre_to_category = [
    'Tower Defense' => 1, 
    'Adventure' => 2, 
    'Free To Play' => 3, 
    'Roguelike' => 4,
    'Survival Horror' => 5,
    'Simulation' => 6,
    'Open World' => 7
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $genre = filter_input(INPUT_POST, 'genre', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $release_date = filter_input(INPUT_POST, 'release_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($title) || empty($description) || empty($genre) || empty($release_date)) {
        header('Location: errormsg.php'); 
        exit; 
    } else {
        if (array_key_exists($genre, $genre_to_category)) {
            $category_id = $genre_to_category[$genre];
        } else {
            $category_id = 0;  
        }

        $query = "INSERT INTO games (title, description, genre, release_date, category_id) 
                  VALUES (:title, :description, :genre, :release_date, :category_id)";
        $statement = $db->prepare($query);

        $statement->bindValue(':title', $title);
        $statement->bindValue(':description', $description);
        $statement->bindValue(':genre', $genre);
        $statement->bindValue(':release_date', $release_date);
        $statement->bindValue(':category_id', $category_id); 

        $statement->execute();

        header('Location: index.php');
        exit; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Add A Game</title>
</head>
<body>
    <div id="wrapper">
        <div id="menu">    
            <h1><a href="index.php">Return Home</a></h1>
        </div>
        <form method="post" action="create.php">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" />

            <label for="description">Description</label>
            <textarea id="description" name="description"></textarea>

            <label for="genre">Genre</label>
            <select id="genre" name="genre">
                <?php foreach ($genres as $genre_option): ?>
                    <option value="<?= htmlspecialchars($genre_option['genre']) ?>"><?= htmlspecialchars($genre_option['genre']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="release_date">Release Date</label>
            <input type="date" name="release_date" />

            <input type="submit" value="Submit">
        </form>
        <?php
                $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'default-page.php';?>
            <button class="back-btn" onclick="window.location.href='<?php echo $previousPage; ?>'">Go Back</button>
    </div>
</body>
</html>
