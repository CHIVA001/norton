<?php
/**
 * Home Page
 * Main landing page
 */

session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Home - Travel Agency';
require_once __DIR__ . '/includes/header.php';

// Get featured tours
$stmt = $conn->prepare("
    SELECT t.*, d.name as destinationName
    FROM tours t
    JOIN destinations d ON t.destinationId = d.id
    WHERE t.status = 'active'
    ORDER BY t.rating DESC, t.reviewCount DESC
    LIMIT 6
");
$stmt->execute();
$featuredTours = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get popular destinations
$stmt = $conn->prepare("
    SELECT d.*, (SELECT COUNT(*) FROM tours WHERE destinationId = d.id) as tourCount
    FROM destinations d
    LIMIT 6
");
$stmt->execute();
$destinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!-- Hero Section -->
<div class="row mb-5">
    <div class="col-md-12">
        <div class="bg-primary text-white p-5 rounded-3">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-4 fw-bold mb-3">Discover the World</h1>
                    <p class="lead mb-4">Explore amazing destinations and create unforgettable memories with our curated
                        travel packages.</p>
                    <div class="d-flex gap-2">
                        <a href="<?php echo BASE_URL; ?>tours/tour-list.php" class="btn btn-light btn-lg">
                            <i class="fas fa-compass"></i> Browse Tours
                        </a>
                        <a href="<?php echo BASE_URL; ?>tours/tour-search.php" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-search"></i> Search Tours
                        </a>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-globe fa-10x opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search Bar -->
<div class="row mb-5">
    <div class="col-md-12">
        <div class="card shadow-lg">
            <div class="card-body">
                <form method="GET" action="<?php echo BASE_URL; ?>tours/tour-list.php" class="row g-3">
                    <div class="col-md-5">
                        <label for="search" class="form-label">Search Destination</label>
                        <input type="text" class="form-control form-control-lg" id="search" name="search"
                            placeholder="Where do you want to go?">
                    </div>
                    <div class="col-md-3">
                        <label for="budget" class="form-label">Budget Range</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="min_price" name="min_price" placeholder="Min">
                            <input type="number" class="form-control" id="max_price" name="max_price" placeholder="Max">
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Popular Destinations -->
<div class="row mb-5">
    <div class="col-md-12">
        <h2 class="mb-4"><i class="fas fa-map-pin"></i> Popular Destinations</h2>
    </div>

    <?php foreach ($destinations as $dest): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($dest['imageUrl'])): ?>
                    <img src="<?php echo BASE_URL . 'uploads/tours/' . htmlspecialchars($dest['imageUrl']); ?>"
                        class="card-img-top" style="height: 250px; object-fit: cover;"
                        alt="<?php echo htmlspecialchars($dest['name']); ?>">
                <?php else: ?>
                    <div class="bg-light d-flex justify-content-center align-items-center" style="height: 250px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>

                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($dest['name']); ?></h5>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($dest['country']); ?></p>
                    <p class="card-text">
                        <small class="text-muted"><?php echo $dest['tourCount']; ?> tours available</small>
                    </p>

                    <a href="<?php echo BASE_URL; ?>tours/tour-list.php?destination=<?php echo urlencode($dest['name']); ?>"
                        class="btn btn-primary">View Tours</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Featured Tours -->
<div class="row mb-5">
    <div class="col-md-12">
        <h2 class="mb-4"><i class="fas fa-star"></i> Featured Tours</h2>
    </div>

    <?php foreach ($featuredTours as $tour): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <?php if (!empty($tour['imageUrl'])): ?>
                    <img src="<?php echo BASE_URL . 'uploads/tours/' . htmlspecialchars($tour['imageUrl']); ?>"
                        class="card-img-top" style="height: 250px; object-fit: cover;"
                        alt="<?php echo htmlspecialchars($tour['title']); ?>">
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

                    <?php if ($tour['rating'] > 0): ?>
                        <p class="card-text mb-2">
                            <small><?php echo getRatingStars($tour['rating']); ?> (<?php echo $tour['reviewCount']; ?>)</small>
                        </p>
                    <?php endif; ?>

                    <p class="card-text flex-grow-1">
                        <small><?php echo htmlspecialchars(substr($tour['description'], 0, 80)); ?>...</small>
                    </p>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <span class="h5 mb-0"><?php echo formatCurrency($tour['price']); ?></span>
                    </div>

                    <a href="<?php echo BASE_URL; ?>tours/tour-detail.php?id=<?php echo $tour['id']; ?>"
                        class="btn btn-primary mt-3">View Details</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Why Choose Us -->
<div class="row mb-5">
    <div class="col-md-12">
        <h2 class="mb-4 text-center">Why Choose Us</h2>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Secure Booking</h5>
                <p class="card-text">Your payment and personal information are always secure with us.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h5 class="card-title">24/7 Support</h5>
                <p class="card-text">Our dedicated team is always here to help you with any questions.</p>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-award fa-3x text-primary mb-3"></i>
                <h5 class="card-title">Best Deals</h5>
                <p class="card-text">Get the best prices on tours and travel packages worldwide.</p>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="row">
    <div class="col-md-12">
        <div class="bg-success text-white p-5 rounded-3 text-center">
            <h3 class="mb-3">Ready for Your Adventure?</h3>
            <p class="lead mb-4">Start exploring amazing destinations today and book your dream vacation!</p>
            <a href="<?php echo BASE_URL; ?>tours/tour-list.php" class="btn btn-light btn-lg">
                <i class="fas fa-suitcase-rolling"></i> Explore All Tours
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>