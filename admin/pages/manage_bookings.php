<?php
require_once '../../config/db.php';
require_once '../api/bookings/get_booking_table.php';
require_once '../api/bookings/get_checkin_table.php';
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

            <div class="bookings-table-container mb-4">
                <h3>Confirm Bookings</h3>
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
            <div class="bookings-table-container">
                <h3>Check In/out</h3>
                <table id="checkInOutTable" class="table table-striped" width="100%">
                    <thead>
                        <tr>
                            <th>Guest Name</th>
                            <th>Room</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checkInBookings as $booking): ?>
                            <?php if ($booking['booking_status'] == 'confirmed' || $booking['booking_status'] == 'checked_in'): ?>
                                <tr>
                                    <td>
                                        <?php echo $booking['first_name'] . ' ' . $booking['last_name']; ?>
                                        <br>
                                    </td>
                                    <td>
                                        Room <?php echo $booking['room_number']; ?>
                                        <br>
                                        <small><?php echo $booking['type_name']; ?></small>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                    <td>
                                        <?php if ($booking['booking_status'] == 'checked_in'): ?>
                                            <span class="status-badge confirmed">
                                                Checked In
                                            </span>
                                        <?php else: ?>

                                        <?php endif; ?>
                                    </td>
                                    <td class="actions">
                                        <?php if ($booking['booking_status'] == 'confirmed'): ?>
                                            <button class="action-btn check-in-btn"
                                                data-booking-id="<?php echo $booking['booking_id']; ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php elseif ($booking['booking_status'] == 'checked_in'): ?>
                                            <button class="action-btn check-out-btn"
                                                data-booking-id="<?php echo $booking['booking_id']; ?>">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            </div>

            <?php include_once 'modals/modal-bookings.php'; ?>
            <script src="../js/manage_booking/manage_bookings.js"></script>
            <script src="../js/manage_booking/manage_check-in.js"></script>
            <script src="../js/manage_booking/manage_check-out.js"></script>
</body>

</html>