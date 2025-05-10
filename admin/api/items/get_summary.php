<?php
declare(strict_types=1);
require_once '../../../config/db.php';

header('Content-Type: application/json');

try {
    // Get total items count
    $countQuery = "SELECT COUNT(*) as total_items FROM items";
    $countResult = mysqli_query($conn, $countQuery);
    
    if (!$countResult) {
        throw new Exception(mysqli_error($conn));
    }
    
    $totalItems = mysqli_fetch_assoc($countResult)['total_items'];
    
    // Get average price
    $avgQuery = "SELECT AVG(item_price) as average_price FROM items";
    $avgResult = mysqli_query($conn, $avgQuery);
    
    if (!$avgResult) {
        throw new Exception(mysqli_error($conn));
    }
    
    $averagePrice = mysqli_fetch_assoc($avgResult)['average_price'] ?? 0;
    
    // Get most recent update timestamp
    $updateQuery = "SELECT MAX(updated_at) as last_update FROM items";
    $updateResult = mysqli_query($conn, $updateQuery);
    
    if (!$updateResult) {
        throw new Exception(mysqli_error($conn));
    }
    
    $lastUpdate = mysqli_fetch_assoc($updateResult)['last_update'];
    
    // Create the response data
    $summaryData = [
        'total_items' => $totalItems,
        'average_price' => $averagePrice,
        'last_update' => $lastUpdate
    ];
    
    echo json_encode($summaryData);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}

mysqli_close($conn); 