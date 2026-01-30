<?php
/**
 * Manage Users Page
 * View and manage users
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Manage Users - Travel Agency';

// Check admin authentication
require_once __DIR__ . '/../includes/admin-auth-check.php';

$adminId = getCurrentUserId();
$roleFilter = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$perPage = 10;

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM users WHERE 1=1";

if (!empty($roleFilter) && in_array($roleFilter, ['user', 'admin'])) {
    $query .= " AND role = ?";
    $countQuery .= " AND role = ?";
}

$query .= " ORDER BY createdAt DESC LIMIT ? OFFSET ?";

// Count
$countStmt = $conn->prepare($countQuery);
if (!empty($roleFilter)) {
    $countStmt->bind_param("s", $roleFilter);
}
$countStmt->execute();
$total = $countStmt->get_result()->fetch_assoc()['total'];
$countStmt->close();

$pagination = getPaginationData($page, $perPage, $total);

// Fetch users
$stmt = $conn->prepare($query);
if (!empty($roleFilter)) {
    $stmt->bind_param("sii", $roleFilter, $pagination['perPage'], $pagination['offset']);
} else {
    $stmt->bind_param("ii", $pagination['perPage'], $pagination['offset']);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="mb-4 d-flex justify-content-between align-items-center">
    <h2><i class="fas fa-users"></i> Manage Users</h2>
</div>

<!-- Filter Buttons -->
<div class="mb-4">
    <div class="btn-group" role="group">
        <a href="manage-users.php" class="btn btn-outline-primary <?php echo empty($roleFilter) ? 'active' : ''; ?>">
            All Users
        </a>
        <a href="manage-users.php?role=user"
            class="btn btn-outline-primary <?php echo $roleFilter === 'user' ? 'active' : ''; ?>">
            Regular Users
        </a>
        <a href="manage-users.php?role=admin"
            class="btn btn-outline-primary <?php echo $roleFilter === 'admin' ? 'active' : ''; ?>">
            Admins
        </a>
    </div>
</div>

<?php if (empty($users)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-inbox fa-2x mb-2"></i><br>
        No users found
    </div>
<?php else: ?>
    <div class="card shadow">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Country</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if (!empty($user['profileImage'])): ?>
                                        <img src="<?php echo BASE_URL . 'uploads/profiles/' . htmlspecialchars($user['profileImage']); ?>"
                                            style="height: 40px; width: 40px; object-fit: cover;" class="rounded-circle me-2">
                                    <?php else: ?>
                                        <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2"
                                            style="width: 40px; height: 40px;">
                                            <i class="fas fa-user text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                    <strong><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></strong>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><small class="text-muted"><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></small></td>
                            <td><?php echo htmlspecialchars($user['country'] ?? 'N/A'); ?></td>
                            <td><?php echo getUserRoleBadge($user['role']); ?></td>
                            <td><small class="text-muted"><?php echo formatDate($user['createdAt'], 'd M Y'); ?></small></td>
                            <td>
                                <a href="<?php echo BASE_URL; ?>users/user-profile.php?user_id=<?php echo $user['id']; ?>"
                                    class="btn btn-sm btn-outline-primary">View</a>
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