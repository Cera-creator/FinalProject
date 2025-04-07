<?php

/*******w******** 
    
    Name: Cera McEwan
    Date: 2025-03-20
    Description: Form to allow file uploads and resizing.

****************/
require __DIR__ . '/php-image-resize-master/lib/ImageResize.php';
require __DIR__ . '/php-image-resize-master/lib/ImageResizeException.php';

function file_upload_path($original_filename, $upload_subfolder_name = 'uploads') {
    $current_folder = dirname(__FILE__);
    $path_segments = [$current_folder, $upload_subfolder_name, basename($original_filename)];
    return join(DIRECTORY_SEPARATOR, $path_segments);
}

$image_upload_detected = isset($_FILES['image']) && ($_FILES['image']['error'] === 0);

if ($image_upload_detected) {
    $image_filename       = $_FILES['image']['name'];
    $temporary_image_path = $_FILES['image']['tmp_name'];
    $new_image_path       = file_upload_path($image_filename); 

function file_is_an_image($temporary_path, $new_path) {
    $allowed_mime_types = ['image/gif', 'image/jpeg', 'image/png'];
    $allowed_file_extensions = ['gif', 'jpg', 'jpeg', 'png'];

    $actual_file_extension = strtolower(pathinfo($new_path, PATHINFO_EXTENSION)); 
    $actual_mime_type = mime_content_type($temporary_path);

    $file_extension_is_valid = in_array($actual_file_extension, $allowed_file_extensions);
    
    $mime_type_is_valid = in_array($actual_mime_type, $allowed_mime_types);

    return $file_extension_is_valid && $mime_type_is_valid;
}

$image_upload_detected = isset($_FILES['image']) && ($_FILES['image']['error'] === 0);

if ($image_upload_detected) {
    $image_filename       = $_FILES['image']['name'];
    $temporary_image_path = $_FILES['image']['tmp_name'];
    $new_image_path       = file_upload_path($image_filename);

    if (file_is_an_image($temporary_image_path, $new_image_path)) {
        move_uploaded_file($temporary_image_path, $new_image_path);

        $image = new \Gumlet\ImageResize($new_image_path);
        $image_medium_path = file_upload_path(pathinfo($image_filename, PATHINFO_FILENAME) . '_medium.' . pathinfo($image_filename, PATHINFO_EXTENSION));
        $image->resizeToWidth(400);
        $image->save($image_medium_path);

        $image_thumbnail_path = file_upload_path(pathinfo($image_filename, PATHINFO_FILENAME) . '_thumbnail.' . pathinfo($image_filename, PATHINFO_EXTENSION));
        $image->resizeToWidth(50);
        $image->save($image_thumbnail_path);

        $image_path = 'uploads/' . basename($new_image_path); 

        $query = "INSERT INTO images (image_path, game_id, created_at) VALUES (:image_path, :game_id, NOW())";
        $statement = $db->prepare($query);
        $statement->bindValue(':image_path', $image_path);
        $statement->bindValue(':game_id', $game_id); 

        if ($statement->execute()) {
            header('Location: index.php');
            exit;
        } else {
            echo "Error inserting image into database.";
        }
} else {
    if (isset($_FILES['image']['error']) && $_FILES['image']['error'] != 0) {
        echo "Error uploading the file. Please try again.";
    }
}
