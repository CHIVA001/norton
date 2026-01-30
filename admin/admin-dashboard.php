<?php
/**
 * Admin Dashboard Page
 * Overview with statistics
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Admin Dashboard - Travel Agency';

// Check admin authentication
require_once __DIR__ . '/../includes/admin-auth-check.php';

$adminId = getCurrentUserId();

// Get statistics
$stats = [];

// Total users
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$stmt->execute();
$stats['total_users'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total bookings
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings");
$stmt->execute();
$stats['total_bookings'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Confirmed bookings
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'");
$stmt->execute();
$stats['confirmed_bookings'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total revenue (paid bookings)
$stmt = $conn->prepare("SELECT SUM(totalPrice) as total FROM bookings WHERE paymentStatus = 'paid'");
$stmt->execute();
$stats['total_revenue'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Total tours
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tours");
$stmt->execute();
$stats['total_tours'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total destinations
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM destinations");
$stmt->execute();
$stats['total_destinations'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Pending payments
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM payments WHERE status = 'pending'");
$stmt->execute();
$stats['pending_payments'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Recent bookings
$stmt = $conn->prepare("
    SELECT b.*, u.firstname, u.lastname, u.email, t.title
    FROM bookings b
    JOIN users u ON b.userId = u.id
    JOIN tours t ON b.tourId = t.id
    ORDER BY b.createdAt DESC
    LIMIT 5
");
$stmt->execute();
$recentBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4">
    <h2><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h2>
    <p class="text-muted">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h6 class="card-title">Total Users</h6>
                <h3 class="card-text"><?php echo $stats['total_users']; ?></h3>
            </div>
            <div class="card-footer">
                <small><a href="manage-users.php" class="text-white">View Users</a></small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h6 class="card-title">Total Bookings</h6>
                <h3 class="card-text"><?php echo $stats['total_bookings']; ?></h3>
            </div>
            <div class="card-footer">
                <small>
                    <a href="manage-bookings.php" class="text-white">Confirmed:
                        <?php echo $stats['confirmed_bookings']; ?></a>
                </small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h6 class="card-title">Total Revenue</h6>
                <h3 class="card-text"><?php echo formatCurrency($stats['total_revenue']); ?></h3>
            </div>
            <div class="card-footer">
                <small>From paid bookings</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h6 class="card-title">Pending Payments</h6>
                <h3 class="card-text"><?php echo $stats['pending_payments']; ?></h3>
            </div>
            <div class="card-footer">
                <small>Awaiting payment</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <h6 class="card-title">Total Tours</h6>
                <h3 class="card-text"><?php echo $stats['total_tours']; ?></h3>
            </div>
            <div class="card-footer">
                <small><a href="manage-tours.php" class="text-white">Manage Tours</a></small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card text-white bg-dark">
            <div class="card-body">
                <h6 class="card-title">Total Destinations</h6>
                <h3 class="card-text"><?php echo $stats['total_destinations']; ?></h3>
            </div>
            <div class="card-footer">
                <small><a href="manage-destinations.php" class="text-white">Manage</a></small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Bookings -->
<div class="card shadow mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Recent Bookings</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Booking Code</th>
                        <th>Customer</th>
                        <th>Tour</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentBookings as $booking): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($booking['bookingCode']); ?></code></td>
                            <td><?php echo htmlspecialchars($booking['firstname'] . ' ' . $booking['lastname']); ?></td>
                            <td><?php echo htmlspecialchars(substr($booking['title'], 0, 25)); ?></td>
                            <td><?php echo formatCurrency($booking['totalPrice']); ?></td>
                            <td><?php echo getBookingStatusBadge($booking['status']); ?></td>
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
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-globe fa-3x text-primary mb-3"></i>
                <h6 class="card-title">Manage Destinations</h6>
                <a href="manage-destinations.php" class="btn btn-sm btn-primary">Go</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-suitcase fa-3x text-success mb-3"></i>
                <h6 class="card-title">Manage Tours</h6>
                <a href="manage-tours.php" class="btn btn-sm btn-success">Go</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-calendar fa-3x text-info mb-3"></i>
                <h6 class="card-title">Manage Bookings</h6>
                <a href="manage-bookings.php" class="btn btn-sm btn-info">Go</a>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x text-warning mb-3"></i>
                <h6 class="card-title">Manage Users</h6>
                <a href="manage-users.php" class="btn btn-sm btn-warning">Go</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>