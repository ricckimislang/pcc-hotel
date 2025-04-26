<?php
require_once '../includes/head.php';
?>
<link rel="stylesheet" href="../css/customers.css">

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">

            <div class="page-header d-flex justify-content-between align-items-center">
                <h1>Customer Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="../pages/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Customers</li>
                    </ol>
                </nav>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow-sm">
                        <div class="card-body p-0">
                            <div class="search-container p-4">
                                <div class="d-flex align-items-center">
                                    <div class="search-icon me-3">
                                        <i class="fas fa-search text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="text" class="form-control form-control-lg border-0 shadow-none" id="search-username" placeholder="Search customers by first or last name..." autocomplete="off">
                                        <small class="text-muted">Enter at least 2 characters to start searching</small>
                                    </div>
                                </div>
                            </div>
                            <div id="loading-spinner" class="text-center my-4" style="display:none;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-12">
                    <div id="no-results" class="alert alert-info rounded-3 shadow-sm" style="display:none;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-3 fs-4"></i>
                            <div>
                                <h5 class="mb-1">No Customers Found</h5>
                                <p class="mb-0">Try searching with a different name</p>
                            </div>
                        </div>
                    </div>
                    <div id="customer-results" class="row"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/customers/customer.js"></script>
</body>