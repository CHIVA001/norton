<?php
/**
 * User Registration Page
 * Allows new users to create an account
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'User Registration - Travel Agency';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$errors = [];
$successMessage = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = sanitize($_POST['firstname'] ?? '');
    $lastname = sanitize($_POST['lastname'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $city = sanitize($_POST['city'] ?? '');
    $country = sanitize($_POST['country'] ?? '');

    // Validation
    if (empty($firstname))
        $errors[] = 'First name is required';
    if (empty($lastname))
        $errors[] = 'Last name is required';
    if (empty($email) || !validateEmail($email))
        $errors[] = 'Valid email is required';
    if (empty($password))
        $errors[] = 'Password is required';
    if (!validatePassword($password)) {
        $errors[] = 'Password must be at least 8 characters with uppercase, lowercase, and numbers';
    }
    if ($password !== $confirmPassword)
        $errors[] = 'Passwords do not match';
    if (!empty($phone) && !validatePhone($phone))
        $errors[] = 'Invalid phone format';

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $errors[] = 'Email already registered';
        }
        $stmt->close();
    }

    // Create account if no errors
    if (empty($errors)) {
        $hashedPassword = hashPassword($password);

        $stmt = $conn->prepare("
            INSERT INTO users (firstname, lastname, email, password, phone, address, city, country)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "ssssssss",
            $firstname,
            $lastname,
            $email,
            $hashedPassword,
            $phone,
            $address,
            $city,
            $country
        );

        if ($stmt->execute()) {
            $stmt->close();
            $_SESSION['message'] = 'Registration successful! Please login.';
            $_SESSION['message_type'] = 'success';
            header("Location: user-login.php");
            exit();
        } else {
            $errors[] = 'Registration failed. Please try again.';
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
                <h2 class="card-title text-center mb-4">Create Account</h2>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstname" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="firstname" name="firstname"
                                value="<?php echo htmlspecialchars($_POST['firstname'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastname" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="lastname" name="lastname"
                                value="<?php echo htmlspecialchars($_POST['lastname'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted">Minimum 8 characters, with uppercase, lowercase, and numbers</small>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password *</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address"
                            value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control" id="city" name="city"
                                value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="country" class="form-label">Country</label>
                            <input type="text" class="form-control" id="country" name="country"
                                value="<?php echo htmlspecialchars($_POST['country'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Create Account</button>
                </form>

                <hr>
                <p class="text-center">Already have an account? <a href="user-login.php">Login here</a></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>