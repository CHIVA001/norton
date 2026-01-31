<?php
/**
 * Delete Destination
 */

require_once __DIR__ . '/../config/database.php';
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "admin/manage-destinations.php");
    exit();
}

// Get destination image
$stmt = $conn->prepare("SELECT imageUrl FROM destinations WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $imageUrl = $row['imageUrl'];
} else {
    $stmt->close();
    header("Location: " . BASE_URL . "admin/manage-destinations.php");
    exit();
}
$stmt->close();

// Delete destination (tours will cascade)
$stmtDel = $conn->prepare("DELETE FROM destinations WHERE id = ?");
$stmtDel->bind_param("i", $id);
if ($stmtDel->execute()) {
    if (!empty($imageUrl) && file_exists(__DIR__ . '/../' . $imageUrl)) {
        @unlink(__DIR__ . '/../' . $imageUrl);
    }
    $_SESSION['message'] = 'Destination deleted';
    $_SESSION['message_type'] = 'success';
} else {
    $_SESSION['message'] = 'Error deleting destination: ' . $conn->error;
    $_SESSION['message_type'] = 'danger';
}
$stmtDel->close();

header("Location: " . BASE_URL . "admin/manage-destinations.php");
exit();
