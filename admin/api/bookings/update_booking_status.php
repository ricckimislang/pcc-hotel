<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    // Validate status
    $valid_statuses = ['checked_in', 'checked_out'];
    if (!in_array($status, $valid_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }

    // Update booking status
    $query = "UPDATE bookings SET booking_status = ? WHERE booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $booking_id);

    if ($stmt->execute()) {

        $getRoomId = "SELECT room_id FROM bookings WHERE booking_id = ?";
        $stmtRoom = $conn->prepare($getRoomId);
        $stmtRoom->bind_param("i", $booking_id);
        $stmtRoom->execute();
        $result = $stmtRoom->get_result();
        $row = $result->fetch_assoc();
        $room_id = $row['room_id'];

        $roomStatus = $status === 'checked_in' ? 'occupied' : 'available';
        $updateRoomStatus = "UPDATE rooms SET status = ? WHERE room_id = ?";
        $stmtRoom = $conn->prepare($updateRoomStatus);
        $stmtRoom->bind_param("si", $roomStatus, $room_id);
        $stmtRoom->execute();
        $stmtRoom->close();

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