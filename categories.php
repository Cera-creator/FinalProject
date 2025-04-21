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

    $nullifyQuery = "UPDATE games SET category_id = NULL WHERE category_id = :id";
    $nullifyStmt = $db->prepare($nullifyQuery);
    $nullifyStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $nullifyStmt->execute();

    $deleteQuery = "DELETE FROM categories WHERE id = :id";
    $deleteStmt = $db->prepare($deleteQuery);
    $deleteStmt->bindValue(':id', $id, PDO::PARAM_INT);
    $deleteStmt->execute();
}

$categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cool.css">
    <title>Manage Categories</title>
</head>
<body>
    <?php include('navbar.php'); ?>
    <div id="wrapper">
        <h2>Manage Categories</h2>

        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <?php if (!empty($categories)): ?>
            <table>
                <tr>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($categories as $category): ?>
                    <tr>
    <td>
        <input type="text" form="form-<?= $category['id'] ?>" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
    </td>
    <td>
        <script>
function confirmAction(event) {
    const clickedButton = event.submitter;
    if (clickedButton.name === 'action' && clickedButton.value === 'delete') {
        return confirm('Are you sure you want to delete this category?');
    }
    return true;
}
</script>
        <form id="form-<?= $category['id'] ?>" method="post" onsubmit="return confirmAction(event);">
            <input type="hidden" name="id" value="<?= $category['id'] ?>">
            <button type="submit" name="action" value="update">Update</button>
            <button type="submit" name="action" value="delete">Delete</button>
        </form>
    </td>
</tr>
                <?php endforeach; ?>
            </table>

            <form method="post" style="margin-top: 20px;">
                <input type="hidden" name="action" value="create">
                <input type="text" name="name" placeholder="New Category Name" required>
                <button type="submit">Add Category</button>
            </form>
        <?php else: ?>
            <p>No categories available.</p>
        <?php endif; ?>
    </div>
</body>
</html>

