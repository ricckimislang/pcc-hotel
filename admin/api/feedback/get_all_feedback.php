<?php
/**
 * API Endpoint for Fetching All Feedback Data
 * Returns a list of all customer feedback with ratings and comments
 */

header('Content-Type: application/json');
require_once '../../../config/db.php';

// Set default response
$response = [
    'status' => false,
    'message' => 'Failed to fetch feedback data'
];

try {
    // Prepare SQL query to fetch all feedback with related information
    $sql = "SELECT 
                f.id,
                CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
                f.rating,
                f.comment,
                r.room_number,
                f.created_at AS date
            FROM feedback f
            JOIN users c ON f.customer_id = c.user_id
            JOIN rooms r ON f.room_id = r.room_id
            ORDER BY f.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all results
    $feedbackData = [];
    while ($row = $result->fetch_assoc()) {
        $feedbackData[] = $row;
    }
    
    $stmt->close();
    
    // Return feedback data
    echo json_encode($feedbackData);
    exit;
    
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    
    // Log error to server error log
    error_log("Feedback API Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode($response);
    exit;
} 