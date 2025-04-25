<?php
require_once('../../../config/db.php');
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();

        $room_number = $_POST['room_number'];
        $room_type_id = $_POST['room_type_id'];
        $status = $_POST['status'];
        $description = $_POST['description'];

        // insert to table
        $stmt = $conn->prepare('INSERT INTO rooms (room_number, room_type_id, description, status) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $room_number, $room_type_id, $description, $status);
        $stmt->execute();
        $stmt->close();

        echo json_encode(["status" => true, "message" => "Room added successfully"]);
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
