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
                            ORDER BY FIELD(b.booking_status, 'pending', 'confirmed', 'checked_out', 'cancelled'), b.check_in_date");
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
                                <div class="booking-main-info">
                                    <h3><?php echo htmlspecialchars($booking['type_name']); ?></h3>
                                    <div class="booking-status <?php echo strtolower($booking['booking_status']); ?>">
                                        <i class="fas fa-circle"></i> <?php echo htmlspecialchars(ucfirst($booking['booking_status'])); ?>
                                    </div>
                                </div>
                                
                                <div class="booking-essential">
                                    <div class="booking-dates">
                                        <p><i class="far fa-calendar-alt"></i> Check-in:
                                            <?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?>
                                        </p>
                                        <p><i class="far fa-calendar-alt"></i> Check-out:
                                            <?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?>
                                        </p>
                                    </div>
                                    <p class="booking-price">₱<?php echo number_format($booking['total_price'], 2); ?></p>
                                </div>

                                <div class="booking-expandable">
                                    <div class="booking-meta">
                                        <p><i class="fas fa-user"></i> Guests:
                                            <?php echo htmlspecialchars($booking['guests_count']); ?>
                                        </p>
                                        <p><i class="fas fa-money-bill"></i> Payment Status:
                                            <span class="<?php echo $booking['payment_status'] === 'paid' ? 'text-success' : ''; ?>">
                                                <i class="<?php echo $booking['payment_status'] === 'paid' ? 'fas fa-circle' : ''; ?>"></i>
                                                <?php echo htmlspecialchars(ucfirst($booking['payment_status'])); ?>
                                            </span>
                                        </p>
                                    </div>
                                    <?php if (!empty($booking['special_requests'])): ?>
                                        <div class="special-requests">
                                            <p><i class="fas fa-sticky-note"></i> Special Requests:</p>
                                            <p><?php echo htmlspecialchars($booking['special_requests']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <button class="toggle-details">
                                    <span class="show-more">See more <i class="fas fa-chevron-down"></i></span>
                                    <span class="show-less">See less <i class="fas fa-chevron-up"></i></span>
                                </button>
                            </div>

                            <div class="booking-actions">
                                <?php if ($booking['booking_status'] === 'checked_out' && $booking['is_feedback'] === 0): ?>
                                    <a class="btn-review" data-booking-id="<?php echo $booking['booking_id']; ?>">
                                        Write Review
                                    </a>
                                <?php elseif ($booking['booking_status'] === 'pending'): ?>
                                    <button class="btn-cancel" data-booking-id="<?php echo $booking['booking_id']; ?>">
                                        Cancel Booking
                                    </button>
                                    <a href="booking_details.php?id=<?php echo $booking['booking_id']; ?>" class="btn-details">
                                        View Details
                                    </a>
                                <?php elseif ($booking['booking_status'] === 'confirmed'): ?>
                                    <a href="booking_details.php?id=<?php echo $booking['booking_id']; ?>" class="btn-details">
                                        View Details
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php include_once 'modal/feedback-modal.php'; ?>

    <script>
        // Dropdown menu functionality
        const menuToggle = document.getElementById('menuToggle');
        const navDropdown = document.getElementById('navDropdown');

        if (menuToggle && navDropdown) {
            document.addEventListener('click', function(event) {
                if (!event.target.closest('.menu-button') && navDropdown.classList.contains('show')) {
                    navDropdown.classList.remove('show');
                }
            });

            menuToggle.addEventListener('click', function(event) {
                event.stopPropagation();
                navDropdown.classList.toggle('show');
            });
        }

        // Mobile-only toggle booking details functionality
        function initializeExpandableContent() {
            const isMobile = window.innerWidth <= 768;
            const bookingCards = document.querySelectorAll('.booking-card');
            
            bookingCards.forEach(card => {
                const expandable = card.querySelector('.booking-expandable');
                const toggleBtn = card.querySelector('.toggle-details');
                
                if (isMobile) {
                    expandable.style.display = 'none';
                    toggleBtn.style.display = 'flex';
                } else {
                    expandable.style.display = 'block';
                    toggleBtn.style.display = 'none';
                }
            });
        }

        // Initialize on page load
        initializeExpandableContent();

        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(initializeExpandableContent, 250);
        });

        // Toggle details click handler
        document.querySelectorAll('.toggle-details').forEach(button => {
            button.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    const card = this.closest('.booking-card');
                    const expandable = card.querySelector('.booking-expandable');
                    
                    card.classList.toggle('expanded');
                    if (card.classList.contains('expanded')) {
                        expandable.style.display = 'block';
                        setTimeout(() => {
                            expandable.style.opacity = '1';
                            expandable.style.maxHeight = expandable.scrollHeight + 'px';
                        }, 10);
                    } else {
                        expandable.style.opacity = '0';
                        expandable.style.maxHeight = '0';
                        setTimeout(() => {
                            expandable.style.display = 'none';
                        }, 300);
                    }
                }
            });
        });

        // Cancel booking functionality
        document.querySelectorAll('.btn-cancel').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                if (confirm('Are you sure you want to cancel this booking?')) {
                    fetch('../api/cancel_booking.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                booking_id: bookingId
                            })
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

        // Feedback modal functionality
        document.querySelectorAll('.btn-review').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.getAttribute('data-booking-id');
                document.getElementById('bookingId').value = bookingId;
                document.getElementById('customerId').value = <?php echo $user_id; ?>;
                $('#feedbackModal').modal('show');
            });
        });
    </script>
</body>

</html>