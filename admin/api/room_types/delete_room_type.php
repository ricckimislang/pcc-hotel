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

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete associated rooms first
        $roomsStmt = $conn->prepare("DELETE FROM rooms WHERE room_type_id = ?");
        $roomsStmt->bind_param("i", $roomTypeId);
        $roomsStmt->execute();
        $deletedRooms = $roomsStmt->affected_rows;

        // Delete associated gallery images
        $galleryStmt = $conn->prepare("DELETE FROM room_gallery WHERE room_type_id = ?");
        $galleryStmt->bind_param("i", $roomTypeId);
        $galleryStmt->execute();

        // Delete associated media
        $mediaStmt = $conn->prepare("DELETE FROM room_media WHERE room_type_id = ?");
        $mediaStmt->bind_param("i", $roomTypeId);
        $mediaStmt->execute();

        // Finally delete the room type
        $stmt = $conn->prepare("DELETE FROM room_types WHERE room_type_id = ?");
        $stmt->bind_param("i", $roomTypeId);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete room type');
        }

        if ($stmt->affected_rows === 0) {
            throw new Exception('Room type not found');
        }

        // Commit the transaction
        $conn->commit();

        $message = 'Room type deleted successfully';
        if ($deletedRooms > 0) {
            $message .= " along with {$deletedRooms} associated room(s)";
        }

        echo json_encode([
            'status' => true,
            'message' => $message
        ]);

    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
} 