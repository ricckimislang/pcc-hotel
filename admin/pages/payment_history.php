<?php
require_once '../../config/db.php';
require_once '../includes/functions.php';

// Initialize filter parameters with defaults
$period = $_GET['period'] ?? 'all';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$status = $_GET['status'] ?? 'all';

try {
    // Base query for transactions with related information
    $query = "SELECT t.*, 
              b.check_in_date, b.check_out_date, b.booking_status, 
              CONCAT(u.first_name, ' ', u.last_name) as guest_name,
              r.room_number
              FROM transactions t
              JOIN bookings b ON t.booking_id = b.booking_id
              JOIN users u ON b.user_id = u.user_id
              JOIN rooms r ON b.room_id = r.room_id
              WHERE 1=1";

    $params = [];
    $types = "";

    // Apply date filters
    if ($period == 'custom' && !empty($start_date) && !empty($end_date)) {
        $query .= " AND DATE(t.created_at) BETWEEN ? AND ?";
        $params[] = $start_date;
        $params[] = $end_date;
        $types .= "ss";
    } elseif ($period == 'today') {
        $query .= " AND DATE(t.created_at) = CURDATE()";
    } elseif ($period == 'week') {
        $query .= " AND t.created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
    } elseif ($period == 'month') {
        $query .= " AND t.created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
    }

    // Apply status filter
    if ($status != 'all') {
        $query .= " AND b.booking_status = ?";
        $params[] = $status;
        $types .= "s";
    }

    // Order by newest first
    $query .= " ORDER BY t.created_at DESC";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $transactions = $result->fetch_all(MYSQLI_ASSOC);

    // Get summary statistics
    $stats_query = "SELECT 
                    COUNT(*) as total_transactions,
                    SUM(amount) as total_revenue,
                    ROUND(AVG(amount), 2) as average_payment,
                    MAX(created_at) as last_payment_date
                    FROM transactions";

    $stmt = $conn->prepare($stats_query);
    $stmt->execute();
    $stats_result = $stmt->get_result();
    $stats = $stats_result->fetch_assoc();

    // Get monthly revenue for chart
    $chart_query = "SELECT 
                    DATE_FORMAT(created_at, '%Y-%m') as month,
                    SUM(amount) as total 
                    FROM transactions 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month";

    $stmt = $conn->prepare($chart_query);
    $stmt->execute();
    $chart_result = $stmt->get_result();
    $monthly_revenue = $chart_result->fetch_all(MYSQLI_ASSOC);

    // Get payment method distribution (simplified since there's no payment_method column)
    $payment_methods = [
        ['method' => 'Cash/Bank Transfer', 'count' => count($transactions)]
    ];
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<?php include_once '../includes/head.php'; ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<link rel="stylesheet" href="../css/payment_history.css">
<style>
    /* Custom styles for DataTables */
    .dataTables_wrapper {
        padding: 20px 0;
    }

    .dataTables_length,
    .dataTables_filter {
        margin-bottom: 15px;
    }

    .dt-buttons {
        margin-bottom: 15px;
        display: inline-block;
    }

    .dt-button {
        padding: 5px 10px;
        margin-right: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
        cursor: pointer;
        font-size: 13px;
    }

    .btn-excel {
        background-color: #1e7e34 !important;
        color: white !important;
    }

    .btn-pdf {
        background-color: #dc3545 !important;
        color: white !important;
    }

    .btn-print {
        background-color: #0069d9 !important;
        color: white !important;
    }

    .dataTables_info,
    .dataTables_paginate {
        margin-top: 15px;
    }

    .paginate_button {
        padding: 5px 10px;
        margin: 0 2px;
        border: 1px solid #ddd;
        cursor: pointer;
    }

    .paginate_button.current {
        background-color: #f2f2f2;
    }
</style>

<body>
    <?php include_once '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <h1 class="page-title">Payment History</h1>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php else: ?>

                <!-- Summary Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card summary-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <p class="card-text amount">₱<?php echo number_format($stats['total_revenue'] ?? 0, 2); ?>
                                </p>
                                <small>All time</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card">
                            <div class="card-body">
                                <h5 class="card-title">Transactions</h5>
                                <p class="card-text"><?php echo $stats['total_transactions'] ?? 0; ?></p>
                                <small>All time</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card">
                            <div class="card-body">
                                <h5 class="card-title">Average Payment</h5>
                                <p class="card-text amount">₱<?php echo number_format($stats['average_payment'] ?? 0, 2); ?>
                                </p>
                                <small>All time</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card summary-card">
                            <div class="card-body">
                                <h5 class="card-title">Last Payment</h5>
                                <p class="card-text">
                                    <?php echo $stats['last_payment_date'] ? date('M d, Y', strtotime($stats['last_payment_date'])) : 'N/A'; ?>
                                </p>
                                <small><?php echo $stats['last_payment_date'] ? date('h:i A', strtotime($stats['last_payment_date'])) : ''; ?></small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title">Monthly Revenue</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="revenueChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title">Filters</h5>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" method="GET" action="payment_history.php" class="row g-3">
                            <div class="col-md-3">
                                <label for="period" class="form-label">Time Period</label>
                                <select class="form-select" id="period" name="period">
                                    <option value="all" <?php echo $period == 'all' ? 'selected' : ''; ?>>All Time</option>
                                    <option value="today" <?php echo $period == 'today' ? 'selected' : ''; ?>>Today</option>
                                    <option value="week" <?php echo $period == 'week' ? 'selected' : ''; ?>>Last 7 Days
                                    </option>
                                    <option value="month" <?php echo $period == 'month' ? 'selected' : ''; ?>>Last 30 Days
                                    </option>
                                    <option value="custom" <?php echo $period == 'custom' ? 'selected' : ''; ?>>Custom Range
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 date-range <?php echo $period != 'custom' ? 'd-none' : ''; ?>">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="<?php echo $start_date; ?>">
                            </div>
                            <div class="col-md-3 date-range <?php echo $period != 'custom' ? 'd-none' : ''; ?>">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                    value="<?php echo $end_date; ?>">
                            </div>
                            <div class="col-md-3">
                                <label for="status" class="form-label">Booking Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="all" <?php echo $status == 'all' ? 'selected' : ''; ?>>All Statuses
                                    </option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending
                                    </option>
                                    <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>
                                        Confirmed</option>
                                    <option value="checked_in" <?php echo $status == 'checked_in' ? 'selected' : ''; ?>>
                                        Checked In</option>
                                    <option value="checked_out" <?php echo $status == 'checked_out' ? 'selected' : ''; ?>>
                                        Checked Out</option>
                                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>
                                        Cancelled</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Apply Filters</button>
                                <a href="payment_history.php" class="btn btn-outline-secondary">Reset</a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Transactions Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Transactions</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="transactionsTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Date</th>
                                        <th>Guest</th>
                                        <th>Room</th>
                                        <th>Amount</th>
                                        <th>Reference</th>
                                        <th>Booking Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($transactions) > 0): ?>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo $transaction['receipt_no']; ?></td>
                                                <td data-order="<?php echo strtotime($transaction['created_at']); ?>">
                                                    <?php echo date('M d, Y h:i A', strtotime($transaction['created_at'])); ?>
                                                </td>
                                                <td><?php echo $transaction['guest_name']; ?></td>
                                                <td>Room <?php echo $transaction['room_number']; ?></td>
                                                <td class="amount" data-order="<?php echo $transaction['amount']; ?>">
                                                    ₱<?php echo number_format($transaction['amount'], 2); ?></td>
                                                <td><?php echo $transaction['reference_no'] ?: 'N/A'; ?></td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php echo getBadgeClass($transaction['booking_status']); ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $transaction['booking_status'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-primary view-details"
                                                        data-id="<?php echo $transaction['transaction_id']; ?>"
                                                        data-booking="<?php echo $transaction['booking_id']; ?>">
                                                        <i class="fas fa-eye"></i>
                                                    </button>

                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No transactions found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- View Transaction Details Modal -->
    <div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="transaction-details">
                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Payment Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Receipt Number:</dt>
                                            <dd class="col-sm-7" id="receipt-no"></dd>
                                            <dt class="col-sm-5">Payment Date:</dt>
                                            <dd class="col-sm-7" id="payment-date"></dd>
                                            <dt class="col-sm-5">Amount:</dt>
                                            <dd class="col-sm-7" id="payment-amount"></dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Reference:</dt>
                                            <dd class="col-sm-7" id="reference-no"></dd>
                                            <dt class="col-sm-5">Payment Method:</dt>
                                            <dd class="col-sm-7">Cash/GCASH</dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Booking Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Booking ID:</dt>
                                            <dd class="col-sm-7" id="booking-id"></dd>
                                            <dt class="col-sm-5">Guest:</dt>
                                            <dd class="col-sm-7" id="guest-name"></dd>
                                            <dt class="col-sm-5">Room:</dt>
                                            <dd class="col-sm-7" id="room-number"></dd>
                                        </dl>
                                    </div>
                                    <div class="col-md-6">
                                        <dl class="row mb-0">
                                            <dt class="col-sm-5">Check In:</dt>
                                            <dd class="col-sm-7" id="check-in-date"></dd>
                                            <dt class="col-sm-5">Check Out:</dt>
                                            <dd class="col-sm-7" id="check-out-date"></dd>
                                            <dt class="col-sm-5">Status:</dt>
                                            <dd class="col-sm-7" id="booking-status"></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-header">
                                <h6 class="mb-0">Additional Items</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm" id="additional-items-table">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Price</th>
                                                <th>Quantity</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Will be populated dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                                <div id="no-items-message" class="alert alert-info" style="display: none;">
                                    No additional items for this transaction.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="printReceiptBtn">Print Receipt</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Payment Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="receiptImage" src="" class="img-fluid" alt="Payment Receipt">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="downloadReceipt" class="btn btn-primary" download>Download</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script>
        // Helper function for getting badge class
        <?php
        function getBadgeClass($status)
        {
            switch ($status) {
                case 'pending':
                    return 'warning';
                case 'confirmed':
                    return 'primary';
                case 'checked_in':
                    return 'info';
                case 'checked_out':
                    return 'success';
                case 'cancelled':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }
        ?>

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize DataTables
            const transactionsTable = $('#transactionsTable');

            // Check if there are actual data rows in the table (not just the "no transactions" row)
            const hasTransactions = transactionsTable.find('tbody tr td:first-child').text() !== 'No transactions found';

            if (hasTransactions) {
                transactionsTable.DataTable({
                    responsive: true,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, 'All Records']
                    ],
                    order: [
                        [1, 'desc']
                    ], // Sort by date column descending
                    language: {
                        search: "Search transactions:",
                        lengthMenu: "_MENU_ records per page",
                        info: "Showing _START_ to _END_ of _TOTAL_ transactions",
                        emptyTable: "No transactions found",
                        infoEmpty: "No transactions available",
                        zeroRecords: "No matching transactions found"
                    },
                    columnDefs: [{
                            orderable: false,
                            targets: 7
                        } // Disable sorting on actions column
                    ],
                    dom: 'lBfrtip',
                    buttons: [{
                            extend: 'excel',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            className: 'btn-excel',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            },
                            title: 'Payment History - ' + new Date().toLocaleDateString()
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            className: 'btn-pdf',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            },
                            title: 'Payment History - ' + new Date().toLocaleDateString(),
                            customize: function(doc) {
                                // Formatting PDF
                                doc.pageMargins = [20, 20, 20, 20];
                                doc.defaultStyle.fontSize = 10;
                                doc.styles.tableHeader.fontSize = 11;
                                doc.styles.tableHeader.alignment = 'left';
                                doc.styles.title.fontSize = 14;
                                doc.styles.title.bold = true;
                                doc.styles.title.alignment = 'center';

                                // Add hotel name
                                doc.content.splice(0, 1, {
                                    text: 'PCC Hotel - Payment History',
                                    style: 'title',
                                    margin: [0, 0, 0, 10]
                                });

                                // Add date
                                doc.content.splice(1, 0, {
                                    text: 'Generated on: ' + new Date().toLocaleDateString(),
                                    style: 'subheader',
                                    margin: [0, 0, 0, 15]
                                });

                                // Style the table
                                doc.content[2].table.widths = ['10%', '15%', '15%', '10%', '10%', '15%', '15%'];
                                doc.content[2].layout = {
                                    hLineWidth: function(i, node) {
                                        return 0.5;
                                    },
                                    vLineWidth: function(i, node) {
                                        return 0.5;
                                    },
                                    hLineColor: function(i, node) {
                                        return '#aaa';
                                    },
                                    vLineColor: function(i, node) {
                                        return '#aaa';
                                    },
                                    paddingLeft: function(i, node) {
                                        return 5;
                                    },
                                    paddingRight: function(i, node) {
                                        return 5;
                                    },
                                    paddingTop: function(i, node) {
                                        return 5;
                                    },
                                    paddingBottom: function(i, node) {
                                        return 5;
                                    }
                                };
                            }
                        },
                        {
                            extend: 'print',
                            text: '<i class="fas fa-print"></i> Print',
                            className: 'btn-print',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5, 6]
                            },
                            title: '<h3 style="text-align: center; margin-bottom: 10px;">PCC Hotel - Payment History</h3>' +
                                '<p style="text-align: center; font-size: 12px; margin-bottom: 20px;">Generated on: ' +
                                new Date().toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                }) + '</p>',
                            customize: function(win) {
                                // Let the print stylesheet handle most of the styling
                                $(win.document.body).find('table').addClass('dataTable');

                                // Format amounts
                                $(win.document.body).find('td:nth-child(5)').each(function() {
                                    const amount = parseFloat($(this).text().replace(/[^\d.]/g, ''));
                                    $(this).text('₱' + amount.toLocaleString('en-US', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }));
                                });
                            }
                        }
                    ]
                });
            } else {
                // If no transactions, hide the export buttons container
                $('.dt-buttons').hide();
            }

            // Initialize date pickers
            flatpickr('#start_date', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });

            flatpickr('#end_date', {
                dateFormat: 'Y-m-d',
                maxDate: 'today'
            });

            // Show/hide date range inputs based on period selection
            document.getElementById('period').addEventListener('change', function() {
                const dateRangeInputs = document.querySelectorAll('.date-range');
                if (this.value === 'custom') {
                    dateRangeInputs.forEach(el => el.classList.remove('d-none'));
                } else {
                    dateRangeInputs.forEach(el => el.classList.add('d-none'));
                }
            });

            // Monthly Revenue Chart
            const monthlyRevenueCtx = document.getElementById('revenueChart').getContext('2d');
            const monthlyData = <?php echo json_encode(array_map(function ($item) {
                                    return $item['total'];
                                }, $monthly_revenue)); ?>;
            const monthLabels = <?php echo json_encode(array_map(function ($item) {
                                    $date = new DateTime($item['month'] . '-01');
                                    return $date->format('M Y');
                                }, $monthly_revenue)); ?>;

            new Chart(monthlyRevenueCtx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: 'Monthly Revenue (₱)',
                        data: monthlyData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });

            // View transaction details
            document.querySelectorAll('.view-details').forEach(button => {
                button.addEventListener('click', function() {
                    const transactionId = this.getAttribute('data-id');
                    const bookingId = this.getAttribute('data-booking');

                    // Get transaction details via AJAX
                    fetch(`../api/payments/get_transaction_details.php?transaction_id=${transactionId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                populateTransactionModal(data.transaction, data.additional_items);
                                $('#transactionDetailsModal').modal('show');
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while fetching transaction details.');
                        });
                });
            });

            // View receipt image
            document.querySelectorAll('.view-receipt').forEach(button => {
                button.addEventListener('click', function() {
                    const receiptPath = this.getAttribute('data-receipt');
                    const receiptImage = document.getElementById('receiptImage');
                    const downloadLink = document.getElementById('downloadReceipt');

                    receiptImage.src = '../../' + receiptPath;
                    downloadLink.href = '../../' + receiptPath;

                    $('#receiptModal').modal('show');
                });
            });

            // Function to populate transaction modal
            function populateTransactionModal(transaction, additionalItems) {
                // Payment information
                document.getElementById('receipt-no').textContent = transaction.receipt_no;
                document.getElementById('payment-date').textContent = transaction.payment_date;
                document.getElementById('payment-amount').textContent = '₱' + parseFloat(transaction.amount).toFixed(2);
                document.getElementById('reference-no').textContent = transaction.reference_no || 'N/A';

                // Booking details
                document.getElementById('booking-id').textContent = transaction.booking_id;
                document.getElementById('guest-name').textContent = transaction.guest_name;
                document.getElementById('room-number').textContent = 'Room ' + transaction.room_number;
                document.getElementById('check-in-date').textContent = transaction.check_in_date;
                document.getElementById('check-out-date').textContent = transaction.check_out_date;

                const statusClass = getStatusBadgeClass(transaction.booking_status);
                document.getElementById('booking-status').innerHTML =
                    `<span class="badge bg-${statusClass}">${formatStatus(transaction.booking_status)}</span>`;

                // Additional items
                const itemsTableBody = document.querySelector('#additional-items-table tbody');
                itemsTableBody.innerHTML = '';

                if (additionalItems && additionalItems.length > 0) {
                    additionalItems.forEach(item => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${item.item_name}</td>
                            <td>₱${parseFloat(item.item_price).toFixed(2)}</td>
                            <td>${item.quantity}</td>
                            <td>₱${parseFloat(item.subtotal).toFixed(2)}</td>
                        `;
                        itemsTableBody.appendChild(row);
                    });
                    document.getElementById('no-items-message').style.display = 'none';
                    document.getElementById('additional-items-table').style.display = '';
                } else {
                    document.getElementById('no-items-message').style.display = '';
                    document.getElementById('additional-items-table').style.display = 'none';
                }
            }

            // Helper function to get badge class
            function getStatusBadgeClass(status) {
                switch (status) {
                    case 'pending':
                        return 'warning';
                    case 'confirmed':
                        return 'primary';
                    case 'checked_in':
                        return 'info';
                    case 'checked_out':
                        return 'success';
                    case 'cancelled':
                        return 'danger';
                    default:
                        return 'secondary';
                }
            }

            // Helper function to format status
            function formatStatus(status) {
                return status.charAt(0).toUpperCase() + status.slice(1).replace('_', ' ');
            }

            // Print receipt
            document.getElementById('printReceiptBtn').addEventListener('click', function() {
                const receiptNo = document.getElementById('receipt-no').textContent;
                const transactionId = document.querySelector('.view-details').getAttribute('data-id');

                // Redirect to receipt print page
                window.open(`print_receipt.php?transaction_id=${transactionId}`, '_blank');
            });
        });
    </script>
</body>

</html>