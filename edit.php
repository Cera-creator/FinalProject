<?php
require('connect.php');
require('authenticate.php');
include('navbar.php');
require __DIR__ . '/php-image-resize-master/lib/ImageResize.php';
require __DIR__ . '/php-image-resize-master/lib/ImageResizeException.php';

use \Gumlet\ImageResize;

$error_message = '';

function file_upload_path($original_filename, $upload_subfolder_name = 'uploads') {
    $current_folder = dirname(__FILE__);
    $path_segments = [$current_folder, $upload_subfolder_name, basename($original_filename)];
    return join(DIRECTORY_SEPARATOR, $path_segments);
}

$query_genre = "SELECT DISTINCT id, name FROM genre";
$statement_genre = $db->prepare($query_genre);
$statement_genre->execute();
$genre = $statement_genre->fetchAll(PDO::FETCH_ASSOC);

$query_categories = "SELECT * FROM categories ORDER BY name ASC";
$statement_categories = $db->prepare($query_categories);
$statement_categories->execute();
$categories = $statement_categories->fetchAll(PDO::FETCH_ASSOC);

if ($_POST && isset($_POST['title'], $_POST['description'], $_POST['id'], $_POST['genre'], $_POST['update'])) {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
    $genre_id = filter_input(INPUT_POST, 'genre_id', FILTER_SANITIZE_NUMBER_INT);

    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);

    $query_genre_name = "SELECT name FROM genre WHERE id = :genre_id";
    $statement_genre_name = $db->prepare($query_genre_name);
    $statement_genre_name->bindValue(':genre_id', $genre_id, PDO::PARAM_INT);
    $statement_genre_name->execute();
    $genre_data = $statement_genre_name->fetch(PDO::FETCH_ASSOC);
    $genre_name = $genre_data ? $genre_data['name'] : null;


    if (empty($title) || empty($description)) {
        $error_message = 'Title and description are required.';
    }
    if (empty($category_id)) {
        $error_message = 'Please select a category.';
    }
    if (empty($genre_name)) {
        $error_message = 'Please select a valid genre.';
    }

    $timezone = new DateTimeZone('America/Winnipeg');
    $datetime = new DateTime('now', $timezone);
    $updated_at = $datetime->format('Y-m-d H:i:s');

    if (isset($_POST['delete_image']) && $_POST['delete_image'] == '1') {
        $query_image = "SELECT * FROM images WHERE game_id = :game_id LIMIT 1";
        $statement_image = $db->prepare($query_image);
        $statement_image->bindValue(':game_id', $id, PDO::PARAM_INT);
        $statement_image->execute();
        $image = $statement_image->fetch(PDO::FETCH_ASSOC);

        if ($image && file_exists($image['image_path'])) {
            unlink($image['image_path']);
        }

        $query_delete_image = "DELETE FROM images WHERE game_id = :game_id";
        $statement_delete_image = $db->prepare($query_delete_image);
        $statement_delete_image->bindValue(':game_id', $id, PDO::PARAM_INT);
        $statement_delete_image->execute();
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $image_filename = $_FILES['image']['name'];
        $temporary_image_path = $_FILES['image']['tmp_name'];

        $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
        $actual_mime_type = mime_content_type($temporary_image_path);
        $file_extension = strtolower(pathinfo($image_filename, PATHINFO_EXTENSION));

        if (in_array($actual_mime_type, $allowed_mime_types) && in_array($file_extension, ['gif', 'jpg', 'jpeg', 'png'])) {
            try {
                $image = new ImageResize($temporary_image_path);
                $image->resizeToWidth(400);

                $image_medium_filename = pathinfo($image_filename, PATHINFO_FILENAME) . '_medium.' . $file_extension;
                $image_medium_path = file_upload_path($image_medium_filename);

                $image->save($image_medium_path);

                $medium_image_path = 'uploads/' . $image_medium_filename;

                $query_image = "INSERT INTO images (image_path, game_id) 
                                VALUES (:image_path, :game_id)
                                ON DUPLICATE KEY UPDATE image_path = :image_path";
                $statement_image = $db->prepare($query_image);
                $statement_image->bindValue(':image_path', $medium_image_path);
                $statement_image->bindValue(':game_id', $id, PDO::PARAM_INT);
                $statement_image->execute();
            } catch (Exception $e) {
                $error_message = 'Error resizing image: ' . $e->getMessage();
            }
        } else {
            $error_message = 'Invalid file type. Please upload a JPG, PNG, or GIF.';
        }
    }

    if (empty($error_message)) {
$query = "UPDATE games 
          SET title = :title, description = :description, genre_id = :genre_id, category_id = :category_id, updated_at = :updated_at
          WHERE id = :id";

$statement = $db->prepare($query);
$statement->bindValue(':title', $title);
$statement->bindValue(':description', $description);  
$statement->bindValue(':genre_id', $genre_id, PDO::PARAM_INT);
$statement->bindValue(':category_id', $category_id, PDO::PARAM_INT);
$statement->bindValue(':updated_at', $updated_at);
$statement->bindValue(':id', $id, PDO::PARAM_INT);


        if ($statement->execute()) {
            header("Location: index.php?id={$id}");
            exit;
        } else {
            $error_message = "Database error: " . implode(", ", $statement->errorInfo());
        }
    }
}

