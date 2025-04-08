<?php
require('connect.php');
require('authenticate.php');
require __DIR__ . '/php-image-resize-master/lib/ImageResize.php';
require __DIR__ . '/php-image-resize-master/lib/ImageResizeException.php';

function file_is_an_image($temporary_path, $new_path) {
    $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
    $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];

    $actual_file_extension = strtolower(pathinfo($new_path, PATHINFO_EXTENSION)); 
    $actual_mime_type = mime_content_type($temporary_path);

    $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
    $mime_type_is_valid = in_array($actual_mime_type, $allowed_mime_types);

    return $file_extension_is_valid && $mime_type_is_valid;
}

function resize_image($image_path, $max_width, $max_height) {
    list($width, $height, $image_type) = getimagesize($image_path);  
    $aspect_ratio = $width / $height;

    if ($width > $height) {
        $new_width = $max_width;
        $new_height = $max_width / $aspect_ratio;
    } else {
        $new_height = $max_height;
        $new_width = $max_height * $aspect_ratio;
    }

    switch ($image_type) {
        case IMAGETYPE_JPEG:
            $src = imagecreatefromjpeg($image_path);
            break;
        case IMAGETYPE_PNG:
            $src = imagecreatefrompng($image_path);
            break;
        case IMAGETYPE_GIF:
            $src = imagecreatefromgif($image_path);
            break;
        default:
            return false;
    }

    $dst = imagecreatetruecolor($new_width, $new_height);

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    $resized_image_path = 'uploads/' . pathinfo($image_path, PATHINFO_FILENAME) . '_medium.' . pathinfo($image_path, PATHINFO_EXTENSION);

    switch ($image_type) {
        case IMAGETYPE_JPEG:
            imagejpeg($dst, $resized_image_path);
            break;
        case IMAGETYPE_PNG:
            imagepng($dst, $resized_image_path);
            break;
        case IMAGETYPE_GIF:
            imagegif($dst, $resized_image_path);
            break;
    }

    imagedestroy($src);
    imagedestroy($dst);

    return $resized_image_path;
}

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
    
    $image_path = null;

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
        $new_image_path = 'uploads/' . basename($image_filename);

        if (!file_is_an_image($temporary_image_path, $new_image_path)) {
            header("Location: index.php?id={$id}");
            exit;
        }

        move_uploaded_file($temporary_image_path, $new_image_path);
        
        $medium_image_path = resize_image($new_image_path, 300, 300); 

        unlink($new_image_path);

        $query_image = "INSERT INTO images (image_path, game_id) VALUES (:image_path, :game_id)
                        ON DUPLICATE KEY UPDATE image_path = :image_path";
        $statement_image = $db->prepare($query_image);
        $statement_image->bindValue(':image_path', $medium_image_path);
        $statement_image->bindValue(':game_id', $id, PDO::PARAM_INT);
        $statement_image->execute();
    }

    if (!$image_path) {
        $query_image = "SELECT * FROM images WHERE game_id = :game_id LIMIT 1";
        $statement_image = $db->prepare($query_image);
        $statement_image->bindValue(':game_id', $id, PDO::PARAM_INT);
        $statement_image->execute();
        $image = $statement_image->fetch(PDO::FETCH_ASSOC);
        $image_path = $image ? $image['image_path'] : null;
    }

    $query = "UPDATE games SET title = :title, description = :description, genre = :genre, updated_at = :updated_at WHERE id = :id";
    $statement = $db->prepare($query);
    $statement->bindValue(':title', $title);
    $statement->bindValue(':description', $description);  
    $statement->bindValue(':genre', $genre);
    $statement->bindValue(':updated_at', $updated_at);
    $statement->bindValue(':id', $id, PDO::PARAM_INT);

    if ($statement->execute()) {
        if ($image_path) {
            $query_image = "INSERT INTO images (image_path, game_id) VALUES (:image_path, :game_id)
                            ON DUPLICATE KEY UPDATE image_path = :image_path";
            $statement_image = $db->prepare($query_image);
            $statement_image->bindValue(':image_path', $image_path);
            $statement_image->bindValue(':game_id', $id, PDO::PARAM_INT);
            $statement_image->execute();
        }

        header("Location: index.php?id={$id}");
        exit;
    } else {
        echo "Error: " . implode(", ", $statement->errorInfo());
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
        <?php if ($id): ?>
            <form action="edit.php" method="post" enctype="multipart/form-data">
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
        <?php endif ?>
        <?php
            $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'default-page.php';?>
        <button class="back-btn" onclick="window.location.href='<?php echo $previousPage; ?>'">Go Back</button>
    </div>
</body>
</html>
