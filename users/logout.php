<?php
/**
 * User Logout
 * Destroys session and logs user out
 */

require_once __DIR__ . '/../config/database.php';
session_start();

// Destroy session
session_destroy();

// Redirect to home
header("Location: " . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/'));
exit();

?>