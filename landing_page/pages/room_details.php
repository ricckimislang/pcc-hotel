<?php
require_once '../../config/db.php';

// Get room type ID from URL parameter
$room_type_id = $_GET['room_type_id'];

// First get room type details
$type_query = "SELECT * FROM room_types WHERE room_type_id = ?";
$type_stmt  = $conn->prepare($type_query);
$type_stmt->bind_param("i", $room_type_id);
$type_stmt->execute();
$type_result = $type_stmt->get_result();
$room_type   = $type_result->fetch_assoc();
$type_stmt->close();

// Then get available rooms of this type
$rooms_query = "SELECT r.*, rt.floor_type, rm.* FROM rooms r LEFT JOIN room_types rt ON r.room_type_id = rt.room_type_id LEFT JOIN room_media rm ON rt.room_type_id = rm.room_type_id WHERE r.status = 'available' AND r.room_type_id = ?";
$rooms_stmt  = $conn->prepare($rooms_query);
$rooms_stmt->bind_param("i", $room_type_id);
$rooms_stmt->execute();
$rooms_result    = $rooms_stmt->get_result();
$available_rooms = [];

while ($room = $rooms_result->fetch_assoc()) {
    $available_rooms[] = $room;
}
$rooms_stmt->close();

// Check if room type data is available
if ($room_type) {
    $type_name   = $room_type['type_name'];
    $description = $room_type['description'];
    $base_price  = $room_type['base_price'];
    $capacity    = $room_type['capacity'];
    $amenities   = $room_type['amenities'];

    // Get first available room details if any exist
    if (! empty($available_rooms)) {
        $room        = $available_rooms[0];
        $room_id     = $room['room_id'];
        $room_number = $room['room_number'];
        $floor       = $room['floor_type'];
        $status      = $room['status'];
        $panorama_image = $room['panorama_image'];
    } else {
        $room_id     = null;
        $room_number = 'N/A';
        $floor       = 'N/A';
        $status      = 'No rooms available';
        $card_image  = null;
        $panorama_image = null;
    }
} else {
    echo "Room type not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include_once '../includes/head.php'; ?>
    <link rel="stylesheet" href="../css/room_details.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
</head>

<body>
    <div class="back-nav">
        <a href="index.php" class="back-button">
            <i class="fas fa-arrow-left"></i>
            Back to Rooms
        </a>
    </div>

    <div class="room-details">
        <div class="hero-section">
            <img src="../assets/images/luxury-twin.jpg" alt="<?php echo $type_name; ?>" class="hero-image">
            <div class="hero-overlay">
                <div class="hero-title">
                    <h1><?php echo $type_name; ?></h1>
                    <span class="subtitle">Exceptional Comfort and Elegance</span>
                </div>
            </div>
        </div>

        <div class="room-info" id="available-rooms">
            <div class="room-label">Room Type</div>
            <h1 class="room-title"><?php echo $type_name; ?></h1>

            <div class="room-meta">
                <div class="meta-item">
                    <div class="meta-label">Price</div>
                    <div class="meta-value">$<?php echo number_format($base_price, 2); ?></div>
                </div>
                <div class="meta-item">
                    <div class="meta-label">Capacity</div>
                    <div class="meta-value"><?php echo $capacity; ?> persons</div>
                </div>
            </div>

            <p class="room-description"><?php echo $description; ?></p>

            <div class="available-rooms">
                <h2>Available Rooms</h2>
                <?php if (! empty($available_rooms)): ?>
                    <div class="rooms-grid">
                        <?php foreach ($available_rooms as $room): ?>
                            <div class="room-card">
                                <div class="room-card-header">
                                    <div class="room-number">
                                        <i class="fas fa-door-open"></i>
                                        <h3>Room <?php echo $room['room_number']; ?></h3>
                                    </div>
                                    <div class="room-floor">
                                        <i class="fas fa-building"></i>
                                        <span class="floor-badge">
                                            <?php echo $room['floor_type'] === 1 ? "Ground" : "Second"; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="room-card-body">
                                    <div class="room-status">
                                        <span class="status-badge <?php echo strtolower($room['status']); ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo ucfirst($room['status']); ?>
                                        </span>
                                    </div>
                                    <div class="room-actions">
                                        <a href="booking-page.php?room_id=<?php echo $room['room_id']; ?>" class="select-room-btn">
                                            <i class="fas fa-calendar-check"></i>
                                            Select Room
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-rooms-message">
                        <i class="fas fa-bed-slash"></i>
                        <p>No rooms currently available for this type.</p>
                        <p class="sub-message">Please check back later or contact our reservations team.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="room-photos">
            <h2 class="section-title">Room Gallery</h2>
            <p class="section-subtitle">Explore our luxurious accommodations</p>
            <div class="photos-grid">
                <?php
                $photoGridstmt = $conn->prepare("SELECT image_path FROM room_gallery WHERE room_type_id = ?");
                $photoGridstmt->bind_param("i", $room_type_id);
                $photoGridstmt->execute();
                $result = $photoGridstmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='photo-item'>";
                    echo "<img src='../../public/room_images_details/{$row['image_path']}' alt='Room Gallery Image'>";
                    echo "</div>";
                }
                $photoGridstmt->close();
                ?>
            </div>
            <!-- Panorama Image Section -->
            <?php if (!empty($panorama_image)): ?>
                <div class="panorama-section">
                    <h2 class="section-title">360° Room View</h2>
                    <p class="section-subtitle">Experience the room in immersive 360°</p>
                    <div class="panorama-container">
                        <a href="panorama_viewer.php?room_id=<?php echo $room_id; ?>" class="panorama-link">
                            <img src="../../public/panoramas/<?php echo htmlspecialchars($panorama_image); ?>" alt="360° view of <?php echo htmlspecialchars($type_name); ?>" class="panorama-image">
                            <div class="panorama-overlay">
                                <span class="panorama-hint"><i class="fas fa-vr-cardboard"></i> View in 360°</span>
                            </div>
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <div class="no-panorama-message">
                    <p><i class="fas fa-exclamation-triangle"></i> No panorama image available for this room.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="amenities-section">
            <h2 class="section-title">Room Amenities</h2>
            <div class="amenities-grid">
                <?php
                $amenities_array = array_map('strtoupper', explode(',', $amenities));
                $amenity_icons   = [
                    'WIFI'             => 'fa-wifi',
                    'TV'               => 'fa-tv',
                    'AIR CONDITIONING' => 'fa-snowflake',
                    'MINI BAR'         => 'fa-wine-glass',
                    'SAFE'             => 'fa-vault',
                    'ROOM SERVICE'     => 'fa-concierge-bell',
                    'COFFEE MAKER'     => 'fa-mug-hot',
                    'HAIR DRYER'       => 'fa-wind',
                    'BATH AMENITIES'   => 'fa-bath',
                ];
                foreach ($amenities_array as $amenity) {
                    $amenity = trim($amenity);
                    $icon    = isset($amenity_icons[$amenity]) ? $amenity_icons[$amenity] : 'fa-check';
                    echo "<div class='amenity-item'>";
                    echo "<i class='fas {$icon} amenity-icon'></i>";
                    echo "<span class='amenity-text'>{$amenity}</span>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <div class="book-now-container">
            <a href="#available-rooms" class="book-now">Book Now</a>
        </div>
    </div>

    <style>
        /* Add styles for panorama link */
        .panorama-link {
            display: block;
            position: relative;
            cursor: pointer;
        }

        /* Room Card Styles */
        .rooms-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 15px;
            padding: 10px;
        }

        .room-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 12px;
            transition: transform 0.2s ease;
        }

        .room-card:hover {
            transform: translateY(-2px);
        }

        .room-card-header {
            margin-bottom: 8px;
        }

        .room-number {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 4px;
        }

        .room-number h3 {
            font-size: 1rem;
            margin: 0;
        }

        .room-floor {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
        }

        .floor-badge {
            background: #f5f5f5;
            padding: 2px 8px;
            border-radius: 4px;
        }

        .room-card-body {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }

        .room-status {
            font-size: 0.85rem;
        }

        .status-badge {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-badge i {
            font-size: 0.7rem;
        }

        .select-room-btn {
            font-size: 0.85rem;
            padding: 6px 12px;
            background: #1a73e8;
            color: white;
            border-radius: 4px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: background 0.2s ease;
        }

        .select-room-btn:hover {
            background: #1557b0;
        }

        /* Existing panorama styles */
        .panorama-hint {
            display: flex;
            align-items: center;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 15px;
            border-radius: 30px;
            font-size: 14px;
            transition: transform 0.3s ease;
        }

        .panorama-hint i {
            margin-right: 8px;
            font-size: 16px;
        }

        .panorama-link:hover .panorama-hint {
            transform: scale(1.05);
        }

        .panorama-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(0, 0, 0, 0.2);
            transition: background 0.3s ease;
        }

        .panorama-link:hover .panorama-overlay {
            background: rgba(0, 0, 0, 0.4);
        }
    </style>
</body>

</html>