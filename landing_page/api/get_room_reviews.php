<?php
/**
 * API Endpoint for Fetching Room Type Reviews
 * Returns reviews and ratings for a specific room type
 */

header('Content-Type: application/json');
require_once '../../config/db.php';

// Set default response
$response = [
    'status' => false,
    'message' => 'Failed to fetch reviews'
];

// Check if room type ID is provided
if (!isset($_GET['room_type_id']) || empty($_GET['room_type_id'])) {
    $response['message'] = 'Room type ID is required';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

$roomTypeId = intval($_GET['room_type_id']);

try {
    // Get reviews with user information
    $sql = "SELECT 
                f.id,
                CONCAT(u.first_name, ' ', LEFT(u.last_name, 1), '.') AS customer_name,
                f.rating,
                f.comment,
                r.room_number,
                DATE_FORMAT(f.created_at, '%M %d, %Y') as review_date
            FROM feedback f
            JOIN users u ON f.customer_id = u.user_id
            JOIN rooms r ON f.room_id = r.room_id
            WHERE r.room_type_id = ?
            ORDER BY f.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $roomTypeId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all reviews
    $reviews = [];
    while ($row = $result->fetch_assoc()) {
        $reviews[] = $row;
    }
    
    // Get average rating
    $avgSql = "SELECT 
                COUNT(*) as total_reviews,
                ROUND(AVG(f.rating), 1) as average_rating,
                SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
            FROM feedback f
            JOIN rooms r ON f.room_id = r.room_id
            WHERE r.room_type_id = ?";
    
    $avgStmt = $conn->prepare($avgSql);
    $avgStmt->bind_param("i", $roomTypeId);
    $avgStmt->execute();
    $avgResult = $avgStmt->get_result();
    $stats = $avgResult->fetch_assoc();
    
    $response = [
        'status' => true,
        'message' => 'Reviews fetched successfully',
        'data' => [
            'reviews' => $reviews,
            'stats' => $stats
        ]
    ];
    
    echo json_encode($response);
    exit;
    
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    error_log("Room Reviews API Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode($response);
    exit;
} 