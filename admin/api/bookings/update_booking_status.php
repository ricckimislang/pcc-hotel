<?php
require_once '../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    // Validate status
    $valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    // Update booking status
    $query = "UPDATE bookings SET status = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $booking_id);
    
    if ($stmt->execute()) {
        // If status is cancelled, make the room available again
        if ($status === 'cancelled') {
            $room_query = "UPDATE rooms r 
                          JOIN bookings b ON r.room_id = b.room_id 
                          SET r.status = 'available' 
                          WHERE b.booking_id = ?";
            $room_stmt = $conn->prepare($room_query);
            $room_stmt->bind_param("i", $booking_id);
            $room_stmt->execute();
            $room_stmt->close();
        }
        
        echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating booking status']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?> 