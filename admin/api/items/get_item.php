<?php
declare(strict_types=1);
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Check if the request is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Check if item_id is provided
if (!isset($_GET['item_id']) || empty($_GET['item_id'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Item ID is required'
    ]);
    exit;
}

$item_id = (int)$_GET['item_id'];

try {
    // Prepare and execute the query
    $stmt = $conn->prepare("
        SELECT item_id, item_name, item_price, item_description, created_at, updated_at 
        FROM items 
        WHERE item_id = ?
    ");
    
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $item_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to execute query: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Item not found'
        ]);
        exit;
    }
    
    // Fetch the item data
    $item = $result->fetch_assoc();
    
    // Format the response
    echo json_encode([
        'status' => 'success',
        'data' => [
            'item_id' => (int)$item['item_id'],
            'item_name' => $item['item_name'],
            'item_price' => (float)$item['item_price'],
            'item_description' => $item['item_description'],
            'created_at' => $item['created_at'],
            'updated_at' => $item['updated_at']
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    // Close statement and connection
    if (isset($stmt)) {
        $stmt->close();
    }
    $conn->close();
}
?> 