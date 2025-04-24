<?php
require_once '../../config/db.php';
require_once '../includes/functions.php';

// Check login session if implemented
// session_start();
// if (!isset($_SESSION['user_id'])) {
//     header('Location: ../index.php');
//     exit;
// }
?>

<!DOCTYPE html>
<html lang="en">
<?php include_once '../includes/head.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="../css/dashboard.css">

<body>
    <?php include_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-container">
            <div class="row mb-4">

            </div>

            <!-- Filters -->
            <div class="filters-container">
                <div class="row">
                    <div class="col-md-12">
                        <h1 class="page-title">Dashboard</h1>
                        <p class="text-muted">Real-time occupancy reports and hotel performance</p>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period-filter">Time Period</label>
                            <select id="period-filter" class="form-control">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div id="date-range-container" class="row d-none">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="start-date">Start Date</label>
                                    <input type="text" id="start-date" class="form-control"
                                        placeholder="Select start date">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="end-date">End Date</label>
                                    <input type="text" id="end-date" class="form-control" placeholder="Select end date">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button id="apply-filter" class="btn btn-primary w-100">Apply</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button id="refresh-data" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(52, 152, 219, 0.2); color: #3498db;">
                        <i class="fas fa-door-open"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="total-rooms">0</h3>
                        <p>Total Rooms</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(231, 76, 60, 0.2); color: #e74c3c;">
                        <i class="fas fa-bed"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="occupied-rooms">0</h3>
                        <p>Occupied Rooms</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(46, 204, 113, 0.2); color: #2ecc71;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="available-rooms">0</h3>
                        <p>Available Rooms</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(241, 196, 15, 0.2); color: #f1c40f;">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="occupancy-rate">0%</h3>
                        <p>Occupancy Rate</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon" style="background-color: rgba(155, 89, 182, 0.2); color: #9b59b6;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3 id="total-bookings">0</h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
            </div>

            <!-- Booking Status Cards -->
            <div class="booking-status-container">
                <div class="booking-status-card">
                    <i class="fas fa-clock fa-2x pending mb-2"></i>
                    <h3 id="pending-bookings" class="pending">0</h3>
                    <p>Pending</p>
                </div>

                <div class="booking-status-card">
                    <i class="fas fa-calendar-alt fa-2x confirmed mb-2"></i>
                    <h3 id="confirmed-bookings" class="confirmed">0</h3>
                    <p>Confirmed</p>
                </div>

                <div class="booking-status-card">
                    <i class="fas fa-sign-in-alt fa-2x checked-in mb-2"></i>
                    <h3 id="checked-in-bookings" class="checked-in">0</h3>
                    <p>Checked In</p>
                </div>

                <div class="booking-status-card">
                    <i class="fas fa-sign-out-alt fa-2x checked-out mb-2"></i>
                    <h3 id="checked-out-bookings" class="checked-out">0</h3>
                    <p>Checked Out</p>
                </div>
            </div>

            <!-- Charts Row 1 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="chart-container">
                        <div class="chart-title">Room Occupancy Status</div>
                        <div class="chart-wrapper">
                            <div class="chart-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <canvas id="occupancy-chart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="chart-container">
                        <div class="chart-title">Booking Status Distribution</div>
                        <div class="chart-wrapper">
                            <div class="chart-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <canvas id="booking-status-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row 2 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="chart-container">
                        <div class="chart-title">Daily Check-ins and Check-outs (Last 30 Days)</div>
                        <div class="chart-wrapper">
                            <div class="chart-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <canvas id="daily-occupancy-chart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Charts Row 3 -->
                <div class="col-md-6">
                    <div class="chart-container">
                        <div class="chart-title">Room Type Distribution and Occupancy</div>
                        <div class="chart-wrapper">
                            <div class="chart-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <canvas id="room-type-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="../js/dashboard/dashboard.js"></script>
    <script>
        // Additional initialization if needed
        document.getElementById('refresh-data').addEventListener('click', function () {
            const period = document.getElementById('period-filter').value;
            if (period === 'custom') {
                const startDate = document.getElementById('start-date').value;
                const endDate = document.getElementById('end-date').value;
                if (startDate && endDate) {
                    loadDashboardData('custom', startDate, endDate);
                } else {
                    showAlert('Please select both start and end dates', 'error');
                }
            } else {
                loadDashboardData(period);
            }
        });
    </script>
</body>

</html>