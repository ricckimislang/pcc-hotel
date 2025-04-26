<?php
// Include necessary files
require_once '../../../config/db.php';
require_once '../../includes/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Handle CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

try {
    // Execute SQL query to get room types
    $sql = "SELECT room_type_id as id, type_name as name FROM room_types ORDER BY type_name";
    $result = mysqli_query($conn, $sql);

    $roomTypes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $roomTypes[] = $row;
    }

    // Output as JSON
    echo json_encode($roomTypes);
} catch (Exception $e) {
    // Handle exceptions
    $error = ['error' => 'Error: ' . $e->getMessage()];
    echo json_encode($error);
    error_log('Room types API error: ' . $e->getMessage());
}
