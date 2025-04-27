<?php
session_start();
require_once 'config/db.php';
header("Content-Type: application/json");



try {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($password === $row["password"]) {
        $_SESSION["username"] = $row["username"];
        $_SESSION["user_id"] = $row["user_id"];
        $_SESSION['user_type'] = $row['user_type'];

        echo json_encode(["status" => true, "message" => "Account is Valid!", "role" => $row["user_type"]]);
    } else {
        echo json_encode(["status" => false, "message" => "Wrong password"]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
$conn->close();
