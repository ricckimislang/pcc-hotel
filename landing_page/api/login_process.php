<?php
session_start();
require_once '../../config/db.php';
header("Content-Type: application/json");

// Constants for lockout policy
define('MAX_FAILED_ATTEMPTS', 5);
define('LOCKOUT_DURATION', 5); // minutes

try {
    $username = $_POST["username"];
    $password = md5($_POST["password"]);
    $ip_address = $_SERVER['REMOTE_ADDR'];

    // First check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo json_encode(["status" => false, "message" => "Invalid username or password"]);
        exit;
    }

    // Check if account is locked
    if ($row['is_locked'] == 1) {
        if ($row['lock_expires'] && strtotime($row['lock_expires']) > time()) {
            $remaining_time = ceil((strtotime($row['lock_expires']) - time()) / 60);
            echo json_encode([
                "status" => false, 
                "message" => "Account is locked. Please try again in {$remaining_time} minutes."
            ]);
            exit;
        } else {
            // Lock has expired, reset the lock
            $stmt = $conn->prepare("UPDATE users SET is_locked = 0, lock_expires = NULL WHERE user_id = ?");
            $stmt->bind_param("i", $row['user_id']);
            $stmt->execute();
        }
    }

    // Check recent failed attempts
    $stmt = $conn->prepare("
        SELECT COUNT(*) as failed_attempts 
        FROM login_attempts 
        WHERE user_id = ? 
        AND success = 0 
        AND attempt_time > DATE_SUB(NOW(), INTERVAL ? MINUTE)
    ");
    $lockout_minutes = LOCKOUT_DURATION;
    $stmt->bind_param("ii", $row['user_id'], $lockout_minutes);
    $stmt->execute();
    $attempts_result = $stmt->get_result();
    $attempts_row = $attempts_result->fetch_assoc();

    if ($attempts_row['failed_attempts'] >= MAX_FAILED_ATTEMPTS) {
        // Lock the account
        $lock_expires = date('Y-m-d H:i:s', strtotime("+".LOCKOUT_DURATION." minutes"));
        $stmt = $conn->prepare("UPDATE users SET is_locked = 1, lock_expires = ? WHERE user_id = ?");
        $stmt->bind_param("si", $lock_expires, $row['user_id']);
        $stmt->execute();

        echo json_encode([
            "status" => false, 
            "message" => "Too many failed attempts. Account locked for ".LOCKOUT_DURATION." minutes."
        ]);
        exit;
    }

    // Verify password
    if ($password === $row["password"]) {
        // Successful login
        $_SESSION["username"] = $row["username"];
        $_SESSION["user_id"] = $row["user_id"];
        $_SESSION["role"] = $row["user_type"];

        // Log successful attempt
        $stmt = $conn->prepare("INSERT INTO login_attempts (user_id, ip_address, success) VALUES (?, ?, 1)");
        $stmt->bind_param("is", $row['user_id'], $ip_address);
        $stmt->execute();

        // Update last login time
        $stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        $stmt->bind_param("i", $row['user_id']);
        $stmt->execute();

        // Delete all past login attempts for this user
        $stmt = $conn->prepare("DELETE FROM login_attempts WHERE user_id = ?");
        $stmt->bind_param("i", $row['user_id']);
        $stmt->execute();

        echo json_encode([
            "status" => true, 
            "message" => "Account is Valid!", 
            "role" => $row["user_type"]
        ]);
    } else {
        // Failed login
        $stmt = $conn->prepare("INSERT INTO login_attempts (user_id, ip_address, success) VALUES (?, ?, 0)");
        $stmt->bind_param("is", $row['user_id'], $ip_address);
        $stmt->execute();

        $remaining_attempts = MAX_FAILED_ATTEMPTS - ($attempts_row['failed_attempts'] + 1);
        echo json_encode([
            "status" => false, 
            "message" => "Wrong password. {$remaining_attempts} attempts remaining before account lockout."
        ]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => false, "message" => $e->getMessage()]);
}
$conn->close();
