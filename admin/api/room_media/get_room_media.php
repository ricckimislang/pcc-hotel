<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Check if room_id is provided
if (!isset($_GET['room_id']) || empty($_GET['room_id'])) {
    echo json_encode(['error' => 'Room ID is required']);
    exit;
}

$room_id = $_GET['room_id'];

// Prepare and execute the query
$stmt = $conn->prepare("
    SELECT 
        r.room_id,
        r.room_number,
        rt.type_name as room_type,
        rm.card_image,
        rm.panorama_image,
        rm.last_updated
    FROM 
        rooms r
    LEFT JOIN 
        room_types rt ON r.room_type_id = rt.room_type_id
    LEFT JOIN 
        room_media rm ON r.room_id = rm.room_id
    WHERE 
        r.room_id = ?
");

$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $room_media = $result->fetch_assoc();
    echo json_encode($room_media);
} else {
    // If no media exists yet, return basic room info
    $stmt = $conn->prepare("
        SELECT 
            r.room_id,
            r.room_number,
            rt.type_name as room_type
        FROM 
            rooms r
        LEFT JOIN 
            room_types rt ON r.room_type_id = rt.room_type_id
        WHERE 
            r.room_id = ?
    ");
    
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $room_info = $result->fetch_assoc();
        $room_info['card_image'] = null;
        $room_info['panorama_image'] = null;
        $room_info['last_updated'] = null;
        echo json_encode($room_info);
    } else {
        echo json_encode(['error' => 'Room not found']);
    }
}

$stmt->close();
$conn->close();
?> 