<?php
session_start();
require_once '../../config/db.php';
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

// Check if form data is submitted
if (!isset($_POST['booking_id']) || empty($_POST['booking_id']) || 
    !isset($_POST['reference_number']) || empty($_POST['reference_number']) || 
    !isset($_POST['payment_amount']) || empty($_POST['payment_amount']) || 
    !isset($_FILES['payment_screenshot']) || $_FILES['payment_screenshot']['error'] !== 0) {
    
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit;
}

$booking_id = $_POST['booking_id'];
$reference_number = $_POST['reference_number'];
$payment_amount = $_POST['payment_amount'];
$user_id = $_SESSION['user_id'];

// Verify the booking belongs to the user
$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ? AND user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Booking not found or does not belong to this user'
    ]);
    exit;
}

$booking = $result->fetch_assoc();

// Handle file upload
$screenshot = $_FILES['payment_screenshot'];
$uploadDir = '../uploads/payments/';

// Create the directory if it doesn't exist
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Generate a unique filename
$filename = 'payment_' . $booking_id . '_' . time() . '_' . basename($screenshot['name']);
$targetFilePath = $uploadDir . $filename;

// Check if image file is a valid image
$check = getimagesize($screenshot['tmp_name']);
if ($check === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Uploaded file is not a valid image'
    ]);
    exit;
}

// Check file size (limit to 5MB)
if ($screenshot['size'] > 5000000) {
    echo json_encode([
        'success' => false,
        'message' => 'File is too large (max 5MB)'
    ]);
    exit;
}

// Allow only certain file formats
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$fileExtension = strtolower(pathinfo($screenshot['name'], PATHINFO_EXTENSION));
if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode([
        'success' => false,
        'message' => 'Only JPG, JPEG, PNG & GIF files are allowed'
    ]);
    exit;
}

// Move uploaded file to the target directory
if (!move_uploaded_file($screenshot['tmp_name'], $targetFilePath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to upload image'
    ]);
    exit;
}

// Insert payment record to database (assuming you have a payments table)
// If you don't have a payments table yet, you'll need to create one

try {
    // Begin transaction
    $conn->begin_transaction();
    
    // 1. Insert payment record
    $stmt = $conn->prepare("INSERT INTO payments (booking_id, user_id, amount, reference_number, screenshot_path, payment_date) 
                           VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iidss", $booking_id, $user_id, $payment_amount, $reference_number, $filename);
    $stmt->execute();
    
    // 2. Update booking payment status
    // If payment equals total price, mark as paid, otherwise mark as partial
    $new_status = ($payment_amount >= $booking['total_price']) ? 'paid' : 'partial';
    
    $stmt = $conn->prepare("UPDATE bookings SET payment_status = ? WHERE booking_id = ?");
    $stmt->bind_param("si", $new_status, $booking_id);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment submitted successfully',
        'status' => $new_status
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error processing payment: ' . $e->getMessage()
    ]);
}

$conn->close(); 