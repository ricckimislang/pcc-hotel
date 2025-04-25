<?php
require_once '../../../config/db.php';

// Ensure proper request method and required data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $booking_status = $_POST['booking_status'] ?? null;
    $payment_status = $_POST['payment_status'] ?? null;
    $special_requests = $_POST['special_requests'] ?? null;

    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Prepare update statement
        $query = "UPDATE bookings SET ";
        $updateParts = [];
        $params = [];
        $types = "";

        // Add fields to update
        if ($booking_status !== null) {
            $updateParts[] = "booking_status = ?";
            $params[] = $booking_status;
            $types .= "s";
        }

        if ($payment_status !== null) {
            $updateParts[] = "payment_status = ?";
            $params[] = $payment_status;
            $types .= "s";
        }

        if ($special_requests !== null) {
            $updateParts[] = "special_requests = ?";
            $params[] = $special_requests;
            $types .= "s";
        }

        // Add booking_id parameter
        $params[] = $booking_id;
        $types .= "i";

        // Finalize query
        $query .= implode(", ", $updateParts) . " WHERE booking_id = ?";

        // Execute update if there are fields to update
        if (count($updateParts) > 0) {
            $stmt = $conn->prepare($query);
            
            // Bind parameters dynamically
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                // Update was successful
                $conn->commit();
                $response = [
                    'success' => true,
                    'message' => 'Booking updated successfully',
                    'booking_id' => $booking_id
                ];
            } else {
                // No rows were updated
                $conn->rollback();
                $response = [
                    'success' => false,
                    'message' => 'No changes made to the booking'
                ];
            }
            $stmt->close();
        } else {
            // No fields to update
            $conn->rollback();
            $response = [
                'success' => false,
                'message' => 'No data provided for update'
            ];
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $response = [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
    
    // Close connection
    $conn->close();
} else {
    // Invalid request
    $response = [
        'success' => false,
        'message' => 'Invalid request'
    ];
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response);
?> 