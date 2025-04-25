<?php
require_once('../../../config/db.php');
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        $room_id = $_POST['edit_room_id'];
        $room_number = $_POST['edit_room_number'];
        $room_type_id = $_POST['edit_room_type_id'];
        $status = $_POST['edit_status'];
        $description = $_POST['edit_description'];

        // Update the room in the database
        $stmt = $conn->prepare('UPDATE rooms SET room_number = ?, room_type_id = ?, description = ?, status = ? WHERE room_id = ?');
        $stmt->bind_param('ssssi', $room_number, $room_type_id, $description, $status, $room_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        echo json_encode(["status" => true, "message" => "Room updated successfully"]);
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(["status" => false, "message" => $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["status" => false, "message" => "Invalid request method"]);
}
$conn->close();
?>