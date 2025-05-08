<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Get all items
$query = "SELECT item_id, item_name, item_price, item_description FROM items ORDER BY item_name";
$result = mysqli_query($conn, $query);

$items = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $items[] = $row;
    }
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch items'
    ]);
}

mysqli_close($conn);
