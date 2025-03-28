<?php
session_start();

define('ADMIN_LOGIN', 'wally');
define('ADMIN_PASSWORD', 'mypass');

// Check if the HTTP Basic Auth credentials are correct
if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW']) 
    || ($_SERVER['PHP_AUTH_USER'] != ADMIN_LOGIN) 
    || ($_SERVER['PHP_AUTH_PW'] != ADMIN_PASSWORD)) { 
    // If not authenticated, send HTTP 401 Unauthorized response
    header('HTTP/1.1 401 Unauthorized'); 
    header('WWW-Authenticate: Basic realm="GamePage"'); 
    exit("Access Denied: Username and password required."); 
} else {
    // If the user is authenticated, store session data
    $_SESSION['user_id'] = 1; // You can set a unique user ID if needed
    $_SESSION['role'] = 'admin'; // Store 'admin' role for this session
}
?>
