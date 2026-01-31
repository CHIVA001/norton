<?php
/**
 * My Bookings Page
 * User's booking history with status filters
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'My Bookings - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$userId = getCurrentUserId();
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Build query
$query = "SELECT b.*, t.title, t.imageUrl FROM bookings b JOIN tours t ON b.tourId = t.id WHERE b.userId = ?";
$countQuery = "SELECT COUNT(*) as total FROM bookings WHERE userId = ?";

if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
    $query .= " AND b.status = ?";
    $countQuery .= " AND status = ?";
}

$query .= " ORDER BY b.createdAt DESC LIMIT ? OFFSET ?";

// Count total
$stmt = $conn->prepare($countQuery);
if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
    $stmt->bind_param("ss", $userId, $statusFilter);
} else {
    $stmt->bind_param("s", $userId);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get pagination data
$pagination = getPaginationData($page, $perPage, $total);

// Fetch bookings
$stmt = $conn->prepare($query);
if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
    $stmt->bind_param("ssii", $userId, $statusFilter, $pagination['perPage'], $pagination['offset']);
} else {
    $stmt->bind_param("sii", $userId, $pagination['perPage'], $pagination['offset']);
}
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">
            <i class="fas fa-calendar"></i> My Bookings
        </h2>
    </div>
</div>

<!-- Filter Buttons -->
<div class="mb-4">
    <div class="btn-group" role="group">
        <a href="my-bookings.php" class="btn btn-outline-primary <?php echo empty($statusFilter) ? 'active' : ''; ?>">
            All Bookings
        </a>
        <a href="my-bookings.php?status=pending"
            class="btn btn-outline-primary <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
            <i class="fas fa-clock"></i> Pending
        </a>
        <a href="my-bookings.php?status=confirmed"
            class="btn btn-outline-primary <?php echo $statusFilter === 'confirmed' ? 'active' : ''; ?>">
            <i class="fas fa-check"></i> Confirmed
        </a>
        <a href="my-bookings.php?status=completed"
            class="btn btn-outline-primary <?php echo $statusFilter === 'completed' ? 'active' : ''; ?>">
            <i class="fas fa-check-double"></i> Completed
        </a>
        <a href="my-bookings.php?status=cancelled"
            class="btn btn-outline-primary <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">
            <i class="fas fa-times"></i> Cancelled
        </a>
    </div>
</div>

<!-- Bookings List -->
<?php if (empty($bookings)): ?>
<div class="alert alert-info text-center py-5">
    <i class="fas fa-inbox fa-3x mb-3"></i><br>
    <strong>No bookings found</strong><br>
    <a href="<?php echo BASE_URL; ?>tours/tour-list.php" class="btn btn-primary mt-3">Browse Tours</a>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($bookings as $booking): ?>
    <div class="col-md-12 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <?php if (!empty($booking['imageUrl'])): ?>
                        <img src="<?php echo BASE_URL . htmlspecialchars($booking['imageUrl']); ?>"
                            class="img-fluid rounded" alt="Tour" style="height: 150px; object-fit: cover;">
                        <?php else: ?>
                        <div class="bg-light d-flex justify-content-center align-items-center rounded"
                            style="height: 150px;">
                            <i class="fas fa-image fa-2x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-7">
                        <h5><?php echo htmlspecialchars(substr($booking['title'], 0, 50)); ?></h5>

                        <p class="text-muted mb-2">
                            <strong>Code:</strong> <code><?php echo htmlspecialchars($booking['bookingCode']); ?></code>
                        </p>

                        <dl class="row mb-0">
                            <dt class="col-sm-4">Booking Date:</dt>
                            <dd class="col-sm-8"><?php echo formatDate($booking['createdAt']); ?></dd>

                            <dt class="col-sm-4">Start Date:</dt>
                            <dd class="col-sm-8"><?php echo formatDate($booking['startDate']); ?></dd>

                            <dt class="col-sm-4">Travelers:</dt>
                            <dd class="col-sm-8"><?php echo $booking['numberOfPeople']; ?> people</dd>
                        </dl>
                    </div>

                    <div class="col-md-3 text-end">
                        <div class="mb-3">
                            <p class="mb-1">
                                <strong>Total Amount:</strong><br>
                            <h5 class="text-primary"><?php echo formatCurrency($booking['totalPrice']); ?></h5>
                            </p>
                        </div>

                        <div class="mb-3">
                            <p class="mb-1">
                                <strong>Status:</strong><br><?php echo getBookingStatusBadge($booking['status']); ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <p class="mb-1">
                                <strong>Payment:</strong><br><?php echo getPaymentStatusBadge($booking['paymentStatus']); ?>
                            </p>
                        </div>

                        <a href="booking-details.php?id=<?php echo $booking['id']; ?>"
                            class="btn btn-sm btn-primary">View Details</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php
    if (!empty($statusFilter)) {
        echo getPaginationHTML($pagination, 'page');
    } else {
        echo getPaginationHTML($pagination, 'page');
    }
?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>