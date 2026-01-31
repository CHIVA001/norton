<?php
/**
 * Booking Confirmation Page
 * Displays booking details with booking code
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Booking Confirmation - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$bookingCode = isset($_GET['code']) ? sanitize($_GET['code']) : '';
$userId = getCurrentUserId();

if (empty($bookingCode)) {
    header("Location: " . BASE_URL . "bookings/my-bookings.php");
    exit();
}

// Fetch booking details
$stmt = $conn->prepare("
    SELECT b.*, t.title, t.imageUrl, t.duration, d.name as destinationName
    FROM bookings b
    JOIN tours t ON b.tourId = t.id
    JOIN destinations d ON t.destinationId = d.id
    WHERE b.bookingCode = ? AND b.userId = ?
");
$stmt->bind_param("si", $bookingCode, $userId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    header("Location: " . BASE_URL . "bookings/my-bookings.php");
    exit();
}

// Fetch payment details
$stmt = $conn->prepare("SELECT * FROM payments WHERE bookingId = ?");
$stmt->bind_param("i", $booking['id']);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Success Message -->
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Booking Confirmed!</h4>
            <p>Your booking has been successfully created. Your confirmation code is
                <strong><?php echo htmlspecialchars($booking['bookingCode']); ?></strong>
            </p>
            <hr>
            <p class="mb-0">A confirmation email has been sent to your registered email address.</p>
        </div>

        <!-- Booking Card -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Booking Details</h5>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-4">
                        <?php if (!empty($booking['imageUrl'])): ?>
                            <img src="<?php echo BASE_URL . htmlspecialchars($booking['imageUrl']); ?>"
                                class="img-fluid rounded" alt="Tour">
                        <?php else: ?>
                            <div class="bg-light d-flex justify-content-center align-items-center rounded"
                                style="height: 250px;">
                                <i class="fas fa-image fa-4x text-muted"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($booking['title']); ?></h4>
                        <p class="text-muted mb-2">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($booking['destinationName']); ?>
                        </p>

                        <dl class="row">
                            <dt class="col-sm-6">Booking Code:</dt>
                            <dd class="col-sm-6"><code><?php echo htmlspecialchars($booking['bookingCode']); ?></code>
                            </dd>

                            <dt class="col-sm-6">Tour Duration:</dt>
                            <dd class="col-sm-6"><?php echo $booking['duration']; ?> days</dd>

                            <dt class="col-sm-6">Number of People:</dt>
                            <dd class="col-sm-6"><?php echo $booking['numberOfPeople']; ?></dd>

                            <dt class="col-sm-6">Start Date:</dt>
                            <dd class="col-sm-6"><?php echo formatDate($booking['startDate']); ?></dd>

                            <dt class="col-sm-6">Status:</dt>
                            <dd class="col-sm-6"><?php echo getBookingStatusBadge($booking['status']); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Card -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Payment Status:</strong> <?php echo getPaymentStatusBadge($payment['status']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Amount Due:</strong> <span
                                class="h5 text-primary"><?php echo formatCurrency($booking['totalPrice']); ?></span></p>
                    </div>
                </div>

                <table class="table">
                    <tbody>
                        <tr>
                            <td>Price per Person</td>
                            <td class="text-end">
                                <?php echo formatCurrency($booking['totalPrice'] / $booking['numberOfPeople']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Number of People</td>
                            <td class="text-end"><?php echo $booking['numberOfPeople']; ?></td>
                        </tr>
                        <tr class="table-active">
                            <td><strong>Total Amount</strong></td>
                            <td class="text-end"><strong><?php echo formatCurrency($booking['totalPrice']); ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php if ($payment['status'] === 'pending'): ?>
                    <a href="<?php echo BASE_URL; ?>payments/payment-form.php?booking_id=<?php echo $booking['id']; ?>"
                        class="btn btn-success btn-lg">
                        <i class="fas fa-credit-card"></i> Proceed to Payment
                    </a>
                <?php else: ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> Payment completed successfully!
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Special Requests -->
        <?php if (!empty($booking['specialRequests'])): ?>
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Special Requests</h5>
                </div>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($booking['specialRequests'])); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Next Steps -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Next Steps</h5>
            </div>
            <div class="card-body">
                <ol>
                    <li><?php if ($payment['status'] === 'pending'): ?>
                            <strong>Complete Payment</strong> - Proceed to payment section to secure your booking.
                        <?php else: ?>
                            <strong>Payment Completed</strong> - Your payment has been received.
                        <?php endif; ?>
                    </li>

                    <li><strong>Booking Confirmation</strong> - You will receive a detailed booking confirmation email.
                    </li>

                    <li><strong>Tour Details</strong> - 7 days before your tour, we'll send you detailed itinerary and
                        meeting information.</li>

                    <li><strong>Enjoy Your Tour</strong> - Have a wonderful experience!</li>
                </ol>
            </div>
        </div>

        <div class="mt-4 text-center">
            <a href="<?php echo BASE_URL; ?>bookings/my-bookings.php" class="btn btn-primary btn-lg">
                <i class="fas fa-calendar"></i> View All Bookings
            </a>
            <a href="<?php echo BASE_URL; ?>index.php" class="btn btn-outline-primary btn-lg">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>