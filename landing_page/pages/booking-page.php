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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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
            style="display: <?php echo $user_id ? 'block' : 'none'; ?>">
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
                            <input type="text" id="check_in_date" name="check_in_date" class="form-control" placeholder="Select Check-in Date" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="check_out_date">Check-out Date</label>
                            <input type="text" id="check_out_date" name="check_out_date" class="form-control" placeholder="Select Check-out Date" required>
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

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        // Fetch booked dates when page loads
        let bookedDates = [];
        const roomId = document.getElementById('room_id').value;

        // Fetch booked dates from the server
        fetch(`../api/get_booked_dates.php?room_id=${roomId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bookedDates = data.booked_dates;
                    initializeDatePickers();
                }
            })
            .catch(error => console.error('Error:', error));

        function initializeDatePickers() {
            // Initialize check-in date picker
            const checkInPicker = flatpickr("#check_in_date", {
                minDate: "today",
                disable: bookedDates,
                dateFormat: "Y-m-d",
                onChange: function(selectedDates) {
                    if (selectedDates[0]) {
                        // Update check-out date minimum
                        const minCheckOut = new Date(selectedDates[0]);
                        minCheckOut.setDate(minCheckOut.getDate() + 1);

                        // Find the next available date after check-in
                        let nextAvailableDate = new Date(minCheckOut);
                        while (bookedDates.includes(nextAvailableDate.toISOString().split('T')[0])) {
                            nextAvailableDate.setDate(nextAvailableDate.getDate() + 1);
                        }

                        // Update check-out picker
                        checkOutPicker.set('minDate', minCheckOut);
                        if (!checkOutPicker.selectedDates[0] ||
                            checkOutPicker.selectedDates[0] <= selectedDates[0] ||
                            bookedDates.includes(checkOutPicker.selectedDates[0].toISOString().split('T')[0])) {
                            checkOutPicker.setDate(nextAvailableDate);
                        }
                    }
                }
            });

            // Initialize check-out date picker
            const checkOutPicker = flatpickr("#check_out_date", {
                minDate: new Date().fp_incr(1),
                disable: bookedDates,
                dateFormat: "Y-m-d",
                onChange: function(selectedDates) {
                    if (selectedDates[0] && checkInPicker.selectedDates[0]) {
                        // Validate the date range
                        const start = new Date(checkInPicker.selectedDates[0]);
                        const end = new Date(selectedDates[0]);
                        let currentDate = new Date(start);

                        // Check if any date in the range is booked
                        while (currentDate <= end) {
                            if (bookedDates.includes(currentDate.toISOString().split('T')[0])) {
                                // Find next available date
                                while (bookedDates.includes(currentDate.toISOString().split('T')[0])) {
                                    currentDate.setDate(currentDate.getDate() + 1);
                                }
                                checkOutPicker.setDate(currentDate);
                                break;
                            }
                            currentDate.setDate(currentDate.getDate() + 1);
                        }
                    }
                }
            });
        }
    </script>
    <script src="../js/booking.js"></script>
</body>

</html>