<?php
/**
 * Add to Wishlist
 * AJAX handler for adding/removing tours from wishlist
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in
if (!isSessionValid()) {
    echo json_encode(['success' => false, 'error' => 'Please login to use wishlist']);
    exit();
}

$userId = getCurrentUserId();
$tourId = isset($_POST['tour_id']) ? intval($_POST['tour_id']) : 0;
$action = isset($_POST['action']) ? sanitize($_POST['action']) : '';

if ($tourId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid tour']);
    exit();
}

// Check if tour exists
$stmt = $conn->prepare("SELECT id FROM tours WHERE id = ?");
$stmt->bind_param("i", $tourId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Tour not found']);
    $stmt->close();
    exit();
}
$stmt->close();

// Check if already in wishlist
$stmt = $conn->prepare("SELECT id FROM wishlist WHERE userId = ? AND tourId = ?");
$stmt->bind_param("ii", $userId, $tourId);
$stmt->execute();
$exists = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($action === 'toggle') {
    if ($exists) {
        // Remove from wishlist
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE userId = ? AND tourId = ?");
        $stmt->bind_param("ii", $userId, $tourId);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => true, 'added' => false, 'message' => 'Removed from wishlist']);
    } else {
        // Add to wishlist
        $stmt = $conn->prepare("INSERT INTO wishlist (userId, tourId) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $tourId);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'added' => true, 'message' => 'Added to wishlist']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update wishlist']);
        }
        $stmt->close();
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

?>