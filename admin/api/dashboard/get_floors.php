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
    // Execute SQL query to get distinct floors
    $sql = "SELECT DISTINCT floor_type as number FROM room_types ORDER BY floor_type";
    $result = mysqli_query($conn, $sql);

    $floors = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $floors[] = $row;
    }

    // Output as JSON
    echo json_encode($floors);

} catch (mysqli_sql_exception $e) {
    // Handle database errors
    $error = ['error' => 'Database error: ' . $e->getMessage()];
    echo json_encode($error);
    error_log('Floors API error: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other exceptions
    $error = ['error' => 'Error: ' . $e->getMessage()];
    echo json_encode($error);
    error_log('Floors API error: ' . $e->getMessage());
}