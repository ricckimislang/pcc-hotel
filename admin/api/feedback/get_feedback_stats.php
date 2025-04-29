<?php

/**
 * API Endpoint for Fetching Feedback Statistics
 * Returns statistics about customer feedback for the dashboard
 */

header('Content-Type: application/json');
require_once '../../../config/db.php';

// Set default response
$response = [
    'status' => false,
    'message' => 'Failed to fetch feedback statistics',
    'data' => null
];

try {
    // Get overall statistics
    $statsQuery = "SELECT 
                    COUNT(*) as total_count,
                    ROUND(AVG(rating), 1) as average_rating,
                    SUM(CASE WHEN rating = 5 THEN 1 ELSE 0 END) as five_star,
                    SUM(CASE WHEN rating = 4 THEN 1 ELSE 0 END) as four_star,
                    SUM(CASE WHEN rating = 3 THEN 1 ELSE 0 END) as three_star,
                    SUM(CASE WHEN rating = 2 THEN 1 ELSE 0 END) as two_star,
                    SUM(CASE WHEN rating = 1 THEN 1 ELSE 0 END) as one_star
                FROM feedback";

    $stmtStats = $conn->prepare($statsQuery);

    if (!$stmtStats) {
        throw new Exception("Prepare failed for stats query: " . $conn->error);
    }

    $stmtStats->execute();
    $resultStats = $stmtStats->get_result();
    $stats = $resultStats->fetch_assoc();
    $stmtStats->close();

    // Get monthly trend (last 6 months)
    $trendQuery = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    DATE_FORMAT(created_at, '%b %Y') as month_name,
                    ROUND(AVG(rating), 1) as average_rating,
                    COUNT(*) as count
                FROM feedback
                WHERE created_at >= DATE_SUB(CURRENT_DATE(), INTERVAL 6 MONTH)
                GROUP BY month, month_name
                ORDER BY month";

    $stmtTrend = $conn->prepare($trendQuery);

    if (!$stmtTrend) {
        throw new Exception("Prepare failed for trend query: " . $conn->error);
    }

    $stmtTrend->execute();
    $resultTrend = $stmtTrend->get_result();

    $trend = [];
    while ($row = $resultTrend->fetch_assoc()) {
        $trend[] = $row;
    }

    $stmtTrend->close();

    // Get recent feedback (last 5)
    $recentQuery = "SELECT 
                    f.id,
                    CONCAT(c.first_name, ' ', c.last_name) AS customer_name,
                    f.rating,
                    f.comment,
                    r.room_number,
                    f.created_at
                FROM feedback f
                JOIN users c ON f.customer_id = c.user_id
                JOIN rooms r ON f.room_id = r.room_id
                ORDER BY f.created_at DESC
                LIMIT 5";

    $stmtRecent = $conn->prepare($recentQuery);

    if (!$stmtRecent) {
        throw new Exception("Prepare failed for recent query: " . $conn->error);
    }

    $stmtRecent->execute();
    $resultRecent = $stmtRecent->get_result();

    $recent = [];
    while ($row = $resultRecent->fetch_assoc()) {
        $recent[] = $row;
    }

    $stmtRecent->close();

    // Calculate percentages for rating distribution
    $totalRatings = intval($stats['total_count']);
    if ($totalRatings > 0) {
        $stats['five_star_percent'] = round(($stats['five_star'] / $totalRatings) * 100, 1);
        $stats['four_star_percent'] = round(($stats['four_star'] / $totalRatings) * 100, 1);
        $stats['three_star_percent'] = round(($stats['three_star'] / $totalRatings) * 100, 1);
        $stats['two_star_percent'] = round(($stats['two_star'] / $totalRatings) * 100, 1);
        $stats['one_star_percent'] = round(($stats['one_star'] / $totalRatings) * 100, 1);
    } else {
        $stats['five_star_percent'] = 0;
        $stats['four_star_percent'] = 0;
        $stats['three_star_percent'] = 0;
        $stats['two_star_percent'] = 0;
        $stats['one_star_percent'] = 0;
    }

    // Prepare response
    $response = [
        'status' => true,
        'message' => 'Feedback statistics fetched successfully',
        'data' => [
            'stats' => $stats,
            'trend' => $trend,
            'recent' => $recent
        ]
    ];

    echo json_encode($response);
    exit;
} catch (Exception $e) {
    $response['message'] = "Database error: " . $e->getMessage();

    // Log error to server error log
    error_log("Feedback Stats API Error: " . $e->getMessage());

    // Return error response
    http_response_code(500);
    echo json_encode($response);
    exit;
}
