<?php
require_once '../../config/db.php';
header('Content-Type: application/json');

if (!isset($_GET['room_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Room ID is required'
    ]);
    exit;
}

$room_id = $_GET['room_id'];

// Get all booked dates for the room where booking is not cancelled
$query = "SELECT check_in_date, check_out_date 
          FROM bookings 
          WHERE room_id = ? 
          AND booking_status NOT IN ('cancelled', 'checked_out')
          AND check_out_date >= CURDATE()";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

$booked_dates = [];
while ($row = $result->fetch_assoc()) {
    // Get all dates between check-in and check-out
    $period = new DatePeriod(
        new DateTime($row['check_in_date']),
        new DateInterval('P1D'),
        (new DateTime($row['check_out_date']))->modify('+1 day') // Include check-out date
    );

    foreach ($period as $date) {
        $booked_dates[] = $date->format('Y-m-d');
    }
}

echo json_encode([
    'success' => true,
    'booked_dates' => array_values(array_unique($booked_dates))
]);

$stmt->close();
$conn->close();
?> 