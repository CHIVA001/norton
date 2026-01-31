<?php
/**
 * Destinations Page
 * Displays all destinations with filters
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Destinations - Travel Agency';
require_once __DIR__ . '/../includes/header.php';

// Get filter parameters
$country = isset($_GET['country']) ? sanitize($_GET['country']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 12;

// Build query
$query = "SELECT * FROM destinations WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM destinations WHERE 1=1";

if (!empty($country)) {
    $query .= " AND country LIKE ?";
    $countQuery .= " AND country LIKE ?";
}

$query .= " ORDER BY name ASC LIMIT ? OFFSET ?";

// Get total count
$stmt = $conn->prepare($countQuery);
if (!empty($country)) {
    $countryParam = "%{$country}%";
    $stmt->bind_param("s", $countryParam);
}
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Get pagination data
$pagination = getPaginationData($page, $perPage, $total);

// Fetch destinations
$stmt = $conn->prepare($query);
if (!empty($country)) {
    $countryParam = "%{$country}%";
    $stmt->bind_param("sii", $countryParam, $pagination['perPage'], $pagination['offset']);
} else {
    $stmt->bind_param("ii", $pagination['perPage'], $pagination['offset']);
}
$stmt->execute();
$destinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique countries for filter
$stmt = $conn->prepare("SELECT DISTINCT country FROM destinations ORDER BY country ASC");
$stmt->execute();
$countries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">
            <i class="fas fa-map-marked-alt"></i> Explore Destinations
        </h2>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="country" class="form-label">Country</label>
                <select class="form-select" id="country" name="country">
                    <option value="">All Countries</option>
                    <?php foreach ($countries as $dest): ?>
                    <option value="<?php echo htmlspecialchars($dest['country']); ?>"
                        <?php echo $country === $dest['country'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($dest['country']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="destinations.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Destinations Grid -->
<?php if (empty($destinations)): ?>
<div class="alert alert-info text-center">
    <i class="fas fa-search fa-2x mb-2"></i><br>
    No destinations found.
</div>
<?php else: ?>
<div class="row">
    <?php foreach ($destinations as $destination): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100 shadow-sm">
            <?php if (!empty($destination['imageUrl'])): ?>
            <img src="<?php echo BASE_URL . htmlspecialchars($destination['imageUrl']); ?>" class="card-img-top"
                style="height: 250px; object-fit: cover;" alt="<?php echo htmlspecialchars($destination['name']); ?>">
            <?php else: ?>
            <div class="bg-light d-flex justify-content-center align-items-center" style="height: 250px;">
                <i class="fas fa-image fa-3x text-muted"></i>
            </div>
            <?php endif; ?>

            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?php echo htmlspecialchars($destination['name']); ?></h5>
                <p class="card-text text-muted mb-2">
                    <i class="fas fa-flag"></i> <?php echo htmlspecialchars($destination['country']); ?>
                </p>

                <p class="card-text flex-grow-1">
                    <?php echo nl2br(htmlspecialchars(substr($destination['description'], 0, 100))); ?>...
                </p>

                <?php if (!empty($destination['bestTimeToVisit'])): ?>
                <p class="card-text mb-3">
                    <small class="text-muted">
                        <i class="fas fa-calendar-alt"></i> Best Time:
                        <?php echo htmlspecialchars($destination['bestTimeToVisit']); ?>
                    </small>
                </p>
                <?php endif; ?>

                <a href="<?php echo BASE_URL; ?>tours/tour-list.php?destination=<?php echo urlencode($destination['name']); ?>"
                    class="btn btn-primary mt-auto">View Tours</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php echo getPaginationHTML($pagination, 'page'); ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>