<?php
session_start();
require_once '../../config/db.php';
$room_id = $_GET['room_id'];
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($user_id) {
    $query = "SELECT * FROM users WHERE user_id = $user_id";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);

    $firstname = $row["first_name"];
    $lastname = $row["last_name"];
    $phone = $row['phone_number'];
    $email = $row['email'];
} else {
    $firstname = "";
    $lastname = "";
    $phone = "";
    $email = "";
}
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
                Back to Home
            </a>
        </div>
        <div class="booking-header">
            <h1>Book Your Luxury Stay</h1>
            <p>Complete the form below to reserve your perfect accommodation</p>
        </div>

        <?php if (!$user_id) { ?>
            <div class="alert alert-warning">
                Please login to book a room
                <a href="login.php">Login</a>
            </div>
        <?php } ?>

        <form id="bookingForm"
            style="display:                                                                                                                                                                                                                                                                                                                                   <?php echo $user_id ? 'block' : 'none'; ?>">
            <!-- Personal Information Section -->
            <input type="hidden" id="room_id" name="room_id" value="<?php echo $room_id; ?>">
            <input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
            <div class="form-section">
                <h2 class="section-title">Personal Information</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name" class="form-control"
                                value="<?php echo $firstname; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name" class="form-control"
                                value="<?php echo $lastname ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control"
                                value="<?php echo $email; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="phone_number">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number" class="form-control"
                                value="<?php echo $phone; ?>" readonly>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Booking Details Section -->
            <div class="form-section">
                <h2 class="section-title">Booking Details</h2>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="check_in_date">Check-in Date</label>
                            <input type="date" id="check_in_date" name="check_in_date"
                                min="<?php echo date('Y-m-d'); ?>" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="check_out_date">Check-out Date</label>
                            <input type="date" id="check_out_date" name="check_out_date" min="" class="form-control"
                                required onchange="validateDates()">
                            <script>
                                document.getElementById('check_in_date').addEventListener('change', function() {
                                    validateDates();
                                });

                                function validateDates() {
                                    var checkIn = document.getElementById('check_in_date');
                                    var checkOut = document.getElementById('check_out_date');

                                    if (checkIn.value) {
                                        // Set minimum check-out date to day after check-in
                                        var minDate = new Date(checkIn.value);
                                        minDate.setDate(minDate.getDate() + 1);
                                        checkOut.min = minDate.toISOString().split('T')[0];

                                        // If check-out date is before new minimum, clear it
                                        if (checkOut.value && new Date(checkOut.value) <= new Date(checkIn.value)) {
                                            checkOut.value = '';
                                        }
                                    }
                                }
                            </script>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="guests_count">Number of Guests</label>
                            <select id="guests_count" name="guests_count" class="form-control" required>
                                <option value="">Select number of guests</option>
                                <?php $query = "SELECT r.room_type_id ,rt.capacity FROM rooms r LEFT JOIN room_types rt ON r.room_type_id = rt.room_type_id WHERE r.room_id = ?";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param('i', $room_id);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $row = $result->fetch_assoc();
                                $capacity = $row['capacity'];
                                ?>
                                <?php for ($i = 1; $i <= $capacity; $i++): ?>
                                    <option value="<?php echo $i; ?>"><?php echo $i; ?> Guests</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="special_requests">Special Requests</label>
                    <textarea id="special_requests" name="special_requests" class="form-control" rows="4"></textarea>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="submit-section">
                <button type="submit" class="btn-submit">Confirm Booking</button>
            </div>
        </form>
    </div>

    <script src="../js/booking.js"></script>

</body>

</html>