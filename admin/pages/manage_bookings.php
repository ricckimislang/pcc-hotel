<?php
require_once '../../config/db.php';

// Get all bookings with room and guest details
$query = "SELECT b.*, r.room_number, rt.type_name, u.first_name, u.last_name, u.email FROM bookings b JOIN rooms r ON b.room_id = r.room_id JOIN room_types rt ON r.room_type_id = rt.room_type_id JOIN users u ON b.user_id = u.user_id";

$stmt = $conn->prepare($query);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="../css/manage_bookings.css">
<?php include_once '../includes/head.php'; ?>

<body>
    <?php include_once '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="bookings-container">
            <div class="bookings-header">
                <h1>Manage Bookings</h1>
                <div class="header-actions">
                    <button class="refresh-btn" id="refreshTable">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>

            <div class="bookings-table-container">
                <table id="bookingsTable" class="table table-striped" width="100%">
                    <thead>
                        <tr>
                            <th>Guest Name</th>
                            <th>Room</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Booking Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td>
                                    <?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?>
                                    <br>
                                    <small><?php echo $booking['email']; ?></small>
                                </td>
                                <td>
                                    Room <?php echo $booking['room_number']; ?>
                                    <br>
                                    <small><?php echo $booking['type_name']; ?></small>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($booking['booking_status']); ?>">
                                        <?php echo ucfirst($booking['booking_status']); ?>
                                    </span>
                                </td>
                                <td class="actions">
                                    <button class="action-btn view-btn"
                                        data-booking-id="<?php echo $booking['booking_id']; ?>">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="action-btn edit-btn"
                                        data-booking-id="<?php echo $booking['booking_id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php include_once 'modals/modal-bookings.php'; ?>
        <script src="../js/manage_booking/manage_bookings.js"></script>
</body>

</html>