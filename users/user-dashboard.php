<?php
/**
 * User Dashboard Page
 * Shows user's bookings, wishlist, and profile summary
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Dashboard - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$userId = getCurrentUserId();

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get statistics
$stats = [];

// Total bookings
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE userId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stats['total_bookings'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Confirmed bookings
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM bookings WHERE userId = ? AND status = 'confirmed'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stats['confirmed_bookings'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Total spent
$stmt = $conn->prepare("SELECT SUM(totalPrice) as total FROM bookings WHERE userId = ? AND paymentStatus = 'paid'");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stats['total_spent'] = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Wishlist count
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM wishlist WHERE userId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stats['wishlist_count'] = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Recent bookings
$stmt = $conn->prepare("
    SELECT b.*, t.title, t.imageUrl
    FROM bookings b
    JOIN tours t ON b.tourId = t.id
    WHERE b.userId = ?
    ORDER BY b.createdAt DESC
    LIMIT 5
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$recentBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
            </div>
        </div>

        <!-- Navigation -->
        <div class="list-group">
            <a href="user-dashboard.php" class="list-group-item list-group-item-action active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="user-profile.php" class="list-group-item list-group-item-action">
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
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <h6 class="card-title">Total Bookings</h6>
                        <h3 class="card-text">
                            <?php echo $stats['total_bookings']; ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <h6 class="card-title">Confirmed</h6>
                        <h3 class="card-text">
                            <?php echo $stats['confirmed_bookings']; ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <h6 class="card-title">Total Spent</h6>
                        <h3 class="card-text">
                            <?php echo formatCurrency($stats['total_spent']); ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <h6 class="card-title">Wishlist</h6>
                        <h3 class="card-text">
                            <?php echo $stats['wishlist_count']; ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Recent Bookings</h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentBookings)): ?>
                    <p class="text-muted text-center py-4">
                        <i class="fas fa-inbox fa-2x mb-2"></i><br>
                        No bookings yet. <a href="<?php echo BASE_URL; ?>tours/tour-list.php">Browse tours</a>
                    </p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tour</th>
                                    <th>Booking Code</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentBookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars(substr($booking['title'], 0, 25)); ?>
                                        </td>
                                        <td><code><?php echo htmlspecialchars($booking['bookingCode']); ?></code></td>
                                        <td>
                                            <?php echo formatDate($booking['startDate']); ?>
                                        </td>
                                        <td>
                                            <?php echo formatCurrency($booking['totalPrice']); ?>
                                        </td>
                                        <td>
                                            <?php echo getBookingStatusBadge($booking['status']); ?>
                                        </td>
                                        <td>
                                            <?php echo getPaymentStatusBadge($booking['paymentStatus']); ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo BASE_URL; ?>bookings/booking-details.php?id=<?php echo $booking['id']; ?>"
                                                class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="<?php echo BASE_URL; ?>bookings/my-bookings.php" class="btn btn-primary btn-sm">View All
                            Bookings</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>