<?php
/**
 * Manage Tours Page
 * CRUD operations for tours
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Manage Tours - Travel Agency';

// Check admin authentication
require_once __DIR__ . '/../includes/admin-auth-check.php';

$adminId = getCurrentUserId();
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Count tours
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tours");
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$pagination = getPaginationData($page, $perPage, $total);

// Fetch tours
$stmt = $conn->prepare("
    SELECT t.*, d.name as destinationName FROM tours t
    JOIN destinations d ON t.destinationId = d.id
    ORDER BY t.createdAt DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $pagination['perPage'], $pagination['offset']);
$stmt->execute();
$tours = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-suitcase-rolling"></i> Manage Tours</h2>
    <a href="add-tour.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Tour
    </a>
</div>

<?php if (empty($tours)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-inbox fa-2x mb-2"></i><br>
        No tours found. <a href="add-tour.php">Add one now</a>
    </div>
<?php else: ?>
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Destination</th>
                        <th>Duration</th>
                        <th>Price</th>
                        <th>Capacity</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tours as $tour): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars(substr($tour['title'], 0, 30)); ?></strong></td>
                            <td><?php echo htmlspecialchars($tour['destinationName']); ?></td>
                            <td><?php echo $tour['duration']; ?> days</td>
                            <td><?php echo formatCurrency($tour['price']); ?></td>
                            <td>
                                <small class="text-muted">
                                    <?php echo $tour['currentBookings']; ?>/<?php echo $tour['maxCapacity']; ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($tour['rating'] > 0): ?>
                                    <small><?php echo getRatingStars($tour['rating']); ?></small>
                                <?php else: ?>
                                    <small class="text-muted">No ratings</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $tour['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($tour['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit-tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete-tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this tour?')">Delete</a>
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