<?php
/**
 * API Endpoint for Submitting Customer Feedback
 * Allows customers to submit feedback with ratings and comments
 */

header('Content-Type: application/json');
require_once '../../config/db.php';

// Set default response
$response = [
    'status' => false,
    'message' => 'Failed to submit feedback'
];

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    http_response_code(405);
    echo json_encode($response);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

// Validate required fields
$requiredFields = ['booking_id', 'customer_id', 'rating'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        $response['message'] = "Missing required field: $field";
        http_response_code(400);
        echo json_encode($response);
        exit;
    }
}

// Extract and sanitize data
$bookingId = intval($data['booking_id']);
$customerId = intval($data['customer_id']);
$rating = intval($data['rating']);
$comment = isset($data['comment']) ? trim($data['comment']) : null;

// Validate rating range (1-5)
if ($rating < 1 || $rating > 5) {
    $response['message'] = 'Rating must be between 1 and 5';
    http_response_code(400);
    echo json_encode($response);
    exit;
}

try {
    // Get room_id from the booking
    $sqlGetRoom = "SELECT room_id FROM bookings WHERE booking_id = ? LIMIT 1";
    $stmtGetRoom = $conn->prepare($sqlGetRoom);
    
    if (!$stmtGetRoom) {
        throw new Exception("Prepare failed for get room: " . $conn->error);
    }
    
    $stmtGetRoom->bind_param("i", $bookingId);
    $stmtGetRoom->execute();
    $resultRoom = $stmtGetRoom->get_result();
    $booking = $resultRoom->fetch_assoc();
    $stmtGetRoom->close();
    
    if (!$booking) {
        $response['message'] = 'Invalid booking ID';
        http_response_code(400);
        echo json_encode($response);
        exit;
    }
    
    $roomId = $booking['room_id'];
    
    // Check if feedback already exists for this booking
    $sqlCheckExisting = "SELECT id FROM feedback WHERE booking_id = ? LIMIT 1";
    $stmtCheckExisting = $conn->prepare($sqlCheckExisting);
    
    if (!$stmtCheckExisting) {
        throw new Exception("Prepare failed for check existing: " . $conn->error);
    }
    
    $stmtCheckExisting->bind_param("i", $bookingId);
    $stmtCheckExisting->execute();
    $resultExisting = $stmtCheckExisting->get_result();
    $existingFeedback = $resultExisting->fetch_assoc();
    $stmtCheckExisting->close();
    
    if ($existingFeedback) {
        // Update existing feedback
        $sqlUpdate = "UPDATE feedback 
                      SET rating = ?, comment = ?, updated_at = CURRENT_TIMESTAMP 
                      WHERE booking_id = ?";
        
        $stmtUpdate = $conn->prepare($sqlUpdate);
        
        if (!$stmtUpdate) {
            throw new Exception("Prepare failed for update: " . $conn->error);
        }
        
        $stmtUpdate->bind_param("isi", $rating, $comment, $bookingId);
        $stmtUpdate->execute();
        
        if ($stmtUpdate->affected_rows === 0) {
            throw new Exception("Failed to update feedback");
        }
        
        $stmtUpdate->close();
        
        $response['status'] = true;
        $response['message'] = 'Feedback updated successfully';
        echo json_encode($response);
        exit;
    } else {
        // Insert new feedback
        $sqlInsert = "INSERT INTO feedback (customer_id, booking_id, room_id, rating, comment)
                     VALUES (?, ?, ?, ?, ?)";
        
        $stmtInsert = $conn->prepare($sqlInsert);

        // Update booking table to set is_feedback to 1
        $sqlUpdateBooking = "UPDATE bookings SET is_feedback = 1 WHERE booking_id = ?";
        $stmtUpdateBooking = $conn->prepare($sqlUpdateBooking);
        
        if (!$stmtUpdateBooking) {
            throw new Exception("Prepare failed for update booking: " . $conn->error);
        }
        
        if (!$stmtInsert) {
            throw new Exception("Prepare failed for insert: " . $conn->error);
        }

        $stmtUpdateBooking->bind_param("i", $bookingId);
        $stmtUpdateBooking->execute();
        
        $stmtInsert->bind_param("iiiss", $customerId, $bookingId, $roomId, $rating, $comment);
        $stmtInsert->execute();
        
        if ($stmtInsert->affected_rows === 0) {
            throw new Exception("Failed to insert feedback");
        }
        
        $feedbackId = $conn->insert_id;
        $stmtInsert->close();
        
        $response['status'] = true;
        $response['message'] = 'Feedback submitted successfully';
        $response['feedback_id'] = $feedbackId;
        echo json_encode($response);
        exit;
    }
    
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();
    
    // Log error to server error log
    error_log("Feedback Submission API Error: " . $e->getMessage());
    
    // Return error response
    http_response_code(500);
    echo json_encode($response);
    exit;
} 