<?php
session_start();
require('connect.php');

// Default sorting
$orderBy = 'id DESC'; 

// Check for sorting parameter in the URL
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

// Fetch categories to display in the tabs
$categoryQuery = "SELECT * FROM categories ORDER BY name ASC";
$categoryStatement = $db->prepare($categoryQuery);
$categoryStatement->execute();

// Handle category filtering if selected
$categoryFilter = '';
$categoryId = null; // Initialize $categoryId

if (isset($_GET['category_id'])) {
    $categoryId = (int) $_GET['category_id'];  // Sanitize category input
    $categoryFilter = " WHERE category_id = :category_id";
}

// Fetch games, optionally filtered by category
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
            <!-- Tabs for All Games and Categories -->
            <h3>Browse by Categories</h3>
            <ul class="tabs">
                <!-- "All Games" tab, active when category_id is not set -->
                <li><a href="allgames.php" class="<?= !isset($_GET['category_id']) ? 'active' : ''; ?>">All Games</a></li>
                <?php while ($category = $categoryStatement->fetch(PDO::FETCH_ASSOC)): ?>
                    <li><a href="allgames.php?category_id=<?= $category['id'] ?>" class="<?= isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'active' : ''; ?>"><?= htmlspecialchars($category['name']) ?></a></li>
                <?php endwhile; ?>
            </ul>
        </div>
        <!-- Show the games list only if category_id is set or if we're on the All Games view -->
        <ul class="games-list" style="display: <?= isset($_GET['category_id']) || !empty($categoryId) ? 'block' : 'none'; ?>;">
            <?php while ($row = $statement->fetch(PDO::FETCH_ASSOC)): ?>
                <h2>Available Games</h2>
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
