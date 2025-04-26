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
$room_type = $_GET['room_type'] ?? 'all';

try {
    // Room occupancy status counts
    $occupancy_query = "SELECT 
                        status, 
                        COUNT(*) as count 
                      FROM rooms ";
    
    // Add room type filter if specified
    if ($room_type != 'all') {
        $occupancy_query .= " WHERE room_type_id = ? ";
        $room_type_params = [$room_type];
        $room_type_types = "i";
    }
    
    $occupancy_query .= " GROUP BY status";
    
    $stmt = $conn->prepare($occupancy_query);
    
    if (isset($room_type_params) && isset($room_type_types)) {
        $stmt->bind_param($room_type_types, ...$room_type_params);
    }
    
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
                       FROM bookings b";
    
    // Join to rooms if room type filter is applied
    if ($room_type != 'all') {
        $bookings_query .= " JOIN rooms r ON b.room_id = r.room_id WHERE r.room_type_id = ? ";
        $booking_params = [$room_type];
        $booking_types = "i";
    } else {
        $bookings_query .= " WHERE 1=1 ";
    }
    
    // Apply date filters if needed
    if ($period == 'custom' && !empty($start_date) && !empty($end_date)) {
        $bookings_query .= " AND ((check_in_date BETWEEN ? AND ?) OR (check_out_date BETWEEN ? AND ?))";
        if (isset($booking_params)) {
            $booking_params = array_merge($booking_params, [$start_date, $end_date, $start_date, $end_date]);
            $booking_types .= "ssss";
        } else {
            $booking_params = [$start_date, $end_date, $start_date, $end_date];
            $booking_types = "ssss";
        }
    } elseif ($period == 'today') {
        $bookings_query .= " AND (check_in_date = CURDATE() OR check_out_date = CURDATE() OR (check_in_date <= CURDATE() AND check_out_date >= CURDATE()))";
    } elseif ($period == 'week') {
        $bookings_query .= " AND (check_in_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) OR check_out_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY))";
    } elseif ($period == 'month') {
        $bookings_query .= " AND (check_in_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) OR check_out_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY))";
    } elseif ($period == 'year') {
        $bookings_query .= " AND (check_in_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR) OR check_out_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR))";
    }
    
    $bookings_query .= " GROUP BY booking_status";
    
    $stmt = $conn->prepare($bookings_query);
    
    if (isset($booking_params) && isset($booking_types)) {
        $stmt->bind_param($booking_types, ...$booking_params);
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
                             FROM bookings b ";
    
    // Apply room type filter if specified
    if ($room_type != 'all') {
        $daily_occupancy_query .= " JOIN rooms r ON b.room_id = r.room_id WHERE r.room_type_id = ? AND ";
        $daily_params = [$room_type];
        $daily_types = "i";
    } else {
        $daily_occupancy_query .= " WHERE ";
    }
    
    $daily_occupancy_query .= " check_in_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                              GROUP BY DATE(check_in_date)
                              ORDER BY date";
    
    $stmt = $conn->prepare($daily_occupancy_query);
    
    if (isset($daily_params) && isset($daily_types)) {
        $stmt->bind_param($daily_types, ...$daily_params);
    }
    
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
                       JOIN room_types rt ON r.room_type_id = rt.room_type_id ";
    
    // Apply room type filter if specified
    if ($room_type != 'all') {
        $room_type_query .= " WHERE r.room_type_id = ? ";
        $rt_params = [$room_type];
        $rt_types = "i";
    }
    
    $room_type_query .= " GROUP BY rt.type_name";
    
    $stmt = $conn->prepare($room_type_query);
    
    if (isset($rt_params) && isset($rt_types)) {
        $stmt->bind_param($rt_types, ...$rt_params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $room_type_data = [];
    while ($row = $result->fetch_assoc()) {
        $available = $row['count'] - $row['occupied'];
        $room_type_data[] = [
            'type' => $row['type_name'],
            'total' => (int)$row['count'],
            'occupied' => (int)$row['occupied'],
            'available' => $available,
            'occupancy_rate' => $row['count'] > 0 ? round(($row['occupied'] / $row['count']) * 100, 1) : 0
        ];
    }
    
    // Get revenue vs occupancy data for the last 12 months
    $revenue_occupancy_query = "SELECT 
                               DATE_FORMAT(b.check_in_date, '%Y-%m') as month,
                               DATE_FORMAT(b.check_in_date, '%b %Y') as month_name,
                               SUM(b.total_price) as revenue,
                               COUNT(DISTINCT b.room_id) as occupied_rooms,
                               (SELECT COUNT(*) FROM rooms) as total_rooms
                               FROM bookings b
                               WHERE b.check_in_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                               GROUP BY DATE_FORMAT(b.check_in_date, '%Y-%m'), DATE_FORMAT(b.check_in_date, '%b %Y')
                               ORDER BY month";
    
    $stmt = $conn->prepare($revenue_occupancy_query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $revenue_occupancy_data = [
        'dates' => [],
        'revenue' => [],
        'occupancy' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $revenue_occupancy_data['dates'][] = $row['month_name'];
        $revenue_occupancy_data['revenue'][] = (float)$row['revenue'];
        $occupancy_rate = $row['total_rooms'] > 0 ? round(($row['occupied_rooms'] / $row['total_rooms']) * 100, 1) : 0;
        $revenue_occupancy_data['occupancy'][] = $occupancy_rate;
    }
    
    // Get weekly occupancy trend data
    $weekly_trend_query = "SELECT 
                          CONCAT('W', WEEK(check_in_date), ' ', YEAR(check_in_date)) as week,
                          COUNT(DISTINCT room_id) as occupied_rooms,
                          (SELECT COUNT(*) FROM rooms) as total_rooms
                          FROM bookings
                          WHERE check_in_date >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
                          GROUP BY WEEK(check_in_date), YEAR(check_in_date)
                          ORDER BY YEAR(check_in_date), WEEK(check_in_date)";
    
    $stmt = $conn->prepare($weekly_trend_query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $occupancy_trend = [];
    while ($row = $result->fetch_assoc()) {
        $occupancy_rate = $row['total_rooms'] > 0 ? round(($row['occupied_rooms'] / $row['total_rooms']) * 100, 1) : 0;
        $occupancy_trend[$row['week']] = $occupancy_rate;
    }
    
    // Get booking trends data by calling the room booking trends API
    $booking_trends_url = 'get_room_booking_trends.php';
    $booking_trends_params = [
        'period' => $period
    ];
    
    if ($period == 'custom' && !empty($start_date) && !empty($end_date)) {
        $booking_trends_params['start_date'] = $start_date;
        $booking_trends_params['end_date'] = $end_date;
    }
    
    if ($room_type != 'all') {
        $booking_trends_params['room_type'] = $room_type;
    }
    
    // Create a direct include of the file instead of using cURL
    // This avoids HTTP issues and ensures proper execution
    ob_start();
    $_GET = $booking_trends_params; // Set the GET parameters
    include __DIR__ . '/get_room_booking_trends.php'; // Include the file directly
    $booking_trends_data = ob_get_clean();
    
    // Process the results
    $booking_trends_response = json_decode($booking_trends_data, true);
    
    if ($booking_trends_response && isset($booking_trends_response['data'])) {
        $most_booked_rooms = $booking_trends_response['data']['most_booked_rooms'] ?? [];
        $peak_booking_days = $booking_trends_response['data']['peak_booking_days'] ?? [];
    } else {
        // If API call fails, provide empty data
        $most_booked_rooms = [];
        $peak_booking_days = [
            'days' => [],
            'counts' => []
        ];
    }
    
    // Return all dashboard data
    echo json_encode([
        'success' => true,
        'data' => [
            'total_rooms' => $total_rooms,
            'occupancy' => $occupancy_data,
            'bookings' => $bookings_data,
            'daily_occupancy' => $daily_occupancy,
            'room_types' => $room_type_data,
            'revenue_occupancy' => $revenue_occupancy_data,
            'occupancy_trend' => $occupancy_trend,
            'most_booked_rooms' => $most_booked_rooms,
            'peak_booking_days' => $peak_booking_days
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
