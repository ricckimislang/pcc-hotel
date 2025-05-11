<?php
declare(strict_types=1);
require_once '../../../config/db.php';

header('Content-Type: application/json');

try {
    // Validate input
    if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
        throw new Exception('Invalid room ID');
    }

    $roomId = (int)$_POST['id'];

    // Check if room exists and is not occupied/reserved
    $checkStmt = $conn->prepare("SELECT status FROM rooms WHERE room_id = ?");
    $checkStmt->bind_param("i", $roomId);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Room not found');
    }

    $room = $result->fetch_assoc();
    if (in_array($room['status'], ['occupied', 'reserved'])) {
        throw new Exception('Cannot delete room that is occupied or reserved');
    }

    // Delete the room
    $deleteStmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
    $deleteStmt->bind_param("i", $roomId);
    
    if ($deleteStmt->execute()) {
        echo json_encode([
            'status' => true,
            'message' => 'Room deleted successfully'
        ]);
    } else {
        throw new Exception('Failed to delete room');
    }

    $deleteStmt->close();
    $checkStmt->close();

} catch (Exception $e) {
    error_log($e->getMessage());
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close(); 