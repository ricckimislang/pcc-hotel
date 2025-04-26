<?php
// Include necessary files
require_once '../../../config/db.php';
require_once '../../includes/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Handle CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'data' => []
];

try {
    
    // Prepare and execute SQL query to get current room statuses
    $sql = "SELECT 
                r.room_id as id,
                r.room_number,
                rt.floor_type as floor,
                rt.type_name as type,
                r.status,
                CURDATE() as last_cleaned,
                b.booking_id,
                u.first_name,
                u.last_name,
                b.check_in_date,
                b.check_out_date
            FROM rooms r
            LEFT JOIN room_types rt ON r.room_type_id = rt.room_type_id
            LEFT JOIN bookings b ON r.room_id = b.room_id AND b.booking_status = 'checked_in'
            LEFT JOIN users u ON b.user_id = u.user_id
            WHERE r.is_active = 1
            ORDER BY rt.floor_type, r.room_number";
    
    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception("Query failed: " . mysqli_error($conn));
    }
    
    $rooms = [];
    
    // Fetch all rows and process them
    while ($row = mysqli_fetch_assoc($result)) {
        $room = [
            'id' => $row['id'],
            'room_number' => $row['room_number'],
            'floor' => $row['floor'],
            'type' => $row['type'],
            'status' => $row['status'] ?: 'available',
            'last_cleaned' => formatDate($row['last_cleaned'])
        ];
        
        // Add guest information if room is occupied
        if ($row['booking_id']) {
            $room['guest'] = [
                'name' => $row['first_name'] . ' ' . $row['last_name'],
                'booking_id' => $row['booking_id'],
                'check_in' => formatDate($row['check_in_date']),
                'check_out' => formatDate($row['check_out_date'])
            ];
        }
        
        $rooms[] = $room;
    }
    
    // Set success response
    $response['success'] = true;
    $response['data'] = $rooms;
    
    mysqli_close($conn);
    
} catch (Exception $e) {
    // Set error response
    $response['message'] = 'Error: ' . $e->getMessage();
    error_log('Room status API error: ' . $e->getMessage());
}

// Output response as JSON
echo json_encode($response);