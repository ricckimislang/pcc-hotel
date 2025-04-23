document.addEventListener("DOMContentLoaded", function () {
  // Check-out button click handler
  const checkOutButtons = document.querySelectorAll(".check-out-btn");
  checkOutButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const bookingId = button.getAttribute("data-booking-id");
      $('#payment_booking_id').val(bookingId);
      $('#processPaymentModal').modal('show');
    });
  });

  // Initialize UI components for additional fees
  $('#addFeeBtn').click(function() {
    $('#addFeeItemModal').modal('show');
  });
  
  // Show/hide custom item name field
  $('#fee_item_name').change(function() {
    if ($(this).val() === 'custom') {
      $('#customItemNameContainer').show();
    } else {
      $('#customItemNameContainer').hide();
    }
  });
  
  // Pre-fill prices when selecting standard items
  $('#fee_item_name').change(function() {
    const priceGuide = {
      'Towel': 5.00,
      'Soap': 2.00,
      'Shampoo': 3.00,
      'Toothbrush': 2.50,
      'Toothpaste': 2.50,
      'Slippers': 4.00,
      'Extra Bed': 15.00,
      'Laundry': 10.00,
      'Room Service': 8.00,
      'Late Checkout': 20.00
    };
    
    const selectedItem = $(this).val();
    if (priceGuide[selectedItem]) {
      $('#fee_item_price').val(priceGuide[selectedItem].toFixed(2));
    } else {
      $('#fee_item_price').val('');
    }
  });
  
  // Add fee button in the modal
  $('#confirmAddFeeBtn').click(function() {
    // Just close the modal in this simplified version
    $('#addFeeItemModal').modal('hide');
    $('#addFeeItemForm')[0].reset();
    $('#customItemNameContainer').hide();
  });
  
  // Add New Payment button in history modal
  $('#addNewPaymentBtn').click(function() {
    $('#paymentHistoryModal').modal('hide');
    $('#processPaymentModal').modal('show');
  });
  
  // Payment form submission - just prevent default for now
  $('#processPaymentForm').submit(function(e) {
    e.preventDefault();
    // UI feedback
    alert('Payment processing would happen here');
    $('#processPaymentModal').modal('hide');
  });
  
  // View receipt button
  $(document).on('click', '.view-receipt', function() {
    const receiptUrl = $(this).data('receipt');
    $('.receipt-image').attr('src', receiptUrl);
    $('.download-receipt').attr('href', receiptUrl);
    $('#viewReceiptModal').modal('show');
  });
});
