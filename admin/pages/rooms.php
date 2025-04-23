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

    <?php include 'modals/modal-rooms.php'?>

    <script src="../js/rooms/room.js"></script>
</body>

</html>