<?php
session_start();

// Include database connection
require_once '../../../config/db.php';

// Check if user is logged in and is admin
// This will be implemented with proper authentication later

// Handle different actions based on request method and parameters
$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// Default response
$response = [
    'status' => 'error',
    'message' => 'Invalid request',
    'data' => null
];

switch ($action) {
    case 'create':
        // Create new room
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $room_number = trim($_POST['room_number'] ?? '');
            $room_type_id = intval($_POST['room_type_id'] ?? 0);
            $floor = trim($_POST['floor'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = trim($_POST['status'] ?? 'available');
            
            if (empty($room_number) || $room_type_id <= 0) {
                $response['message'] = 'Please provide all required fields';
                break;
            }
            
            try {
                // Check if room number already exists
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_number = ?");
                $stmt->bind_param("s", $room_number);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] > 0) {
                    $response['message'] = 'Room number already exists';
                    break;
                }
                
                // Insert into database
                $stmt = $conn->prepare("INSERT INTO rooms (room_number, room_type_id, floor, description, status) 
                                        VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sisss", $room_number, $room_type_id, $floor, $description, $status);
                
                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Room created successfully';
                    $response['data'] = [
                        'room_id' => $conn->insert_id,
                        'room_number' => $room_number
                    ];
                    
                    // Redirect back to rooms page
                    header('Location: ../pages/rooms.php?success=created');
                    exit;
                } else {
                    $response['message'] = 'Failed to create room: ' . $conn->error;
                }
                
                $stmt->close();
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
        }
        break;
        
    case 'read':
        // Get all rooms or a specific one
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $room_type_id = isset($_GET['room_type_id']) ? intval($_GET['room_type_id']) : 0;
        
        try {
            if ($id > 0) {
                // Get specific room with room type details
                $stmt = $conn->prepare("SELECT r.*, rt.type_name, rt.base_price, rt.capacity 
                                        FROM rooms r 
                                        JOIN room_types rt ON r.room_type_id = rt.room_type_id 
                                        WHERE r.room_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $response['status'] = 'success';
                    $response['message'] = 'Room retrieved successfully';
                    $response['data'] = $result->fetch_assoc();
                } else {
                    $response['message'] = 'Room not found';
                }
                
                $stmt->close();
            } else {
                // Get all rooms with room type details
                $query = "SELECT r.*, rt.type_name, rt.base_price, rt.capacity 
                          FROM rooms r 
                          JOIN room_types rt ON r.room_type_id = rt.room_type_id";
                
                // Filter by room type if specified
                if ($room_type_id > 0) {
                    $query .= " WHERE r.room_type_id = " . $room_type_id;
                }
                
                $query .= " ORDER BY r.room_number";
                
                $result = $conn->query($query);
                $rooms = [];
                
                while ($row = $result->fetch_assoc()) {
                    $rooms[] = $row;
                }
                
                $response['status'] = 'success';
                $response['message'] = 'Rooms retrieved successfully';
                $response['data'] = $rooms;
            }
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'update':
        // Update room
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $room_id = intval($_POST['room_id'] ?? 0);
            $room_number = trim($_POST['room_number'] ?? '');
            $room_type_id = intval($_POST['room_type_id'] ?? 0);
            $floor = trim($_POST['floor'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $status = trim($_POST['status'] ?? 'available');
            
            if ($room_id <= 0 || empty($room_number) || $room_type_id <= 0) {
                $response['message'] = 'Please provide all required fields';
                break;
            }
            
            try {
                // Check if room number already exists for another room
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_number = ? AND room_id != ?");
                $stmt->bind_param("si", $room_number, $room_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                
                if ($row['count'] > 0) {
                    $response['message'] = 'Room number already exists';
                    break;
                }
                
                // Update in database
                $stmt = $conn->prepare("UPDATE rooms 
                                        SET room_number = ?, room_type_id = ?, floor = ?, description = ?, status = ? 
                                        WHERE room_id = ?");
                $stmt->bind_param("sisssi", $room_number, $room_type_id, $floor, $description, $status, $room_id);
                
                if ($stmt->execute()) {
                    $response['status'] = 'success';
                    $response['message'] = 'Room updated successfully';
                    $response['data'] = [
                        'room_id' => $room_id,
                        'room_number' => $room_number
                    ];
                    
                    // Redirect back to rooms page
                    header('Location: ../pages/rooms.php?success=updated');
                    exit;
                } else {
                    $response['message'] = 'Failed to update room: ' . $conn->error;
                }
                
                $stmt->close();
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
        }
        break;
        
    case 'delete':
        // Delete room
        $id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
        
        if ($id <= 0) {
            $response['message'] = 'Invalid room ID';
            break;
        }
        
        try {
            // Check if room is in use in bookings
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE room_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] > 0) {
                $response['message'] = 'Cannot delete room as it has associated bookings';
                break;
            }
            
            // Delete room media first
            $stmt = $conn->prepare("DELETE FROM room_media WHERE room_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            // Delete from database
            $stmt = $conn->prepare("DELETE FROM rooms WHERE room_id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Room deleted successfully';
                
                // If it's an AJAX request, return JSON response
                if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                    echo json_encode($response);
                    exit;
                }
                
                // Otherwise redirect back to rooms page
                header('Location: ../pages/rooms.php?success=deleted');
                exit;
            } else {
                $response['message'] = 'Failed to delete room: ' . $conn->error;
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    case 'get_room_types':
        // Get all room types for dropdowns
        try {
            $result = $conn->query("SELECT room_type_id, type_name, base_price, capacity FROM room_types ORDER BY type_name");
            $room_types = [];
            
            while ($row = $result->fetch_assoc()) {
                $room_types[] = $row;
            }
            
            $response['status'] = 'success';
            $response['message'] = 'Room types retrieved successfully';
            $response['data'] = $room_types;
        } catch (Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        break;
        
    default:
        $response['message'] = 'Invalid action';
        break;
}

// Return JSON response for API calls
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not redirected yet, go back to rooms page
header('Location: ../pages/rooms.php?error=' . urlencode($response['message']));
exit;