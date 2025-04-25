<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'User not logged in', 'hasPendingBookings' => false]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Database connection
require_once '../../config/db.php';

try {
    // Query to check for pending bookings (status 'pending' or 'awaiting_payment')
    $stmt = $conn->prepare(
        "SELECT COUNT(*) as pending_count 
         FROM bookings 
         WHERE user_id = ? AND (booking_status = 'pending' or payment_status = 'pending')"
    );

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Return response with hasPendingBookings flag
    echo json_encode([
        'hasPendingBookings' => ($row['pending_count'] > 0),
        'pendingCount' => (int)$row['pending_count']
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage(), 'hasPendingBookings' => false]);
} finally {
    // Close connection
    $conn->close();
}
