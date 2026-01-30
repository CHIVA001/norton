<?php
/**
 * Authentication Check Middleware
 * Verifies user session and redirects if not authenticated
 */

if (!isset($_SESSION)) {
    session_start();
}

if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/database.php';
}

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please login to access this page';
    $_SESSION['message_type'] = 'warning';
    header("Location: " . BASE_URL . "users/user-login.php");
    exit();
}

?>