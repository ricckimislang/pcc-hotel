<?php
declare(strict_types=1);
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check if item_id is provided
if (!isset($_POST['item_id']) || empty($_POST['item_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Item ID is required']);
    exit;
}

$item_id = (int)$_POST['item_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Check if item exists
    $check_stmt = $conn->prepare("SELECT item_id FROM items WHERE item_id = ?");
    $check_stmt->bind_param("i", $item_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Item not found');
    }
    
    
    // Delete the item
    $delete_stmt = $conn->prepare("DELETE FROM items WHERE item_id = ?");
    $delete_stmt->bind_param("i", $item_id);
    
    if (!$delete_stmt->execute()) {
        throw new Exception('Failed to delete item: ' . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Item deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}

// Close statements
if (isset($check_stmt)) $check_stmt->close();
if (isset($check_usage_stmt)) $check_usage_stmt->close();
if (isset($delete_stmt)) $delete_stmt->close();

// Close connection
$conn->close();
?>
