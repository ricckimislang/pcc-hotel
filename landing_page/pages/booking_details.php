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
                              u.username, u.email, u.phone_number,
                              rm.room_number, rm.floor
                       FROM bookings b
                       JOIN rooms rm ON b.room_id = rm.room_id
                       JOIN room_types r ON rm.room_type_id = r.room_type_id
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
    <div class="booking-container">
        <div class="back-button" style="margin-bottom: 20px;">
            <a href="javascript:history.back()"
                style="text-decoration: none; color: var(--dark-text); display: flex; align-items: center; width: fit-content;">
                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
                Back
            </a>
        </div>
        <div class="booking-header">
            <h1>Booking Details</h1>
            <p>Reservation #<?php echo $booking_id; ?></p>
        </div>

        <div class="booking-details-container">
            <div class="back-link">
                <a href="my_bookings.php"><i class="fas fa-arrow-left"></i> Back to My Bookings</a>
            </div>

            <div class="booking-status-bar">
                <div class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                    <?php echo ucfirst($booking['booking_status']); ?>
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
                        <div class="detail-value"><?php echo date('l, F d, Y', strtotime($booking['check_in_date'])); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label"><i class="far fa-calendar-times"></i> Check-out</div>
                        <div class="detail-value">
                            <?php echo date('l, F d, Y', strtotime($booking['check_out_date'])); ?>
                        </div>
                    </div>

                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-moon"></i> Duration</div>
                        <div class="detail-value"><?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>
                        </div>
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
                    <span>Room Rate (₱<?php echo number_format($booking['base_price'], 2); ?> x <?php echo $nights; ?>
                        nights)</span>
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

            <?php if ($booking['booking_status'] === 'confirmed' && strtotime($booking['check_in_date']) > time()): ?>
                <div class="booking-actions-section">
                    <button id="cancelBooking" class="btn-cancel" data-booking-id="<?php echo $booking_id; ?>">Cancel
                        Booking</button>
                </div>
            <?php endif; ?>

            <?php if ($booking['payment_status'] === 'pending' || $booking['payment_status'] === 'partial'): ?>
                <div class="booking-actions-section">
                    <button id="payBooking" class="btn-pay">Pay Now</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" class="payment-modal">
        <div class="payment-modal-content">
            <span class="payment-close">&times;</span>
            <h2>Payment Details</h2>
            
            <div class="payment-info">
                <h3>GCash Payment Information</h3>
                <div class="gcash-details">
                    <p><strong>Account Name:</strong> PCC Home Suite Home</p>
                    <p><strong>GCash Number:</strong> 09123456789</p>
                    <p><strong>Amount to Pay:</strong> ₱<?php echo number_format($booking['total_price'], 2); ?></p>
                </div>
                <div class="payment-instructions">
                    <ol>
                        <li>Open your GCash app and send the payment to the number above</li>
                        <li>Take a screenshot of your payment confirmation</li>
                        <li>Enter the reference number from your payment</li>
                        <li>Upload the screenshot below</li>
                        <li>Click Submit to complete your payment</li>
                    </ol>
                </div>
            </div>
            
            <form id="paymentForm" enctype="multipart/form-data">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">
                
                <div class="form-group">
                    <label for="referenceNumber">GCash Reference Number</label>
                    <input type="text" id="referenceNumber" name="reference_number" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="paymentScreenshot">Payment Screenshot</label>
                    <input type="file" id="paymentScreenshot" name="payment_screenshot" accept="image/*" class="form-control" required>
                    <div class="file-preview-container">
                        <img id="screenshotPreview" src="#" alt="Preview" style="display: none; max-width: 100%; max-height: 200px; margin-top: 10px;">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="paymentAmount">Payment Amount</label>
                    <input type="number" id="paymentAmount" name="payment_amount" class="form-control" value="<?php echo $booking['total_price']; ?>" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn-submit">Submit Payment</button>
                </div>
            </form>
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
        const cancelButton = document.getElementById('cancelBooking');
        if (cancelButton) {
            cancelButton.addEventListener('click', function () {
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

        // Payment Modal Functionality
        const payButton = document.getElementById('payBooking');
        const paymentModal = document.getElementById('paymentModal');
        const closeButton = document.querySelector('.payment-close');
        const paymentForm = document.getElementById('paymentForm');
        const fileInput = document.getElementById('paymentScreenshot');
        const filePreview = document.getElementById('screenshotPreview');

        // Open modal when pay button is clicked
        if (payButton) {
            payButton.addEventListener('click', function() {
                paymentModal.style.display = 'block';
            });
        }

        // Close modal when close button is clicked
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                paymentModal.style.display = 'none';
            });
        }

        // Close modal when clicking outside the modal content
        window.addEventListener('click', function(event) {
            if (event.target === paymentModal) {
                paymentModal.style.display = 'none';
            }
        });

        // Preview uploaded image
        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        filePreview.style.display = 'block';
                        filePreview.src = e.target.result;
                    }
                    
                    reader.readAsDataURL(this.files[0]);
                } else {
                    filePreview.style.display = 'none';
                }
            });
        }

        // Handle form submission
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                const formData = new FormData(this);
                
                // Submit payment form via AJAX
                fetch('../api/submit_payment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Payment submitted successfully!');
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while submitting payment');
                });
            });
        }
    </script>
</body>

</html>