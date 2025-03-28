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
        case 'updated_at':
            $orderBy = 'updated_at DESC'; 
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

        <h3>Browse by Categories</h3>
        <ul class="tabs">
            <li><a href="allgames.php" class="<?= !isset($_GET['category_id']) ? 'active' : ''; ?>">All Games</a></li>
            <?php while ($category = $categoryStatement->fetch(PDO::FETCH_ASSOC)): ?>
                <li><a href="allgames.php?category_id=<?= $category['id'] ?>" class="<?= isset($_GET['category_id']) && $_GET['category_id'] == $category['id'] ? 'active' : ''; ?>"><?= htmlspecialchars($category['name']) ?></a></li>
            <?php endwhile; ?>
        </ul>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
            <form method="GET" action="allgames.php">
                <label for="sort_by">Sort by: </label>
                <select name="sort_by" id="sort_by" onchange="this.form.submit()">
                    <option value="title" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'title') ? 'selected' : ''; ?>>Title</option>
                    <option value="genre" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'genre') ? 'selected' : ''; ?>>Genre</option>
                    <option value="release_date" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'release_date') ? 'selected' : ''; ?>>Release Date</option>
                    <option value="updated_at" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'updated_at') ? 'selected' : ''; ?>>Recently Updated</option>
                </select>
            </form>
        <?php endif; ?>

        <div id="menu">
            <ul>
                <li><a href="create.php">Add A Game</a></li>
            </ul>

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
                <?php
                    $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'default-page.php';?>
                <button class="back-btn" onclick="window.location.href='<?php echo $previousPage; ?>'">Go Back</button>
            </ul>
        </div>
    </div>
</body>
</html>
