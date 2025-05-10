<?php
declare(strict_types=1);

/**
 * Database Import API
 * 
 * Handles importing SQL files into the database
 */

// Set headers to prevent caching
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Include database configuration
require_once __DIR__ . '/../../../config/db.php';

// Function to validate the SQL file
function validateSqlFile(array $file): array
{
    $result = ['valid' => false, 'message' => ''];
    
    // Check if file was uploaded
    if (!isset($file['sqlFile']) || $file['sqlFile']['error'] !== UPLOAD_ERR_OK) {
        $result['message'] = 'No file uploaded or upload error occurred';
        return $result;
    }
    
    $uploadedFile = $file['sqlFile'];
    
    // Check file size (limit to 50MB)
    $maxSize = 52428800; // 50MB in bytes
    if ($uploadedFile['size'] > $maxSize) {
        $result['message'] = 'File is too large. Maximum size is 50MB';
        return $result;
    }
    
    // Check file extension
    $fileExtension = strtolower(pathinfo($uploadedFile['name'], PATHINFO_EXTENSION));
    if ($fileExtension !== 'sql') {
        $result['message'] = 'Only SQL files are allowed';
        return $result;
    }
    
    // Basic content check to ensure it's an SQL file
    $fileContent = file_get_contents($uploadedFile['tmp_name']);
    if (!$fileContent || !preg_match('/(CREATE|INSERT|UPDATE|DELETE|ALTER|DROP|SELECT)\s+/i', $fileContent)) {
        $result['message'] = 'The file does not appear to be a valid SQL file';
        return $result;
    }
    
    $result['valid'] = true;
    return $result;
}

/**
 * Truncate all tables in the database
 * 
 * @param mysqli $conn Database connection
 * @return bool True if successful, false otherwise
 */
function truncateAllTables(mysqli $conn): bool
{
    try {
        // Disable foreign key checks to avoid constraint errors
        $conn->query('SET FOREIGN_KEY_CHECKS = 0');
        
        // Get all tables in the database
        $tablesResult = $conn->query("SHOW TABLES");
        
        if (!$tablesResult) {
            return false;
        }
        
        while ($row = $tablesResult->fetch_row()) {
            $tableName = $row[0];
            // Truncate each table
            $result = $conn->query("TRUNCATE TABLE `$tableName`");
            if (!$result) {
                return false;
            }
        }
        
        // Re-enable foreign key checks
        $conn->query('SET FOREIGN_KEY_CHECKS = 1');
        
        return true;
    } catch (Exception $e) {
        error_log('Error truncating tables: ' . $e->getMessage());
        return false;
    }
}

// Function to import SQL file
function importSqlFile(string $filePath, mysqli $conn): array
{
    try {
        // Begin transaction
        $conn->begin_transaction();
        
        // Truncate all tables first
        if (!truncateAllTables($conn)) {
            $conn->rollback();
            return ['success' => false, 'message' => 'Failed to truncate database tables'];
        }
        
        // Read SQL file
        $sql = file_get_contents($filePath);
        
        // Split file into individual SQL statements
        $statements = explode(';', $sql);
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $result = $conn->query($statement);
                if (!$result) {
                    // If error, rollback
                    $conn->rollback();
                    
                    return [
                        'success' => false, 
                        'message' => 'Error executing SQL: ' . $conn->error . ' in statement: ' . substr($statement, 0, 100) . '...'
                    ];
                }
            }
        }
        
        // Commit transaction if all queries executed successfully
        $conn->commit();
        
        return ['success' => true, 'message' => 'Database import completed successfully'];
    } catch (Exception $e) {
        if ($conn->ping()) {
            $conn->rollback();
        }
        
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

// Process the upload
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate the uploaded file
        $validation = validateSqlFile($_FILES);
        
        if (!$validation['valid']) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => $validation['message']]);
            exit;
        }
        
        // Process the import
        $filePath = $_FILES['sqlFile']['tmp_name'];
        $result = importSqlFile($filePath, $conn);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => $result['message']]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log('Database import error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred. Check error logs for details.']);
} 