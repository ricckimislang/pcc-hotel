<?php
// Include the database connection
require_once '../../../config/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit;
}

// Check for required fields
if (!isset($_POST['booking_id']) || empty($_POST['booking_id']) ||
    !isset($_POST['payment_amount']) || empty($_POST['payment_amount'])
) {
    echo json_encode(['status' => 'error', 'message' => 'Booking ID and payment amount are required']);
    exit;
}

$booking_id = intval($_POST['booking_id']);
$payment_amount = floatval($_POST['payment_amount']);
$transaction_id = $_POST['transaction_id'] ?? null;
$payment_date = $_POST['payment_date'] ?? date('Y-m-d');
$payment_notes = $_POST['payment_notes'] ?? null;
$additional_items = $_POST['additional_items'] ?? '[]';

// Parse additional items for extra payment calculation
$extra_payment = 0;
$items = [];
if (!empty($additional_items)) {
    $items = json_decode($additional_items, true);
    if (is_array($items)) {
        foreach ($items as $item) {
            if (isset($item['subtotal'])) {
                $extra_payment += floatval($item['subtotal']);
            }
        }
    }
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Check if booking exists and is active
    $query = "SELECT b.*, rt.base_price, DATEDIFF(b.check_out_date, b.check_in_date) as nights,
              r.room_id
              FROM bookings b
              JOIN rooms r ON b.room_id = r.room_id
              JOIN room_types rt ON r.room_type_id = rt.room_type_id
              WHERE b.booking_id = ? AND (b.booking_status = 'checked_in' OR b.booking_status = 'confirmed')";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Booking not found or not in active status');
    }
    
    $booking = $result->fetch_assoc();
    
    // Check if transaction already exists for this booking
    $query = "SELECT * FROM transactions WHERE booking_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $transaction_result = $stmt->get_result();
    
    if ($transaction_result->num_rows === 0) {
        throw new Exception('No transaction found for this booking. Please create a transaction first.');
    }
    
    $transaction = $transaction_result->fetch_assoc();
    $transaction_id = $transaction['transaction_id'];
    
    // Update the existing transaction with the extra payment amount
    $query = "UPDATE transactions SET 
              extra_pay = ?
              WHERE booking_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('di', $extra_payment, $booking_id);
    $stmt->execute();
    
    // Delete any existing additional items for this transaction
    $query = "DELETE FROM additional_items WHERE transaction_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    
    // Insert new additional items
    if (!empty($items) && is_array($items)) {
        $query = "INSERT INTO additional_items (transaction_id, item_name, item_price, quantity, subtotal) 
                  VALUES (?, ?, ?, ?, ?)";
                  
        $stmt = $conn->prepare($query);
        
        foreach ($items as $item) {
            if (isset($item['name'], $item['price'], $item['quantity'], $item['subtotal'])) {
                $item_name = $item['name'];
                $item_price = floatval($item['price']);
                $item_quantity = intval($item['quantity']);
                $item_subtotal = floatval($item['subtotal']);
                
                $stmt->bind_param('isdid', 
                    $transaction_id, 
                    $item_name, 
                    $item_price, 
                    $item_quantity, 
                    $item_subtotal
                );
                $stmt->execute();
            }
        }
    }
    
    // Calculate room total
    $room_total = $booking['base_price'] * $booking['nights'];
    
    // Get total paid from transaction
    $total_paid = $transaction['amount'] + $extra_payment;
    $checkout_complete = false;
    
    // If total paid equals or exceeds room total, mark booking as checked out
    if ($total_paid >= $room_total) {
        $query = "UPDATE bookings SET booking_status = 'checked_out', 
                  payment_status = 'paid', 
                  booking_date = NOW() 
                  WHERE booking_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $booking_id);
        $stmt->execute();
        
        // Update room status to available
        $query = "UPDATE rooms SET status = 'available'
                  WHERE room_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $booking['room_id']);
        $stmt->execute();
        
        $checkout_complete = true;
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'status' => 'success', 
        'message' => 'Payment processed successfully',
        'checkout_complete' => $checkout_complete
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}