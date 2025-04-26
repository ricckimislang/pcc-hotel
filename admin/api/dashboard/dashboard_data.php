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
    
    // Get occupancy forecast for the next 30 days
    $forecast_query = "SELECT 
                      DATE_FORMAT(forecast_date, '%Y-%m-%d') as date,
                      DATE_FORMAT(forecast_date, '%d %b') as date_display,
                      confirmed_rooms,
                      predicted_rooms,
                      total_rooms,
                      ROUND((confirmed_rooms / total_rooms) * 100, 1) as confirmed_percentage,
                      ROUND((predicted_rooms / total_rooms) * 100, 1) as predicted_percentage
                      FROM (
                          SELECT 
                              d.date as forecast_date,
                              COUNT(DISTINCT b.room_id) as confirmed_rooms,
                              FLOOR(COUNT(DISTINCT b.room_id) * 1.2) as predicted_rooms, -- Simple prediction based on confirmed
                              (SELECT COUNT(*) FROM rooms) as total_rooms
                          FROM 
                              (
                                  SELECT CURDATE() + INTERVAL (a.a + (10 * b.a)) DAY as date
                                  FROM (SELECT 0 as a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) as a
                                  CROSS JOIN (SELECT 0 as a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3) as b
                                  WHERE CURDATE() + INTERVAL (a.a + (10 * b.a)) DAY <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                              ) d
                          LEFT JOIN bookings b ON d.date BETWEEN b.check_in_date AND b.check_out_date
                              AND b.booking_status IN ('confirmed', 'checked_in')
                          GROUP BY d.date
                      ) forecast
                      ORDER BY forecast_date";
    
    $stmt = $conn->prepare($forecast_query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $forecast_data = [
        'dates' => [],
        'confirmed' => [],
        'predicted' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        $forecast_data['dates'][] = $row['date_display'];
        $forecast_data['confirmed'][] = (float)$row['confirmed_percentage'];
        $forecast_data['predicted'][] = (float)$row['predicted_percentage'];
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
    
    // Return all data
    echo json_encode([
        'success' => true,
        'data' => [
            'occupancy' => $occupancy_data,
            'total_rooms' => $total_rooms,
            'bookings' => $bookings_data,
            'daily_occupancy' => $daily_occupancy,
            'room_types' => $room_type_data,
            'revenue_occupancy' => $revenue_occupancy_data,
            'occupancy_forecast' => $forecast_data,
            'occupancy_trend' => $occupancy_trend
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
