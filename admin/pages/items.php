<?php
require_once '../includes/head.php';
?>

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Items</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addItemModal">
                <i class="fas fa-plus"></i> Add Item
            </button>
        </div>

        <div class="summary-section mb-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="summary-card bg-primary text-white">
                        <div class="summary-icon"><i class="fas fa-box"></i></div>
                        <div class="summary-info">
                            <h3 id="totalItems">0</h3>
                            <span>Total Items</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card bg-success text-white">
                        <div class="summary-icon"><i class="fas fa-dollar-sign"></i></div>
                        <div class="summary-info">
                            <h3 id="averagePrice">₱0.00</h3>
                            <span>Average Price</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="summary-card bg-info text-white">
                        <div class="summary-icon"><i class="fas fa-sync"></i></div>
                        <div class="summary-info">
                            <h3 id="lastUpdate">-</h3>
                            <span>Last Updated</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Manage Items</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="itemsTable" class="table table-hover display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Price</th>
                                <th>Updated At</th>
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

    <?php include 'modals/modal-items.php' ?>

    <script src="../js/items/item.js"></script>
</body>

</html>