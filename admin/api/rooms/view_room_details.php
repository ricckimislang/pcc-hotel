<?php
require_once '../../../config/db.php';
header("Content-Type: application/json");

$response = array("status" => "error", "message" => "");
$id = $_GET['id'];

try {
    $stmt = $conn->prepare("SELECT r.*, b.*, u.first_name, u.last_name FROM rooms r LEFT JOIN bookings b ON r.room_id = b.room_id LEFT JOIN users u ON u.user_id = b.user_id WHERE r.room_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rooms = [];

    $row = $result->fetch_assoc();
    if (!$row) {
        $response = array("status" => "error", "message" => "Room not found");
        echo json_encode($response);
        exit;
    }
    if (!$row['user_id']) {
        $response = array("status" => false, "message" => "No user associated with this room");
        echo json_encode($response);
        exit;
    }
    $fullname = $row['first_name'] . ' ' . $row['last_name'];
    $rooms = [
        "bookingId" => $row["booking_id"],
        "userId" => $row["user_id"],
        "roomId" => $row["room_id"],
        "fullname" => $fullname,
        "bookingDate" => $row["booking_date"] ? date('F j, Y', strtotime($row["booking_date"])) : null,
        "check_in_date" => $row["check_in_date"] ? date('F j, Y', strtotime($row["check_in_date"])) : null,
        "check_out_date" => $row["check_out_date"] ? date('F j, Y', strtotime($row["check_out_date"])) : null,
        "bookingStatus" => $row["booking_status"],
        "totalPrice" => $row["total_price"],
        "paymentStatus" => $row["payment_status"],
    ];
    $response = array("status" => true, "message" => "Room details retrieved successfully", "details" => $rooms);
} catch (Exception $e) {
    $response = array("status" => "error", "message" => $e->getMessage());
}
echo json_encode($response);
$conn->close();
