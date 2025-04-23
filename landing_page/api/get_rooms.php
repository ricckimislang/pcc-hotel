<?php
require_once '../../config/db.php';
header("Content-Type: application/json");


try {
    $stmt = $conn->prepare("SELECT * FROM room_types");
    $stmt->execute();
    $result = $stmt->get_result();
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = [
            'room_type_id' => $row['room_type_id'],
            'type_name' => $row['type_name'],
            'description' => $row['description'],
            'base_price' => $row['base_price'],
            'capacity' => $row['capacity'],
            'amenities' => $row['amenities'],
        ];
    }
    echo json_encode(["status" => true, "message" => "Room retrieved successfully", "data" => $rooms]);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
$conn->close();
?>