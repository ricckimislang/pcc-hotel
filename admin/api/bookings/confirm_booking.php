<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];

    // First check if payment status is 'paid'
    $check_query = "SELECT payment_status FROM bookings WHERE booking_id = ?";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("i", $booking_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (strtolower($row['payment_status']) !== 'paid') {
            echo json_encode(['success' => false, 'message' => 'Cannot confirm booking: payment is not complete']);
            $check_stmt->close();
            $conn->close();
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        $check_stmt->close();
        $conn->close();
        exit;
    }

    $check_stmt->close();

    // Update booking status to confirmed
    $query = "UPDATE bookings SET booking_status = 'confirmed' WHERE booking_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);

    $updateRoomStatus = "UPDATE rooms SET status = 'reserved' WHERE room_id = ?";
    $stmtRoom = $conn->prepare($updateRoomStatus);
    $stmtRoom->bind_param("i", $booking_id);
    $stmtRoom->execute();
    $stmtRoom->close();

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Booking confirmed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error confirming booking']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}

$conn->close();
?>