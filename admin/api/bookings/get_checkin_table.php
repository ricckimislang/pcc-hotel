<?php
// Get all bookings with room and guest details
$query = "SELECT b.*, r.room_number, rt.type_name, u.first_name, u.last_name, u.email FROM bookings b JOIN rooms r ON b.room_id = r.room_id JOIN room_types rt ON r.room_type_id = rt.room_type_id JOIN users u ON b.user_id = u.user_id WHERE booking_status = 'confirmed' OR booking_status = 'checked_in'";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$checkInBookings = $result->fetch_all(MYSQLI_ASSOC);
?>