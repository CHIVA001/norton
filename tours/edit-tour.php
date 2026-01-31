<?php
/**
 * Edit Tour Page
 * Allows admins to edit existing tours, including replacing the image.
 */

require_once __DIR__ . '/../config/database.php';
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$pageTitle = 'Edit Tour - Travel Agency';

$error = '';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: " . BASE_URL . "admin/manage-tours.php");
    exit();
}

// Fetch existing tour
$stmt = $conn->prepare("SELECT * FROM tours WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) {
    $stmt->close();
    header("Location: " . BASE_URL . "admin/manage-tours.php");
    exit();
}
$tour = $res->fetch_assoc();
$stmt->close();

$title = $tour['title'];
$description = $tour['description'];
$price = $tour['price'];
$duration = $tour['duration'];
$destination = $tour['destinationId'];
$imageUrl = $tour['imageUrl'] ?? '';
$destinationId = $destination;
$maxCapacity = $tour['maxCapacity'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize($_POST['tour_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $duration = intval($_POST['duration'] ?? 0);
    $destination = sanitize($_POST['destination'] ?? '');
    $maxCapacity = intval($_POST['max_capacity'] ?? 0);

    // Handle image upload (optional)
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
            $filename = uniqid('tour_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/tours/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $target = $uploadDir . $filename;
            if (move_uploaded_file($img['tmp_name'], $target)) {
                // delete old image
                if (!empty($imageUrl) && file_exists(__DIR__ . '/../' . $imageUrl)) {
                    @unlink(__DIR__ . '/../' . $imageUrl);
                }
                $imageUrl = 'uploads/tours/' . $filename;
            } else {
                $error = 'Failed to move uploaded image';
            }
        }
    }

    if (empty($error)) {
        // Resolve destinationId similar to add
        $destinationId = intval($destination) > 0 ? intval($destination) : 0;
        if ($destinationId === 0) {
            $stmtDest = $conn->prepare("SELECT id FROM destinations WHERE name = ? LIMIT 1");
            $stmtDest->bind_param("s", $destination);
            $stmtDest->execute();
            $resDest = $stmtDest->get_result();
            if ($resDest && $resDest->num_rows === 1) {
                $row = $resDest->fetch_assoc();
                $destinationId = $row['id'];
            } else {
                $country = 'Unknown';
                $stmtIns = $conn->prepare("INSERT INTO destinations (name, country) VALUES (?, ?)");
                $stmtIns->bind_param("ss", $destination, $country);
                if ($stmtIns->execute()) {
                    $destinationId = $stmtIns->insert_id;
                }
                $stmtIns->close();
            }
            $stmtDest->close();
        }

        $stmtUp = $conn->prepare("UPDATE tours SET title = ?, description = ?, price = ?, duration = ?, destinationId = ?, maxCapacity = ?, imageUrl = ? WHERE id = ?");
        $stmtUp->bind_param("ssdiiisi", $title, $description, $price, $duration, $destinationId, $maxCapacity, $imageUrl, $id);
        if ($stmtUp->execute()) {
            $_SESSION['message'] = 'Tour updated successfully!';
            $_SESSION['message_type'] = 'success';
            header("Location: " . BASE_URL . "admin/manage-tours.php");
            exit();
        } else {
            $error = 'Error updating tour: ' . $conn->error;
        }
        $stmtUp->close();
    }
}

// Fetch destinations for select
$stmt = $conn->prepare("SELECT id, name FROM destinations ORDER BY name ASC");
$stmt->execute();
$destinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Edit Tour</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" novalidate>
                    <div class="mb-3">
                        <label for="tour_name" class="form-label">Tour Name *</label>
                        <input type="text" class="form-control" id="tour_name" name="tour_name"
                            value="<?php echo htmlspecialchars($title); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                            required><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="price" class="form-label">Price *</label>
                        <input type="number" step=".01" min="0" class="form-control" id="price" name="price"
                            value="<?php echo htmlspecialchars($price); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (days) *</label>
                        <input type="number" class="form-control" id="duration" name="duration"
                            value="<?php echo htmlspecialchars($duration); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="destination" class="form-label">Destination *</label>
                        <select class="form-select" id="destination" name="destination" required>
                            <option value="">Select Destination</option>
                            <?php foreach ($destinations as $d): ?>
                                <option value="<?php echo $d['id']; ?>" <?php echo (intval($destination) === intval($d['id'])) ? 'selected' : ''; ?>><?php echo htmlspecialchars($d['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="max_capacity" class="form-label">Max Capacity *</label>
                        <input type="number" class="form-control" id="max_capacity" name="max_capacity" min="1"
                            value="<?php echo htmlspecialchars($maxCapacity); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="image" class="form-label">Image (optional)</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*">
                        <?php if (!empty($imageUrl)): ?>
                            <div class="mt-2"><img src="<?php echo htmlspecialchars(BASE_URL . $imageUrl); ?>"
                                    alt="tour image" style="max-width:200px"></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Save Changes</button>
                </form>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>