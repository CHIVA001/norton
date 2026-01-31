<?php
/**
 * User Login Page
 * Allows users to log in with email and password
 */

require_once __DIR__ . '/../config/database.php';
session_start();
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'User Login - Travel Agency';

// Check if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        // Check credentials
        $stmt = $conn->prepare("SELECT id, firstname, lastname, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (verifyPassword($password, $user['password'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['firstname'] . ' ' . $user['lastname'];

                $_SESSION['message'] = 'Login successful!';
                $_SESSION['message_type'] = 'success';

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: " . BASE_URL . "admin/admin-dashboard.php");
                } else {
                    header("Location: " . BASE_URL . "users/user-dashboard.php");
                }
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-body">
                <h2 class="card-title text-center mb-4">Login</h2>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password *</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <hr>
                <p class="text-center">Don't have an account? <a href="user-registration.php">Sign up here</a></p>


            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>