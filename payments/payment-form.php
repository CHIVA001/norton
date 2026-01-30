<?php
/**
 * Payment Form Page
 * Collects payment information from users
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Payment - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$bookingId = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$userId = getCurrentUserId();

if ($bookingId <= 0) {
    header("Location: " . BASE_URL . "bookings/my-bookings.php");
    exit();
}

// Fetch booking and payment details
$stmt = $conn->prepare("
    SELECT b.*, t.title, p.id as paymentId, p.status as paymentStatus, p.amount
    FROM bookings b
    JOIN tours t ON b.tourId = t.id
    LEFT JOIN payments p ON b.id = p.bookingId
    WHERE b.id = ? AND b.userId = ?
");
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    header("Location: " . BASE_URL . "bookings/my-bookings.php");
    exit();
}

// If already paid, redirect
if ($booking['paymentStatus'] === 'successful') {
    $_SESSION['message'] = 'This booking is already paid';
    $_SESSION['message_type'] = 'info';
    header("Location: " . BASE_URL . "bookings/booking-details.php?id=$bookingId");
    exit();
}

$errors = [];
$successMessage = '';

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cardholderName = sanitize($_POST['cardholder_name'] ?? '');
    $cardNumber = sanitize($_POST['card_number'] ?? '');
    $cardExpiry = sanitize($_POST['card_expiry'] ?? '');
    $cardCvv = sanitize($_POST['card_cvv'] ?? '');
    $paymentMethod = sanitize($_POST['payment_method'] ?? '');

    // Basic validation
    if (empty($cardholderName))
        $errors[] = 'Cardholder name is required';
    if (empty($cardNumber) || strlen($cardNumber) < 13)
        $errors[] = 'Valid card number is required';
    if (empty($cardExpiry) || !preg_match('/^\d{2}\/\d{2}$/', $cardExpiry))
        $errors[] = 'Card expiry must be MM/YY format';
    if (empty($cardCvv) || !preg_match('/^\d{3,4}$/', $cardCvv))
        $errors[] = 'Valid CVV is required';
    if (empty($paymentMethod))
        $errors[] = 'Payment method is required';

    // Process payment if no errors
    if (empty($errors)) {
        // In a real application, you would use a payment gateway like Stripe, PayPal, etc.
        // For now, we'll simulate a successful payment

        $transactionId = generateTransactionId();

        // Update payment record
        $stmt = $conn->prepare("
            UPDATE payments
            SET status = 'successful', transactionId = ?, paymentMethod = ?
            WHERE bookingId = ?
        ");
        $stmt->bind_param("ssi", $transactionId, $paymentMethod, $bookingId);

        if ($stmt->execute()) {
            $stmt->close();

            // Update booking status to confirmed
            $status = 'confirmed';
            $updateStmt = $conn->prepare("UPDATE bookings SET status = ?, paymentStatus = 'paid' WHERE id = ?");
            $updateStmt->bind_param("si", $status, $bookingId);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['message'] = 'Payment successful! Your booking is confirmed.';
            $_SESSION['message_type'] = 'success';
            header("Location: " . BASE_URL . "payments/payment-receipt.php?booking_id=$bookingId");
            exit();
        } else {
            $errors[] = 'Payment processing failed. Please try again.';
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Booking Summary -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Booking Summary</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h6><?php echo htmlspecialchars($booking['title']); ?></h6>
                        <p class="text-muted mb-0">
                            Booking Code: <code><?php echo htmlspecialchars($booking['bookingCode']); ?></code>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <h5 class="text-primary"><?php echo formatCurrency($booking['totalPrice']); ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
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
                    <!-- Payment Method -->
                    <div class="mb-4">
                        <label class="form-label">Payment Method *</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="payment_method" id="credit_card"
                                value="credit_card" checked>
                            <label class="btn btn-outline-primary" for="credit_card">
                                <i class="fas fa-credit-card"></i> Credit Card
                            </label>

                            <input type="radio" class="btn-check" name="payment_method" id="debit_card"
                                value="debit_card">
                            <label class="btn btn-outline-primary" for="debit_card">
                                <i class="fas fa-credit-card"></i> Debit Card
                            </label>

                            <input type="radio" class="btn-check" name="payment_method" id="paypal" value="paypal">
                            <label class="btn btn-outline-primary" for="paypal">
                                <i class="fab fa-paypal"></i> PayPal
                            </label>
                        </div>
                    </div>

                    <!-- Cardholder Name -->
                    <div class="mb-3">
                        <label for="cardholder_name" class="form-label">Cardholder Name *</label>
                        <input type="text" class="form-control" id="cardholder_name" name="cardholder_name"
                            value="<?php echo htmlspecialchars($_POST['cardholder_name'] ?? ''); ?>" required>
                    </div>

                    <!-- Card Number -->
                    <div class="mb-3">
                        <label for="card_number" class="form-label">Card Number *</label>
                        <input type="text" class="form-control" id="card_number" name="card_number"
                            placeholder="1234 5678 9012 3456"
                            value="<?php echo htmlspecialchars($_POST['card_number'] ?? ''); ?>" inputmode="numeric"
                            required>
                        <small class="text-muted">16 digits for credit/debit cards</small>
                    </div>

                    <!-- Expiry and CVV -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="card_expiry" class="form-label">Expiry Date *</label>
                            <input type="text" class="form-control" id="card_expiry" name="card_expiry"
                                placeholder="MM/YY" value="<?php echo htmlspecialchars($_POST['card_expiry'] ?? ''); ?>"
                                maxlength="5" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="card_cvv" class="form-label">CVV *</label>
                            <input type="text" class="form-control" id="card_cvv" name="card_cvv" placeholder="123"
                                value="<?php echo htmlspecialchars($_POST['card_cvv'] ?? ''); ?>" maxlength="4"
                                inputmode="numeric" required>
                        </div>
                    </div>

                    <!-- Amount Breakdown -->
                    <div class="card mb-4 bg-light">
                        <div class="card-body">
                            <h6 class="card-title">Amount to Pay</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Tour Price:</span>
                                <span><?php echo formatCurrency($booking['totalPrice']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Taxes & Fees:</span>
                                <span>$0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <strong>Total:</strong>
                                <strong
                                    class="text-primary h5"><?php echo formatCurrency($booking['totalPrice']); ?></strong>
                            </div>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the terms and conditions and authorize this payment
                        </label>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        <i class="fas fa-lock"></i> Complete Payment
                    </button>

                    <a href="<?php echo BASE_URL; ?>bookings/booking-details.php?id=<?php echo $bookingId; ?>"
                        class="btn btn-outline-secondary btn-lg w-100 mt-2">Cancel</a>
                </form>

                <!-- Security Info -->
                <div class="alert alert-info mt-4">
                    <i class="fas fa-shield-alt"></i> <strong>Secure Payment</strong><br>
                    Your payment information is encrypted and secure. We never store your card details.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>