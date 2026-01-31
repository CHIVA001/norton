<?php
/**
 * My Wishlist Page
 * Display user's saved tours
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'My Wishlist - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$userId = getCurrentUserId();
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 12;

// Count total wishlist items
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM wishlist WHERE userId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get pagination data
$pagination = getPaginationData($page, $perPage, $total);

// Fetch wishlist tours
$stmt = $conn->prepare("
    SELECT t.*, d.name as destinationName, w.addedAt
    FROM wishlist w
    JOIN tours t ON w.tourId = t.id
    JOIN destinations d ON t.destinationId = d.id
    WHERE w.userId = ?
    ORDER BY w.addedAt DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $userId, $pagination['perPage'], $pagination['offset']);
$stmt->execute();
$wishlistTours = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">
            <i class="fas fa-heart"></i> My Wishlist
        </h2>
    </div>
</div>

<?php if (empty($wishlistTours)): ?>
<div class="alert alert-info text-center py-5">
    <i class="fas fa-heart fa-3x mb-3 text-danger"></i><br>
    <strong>Your wishlist is empty</strong><br>
    <p class="text-muted">Start exploring tours and add your favorites!</p>
    <a href="<?php echo BASE_URL; ?>tours/tour-list.php" class="btn btn-primary mt-3">Browse Tours</a>
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($wishlistTours as $tour): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <!-- Image -->
            <?php if (!empty($tour['imageUrl'])): ?>
            <img src="<?php echo BASE_URL . htmlspecialchars($tour['imageUrl']); ?>" class="card-img-top"
                style="height: 250px; object-fit: cover;" alt="<?php echo htmlspecialchars($tour['title']); ?>">
            <?php else: ?>
            <div class="bg-light d-flex justify-content-center align-items-center" style="height: 250px;">
                <i class="fas fa-image fa-3x text-muted"></i>
            </div>
            <?php endif; ?>

            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo htmlspecialchars(substr($tour['title'], 0, 40)); ?></h5>

                <p class="card-text text-muted mb-2">
                    <i class="fas fa-location-dot"></i> <?php echo htmlspecialchars($tour['destinationName']); ?>
                </p>

                <p class="card-text mb-2">
                    <small class="text-muted">
                        <i class="fas fa-calendar-days"></i> <?php echo $tour['duration']; ?> days
                    </small>
                </p>

                <p class="card-text mb-2">
                    <small class="text-muted">
                        Added: <?php echo formatDate($tour['addedAt'], 'd M Y'); ?>
                    </small>
                </p>

                <p class="card-text flex-grow-1">
                    <small><?php echo htmlspecialchars(substr($tour['description'], 0, 80)); ?>...</small>
                </p>

                <?php if ($tour['rating'] > 0): ?>
                <p class="card-text mb-2">
                    <small><?php echo getRatingStars($tour['rating']); ?> (<?php echo $tour['reviewCount']; ?>)</small>
                </p>
                <?php endif; ?>

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="h5 mb-0"><?php echo formatCurrency($tour['price']); ?></span>
                </div>

                <div class="mt-3">
                    <a href="<?php echo BASE_URL; ?>tours/tour-detail.php?id=<?php echo $tour['id']; ?>"
                        class="btn btn-sm btn-primary w-100 mb-2">View Details</a>
                    <button class="btn btn-sm btn-outline-danger w-100"
                        onclick="removeFromWishlist(<?php echo $tour['id']; ?>)">
                        <i class="fas fa-trash"></i> Remove
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php echo getPaginationHTML($pagination, 'page'); ?>
<?php endif; ?>

<script>
function removeFromWishlist(tourId) {
    if (confirm('Remove this tour from your wishlist?')) {
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
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            });
    }
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>