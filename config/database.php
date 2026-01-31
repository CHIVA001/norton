<?php
/**
 * Database Configuration File
 * MySQLi Connection Handler with Error Handling
 */

// Base URL for the application - MUST be defined first before any redirects
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/travel_agency_1/');
}

// Database credentials
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'travel_db');
define('DB_PORT', 3307);

// Create connection using MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, DB_PORT);

// Check connection
if ($conn->connect_error) {
    // Log error for debugging
    error_log("Database Connection Failed: " . $conn->connect_error);

    // Display user-friendly error message
    die("Connection to database failed. Please try again later.");
}

// Set charset to utf8mb4
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
}

// Enable error reporting for MySQLi
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set session timezone
date_default_timezone_set('UTC');

?>