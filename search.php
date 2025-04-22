<?php
require ('connect.php');

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($search === '') {
    echo "Please enter a search term.";
    exit;
}

$sql = "
    SELECT id, title, description, release_date 
    FROM games 
    WHERE title LIKE ? 
       OR description LIKE ? 
       OR release_date LIKE ?
";

$searchTerm = "%$search%";
$stmt = $db->prepare($sql);
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$results = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search</title>
    <link rel="stylesheet" href="cool.css">
</head>
<body>
    <?php include('navbar.php'); ?>
<div id="wrapper">
    <div id="header">
        <h1><a href="index.php">SuperCoolTwitchName's Top Games</a></h1>
    </div>
    <div id="result">
<h2>Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>
<?php if (count($results) === 0): ?>
    <p>No matching games found.</p>
<?php else: ?>
    <ul>
        <?php
        $searchEscaped = preg_quote($search, '/');

        foreach ($results as $games):
            $highlightedTitle = $games['title']; 
            $highlightedDescription = $games['description'];
            $highlightedReleaseDate = $games['release_date'];

            if (preg_match("/($searchEscaped)/i", $highlightedTitle)) {
                $highlightedTitle = preg_replace("/($searchEscaped)/i", '<span class="highlight">$1</span>', $highlightedTitle);
            }
            if (preg_match("/($searchEscaped)/i", $highlightedDescription)) {
                $highlightedDescription = preg_replace("/($searchEscaped)/i", '<span class="highlight">$1</span>', $highlightedDescription);
            }
            if (preg_match("/($searchEscaped)/i", $highlightedReleaseDate)) {
                $highlightedReleaseDate = preg_replace("/($searchEscaped)/i", '<span class="highlight">$1</span>', $highlightedReleaseDate);
            }

        ?>
<li>
    <a href="fullgame.php?id=<?php echo $games['id']; ?>">
        <?php echo $highlightedTitle; ?>
    </a>
    <p><strong>Release:</strong> <?php echo $highlightedReleaseDate; ?></p>
    <p><strong>Description:</strong> <?php echo substr($highlightedDescription, 0, 500) . '...'; ?></p>
</li>

        <?php endforeach; ?>
    </ul>
<?php endif; ?>
</div>
</div>
    <footer>
    <p>Check out more: 
        <a href="https://yourlink1.com">Link 1</a> |
        <a href="https://yourlink2.com">Link 2</a>
    </p>
</footer>
</body>
</html>