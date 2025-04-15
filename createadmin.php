<?php
// create_admin.php - run this ONCE and then delete it

$password = 'mypass'; // change this to your desired admin password
$hashed = password_hash($password, PASSWORD_DEFAULT);
echo "Hashed password: " . $hashed;
?>