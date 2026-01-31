<?php
/**
 * Add Destination Page
 * Allows admins to add new destinations with image
 */

require_once __DIR__ . '/../config/database.php';
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$pageTitle = 'Add Destination - Travel Agency';
$error = '';
$name = '';
$country = '';
$description = '';
$bestTime = '';
$imageUrl = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $country = sanitize($_POST['country'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $bestTime = sanitize($_POST['best_time'] ?? '');

    // Handle image upload
    if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $img = $_FILES['image'];
        $allowed = ['image/jpeg', 'image/png', 'image/gif'];
        if ($img['error'] !== UPLOAD_ERR_OK) {
            $error = 'Error uploading image';
        } elseif (!in_array(mime_content_type($img['tmp_name']), $allowed, true)) {
            $error = 'Invalid image type. Allowed: JPG, PNG, GIF';
        } elseif ($img['size'] > 4 * 1024 * 1024) {
            $error = 'Image size must be under 4MB';
        } else {
            $ext = pathinfo($img['name'], PATHINFO_EXTENSION);
            $filename = uniqid('dest_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/destinations/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $target = $uploadDir . $filename;
            if (move_uploaded_file($img['tmp_name'], $target)) {
                $imageUrl = 'uploads/destinations/' . $filename;
            } else {
                $error = 'Failed to move uploaded image';
            }
        }
    }

    if (empty($name) || empty($country)) {
        $error = 'Name and country are required';
    }

    if (empty($error)) {
        $stmt = $conn->prepare("INSERT INTO destinations (name, country, description, imageUrl, bestTimeToVisit) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $name, $country, $description, $imageUrl, $bestTime);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Destination added successfully!';
            $_SESSION['message_type'] = 'success';
            header("Location: " . BASE_URL . "admin/manage-destinations.php");
            exit();
        } else {
            $error = 'Error adding destination: ' . $conn->error;
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Add Destination</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" novalidate>
                    <div class="mb-3">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control"
                            value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Country *</label>
                        <input type="text" name="country" class="form-control"
                            value="<?php echo htmlspecialchars($country); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Best time to visit</label>
                        <input type="text" name="best_time" class="form-control"
                            value="<?php echo htmlspecialchars($bestTime); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control"
                            rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Image (optional)</label>
                        <input type="file" name="image" class="form-control" accept="image/*">
                    </div>

                    <button class="btn btn-primary w-100">Add Destination</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>