if (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    $query = "SELECT * FROM games WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);
    $statement->execute();
    $row = $statement->fetch();

    $query_image = "SELECT * FROM images WHERE game_id = :game_id LIMIT 1";
    $statement_image = $db->prepare($query_image);
    $statement_image->bindValue(':game_id', $id, PDO::PARAM_INT);
    $statement_image->execute();
    $image = $statement_image->fetch(PDO::FETCH_ASSOC);

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
    <link rel="stylesheet" href="cool.css">
    <title>Edit Game</title>
</head>
<body>
    <div id="wrapper">
        <div id="menu">    
            <h1><a href="index.php">Return Home</a></h1>
        </div>

        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <?php if ($id): ?>
            <form action="edit.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $row['id'] ?>">

                <label for="title">Title</label>
                <input id="title" name="title" value="<?= isset($title) ? htmlspecialchars($title) : htmlspecialchars($row['title']) ?>" />

                <label for="description">Description</label>
                <textarea id="description" name="description"><?= isset($description) ? htmlspecialchars($description) : htmlspecialchars($row['description']) ?></textarea> 

<label for="genre">Genre</label>
<select id="genre_id" name="genre_id">
    <?php foreach ($genre as $genre_option): ?>

        <option value="<?= $genre_option['id'] ?>" <?= (isset($genre_id) && $genre_id == $genre_option['id']) || (isset($row['genre_id']) && $row['genre_id'] == $genre_option['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($genre_option['name']) ?>
        </option>
    <?php endforeach; ?>
</select>
                <label for="category">Category</label>
                <select id="category" name="category_id">
                    <?php foreach ($categories as $category_option): ?>
                        <option value="<?= $category_option['id'] ?>" <?= (isset($category_id) && $category_id == $category_option
['id']) ? 'selected' : '' ?>> <?= htmlspecialchars($category_option['name']) ?> </option> <?php endforeach; ?> </select>
            <label for="image">New Image (optional)</label>
            <input type="file" id="image" name="image" accept="image/*" />

            <?php if ($image): ?>
                <div class="current-image">
                    <p>Current Image:</p>
                    <img src="<?= $image['image_path'] ?>" alt="Current Image" class="medium-image">
                </div>

                <label for="delete_image">Delete Image</label>
                <input type="checkbox" id="delete_image" name="delete_image" value="1" />
            <?php else: ?>
                <p>No image available.</p>
            <?php endif; ?>

            <input type="submit" name="update" value="Update" />
            <input type="submit" name="delete" value="Delete" onclick="return confirm('Are you sure you wish to delete this post?')" />
        </form>
    <?php endif; ?>

    <?php
        $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'default-page.php';?>
    <button class="back-btn" onclick="window.location.href='<?php echo $previousPage; ?>'">Go Back</button>
</div>
