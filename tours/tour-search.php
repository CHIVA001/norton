<?php
/**
 * Tour Search Page
 * Advanced search functionality
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Search Tours - Travel Agency';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-4">
            <i class="fas fa-search"></i> Advanced Tour Search
        </h2>
    </div>
</div>

<!-- Advanced Search Form -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="tour-list.php" class="row g-3">
            <div class="col-md-12">
                <label for="search" class="form-label">Search by Tour Name or Description</label>
                <input type="text" class="form-control form-control-lg" id="search" name="search"
                    placeholder="Enter tour name, country, or description">
            </div>

            <div class="col-md-4">
                <label for="destination" class="form-label">Destination</label>
                <select class="form-select" id="destination" name="destination">
                    <option value="">All Destinations</option>
                    <?php
                    $stmt = $conn->prepare("SELECT DISTINCT name FROM destinations ORDER BY name ASC");
                    $stmt->execute();
                    $destinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt->close();

                    foreach ($destinations as $dest) {
                        echo '<option value="' . htmlspecialchars($dest['name']) . '">' . htmlspecialchars($dest['name']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-4">
                <label for="budget" class="form-label">Budget Range</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="min_price" name="min_price" placeholder="Min" min="0">
                    <span class="input-group-text">-</span>
                    <input type="number" class="form-control" id="max_price" name="max_price" placeholder="Max" min="0">
                </div>
            </div>

            <div class="col-md-4">
                <label for="duration" class="form-label">Tour Duration (Days)</label>
                <div class="input-group">
                    <input type="number" class="form-control" id="min_duration" name="min_duration" placeholder="Min"
                        min="1">
                    <span class="input-group-text">-</span>
                    <input type="number" class="form-control" id="max_duration" name="max_duration" placeholder="Max"
                        min="1">
                </div>
            </div>

            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-search"></i> Search Tours
                </button>
                <a href="tour-list.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-redo"></i> Reset Search
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Search Tips -->
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-lightbulb text-warning"></i> Search Tips</h5>
                <ul>
                    <li>Use specific keywords for better results</li>
                    <li>Filter by destination to narrow down options</li>
                    <li>Set budget and duration limits</li>
                    <li>Sort results by price or rating</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-star text-warning"></i> Popular Tours</h5>
                <?php
                $stmt = $conn->prepare("
                    SELECT id, title, rating, reviewCount
                    FROM tours
                    WHERE status = 'active'
                    ORDER BY rating DESC, reviewCount DESC
                    LIMIT 5
                ");
                $stmt->execute();
                $popular = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
                $stmt->close();

                foreach ($popular as $tour) {
                    echo '<div class="mb-2">';
                    echo '<a href="tour-detail.php?id=' . $tour['id'] . '">' . htmlspecialchars(substr($tour['title'], 0, 25)) . '</a>';
                    echo '<br><small class="text-muted">' . getRatingStars($tour['rating']) . ' (' . $tour['reviewCount'] . ' reviews)</small>';
                    echo '</div>';
                }
                ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-info-circle text-info"></i> Need Help?</h5>
                <p>Contact our travel experts:</p>
                <p>
                    <i class="fas fa-phone"></i> +1 (555) 123-4567<br>
                    <i class="fas fa-envelope"></i> support@travel.com<br>
                    <i class="fas fa-clock"></i> 24/7 Customer Support
                </p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>