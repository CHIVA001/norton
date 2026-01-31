<?php
/**
 * Admin route shim for Add Tour
 * Verifies admin access then redirects to the actual add form in `tours/add-tour.php`.
 */

require_once __DIR__ . '/../config/database.php';
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Only allow admins
if (!isAdmin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Redirect to the tours add page
header("Location: " . BASE_URL . "tours/add-tour.php");
exit();
