<?php
/**
 * Sample Data Setup Script
 * This script populates the database with sample data for testing
 * Run this ONCE after setting up the database: php setup-sample-data.php
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

echo "=== Travel Agency Sample Data Setup ===\n\n";

try {
    // Clear existing data
    echo "Clearing existing data...\n";
    $conn->query("TRUNCATE TABLE wishlist");
    $conn->query("TRUNCATE TABLE reviews");
    $conn->query("TRUNCATE TABLE payments");
    $conn->query("TRUNCATE TABLE bookings");
    $conn->query("TRUNCATE TABLE tourGallery");
    $conn->query("TRUNCATE TABLE tours");
    $conn->query("TRUNCATE TABLE destinations");
    $conn->query("TRUNCATE TABLE activityLog");
    $conn->query("TRUNCATE TABLE users");

    // Insert demo users
    echo "Creating users...\n";

    $adminPassword = hashPassword('Admin@123');
    $userPassword = hashPassword('User@123');

    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password, phone, city, country, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    // Admin user
    $stmt->bind_param(
        "ssssssss",
        $fname = "Admin",
        $lname = "User",
        $email = "admin@travel.com",
        $pass = $adminPassword,
        $phone = "+1234567890",
        $city = "New York",
        $country = "USA",
        $role = "admin"
    );
    $stmt->execute();
    echo "✓ Admin created (admin@travel.com / Admin@123)\n";

    // Regular user
    $fname = "John";
    $lname = "Doe";
    $email = "user@travel.com";
    $pass = $userPassword;
    $phone = "+1234567891";
    $city = "Los Angeles";
    $country = "USA";
    $role = "user";
    $stmt->execute();
    echo "✓ User created (user@travel.com / User@123)\n";

    $stmt->close();

    // Insert destinations
    echo "\nCreating destinations...\n";

    $destinations = [
        ['Paris', 'France', 'Romantic city with iconic landmarks', 'April-June'],
        ['Tokyo', 'Japan', 'Modern city with ancient temples', 'March-May, September-October'],
        ['New York', 'USA', 'The city that never sleeps', 'April-June, September-October'],
        ['Bali', 'Indonesia', 'Tropical paradise with beautiful beaches', 'April-October'],
        ['Barcelona', 'Spain', 'Vibrant city with art and architecture', 'May-June, September-October'],
        ['Dubai', 'UAE', 'Luxury desert destination', 'October-April'],
    ];

    $stmt = $conn->prepare("INSERT INTO destinations (name, country, description, bestTimeToVisit) VALUES (?, ?, ?, ?)");

    foreach ($destinations as $dest) {
        $stmt->bind_param("ssss", $dest[0], $dest[1], $dest[2], $dest[3]);
        $stmt->execute();
    }
    echo "✓ " . count($destinations) . " destinations created\n";
    $stmt->close();

    // Insert tours
    echo "\nCreating tours...\n";

    $tours = [
        [1, 'Romantic Paris Getaway', 'Experience the magic of Paris with iconic attractions', 5, 1500, 10, 'active'],
        [1, 'Paris & French Riviera', 'Explore Paris and the beautiful French coast', 7, 2200, 8, 'active'],
        [2, 'Tokyo Cultural Tour', 'Discover Japanese culture and traditions', 6, 1800, 12, 'active'],
        [2, 'Japan Complete Circuit', 'Visit Tokyo, Kyoto, and Osaka', 10, 3000, 10, 'active'],
        [3, 'New York City Explorer', 'See the best of NYC including Broadway', 4, 1200, 15, 'active'],
        [3, 'East Coast USA Road Trip', 'Boston to Washington DC adventure', 8, 2500, 6, 'active'],
        [4, 'Bali Beach Relaxation', 'Relax on beautiful Bali beaches', 5, 1600, 20, 'active'],
        [4, 'Bali Adventure Package', 'Temples, hiking, and water sports', 7, 2000, 15, 'active'],
        [5, 'Barcelona Architecture Tour', 'Gaudí and modernist architecture', 4, 1300, 18, 'active'],
        [5, 'Barcelona & Costa Brava', 'City and beach combined', 6, 1900, 12, 'active'],
        [6, 'Dubai Luxury Experience', 'Luxury hotels and shopping', 5, 2800, 8, 'active'],
        [6, 'Dubai & Desert Safari', 'City and desert adventure', 4, 1500, 14, 'active'],
    ];

    $stmt = $conn->prepare("INSERT INTO tours (destinationId, title, description, duration, price, maxCapacity, status) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($tours as $tour) {
        $stmt->bind_param("issidis", $tour[0], $tour[1], $tour[2], $tour[3], $tour[4], $tour[5], $tour[6]);
        $stmt->execute();
    }
    echo "✓ " . count($tours) . " tours created\n";
    $stmt->close();

    // Insert sample bookings
    echo "\nCreating sample bookings...\n";

    $userId = 2; // Regular user
    $bookings = [
        [1, 2, '2026-03-15', 1500, 2, 'confirmed', 'paid'],
        [2, 3, '2026-04-20', 3600, 2, 'pending', 'unpaid'],
        [4, 4, '2026-05-10', 2000, 1, 'completed', 'paid'],
        [6, 5, '2026-06-01', 2500, 2, 'confirmed', 'unpaid'],
    ];

    $stmt = $conn->prepare("INSERT INTO bookings (tourId, userId, startDate, totalPrice, numberOfPeople, status, paymentStatus, bookingCode) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($bookings as $booking) {
        $code = generateBookingCode();
        $stmt->bind_param("iisdisss", $booking[0], $userId, $booking[2], $booking[3], $booking[4], $booking[5], $booking[6], $code);
        $stmt->execute();
    }
    echo "✓ " . count($bookings) . " sample bookings created\n";
    $stmt->close();

    // Insert sample reviews
    echo "\nCreating sample reviews...\n";

    $reviews = [
        [2, 4, 4, 5, 'Absolutely amazing tour! The guides were knowledgeable and friendly. Highly recommended!'],
        [2, 4, 4, 5, 'Best trip ever! Everything was perfectly organized. Will definitely book again.'],
        [2, 6, 3, 4, 'Great experience overall. Food could have been better but everything else was excellent.'],
        [2, 8, 5, 5, 'Paradise on earth! The beach is absolutely stunning and the people are so welcoming.'],
    ];

    $stmt = $conn->prepare("INSERT INTO reviews (userId, tourId, bookingId, rating, comment) VALUES (?, ?, ?, ?, ?)");

    foreach ($reviews as $review) {
        $stmt->bind_param("iiis", $review[0], $review[1], $review[2], $review[3], $review[4]);
        $stmt->execute();
    }
    echo "✓ " . count($reviews) . " sample reviews created\n";
    $stmt->close();

    // Insert sample wishlist items
    echo "\nCreating sample wishlist items...\n";

    $stmt = $conn->prepare("INSERT INTO wishlist (userId, tourId) VALUES (?, ?)");

    $wishlistItems = [[2, 1], [2, 7], [2, 9], [2, 11]];

    foreach ($wishlistItems as $item) {
        $stmt->bind_param("ii", $item[0], $item[1]);
        $stmt->execute();
    }
    echo "✓ " . count($wishlistItems) . " wishlist items created\n";
    $stmt->close();

    echo "\n=== Setup Complete ===\n";
    echo "\nDemo Accounts:\n";
    echo "Admin: admin@travel.com / Admin@123\n";
    echo "User: user@travel.com / User@123\n";
    echo "\nYou can now access the application at: http://localhost/travel_agency_1/\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

?>