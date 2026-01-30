<?php
/**
 * Tour Detail Page
 * Displays full tour information with gallery, itinerary, reviews, and booking
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Tour Details - Travel Agency';

// Get tour ID
$tourId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($tourId <= 0) {
    header("Location: tour-list.php");
    exit();
}

// Fetch tour details
$stmt = $conn->prepare("
    SELECT t.*, d.name as destinationName, d.country
    FROM tours t
    JOIN destinations d ON t.destinationId = d.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $tourId);
$stmt->execute();
$result = $stmt->get_result();
$tour = $result->fetch_assoc();
$stmt->close();

if (!$tour) {
    header("Location: tour-list.php");
    exit();
}

// Fetch tour gallery
$stmt = $conn->prepare("SELECT imageUrl FROM tourGallery WHERE tourId = ? ORDER BY imageOrder ASC");
$stmt->bind_param("i", $tourId);
$stmt->execute();
$gallery = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch reviews
$stmt = $conn->prepare("
    SELECT r.*, u.firstname, u.lastname, u.profileImage
    FROM reviews r
    JOIN users u ON r.userId = u.id
    WHERE r.tourId = ?
    ORDER BY r.createdAt DESC
");
$stmt->bind_param("i", $tourId);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check if user has completed booking for this tour
$hasCompletedBooking = false;
if (isSessionValid()) {
    $userId = getCurrentUserId();
    $stmt = $conn->prepare("
        SELECT id FROM bookings
        WHERE userId = ? AND tourId = ? AND status = 'completed'
        LIMIT 1
    ");
    $stmt->bind_param("ii", $userId, $tourId);
    $stmt->execute();
    $hasCompletedBooking = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

require_once __DIR__ . '/../includes/header.php';

// Parse amenities
$amenities = !empty($tour['amenities']) ? json_decode($tour['amenities'], true) : [];
$pageTitle = htmlspecialchars($tour['title']) . ' - Travel Agency';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="tour-list.php">Tours</a></li>
        <li class="breadcrumb-item active"><?php echo htmlspecialchars(substr($tour['title'], 0, 40)); ?></li>
    </ol>
</nav>

<div class="row mb-4">
    <div class="col-md-8">
        <!-- Main Image -->
        <div class="mb-4">
            <?php if (!empty($tour['imageUrl'])): ?>
                <img src="<?php echo BASE_URL . 'uploads/tours/' . htmlspecialchars($tour['imageUrl']); ?>"
                    class="img-fluid rounded mb-3" style="max-height: 400px; object-fit: cover; width: 100%;"
                    alt="<?php echo htmlspecialchars($tour['title']); ?>">
            <?php else: ?>
                <div class="bg-light d-flex justify-content-center align-items-center rounded mb-3" style="height: 400px;">
                    <i class="fas fa-image fa-4x text-muted"></i>
                </div>
            <?php endif; ?>
        </div>

        <!-- Gallery -->
        <?php if (!empty($gallery)): ?>
            <div class="mb-4">
                <h5 class="mb-3">Photo Gallery</h5>
                <div class="row g-3">
                    <?php foreach ($gallery as $image): ?>
                        <div class="col-md-4">
                            <img src="<?php echo BASE_URL . 'uploads/tours/' . htmlspecialchars($image['imageUrl']); ?>"
                                class="img-fluid rounded" alt="Gallery">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tour Info -->
        <div class="card mb-4">
            <div class="card-body">
                <h3><?php echo htmlspecialchars($tour['title']); ?></h3>

                <p class="text-muted mb-3">
                    <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($tour['destinationName']); ?>,
                    <?php echo htmlspecialchars($tour['country']); ?>
                </p>

                <div class="mb-3">
                    <span class="badge bg-primary me-2">
                        <i class="fas fa-calendar-days"></i> <?php echo $tour['duration']; ?> Days
                    </span>
                    <?php if ($tour['rating'] > 0): ?>
                        <span class="badge bg-warning">
                            <?php echo getRatingStars($tour['rating']); ?> (<?php echo $tour['reviewCount']; ?> reviews)
                        </span>
                    <?php endif; ?>
                </div>

                <h5>Description</h5>
                <p><?php echo nl2br(htmlspecialchars($tour['description'])); ?></p>
            </div>
        </div>

        <!-- Itinerary -->
        <?php if (!empty($tour['itinerary'])): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Itinerary</h5>
                </div>
                <div class="card-body">
                    <?php echo nl2br(htmlspecialchars($tour['itinerary'])); ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Amenities -->
        <?php if (!empty($amenities)): ?>
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Amenities & Facilities</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="col-md-6 mb-2">
                                <i class="fas fa-check-circle text-success"></i> <?php echo htmlspecialchars($amenity); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Reviews Section -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Customer Reviews</h5>
            </div>
            <div class="card-body">
                <?php if (!isSessionValid()): ?>
                    <p class="text-muted"><a href="<?php echo BASE_URL; ?>users/user-login.php">Login</a> to write a review
                    </p>
                <?php elseif ($hasCompletedBooking): ?>
                    <a href="<?php echo BASE_URL; ?>reviews/add-review.php?tour_id=<?php echo $tourId; ?>"
                        class="btn btn-primary mb-3">
                        <i class="fas fa-pen"></i> Write a Review
                    </a>
                <?php else: ?>
                    <p class="text-muted">You can review this tour after completing a booking.</p>
                <?php endif; ?>

                <?php if (empty($reviews)): ?>
                    <p class="text-muted">No reviews yet. Be the first to review!</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($reviews as $review): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php echo htmlspecialchars($review['firstname'] . ' ' . $review['lastname']); ?>
                                        </h6>
                                        <p class="mb-1">
                                            <?php echo getRatingStars($review['rating']); ?>
                                        </p>
                                    </div>
                                    <small class="text-muted"><?php echo formatDate($review['createdAt'], 'd M Y'); ?></small>
                                </div>
                                <p class="mb-0 mt-2"><?php echo htmlspecialchars($review['comment']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-md-4">
        <!-- Booking Card -->
        <div class="card shadow-lg mb-4 sticky-top" style="top: 20px;">
            <div class="card-body">
                <h3 class="card-title text-center mb-3"><?php echo formatCurrency($tour['price']); ?></h3>

                <p class="card-text text-center mb-3">
                    <strong>Per Person</strong>
                </p>

                <?php
                $availableSpots = $tour['maxCapacity'] - $tour['currentBookings'];
                $spotClass = $availableSpots > 5 ? 'success' : ($availableSpots > 0 ? 'warning' : 'danger');
                ?>

                <div class="alert alert-<?php echo $spotClass; ?> text-center" role="alert">
                    <strong>
                        <?php
                        if ($availableSpots > 0) {
                            echo "Only $availableSpots spots left!";
                        } else {
                            echo "Sold Out";
                        }
                        ?>
                    </strong>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block mb-2">
                        <i class="fas fa-users"></i>
                        Booked: <?php echo $tour['currentBookings']; ?> / <?php echo $tour['maxCapacity']; ?>
                    </small>
                </div>

                <?php if ($availableSpots > 0): ?>
                    <?php if (isSessionValid()): ?>
                        <a href="<?php echo BASE_URL; ?>bookings/booking-form.php?tour_id=<?php echo $tourId; ?>"
                            class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-shopping-cart"></i> Book Now
                        </a>
                    <?php else: ?>
                        <a href="<?php echo BASE_URL; ?>users/user-login.php" class="btn btn-primary w-100 mb-3">
                            <i class="fas fa-sign-in-alt"></i> Login to Book
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="btn btn-secondary w-100 mb-3" disabled>
                        <i class="fas fa-times-circle"></i> Sold Out
                    </button>
                <?php endif; ?>

                <button class="btn btn-outline-primary w-100" id="wishlistBtn"
                    onclick="toggleWishlist(<?php echo $tourId; ?>)">
                    <i class="fas fa-heart"></i> Add to Wishlist
                </button>
            </div>
        </div>

        <!-- Tour Info Card -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Tour Information</h5>

                <dl class="row">
                    <dt class="col-sm-6">Duration:</dt>
                    <dd class="col-sm-6"><strong><?php echo $tour['duration']; ?> days</strong></dd>

                    <dt class="col-sm-6">Destination:</dt>
                    <dd class="col-sm-6"><strong><?php echo htmlspecialchars($tour['destinationName']); ?></strong></dd>

                    <dt class="col-sm-6">Group Size:</dt>
                    <dd class="col-sm-6"><strong><?php echo $tour['maxCapacity']; ?> people max</strong></dd>

                    <dt class="col-sm-6">Status:</dt>
                    <dd class="col-sm-6">
                        <span class="badge bg-success">Active</span>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleWishlist(tourId) {
        const btn = document.getElementById('wishlistBtn');
        fetch('<?php echo BASE_URL; ?>wishlist/add-to-wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'tour_id=' + tourId + '&action=toggle'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.added) {
                        btn.classList.remove('btn-outline-primary');
                        btn.classList.add('btn-primary');
                        btn.innerHTML = '<i class="fas fa-heart"></i> Saved to Wishlist';
                    } else {
                        btn.classList.add('btn-outline-primary');
                        btn.classList.remove('btn-primary');
                        btn.innerHTML = '<i class="fas fa-heart"></i> Add to Wishlist';
                    }
                }
            });
    }
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>