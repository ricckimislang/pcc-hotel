<?php
require_once '../../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    
    // Validate required fields
    $required_fields = ['check_in_date', 'check_out_date', 'guests_count', 'booking_status', 'payment_status'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode([
                'success' => false,
                'message' => "Missing required field: $field"
            ]);
            exit;
        }
    }

    // Prepare the update query
    $query = "UPDATE bookings SET 
              check_in_date = ?,
              check_out_date = ?,
              guests_count = ?,
              special_requests = ?,
              booking_status = ?,
              payment_status = ?,
              updated_at = CURRENT_TIMESTAMP
              WHERE booking_id = ?";

    $stmt = $conn->prepare($query);
    
    // Get the values
    $check_in = $_POST['check_in_date'];
    $check_out = $_POST['check_out_date'];
    $guests_count = $_POST['guests_count'];
    $special_requests = $_POST['special_requests'] ?? null;
    $booking_status = $_POST['booking_status'];
    $payment_status = $_POST['payment_status'];

    $stmt->bind_param("ssisssi", 
        $check_in,
        $check_out,
        $guests_count,
        $special_requests,
        $booking_status,
        $payment_status,
        $booking_id
    );

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Booking updated successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating booking: ' . $stmt->error
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
}
?> 