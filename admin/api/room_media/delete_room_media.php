<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if room_id is provided
if (!isset($_POST['room_id']) || empty($_POST['room_id'])) {
    echo json_encode(['success' => false, 'message' => 'Room ID is required']);
    exit;
}

$room_id = $_POST['room_id'];

// Get current image filenames before deletion
$stmt = $conn->prepare("SELECT card_image, panorama_image FROM room_media WHERE room_id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $card_image = $row['card_image'];
    $panorama_image = $row['panorama_image'];
    
    // Delete record from database
    $stmt = $conn->prepare("DELETE FROM room_media WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    
    if ($stmt->execute()) {
        // Delete image files if they exist
        $card_path = '../../uploads/room_images/' . $card_image;
        $panorama_path = '../../uploads/panoramas/' . $panorama_image;
        
        if ($card_image && file_exists($card_path)) {
            unlink($card_path);
        }
        
        if ($panorama_image && file_exists($panorama_path)) {
            unlink($panorama_path);
        }
        
        echo json_encode(['success' => true, 'message' => 'Room media deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete media record: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No media found for this room']);
}

$stmt->close();
$conn->close();
?> 