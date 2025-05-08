<?php
// Include the database connection
require_once '../../config/db.php';
require_once '../includes/functions.php';

// Check for transaction ID
if (!isset($_GET['transaction_id']) || empty($_GET['transaction_id'])) {
    die('Transaction ID is required');
}

$transaction_id = intval($_GET['transaction_id']);

try {
    // Get transaction details with booking and guest information
    $query = "SELECT t.*, 
              DATE_FORMAT(t.created_at, '%M %d, %Y %h:%i %p') as payment_date,
              DATE_FORMAT(b.check_in_date, '%M %d, %Y') as check_in_date,
              DATE_FORMAT(b.check_out_date, '%M %d, %Y') as check_out_date,
              b.booking_status, b.total_price, 
              DATEDIFF(b.check_out_date, b.check_in_date) as nights_stayed,
              CONCAT(u.first_name, ' ', u.last_name) as guest_name,
              u.email,
              r.room_number, rt.type_name
              FROM transactions t
              JOIN bookings b ON t.booking_id = b.booking_id
              JOIN users u ON b.user_id = u.user_id
              JOIN rooms r ON b.room_id = r.room_id
              JOIN room_types rt ON r.room_type_id = rt.room_type_id
              WHERE t.transaction_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die('Transaction not found');
    }

    $transaction = $result->fetch_assoc();

    // Get additional items for this transaction
    $query = "SELECT * FROM additional_items WHERE transaction_id = ? ORDER BY item_name";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $transaction_id);
    $stmt->execute();
    $items_result = $stmt->get_result();

    $additional_items = [];
    $additional_total = 0;

    while ($item = $items_result->fetch_assoc()) {
        $additional_items[] = $item;
        $additional_total += $item['subtotal'];
    }

    // If no items in the table but there's an extra_pay value, create a legacy entry
    if (empty($additional_items) && !empty($transaction['extra_pay']) && $transaction['extra_pay'] > 0) {
        $additional_items[] = [
            'item_id' => 0,
            'transaction_id' => $transaction_id,
            'item_name' => 'Additional Charges',
            'item_price' => $transaction['extra_pay'],
            'quantity' => 1,
            'subtotal' => $transaction['extra_pay']
        ];
        $additional_total = $transaction['extra_pay'];
    }

    // Get hotel information
    $hotel_name = "PCC Hotel";
    $hotel_address = "OSMENA STREET, ZONE 1, KORONADAL CITY, Koronadal, Philippines";
    $hotel_phone = "0920 665 3062";
    $hotel_email = "pcchotel@gmail.com";
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?php echo $transaction['receipt_no']; ?></title>
    <style>
        @media print {
            body {
                width: 100%;
                margin: 0;
                padding: 0;
                font-size: 12pt;
                font-family: Arial, sans-serif;
            }

            .no-print {
                display: none !important;
            }

            .receipt-container {
                width: 100%;
                max-width: 800px;
                margin: 0 auto;
                padding: 20px;
            }
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: #333;
            background: #f9f9f9;
        }

        .receipt-container {
            width: 100%;
            max-width: 800px;
            margin: 20px auto;
            padding: 30px;
            background: #fff;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .receipt-header h1 {
            margin: 0;
            color: #0d6efd;
            font-size: 24px;
        }

        .receipt-header p {
            margin: 5px 0 0;
            color: #777;
            font-size: 14px;
        }

        .receipt-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .receipt-info div {
            flex: 1;
        }

        .receipt-info h4 {
            margin: 0 0 10px;
            color: #555;
            font-size: 16px;
        }

        .receipt-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        .booking-details {
            margin-bottom: 30px;
        }

        .booking-details h4 {
            margin: 0 0 15px;
            color: #555;
            font-size: 16px;
        }

        .booking-details p {
            margin: 5px 0;
            display: flex;
        }

        .booking-details p span:first-child {
            width: 150px;
            font-weight: bold;
            color: #666;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            color: #555;
            border-bottom: 1px solid #ddd;
        }

        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .totals {
            width: 100%;
            margin-bottom: 30px;
        }

        .totals td {
            padding: 5px 10px;
        }

        .totals .label {
            text-align: right;
            font-weight: bold;
            color: #666;
        }

        .totals .total {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 50px;
            color: #777;
            font-size: 14px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .print-button {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            background: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .print-button:hover {
            background: #0b5ed7;
        }
    </style>
</head>

<body>
    <div class="receipt-container">
        <div class="receipt-header">
            <h1><?php echo $hotel_name; ?></h1>
            <p><?php echo $hotel_address; ?></p>
            <p>Tel: <?php echo $hotel_phone; ?> | Email: <?php echo $hotel_email; ?></p>
            <h2>PAYMENT RECEIPT</h2>
            <p>Receipt #: <?php echo $transaction['receipt_no']; ?></p>
            <p>Date: <?php echo $transaction['payment_date']; ?></p>
        </div>

        <div class="receipt-info">
            <div>
                <h4>Guest Information</h4>
                <p>Name: <?php echo $transaction['guest_name']; ?></p>
                <p>Email: <?php echo $transaction['email']; ?></p>
            </div>
            <div>
                <h4>Payment Information</h4>
                <p>Payment Method: Cash/Bank Transfer</p>
                <p>Reference #: <?php echo $transaction['reference_no'] ?: 'N/A'; ?></p>
            </div>
        </div>

        <div class="booking-details">
            <h4>Booking Details</h4>
            <p>
                <span>Booking ID:</span>
                <span><?php echo $transaction['booking_id']; ?></span>
            </p>
            <p>
                <span>Room:</span>
                <span>Room <?php echo $transaction['room_number']; ?> (<?php echo $transaction['type_name']; ?>)</span>
            </p>
            <p>
                <span>Check-in Date:</span>
                <span><?php echo $transaction['check_in_date']; ?></span>
            </p>
            <p>
                <span>Check-out Date:</span>
                <span><?php echo $transaction['check_out_date']; ?></span>
            </p>
            <p>
                <span>Nights:</span>
                <span><?php echo $transaction['nights_stayed']; ?></span>
            </p>
            <p>
                <span>Status:</span>
                <span><?php echo ucfirst(str_replace('_', ' ', $transaction['booking_status'])); ?></span>
            </p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Room Payment</td>
                    <td>-</td>
                    <td>-</td>
                    <td style="text-align: right;">
                        ₱<?php echo number_format($transaction['amount'] - $additional_total, 2); ?></td>
                </tr>
                <?php if (count($additional_items) > 0): ?>
                    <?php foreach ($additional_items as $item): ?>
                        <tr>
                            <td><?php echo $item['item_name']; ?></td>
                            <td>₱<?php echo number_format($item['item_price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right;">₱<?php echo number_format($item['subtotal'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td></td>
                <td class="label">Subtotal:</td>
                <td>₱<?php echo number_format($transaction['amount'], 2); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="label">Additional Charges:</td>
                <td>₱<?php echo number_format($additional_total, 2); ?></td>
            </tr>
            <tr>
                <td></td>
                <td class="label">Total Paid:</td>
                <td class="total">₱<?php echo number_format($transaction['amount'], 2); ?></td>
            </tr>
        </table>

        <div class="receipt-footer">
            <p>Thank you for choosing <?php echo $hotel_name; ?>!</p>
            <p>We hope you enjoyed your stay and look forward to welcoming you again.</p>
        </div>

        <button class="print-button no-print" onclick="window.print()">Print Receipt</button>
    </div>

    <script>
        // Auto-print when the page loads
        window.onload = function() {
            // Automatically open print dialog
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>

</html>