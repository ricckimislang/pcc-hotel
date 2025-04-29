<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db.php';

$response = ['success' => false, 'message' => ''];

// Function to generate receipt number
function generateReceiptNumber($conn)
{
    // Get the current date in YYYYMMDD format
    $date_prefix = date('Ymd');

    // Get the last receipt number from transactions table
    $query = "SELECT receipt_no FROM transactions ORDER BY transaction_id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastReceiptNo = $row['receipt_no'];

        // Extract the numeric part (last 3 digits) and increment by 1
        $numericPart = substr($lastReceiptNo, -3);
        $receiptNumber = intval($numericPart) + 1;

        // Format with leading zeros to make it 3 digits
        $formattedNumber = sprintf('%03d', $receiptNumber);

        // Combine date prefix with formatted number
        return $date_prefix . $formattedNumber;
    } else {
        // First transaction, start with date prefix and 001
        return $date_prefix . '001';
    }
}

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
    $target_dir = '../../public/uploads/payment_screenshots/';

    // Create a unique filename with timestamp, booking id and random string
    $timestamp = date('Ymd_His');
    $random_string = substr(md5(uniqid(mt_rand(), true)), 0, 8);
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = "payment_{$booking_id}_{$timestamp}_{$random_string}.{$file_extension}";

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

    // Generate receipt number
    $receipt_no = generateReceiptNumber($conn);

    // Insert into transactions table
    $stmt = $conn->prepare("INSERT INTO transactions (booking_id, room_id, user_id, reference_no, payment_screenshot, amount, receipt_no, created_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("iiissds", $booking_id, $room_id, $user_id, $reference_number, $upload_path, $payment_amount, $receipt_no);

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

    $loyalty_points = $conn->prepare("UPDATE customer_profiles SET loyal_points = loyal_points - 100 WHERE user_id = ?");
    $loyalty_points->bind_param("i", $user_id);
    $loyalty_points->execute();

    if (!$stmt->execute()) {
        throw new Exception('Failed to update booking status: ' . $stmt->error);
    }

    // Commit transaction
    $conn->commit();

    $response['success'] = true;
    $response['message'] = 'Payment submitted successfully';
    $response['receipt_no'] = $receipt_no;
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
