<?php
/**
 * API Endpoint - Peak Booking Days
 * Returns booking frequency data by day of week, month, or year
 * 
 * Parameters:
 * - period (today, week, month, year, custom)
 * - booking_period (weekly, monthly, yearly)
 * - start_date, end_date (required for custom period)
 * - room_type (optional)
 */
header('Content-Type: application/json');
require_once '../../../config/db.php';

// Initialize response
$response = [
    'success' => false,
    'message' => '',
    'data' => null
];

try {
    // Validate required parameters
    if (!isset($_GET['period'])) {
        throw new Exception('Period parameter is required');
    }
    
    $period = $_GET['period'];
    $bookingPeriod = isset($_GET['booking_period']) ? $_GET['booking_period'] : 'weekly';
    $roomType = isset($_GET['room_type']) && $_GET['room_type'] !== 'all' ? $_GET['room_type'] : null;
    
    // Validate custom date range
    if ($period === 'custom') {
        if (!isset($_GET['start_date']) || !isset($_GET['end_date'])) {
            throw new Exception('Start date and end date are required for custom period');
        }
        $startDate = $_GET['start_date'];
        $endDate = $_GET['end_date'];
    }
    
    // Build date filter based on period
    $dateFilter = "";
    switch ($period) {
        case 'today':
            $dateFilter = "WHERE DATE(b.check_in_date) = CURDATE() OR DATE(b.booking_date) = CURDATE()";
            break;
        case 'week':
            $dateFilter = "WHERE YEARWEEK(b.check_in_date, 1) = YEARWEEK(CURDATE(), 1) OR YEARWEEK(b.booking_date, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $dateFilter = "WHERE YEAR(b.check_in_date) = YEAR(CURDATE()) AND MONTH(b.check_in_date) = MONTH(CURDATE()) 
                        OR YEAR(b.booking_date) = YEAR(CURDATE()) AND MONTH(b.booking_date) = MONTH(CURDATE())";
            break;
        case 'year':
            $dateFilter = "WHERE YEAR(b.check_in_date) = YEAR(CURDATE()) OR YEAR(b.booking_date) = YEAR(CURDATE())";
            break;
        case 'custom':
            $dateFilter = "WHERE (DATE(b.check_in_date) BETWEEN '$startDate' AND '$endDate' OR DATE(b.booking_date) BETWEEN '$startDate' AND '$endDate')";
            break;
        default:
            $dateFilter = "WHERE 1"; // No filter
    }
    
    // Add room type filter if specified
    if ($roomType !== null) {
        $dateFilter .= " AND r.room_type_id = $roomType";
    }
    
    // Different queries based on booking period
    $query = "";
    
    switch ($bookingPeriod) {
        case 'weekly':
            $query = "SELECT 
                        DAYNAME(b.booking_date) as day_name,
                        COUNT(*) as booking_count
                      FROM 
                        bookings b
                      LEFT JOIN 
                        rooms r ON b.room_id = r.room_id
                      $dateFilter
                      GROUP BY 
                        day_name
                      ORDER BY 
                        FIELD(day_name, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')";
            break;
            
        case 'monthly':
            $query = "SELECT 
                        MONTHNAME(b.booking_date) as month_name,
                        COUNT(*) as booking_count
                      FROM 
                        bookings b
                      LEFT JOIN 
                        rooms r ON b.room_id = r.room_id
                      $dateFilter
                      GROUP BY 
                        month_name
                      ORDER BY 
                        MONTH(b.booking_date)";
            break;
            
        case 'yearly':
            $query = "SELECT 
                        YEAR(b.booking_date) as year,
                        COUNT(*) as booking_count
                      FROM 
                        bookings b
                      LEFT JOIN 
                        rooms r ON b.room_id = r.room_id
                      $dateFilter
                      GROUP BY 
                        year
                      ORDER BY 
                        year";
            break;
        
        default:
            throw new Exception("Invalid booking period specified");
    }
    
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    // Format data for response
    $data = [];
    
    if ($bookingPeriod === 'weekly') {
        // Initialize all days of the week with 0
        $data = [
            'Monday' => 0,
            'Tuesday' => 0,
            'Wednesday' => 0,
            'Thursday' => 0,
            'Friday' => 0,
            'Saturday' => 0,
            'Sunday' => 0
        ];
        
        while ($row = $result->fetch_assoc()) {
            $data[$row['day_name']] = (int)$row['booking_count'];
        }
    } elseif ($bookingPeriod === 'monthly') {
        // Initialize all months with 0
        $months = [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December'
        ];
        
        foreach ($months as $month) {
            $data[$month] = 0;
        }
        
        while ($row = $result->fetch_assoc()) {
            $data[$row['month_name']] = (int)$row['booking_count'];
        }
    } elseif ($bookingPeriod === 'yearly') {
        while ($row = $result->fetch_assoc()) {
            $data[$row['year']] = (int)$row['booking_count'];
        }
    }
    
    // Successfully return data
    $response['success'] = true;
    $response['data'] = $data;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    if (isset($conn)) {
        $conn->close();
    }
    
    // Return JSON response
    echo json_encode($response);
}
