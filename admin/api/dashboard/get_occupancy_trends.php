<?php
// Include necessary files
require_once '../../../config/db.php';
require_once '../includes/functions.php';

// Set header to return JSON
header('Content-Type: application/json');

// Handle CORS if needed
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Get trend type from query params (default to weekly)
$trendType = isset($_GET['type']) ? $_GET['type'] : 'weekly';

try {
    // Connect to database
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Define date format and group by clause based on trend type
    switch ($trendType) {
        case 'monthly':
            $dateFormat = '%Y-%m';
            $labelFormat = '%b %Y'; // Month Year (Jan 2023)
            $interval = 'INTERVAL 12 MONTH';
            break;
        case 'quarterly':
            $dateFormat = '%Y-Q%q';
            $labelFormat = 'Q%q %Y'; // Q1 2023
            $interval = 'INTERVAL 8 QUARTER';
            break;
        case 'weekly':
        default:
            $dateFormat = '%Y-W%v';
            $labelFormat = 'W%v %Y'; // W01 2023
            $interval = 'INTERVAL 12 WEEK';
            $trendType = 'weekly';
            break;
    }
    
    // Prepare and execute SQL query for occupancy trends
    $sql = "WITH DateRanges AS (
                SELECT 
                    DATE_FORMAT(d.date, '$dateFormat') AS period,
                    DATE_FORMAT(d.date, '$labelFormat') AS period_label,
                    COUNT(DISTINCT r.room_id) AS total_rooms
                FROM 
                    (
                        SELECT CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY AS date
                        FROM (SELECT 0 AS a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS a
                        CROSS JOIN (SELECT 0 AS a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS b
                        CROSS JOIN (SELECT 0 AS a UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) AS c
                        WHERE CURDATE() - INTERVAL (a.a + (10 * b.a) + (100 * c.a)) DAY >= DATE_SUB(CURDATE(), $interval)
                    ) d
                CROSS JOIN rooms r
                WHERE r.is_active = 1
                GROUP BY 
                    DATE_FORMAT(d.date, '$dateFormat'),
                    DATE_FORMAT(d.date, '$labelFormat')
                ORDER BY 
                    MIN(d.date)
            ),
            OccupiedRooms AS (
                SELECT 
                    DATE_FORMAT(b.check_in_date, '$dateFormat') AS period,
                    COUNT(DISTINCT b.room_id) AS occupied_rooms
                FROM 
                    bookings b
                WHERE 
                    b.booking_status IN ('confirmed', 'checked_in', 'checked_out')
                    AND b.check_in_date >= DATE_SUB(CURDATE(), $interval)
                GROUP BY 
                    DATE_FORMAT(b.check_in_date, '$dateFormat')
            )
            SELECT 
                dr.period_label,
                IFNULL(ROUND((or2.occupied_rooms / dr.total_rooms) * 100, 1), 0) AS occupancy_rate
            FROM 
                DateRanges dr
            LEFT JOIN 
                OccupiedRooms or2 ON dr.period = or2.period
            ORDER BY 
                dr.period";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format data for chart
    $trendData = [];
    foreach ($results as $row) {
        $trendData[$row['period_label']] = (float) $row['occupancy_rate'];
    }
    
    // Output as JSON
    echo json_encode($trendData);
    
} catch (PDOException $e) {
    // Handle database errors
    $error = ['error' => 'Database error: ' . $e->getMessage()];
    echo json_encode($error);
    error_log('Occupancy trends API error: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle other exceptions
    $error = ['error' => 'Error: ' . $e->getMessage()];
    echo json_encode($error);
    error_log('Occupancy trends API error: ' . $e->getMessage());
} 