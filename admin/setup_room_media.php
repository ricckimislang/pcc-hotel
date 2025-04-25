<?php
require_once '../config/db.php';

// Create the required directories
$directories = [
    'uploads/room_images',
    'uploads/panoramas'
];

$success = true;
$messages = [];

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!file_exists($path)) {
        if (mkdir($path, 0755, true)) {
            $messages[] = "Created directory: $dir";
        } else {
            $success = false;
            $messages[] = "Failed to create directory: $dir";
        }
    } else {
        $messages[] = "Directory already exists: $dir";
    }
}

// Create the database table
$sql = file_get_contents(__DIR__ . '/sql/room_media_table.sql');

if ($conn->multi_query($sql)) {
    $messages[] = "Room media table created or already exists";
} else {
    $success = false;
    $messages[] = "Failed to create room media table: " . $conn->error;
}

// Close the connection
$conn->close();

// Output results
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Media Setup</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        .setup-container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .message-list {
            margin-top: 20px;
        }
        .message-item {
            padding: 10px;
            border-left: 4px solid #ccc;
            margin-bottom: 10px;
            background-color: #f8f9fa;
        }
        .message-item.success {
            border-left-color: #28a745;
        }
        .message-item.error {
            border-left-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1 class="mb-4">Room Media System Setup</h1>
        
        <div class="alert <?php echo $success ? 'alert-success' : 'alert-danger'; ?>">
            <strong><?php echo $success ? 'Setup Completed' : 'Setup Failed'; ?></strong>
            <p><?php echo $success ? 'The room media system has been set up successfully.' : 'There were issues during setup. Please check the details below.'; ?></p>
        </div>
        
        <h2>Setup Details</h2>
        <div class="message-list">
            <?php foreach ($messages as $message): ?>
                <div class="message-item <?php echo strpos($message, 'Failed') === false ? 'success' : 'error'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-4">
            <a href="pages/room_media.php" class="btn btn-primary">Go to Room Media Manager</a>
            <a href="index.php" class="btn btn-secondary ms-2">Return to Dashboard</a>
        </div>
    </div>
</body>
</html> 