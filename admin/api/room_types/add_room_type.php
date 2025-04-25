<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';
// Verify database connection
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed'
    ]);
    exit;
}

// Default response
$response = [
    'status' => false,
    'message' => 'Invalid request',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_name = strtoupper($_POST['type_name']);
    $base_price = $_POST['base_price'];
    $floor = $_POST['floor'];
    $capacity = $_POST['capacity'];
    $description = $_POST['description'];
    $amenities = strtoupper($_POST['amenities']);
    
    // Handle image upload
    $image_path = null;
    if(isset($_FILES['room_image']) && $_FILES['room_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if(in_array($_FILES['room_image']['type'], $allowed_types) && $_FILES['room_image']['size'] <= $max_size) {
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
            } else {
                $response['message'] = 'Failed to upload image. Please try again.';
                echo json_encode($response);
                exit;
            }
        } else {
            $response['message'] = 'Invalid image format or file size too large. Allowed formats: JPG, PNG, WebP (max 5MB)';
            echo json_encode($response);
            exit;
        }
    }

    $sql = "INSERT INTO room_types (type_name, base_price, capacity, description, floor_type, amenities, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sdisiss", $type_name, $base_price, $capacity, $description, $floor, $amenities, $image_path);

    if ($stmt->execute()) {
        $response['status'] = true;
        $response['message'] = 'Room type added successfully';
    } else {
        $response['status'] = false;
        $response['message'] = 'Failed to add room type: ' . $stmt->error;
    }

    $stmt->close();
}

// Return the JSON response
echo json_encode($response);
$conn->close();