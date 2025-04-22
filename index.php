<?php
session_start();
require('connect.php');

$orderBy = 'id DESC'; 

if (isset($_GET['sort_by'])) {
    $sortBy = $_GET['sort_by'];
    
    switch ($sortBy) {
        case 'title':
            $orderBy = 'title ASC';
            break;
        case 'genre':
            $orderBy = 'genre ASC';
            break;
        case 'release_date':
            $orderBy = 'release_date ASC';
            break;
        default:
            $orderBy = 'id DESC'; 
            break;
    }
}

$categoryQuery = "SELECT * FROM categories ORDER BY name ASC";
$categoryStatement = $db->prepare($categoryQuery);
$categoryStatement->execute();

$categoryFilter = '';
$categoryId = null; 

if (isset($_GET['category_id'])) {
    $categoryId = (int) $_GET['category_id']; 
    $categoryFilter = " WHERE category_id = :category_id";
}

$query = "SELECT * FROM games" . $categoryFilter . " ORDER BY $orderBy";
$statement = $db->prepare($query);

if ($categoryFilter) {
    $statement->bindParam(':category_id', $categoryId, PDO::PARAM_INT);
}

$statement->execute();
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
    <?php include('navbar.php'); ?>
    <div id="wrapper">
        <div id="header">
            <h1>
                <a href="index.php">SuperCoolTwitchName's Top Games</a>
            </h1>
        </div>

        <h3>Browse by Categories</h3>
        <ul class="tabs">
            <li><a href="allgames.php" class="<?= !isset($_GET['category_id']) ? 'active' : ''; ?>">All Games</a></li>

            <?php while ($category = $categoryStatement->fetch(PDO::FETCH_ASSOC)): ?>
                <li><a href="allgames.php?category_id=<?= $category['id'] ?>" class="<?= isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'active' : ''; ?>"><?= htmlspecialchars($category['name']) ?></a></li>
            <?php endwhile; ?>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a href="create.php">Add A Game</a></li>
            <?php endif; ?>
        </ul>

        <p>Welcome to my content site! Here you can find all types of games that I play, or plan on playing. </p>
        <p>If you want to stay involved, feel free to register an account and unlock the ability to comment on my games. Let me know how you feel! </p>
    </div>

    <div id="didYouKnow" class="did-you-know-box">
    </div>

    <script src="script.js"></script>
    <footer>
    <p>Check out more: 
        <a href="https://yourlink1.com">Link 1</a> |
        <a href="https://yourlink2.com">Link 2</a>
    </p>
</footer>
</body>
</html>
