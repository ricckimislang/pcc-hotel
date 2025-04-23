<?php
session_start();
require_once '../../config/db.php';

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Redirect to login if not logged in
if (!$user_id) {
    header("Location: login.php");
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my_bookings.php");
    exit;
}

$booking_id = $_GET['id'];

// Fetch booking details
$stmt = $conn->prepare("SELECT b.*, 
                              r.type_name, r.description, r.base_price, r.capacity, r.amenities,
                              u.username, u.email, u.phone
                       FROM bookings b
                       JOIN room_types r ON b.room_type_id = r.room_type_id
                       JOIN users u ON b.user_id = u.user_id
                       WHERE b.booking_id = ? AND b.user_id = ?");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Booking not found or doesn't belong to user
    header("Location: my_bookings.php");
    exit;
}

$booking = $result->fetch_assoc();
$stmt->close();

// Calculate number of nights
$check_in = new DateTime($booking['check_in_date']);
$check_out = new DateTime($booking['check_out_date']);
$nights = $check_in->diff($check_out)->days;
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/booking.css">
<?php include_once '../includes/head.php'; ?>

<body>
    <header>
        <div class="header-container">
            <div class="menu-button">
                <button id="menuToggle"><i class="fas fa-bars"></i> Menu</button>
                <div class="dropdown-menu" id="navDropdown">
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="my_bookings.php">My Bookings</a></li>
                        <?php if ($user_id) { ?>
                            <li><a href="profile.php">My Profile</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="logo">
                <a href="index.php">PCC HOME SUITE HOME</a>
            </div>
            <div class="user-container">
                <span><?php echo $_SESSION['username']; ?></span>
            </div>
        </div>
    </header>

    <div class="booking-container">
        <div class="booking-header">
            <h1>Booking Details</h1>
            <p>Reservation #<?php echo $booking_id; ?></p>
        </div>

        <div class="booking-details-container">
            <div class="back-link">
                <a href="my_bookings.php"><i class="fas fa-arrow-left"></i> Back to My Bookings</a>
            </div>

            <div class="booking-status-bar">
                <div class="status-badge <?php echo strtolower($booking['status']); ?>">
                    <?php echo ucfirst($booking['status']); ?>
                </div>
                <div class="booking-date">
                    Booked on: <?php echo date('F d, Y', strtotime($booking['booking_date'])); ?>
                </div>
            </div>

            <div class="booking-info-card">
                <div class="room-info">
                    <h2><?php echo htmlspecialchars($booking['type_name']); ?></h2>
                    <p class="room-description"><?php echo htmlspecialchars($booking['description']); ?></p>
                    
                    <div class="amenities">
                        <h3>Amenities</h3>
                        <p><?php echo htmlspecialchars($booking['amenities']); ?></p>
                    </div>
                </div>

                <div class="reservation-details">
                    <div class="detail-item">
                        <div class="detail-label"><i class="far fa-calendar-check"></i> Check-in</div>
                        <div class="detail-value"><?php echo date('l, F d, Y', strtotime($booking['check_in_date'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label"><i class="far fa-calendar-times"></i> Check-out</div>
                        <div class="detail-value"><?php echo date('l, F d, Y', strtotime($booking['check_out_date'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-moon"></i> Duration</div>
                        <div class="detail-value"><?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-user-friends"></i> Guests</div>
                        <div class="detail-value"><?php echo $booking['guests_count']; ?></div>
                    </div>
                </div>
            </div>

            <div class="pricing-summary">
                <h3>Price Details</h3>
                <div class="price-row">
                    <span>Room Rate (₱<?php echo number_format($booking['base_price'], 2); ?> x <?php echo $nights; ?> nights)</span>
                    <span>₱<?php echo number_format($booking['base_price'] * $nights, 2); ?></span>
                </div>
                <div class="price-row">
                    <span>Taxes & Fees</span>
                    <span>₱<?php echo number_format($booking['total_price'] - ($booking['base_price'] * $nights), 2); ?></span>
                </div>
                <div class="price-row total">
                    <span>Total</span>
                    <span>₱<?php echo number_format($booking['total_price'], 2); ?></span>
                </div>
            </div>

            <?php if (!empty($booking['special_requests'])): ?>
            <div class="special-requests-section">
                <h3>Special Requests</h3>
                <p><?php echo htmlspecialchars($booking['special_requests']); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($booking['status'] === 'confirmed' && strtotime($booking['check_in_date']) > time()): ?>
            <div class="booking-actions-section">
                <button id="cancelBooking" class="btn-cancel" data-booking-id="<?php echo $booking_id; ?>">Cancel Booking</button>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Dropdown menu functionality
        const menuToggle = document.getElementById('menuToggle');
        const navDropdown = document.getElementById('navDropdown');
        
        if (menuToggle && navDropdown) {
            // Add a click event handler to close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.menu-button') && navDropdown.classList.contains('show')) {
                    navDropdown.classList.remove('show');
                }
            });
            
            // Toggle the dropdown when clicking the menu button
            menuToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                navDropdown.classList.toggle('show');
            });
        }

        // Cancel booking functionality
        const cancelButton = document.getElementById('cancelBooking');
        if (cancelButton) {
            cancelButton.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                if (confirm('Are you sure you want to cancel this booking?')) {
                    // Send cancellation request
                    fetch('../api/cancel_booking.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ booking_id: bookingId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Booking cancelled successfully');
                            window.location.href = 'my_bookings.php';
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while cancelling the booking');
                    });
                }
            });
        }
    </script>
</body>
</html> 