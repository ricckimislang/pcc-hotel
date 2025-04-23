<?php
// Include the database connection
require_once '../../../config/db.php';
require_once '../../includes/functions.php';

header('Content-Type: application/json');

// Check for booking ID
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Booking ID is required']);
    exit;
}

$booking_id = intval($_GET['booking_id']);

try {
    // Get booking details
    $query = "SELECT b.*, CONCAT(u.first_name, ' ', u.last_name) as guest_name, 
              r.room_number, rt.base_price as room_rate,
              DATEDIFF(b.check_out_date, b.check_in_date) as nights_stayed
              FROM bookings b
              JOIN users u ON b.user_id = u.user_id
              JOIN rooms r ON b.room_id = r.room_id
              JOIN room_types rt ON r.room_type_id = rt.room_type_id
              WHERE b.booking_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Booking not found']);
        exit;
    }
    
    $booking = $result->fetch_assoc();
    
    // Calculate room total
    $nights = $booking['nights_stayed'];
    $room_rate = $booking['room_rate'];
    $room_total = $nights * $room_rate;
    
    // Get payment information from transactions table
    $query = "SELECT * FROM transactions WHERE booking_id = ? ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $payment_result = $stmt->get_result();
    
    $payments = [];
    $total_paid = 0;
    
    while ($payment = $payment_result->fetch_assoc()) {
        $transaction_id = $payment['transaction_id'];
        $total_paid += $payment['amount'];
        
        // Get additional items from the additional_items table
        $query = "SELECT * FROM additional_items WHERE transaction_id = ? ORDER BY item_name";
        $stmt_items = $conn->prepare($query);
        $stmt_items->bind_param('i', $transaction_id);
        $stmt_items->execute();
        $items_result = $stmt_items->get_result();
        
        $additional_items = [];
        while ($item = $items_result->fetch_assoc()) {
            $additional_items[] = [
                'name' => $item['item_name'],
                'price' => $item['item_price'],
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal']
            ];
        }
        
        // If no items in the table but there's an extra_pay value, create a legacy entry
        if (empty($additional_items) && !empty($payment['extra_pay']) && $payment['extra_pay'] > 0) {
            $additional_items[] = [
                'name' => 'Additional Charges',
                'price' => $payment['extra_pay'],
                'quantity' => 1,
                'subtotal' => $payment['extra_pay']
            ];
        }
        
        // Format receipt URL if exists
        $receipt_url = null;
        if (!empty($payment['payment_screenshot'])) {
            $receipt_url = '../../../uploads/receipts/' . $payment['payment_screenshot'];
        }
        
        $payments[] = [
            'id' => $payment['transaction_id'],
            'payment_date' => formatDate($payment['created_at']),
            'amount' => $payment['amount'],
            'payment_method' => 'cash', // Default to cash since schema doesn't have payment method
            'transaction_id' => $payment['reference_no'],
            'additional_items' => $additional_items,
            'receipt_url' => $receipt_url,
            'notes' => '', // No notes field in transactions table
            'status' => 'completed'
        ];
    }
    
    // Calculate balance due
    $balance_due = $room_total - $total_paid;
    
    // Prepare booking summary
    $booking_summary = [
        'id' => $booking['booking_id'],
        'guest_name' => $booking['guest_name'],
        'room_number' => $booking['room_number'],
        'check_in_date' => formatDate($booking['check_in_date']),
        'check_out_date' => formatDate($booking['check_out_date']),
        'total_amount' => $room_total,
        'amount_paid' => $total_paid,
        'balance_due' => $balance_due,
        'booking_status' => $booking['booking_status']
    ];
    
    // Return success response
    echo json_encode([
        'status' => 'success',
        'booking' => $booking_summary,
        'payments' => $payments
    ]);
    
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
} 