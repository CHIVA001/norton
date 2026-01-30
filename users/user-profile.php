<?php
/**
 * User Profile Page
 * Allows users to view and edit their profile
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'My Profile - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$userId = getCurrentUserId();
$errors = [];
$successMessage = '';

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: " . BASE_URL . "users/logout.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = sanitize($_POST['firstname'] ?? '');
    $lastname = sanitize($_POST['lastname'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $country = sanitize($_POST['country'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($firstname))
        $errors[] = 'First name is required';
    if (empty($lastname))
        $errors[] = 'Last name is required';
    if (!empty($phone) && !validatePhone($phone))
        $errors[] = 'Invalid phone format';

    // Check password change
    if (!empty($newPassword)) {
        if (!validatePassword($newPassword)) {
            $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, and numbers';
        }
        if ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }
    }

    // Handle profile image upload
    $profileImage = $user['profileImage'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handleFileUpload(
            $_FILES['profile_image'],
            __DIR__ . '/../uploads/profiles',
            ['image/jpeg', 'image/png', 'image/gif'],
            5242880 // 5MB
        );

        if ($uploadResult['success']) {
            // Delete old image if exists
            if (!empty($user['profileImage']) && file_exists(__DIR__ . '/../uploads/profiles/' . $user['profileImage'])) {
                unlink(__DIR__ . '/../uploads/profiles/' . $user['profileImage']);
            }
            $profileImage = $uploadResult['filename'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    }

    // Update profile if no errors
    if (empty($errors)) {
        if (!empty($newPassword)) {
            $hashedPassword = hashPassword($newPassword);
            $stmt = $conn->prepare("
                UPDATE users 
                SET firstname = ?, lastname = ?, phone = ?, address = ?, city = ?, country = ?, profileImage = ?, password = ?
                WHERE id = ?
            ");
            $stmt->bind_param(
                "ssssssssi",
                $firstname,
                $lastname,
                $phone,
                $address,
                $city,
                $country,
                $profileImage,
                $hashedPassword,
                $userId
            );
        } else {
            $stmt = $conn->prepare("
                UPDATE users 
                SET firstname = ?, lastname = ?, phone = ?, address = ?, city = ?, country = ?, profileImage = ?
                WHERE id = ?
            ");
            $stmt->bind_param(
                "sssssssi",
                $firstname,
                $lastname,
                $phone,
                $address,
                $city,
                $country,
                $profileImage,
                $userId
            );
        }

        if ($stmt->execute()) {
            $successMessage = 'Profile updated successfully!';
            $_SESSION['user_name'] = $firstname . ' ' . $lastname;

            // Refresh user data
            $user['firstname'] = $firstname;
            $user['lastname'] = $lastname;
            $user['phone'] = $phone;
            $user['address'] = $address;
            $user['city'] = $city;
            $user['country'] = $country;
            $user['profileImage'] = $profileImage;
        } else {
            $errors[] = 'Failed to update profile. Please try again.';
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-3">
        <!-- Profile Card -->
        <div class="card shadow mb-4">
            <div class="card-body text-center">
                <?php if (!empty($user['profileImage'])): ?>
                    <img src="<?php echo BASE_URL . 'uploads/profiles/' . htmlspecialchars($user['profileImage']); ?>"
                        alt="Profile" class="rounded-circle mb-3" width="150" height="150">
                <?php else: ?>
                    <div class="rounded-circle bg-secondary d-inline-flex justify-content-center align-items-center mb-3"
                        style="width: 150px; height: 150px;">
                        <i class="fas fa-user fa-4x text-white"></i>
                    </div>
                <?php endif; ?>

                <h4>
                    <?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?>
                </h4>
                <p class="text-muted">
                    <?php echo htmlspecialchars($user['email']); ?>
                </p>
                <?php echo getUserRoleBadge($user['role']); ?>

                <small class="d-block text-muted mt-2">
                    Joined:
                    <?php echo formatDate($user['createdAt'], 'd M Y'); ?>
                </small>
            </div>
        </div>

        <!-- Navigation -->
        <div class="list-group">
            <a href="user-dashboard.php" class="list-group-item list-group-item-action">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="user-profile.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-user"></i> My Profile
            </a>
            <a href="<?php echo BASE_URL; ?>bookings/my-bookings.php" class="list-group-item list-group-item-action">
                <i class="fas fa-calendar"></i> My Bookings
            </a>
            <a href="<?php echo BASE_URL; ?>wishlist/my-wishlist.php" class="list-group-item list-group-item-action">
                <i class="fas fa-heart"></i> Wishlist
            </a>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title mb-4">Edit Profile</h3>

                <?php if (!empty($successMessage)): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li>
                                    <?php echo htmlspecialchars($error); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstname" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="firstname" name="firstname"
                                value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastname" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="lastname" name="lastname"
                                value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email (Cannot be changed)</label>
                        <input type="email" class="form-control" disabled
                            value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address"
                            value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city"
                                value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country"
                                value="<?php echo htmlspecialchars($user['country'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image"
                            accept="image/*">
                        <small class="text-muted">Accepted: JPG, PNG, GIF (Max 5MB)</small>
                    </div>

                    <hr>

                    <h5>Change Password (Optional)</h5>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                        <small class="text-muted">Leave blank to keep current password</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>

                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="user-dashboard.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>