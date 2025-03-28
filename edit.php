<?php
require('connect.php');
require('authenticate.php');

$query_genre = "SELECT DISTINCT genre FROM games";  
$statement_genre = $db->prepare($query_genre);
$statement_genre->execute();
$genres = $statement_genre->fetchAll(PDO::FETCH_ASSOC);

if ($_POST && isset($_POST['title']) && isset($_POST['description']) && isset($_POST['id']) && isset($_POST['genre']) && isset($_POST['update'])) {
    $title  = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);  
    $id      = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $genre = filter_input(INPUT_POST, 'genre', FILTER_SANITIZE_STRING); 

    if (empty($title) || empty($description)) {
        header('Location: errormsg.php');
        exit;
    }

    $timezone = new DateTimeZone('America/Winnipeg');
    $datetime = new DateTime('now', $timezone);
    $updated_at = $datetime->format('Y-m-d H:i:s');


    $query = "UPDATE games SET title = :title, description = :description, genre = :genre, updated_at = :updated_at WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':title', $title);
    $statement->bindValue(':description', $description);  
    $statement->bindValue(':genre', $genre);
    $statement->bindValue(':updated_at', $updated_at);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);

    if ($statement->execute()) {
        header("Location: index.php?id={$id}");
        exit;
    } else {
        echo "Error: " . implode(", ", $statement->errorInfo());
    }
} elseif (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    $query = "SELECT * FROM games WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);

    $statement->execute();
    $row = $statement->fetch();
} elseif ($_POST && isset($_POST['delete'])) {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    $query = "DELETE FROM games WHERE id = :id";
    $statement = $db->prepare($query);

    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();

    header("Location: index.php");
    exit;
} else {
    $id = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Edit Game</title>
</head>
<body>
    <div id="wrapper">
        <div id="menu">    
            <h1><a href="index.php">Return Home</a></h1>
        </div>
        <?php if ($id): ?>
            <form action="edit.php" method="post">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <label for="title">Title</label>
                <input id="title" name="title" value="<?= $row['title'] ?>">

                <label for="description">Description</label>
                <textarea id="description" name="description"><?= $row['description'] ?></textarea> 
                <label for="genre">Genre</label>
                <select id="genre" name="genre">
                    <?php foreach ($genres as $genre_option): ?>
                        <option value="<?= $genre_option['genre'] ?>" <?= $row['genre'] == $genre_option['genre'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($genre_option['genre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <input type="submit" name="update" value="Update" />
                <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you wish to delete this post?')" />
            </form>
        <?php endif ?>
        <?php
            $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'default-page.php';?>
        <button class="back-btn" onclick="window.location.href='<?php echo $previousPage; ?>'">Go Back</button>
    </div>
</body>
</html>
