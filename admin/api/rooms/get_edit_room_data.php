<?php
require_once('../../../config/db.php');
header("Content-Type: application/json");


try {
    $conn->begin_transaction();

    $room_id = $_GET['id'];

    $stmt = $conn->prepare('SELECT * FROM rooms WHERE room_id = ?');
    $stmt->bind_param('i', $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $room = $result->fetch_assoc();
        $roomData[] = $room;
        echo json_encode(
            [
                'status' => true,
                'roomData' => $roomData,
            ]
        );
    } else {
        echo json_encode(['status' => false, 'message' => 'Room not found']);
    }
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}
