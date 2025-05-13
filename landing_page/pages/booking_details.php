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
                              rm.room_number, r.floor_type,
                              COALESCE(SUM(t.amount), 0) AS paid_amount
                       FROM bookings b
                       JOIN rooms rm ON b.room_id = rm.room_id
                       JOIN room_types r ON rm.room_type_id = r.room_type_id
                       JOIN users u ON b.user_id = u.user_id
                       LEFT JOIN transactions t ON b.booking_id = t.booking_id
                       WHERE b.booking_id = ? AND b.user_id = ?
                       GROUP BY b.booking_id");
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

// Fetch customer profile for loyalty points check
$customerProfileQuery = $conn->prepare("SELECT loyal_points FROM customer_profiles WHERE user_id = ?");
$customerProfileQuery->bind_param("i", $user_id);
$customerProfileQuery->execute();
$customerProfileResult = $customerProfileQuery->get_result();
$customerProfile = $customerProfileResult->fetch_assoc();
$loyalty_points = isset($customerProfile['loyal_points']) ? $customerProfile['loyal_points'] : 0;
$customerProfileQuery->close();

// Calculate number of nights
$check_in = new DateTime($booking['check_in_date']);
$check_out = new DateTime($booking['check_out_date']);
$nights = $check_in->diff($check_out)->days;

// Calculate remaining balance
$paid_amount = floatval($booking['paid_amount']);
$total_price = floatval($booking['total_price']);
$remaining_balance = $total_price - $paid_amount;
$has_partial_payment = $paid_amount > 0 && $paid_amount < $total_price;
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/booking.css">
<?php include_once '../includes/head.php'; ?>

