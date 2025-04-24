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

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get form data
$room_type_id = isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) : 0;
$type_name = isset($_POST['type_name']) ? trim($_POST['type_name']) : '';
$base_price = isset($_POST['base_price']) ? floatval($_POST['base_price']) : 0;
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$amenities = isset($_POST['amenities']) ? trim($_POST['amenities']) : '';

// Validate required fields
if (empty($room_type_id) || empty($type_name) || $base_price <= 0 || $capacity <= 0) {
    echo json_encode([
        'status' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

try {
    // Update room type in database
    $stmt = $conn->prepare("UPDATE room_types SET 
                            type_name = ?, 
                            base_price = ?, 
                            capacity = ?, 
                            description = ?, 
                            amenities = ? 
                            WHERE room_type_id = ?");
    
    $stmt->bind_param('sdissi', $type_name, $base_price, $capacity, $description, $amenities, $room_type_id);
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode([
            'status' => true,
            'message' => 'Room type updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to update room type: ' . $stmt->error
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
} 