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

// Default response
$response = [
    'status' => false,
    'message' => 'Invalid request',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_name = strtoupper($_POST['type_name']);
    $base_price = $_POST['base_price'];
    $floor = $_POST['floor'];
    $capacity = $_POST['capacity'];
    $description = $_POST['description'];
    $amenities = strtoupper($_POST['amenities']);

    $sql = "INSERT INTO room_types (type_name, base_price, capacity, description, floor_type, amenities) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdisis", $type_name, $base_price, $capacity, $description, $floor, $amenities);

    if ($stmt->execute()) {
        $response['status'] = true;
        $response['message'] = 'Room type added successfully';
    } else {
        $response['status'] = false;
        $response['message'] = 'Failed to add room type';
    }

    $stmt->close();
}

// Return the JSON response
echo json_encode($response);
$conn->close();