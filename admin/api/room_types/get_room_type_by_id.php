<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';

// Verify database connection
if (!$conn) {
    echo json_encode([
        'status' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Check if ID parameter exists
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'status' => false,
        'message' => 'Room type ID is required'
    ]);
    exit;
}

$id = intval($_GET['id']);

try {
    $stmt = $conn->prepare('SELECT * FROM room_types WHERE room_type_id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => false,
            'message' => 'Room type not found'
        ]);
        exit;
    }

    $row = $result->fetch_assoc();
    $room_type = [
        'id' => $row['room_type_id'],
        'type_name' => $row['type_name'],
        'base_price' => $row['base_price'],
        'description' => $row['description'],
        'capacity' => $row['capacity'],
        'amenities' => $row['amenities'],
    ];

    echo json_encode([
        'status' => true,
        'room_type' => $room_type
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
    exit;
} 