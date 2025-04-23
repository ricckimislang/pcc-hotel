<?php
session_start();
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Check if booking ID is provided
if (empty($_POST['booking_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking ID is required'
    ]);
    exit;
}

$booking_id = $_POST['booking_id'];

// Start transaction
$conn->begin_transaction();

try {
    // First, get current booking data
    $stmt = $conn->prepare("SELECT b.*, u.username, u.email, rm.room_number, rt.type_name 
                            FROM bookings b
                            JOIN users u ON b.user_id = u.user_id
                            JOIN rooms rm ON b.room_id = rm.room_id
                            JOIN room_types rt ON rm.room_type_id = rt.room_type_id
                            WHERE b.booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Booking not found");
    }

    // Update booking status to cancelled
    $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $result = $stmt->execute();

    if (!$result) {
        throw new Exception("Error cancelling booking: " . $conn->error);
    }

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Booking cancelled successfully'
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>