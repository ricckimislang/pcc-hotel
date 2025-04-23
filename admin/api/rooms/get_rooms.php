<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';
// Verify database connection
if (! $conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
    ]);
    exit;
}

try {
    $stmt = $conn->prepare('SELECT r.*, rt.type_name, rt.base_price, rt.capacity
FROM rooms r
JOIN room_types rt ON r.room_type_id = rt.room_type_id');
    $stmt->execute();
    $result = $stmt->get_result();

    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = [
            'id'          => $row['room_id'],
            'room_number' => $row['room_number'],
            'room_type'   => $row['type_name'],
            'floor'       => $row['floor'],
            'status'      => $row['status'],
            'price'       => $row['base_price'],
            'capacity'    => $row['capacity'],
        ];
    }
    $roomTypes = [];
    $stmt      = $conn->prepare('SELECT * FROM room_types');
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $roomTypes[] = [
            'id'   => $row['room_type_id'],
            'name' => $row['type_name'],
        ];
    }
    echo json_encode([
        'success'    => true,
        'rooms'      => $rooms,
        'room_types' => $roomTypes,
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
    exit;
}
