<?php
// Include database connection
require_once '../../config/db.php';

// First check if we have a direct image path
if (isset($_GET['image'])) {
    $imagePath = $_GET['image'];

    // Security check - validate the path exists
    if (!file_exists($imagePath)) {
        header('Location: index.php?status=error&message=Invalid image path');
        exit;
    }

    // Set minimal panorama info
    $panorama = [
        'title' => basename($imagePath),
        'file_path' => $imagePath
    ];
}
// Otherwise check if we have a room_id to fetch from the database
else if (isset($_GET['room_id'])) {
    // Get the room ID from URL
    $room_id = (int)$_GET['room_id'];

    // Fetch the room and panorama data
    $query = "SELECT r.*, rt.type_name, rm.panorama_image 
              FROM rooms r 
              LEFT JOIN room_types rt ON r.room_type_id = rt.room_type_id 
              LEFT JOIN room_media rm ON r.room_id = rm.room_id 
              WHERE r.room_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: index.php?status=error&message=Room not found');
        exit;
    }

    $room = $result->fetch_assoc();
    $stmt->close();

    // Check if panorama exists
    if (empty($room['panorama_image'])) {
        header('Location: index.php?status=error&message=No panorama available for this room');
        exit;
    }

    // Set panorama info
    $panorama = [
        'title' => 'Room ' . $room['room_number'] . ' - ' . $room['type_name'],
        'file_path' => '../../public/panoramas/' . $room['panorama_image']
    ];

    // Check if the image file exists
    if (!file_exists($panorama['file_path'])) {
        header('Location: index.php?status=error&message=Panorama file not found');
        exit;
    }
} else {
    // No image parameter provided
    header('Location: index.php?status=error&message=No image specified');
    exit;
}

// Get image information for display
if (!isset($panorama['filesize']) && file_exists($panorama['file_path'])) {
    $panorama['filesize'] = filesize($panorama['file_path']);
}
$imageInfo = getimagesize($panorama['file_path']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>360° Panorama Viewer - <?php echo htmlspecialchars($panorama['title']); ?></title>
    <?php include_once '../includes/head.php'; ?>
    <!-- Photo Sphere Viewer CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/photo-sphere-viewer@4/dist/photo-sphere-viewer.min.css">
    <style>
        :root {
            --primary-color: #9e7956;
            --primary-dark: #876543;
            --secondary-color: #1e3a5f;
            --light-bg: #f8f7f4;
            --dark-text: #2c2c2c;
            --light-text: #f8f7f4;
            --accent-gold: #d4af37;
            --border-radius: 8px;
            --box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(158, 121, 86, 0.2);
            position: relative;
        }

        header:after {
            content: "";
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 80px;
            height: 2px;
            background-color: var(--primary-color);
        }

        h1 {
            margin: 0;
            color: var(--secondary-color);
            font-family: 'Playfair Display', serif;
            font-weight: 600;
            font-size: 2.2rem;
            letter-spacing: 0.5px;
        }

        h2 {
            font-family: 'Playfair Display', serif;
            color: var(--secondary-color);
            margin-top: 0;
            font-weight: 500;
            letter-spacing: 0.3px;
        }

        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            border: 1px solid var(--primary-color);
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .back-link i {
            margin-right: 8px;
        }

        .back-link:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #viewer-container {
            width: 100%;
            height: 600px;
            background-color: #000;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--box-shadow);
            position: relative;
            transition: height 0.3s ease;
        }

        @media (min-height: 900px) {
            #viewer-container {
                height: 700px;
            }
        }

        .viewer-overlay {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: rgba(0, 0, 0, 0.6);
            padding: 8px 16px;
            border-radius: 30px;
            color: white;
            font-size: 0.9rem;
            z-index: 10;
            pointer-events: none;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        #viewer-container:hover .viewer-overlay {
            opacity: 1;
            transform: translateY(0);
        }

        .image-info {
            margin-top: 30px;
            background-color: white;
            padding: 30px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            position: relative;
            overflow: hidden;
        }

        .image-info:before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary-color), var(--secondary-color));
        }

        .metadata {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .metadata-item {
            background: rgba(248, 247, 244, 0.8);
            padding: 18px;
            border-radius: var(--border-radius);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .metadata-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .metadata-label {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .metadata-label:before {
            content: "";
            display: inline-block;
            width: 12px;
            height: 2px;
            background-color: var(--primary-color);
            margin-right: 8px;
        }

        .metadata-value {
            margin-top: 8px;
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* Override PhotoSphereViewer styles for a more premium look */
        .psv-container {
            background: linear-gradient(to bottom, #1a1a1a, #000);
        }

        .psv-button {
            transition: all 0.2s ease;
        }

        .psv-button:hover {
            background-color: var(--primary-color) !important;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            h1 {
                font-size: 1.8rem;
            }

            .metadata {
                grid-template-columns: 1fr;
            }

            #viewer-container {
                height: 400px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>360° Panorama Experience</h1>
            <a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php'; ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Room
            </a>
        </header>

        <div id="viewer-container">
            <div class="viewer-overlay">Move to explore in 360°</div>
        </div>

        <div class="image-info">
            <h2><?php echo htmlspecialchars($panorama['title']); ?></h2>

            <div class="metadata">
                <div class="metadata-item">
                    <div class="metadata-label">Experience</div>
                    <div class="metadata-value">Immersive 360° View</div>
                </div>

                <div class="metadata-item">
                    <div class="metadata-label">Controls</div>
                    <div class="metadata-value">Mouse drag or touch to explore</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Photo Sphere Viewer JS -->
    <script src="https://cdn.jsdelivr.net/npm/three/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uevent@2/browser.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/photo-sphere-viewer@4/dist/photo-sphere-viewer.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const viewer = new PhotoSphereViewer.Viewer({
                container: document.getElementById('viewer-container'),
                panorama: '<?php echo $panorama['file_path']; ?>',
                navbar: [
                    'autorotate', 'zoom', 'download', 'fullscreen'
                ],
                defaultZoomLvl: 0,
                mousewheel: true,
                captureCursor: true,
                defaultLat: 0,
                defaultLong: 0,
                touchmoveTwoFingers: true,
                plugins: []
            });

            // Delay showing the overlay hint
            setTimeout(() => {
                document.querySelector('.viewer-overlay').style.opacity = 1;
                document.querySelector('.viewer-overlay').style.transform = 'translateY(0)';

                // Hide it after 5 seconds
                setTimeout(() => {
                    document.querySelector('.viewer-overlay').style.opacity = 0;
                }, 5000);
            }, 1000);
        });
    </script>
</body>

</html>