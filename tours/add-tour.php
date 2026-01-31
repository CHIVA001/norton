<?php
/**
 * Add Tour Page
 * Allows admins to add new tours
 */

require_once __DIR__ . '/../config/database.php';
session_start();
require_once __DIR__ . '/../includes/functions.php';

// Check if user is admin
if (!isAdmin()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$pageTitle = 'Add Tour - Travel Agency';

$error = '';
$success = '';
// Initialize form variables to avoid undefined notices and allow repopulation
$tour_name = '';
$description = '';
$price = 0;
$duration = '';
$destination = '';
// image path stored in DB
$imageUrl = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tour_name = sanitize($_POST['tour_name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $duration = intval($_POST['duration'] ?? 0);
    $destination = sanitize($_POST['destination'] ?? '');

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
            $filename = uniqid('tour_', true) . '.' . $ext;
            $uploadDir = __DIR__ . '/../uploads/tours/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $target = $uploadDir . $filename;
            if (move_uploaded_file($img['tmp_name'], $target)) {
                $imageUrl = 'uploads/tours/' . $filename;
            } else {
                $error = 'Failed to move uploaded image';
            }
        }
    }

    if (empty($tour_name) || empty($description) || empty($price) || empty($duration) || empty($destination)) {
        $error = 'All fields are required';
    } else {
        // Resolve destination to destinationId (use existing or insert new)
        $destinationId = 0;
        if (is_numeric($destination) && intval($destination) > 0) {
            $destinationId = intval($destination);
        } else {
            $stmtDest = $conn->prepare("SELECT id FROM destinations WHERE name = ? LIMIT 1");
            $stmtDest->bind_param("s", $destination);
            $stmtDest->execute();
            $resDest = $stmtDest->get_result();
            if ($resDest && $resDest->num_rows === 1) {
                $rowDest = $resDest->fetch_assoc();
                $destinationId = $rowDest['id'];
            }
            $stmtDest->close();

            if ($destinationId === 0) {
                // Insert a minimal destination record (country required by schema)
                $country = 'Unknown';
                $stmtInsDest = $conn->prepare("INSERT INTO destinations (name, country) VALUES (?, ?)");
                $stmtInsDest->bind_param("ss", $destination, $country);
                if ($stmtInsDest->execute()) {
                    $destinationId = $stmtInsDest->insert_id;
                }
                $stmtInsDest->close();
            }
        }

        // Insert tour into database using schema column names (include imageUrl)
        $stmt = $conn->prepare("INSERT INTO tours (title, description, price, duration, destinationId, imageUrl) VALUES (?, ?, ?, ?, ?, ?)");
        // types: title (s), description (s), price (d), duration (i), destinationId (i), imageUrl (s)
        $stmt->bind_param("ssdiis", $tour_name, $description, $price, $duration, $destinationId, $imageUrl);

        if ($stmt->execute()) {
            $_SESSION['message'] = 'Tour added successfully!';
            $_SESSION['message_type'] = 'success';
            // Redirect admins to the admin manage page
            header("Location: " . BASE_URL . "admin/manage-tours.php");
            exit();
        } else {
            $error = 'Error adding tour: ' . $conn->error;
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
                <h2 class="card-title text-center mb-4">Add New Tour</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="tour_name" class="form-label">Tour Name *</label>
                        <input type="text" class="form-control" id="tour_name" name="tour_name"
                            value="<?php echo htmlspecialchars($tour_name); ?>" required>
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
                        <label for="duration" class="form-label">Duration *</label>
                        <input type="text" class="form-control" id="duration" name="duration"
                            value="<?php echo htmlspecialchars($duration); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="destination" class="form-label">Destination *</label>
                        <input type="text" class="form-control" id="destination" name="destination"
                            value="<?php echo htmlspecialchars($destination); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Add Tour</button>
                </form>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>