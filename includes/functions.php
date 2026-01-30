<?php
/**
 * Common PHP Functions
 * Includes sanitization, validation, and utility functions
 */

/**
 * Sanitize input data
 * @param string $data - Input data to sanitize
 * @return string - Sanitized data
 */
function sanitize($data)
{
    global $conn;
    return $conn->real_escape_string(trim(htmlspecialchars($data)));
}

/**
 * Validate email format
 * @param string $email - Email to validate
 * @return bool - True if valid, false otherwise
 */
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 * Minimum 8 characters, at least one uppercase, one lowercase, one number
 * @param string $password - Password to validate
 * @return bool - True if valid, false otherwise
 */
function validatePassword($password)
{
    return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $password);
}

/**
 * Hash password using PHP's password_hash
 * @param string $password - Plain text password
 * @return string - Hashed password
 */
function hashPassword($password)
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 * @param string $password - Plain text password
 * @param string $hash - Password hash
 * @return bool - True if match, false otherwise
 */
function verifyPassword($password, $hash)
{
    return password_verify($password, $hash);
}

/**
 * Generate unique booking code
 * Format: TRA-YYYYMMDD-RANDOM
 * @return string - Unique booking code
 */
function generateBookingCode()
{
    $prefix = 'TRA';
    $date = date('Ymd');
    $random = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));
    return $prefix . '-' . $date . '-' . $random;
}

/**
 * Generate unique transaction ID
 * Format: TXN-TIMESTAMP-RANDOM
 * @return string - Unique transaction ID
 */
function generateTransactionId()
{
    return 'TXN-' . time() . '-' . bin2hex(random_bytes(4));
}

/**
 * Validate phone number (basic validation)
 * @param string $phone - Phone number
 * @return bool - True if valid format
 */
function validatePhone($phone)
{
    return preg_match('/^[+]?[(]?[0-9]{3}[)]?[-\s.]?[0-9]{3}[-\s.]?[0-9]{4,6}$/', str_replace(' ', '', $phone));
}

/**
 * Validate numeric input
 * @param mixed $value - Value to validate
 * @return bool - True if numeric
 */
function isNumeric($value)
{
    return is_numeric($value) && $value > 0;
}

/**
 * Format currency for display
 * @param float $amount - Amount to format
 * @return string - Formatted currency
 */
function formatCurrency($amount)
{
    return '$' . number_format((float) $amount, 2, '.', ',');
}

/**
 * Format date for display
 * @param string $date - Date string
 * @param string $format - Date format (default: 'd M Y')
 * @return string - Formatted date
 */
function formatDate($date, $format = 'd M Y')
{
    if (empty($date))
        return '';
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return $date;
    }
}

/**
 * Get user role badge HTML
 * @param string $role - User role
 * @return string - HTML badge
 */
function getUserRoleBadge($role)
{
    $badgeClass = $role === 'admin' ? 'badge bg-danger' : 'badge bg-primary';
    $roleDisplay = ucfirst($role);
    return "<span class='{$badgeClass}'>{$roleDisplay}</span>";
}

/**
 * Get booking status badge HTML
 * @param string $status - Booking status
 * @return string - HTML badge
 */
function getBookingStatusBadge($status)
{
    $badgeClasses = [
        'pending' => 'badge bg-warning',
        'confirmed' => 'badge bg-info',
        'completed' => 'badge bg-success',
        'cancelled' => 'badge bg-danger'
    ];

    $badgeClass = $badgeClasses[$status] ?? 'badge bg-secondary';
    $statusDisplay = ucfirst($status);
    return "<span class='{$badgeClass}'>{$statusDisplay}</span>";
}

/**
 * Get payment status badge HTML
 * @param string $status - Payment status
 * @return string - HTML badge
 */
function getPaymentStatusBadge($status)
{
    $badgeClasses = [
        'unpaid' => 'badge bg-danger',
        'paid' => 'badge bg-success',
        'refunded' => 'badge bg-warning'
    ];

    $badgeClass = $badgeClasses[$status] ?? 'badge bg-secondary';
    $statusDisplay = ucfirst($status);
    return "<span class='{$badgeClass}'>{$statusDisplay}</span>";
}

/**
 * Get average rating stars HTML
 * @param float $rating - Rating value (0-5)
 * @return string - HTML stars
 */
function getRatingStars($rating)
{
    $fullStars = floor($rating);
    $hasHalfStar = ($rating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);

    $stars = str_repeat('⭐', $fullStars);
    if ($hasHalfStar)
        $stars .= '⚬';
    $stars .= str_repeat('☆', $emptyStars);

    return "<span title='{$rating}/5'>$stars</span>";
}

/**
 * Check user session validity
 * @return bool - True if session valid
 */
