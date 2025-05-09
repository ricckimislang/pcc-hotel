<?php
declare(strict_types=1);
require_once '../../../config/db.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Validate and sanitize input
    $roomTypeId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$roomTypeId) {
        throw new Exception('Invalid room type ID');
    }

    // Check how many rooms are using this room type
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM rooms WHERE room_type_id = ?");
    $stmt->bind_param("i", $roomTypeId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode([
        'status' => true,
        'roomCount' => (int)$row['count']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
} 