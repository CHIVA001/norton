<?php
/**
 * Clear PHP OpCache
 */

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OpCache cleared successfully!";
} else {
    echo "OpCache is not enabled or available.";
}

// Try to clear realpath cache
clearstatcache(true);
echo "\nRealpath cache cleared!";

echo "\nPlease restart Apache and try again.";
?>