function isSessionValid()
{
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current logged-in user ID
 * @return int|null - User ID or null
 */
function getCurrentUserId()
{
    return isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
}

/**
 * Get current logged-in user email
 * @return string|null - User email or null
 */
function getCurrentUserEmail()
{
    return isset($_SESSION['user_email']) ? $_SESSION['user_email'] : null;
}

/**
 * Get current logged-in user role
 * @return string|null - User role or null
 */
function getCurrentUserRole()
{
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

/**
 * Check if current user is admin
 * @return bool - True if admin
 */
function isAdmin()
{
    return getCurrentUserRole() === 'admin';
}

/**
 * Redirect to page with message
 * @param string $page - Page URL to redirect
 * @param string $message - Message to display
 * @param string $type - Message type (success, error, warning, info)
 */
function redirectWithMessage($page, $message, $type = 'info')
{
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: " . BASE_URL . $page);
    exit();
}

/**
 * Display session message if exists
 * @return string - HTML for message display
 */
function displayMessage()
{
    if (isset($_SESSION['message'])) {
        $message = $_SESSION['message'];
        $type = $_SESSION['message_type'] ?? 'info';

        $alertClass = 'alert-' . $type;
        if ($type === 'error')
            $alertClass = 'alert-danger';

        $html = "<div class='alert $alertClass alert-dismissible fade show' role='alert'>";
        $html .= $message;
        $html .= "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
        $html .= "</div>";

        unset($_SESSION['message']);
        unset($_SESSION['message_type']);

        return $html;
    }
    return '';
}

/**
 * Paginate array or query results
 * @param int $page - Current page number
 * @param int $perPage - Items per page
 * @param int $total - Total items
 * @return array - Pagination data
 */
function getPaginationData($page, $perPage, $total)
{
    $page = max(1, intval($page));
    $totalPages = ceil($total / $perPage);
    $offset = ($page - 1) * $perPage;

    return [
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => $totalPages,
        'offset' => $offset,
        'hasNext' => $page < $totalPages,
        'hasPrev' => $page > 1
    ];
}

/**
 * Get pagination HTML
 * @param array $pagination - Pagination data
 * @param string $queryParam - Query parameter name for page
 * @return string - HTML pagination
 */
function getPaginationHTML($pagination, $queryParam = 'page')
{
    if ($pagination['totalPages'] <= 1) {
        return '';
    }

    $html = "<nav aria-label='Page navigation'>";
    $html .= "<ul class='pagination justify-content-center'>";

    // Previous button
    if ($pagination['hasPrev']) {
        $prevPage = $pagination['page'] - 1;
        $html .= "<li class='page-item'><a class='page-link' href='?" . $queryParam . "=" . $prevPage . "'>Previous</a></li>";
    } else {
        $html .= "<li class='page-item disabled'><span class='page-link'>Previous</span></li>";
    }

    // Page numbers
    for ($i = 1; $i <= $pagination['totalPages']; $i++) {
        $active = $i === $pagination['page'] ? 'active' : '';
        $html .= "<li class='page-item $active'><a class='page-link' href='?" . $queryParam . "=" . $i . "'>$i</a></li>";
    }

    // Next button
    if ($pagination['hasNext']) {
        $nextPage = $pagination['page'] + 1;
        $html .= "<li class='page-item'><a class='page-link' href='?" . $queryParam . "=" . $nextPage . "'>Next</a></li>";
    } else {
        $html .= "<li class='page-item disabled'><span class='page-link'>Next</span></li>";
    }

    $html .= "</ul></nav>";
    return $html;
}

/**
 * Log admin activity
 * @param int $adminId - Admin user ID
 * @param string $action - Action description
 * @param string $entityType - Type of entity (destinations, tours, bookings, etc)
 * @param int $entityId - Entity ID
 * @param string $details - Additional details
 */
function logAdminActivity($adminId, $action, $entityType = null, $entityId = null, $details = null)
{
    global $conn;

    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';

    $stmt = $conn->prepare("
        INSERT INTO activityLog (adminId, action, entityType, entityId, details, ipAddress)
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {
        $stmt->bind_param("isssss", $adminId, $action, $entityType, $entityId, $details, $ipAddress);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Handle file upload
 * @param array $file - $_FILES array element
 * @param string $uploadDir - Directory to upload file
 * @param array $allowedTypes - Allowed MIME types
 * @param int $maxSize - Maximum file size in bytes
 * @return array - ['success' => bool, 'filename' => string, 'error' => string]
 */
function handleFileUpload($file, $uploadDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'], $maxSize = 5242880)
{
    // Ensure upload directory exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Validate file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'File upload error: ' . $file['error']];
    }

    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds maximum limit'];
    }

    if (!in_array($file['type'], $allowedTypes)) {
        return ['success' => false, 'error' => 'File type not allowed'];
    }

    // Generate unique filename
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . $ext;
    $filepath = $uploadDir . '/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'error' => 'Failed to move uploaded file'];
}

?>