<?php
/**
 * Booking Form Page
 * Allows users to book a tour with date and number of people
 */

session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Book Tour - Travel Agency';

// Check authentication
require_once __DIR__ . '/../includes/auth-check.php';

$tourId = isset($_GET['tour_id']) ? intval($_GET['tour_id']) : 0;
$userId = getCurrentUserId();

if ($tourId <= 0) {
    header("Location: " . BASE_URL . "tours/tour-list.php");
    exit();
}

// Fetch tour details
$stmt = $conn->prepare("
    SELECT t.*, d.name as destinationName
    FROM tours t
    JOIN destinations d ON t.destinationId = d.id
    WHERE t.id = ? AND t.status = 'active'
");
$stmt->bind_param("i", $tourId);
$stmt->execute();
$result = $stmt->get_result();
$tour = $result->fetch_assoc();
$stmt->close();

if (!$tour) {
    header("Location: " . BASE_URL . "tours/tour-list.php");
    exit();
}

$errors = [];
$successMessage = '';

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numberOfPeople = isset($_POST['number_of_people']) ? intval($_POST['number_of_people']) : 0;
    $startDate = isset($_POST['start_date']) ? sanitize($_POST['start_date']) : '';
    $specialRequests = isset($_POST['special_requests']) ? sanitize($_POST['special_requests']) : '';

    // Validation
    if ($numberOfPeople <= 0 || $numberOfPeople > 10) {
        $errors[] = 'Number of people must be between 1 and 10';
    }

    if (empty($startDate)) {
        $errors[] = 'Start date is required';
    } else {
        try {
            $date = new DateTime($startDate);
            $today = new DateTime();
            if ($date < $today) {
                $errors[] = 'Start date cannot be in the past';
            }
        } catch (Exception $e) {
            $errors[] = 'Invalid date format';
        }
    }

    // Check availability
    $availableSpots = $tour['maxCapacity'] - $tour['currentBookings'];
    if ($numberOfPeople > $availableSpots) {
        $errors[] = "Only $availableSpots spots available. You cannot book $numberOfPeople people.";
    }

    // Create booking if no errors
    if (empty($errors)) {
        $bookingCode = generateBookingCode();
        $totalPrice = $tour['price'] * $numberOfPeople;

        $stmt = $conn->prepare("
            INSERT INTO bookings (bookingCode, userId, tourId, numberOfPeople, totalPrice, startDate, specialRequests, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");

        if ($stmt) {
            $stmt->bind_param(
                "siiidss",
                $bookingCode,
                $userId,
                $tourId,
                $numberOfPeople,
                $totalPrice,
                $startDate,
                $specialRequests
            );

            if ($stmt->execute()) {
                $bookingId = $stmt->insert_id;
                $stmt->close();

                // Update tour current bookings
                $newBookings = $tour['currentBookings'] + $numberOfPeople;
                $updateStmt = $conn->prepare("UPDATE tours SET currentBookings = ? WHERE id = ?");
                $updateStmt->bind_param("ii", $newBookings, $tourId);
                $updateStmt->execute();
                $updateStmt->close();

                // Create payment record
                $paymentStmt = $conn->prepare("
                    INSERT INTO payments (bookingId, amount, paymentMethod, status)
                    VALUES (?, ?, 'pending', 'pending')
                ");
                $paymentStmt->bind_param("id", $bookingId, $totalPrice);
                $paymentStmt->execute();
                $paymentStmt->close();

                $_SESSION['message'] = 'Booking created! Proceed to payment.';
                $_SESSION['message_type'] = 'success';
                header("Location: booking-confirmation.php?code=$bookingCode");
                exit();
            } else {
                $errors[] = 'Failed to create booking. Please try again.';
            }
            $stmt->close();
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow">
            <div class="card-body">
                <h3 class="card-title mb-4">Book This Tour</h3>

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
                    <div class="row mb-4 p-3 bg-light rounded">
                        <div class="col-md-6">
                            <h5><?php echo htmlspecialchars($tour['title']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt"></i>
                                <?php echo htmlspecialchars($tour['destinationName']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-calendar-days"></i> <?php echo $tour['duration']; ?> days
                            </p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h4 class="text-primary"><?php echo formatCurrency($tour['price']); ?>/person</h4>
                            <small class="text-muted">
                                Available: <?php echo $tour['maxCapacity'] - $tour['currentBookings']; ?> spots
                            </small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="number_of_people" class="form-label">Number of People *</label>
                        <input type="number" class="form-control" id="number_of_people" name="number_of_people" min="1"
                            max="10" value="<?php echo htmlspecialchars($_POST['number_of_people'] ?? ''); ?>" required>
                        <small class="text-muted">Maximum 10 people per booking</small>
                    </div>

                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date *</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="<?php echo htmlspecialchars($_POST['start_date'] ?? ''); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="special_requests" class="form-label">Special Requests (Optional)</label>
                        <textarea class="form-control" id="special_requests" name="special_requests" rows="4"
                            placeholder="Any special requirements or requests?"><?php echo htmlspecialchars($_POST['special_requests'] ?? ''); ?></textarea>
                    </div>

                    <div class="alert alert-info">
                        <h6>Booking Summary:</h6>
                        <p class="mb-1">Price per person: <strong id="pricePerPerson">-</strong></p>
                        <p class="mb-1">Number of people: <strong id="numPeople">0</strong></p>
                        <p class="mb-0">Total price: <strong id="totalPrice">$0.00</strong></p>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">Proceed to Booking</button>
                    <a href="<?php echo BASE_URL; ?>tours/tour-detail.php?id=<?php echo $tourId; ?>"
                        class="btn btn-outline-secondary btn-lg">Cancel</a>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tour Details</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($tour['imageUrl'])): ?>
                    <img src="<?php echo BASE_URL . 'uploads/tours/' . htmlspecialchars($tour['imageUrl']); ?>"
                        class="img-fluid rounded mb-3" alt="Tour">
                <?php else: ?>
                    <div class="bg-light d-flex justify-content-center align-items-center rounded mb-3"
                        style="height: 200px;">
                        <i class="fas fa-image fa-3x text-muted"></i>
                    </div>
                <?php endif; ?>

                <dl class="row">
                    <dt class="col-sm-6">Duration:</dt>
                    <dd class="col-sm-6"><?php echo $tour['duration']; ?> days</dd>

                    <dt class="col-sm-6">Price:</dt>
                    <dd class="col-sm-6"><?php echo formatCurrency($tour['price']); ?></dd>

                    <dt class="col-sm-6">Destination:</dt>
                    <dd class="col-sm-6"><?php echo htmlspecialchars($tour['destinationName']); ?></dd>

                    <dt class="col-sm-6">Rating:</dt>
                    <dd class="col-sm-6">
                        <?php
                        if ($tour['rating'] > 0) {
                            echo getRatingStars($tour['rating']) . ' (' . $tour['reviewCount'] . ')';
                        } else {
                            echo 'No ratings yet';
                        }
                        ?>
                    </dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('number_of_people').addEventListener('input', function () {
        const pricePerPerson = <?php echo $tour['price']; ?>;
        const numPeople = parseInt(this.value) || 0;
        const totalPrice = pricePerPerson * numPeople;

        document.getElementById('numPeople').textContent = numPeople;
        document.getElementById('pricePerPerson').textContent = '$' + pricePerPerson.toFixed(2);
        document.getElementById('totalPrice').textContent = '$' + totalPrice.toFixed(2);
    });

    // Trigger calculation on page load
    document.getElementById('number_of_people').dispatchEvent(new Event('input'));
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>