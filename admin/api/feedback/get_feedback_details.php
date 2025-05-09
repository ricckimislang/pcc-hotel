<?php
/**
 * API Endpoint for Fetching Detailed Feedback Information
 * Returns comprehensive information about a specific feedback entry
 */

header('Content-Type: application/json');
require_once '../../../config/db.php';

// Set default response
$response = [
    'status' => false,
    'message' => 'Failed to fetch feedback details'
];

// Check if feedback ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $response['message'] = 'Feedback ID is required';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

$feedbackId = intval($_GET['id']);

try {
    // Prepare SQL query to fetch detailed feedback information
    $sql = "SELECT 
                f.id,
                CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
                c.email,
                c.phone_number,
                f.rating,
                f.comment,
                r.room_number,
                rt.type_name AS room_type,
                b.check_in_date,
                b.check_out_date,
                f.created_at AS date
            FROM feedback f
            JOIN users c ON f.customer_id = c.user_id
            JOIN rooms r ON f.room_id = r.room_id
            JOIN room_types rt ON r.room_type_id = rt.room_type_id
            JOIN bookings b ON f.booking_id = b.booking_id
            WHERE f.id = ?
            LIMIT 1 ORDER BY f.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("i", $feedbackId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch result
    $feedbackDetails = $result->fetch_assoc();
    
    $stmt->close();
    
    if (!$feedbackDetails) {
        $response['message'] = 'Feedback not found';
        http_response_code(404);
        echo json_encode($response);
        exit;
    }
    
    // Return detailed feedback information
    echo json_encode($feedbackDetails);
    exit;
    
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    
    // Log error to server error log
    error_log("Feedback Details API Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode($response);
    exit;
} 