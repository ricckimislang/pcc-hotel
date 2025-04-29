<?php
require_once '../includes/head.php';
?>
<link rel="stylesheet" href="../css/feedback.css">

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">

            <div class="page-header d-flex justify-content-between align-items-center">
                <h1>Feedback Management</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a class="text-decoration-none" href="../pages/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Feedback</li>
                    </ol>
                </nav>
            </div>

            <!-- Feedback Dashboard -->
            <div class="row">
                <!-- Average Rating Card -->
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body text-center">
                            <h5 class="card-title">Average Rating</h5>
                            <div class="rating-display my-3">
                                <span id="avg-rating-value" class="display-4 fw-bold">0.0</span>
                                <div class="stars-display mt-2">
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                    <i class="fas fa-star text-warning"></i>
                                </div>
                            </div>
                            <p class="text-muted mb-0"><span id="total-ratings">0</span> total ratings</p>
                        </div>
                    </div>
                </div>

                <!-- Rating Distribution Card -->
                <div class="col-lg-5 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Rating Distribution</h5>
                            <div class="rating-bars mt-4">
                                <!-- 5 Star Rating Bar -->
                                <div class="rating-bar-item d-flex align-items-center mb-2">
                                    <div class="rating-label me-2">5</div>
                                    <div class="progress flex-grow-1 me-2" style="height: 12px;">
                                        <div id="five-star-bar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="rating-count" id="five-star-count">0</div>
                                </div>
                                <!-- 4 Star Rating Bar -->
                                <div class="rating-bar-item d-flex align-items-center mb-2">
                                    <div class="rating-label me-2">4</div>
                                    <div class="progress flex-grow-1 me-2" style="height: 12px;">
                                        <div id="four-star-bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="rating-count" id="four-star-count">0</div>
                                </div>
                                <!-- 3 Star Rating Bar -->
                                <div class="rating-bar-item d-flex align-items-center mb-2">
                                    <div class="rating-label me-2">3</div>
                                    <div class="progress flex-grow-1 me-2" style="height: 12px;">
                                        <div id="three-star-bar" class="progress-bar bg-info" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="rating-count" id="three-star-count">0</div>
                                </div>
                                <!-- 2 Star Rating Bar -->
                                <div class="rating-bar-item d-flex align-items-center mb-2">
                                    <div class="rating-label me-2">2</div>
                                    <div class="progress flex-grow-1 me-2" style="height: 12px;">
                                        <div id="two-star-bar" class="progress-bar bg-warning" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="rating-count" id="two-star-count">0</div>
                                </div>
                                <!-- 1 Star Rating Bar -->
                                <div class="rating-bar-item d-flex align-items-center mb-2">
                                    <div class="rating-label me-2">1</div>
                                    <div class="progress flex-grow-1 me-2" style="height: 12px;">
                                        <div id="one-star-bar" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                                    </div>
                                    <div class="rating-count" id="one-star-count">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Trend Card -->
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title">Recent Trend</h5>
                            <div class="trend-chart-container mt-3">
                                <canvas id="rating-trend-chart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <h5 class="me-3 mb-0">Filter Feedback:</h5>
                                    <div class="btn-group me-3" role="group">
                                        <button type="button" class="btn btn-outline-primary active" data-filter="all">All</button>
                                        <button type="button" class="btn btn-outline-primary" data-filter="5">5 Stars</button>
                                        <button type="button" class="btn btn-outline-primary" data-filter="4">4 Stars</button>
                                        <button type="button" class="btn btn-outline-primary" data-filter="3">3 Stars</button>
                                        <button type="button" class="btn btn-outline-primary" data-filter="2">2 Stars</button>
                                        <button type="button" class="btn btn-outline-primary" data-filter="1">1 Star</button>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center">
                                    <div class="input-group date-range">
                                        <input type="date" class="form-control" id="start-date">
                                        <span class="input-group-text">to</span>
                                        <input type="date" class="form-control" id="end-date">
                                        <button class="btn btn-primary" id="apply-date-filter">Apply</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback List -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Customer Feedback</h5>
                            <div class="table-responsive mt-3">
                                <table id="feedback-table" class="table table-hover" width="100%">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer</th>
                                            <th>Rating</th>
                                            <th>Comment</th>
                                            <th>Room</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Feedback Modal -->
    <div class="modal fade" id="viewFeedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Feedback Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="feedback-details">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p><strong>Name:</strong> <span id="modal-customer-name"></span></p>
                                <p><strong>Email:</strong> <span id="modal-customer-email"></span></p>
                                <p><strong>Phone:</strong> <span id="modal-customer-phone"></span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Booking Information</h6>
                                <p><strong>Room:</strong> <span id="modal-room-number"></span></p>
                                <p><strong>Room Type:</strong> <span id="modal-room-type"></span></p>
                                <p><strong>Stay Period:</strong> <span id="modal-stay-period"></span></p>
                            </div>
                        </div>
                        <div class="feedback-rating mb-4">
                            <h6>Rating</h6>
                            <div class="stars-display d-inline-block me-2">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <span id="modal-rating" class="fs-4 fw-bold">5.0</span>
                        </div>
                        <div class="feedback-comment mb-3">
                            <h6>Comment</h6>
                            <div class="card">
                                <div class="card-body bg-light">
                                    <p id="modal-comment" class="mb-0"></p>
                                </div>
                            </div>
                        </div>
                        <div class="feedback-date text-muted">
                            <small>Submitted on <span id="modal-date"></span></small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/feedback/feedback.js"></script>
</body>
