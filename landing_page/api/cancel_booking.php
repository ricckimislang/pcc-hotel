<?php
session_start();
require_once '../../config/db.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['booking_id']) || empty($data['booking_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking ID is required'
    ]);
    exit;
}

$booking_id = $data['booking_id'];
$user_id = $_SESSION['user_id'];

// Verify the booking belongs to the user
$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking not found or does not belong to this user'
    ]);
    exit;
}

$booking = $result->fetch_assoc();

// Check if booking is already cancelled
if ($booking['booking_status'] === 'cancelled') {
    echo json_encode([
        'success' => false,
        'message' => 'Booking is already cancelled'
    ]);
    exit;
}

// Check if check-in date has passed
$check_in_date = strtotime($booking['check_in_date']);
if ($check_in_date <= time()) {
    echo json_encode([
        'success' => false,
        'message' => 'Cannot cancel bookings after check-in date has passed'
    ]);
    exit;
}

// Update booking status to cancelled
$stmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$result = $stmt->execute();

if ($result) {
    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error cancelling booking: ' . $conn->error
    ]);
}

$stmt->close();
$conn->close(); 