<?php
require('connect.php');
require('authenticate.php');
require __DIR__ . '/php-image-resize-master/lib/ImageResize.php';
require __DIR__ . '/php-image-resize-master/lib/ImageResizeException.php';

use \Gumlet\ImageResize;

$error_message = '';

function file_upload_path($original_filename, $upload_subfolder_name = 'uploads') {
    $current_folder = dirname(__FILE__);
    $path_segments = [$current_folder, $upload_subfolder_name, basename($original_filename)];
    return join(DIRECTORY_SEPARATOR, $path_segments);
}

$query_genre = "SELECT * FROM genre ORDER BY name ASC"; 
$statement_genre = $db->prepare($query_genre);
$statement_genre->execute();
$genre = $statement_genre->fetchAll(PDO::FETCH_ASSOC);

$query_category = "SELECT * FROM categories ORDER BY name ASC";  
$statement_category = $db->prepare($query_category);
$statement_category->execute();
$categories = $statement_category->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $genre_id = filter_input(INPUT_POST, 'genre_id', FILTER_SANITIZE_NUMBER_INT); // genre_id, not genre
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_SANITIZE_NUMBER_INT);
    $release_date = filter_input(INPUT_POST, 'release_date', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (empty($title) || empty($description) || empty($genre_id) || empty($release_date)) {
        $error_message = 'All fields are required.';
    } else {
        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image_filename = $_FILES['image']['name'];
            $temporary_image_path = $_FILES['image']['tmp_name'];

            $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
            $file_extension = strtolower(pathinfo($image_filename, PATHINFO_EXTENSION));
            $actual_mime_type = mime_content_type($temporary_image_path);

            if (!in_array($actual_mime_type, $allowed_mime_types) || !in_array($file_extension, ['gif', 'jpg', 'jpeg', 'png'])) {
                $error_message = 'Invalid file type. Please upload a JPG, PNG, or GIF image.';
            }
        }

        if (empty($error_message)) {

            $created_at = date('Y-m-d H:i:s');
            $updated_at = date('Y-m-d H:i:s');

            $query = "INSERT INTO games (title, description, genre_id, category_id, release_date, created_at, updated_at) 
                      VALUES (:title, :description, :genre_id, :category_id, :release_date, :created_at, :updated_at)";
            $statement = $db->prepare($query);
            $statement->bindValue(':title', $title);
            $statement->bindValue(':description', $description);
            $statement->bindValue(':genre_id', $genre_id, PDO::PARAM_INT);
            $statement->bindValue(':category_id', $category_id, PDO::PARAM_INT);
            $statement->bindValue(':release_date', $release_date);
            $statement->bindValue(':created_at', $created_at);
            $statement->bindValue(':updated_at', $updated_at);
            $statement->execute();

            $game_id = $db->lastInsertId();

            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                try {
                    $image = new ImageResize($temporary_image_path);
                    $image->resizeToWidth(400);

                    $image_medium_filename = pathinfo($image_filename, PATHINFO_FILENAME) . '_medium.' . $file_extension;
                    $image_medium_path = file_upload_path($image_medium_filename);
                    $image->save($image_medium_path);

                    $medium_image_path = 'uploads/' . $image_medium_filename;

                    $query_image_medium = "INSERT INTO images (image_path, game_id, created_at) 
                                           VALUES (:image_path, :game_id, NOW())";
                    $statement_image_medium = $db->prepare($query_image_medium);
                    $statement_image_medium->bindValue(':image_path', $medium_image_path);
                    $statement_image_medium->bindValue(':game_id', $game_id);
                    $statement_image_medium->execute();
                } catch (Exception $e) {
                    $error_message = 'Error resizing image: ' . $e->getMessage();
                }
            }

            if (empty($error_message)) {
                header('Location: index.php');
                exit;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="cool.css">
    <title>Add A Game</title>
</head>
<body>
    <div id="wrapper">
        <div id="menu">    
            <h1><a href="index.php">Return Home</a></h1>
        </div>

        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>

        <form method="post" action="create.php" enctype="multipart/form-data">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" required />

            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>

            <label for="genre_id">Genre</label>
            <select id="genre_id" name="genre_id">
                <?php foreach ($genre as $genre_option): ?>
                    <option value="<?= $genre_option['id'] ?>" <?= (isset($genre_id) && $genre_id == $genre_option['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($genre_option['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="category_id">Category</label>
            <select id="category_id" name="category_id">
                <?php foreach ($categories as $category_option): ?>
                    <option value="<?= $category_option['id'] ?>" <?= (isset($category_id) && $category_id == $category_option['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($category_option['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="release_date">Release Date</label>
            <input type="date" name="release_date" required />

            <label for="image">Image (optional)</label>
            <input type="file" id="image" name="image" accept="image/*" />

            <input type="submit" value="Submit">
        </form>
        <?php
            $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'default-page.php';?>
        <button class="back-btn" onclick="window.location.href='<?php echo $previousPage; ?>'">Go Back</button>
    </div>
</body>
</html>
