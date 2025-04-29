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
    // Get booking details with guest information and room details
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
    if($booking['is_discount'] == 1){
        $room_total = $nights * $room_rate * 0.95;
    }else{
        $room_total = $nights * $room_rate;
    }

    // Get payment information from transactions table
    $query = "SELECT COALESCE(SUM(amount), 0) as total_paid 
              FROM transactions 
              WHERE booking_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $payment_result = $stmt->get_result();
    $payment_data = $payment_result->fetch_assoc();

    $amount_paid = $payment_data['total_paid'] ?? 0;

    // Prepare response data
    $response_data = [
        'booking_id' => $booking['booking_id'],
        'guest_name' => $booking['guest_name'],
        'room_number' => $booking['room_number'],
        'check_in_date' => formatDate($booking['check_in_date']),
        'check_out_date' => formatDate($booking['check_out_date']),
        'nights_stayed' => $nights,
        'room_rate' => $room_rate,
        'room_total' => $room_total,
        'amount_paid' => $amount_paid,
        'total_amount' => $room_total, // Base amount before additional fees
        'booking_status' => $booking['booking_status']
    ];

    // Return success response
    echo json_encode(['status' => 'success', 'data' => $response_data]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}