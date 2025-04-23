<?php
header('Content-Type: application/json');
require_once '../../../config/db.php';

// Verify database connection
if (!$conn) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed',
    ]);
    exit;
}

// Get filter parameters
$period = $_GET['period'] ?? 'today';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

try {
    // Room occupancy status counts
    $occupancy_query = "SELECT 
                        status, 
                        COUNT(*) as count 
                      FROM rooms 
                      GROUP BY status";
    
    $stmt = $conn->prepare($occupancy_query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $occupancy_data = [];
    while ($row = $result->fetch_assoc()) {
        $occupancy_data[$row['status']] = (int)$row['count'];
    }
    
    // Total rooms
    $total_rooms = array_sum(array_values($occupancy_data));
    
    // Current bookings
    $bookings_query = "SELECT 
                       booking_status, 
                       COUNT(*) as count 
                       FROM bookings";
    
    // Apply date filters if needed
    if ($period == 'custom' && !empty($start_date) && !empty($end_date)) {
        $bookings_query .= " WHERE (check_in_date BETWEEN ? AND ?) OR (check_out_date BETWEEN ? AND ?)";
        $params = [$start_date, $end_date, $start_date, $end_date];
        $types = "ssss";
    } elseif ($period == 'today') {
        $bookings_query .= " WHERE check_in_date = CURDATE() OR check_out_date = CURDATE() OR (check_in_date <= CURDATE() AND check_out_date >= CURDATE())";
    } elseif ($period == 'week') {
        $bookings_query .= " WHERE check_in_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR check_out_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($period == 'month') {
        $bookings_query .= " WHERE check_in_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR check_out_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($period == 'year') {
        $bookings_query .= " WHERE check_in_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) OR check_out_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    }
    
    $bookings_query .= " GROUP BY booking_status";
    
    $stmt = $conn->prepare($bookings_query);
    
    if (isset($params) && isset($types)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings_data = [];
    while ($row = $result->fetch_assoc()) {
        $bookings_data[$row['booking_status']] = (int)$row['count'];
    }
    
    // Get occupancy rate over time (last 30 days)
    $daily_occupancy_query = "SELECT 
                             DATE(check_in_date) as date,
                             COUNT(*) as checkins,
                             (SELECT COUNT(*) FROM bookings WHERE DATE(check_out_date) = DATE(b.check_in_date)) as checkouts
                             FROM bookings b
                             WHERE check_in_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                             GROUP BY DATE(check_in_date)
                             ORDER BY date";
    
    $stmt = $conn->prepare($daily_occupancy_query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $daily_occupancy = [];
    while ($row = $result->fetch_assoc()) {
        $daily_occupancy[] = [
            'date' => $row['date'],
            'checkins' => (int)$row['checkins'],
            'checkouts' => (int)$row['checkouts']
        ];
    }
    
    // Get room type distribution
    $room_type_query = "SELECT 
                       rt.type_name,
                       COUNT(r.room_id) as count,
                       SUM(CASE WHEN r.status = 'occupied' THEN 1 ELSE 0 END) as occupied
                       FROM rooms r
                       JOIN room_types rt ON r.room_type_id = rt.room_type_id
                       GROUP BY rt.type_name";
    
    $stmt = $conn->prepare($room_type_query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $room_type_data = [];
    while ($row = $result->fetch_assoc()) {
        $room_type_data[] = [
            'type' => $row['type_name'],
            'total' => (int)$row['count'],
            'occupied' => (int)$row['occupied'],
            'occupancy_rate' => $row['count'] > 0 ? round(($row['occupied'] / $row['count']) * 100, 1) : 0
        ];
    }
    
    // Return all data
    echo json_encode([
        'success' => true,
        'data' => [
            'occupancy' => $occupancy_data,
            'total_rooms' => $total_rooms,
            'bookings' => $bookings_data,
            'daily_occupancy' => $daily_occupancy,
            'room_types' => $room_type_data
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
    exit;
}
?>
