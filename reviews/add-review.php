<?php
/**
 * Add Review Page
 * Allows users to submit reviews for completed bookings
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Add Review - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$tourId = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0;
$userId = getCurrentUserId();

if ($tourId <= 0) {
    header("Location: " . BASE_URL . "tours/tour-list.php");
    exit();
}

// Fetch tour details
$stmt = $conn->prepare("SELECT * FROM tours WHERE id = ?");
$stmt->bind_param("i", $tourId);
$stmt->execute();
$tour = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tour) {
    header("Location: " . BASE_URL . "tours/tour-list.php");
    exit();
}

// Check if user has completed booking for this tour
$stmt = $conn->prepare("
    SELECT id, bookingCode FROM bookings
    WHERE userId = ? AND tourId = ? AND status = 'completed'
    LIMIT 1
");
$stmt->bind_param("ii", $userId, $tourId);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$booking) {
    $_SESSION['message'] = 'You can only review tours you have completed';
    $_SESSION['message_type'] = 'warning';
    header("Location: " . BASE_URL . "tours/tour-detail.php?id=$tourId");
    exit();
}

$errors = [];
$successMessage = '';

// Check if already reviewed
$stmt = $conn->prepare("SELECT id, rating, comment FROM reviews WHERE userId = ? AND bookingId = ? LIMIT 1");
$stmt->bind_param("ii", $userId, $booking['id']);
$stmt->execute();
$existingReview = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comment = isset($_POST['comment']) ? sanitize($_POST['comment']) : '';

    // Validation
    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Rating must be between 1 and 5 stars';
    }
    if (empty($comment)) {
        $errors[] = 'Comment is required';
    }
    if (strlen($comment) < 10) {
        $errors[] = 'Comment must be at least 10 characters long';
    }

    // Submit review if no errors
    if (empty($errors)) {
        if ($existingReview) {
            // Update existing review
            $stmt = $conn->prepare("
                UPDATE reviews
                SET rating = ?, comment = ?
                WHERE id = ?
            ");
            $stmt->bind_param("isi", $rating, $comment, $existingReview['id']);
            $message = 'Review updated successfully!';
        } else {
            // Insert new review
            $stmt = $conn->prepare("
                INSERT INTO reviews (userId, tourId, bookingId, rating, comment)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iiiis", $userId, $tourId, $booking['id'], $rating, $comment);
            $message = 'Review submitted successfully!';
        }

        if ($stmt->execute()) {
            $stmt->close();

            // Recalculate tour rating
            $ratingStmt = $conn->prepare("
                SELECT AVG(rating) as avgRating, COUNT(*) as reviewCount
                FROM reviews
                WHERE tourId = ?
            ");
            $ratingStmt->bind_param("i", $tourId);
            $ratingStmt->execute();
            $ratingData = $ratingStmt->get_result()->fetch_assoc();
            $ratingStmt->close();

            // Update tour rating
            $updateStmt = $conn->prepare("
                UPDATE tours
                SET rating = ?, reviewCount = ?
                WHERE id = ?
            ");
            $updateStmt->bind_param("dii", $ratingData['avgRating'], $ratingData['reviewCount'], $tourId);
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['message'] = $message;
            $_SESSION['message_type'] = 'success';
            header("Location: " . BASE_URL . "tours/tour-detail.php?id=$tourId");
            exit();
        } else {
            $errors[] = 'Failed to submit review. Please try again.';
        }
        $stmt->close();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="fas fa-pen"></i>
                    <?php echo $existingReview ? 'Edit Your Review' : 'Add a Review'; ?>
                </h4>
            </div>
            <div class="card-body">
                <!-- Tour Info -->
                <div class="alert alert-info mb-4">
                    <h5><?php echo htmlspecialchars($tour['title']); ?></h5>
                    <p class="mb-0">Booking: <strong><?php echo htmlspecialchars($booking['bookingCode']); ?></strong>
                    </p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="mb-4">
                        <label class="form-label">Rating *</label>
                        <div class="rating-input">
                            <div class="btn-group" role="group">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <input type="radio" class="btn-check" name="rating" id="rating<?php echo $i; ?>"
                                        value="<?php echo $i; ?>" <?php echo ($existingReview && $existingReview['rating'] == $i) ? 'checked' : ''; ?>>
                                    <label class="btn btn-outline-warning" for="rating<?php echo $i; ?>">
                                        <i class="fas fa-star"></i> <?php echo $i; ?>
                                    </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Your Review *</label>
                        <textarea class="form-control" id="comment" name="comment" rows="6" required
                            placeholder="Share your experience with this tour..."><?php
                            if ($existingReview) {
                                echo htmlspecialchars($existingReview['comment']);
                            } else {
                                echo htmlspecialchars($_POST['comment'] ?? '');
                            }
                            ?></textarea>
                        <small class="text-muted">Minimum 10 characters</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i>
                        <?php echo $existingReview ? 'Update Review' : 'Submit Review'; ?>
                    </button>
                    <a href="<?php echo BASE_URL; ?>tours/tour-detail.php?id=<?php echo $tourId; ?>"
                        class="btn btn-outline-secondary btn-lg">Cancel</a>
                </form>
            </div>
        </div>

        <!-- Help Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Tips for Writing a Great Review</h5>
            </div>
            <div class="card-body">
                <ul>
                    <li>Be specific about what you liked or disliked</li>
                    <li>Mention the guide, accommodations, food, and activities</li>
                    <li>Share tips for future travelers</li>
                    <li>Be honest and fair in your rating</li>
                    <li>Keep your review constructive and respectful</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>