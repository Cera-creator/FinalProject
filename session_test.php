<?php
// Start the session
session_start();

// Check if a session variable is set
if (isset($_SESSION['test'])) {
    echo "Session is working! The value of 'test' is: " . $_SESSION['test'];
} else {
    // If 'test' is not set, create it
    $_SESSION['test'] = 'Session is working!';
    echo "Session variable 'test' has been set.";
}
?>