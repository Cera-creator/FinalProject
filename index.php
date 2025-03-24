<?php
    require('dbconnect.php');
    
    if ($_POST && !empty($_POST['title']) && !empty($_POST['description'])) {
        //  Sanitize user input to escape HTML entities and filter out dangerous characters.
        $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        
        //  Build the parameterized SQL query and bind to the above sanitized values.
        $query = "INSERT INTO games (title, description) VALUES (:title, :description)";
        $statement = $db->prepare($query);
        
        //  Bind values to the parameters
        $statement->bindValue(':title', $title);
        $statement->bindValue(':description', $description);
        
        //  Execute the INSERT.
        //  execute() will check for possible SQL injection and remove if necessary
        if($statement->execute()){
            echo "Success";
        }

    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>PDO Insert</title>
    <link rel="stylesheet" type="text/css" href="styles.css" />
</head>
<body>
    <form method="post" action="index.php">
        <label for="title">Title</label>
        <input id="title" name="title">
        <label for="description">Description</label>
        <input id="description" name="description">
        <input type="submit">
    </form>
</body>
</html>