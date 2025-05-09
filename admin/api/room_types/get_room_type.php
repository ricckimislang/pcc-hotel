<?php

header('Content-Type: application/json');
require_once '../../../config/db.php';
// Verify database connection
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare('SELECT * FROM room_types');
    $stmt->execute();
    $result = $stmt->get_result();

    $room_types = [];
    while ($row = $result->fetch_assoc()) {
        $room_types[] = [
            'room_type_id' => $row['room_type_id'],
            'type_name' => $row['type_name'],
            'base_price' => $row['base_price'],
            'floor_type' => $row['floor_type'],
            'description' => $row['description'],
            'capacity' => $row['capacity'],
            'amenities' => $row['amenities'],
            'image_path' => $row['image_path']
        ];
    }

    echo json_encode([
        'status' => true,
        'room_types' => $room_types
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
