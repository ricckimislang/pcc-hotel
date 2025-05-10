<?php
declare(strict_types=1);

// Error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Database configuration
$host = 'localhost';
$user = 'root';
$pass = ''; // Set your MySQL password here
$db   = 'pcc-hotel';

// Generate backup filename
$filename = $db . '_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Function to backup the database
function createDatabaseBackup(string $host, string $user, string $pass, string $dbName): ?string 
{
    try {
        // Connect to database
        $mysqli = new mysqli($host, $user, $pass, $dbName);
        
        if ($mysqli->connect_error) {
            throw new Exception("Connection failed: " . $mysqli->connect_error);
        }
        
        // Set charset to utf8mb4
        $mysqli->set_charset("utf8mb4");
        
        // Start output buffer to capture SQL content
        ob_start();
        
        // Add header with creation info
        echo "-- Database Backup for '$dbName'\n";
        echo "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        echo "-- Server version: " . $mysqli->server_info . "\n\n";
        
        // Get all tables
        $tables = [];
        $result = $mysqli->query("SHOW TABLES");
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        
        // Create SQL for each table
        foreach ($tables as $table) {
            // Get create table statement
            $result = $mysqli->query("SHOW CREATE TABLE `$table`");
            $row = $result->fetch_row();
            
            echo "-- Table structure for '$table'\n\n";
            echo "DROP TABLE IF EXISTS `$table`;\n";
            echo $row[1] . ";\n\n";
            
            // Get table data
            $result = $mysqli->query("SELECT * FROM `$table`");
            
            if ($result->num_rows > 0) {
                echo "-- Data for '$table'\n\n";
                
                $fields = [];
                while ($field = $result->fetch_field()) {
                    $fields[] = $field->name;
                }
                
                // Create INSERT statements
                $insertHeader = "INSERT INTO `$table` (`" . implode("`, `", $fields) . "`) VALUES\n";
                $insertCount = 0;
                $rowCount = 0;
                
                while ($row = $result->fetch_assoc()) {
                    // Start a new insert statement for every 100 rows
                    if ($insertCount === 0) {
                        echo $insertHeader;
                    }
                    
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = "NULL";
                        } else {
                            $values[] = "'" . $mysqli->real_escape_string($value) . "'";
                        }
                    }
                    
                    // Add comma or semicolon as needed
                    $rowCount++;
                    $insertCount++;
                    if ($insertCount >= 100 || $rowCount >= $result->num_rows) {
                        echo "(" . implode(", ", $values) . ");\n";
                        $insertCount = 0;
                    } else {
                        echo "(" . implode(", ", $values) . "),\n";
                    }
                }
                echo "\n";
            }
        }
        
        $mysqli->close();
        
        // Get the content from buffer and clean the buffer
        $backup = ob_get_clean();
        return $backup;
    } catch (Exception $e) {
        if (ob_get_level()) {
            ob_end_clean();
        }
        error_log("Backup failed: " . $e->getMessage());
        return null;
    }
}

// Perform the backup and send as download
try {
    $backup = createDatabaseBackup($host, $user, $pass, $db);
    
    if ($backup) {
        // Send headers for file download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($backup));
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        // Output the backup content
        echo $backup;
        exit;
    } else {
        header('Content-Type: text/plain');
        echo "❌ Backup failed. Check credentials or database connection.\n";
    }
} catch (Exception $e) {
    header('Content-Type: text/plain');
    echo "❌ Error: " . $e->getMessage() . "\n";
}
