<?php
require_once '../../../config/db.php';

header('Content-Type: application/json');

try {
    // Get all items from the database
    $query = "SELECT * FROM items ORDER BY item_name ASC";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    $items = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }

    // Send success response
    echo json_encode([
        'status' => 'success',
        'data' => $items
    ]);

} catch (Exception $e) {
    // Send error response
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn);
