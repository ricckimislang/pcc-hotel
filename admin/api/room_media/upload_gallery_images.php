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

// Create upload directory if it doesn't exist
$upload_dir = '../../../public/room_images_details/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Get existing gallery images for this room
$existing_images = [];
$stmt = $conn->prepare("SELECT gallery_id, image_path FROM room_gallery WHERE room_type_id = (SELECT room_type_id FROM rooms WHERE room_id = ?)");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $existing_images[] = $row;
}
$stmt->close();

// Process the uploaded images
$new_images = [];
$errors = [];

// Check if we're only updating existing image order (no new uploads)
$update_only = isset($_POST['update_only']) && $_POST['update_only'] === 'true';

if (!$update_only) {
    // Process new uploaded files
    foreach ($_FILES as $key => $file) {
        if (strpos($key, 'gallery_image_') === 0 && $file['error'] === UPLOAD_ERR_OK) {
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
            if (!in_array($file['type'], $allowed_types)) {
                $errors[] = 'Invalid file type for ' . $file['name'] . '. Only JPEG, PNG, and WebP images are allowed';
                continue;
            }

            // Validate file size (max 5MB)
            $max_size = 5 * 1024 * 1024; // 5MB in bytes
            if ($file['size'] > $max_size) {
                $errors[] = 'File size exceeds the limit of 5MB for ' . $file['name'];
                continue;
            }

            // Generate a unique filename
            $filename = 'room_' . $room_id . '_gallery_' . time() . '_' . mt_rand(1000, 9999) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $target_path = $upload_dir . $filename;

            // Move the uploaded file to the target directory
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $new_images[] = $filename;
            } else {
                $errors[] = 'Failed to move uploaded file ' . $file['name'];
            }
        }
    }
}

// Process existing images
$keep_images = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'existing_image_') === 0) {
        $keep_images[] = $value;
    }
}

// Get the room type ID
$stmt = $conn->prepare("SELECT room_type_id FROM rooms WHERE room_id = ?");
$stmt->bind_param("i", $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room_type_id = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $room_type_id = $row['room_type_id'];
} else {
    echo json_encode(['success' => false, 'message' => 'Room not found']);
    exit;
}
$stmt->close();

// Begin transaction
$conn->begin_transaction();

try {
    // Delete images that are not in keep_images
    foreach ($existing_images as $existing) {
        if (!in_array($existing['image_path'], $keep_images)) {
            // Delete from database
            $stmt = $conn->prepare("DELETE FROM room_gallery WHERE gallery_id = ?");
            $stmt->bind_param("i", $existing['gallery_id']);
            $stmt->execute();
            $stmt->close();

            // Delete file
            if (file_exists($upload_dir . $existing['image_path'])) {
                unlink($upload_dir . $existing['image_path']);
            }
        }
    }

    // Insert new images
    foreach ($new_images as $index => $filename) {
        $display_order = $index;
        $stmt = $conn->prepare("INSERT INTO room_gallery (room_type_id, image_path, display_order) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $room_type_id, $filename, $display_order);
        $stmt->execute();
        $stmt->close();
    }

    // Update the last_updated timestamp in room_media table
    $stmt = $conn->prepare("SELECT room_id FROM room_media WHERE room_id = ?");
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE room_media SET last_updated = NOW() WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO room_media (room_id, last_updated) VALUES (?, NOW())");
        $stmt->bind_param("i", $room_id);
    }
    $stmt->execute();
    $stmt->close();

    // Commit transaction
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Gallery images processed successfully', 
        'new_images' => $new_images,
        'errors' => $errors
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Delete any new uploaded files
    foreach ($new_images as $filename) {
        if (file_exists($upload_dir . $filename)) {
            unlink($upload_dir . $filename);
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Error processing images: ' . $e->getMessage()]);
}

$conn->close();
?> 