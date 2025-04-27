<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check if room_id is provided
if (!isset($_POST['room_type_id']) || empty($_POST['room_type_id'])) {
    echo json_encode(['success' => false, 'message' => 'Room ID is required']);
    exit;
}

// Check if panorama image is uploaded
if (!isset($_FILES['panorama_image']) || $_FILES['panorama_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No panorama image uploaded or upload error']);
    exit;
}

$room_type_id = $_POST['room_type_id'];
$file = $_FILES['panorama_image'];

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPEG, PNG, and WebP images are allowed']);
    exit;
}

// Validate file size (max 30MB)
$max_size = 30 * 1024 * 1024; // 30MB in bytes
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size exceeds the limit of 30MB']);
    exit;
}

// Create upload directory if it doesn't exist
$upload_dir = '../../../public/panoramas/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate a unique filename
$filename = 'room_' . $room_type_id . '_panorama_' . time() . '_' . mt_rand(1000, 9999) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
$target_path = $upload_dir . $filename;

// Move the uploaded file to the target directory
if (move_uploaded_file($file['tmp_name'], $target_path)) {
    // Get the current panorama image filename if exists
    $stmt = $conn->prepare("SELECT panorama_image FROM room_media WHERE room_type_id = ?");
    $stmt->bind_param("i", $room_type_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $old_panorama = null;
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $old_panorama = $row['panorama_image'];
        
        // Update the record with new panorama image
        $stmt = $conn->prepare("UPDATE room_media SET panorama_image = ?, last_updated = NOW() WHERE room_type_id = ?");
        $stmt->bind_param("si", $filename, $room_type_id);
    } else {
        // Insert a new record
        $stmt = $conn->prepare("INSERT INTO room_media (room_type_id, panorama_image, last_updated) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $room_type_id, $filename);
    }
    
    if ($stmt->execute()) {
        // Delete the old panorama if it exists
        if ($old_panorama && file_exists($upload_dir . $old_panorama)) {
            unlink($upload_dir . $old_panorama);
        }
        
        echo json_encode(['success' => true, 'message' => '360° panorama uploaded successfully', 'filename' => $filename]);
    } else {
        // Delete the uploaded file if database operation fails
        unlink($target_path);
        echo json_encode(['success' => false, 'message' => 'Failed to update database: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
}

$conn->close();
?> 