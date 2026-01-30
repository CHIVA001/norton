-- Travel Agency Database Schema
-- Database: travel_db

CREATE DATABASE IF NOT EXISTS travel_db;

USE travel_db;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    city VARCHAR(100),
    country VARCHAR(100),
    profileImage VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Destinations Table
CREATE TABLE destinations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    country VARCHAR(100) NOT NULL,
    description TEXT,
    imageUrl VARCHAR(255),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    bestTimeToVisit VARCHAR(100),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tours Table
CREATE TABLE tours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    destinationId INT NOT NULL,
    imageUrl VARCHAR(255),
    duration INT NOT NULL COMMENT 'Duration in days',
    price DECIMAL(10, 2) NOT NULL,
    maxCapacity INT NOT NULL,
    currentBookings INT DEFAULT 0,
    rating DECIMAL(3, 2) DEFAULT 0.0,
    reviewCount INT DEFAULT 0,
    itinerary LONGTEXT,
    amenities TEXT COMMENT 'JSON array of amenities',
    status ENUM(
        'active',
        'inactive',
        'archived'
    ) DEFAULT 'active',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (destinationId) REFERENCES destinations (id) ON DELETE CASCADE
);

-- Tour Gallery (Multiple images for each tour)
CREATE TABLE tourGallery (
    id INT PRIMARY KEY AUTO_INCREMENT,
    tourId INT NOT NULL,
    imageUrl VARCHAR(255) NOT NULL,
    imageOrder INT DEFAULT 0,
    FOREIGN KEY (tourId) REFERENCES tours (id) ON DELETE CASCADE
);

-- Bookings Table
CREATE TABLE bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bookingCode VARCHAR(50) UNIQUE NOT NULL,
    userId INT NOT NULL,
    tourId INT NOT NULL,
    numberOfPeople INT NOT NULL,
    totalPrice DECIMAL(10, 2) NOT NULL,
    startDate DATE NOT NULL,
    status ENUM(
        'pending',
        'confirmed',
        'completed',
        'cancelled'
    ) DEFAULT 'pending',
    paymentStatus ENUM('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
    specialRequests TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (tourId) REFERENCES tours (id) ON DELETE CASCADE
);

-- Reviews Table
CREATE TABLE reviews (
    id INT PRIMARY KEY AUTO_INCREMENT,
    userId INT NOT NULL,
    tourId INT NOT NULL,
    bookingId INT NOT NULL,
    rating INT NOT NULL CHECK (
        rating >= 1
        AND rating <= 5
    ),
    comment TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (tourId) REFERENCES tours (id) ON DELETE CASCADE,
    FOREIGN KEY (bookingId) REFERENCES bookings (id) ON DELETE CASCADE
);

-- Payments Table
CREATE TABLE payments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    bookingId INT NOT NULL UNIQUE,
    amount DECIMAL(10, 2) NOT NULL,
    paymentMethod ENUM(
        'credit_card',
        'debit_card',
        'paypal',
        'bank_transfer'
    ) NOT NULL,
    transactionId VARCHAR(100) UNIQUE,
    status ENUM(
        'pending',
        'successful',
        'failed'
    ) DEFAULT 'pending',
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (bookingId) REFERENCES bookings (id) ON DELETE CASCADE
);

-- Wishlist Table
CREATE TABLE wishlist (
    id INT PRIMARY KEY AUTO_INCREMENT,
    userId INT NOT NULL,
    tourId INT NOT NULL,
    addedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_tour (userId, tourId),
    FOREIGN KEY (userId) REFERENCES users (id) ON DELETE CASCADE,
    FOREIGN KEY (tourId) REFERENCES tours (id) ON DELETE CASCADE
);

-- Admin Activity Log
CREATE TABLE activityLog (
    id INT PRIMARY KEY AUTO_INCREMENT,
    adminId INT NOT NULL,
    action VARCHAR(255) NOT NULL,
    entityType VARCHAR(50),
    entityId INT,
    details TEXT,
    ipAddress VARCHAR(45),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (adminId) REFERENCES users (id) ON DELETE CASCADE
);

-- Create Indexes for better performance
CREATE INDEX idx_users_email ON users (email);

CREATE INDEX idx_users_role ON users (role);

CREATE INDEX idx_tours_destinationId ON tours (destinationId);

CREATE INDEX idx_tours_status ON tours (status);

CREATE INDEX idx_bookings_userId ON bookings (userId);

CREATE INDEX idx_bookings_tourId ON bookings (tourId);

CREATE INDEX idx_bookings_status ON bookings (status);

CREATE INDEX idx_bookings_paymentStatus ON bookings (paymentStatus);

CREATE INDEX idx_reviews_tourId ON reviews (tourId);

CREATE INDEX idx_reviews_userId ON reviews (userId);

CREATE INDEX idx_wishlist_userId ON wishlist (userId);

CREATE INDEX idx_activityLog_adminId ON activityLog (adminId);