<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';

// Verify database connection
if (!$conn) {
    echo json_encode([
        'status' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get form data
$room_type_id = isset($_POST['room_type_id']) ? intval($_POST['room_type_id']) : 0;
$type_name = isset($_POST['type_name']) ? trim($_POST['type_name']) : '';
$base_price = isset($_POST['base_price']) ? floatval($_POST['base_price']) : 0;
$capacity = isset($_POST['capacity']) ? intval($_POST['capacity']) : 0;
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$amenities = isset($_POST['amenities']) ? trim($_POST['amenities']) : '';

// Validate required fields
if (empty($room_type_id) || empty($type_name) || $base_price <= 0 || $capacity <= 0) {
    echo json_encode([
        'status' => false,
        'message' => 'Please fill in all required fields'
    ]);
    exit;
}

try {
    // Handle image upload
    $image_path = null;
    $image_set = false;
    
    if(isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($_FILES['room_image']['type'], $allowed_types) && $_FILES['room_image']['size'] <= $max_size) {
            // Get current image path to delete after successful update
            $stmt = $conn->prepare("SELECT image_path FROM room_types WHERE room_type_id = ?");
            $stmt->bind_param('i', $room_type_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $current_image = null;
            
            if($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $current_image = $row['image_path'];
            }
            
            // Generate a unique filename
            $file_extension = pathinfo($_FILES['room_image']['name'], PATHINFO_EXTENSION);
            $filename = 'room_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $upload_dir = '../../../public/room_images/';
            
            // Ensure upload directory exists
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $target_path = $upload_dir . $filename;
            
            if(move_uploaded_file($_FILES['room_image']['tmp_name'], $target_path)) {
                $image_path = 'public/room_images/' . $filename;
                $image_set = true;
                
                // Delete old image if exists
                if($current_image && file_exists('../../../' . ltrim($current_image, '/'))) {
                    @unlink('../../../' . ltrim($current_image, '/'));
                }
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Failed to upload image. Please try again.'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Invalid image format or file size too large. Allowed formats: JPG, PNG, WebP (max 5MB)'
            ]);
            exit;
        }
    }
    
    // Prepare SQL based on whether an image was uploaded
    if($image_set) {
        $stmt = $conn->prepare("UPDATE room_types SET 
                                type_name = ?, 
                                base_price = ?, 
                                capacity = ?, 
                                description = ?, 
                                amenities = ?,
                                image_path = ? 
                                WHERE room_type_id = ?");
        
        $stmt->bind_param('sdisssi', $type_name, $base_price, $capacity, $description, $amenities, $image_path, $room_type_id);
    } else {
        $stmt = $conn->prepare("UPDATE room_types SET 
                                type_name = ?, 
                                base_price = ?, 
                                capacity = ?, 
                                description = ?, 
                                amenities = ? 
                                WHERE room_type_id = ?");
        
        $stmt->bind_param('sdissi', $type_name, $base_price, $capacity, $description, $amenities, $room_type_id);
    }
    
    $result = $stmt->execute();
    
    if ($result) {
        echo json_encode([
            'status' => true,
            'message' => 'Room type updated successfully'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Failed to update room type: ' . $stmt->error
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit;
} 