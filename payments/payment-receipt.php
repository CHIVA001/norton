<?php
/**
 * Payment Receipt Page
 * Displays payment confirmation
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Payment Receipt - Travel Agency';

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
    SELECT b.*, t.title, t.imageUrl, d.name as destinationName, p.*
    FROM bookings b
    JOIN tours t ON b.tourId = t.id
    JOIN destinations d ON t.destinationId = d.id
    LEFT JOIN payments p ON b.id = p.bookingId
    WHERE b.id = ? AND b.userId = ?
");
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking || $booking['paymentStatus'] !== 'successful') {
    header("Location: " . BASE_URL . "bookings/my-bookings.php");
    exit();
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Success Message -->
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <h4 class="alert-heading"><i class="fas fa-check-circle"></i> Payment Successful!</h4>
            <p>Thank you for your payment. Your booking is now confirmed.</p>
            <hr>
            <p class="mb-0">A receipt has been sent to your email address.</p>
        </div>

        <!-- Receipt Card -->
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Payment Receipt</h5>
            </div>
            <div class="card-body">
                <!-- Company Info -->
                <div class="row mb-4 pb-4 border-bottom">
                    <div class="col-md-6">
                        <h4><i class="fas fa-globe"></i> Travel Agency</h4>
                        <p class="text-muted mb-0">
                            123 Travel Street<br>
                            World City, WC 12345<br>
                            Phone: +1 (555) 123-4567<br>
                            Email: info@travelagency.com
                        </p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h6>Receipt #</h6>
                        <p class="text-muted"><?php echo htmlspecialchars($booking['bookingCode']); ?></p>

                        <h6>Date</h6>
                        <p class="text-muted"><?php echo date('d M Y, H:i'); ?></p>

                        <h6>Transaction ID</h6>
                        <p class="text-muted"><code><?php echo htmlspecialchars($booking['transactionId']); ?></code>
                        </p>
                    </div>
                </div>

                <!-- Booking Details -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted">Booking Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Tour:</strong> <?php echo htmlspecialchars($booking['title']); ?>
                            </p>
                            <p class="mb-1"><strong>Destination:</strong>
                                <?php echo htmlspecialchars($booking['destinationName']); ?></p>
                            <p class="mb-1"><strong>Start Date:</strong>
                                <?php echo formatDate($booking['startDate']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Booking Code:</strong>
                                <code><?php echo htmlspecialchars($booking['bookingCode']); ?></code></p>
                            <p class="mb-1"><strong>Number of Travelers:</strong>
                                <?php echo $booking['numberOfPeople']; ?></p>
                            <p class="mb-1"><strong>Booking Date:</strong>
                                <?php echo formatDate($booking['createdAt']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Payment Breakdown -->
                <div class="mb-4">
                    <h6 class="text-uppercase text-muted">Payment Breakdown</h6>
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <td>Tour Price (per person)</td>
                                <td class="text-end">
                                    <?php echo formatCurrency($booking['totalPrice'] / $booking['numberOfPeople']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>Number of Travelers</td>
                                <td class="text-end">×<?php echo $booking['numberOfPeople']; ?></td>
                            </tr>
                            <tr class="border-top border-bottom">
                                <td>Subtotal</td>
                                <td class="text-end"><?php echo formatCurrency($booking['totalPrice']); ?></td>
                            </tr>
                            <tr>
                                <td>Taxes & Fees</td>
                                <td class="text-end">$0.00</td>
                            </tr>
                            <tr class="table-active">
                                <td><strong>Total Paid</strong></td>
                                <td class="text-end"><strong
                                        class="h5"><?php echo formatCurrency($booking['amount']); ?></strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Payment Details -->
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="text-uppercase text-muted">Payment Method</h6>
                    <p class="mb-1">
                        <strong>Method:</strong>
                        <?php echo ucfirst(str_replace('_', ' ', $booking['paymentMethod'])); ?>
                    </p>
                    <p class="mb-0">
                        <strong>Status:</strong> <span class="badge bg-success">Successful</span>
                    </p>
                </div>

                <!-- Terms -->
                <div class="mb-4 text-muted small">
                    <p class="mb-1">Thank you for your booking! You will receive a detailed tour itinerary 7 days before
                        your tour starts.</p>
                    <p class="mb-0">If you have any questions, please contact our support team at
                        support@travelagency.com</p>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row">
            <div class="col-md-6">
                <a href="<?php echo BASE_URL; ?>bookings/booking-details.php?id=<?php echo $bookingId; ?>"
                    class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-eye"></i> View Booking Details
                </a>
            </div>
            <div class="col-md-6">
                <a href="<?php echo BASE_URL; ?>bookings/my-bookings.php" class="btn btn-outline-primary btn-lg w-100">
                    <i class="fas fa-list"></i> My Bookings
                </a>
            </div>
        </div>

        <!-- Print Button -->
        <div class="text-center mt-4">
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Print Receipt
            </button>
        </div>
    </div>
</div>

<style>
    @media print {

        .navbar,
        .btn,
        .container>* {
            display: none;
        }

        .container {
            width: 100%;
            max-width: 100%;
        }
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>