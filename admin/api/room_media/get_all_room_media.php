<?php
require_once '../../../config/db.php';
header('Content-Type: application/json');

// SQL query to get all rooms with their media (or null if no media exists)
$query = "
    SELECT 
        rt.room_type_id,
        rt.type_name as room_type,
        rm.panorama_image,
        rm.last_updated
    FROM room_types rt
    LEFT JOIN 
        room_media rm ON rt.room_type_id = rm.room_type_id
    ORDER BY 
        rt.room_type_id
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

        // Get gallery images for this room type
        $gallery_images = [];
        $room_type_id = $row['room_type_id'];

        $gallery_query = "
            SELECT image_path
            FROM room_gallery
            WHERE room_type_id = ?
            ORDER BY display_order ASC
        ";

        $stmt = $conn->prepare($gallery_query);
        $stmt->bind_param("i", $room_type_id);
        $stmt->execute();
        $gallery_result = $stmt->get_result();

        while ($gallery_row = $gallery_result->fetch_assoc()) {
            $gallery_images[] = $gallery_row['image_path'];
        }

        $stmt->close();

        // Add gallery images to response
        $row['gallery_images'] = $gallery_images;

        $rooms_media[] = $row;
    }

    echo json_encode($rooms_media);
} else {
    echo json_encode(['error' => 'Failed to fetch room media data']);
}

$conn->close();
