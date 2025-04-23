<?php
// Include the database connection
require_once '../../../config/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Check for transaction ID
if (!isset($_GET['transaction_id']) || empty($_GET['transaction_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Transaction ID is required']);
    exit;
}

$transaction_id = intval($_GET['transaction_id']);

try {
    // Get transaction details with booking and guest information
    $query = "SELECT t.*, 
              DATE_FORMAT(t.created_at, '%M %d, %Y %h:%i %p') as payment_date,
              DATE_FORMAT(b.check_in_date, '%M %d, %Y') as check_in_date,
              DATE_FORMAT(b.check_out_date, '%M %d, %Y') as check_out_date,
              b.booking_status,
              CONCAT(u.first_name, ' ', u.last_name) as guest_name,
              r.room_number
              FROM transactions t
              JOIN bookings b ON t.booking_id = b.booking_id
              JOIN users u ON b.user_id = u.user_id
              JOIN rooms r ON b.room_id = r.room_id
              WHERE t.transaction_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Transaction not found']);
        exit;
    }
    
    $transaction = $result->fetch_assoc();
    
    // Get additional items for this transaction
    $query = "SELECT * FROM additional_items WHERE transaction_id = ? ORDER BY item_name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $items_result = $stmt->get_result();
    
    $additional_items = [];
    while ($item = $items_result->fetch_assoc()) {
        $additional_items[] = $item;
    }
    
    // If no items in the table but there's an extra_pay value, create a legacy entry
    if (empty($additional_items) && !empty($transaction['extra_pay']) && $transaction['extra_pay'] > 0) {
        $additional_items[] = [
            'item_id' => 0,
            'transaction_id' => $transaction_id,
            'item_name' => 'Additional Charges',
            'item_price' => $transaction['extra_pay'],
            'quantity' => 1,
            'subtotal' => $transaction['extra_pay'],
            'created_at' => $transaction['created_at']
        ];
    }
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'transaction' => $transaction,
        'additional_items' => $additional_items
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
} 