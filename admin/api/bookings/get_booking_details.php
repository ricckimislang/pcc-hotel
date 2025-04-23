<?php
require_once '../../../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];

    // Get detailed booking information using all relevant tables
    $query = "SELECT b.*, 
                     u.first_name, u.last_name, u.email, u.phone_number,
                     r.room_number, r.floor, r.status as room_status,
                     rt.type_name, rt.base_price, rt.capacity,
                     t.reference_no, t.payment_screenshot
              FROM bookings b
              JOIN users u ON b.user_id = u.user_id
              JOIN rooms r ON b.room_id = r.room_id
              JOIN room_types rt ON r.room_type_id = rt.room_type_id
              LEFT JOIN transactions t ON b.booking_id = t.booking_id
              WHERE b.booking_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($booking) {
        // Calculate total nights
        $check_in = new DateTime($booking['check_in_date']);
        $check_out = new DateTime($booking['check_out_date']);
        $nights = $check_in->diff($check_out)->days;

        // Format dates
        $check_in_formatted = date('F j, Y', strtotime($booking['check_in_date']));
        $check_out_formatted = date('F j, Y', strtotime($booking['check_out_date']));
        $booking_date_formatted = date('F j, Y, g:i A', strtotime($booking['booking_date']));

        // Prepare response data
        $response = [
            'success' => true,
            'data' => [
                'guest' => [
                    'name' => $booking['first_name'] . ' ' . $booking['last_name'],
                    'email' => $booking['email'],
                    'phone' => $booking['phone_number']
                ],
                'room' => [
                    'number' => $booking['room_number'],
                    'type' => $booking['type_name'],
                    'floor' => $booking['floor'],
                    'status' => $booking['room_status'],
                    'capacity' => $booking['capacity']
                ],
                'booking' => [
                    'id' => $booking['booking_id'],
                    'check_in' => $check_in_formatted,
                    'check_out' => $check_out_formatted,
                    'edit_check_in' => $booking['check_in_date'],
                    'edit_check_out' => $booking['check_out_date'],
                    'booking_date' => $booking_date_formatted,
                    'total_nights' => $nights,
                    'guests_count' => $booking['guests_count'],
                    'total_price' => number_format($booking['total_price'], 2),
                    'status' => $booking['booking_status'],
                    'payment_status' => $booking['payment_status'],
                    'special_requests' => $booking['special_requests'],
                    'source' => $booking['booking_source']
                ],
                'payment' => [
                    'reference_no' => $booking['reference_no'] ?? null,
                    'payment_screenshot' => $booking['payment_screenshot'] ?? null
                ]
            ]
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Booking not found'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request'
    ];
}

header('Content-Type: application/json');
echo json_encode($response);
?>