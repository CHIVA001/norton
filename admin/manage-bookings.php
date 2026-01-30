<?php
/**
 * Manage Bookings Page
 * View and update booking statuses
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Manage Bookings - Travel Agency';

// Check admin authentication
require_once __DIR__ . '/../includes/admin-auth-check.php';

$adminId = getCurrentUserId();
$statusFilter = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Build query
$query = "SELECT b.*, u.firstname, u.lastname, u.email, t.title FROM bookings b 
          JOIN users u ON b.userId = u.id 
          JOIN tours t ON b.tourId = t.id WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM bookings WHERE 1=1";

if (!empty($statusFilter) && in_array($statusFilter, ['pending', 'confirmed', 'completed', 'cancelled'])) {
    $query .= " AND b.status = ?";
    $countQuery .= " AND status = ?";
}

$query .= " ORDER BY b.createdAt DESC LIMIT ? OFFSET ?";

// Count
$countStmt = $conn->prepare($countQuery);
if (!empty($statusFilter)) {
    $countStmt->bind_param("s", $statusFilter);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$pagination = getPaginationData($page, $perPage, $total);

// Fetch bookings
$stmt = $conn->prepare($query);
if (!empty($statusFilter)) {
    $stmt->bind_param("sii", $statusFilter, $pagination['perPage'], $pagination['offset']);
} else {
    $stmt->bind_param("ii", $pagination['perPage'], $pagination['offset']);
}
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['new_status'])) {
    $bookingId = intval($_POST['booking_id']);
    $newStatus = sanitize($_POST['new_status']);

    if (in_array($newStatus, ['pending', 'confirmed', 'completed', 'cancelled'])) {
        $updateStmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $updateStmt->bind_param("si", $newStatus, $bookingId);

        if ($updateStmt->execute()) {
            // Log activity
            logAdminActivity($adminId, "Updated booking status to $newStatus", 'bookings', $bookingId);

            $_SESSION['message'] = 'Booking status updated successfully';
            $_SESSION['message_type'] = 'success';
        }
        $updateStmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-calendar"></i> Manage Bookings</h2>
</div>

<!-- Filter Buttons -->
<div class="mb-4">
    <div class="btn-group" role="group">
        <a href="manage-bookings.php"
            class="btn btn-outline-primary <?php echo empty($statusFilter) ? 'active' : ''; ?>">
            All
        </a>
        <a href="manage-bookings.php?status=pending"
            class="btn btn-outline-primary <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
            Pending
        </a>
        <a href="manage-bookings.php?status=confirmed"
            class="btn btn-outline-primary <?php echo $statusFilter === 'confirmed' ? 'active' : ''; ?>">
            Confirmed
        </a>
        <a href="manage-bookings.php?status=completed"
            class="btn btn-outline-primary <?php echo $statusFilter === 'completed' ? 'active' : ''; ?>">
            Completed
        </a>
    </div>
</div>

<?php if (empty($bookings)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-inbox fa-2x mb-2"></i><br>
        No bookings found
    </div>
<?php else: ?>
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Booking Code</th>
                        <th>Customer</th>
                        <th>Tour</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($booking['bookingCode']); ?></code></td>
                            <td>
                                <strong><?php echo htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars(substr($booking['title'], 0, 25)); ?></td>
                            <td><?php echo formatCurrency($booking['totalPrice']); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                    <select name="new_status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $booking['status'] === 'pending' ? 'selected' : ''; ?>>
                                            Pending</option>
                                        <option value="confirmed" <?php echo $booking['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                        <option value="completed" <?php echo $booking['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        <option value="cancelled" <?php echo $booking['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </form>
                            </td>
                            <td><?php echo getPaymentStatusBadge($booking['paymentStatus']); ?></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>bookings/booking-details.php?id=<?php echo $booking['id']; ?>"
                                    class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php echo getPaginationHTML($pagination, 'page'); ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>