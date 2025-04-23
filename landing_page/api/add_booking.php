<?php
require_once '../../config/db.php';
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $room_id = $_POST['room_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $check_in = $_POST['check_in_date'];
    $check_out = $_POST['check_out_date'];
    $guests_count = $_POST['guests_count'];
    $special_requests = $_POST['special_requests'];

    // Get room_type_id
    $type = "SELECT room_type_id FROM rooms WHERE room_id = $room_id";
    $resultType = mysqli_query($conn, $type);
    $rowType = mysqli_fetch_assoc($resultType);
    $room_type_id = $rowType['room_type_id'];

    // Get base_price from room_types
    $priceQuery = "SELECT base_price FROM room_types WHERE room_type_id = $room_type_id";
    $resultPrice = mysqli_query($conn, $priceQuery);
    $rowPrice = mysqli_fetch_assoc($resultPrice);
    $base_price = $rowPrice["base_price"];

    // Calculate number of nights
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $interval = $check_in_date->diff($check_out_date);
    $nights = $interval->days;

    if ($nights <= 0) {
        echo json_encode(["status" => false, "message" => "Invalid booking dates"]);
        exit;
    }

    // Total price = nights * base price
    $total_price = $nights * $base_price;

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, guests_count, total_price, special_requests) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissids", $user_id, $room_id, $check_in, $check_out, $guests_count, $total_price, $special_requests);

    if ($stmt->execute()) {
        echo json_encode(["status" => true, "message" => "Booking successful. Check your bookings for payment and confirmation!"]);
    } else {
        echo json_encode(["status" => false, "message" => "Booking failed", "error" => $stmt->error]);
    }
} else {
    echo json_encode(["status" => false, "message" => "Invalid request method"]);
}
?>
