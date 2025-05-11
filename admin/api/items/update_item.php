<?php
declare(strict_types=1);
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Validate required fields
$required_fields = ['item_id', 'item_name', 'item_price'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        echo json_encode([
            'status' => 'error',
            'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required'
        ]);
        exit;
    }
}

// Sanitize and validate input
$item_id = (int)$_POST['item_id'];
$item_name = trim($_POST['item_name']);
$item_price = (float)$_POST['item_price'];
$item_description = isset($_POST['item_description']) ? trim($_POST['item_description']) : '';

// Validate item name length
if (strlen($item_name) > 100) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Item name must not exceed 100 characters'
    ]);
    exit;
}

// Validate price
if ($item_price <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Price must be greater than 0'
    ]);
    exit;
}

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
    
    // Check if item name already exists for other items
    $check_name_stmt = $conn->prepare("SELECT item_id FROM items WHERE item_name = ? AND item_id != ?");
    $check_name_stmt->bind_param("si", $item_name, $item_id);
    $check_name_stmt->execute();
    $name_result = $check_name_stmt->get_result();
    
    if ($name_result->num_rows > 0) {
        throw new Exception('Item name already exists');
    }
    
    // Update the item
    $update_stmt = $conn->prepare("
        UPDATE items 
        SET item_name = ?, 
            item_price = ?, 
            item_description = ?,
            updated_at = CURRENT_TIMESTAMP
        WHERE item_id = ?
    ");
    
    $update_stmt->bind_param("sdsi", $item_name, $item_price, $item_description, $item_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception('Failed to update item: ' . $conn->error);
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Item updated successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
} finally {
    // Close statements
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($check_name_stmt)) $check_name_stmt->close();
    if (isset($update_stmt)) $update_stmt->close();
    
    // Close connection
    $conn->close();
}
?> 