<?php
require_once '../includes/head.php';
?>

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Rooms</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomModal">
                <i class="fas fa-plus"></i> Add Room
            </button>
        </div>

        <div class="summary-section mb-4">
            <div class="row">
                <div class="col-md-3">
                    <div class="summary-card bg-primary text-white">
                        <div class="summary-icon"><i class="fas fa-bed"></i></div>
                        <div class="summary-info">
                            <h3 id="totalRooms">0</h3>
                            <span>Total Rooms</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-success text-white">
                        <div class="summary-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="summary-info">
                            <h3 id="availableRooms">0</h3>
                            <span>Available Rooms</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-danger text-white">
                        <div class="summary-icon"><i class="fas fa-door-closed"></i></div>
                        <div class="summary-info">
                            <h3 id="occupiedRooms">0</h3>
                            <span>Occupied Rooms</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card bg-info text-white">
                        <div class="summary-icon"><i class="fas fa-broom"></i></div>
                        <div class="summary-info">
                            <h3 id="maintenanceRooms">0</h3>
                            <span>Under Maintenance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Manage Rooms</h2>
                <div class="card-tools">
                    <select class="form-select" id="roomTypeFilter">
                        <option value="">All Room Types</option>
                        <!-- dynamic ni dire -->
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="roomsTable" class="table table-hover display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Room #</th>
                                <th>Room Type</th>
                                <th>Floor</th>
                                <th>Status</th>
                                <th>Price</th>
                                <th>Capacity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- dynamic populate -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'modals/modal-rooms.php' ?>

    <script src="../js/rooms/room.js"></script>
</body>

</html>