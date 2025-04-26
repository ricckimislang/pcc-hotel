<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_term'])) {
    $searchTerm = trim($_POST['search_term']);
    $like = "%$searchTerm%";
    $query = "SELECT u.user_id, u.username, u.first_name, u.last_name, u.phone_number, u.profile_image, cp.frequent_guest, cp.loyal_points
              FROM users u
              LEFT JOIN customer_profiles cp ON u.user_id = cp.user_id
              WHERE (u.first_name LIKE ? OR u.last_name LIKE ? OR u.username LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?) 
              AND u.user_type = 'customer'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssss', $like, $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
    $customers = [];
    function na($val)
    {
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
    if (count($customers) > 0) {
        echo json_encode([
            'success' => true,
            'data' => $customers
        ]);
    } else {
        echo json_encode(['success' => false, 'data' => [], 'message' => 'Customer not found']);
    }
} else {
    echo json_encode(['success' => false, 'data' => [], 'message' => 'Invalid request']);
}
