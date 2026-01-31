<?php
/**
 * Manage Destinations Page
 * CRUD operations for destinations
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Manage Destinations - Travel Agency';

// Check admin authentication
require_once __DIR__ . '/../includes/admin-auth-check.php';

$adminId = getCurrentUserId();
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Count destinations
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM destinations");
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$pagination = getPaginationData($page, $perPage, $total);

// Fetch destinations
$stmt = $conn->prepare("
    SELECT * FROM destinations
    ORDER BY createdAt DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $pagination['perPage'], $pagination['offset']);
$stmt->execute();
$destinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-globe"></i> Manage Destinations</h2>
    <a href="add-destination.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Destination
    </a>
</div>

<?php if (empty($destinations)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-inbox fa-2x mb-2"></i><br>
        No destinations found. <a href="add-destination.php">Add one now</a>
    </div>
<?php else: ?>
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Country</th>
                        <th>Best Time to Visit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($destinations as $dest): ?>
                        <tr>
                            <td>
                                <?php if (!empty($dest['imageUrl'])): ?>
                                    <img src="<?php echo BASE_URL . htmlspecialchars($dest['imageUrl']); ?>"
                                        style="height: 50px; width: 50px; object-fit: cover;" class="rounded">
                                <?php else: ?>
                                    <i class="fas fa-image text-muted"></i>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($dest['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($dest['country']); ?></td>
                            <td><small
                                    class="text-muted"><?php echo htmlspecialchars($dest['bestTimeToVisit'] ?? 'N/A'); ?></small>
                            </td>
                            <td>
                                <a href="edit-destination.php?id=<?php echo $dest['id']; ?>"
                                    class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete-destination.php?id=<?php echo $dest['id']; ?>" class="btn btn-sm btn-danger"
                                    onclick="return confirm('Delete this destination?')">Delete</a>
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