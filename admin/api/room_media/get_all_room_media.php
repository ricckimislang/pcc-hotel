<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// SQL query to get all rooms with their media (or null if no media exists)
$query = "
    SELECT 
        r.room_id,
        r.room_number,
        rt.type_name as room_type,
        rm.card_image,
        rm.panorama_image,
        rm.last_updated
    FROM 
        rooms r
    LEFT JOIN 
        room_types rt ON r.room_type_id = rt.room_type_id
    LEFT JOIN 
        room_media rm ON r.room_id = rm.room_id
    ORDER BY 
        r.room_number
";

$result = $conn->query($query);

if ($result) {
    $rooms_media = [];
    
    while ($row = $result->fetch_assoc()) {
        // Format the last_updated date if it exists
        if ($row['last_updated']) {
            $date = new DateTime($row['last_updated']);
            $row['last_updated'] = $date->format('M d, Y H:i');
        }
        
        $rooms_media[] = $row;
    }
    
    echo json_encode($rooms_media);
} else {
    echo json_encode(['error' => 'Failed to fetch room media data']);
}

$conn->close();
?> 