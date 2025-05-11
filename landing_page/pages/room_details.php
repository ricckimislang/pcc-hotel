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
                    <div class="meta-value">₱<?php echo number_format($base_price, 2); ?></div>
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

        <!-- Reviews Section -->
        <div class="reviews-section">
            <h2 class="section-title">Guest Reviews</h2>
            <div class="reviews-container">
                <div class="reviews-summary">
                    <div class="average-rating">
                        <div class="rating-number">
                            <span id="avgRating">0.0</span>
                            <span class="out-of">/5</span>
                        </div>
                        <div class="stars-display" id="avgStars"></div>
                        <div class="total-reviews">
                            Based on <span id="totalReviews">0</span> reviews
                        </div>
                    </div>
                    <div class="rating-bars">
                        <div class="rating-bar-item">
                            <span class="rating-label">5</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" id="fiveStarBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="rating-count" id="fiveStarCount">0</span>
                        </div>
                        <div class="rating-bar-item">
                            <span class="rating-label">4</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" id="fourStarBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="rating-count" id="fourStarCount">0</span>
                        </div>
                        <div class="rating-bar-item">
                            <span class="rating-label">3</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" id="threeStarBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="rating-count" id="threeStarCount">0</span>
                        </div>
                        <div class="rating-bar-item">
                            <span class="rating-label">2</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" id="twoStarBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="rating-count" id="twoStarCount">0</span>
                        </div>
                        <div class="rating-bar-item">
                            <span class="rating-label">1</span>
                            <div class="progress">
                                <div class="progress-bar bg-warning" id="oneStarBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <span class="rating-count" id="oneStarCount">0</span>
                        </div>
                    </div>
                </div>
                <div class="reviews-list" id="reviewsList">
                    <!-- Reviews will be loaded here dynamically -->
                </div>
            </div>
        </div>

        <div class="book-now-container">
            <a href="#available-rooms" class="book-now">Book Now</a>
        </div>
    </div>

    <script>
        // Function to create star rating display
        function createStarRating(rating) {
            const stars = [];
            for (let i = 1; i <= 5; i++) {
                if (i <= rating) {
                    stars.push('<i class="fas fa-star"></i>');
                } else if (i - 0.5 <= rating) {
                    stars.push('<i class="fas fa-star-half-alt"></i>');
                } else {
                    stars.push('<i class="far fa-star"></i>');
                }
            }
            return stars.join('');
        }

        // Function to load reviews
        function loadReviews() {
            fetch(`../api/get_room_reviews.php?room_type_id=<?php echo $room_type_id; ?>`)
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        // Update summary statistics
                        const stats = data.data.stats;
                        document.getElementById('avgRating').textContent = stats.average_rating || '0.0';
                        document.getElementById('totalReviews').textContent = stats.total_reviews;
                        document.getElementById('avgStars').innerHTML = createStarRating(stats.average_rating);

                        // Update rating bars
                        const total = parseInt(stats.total_reviews);
                        if (total > 0) {
                            ['five', 'four', 'three', 'two', 'one'].forEach(rating => {
                                const count = stats[`${rating}_star`];
                                const percentage = (count / total * 100).toFixed(1);
                                document.getElementById(`${rating}StarBar`).style.width = `${percentage}%`;
                                document.getElementById(`${rating}StarCount`).textContent = count;
                            });
                        }

                        // Display reviews
                        const reviewsList = document.getElementById('reviewsList');
                        reviewsList.innerHTML = '';

                        if (data.data.reviews.length > 0) {
                            data.data.reviews.forEach(review => {
                                const reviewCard = document.createElement('div');
                                reviewCard.className = 'review-card';
                                reviewCard.innerHTML = `
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div>
                                                <div class="reviewer-name">${review.customer_name}</div>
                                                <div class="review-date">${review.review_date}</div>
                                            </div>
                                        </div>
                                        <div class="review-rating">
                                            ${createStarRating(review.rating)}
                                        </div>
                                    </div>
                                    <div class="review-comment">${review.comment || 'No comment provided.'}</div>
                                    <div class="room-number">Room ${review.room_number}</div>
                                `;
                                reviewsList.appendChild(reviewCard);
                            });
                        } else {
                            reviewsList.innerHTML = `
                                <div class="no-reviews">
                                    <p>No reviews yet for this room type.</p>
                                </div>
                            `;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error loading reviews:', error);
                });
        }

        // Load reviews when the page loads
        document.addEventListener('DOMContentLoaded', loadReviews);
    </script>
</body>

</html>