<body>
    <style>
        .price-row.remaining {
            font-weight: bold;
            color: #2980b9;
            font-size: 1.1em;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 1px dashed #ccc;
        }
    </style>
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
                    <div class="detail-item">
                        <div class="detail-label"><i class="fas fa-building"></i> Floor</div>
                        <div class="detail-value"><?php echo $booking['floor_type']; ?></div>
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
                <?php if (isset($booking['is_discount']) && $booking['is_discount'] == 1): ?>
                    <div class="price-row">
                        <span>Loyalty Discount:</span>
                        <span>5%</span>
                    </div>
                <?php endif; ?>
                <div class="price-row total">
                    <span>Total</span>
                    <span>₱<?php echo number_format($booking['total_price'], 2); ?></span>
                </div>
                <?php if ($paid_amount > 0): ?>
                    <div class="price-row">
                        <span>Amount Paid</span>
                        <span>₱<?php echo number_format($paid_amount, 2); ?></span>
                    </div>
                    <?php if ($remaining_balance > 0): ?>
                        <div class="price-row remaining">
                            <span>Remaining Balance</span>
                            <span>₱<?php echo number_format($remaining_balance, 2); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <?php if (!empty($booking['special_requests'])): ?>
                <div class="special-requests-section">
                    <h3>Special Requests</h3>
                    <p><?php echo htmlspecialchars($booking['special_requests']); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($booking['booking_status'] === 'confirmed'): ?>
                <div class="booking-actions-section">
                    <button id="cancelBooking" class="btn-cancel" data-booking-id="<?php echo $booking_id; ?>">Cancel
                        Booking</button>
                </div>
            <?php endif; ?>

            <?php if ($booking['payment_status'] === 'pending'): ?>
                <div class="booking-actions-section">
                    <button id="payBooking" class="btn-pay">Pay Now</button>
                </div>
            <?php elseif ($booking['payment_status'] === 'partial'): ?>
                <div class="booking-actions-section">
                    <button id="payBooking" class="btn-pay">Pay Remaining Balance</button>
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
                    <p><strong>Amount to Pay:</strong> ₱<?php echo number_format($booking['payment_status'] === 'partial' ? $remaining_balance : $booking['total_price'], 2); ?></p>
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
                    <input type="file" id="paymentScreenshot" name="payment_screenshot" accept="image/*"
                        class="form-control" required>
                    <div class="file-preview-container">
                        <img id="screenshotPreview" src="#" alt="Preview"
                            style="display: none; max-width: 100%; max-height: 200px; margin-top: 10px;">
                    </div>
                </div>

                <div class="form-group">
                    <label for="paymentAmount">Payment Amount</label>
                    <input type="number" id="paymentAmount" name="payment_amount" class="form-control"
                        value="<?php echo $booking['payment_status'] === 'partial' ? $remaining_balance : $booking['total_price']; ?>" step="0.01" min="0" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn-submit">Submit Payment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Policy Modal -->
    <div id="policyModal" class="payment-modal">
        <div class="payment-modal-content">
            <span class="policy-close">&times;</span>
            <h2>Booking Policy</h2>

            <div class="policy-content">
                <div class="policy-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p><strong>Important Notice:</strong> Once a booking is confirmed, it cannot be cancelled.</p>
                </div>

                <div class="policy-details">
                    <p>Please review the following booking policies before proceeding:</p>
                    <ul>
                        <li>Confirmed bookings are final and non-refundable</li>
                        <li>Payments are processed immediately upon confirmation</li>
                        <li>Changes to confirmed bookings are not permitted</li>
                    </ul>
                </div>

                <div class="policy-agreement">
                    <input type="checkbox" id="policyAgreement" required>
                    <label for="policyAgreement">I have read and agree to the booking policy</label>
                </div>

                <div class="policy-actions">
                    <button id="policyDecline" class="btn-cancel">Decline</button>
                    <button id="policyAccept" class="btn-submit" disabled>Accept & Continue</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            // Payment modal functionality
            const payButton = document.getElementById('payBooking');
            const paymentModal = document.getElementById('paymentModal');
            const closePaymentModal = document.querySelector('#paymentModal .payment-close');
            const paymentForm = document.getElementById('paymentForm');
            const screenshotInput = document.getElementById('paymentScreenshot');
            const screenshotPreview = document.getElementById('screenshotPreview');

            // Add this code to handle payment modal close button
            if (closePaymentModal) {
                closePaymentModal.addEventListener('click', function(event) {
                    event.stopPropagation();
                    paymentModal.style.display = 'none';
                    console.log('Payment modal closed');
                });
            }
            // Policy modal elements
            const policyModal = document.getElementById('policyModal');
            const closePolicyModal = document.querySelector('#policyModal .policy-close');
            const policyAgreement = document.getElementById('policyAgreement');
            const policyAccept = document.getElementById('policyAccept');
            const policyDecline = document.getElementById('policyDecline');
            let currentAction = ''; // Track whether we're proceeding with 'payment' or 'cancellation'

            // Policy agreement checkbox handler
            if (policyAgreement) {
                policyAgreement.addEventListener('change', function() {
                    policyAccept.disabled = !this.checked;
                });
            }

            // Close policy modal
            if (closePolicyModal) {
                closePolicyModal.addEventListener('click', function(event) {
                    event.stopPropagation();
                    policyModal.style.display = 'none';
                    console.log('Policy modal close button element:', closePolicyModal);
                });
            }

            // Policy decline button
            if (policyDecline) {
                policyDecline.addEventListener('click', function() {
                    policyModal.style.display = 'none';
                });
            }

            // Policy accept button
            if (policyAccept) {
                policyAccept.addEventListener('click', function() {
                    policyModal.style.display = 'none';

                    if (currentAction === 'payment') {
                        paymentModal.style.display = 'block';
                    } else if (currentAction === 'cancellation') {
                        proceedWithCancellation();
                    }
                });
            }

            if (payButton) {
                payButton.addEventListener('click', function() {
                    // Show policy modal first
                    currentAction = 'payment';
                    policyModal.style.display = 'block';
                    policyAgreement.checked = false;
                    policyAccept.disabled = true;
                });
            }

            // Cancel booking functionality
            const cancelButton = document.getElementById('cancelBooking');
            if (cancelButton) {
                cancelButton.addEventListener('click', function() {
                    // Show policy modal first
                    currentAction = 'cancellation';
                    policyModal.style.display = 'block';
                    policyAgreement.checked = false;
                    policyAccept.disabled = true;
                });
            }

            // Function to proceed with cancellation after policy acceptance
            function proceedWithCancellation() {
                const bookingId = cancelButton.getAttribute('data-booking-id');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to cancel this booking?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, cancel it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Send cancellation request
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
                                    Swal.fire(
                                        'Cancelled!',
                                        'Your booking has been cancelled.',
                                        'success'
                                    ).then(() => {
                                        window.location.href = 'my_bookings.php';
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        data.message,
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire(
                                    'Error!',
                                    'An error occurred while cancelling the booking',
                                    'error'
                                );
                            });
                    }
                });
            }

            // Close modal when clicking outside the modal content
            window.addEventListener('click', function(event) {
                if (event.target === paymentModal) {
                    paymentModal.style.display = 'none';
                }
                if (event.target === policyModal) {
                    policyModal.style.display = 'none';
                }
            });

            // Preview payment screenshot when selected
            if (screenshotInput) {
                screenshotInput.addEventListener('change', function() {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            screenshotPreview.src = e.target.result;
                            screenshotPreview.style.display = 'block';
                            document.querySelector('.file-preview-container').classList.add('has-preview');
                        }
                        reader.readAsDataURL(file);

                        // Update the file input label with filename
                        const fileLabel = this.nextElementSibling;
                        if (fileLabel) {
                            fileLabel.textContent = file.name;
                        }
                    }
                });
            }

            // Handle payment form submission
            if (paymentForm) {
                paymentForm.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const submitButton = this.querySelector('button[type="submit"]');
                    submitButton.disabled = true;
                    submitButton.textContent = 'Processing...';

                    const formData = new FormData(this);

                    fetch('../api/submit_payment.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success!',
                                    text: data.message,
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message,
                                    confirmButtonColor: '#3085d6'
                                });
                                submitButton.disabled = false;
                                submitButton.textContent = 'Submit Payment';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'An error occurred while processing your payment',
                                confirmButtonColor: '#3085d6'
                            });
                            submitButton.disabled = false;
                            submitButton.textContent = 'Submit Payment';
                        });
                });
            }
        });
    </script>
</body>

</html>