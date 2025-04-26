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
$period = $_GET['period'] ?? 'month';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$room_type = $_GET['room_type'] ?? 'all';

try {
    // Most booked rooms
    $most_booked_rooms_query = "SELECT 
                                r.room_id,
                                r.room_number,
                                rt.type_name as room_type,
                                r.floor,
                                COUNT(b.booking_id) as booking_count,
                                IFNULL(SUM(b.total_price), 0) as total_revenue
                              FROM rooms r
                              LEFT JOIN room_types rt ON r.room_type_id = rt.room_type_id
                              LEFT JOIN bookings b ON r.room_id = b.room_id 
                                   AND b.booking_status NOT IN ('cancelled')";
    
    // Add date filters directly into the JOIN condition
    if ($period == 'custom' && !empty($start_date) && !empty($end_date)) {
        $most_booked_rooms_query .= " AND ((b.check_in_date BETWEEN ? AND ?) 
                                       OR (b.check_out_date BETWEEN ? AND ?)
                                       OR (b.check_in_date <= ? AND b.check_out_date >= ?))";
        $params = [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date];
        $types = "ssssss";
    } elseif ($period == 'today') {
        $most_booked_rooms_query .= " AND (b.check_in_date = CURDATE() 
                                       OR b.check_out_date = CURDATE() 
                                       OR (b.check_in_date <= CURDATE() AND b.check_out_date >= CURDATE()))";
    } elseif ($period == 'week') {
        $most_booked_rooms_query .= " AND (b.check_in_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
                                       OR b.check_out_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                                       OR (b.check_in_date <= CURDATE() AND b.check_out_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)))";
    } elseif ($period == 'month') {
        $most_booked_rooms_query .= " AND (b.check_in_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) 
                                       OR b.check_out_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                                       OR (b.check_in_date <= CURDATE() AND b.check_out_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)))";
    } elseif ($period == 'year') {
        $most_booked_rooms_query .= " AND (b.check_in_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) 
                                       OR b.check_out_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                                       OR (b.check_in_date <= CURDATE() AND b.check_out_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)))";
    }
    
    // Add room type filter if specified
    if ($room_type != 'all') {
        $most_booked_rooms_query .= " WHERE r.room_type_id = ? ";
        if (isset($params)) {
            $params[] = $room_type;
            $types .= "i";
        } else {
            $params = [$room_type];
            $types = "i";
        }
    }
    
    $most_booked_rooms_query .= " GROUP BY r.room_id, r.room_number, rt.type_name, r.floor
                                ORDER BY booking_count DESC
                                LIMIT 10";
    
    $stmt = $conn->prepare($most_booked_rooms_query);
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $most_booked_rooms = [];
    while ($row = $result->fetch_assoc()) {
        $most_booked_rooms[] = [
            'room_id' => $row['room_id'],
            'room_number' => $row['room_number'],
            'room_type' => $row['room_type'],
            'floor' => $row['floor'],
            'booking_count' => (int)$row['booking_count'],
            'total_revenue' => (float)$row['total_revenue']
        ];
    }
    
    // Peak booking days - analyze by day of week
    $peak_booking_days_query = "SELECT 
                               DAYNAME(check_in_date) as day_name,
                               DAYOFWEEK(check_in_date) as day_number,
                               COUNT(*) as booking_count
                             FROM bookings b
                             WHERE booking_status NOT IN ('cancelled') ";
    
    $day_params = [];
    $day_types = "";
    
    // Apply room type filter if specified
    if ($room_type != 'all') {
        $peak_booking_days_query .= " AND b.room_id IN (SELECT room_id FROM rooms WHERE room_type_id = ?) ";
        $day_params[] = $room_type;
        $day_types .= "i";
    }
    
    // Apply date filters if needed
    if ($period == 'custom' && !empty($start_date) && !empty($end_date)) {
        $peak_booking_days_query .= " AND (check_in_date BETWEEN ? AND ?)";
        $day_params = array_merge($day_params, [$start_date, $end_date]);
        $day_types .= "ss";
    } elseif ($period == 'today') {
        // For "today", we'll look at the past 30 days to still get meaningful day-of-week data
        $peak_booking_days_query .= " AND check_in_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    } elseif ($period == 'week') {
        // For "week", we'll look at the past 90 days to get meaningful day-of-week data
        $peak_booking_days_query .= " AND check_in_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)";
    } elseif ($period == 'month') {
        $peak_booking_days_query .= " AND check_in_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
    } elseif ($period == 'year') {
        $peak_booking_days_query .= " AND check_in_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)";
    }
    
    $peak_booking_days_query .= " GROUP BY day_name, day_number
                               ORDER BY day_number";
    
    $stmt = $conn->prepare($peak_booking_days_query);
    
    if (!empty($day_params)) {
        $stmt->bind_param($day_types, ...$day_params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $peak_booking_days = [
        'days' => [],
        'counts' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $peak_booking_days['days'][] = $row['day_name'];
        $peak_booking_days['counts'][] = (int)$row['booking_count'];
    }
    
    // Return the data
    echo json_encode([
        'success' => true,
        'data' => [
            'most_booked_rooms' => $most_booked_rooms,
            'peak_booking_days' => $peak_booking_days
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
    ]);
} 