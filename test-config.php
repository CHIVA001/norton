<?php
// Simple test to verify BASE_URL is defined
require_once __DIR__ . '/config/database.php';

echo "BASE_URL is defined: " . (defined('BASE_URL') ? "YES ✓" : "NO ✗") . "<br>";
echo "BASE_URL value: " . (defined('BASE_URL') ? BASE_URL : "NOT DEFINED") . "<br>";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : "NOT DEFINED") . "<br>";
echo "PHP Version: " . phpversion() . "<br>";

// Test if functions work
echo "Functions included: " . (function_exists('sanitize') ? "YES ✓" : "NO ✗") . "<br>";
?>