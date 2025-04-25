<?php
    require_once '../includes/head.php';
?>
<link rel="stylesheet" href="../css/room_types.css">

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Room Types</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoomTypeModal">
                <i class="fas fa-plus"></i> Add Room Type
            </button>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Manage Room Types</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="roomTypeTable" class="table table-hover display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Type Name</th>
                                <th>Base Price</th>
                                <th>Capacity</th>
                                <th>Floor</th>
                                <th class="col-description">Description</th>
                                <th class="col-amenities">Amenities</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- dynamic table dre -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include_once 'modals/room-types-modal.php'?>

    <link rel="stylesheet" href="../css/room_types.css">
    <script src="../js/room_types/room_types.js"></script>
</body>

</html>