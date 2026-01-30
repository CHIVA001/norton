<?php
/**
 * Admin Authentication Check Middleware
 * Verifies admin session and redirects if not authenticated or not admin
 */

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    $_SESSION['message'] = 'Please login as admin to access this page';
    $_SESSION['message_type'] = 'warning';
    header("Location: " . BASE_URL . "admin/admin-login.php");
    exit();
}

if ($_SESSION['user_role'] !== 'admin') {
    $_SESSION['message'] = 'You do not have permission to access this page';
    $_SESSION['message_type'] = 'danger';
    header("Location: " . BASE_URL . "index.php");
    exit();
}

?>