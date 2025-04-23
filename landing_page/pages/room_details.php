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
    $rooms_query = "SELECT * FROM rooms WHERE status = 'available' AND room_type_id = $room_type_id";
    $rooms_stmt  = $conn->prepare($rooms_query);
    $rooms_stmt->execute();
    $rooms_result    = $rooms_stmt->get_result();
    $available_rooms = [];
    while ($room = $rooms_result->fetch_assoc()) {
        $available_rooms[] = $room;
    }
    $rooms_stmt->close();
    $conn->close();

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
            $floor       = $room['floor'];
            $status      = $room['status'];
        } else {
            $room_id     = null;
            $room_number = 'N/A';
            $floor       = 'N/A';
            $status      = 'No rooms available';
        }
    } else {
        echo "Room type not found.";
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/room_details.css">
<?php include_once '../includes/head.php'; ?>

<body>
    <div class="room-details">
        <div class="hero-section">
            <img src="../assets/images/luxury-twin.jpg" alt="<?php echo $type_name; ?>" class="hero-image">
        </div>

        <div class="room-info" id="available-rooms">
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

            <div class="available-rooms" >
                <h2>Available Rooms</h2>
                <?php if (! empty($available_rooms)): ?>
                    <div class="rooms-grid">
                        <?php foreach ($available_rooms as $room): ?>
                            <div class="room-card">
                                <div class="room-card-header">
                                    <div class="room-number">
                                        <i class="fas fa-door-open"></i>
                                        <h3>Room                                                                                                                                                                                                                                                                                                                                                                                                                                                 <?php echo $room['room_number']; ?></h3>
                                    </div>
                                    <div class="room-floor">
                                        <i class="fas fa-building"></i>
                                        <span class="floor-badge">Floor                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                <?php echo $room['floor']; ?></span>
                                    </div>
                                </div>
                                <div class="room-card-body">
                                    <div class="room-status">
                                        <span class="status-badge                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          <?php echo strtolower($room['status']); ?>">
                                            <i class="fas fa-circle"></i>
                                            <?php echo ucfirst($room['status']); ?>
                                        </span>
                                    </div>
                                    <div class="room-actions">
                                        <a href="booking-page.php?room_id=<?php echo $room['room_id']; ?>" class="select-room-btn">
                                            <i class="fas fa-calendar-check"></i>
                                            Select Room
                                        </a>
                                        <button class="view-details-btn" data-room-id="<?php echo $room['room_id']; ?>">
                                            <i class="fas fa-info-circle"></i>
                                            View Details
                                        </button>
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
            <div class="photos-grid">
                <div class="photo-item">
                    <img src="../assets/images/gallery/room1.svg" alt="Luxury Room - Bedroom View">
                </div>
                <div class="photo-item">
                    <img src="../assets/images/gallery/room2.svg" alt="Luxury Room - Bathroom View">
                </div>
                <div class="photo-item">
                    <img src="../assets/images/gallery/room3.svg" alt="Luxury Room - Living Space">
                </div>
            </div>
        </div>

        <div class="amenities-section">
            <h2 class="section-title">Room Amenities</h2>
            <div class="amenities-grid">
                <?php
                    $amenities_array = explode(',', $amenities);
                    $amenity_icons   = [
                        'WiFi'             => 'fa-wifi',
                        'TV'               => 'fa-tv',
                        'Air Conditioning' => 'fa-snowflake',
                        'Mini Bar'         => 'fa-wine-glass',
                        'Safe'             => 'fa-vault',
                        'Room Service'     => 'fa-concierge-bell',
                        'Coffee Maker'     => 'fa-mug-hot',
                        'Hair Dryer'       => 'fa-wind',
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
            <a href="#available-rooms" style="text-decoration: none;" class="book-now">Book Now</a>
        </div>
    </div>
</body>

</html>