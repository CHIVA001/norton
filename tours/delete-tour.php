<?php
/**
 * Delete Tour
 * Allows admins to delete a tour and its main image.
 */

require_once __DIR__ . '/../config/database.php';
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Check admin
if (!isAdmin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "admin/manage-tours.php");
    exit();
}

// Get tour and image path
$stmt = $conn->prepare("SELECT imageUrl FROM tours WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $imageUrl = $row['imageUrl'];
} else {
    $stmt->close();
    header("Location: " . BASE_URL . "admin/manage-tours.php");
    exit();
}
$stmt->close();

// Delete tour
$stmtDel = $conn->prepare("DELETE FROM tours WHERE id = ?");
$stmtDel->bind_param("i", $id);
if ($stmtDel->execute()) {
    // delete image file
    if (!empty($imageUrl) && file_exists(__DIR__ . '/../' . $imageUrl)) {
        @unlink(__DIR__ . '/../' . $imageUrl);
    }
    $_SESSION['message'] = 'Tour deleted successfully';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error deleting tour: ' . $conn->error;
    $_SESSION['message_type'] = 'danger';
}
$stmtDel->close();

header("Location: " . BASE_URL . "admin/manage-tours.php");
exit();
