document.addEventListener("DOMContentLoaded", function () {
  // Check-out button click handler - use delegated event handling
  $(document).on("click", ".check-out-btn", function() {
    const bookingId = $(this).data("booking-id");
    loadBookingDetails(bookingId);
  });

  // Load booking details for payment processing
  function loadBookingDetails(bookingId) {
    // Clear previous form data
    resetPaymentForm();

    // Set booking ID
    $("#payment_booking_id").val(bookingId);

    // Fetch booking details from the server
    $.ajax({
      url: "../api/payments/get_booking_details.php",
      type: "GET",
      data: { booking_id: bookingId },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          const booking = response.data;

          // Populate booking details
          $(".guest-name").text(booking.guest_name);
          $(".room-number").text(booking.room_number);
          $(".check-in-date").text(booking.check_in_date);
          $(".check-out-date").text(booking.check_out_date);

          // Populate amount details
          $(".room-total").text(
            "₱" + parseFloat(booking.room_total).toFixed(2)
          );
          $(".total-amount").text(
            "₱" + parseFloat(booking.total_amount).toFixed(2)
          );
          $(".amount-paid").text(
            "₱" + parseFloat(booking.amount_paid).toFixed(2)
          );

          const balanceDue =
            parseFloat(booking.total_amount) - parseFloat(booking.amount_paid);
          $(".balance-due").text("₱" + balanceDue.toFixed(2));

          // No need to set payment amount as we're only updating extra charges
          // Instead, set payment amount to 0 or hide it
          $("#payment_amount").val("0.00");
          // Hide container but don't disable the field so it's still submitted
          $(".payment-amount-container").addClass("d-none");

          // Change the Process Payment button text
          $("#processPaymentBtn").text("Process Check-out");

          // Show the modal
          $("#processPaymentModal").modal("show");
        } else {
          showToast(
            "error",
            "Error loading booking details: " + response.message
          );
        }
      },
      error: function (xhr, status, error) {
        showToast("error", "Error: " + error);
      },
    });
  }

  // Reset payment form
  function resetPaymentForm() {
    $("#processPaymentForm")[0].reset();
    $("#additionalFeesTable tbody").empty();
    $("#noFeesMessage").show();
    $(".additional-fees").text("₱0.00");
    $("#totalAdditionalFees").text("₱0.00");
    updateTotalAmount();

    // Reset UI elements
    $(".payment-amount-container").removeClass("d-none");
    $("#payment_amount").prop("disabled", false);
    $("#processPaymentBtn").text("Process Check-out");
  }

  // Initialize UI components for additional fees
  $("#addFeeBtn").click(function () {
    $("#fee_item_name").val("");
    $("#fee_item_price").val("");
    $("#fee_item_quantity").val(1);
    $("#customItemNameContainer").hide();
    $("#addFeeItemModal").modal("show");
  });

  // Show/hide custom item name field
  $("#fee_item_name").change(function () {
    if ($(this).val() === "custom") {
      $("#customItemNameContainer").show();
    } else {
      $("#customItemNameContainer").hide();
    }
  });


  // Add fee button in the modal
  $("#confirmAddFeeBtn").click(function () {
    // Get form values
    let itemName = $("#fee_item_name").val();
    const selectedOption = $("#fee_item_name option:selected");
    
    if (itemName === "custom") {
      itemName = $("#custom_item_name").val();
      if (!itemName.trim()) {
        showToast("error", "Please enter a custom item name");
        return;
      }
    } else {
      // Use the item name from the dataset for non-custom items
      itemName = selectedOption.data('name');
    }

    const itemPrice = parseFloat($("#fee_item_price").val());
    if (isNaN(itemPrice) || itemPrice <= 0) {
      showToast("error", "Please enter a valid price");
      return;
    }

    const itemQuantity = parseInt($("#fee_item_quantity").val());
    if (isNaN(itemQuantity) || itemQuantity <= 0) {
      showToast("error", "Please enter a valid quantity");
      return;
    }

    const subtotal = itemPrice * itemQuantity;

    // Add row to the table
    const newRow = `
      <tr>
        <td>${itemName}</td>
        <td>₱${itemPrice.toFixed(2)}</td>
        <td>${itemQuantity}</td>
        <td>₱${subtotal.toFixed(2)}</td>
        <td>
          <button type="button" class="btn btn-sm btn-danger remove-fee">
            <i class="fa fa-trash"></i>
          </button>
        </td>
      </tr>
    `;

    $("#additionalFeesTable tbody").append(newRow);
    $("#noFeesMessage").hide();

    // Update totals
    updateAdditionalFees();

    // Close modal and reset form
    $("#addFeeItemModal").modal("hide");
    $("#addFeeItemForm")[0].reset();
    $("#customItemNameContainer").hide();
  });

  // Remove fee item
  $(document).on("click", ".remove-fee", function () {
    $(this).closest("tr").remove();

    if ($("#additionalFeesTable tbody tr").length === 0) {
      $("#noFeesMessage").show();
    }

    updateAdditionalFees();
  });

  // Calculate additional fees total
  function updateAdditionalFees() {
    let total = 0;

    $("#additionalFeesTable tbody tr").each(function () {
      const subtotalText = $(this).find("td:eq(3)").text().replace("₱", "");
      total += parseFloat(subtotalText);
    });

    $("#totalAdditionalFees").text("₱" + total.toFixed(2));
    $(".additional-fees").text("₱" + total.toFixed(2));

    updateTotalAmount();
  }

  // Update total amount when additional fees change
  function updateTotalAmount() {
    const roomTotal = parseFloat($(".room-total").text().replace("₱", "")) || 0;
    const additionalFees =
      parseFloat($(".additional-fees").text().replace("₱", "")) || 0;
    const amountPaid =
      parseFloat($(".amount-paid").text().replace("₱", "")) || 0;

    const totalAmount = roomTotal + additionalFees;
    const balanceDue = totalAmount - amountPaid;

    $(".total-amount").text("₱" + totalAmount.toFixed(2));
    $(".balance-due").text("₱" + balanceDue.toFixed(2));
  }

  // Add New Payment button in history modal
  $("#addNewPaymentBtn").click(function () {
    const bookingId = $(this).data("booking-id");
    $("#paymentHistoryModal").modal("hide");
    loadBookingDetails(bookingId);
  });

  // View payment history button
  $(document).on("click", ".view-history-btn", function () {
    const bookingId = $(this).data("booking-id");
    loadPaymentHistory(bookingId);
  });

  // Load payment history
  function loadPaymentHistory(bookingId) {
    $.ajax({
      url: "api/payments/get_payment_history.php",
      type: "GET",
      data: { booking_id: bookingId },
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          const booking = response.booking;
          const payments = response.payments;

          // Set booking info
          $(".guest-name").text(booking.guest_name);
          $(".room-number").text(booking.room_number);
          $(".total-amount").text(
            "₱" + parseFloat(booking.total_amount).toFixed(2)
          );
          $(".balance-due").text(
            "₱" + parseFloat(booking.balance_due).toFixed(2)
          );

          // Set payment status badge
          let statusClass = "bg-success";
          let statusText = "Paid";

          if (parseFloat(booking.balance_due) > 0) {
            statusClass = "bg-warning";
            statusText = "Partially Paid";

            if (parseFloat(booking.amount_paid) === 0) {
              statusClass = "bg-danger";
              statusText = "Unpaid";
            }
          }

          $(".payment-status-badge")
            .removeClass("bg-success bg-warning bg-danger")
            .addClass(statusClass)
            .text(statusText);

          // Populate payment history table
          $("#paymentHistoryTableBody").empty();

          if (payments.length > 0) {
            payments.forEach(function (payment) {
              let additionalItems = "None";
              if (
                payment.additional_items &&
                payment.additional_items.length > 0
              ) {
                additionalItems = payment.additional_items
                  .map((item) => `${item.name} (${item.quantity})`)
                  .join(", ");
              }

              let receiptButton = "";
              if (payment.receipt_url) {
                receiptButton = `<button type="button" class="btn btn-sm btn-info view-receipt" data-receipt="${payment.receipt_url}">
                  <i class="bi bi-file-earmark-image"></i> View
                </button>`;
              }

              const row = `
                <tr>
                  <td>${payment.payment_date}</td>
                  <td>₱${parseFloat(payment.amount).toFixed(2)}</td>
                  <td>${payment.transaction_id || "N/A"}</td>
                  <td>${additionalItems}</td>
                  <td><span class="badge bg-success">Completed</span></td>
                  <td>${receiptButton}</td>
                </tr>
              `;

              $("#paymentHistoryTableBody").append(row);
            });

            $("#noPaymentsMessage").hide();
          } else {
            $("#noPaymentsMessage").show();
          }

          // Store booking ID for new payment button
          $("#addNewPaymentBtn").data("booking-id", bookingId);

          // Show the modal
          $("#paymentHistoryModal").modal("show");
        } else {
          showToast(
            "error",
            "Error loading payment history: " + response.message
          );
        }
      },
      error: function (xhr, status, error) {
        showToast("error", "Error: " + error);
      },
    });
  }

  // JavaScript code related to payment form submission
  $("#processPaymentBtn").on("click", function (e) {
    e.preventDefault();
    $("#checkoutConfirmModal").modal("show");
  });

  $("#confirmCheckoutBtn").on("click", function (e) {
    e.preventDefault();
    $("#checkoutConfirmModal").modal("hide");

    // Grab the form element
    const form = document.getElementById("processPaymentForm");

    // Collect additional items data
    const additionalItems = [];
    $("#additionalFeesTable tbody tr").each(function () {
      const item = {
        name: $(this).find("td:eq(0)").text(),
        price: parseFloat($(this).find("td:eq(1)").text().replace("₱", "")),
        quantity: parseInt($(this).find("td:eq(2)").text()),
        subtotal: parseFloat($(this).find("td:eq(3)").text().replace("₱", "")),
      };
      additionalItems.push(item);
    });

    // ✅ Use actual form element
    const formData = new FormData(form);
    formData.append("additional_items", JSON.stringify(additionalItems));

    // ✅ Target the submit button correctly
    const submitBtn = $("#processPaymentBtn");
    const originalBtnText = submitBtn.html();
    submitBtn.html(
      '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...'
    );
    submitBtn.prop("disabled", true);

    // Submit form to server
    $.ajax({
      url: "../api/payments/process_payment.php",
      type: "POST",
      data: formData,
      contentType: false,
      processData: false,
      dataType: "json",
      success: function (response) {
        if (response.status === "success") {
          showToast("success", "Check-out processed successfully");

          // If this is the final payment and checkout is complete
          if (response.checkout_complete) {
            showToast("success", "Check-out completed successfully");
            window.location.reload();
            
          } else {
            // Just close the modal
            $("#processPaymentModal").modal("hide");
            // Refresh the bookings table after a short delay
          }
        } else {
          showToast("error", "Error processing payment: " + response.message);
        }
      },
      error: function (xhr, status, error) {
        showToast("error", "Error: " + error);
      },
      complete: function () {
        // Reset button state
        submitBtn.html(originalBtnText);
        submitBtn.prop("disabled", false);
      },
    });
  });

  // View receipt button
  $(document).on("click", ".view-receipt", function () {
    const receiptUrl = $(this).data("receipt");
    $(".receipt-image").attr("src", receiptUrl);
    $(".download-receipt").attr("href", receiptUrl);
    $("#viewReceiptModal").modal("show");
  });

  // Helper function for showing toast notifications
  function showToast(type, message) {
    const toastClass = type === "success" ? "bg-success" : "bg-danger";
    const toast = `
      <div class="toast align-items-center ${toastClass} text-white" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            ${message}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    `;

    const toastContainer = document.getElementById("toast-container");
    if (!toastContainer) {
      $("body").append(
        '<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>'
      );
    }

    $("#toast-container").append(toast);
    const toastElement = new bootstrap.Toast(
      $("#toast-container .toast").last()[0],
      {
        delay: 3000,
      }
    );
    toastElement.show();
  }
});
