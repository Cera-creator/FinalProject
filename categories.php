<?php
session_start();
require('connect.php');

$error_message = '';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'create') {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $check = $db->prepare("SELECT COUNT(*) FROM categories WHERE name = :name");
        $check->bindValue(':name', $name);
        $check->execute();
        if ($check->fetchColumn() == 0) {
            $query = "INSERT INTO categories (name) VALUES (:name)";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':name', $name);
            $stmt->execute();
        } else {
            $error_message = "That category already exists.";
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'update') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $check = $db->prepare("SELECT COUNT(*) FROM categories WHERE name = :name AND id != :id");
        $check->bindValue(':name', $name);
        $check->bindValue(':id', $id, PDO::PARAM_INT);
        $check->execute();
        if ($check->fetchColumn() == 0) {
            $query = "UPDATE categories SET name = :name WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindValue(':name', $name);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $error_message = "That name is already used by another category.";
        }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

    $checkQuery = "SELECT COUNT(*) FROM games WHERE category_id = :id";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();
    $count = $checkStmt->fetchColumn();

    if ($count == 0) {
        $query = "DELETE FROM categories WHERE id = :id";
        $stmt = $db->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    } else {
        $error_message = "Cannot delete: This category is assigned to $count game(s).";
    }
}


$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="cool.css">
</head>
<body>
    <div id="wrapper">
        <h2>Manage Categories</h2>
        <?php if (!empty($error_message)): ?>
    <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
<?php endif; ?>
        <form method="post">
            <input type="hidden" name="action" value="create">
            <input type="text" name="name" placeholder="New Category Name" required>
            <button type="submit">Add Category</button>
        </form>

        <ul>
            <?php foreach ($categories as $category): ?>
                <li>
                    <form method="post" style="display:inline-block;">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
                        <button type="submit">Update</button>
                    </form>
                    <form method="post" style="display:inline-block; margin-left: 10px;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $category['id'] ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this category?');">Delete</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>

        <p><a href="index.php">← Return to Home</a></p>
    </div>
</body>
</html>

