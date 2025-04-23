<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db.php';

$response = ['success' => false, 'message' => ''];

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'User not logged in';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method';
    echo json_encode($response);
    exit;
}

// Validate required fields
if (
    !isset($_POST['booking_id']) || empty($_POST['booking_id']) ||
    !isset($_POST['reference_number']) || empty($_POST['reference_number']) ||
    !isset($_POST['payment_amount']) || empty($_POST['payment_amount'])
) {
    $response['message'] = 'Missing required fields';
    echo json_encode($response);
    exit;
}

// Validate file upload
if (!isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] !== UPLOAD_ERR_OK) {
    $response['message'] = 'Payment screenshot is required';
    echo json_encode($response);
    exit;
}

// Get form data
$booking_id = $_POST['booking_id'];
$reference_number = $_POST['reference_number'];
$payment_amount = $_POST['payment_amount'];

// Verify booking belongs to the user
$stmt = $conn->prepare("SELECT b.*, r.room_id FROM bookings b JOIN rooms r ON b.room_id = r.room_id WHERE b.booking_id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response['message'] = 'Booking not found or does not belong to this user';
    echo json_encode($response);
    exit;
}

$booking = $result->fetch_assoc();
$room_id = $booking['room_id'];

// Process file upload
try {
    // Handle file upload
    $file = $_FILES['payment_screenshot'];
    $filename = time() . '_' . basename($file['name']);
    $target_dir = '../../public/uploads/payment_screenshots/';
    $upload_path = 'uploads/payment_screenshots/' . $filename;
    $target_file = $target_dir . $filename;

    // Check if file is an actual image
    $check = getimagesize($file['tmp_name']);
    if ($check === false) {
        throw new Exception('File is not an image');
    }

    // Check file size (limit to 5MB)
    if ($file['size'] > 5000000) {
        throw new Exception('File is too large (max 5MB)');
    }

    // Allow only certain file formats
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_types)) {
        throw new Exception('Only JPG, JPEG, PNG & GIF files are allowed');
    }

    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Upload file
    if (!move_uploaded_file($file['tmp_name'], $target_file)) {
        throw new Exception('Failed to upload file');
    }

    // Begin transaction
    $conn->begin_transaction();

    // Insert into transactions table
    $stmt = $conn->prepare("INSERT INTO transactions (booking_id, room_id, user_id, reference_no, payment_screenshot, amount, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiissd", $booking_id, $room_id, $user_id, $reference_number, $upload_path, $payment_amount);

    if (!$stmt->execute()) {
        throw new Exception('Failed to record transaction: ' . $stmt->error);
    }

    // Update booking payment status
    $stmt = $conn->prepare("UPDATE bookings 
                           SET payment_status = 
                               CASE 
                                   WHEN (SELECT SUM(amount) FROM transactions WHERE booking_id = ?) >= total_price 
                                   THEN 'paid' 
                                   ELSE 'partial' 
                               END
                           WHERE booking_id = ?");
    $stmt->bind_param("ii", $booking_id, $booking_id);

    if (!$stmt->execute()) {
        throw new Exception('Failed to update booking status: ' . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'Payment submitted successfully';

} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }

    // Remove uploaded file if transaction failed
    if (isset($target_file) && file_exists($target_file)) {
        unlink($target_file);
    }

    $response['message'] = $e->getMessage();
}

echo json_encode($response);