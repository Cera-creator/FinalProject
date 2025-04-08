<?php
require('connect.php');
require('authenticate.php');
require __DIR__ . '/php-image-resize-master/lib/ImageResize.php';
require __DIR__ . '/php-image-resize-master/lib/ImageResizeException.php';

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

        $game_id = $db->lastInsertId();

        if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
            $image_filename = $_FILES['image']['name'];
            $temporary_image_path = $_FILES['image']['tmp_name'];

            function file_upload_path($original_filename, $upload_subfolder_name = 'uploads') {
                $current_folder = dirname(__FILE__);
                $path_segments = [$current_folder, $upload_subfolder_name, basename($original_filename)];
                return join(DIRECTORY_SEPARATOR, $path_segments);
            }

            $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
            $actual_mime_type = mime_content_type($temporary_image_path);
            $file_extension = pathinfo($image_filename, PATHINFO_EXTENSION);

            if (in_array($actual_mime_type, $allowed_mime_types) && in_array($file_extension, ['gif', 'jpg', 'jpeg', 'png'])) {
                try {
                    $image = new \Gumlet\ImageResize($temporary_image_path);
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
                    echo "Error resizing image: " . $e->getMessage();
                }
            }
        }

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
    <link rel="stylesheet" href="cool.css">
    <title>Add A Game</title>
</head>
<body>
    <div id="wrapper">
        <div id="menu">    
            <h1><a href="index.php">Return Home</a></h1>
        </div>
        <form method="post" action="create.php" enctype="multipart/form-data">
            <label for="title">Title</label>
            <input id="title" name="title" type="text" required />

            <label for="description">Description</label>
            <textarea id="description" name="description" required></textarea>

            <label for="genre">Genre</label>
            <select id="genre" name="genre" required>
                <?php foreach ($genres as $genre_option): ?>
                    <option value="<?= htmlspecialchars($genre_option['genre']) ?>"><?= htmlspecialchars($genre_option['genre']) ?></option>
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
