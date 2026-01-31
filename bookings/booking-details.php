<?php
/**
 * Booking Details Page
 * View single booking details
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Booking Details - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$bookingId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$userId = getCurrentUserId();

if ($bookingId <= 0) {
    header("Location: my-bookings.php");
    exit();
}

// Fetch booking details
$stmt = $conn->prepare("
    SELECT b.*, t.title, t.imageUrl, t.duration, d.name as destinationName
    FROM bookings b
    JOIN tours t ON b.tourId = t.id
    JOIN destinations d ON t.destinationId = d.id
    WHERE b.id = ? AND b.userId = ?
");
$stmt->bind_param("ii", $bookingId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();
$stmt->close();

if (!$booking) {
    header("Location: my-bookings.php");
    exit();
}

// Fetch payment details
$stmt = $conn->prepare("SELECT * FROM payments WHERE bookingId = ?");
$stmt->bind_param("i", $bookingId);
$stmt->execute();
$payment = $stmt->get_result()->fetch_assoc();
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="my-bookings.php">My Bookings</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars($booking['bookingCode']); ?></li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-8">
        <!-- Booking Card -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Booking Details</h4>
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
                        <p class="text-muted mb-3">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($booking['destinationName']); ?>
                        </p>

                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><strong>Booking Code:</strong></td>
                                    <td><code><?php echo htmlspecialchars($booking['bookingCode']); ?></code></td>
                                </tr>
                                <tr>
                                    <td><strong>Tour Duration:</strong></td>
                                    <td><?php echo $booking['duration']; ?> days</td>
                                </tr>
                                <tr>
                                    <td><strong>Number of Travelers:</strong></td>
                                    <td><?php echo $booking['numberOfPeople']; ?> people</td>
                                </tr>
                                <tr>
                                    <td><strong>Start Date:</strong></td>
                                    <td><?php echo formatDate($booking['startDate']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Booked On:</strong></td>
                                    <td><?php echo formatDate($booking['createdAt']); ?></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td><?php echo getBookingStatusBadge($booking['status']); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Card -->
        <div class="card shadow mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Payment Status:</strong></p>
                        <?php echo getPaymentStatusBadge($payment['status']); ?>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Amount:</strong></p>
                        <h5 class="text-primary"><?php echo formatCurrency($booking['totalPrice']); ?></h5>
                    </div>
                </div>

                <table class="table">
                    <tbody>
                        <tr>
                            <td>Price per Person:</td>
                            <td class="text-end">
                                <?php echo formatCurrency($booking['totalPrice'] / $booking['numberOfPeople']); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Number of People:</td>
                            <td class="text-end"><?php echo $booking['numberOfPeople']; ?></td>
                        </tr>
                        <tr class="table-active">
                            <td><strong>Total Amount:</strong></td>
                            <td class="text-end"><strong><?php echo formatCurrency($booking['totalPrice']); ?></strong>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php if ($payment['status'] === 'pending'): ?>
                    <a href="<?php echo BASE_URL; ?>payments/payment-form.php?booking_id=<?php echo $bookingId; ?>"
                        class="btn btn-success">
                        <i class="fas fa-credit-card"></i> Complete Payment Now
                    </a>
                <?php elseif ($payment['status'] === 'successful'): ?>
                    <div class="alert alert-success mt-3">
                        <i class="fas fa-check-circle"></i> Payment successful!
                        <?php if (!empty($payment['transactionId'])): ?>
                            <br><small>Transaction ID: <?php echo htmlspecialchars($payment['transactionId']); ?></small>
                        <?php endif; ?>
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
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                <a href="<?php echo BASE_URL; ?>tours/tour-detail.php?id=<?php echo $booking['tourId']; ?>"
                    class="btn btn-outline-primary w-100 mb-2">
                    <i class="fas fa-eye"></i> View Tour Details
                </a>

                <?php if ($booking['status'] === 'completed'): ?>
                    <a href="<?php echo BASE_URL; ?>reviews/add-review.php?tour_id=<?php echo $booking['tourId']; ?>"
                        class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-pen"></i> Write Review
                    </a>
                <?php endif; ?>

                <a href="my-bookings.php" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left"></i> Back to Bookings
                </a>
            </div>
        </div>

        <!-- Timeline -->
        <div class="card shadow">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Booking Timeline</h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-primary"></div>
                        <div>
                            <p class="mb-1"><strong>Booking Created</strong></p>
                            <small
                                class="text-muted"><?php echo formatDate($booking['createdAt'], 'd M Y, H:i'); ?></small>
                        </div>
                    </div>

                    <?php if (!empty($payment['createdAt'])): ?>
                        <div class="timeline-item">
                            <div
                                class="timeline-marker <?php echo $payment['status'] === 'successful' ? 'bg-success' : 'bg-warning'; ?>">
                            </div>
                            <div>
                                <p class="mb-1"><strong>Payment <?php echo ucfirst($payment['status']); ?></strong></p>
                                <small
                                    class="text-muted"><?php echo formatDate($payment['createdAt'], 'd M Y, H:i'); ?></small>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="timeline-item">
                        <div
                            class="timeline-marker <?php echo $booking['status'] === 'confirmed' ? 'bg-success' : 'bg-secondary'; ?>">
                        </div>
                        <div>
                            <p class="mb-1"><strong>Tour Start Date</strong></p>
                            <small class="text-muted"><?php echo formatDate($booking['startDate'], 'd M Y'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        position: relative;
        padding: 10px 0;
    }

    .timeline-item {
        display: flex;
        margin-bottom: 20px;
    }

    .timeline-marker {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 15px;
        margin-top: 3px;
        flex-shrink: 0;
    }

    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 9px;
        width: 2px;
        height: 40px;
        background: #dee2e6;
        margin-top: 20px;
    }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>