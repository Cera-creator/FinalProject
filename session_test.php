<?php
session_start();

if (isset($_SESSION['test'])) {
    echo "Session is working! The value of 'test' is: " . $_SESSION['test'];
} else {
    $_SESSION['test'] = 'Session is working!';
    echo "Session variable 'test' has been set.";
}
?>