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
        <div class="page-header">
            <h1>Dashboard</h1>
            <div>
                <span id="last-updated" class="me-2 text-muted">Last updated: Never</span>
                <button id="refresh-data" class="btn btn-outline-secondary">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Overview Filters</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period-filter">Time Period</label>
                            <select id="period-filter" class="form-select">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month" selected>This Month</option>
                                <option value="year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="room-type-filter">Room Type</label>
                            <select id="room-type-filter" class="form-select">
                                <option value="all" selected>All Types</option>
                                <!-- Room types will be loaded dynamically -->
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
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
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="summary-section mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="summary-card bg-primary text-white">
                        <div class="summary-icon"><i class="fas fa-door-open"></i></div>
                        <div class="summary-info">
                            <h3 id="total-rooms">0</h3>
                            <span>Total Rooms</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-success text-white">
                        <div class="summary-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="summary-info">
                            <h3 id="available-rooms">0</h3>
                            <span>Available Rooms</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-danger text-white">
                        <div class="summary-icon"><i class="fas fa-bed"></i></div>
                        <div class="summary-info">
                            <h3 id="occupied-rooms">0</h3>
                            <span>Occupied Rooms</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-info text-white">
                        <div class="summary-icon"><i class="fas fa-percentage"></i></div>
                        <div class="summary-info">
                            <h3 id="occupancy-rate">0%</h3>
                            <span>Occupancy Rate</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Booking Status Row -->
        <div class="card mb-4">
            <div class="card-header">
                <h2>Booking Status</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="status-card">
                            <div class="status-icon pending">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                            <div class="status-info">
                                <h3 id="pending-bookings">0</h3>
                                <span>Pending</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-card">
                            <div class="status-icon confirmed">
                                <i class="fas fa-calendar-alt fa-2x"></i>
                            </div>
                            <div class="status-info">
                                <h3 id="confirmed-bookings">0</h3>
                                <span>Confirmed</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-card">
                            <div class="status-icon checked-in">
                                <i class="fas fa-sign-in-alt fa-2x"></i>
                            </div>
                            <div class="status-info">
                                <h3 id="checked-in-bookings">0</h3>
                                <span>Checked In</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="status-card">
                            <div class="status-icon checked-out">
                                <i class="fas fa-sign-out-alt fa-2x"></i>
                            </div>
                            <div class="status-info">
                                <h3 id="checked-out-bookings">0</h3>
                                <span>Checked Out</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real-time Occupancy Monitoring -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2>Real-time Occupancy Monitor</h2>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="realtime-toggle" checked>
                    <label class="form-check-label" for="realtime-toggle">Auto-refresh (30s)</label>
                </div>
            </div>
            <div class="card-body">
                <div class="floor-selector mb-3">
                    <label for="floor-filter" class="me-2">Floor:</label>
                    <div class="btn-group" role="group" id="floor-filter">
                        <button type="button" class="btn btn-outline-primary active" data-floor="all">All</button>
                        <!-- Floor buttons will be populated dynamically -->
                    </div>
                </div>
                <div class="room-grid-container">
                    <div id="room-occupancy-grid" class="room-grid">
                        <!-- Room blocks will be loaded dynamically -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <div class="d-flex justify-content-center flex-wrap">
                        <div class="legend-item me-3">
                            <span class="status-dot available"></span> Available
                        </div>
                        <div class="legend-item me-3">
                            <span class="status-dot occupied"></span> Occupied
                        </div>
                        <div class="legend-item me-3">
                            <span class="status-dot reserved"></span> Reserved
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Room Occupancy Status</h2>
                    </div>
                    <div class="card-body">
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
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Booking Status Distribution</h2>
                    </div>
                    <div class="card-body">
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
        </div>

        <!-- Charts Row 2 -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Daily Check-ins and Check-outs</h2>
                        <small class="text-muted">Last 30 Days</small>
                    </div>
                    <div class="card-body">
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
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Room Type Distribution</h2>
                    </div>
                    <div class="card-body">
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

        <!-- Charts Row 3 - Advanced Analytics -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2>Occupancy Trend Analysis</h2>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-trend="weekly">Weekly</button>
                            <button type="button" class="btn btn-outline-primary" data-trend="monthly">Monthly</button>
                            <button type="button" class="btn btn-outline-primary" data-trend="quarterly">Quarterly</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <div class="chart-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <canvas id="occupancy-trend-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue and Occupancy Correlation -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2>Peak Booking Days</h2>  
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-booking-period="weekly">Weekly</button>
                            <button type="button" class="btn btn-outline-primary" data-booking-period="monthly">Monthly</button>
                            <button type="button" class="btn btn-outline-primary" data-booking-period="yearly">Yearly</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <div class="chart-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <canvas id="peak-booking-days-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Room Booking Trends -->
        <div class="row mb-4">
            <div class="col-12 mb-3">
                <h2 class="section-title">Room Booking Trends Analysis</h2>
                <p class="text-muted">Track the most popular rooms and peak booking days for resource management and pricing optimization</p>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h2>Most Booked Rooms</h2>
                        <small class="text-muted">Top 10 by booking frequency</small>
                    </div>
                    <div class="card-body">
                        <div class="chart-wrapper">
                            <div class="chart-loading">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                            <canvas id="most-booked-rooms-chart"></canvas>
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
</body>

</html>