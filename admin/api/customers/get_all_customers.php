<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $query = "SELECT u.user_id, u.username, u.first_name, u.last_name, u.phone_number, u.profile_image, cp.frequent_guest, cp.loyal_points
              FROM users u
              LEFT JOIN customer_profiles cp ON u.user_id = cp.user_id
              WHERE u.user_type = 'customer'";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = [];
    
    function na($val) {
        return (isset($val) && $val !== '' && $val !== null) ? $val : 'N/A';
    }
    
    while ($user = $result->fetch_assoc()) {
        $customers[] = [
            'fullname' => na($user['first_name']) . ' ' . na($user['last_name']),
            'username' => na($user['username']),
            'phone' => na($user['phone_number']),
            'profile_image' => na($user['profile_image']),
            'frequent_guest' => na($user['frequent_guest']),
            'loyal_points' => na($user['loyal_points'])
        ];
    }
    $stmt->close();
    $conn->close();
    
    echo json_encode([
        'success' => true,
        'data' => $customers
    ]);
} else {
    echo json_encode(['success' => false, 'data' => [], 'message' => 'Invalid request method']);
} 