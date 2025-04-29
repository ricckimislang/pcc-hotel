<?php
session_start();
require_once '../../config/db.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit();
}

try {
    $userId = $_SESSION['user_id'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    // Verify current password
    $checkQuery = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'User not found'
        ]);
        exit();
    }

    $user = $result->fetch_assoc();

    // Check if current password matches
    if (md5($currentPassword) !== $user['password']) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        exit();
    }

    // Update password with MD5 hash
    $hashedPassword = md5($newPassword);
    $updateQuery = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('si', $hashedPassword, $userId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Password updated successfully!'
        ]);
        exit();
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No changes were made to the password'
        ]);
        exit();
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
