<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if room_type_id is provided
if (!isset($_POST['room_type_id']) || empty($_POST['room_type_id'])) {
    echo json_encode(['success' => false, 'message' => 'Room ID is required']);
    exit;
}

$room_type_id = $_POST['room_type_id'];

// Start transaction
$conn->begin_transaction();

try {
    // Get panorama image before deletion
    $stmt = $conn->prepare("SELECT panorama_image FROM room_media WHERE room_type_id = ?");
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $panorama_image = null;
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $panorama_image = $row['panorama_image'];
    }
    
    // Get gallery images before deletion
    $stmt = $conn->prepare("SELECT image_path FROM room_gallery WHERE room_type_id = ?");
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    $gallery_result = $stmt->get_result();
    $gallery_images = [];
    
    while ($row = $gallery_result->fetch_assoc()) {
        $gallery_images[] = $row['image_path'];
    }
    
    // Delete from room_media table
    $stmt = $conn->prepare("DELETE FROM room_media WHERE room_type_id = ?");
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    
    // Delete from room_gallery table
    $stmt = $conn->prepare("DELETE FROM room_gallery WHERE room_type_id = ?");
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    
    // Delete physical files
    // Delete panorama image if exists
    if ($panorama_image) {
        $panorama_path = '../../public/panoramas/' . $panorama_image;
        if (file_exists($panorama_path)) {
            unlink($panorama_path);
        }
    }
    
    // Delete gallery images if they exist
    foreach ($gallery_images as $image) {
        $gallery_path = '../../public/room_images_details/' . $image;
        if (file_exists($gallery_path)) {
            unlink($gallery_path);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode(['success' => true, 'message' => 'Room media deleted successfully']);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error deleting room media: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();
?> 