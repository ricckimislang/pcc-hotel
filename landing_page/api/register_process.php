<?php
// Register Process for PCC Hotel Reservation
require_once '../../config/db.php';
header("Content-Type: application/json");

try{
    $username = $_POST["username"];
    $password = md5($_POST["password"]);
    $first_name = $_POST["first_name"];
    $last_name = $_POST["last_name"];
    $contact_number = $_POST["contact_number"];
    $email = $_POST["email"];
    $user_type = "customer";

    $stmt = $conn->prepare("INSERT INTO users (username, password, first_name, last_name, phone_number, email, user_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $username, $password, $first_name, $last_name, $contact_number, $email, $user_type);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["status" => true, "message" => "Registration successful!"]);
} catch (Exception $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}

?>