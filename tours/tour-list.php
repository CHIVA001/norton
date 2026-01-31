<?php
/**
 * Tour List Page
 * Displays all tours with search, filter, and sorting
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Tours - Travel Agency';
require_once __DIR__ . '/../includes/header.php';

// Get filter parameters
$destination = isset($_GET['destination']) ? sanitize($_GET['destination']) : '';
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$minPrice = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$maxPrice = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 99999;
$minDuration = isset($_GET['min_duration']) ? intval($_GET['min_duration']) : 0;
$maxDuration = isset($_GET['max_duration']) ? intval($_GET['max_duration']) : 365;
$sortBy = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'createdAt';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 12;

// Build query
$query = "SELECT t.*, d.name as destinationName FROM tours t JOIN destinations d ON t.destinationId = d.id WHERE t.status = 'active'";
$countQuery = "SELECT COUNT(*) as total FROM tours t JOIN destinations d ON t.destinationId = d.id WHERE t.status = 'active'";

if (!empty($destination)) {
    $query .= " AND d.name LIKE ?";
    $countQuery .= " AND d.name LIKE ?";
}

if (!empty($search)) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $countQuery .= " AND (t.title LIKE ? OR t.description LIKE ?)";
}

if ($minPrice > 0 || $maxPrice < 99999) {
    $query .= " AND t.price BETWEEN ? AND ?";
    $countQuery .= " AND t.price BETWEEN ? AND ?";
}

if ($minDuration > 0 || $maxDuration < 365) {
    $query .= " AND t.duration BETWEEN ? AND ?";
    $countQuery .= " AND t.duration BETWEEN ? AND ?";
}

// Sort options
$sortOptions = [
    'createdAt' => 't.createdAt DESC',
    'price_asc' => 't.price ASC',
    'price_desc' => 't.price DESC',
    'rating' => 't.rating DESC',
    'duration' => 't.duration ASC'
];

$orderBy = $sortOptions[$sortBy] ?? 't.createdAt DESC';
$query .= " ORDER BY " . $orderBy . " LIMIT ? OFFSET ?";

// Prepare and execute count query
$countStmt = $conn->prepare($countQuery);
$params = [];
$types = '';

if (!empty($destination)) {
    $params[] = "%{$destination}%";
    $types .= 's';
}

if (!empty($search)) {
    $searchParam = "%{$search}%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'ss';
}

if ($minPrice > 0 || $maxPrice < 99999) {
    $params[] = $minPrice;
    $params[] = $maxPrice;
    $types .= 'dd';
}

if ($minDuration > 0 || $maxDuration < 365) {
    $params[] = $minDuration;
    $params[] = $maxDuration;
    $types .= 'ii';
}

if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}

$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

// Get pagination data
$pagination = getPaginationData($page, $perPage, $total);

// Prepare and execute main query
$stmt = $conn->prepare($query);
$queryParams = $params;
$queryParams[] = $pagination['perPage'];
$queryParams[] = $pagination['offset'];
$queryTypes = $types . 'ii';

if (!empty($queryParams)) {
    $stmt->bind_param($queryTypes, ...$queryParams);
}

$stmt->execute();
$tours = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique destinations for filter
$stmt = $conn->prepare("SELECT DISTINCT name FROM destinations ORDER BY name ASC");
$stmt->execute();
$destinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">
            <i class="fas fa-suitcase-rolling"></i> Available Tours
        </h2>
    </div>
</div>

<!-- Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <label for="search" class="form-label">Search Tours</label>
                <input type="text" class="form-control" id="search" name="search" 
                       placeholder="Tour name or description" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            
            <div class="col-md-6">
                <label for="destination" class="form-label">Destination</label>
                <select class="form-select" id="destination" name="destination">
                    <option value="">All Destinations</option>
                    <?php foreach ($destinations as $dest): ?>
                        <option value="<?php echo htmlspecialchars($dest['name']); ?>" 
                                <?php echo $destination === $dest['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($dest['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label for="min_price" class="form-label">Min Price</label>
                <input type="number" class="form-control" id="min_price" name="min_price" 
                       placeholder="0" value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>" min="0">
            </div>
            
            <div class="col-md-3">
                <label for="max_price" class="form-label">Max Price</label>
                <input type="number" class="form-control" id="max_price" name="max_price" 
                       placeholder="99999" value="<?php echo $maxPrice < 99999 ? $maxPrice : ''; ?>" min="0">
            </div>
            
            <div class="col-md-3">
                <label for="min_duration" class="form-label">Min Days</label>
                <input type="number" class="form-control" id="min_duration" name="min_duration" 
                       placeholder="1" value="<?php echo $minDuration > 0 ? $minDuration : ''; ?>" min="1">
            </div>
            
            <div class="col-md-3">
                <label for="max_duration" class="form-label">Max Days</label>
                <input type="number" class="form-control" id="max_duration" name="max_duration" 
                       placeholder="365" value="<?php echo $maxDuration < 365 ? $maxDuration : ''; ?>" min="1">
            </div>
            
            <div class="col-md-6">
                <label for="sort" class="form-label">Sort By</label>
                <select class="form-select" id="sort" name="sort">
                    <option value="createdAt" <?php echo $sortBy === 'createdAt' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="price_asc" <?php echo $sortBy === 'price_asc' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="price_desc" <?php echo $sortBy === 'price_desc' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="rating" <?php echo $sortBy === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                    <option value="duration" <?php echo $sortBy === 'duration' ? 'selected' : ''; ?>>Shortest Duration</option>
                </select>
            </div>
            
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Filter & Search</button>
                <a href="tour-list.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Tours Grid -->
<?php if (empty($tours)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-search fa-2x mb-2"></i><br>
        No tours found matching your criteria. Try adjusting your filters.
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($tours as $tour): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if (!empty($tour['imageUrl'])): ?>
                        <img src="<?php echo BASE_URL . htmlspecialchars($tour['imageUrl']); ?>" 
                             class="card-img-top" style="height: 250px; object-fit: cover;" alt="<?php echo htmlspecialchars($tour['title']); ?>">
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
                        
                        <p class="card-text flex-grow-1">
                            <small><?php echo htmlspecialchars(substr($tour['description'], 0, 80)); ?>...</small>
                        </p>
                        
                        <?php if ($tour['rating'] > 0): ?>
                            <p class="card-text mb-2">
                                <small><?php echo getRatingStars($tour['rating']); ?> (<?php echo $tour['reviewCount']; ?> reviews)</small>
                            </p>
                        <?php endif; ?>
                        
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="h5 mb-0"><?php echo formatCurrency($tour['price']); ?></span>
                            <?php 
                            $availableSpots = $tour['maxCapacity'] - $tour['currentBookings'];
                            $spotClass = $availableSpots > 5 ? 'success' : ($availableSpots > 0 ? 'warning' : 'danger');
                            ?>
                            <small class="text-<?php echo $spotClass; ?>">
                                <?php echo $availableSpots > 0 ? "$availableSpots spots left" : "Sold Out"; ?>
                            </small>
                        </div>
                        
                        <a href="tour-detail.php?id=<?php echo $tour['id']; ?>" class="btn btn-primary mt-3">View Details</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php echo getPaginationHTML($pagination, 'page'); ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
