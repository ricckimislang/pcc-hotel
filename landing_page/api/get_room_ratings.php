<?php
require_once '../../config/db.php';
header("Content-Type: application/json");

try {
    // Query to get average ratings for each room type
    $sql = "SELECT 
                rt.room_type_id,
                rt.type_name,
                COALESCE(AVG(f.rating), 0) as average_rating,
                COUNT(f.id) as total_ratings
            FROM room_types rt
            LEFT JOIN rooms r ON rt.room_type_id = r.room_type_id
            LEFT JOIN feedback f ON r.room_id = f.room_id
            GROUP BY rt.room_type_id, rt.type_name";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $ratings = [];
    while ($row = $result->fetch_assoc()) {
        $ratings[] = [
            'room_type_id' => $row['room_type_id'],
            'type_name' => $row['type_name'],
            'average_rating' => round(floatval($row['average_rating']), 1),
            'total_ratings' => intval($row['total_ratings'])
        ];
    }
    
    echo json_encode([
        'status' => true,
        'message' => 'Ratings retrieved successfully',
        'data' => $ratings
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close(); 