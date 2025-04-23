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

// Fetch user's bookings from database
$bookings = [];
if ($user_id) {
    $stmt = $conn->prepare("SELECT b.*, r.type_name, r.base_price, rm.room_type_id 
                          FROM bookings b
                          JOIN rooms rm ON b.room_id = rm.room_id
                          JOIN room_types r ON rm.room_type_id = r.room_type_id
                          WHERE b.user_id = ?
                          ORDER BY b.check_in_date DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/booking.css">
<?php include_once '../includes/head.php'; ?>

<body>

    <div class="booking-container">
        <div class="back-button" style="margin-bottom: 20px;">
            <a href="index.php"
                style="text-decoration: none; color: var(--dark-text); display: flex; align-items: center; width: fit-content;">
                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                Back to Home
            </a>
        </div>
        <div class="booking-header">
            <h1>My Bookings</h1>
            <p>View and manage your reservations</p>
        </div>

        <div class="bookings-section">
            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    <p>You don't have any bookings yet.</p>
                    <a href="index.php" class="btn-primary">Browse Rooms</a>
                </div>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-details">
                                <h3><?php echo htmlspecialchars($booking['type_name']); ?></h3>
                                <div class="booking-dates">
                                    <p><i class="far fa-calendar-alt"></i> Check-in:
                                        <?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?>
                                    </p>
                                    <p><i class="far fa-calendar-alt"></i> Check-out:
                                        <?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?>
                                    </p>
                                </div>
                                <div class="booking-meta">
                                    <p><i class="fas fa-user"></i> Guests:
                                        <?php echo htmlspecialchars($booking['guests_count']); ?>
                                    </p>
                                    <p><i class="fas fa-bookmark"></i> Booking ID:
                                        <?php echo htmlspecialchars($booking['booking_id']); ?>
                                    </p>
                                    <p class="booking-status <?php echo strtolower($booking['booking_status']); ?>">
                                        <i class="fas fa-circle"></i> Status:
                                        <?php echo htmlspecialchars(ucfirst($booking['booking_status'])); ?>
                                    </p>
                                </div>
                                <?php if (!empty($booking['special_requests'])): ?>
                                    <div class="special-requests">
                                        <p><i class="fas fa-sticky-note"></i> Special Requests:</p>
                                        <p><?php echo htmlspecialchars($booking['special_requests']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="booking-actions">
                                <p class="booking-price">₱<?php echo number_format($booking['total_price'], 2); ?></p>
                                <?php if ($booking['booking_status'] === 'confirmed' && strtotime($booking['check_in_date']) > time()): ?>
                                    <button class="btn-cancel" data-booking-id="<?php echo $booking['booking_id']; ?>">Cancel
                                        Booking</button>
                                <?php elseif ($booking['booking_status'] === 'completed'): ?>
                                    <button class="btn-review" data-booking-id="<?php echo $booking['booking_id']; ?>">Write
                                        Review</button>
                                <?php endif; ?>
                                <a href="booking_details.php?id=<?php echo $booking['booking_id']; ?>" class="btn-details">View
                                    Details</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.menu-button') && navDropdown.classList.contains('show')) {
                    navDropdown.classList.remove('show');
                }
            });

            // Toggle the dropdown when clicking the menu button
            menuToggle.addEventListener('click', function (event) {
                event.stopPropagation();
                navDropdown.classList.toggle('show');
            });
        }

        // Cancel booking functionality
        document.querySelectorAll('.btn-cancel').forEach(button => {
            button.addEventListener('click', function () {
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
                                location.reload();
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
        });
    </script>
</body>

</html>