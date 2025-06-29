$(document).ready(function () {
  // Initialize DataTable
  const bookingsTable = initializeBookingsDataTable();
  const checkInOutTable = initializeCheckInOutDataTable();
  const refreshTable = document.querySelector("#refreshTable");

  // Initialize event handlers
  initializeEventHandlers(bookingsTable);

  // Initialize modal handlers
  initializeModalHandlers();

  refreshTable.addEventListener("click", function () {
    window.location.reload();
  });
});

// DataTable initialization
function initializeBookingsDataTable() {
  return $("#bookingsTable").DataTable({
    dom: "Bfrtip",
    buttons: [
      {
        extend: "print",
        text: '<i class="fas fa-print"></i> Print',
        className: "btn btn-primary",
      },
    ],
    pageLength: 10,
    order: [[0, "desc"]],
    responsive: true,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search bookings...",
      lengthMenu: "Show _MENU_ entries",
      info: "Showing _START_ to _END_ of _TOTAL_ entries",
      infoEmpty: "Showing 0 to 0 of 0 entries",
      infoFiltered: "(filtered from _MAX_ total entries)",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Previous",
      },
    },
  });
}
function initializeCheckInOutDataTable() {
  return $("#checkInOutTable").DataTable({
    dom: "Bfrtip",
    buttons: [
      {
        extend: "print",
        text: '<i class="fas fa-print"></i> Print',
        className: "btn btn-primary",
      },
    ],
    pageLength: 10,
    order: [[0, "desc"]],
    responsive: true,
    language: {
      search: "_INPUT_",
      searchPlaceholder: "Search Confirmed Bookings...",
      lengthMenu: "Show _MENU_ entries",
      info: "Showing _START_ to _END_ of _TOTAL_ entries",
      infoEmpty: "Showing 0 to 0 of 0 entries",
      infoFiltered: "(filtered from _MAX_ total entries)",
      paginate: {
        first: "First",
        last: "Last",
        next: "Next",
        previous: "Previous",
      },
    },
  });
}

// Event Handlers
function initializeEventHandlers(bookingsTable) {
  // View booking - use delegated event handling
  $(document).on("click", ".view-btn", function () {
    showBookingDetails($(this).data("booking-id"));
  });

  // Edit booking - use delegated event handling
  $(document).on("click", ".edit-btn", function () {
    const bookingId = $(this).data("booking-id");
    if (!bookingId) {
      console.error("No booking ID found on edit button");
      return;
    }
    $("#editBookingModal").data("booking-id", bookingId);
    showEditBookingModal(bookingId);
  });

  // Confirm booking
  $(document).on("click", "#confirmBookingBtn", function () {
    console.log("Confirm booking button clicked", $(this).data("booking-id"));
    confirmBooking($(this).data("booking-id"));
  });

  // Save booking changes
  $("#saveBookingChanges").on("click", saveBookingChanges);
}

// Modal Handlers
function initializeModalHandlers() {
  // View Booking Modal
  $("#viewBookingModal").on("show.bs.modal", function (e) {
    // Get booking ID from either relatedTarget or the cancel button
    const bookingId =
      $(e.relatedTarget)?.data("booking-id") ||
      $("#cancelBookingBtn").data("booking-id");
    console.log("View modal opened, booking ID:", bookingId);

    if (!bookingId) {
      console.error("No booking ID found when opening view modal");
      return;
    }

    // Ensure cancel button has the booking ID
    $("#cancelBookingBtn").data("booking-id", bookingId);
    // Also set the booking ID on the confirm button
    $("#confirmBookingBtn").data("booking-id", bookingId);
    toggleCancelButtonVisibility();
  });

  // Edit Booking Modal
  $("#editBookingModal").on("show.bs.modal", function (e) {
    const bookingId = e.relatedTarget
      ? $(e.relatedTarget).data("booking-id")
      : $(this).data("booking-id");

    if (!bookingId) {
      console.error("No booking ID found for edit modal");
      $(this).modal("hide");
      return;
    }

    $(this).data("booking-id", bookingId);
    loadBookingDetails(bookingId);
  });

  // Policy Modal
  initializePolicyModal();
}

// Booking Functions
function showBookingDetails(bookingId) {
  console.log("Showing booking details for ID:", bookingId);
  if (!bookingId) {
    console.error("No booking ID provided for showing details");
    showError("No booking ID provided");
    return;
  }

  $.ajax({
    url: "../api/bookings/get_booking_details.php",
    method: "POST",
    data: { booking_id: bookingId },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        populateBookingDetails(response.data);
        // Set the booking ID on the cancel button before showing modal
        $("#cancelBookingBtn").data("booking-id", bookingId);
        showModal("viewBookingModal");
      } else {
        showError(response.message || "Error loading booking details");
      }
    },
    error: function () {
      showError("Error loading booking details");
    },
  });
}

function showEditBookingModal(bookingId) {
  $.ajax({
    url: "../api/bookings/get_booking_details.php",
    method: "POST",
    data: { booking_id: bookingId },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        populateEditForm(response.data);
        showModal("editBookingModal");
      } else {
        showError(response.message || "Error loading booking details");
      }
    },
    error: function () {
      showError("Error loading booking details");
    },
  });
}

function confirmBooking(bookingId) {
  if (confirm("Are you sure you want to confirm this booking?")) {
    $.ajax({
      url: "../api/bookings/confirm_booking.php",
      method: "POST",
      data: { booking_id: bookingId },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          showSuccess("Booking confirmed successfully!");
          hideModal("viewBookingModal");
          location.reload();
        } else {
          showError(response.message || "Error confirming booking");
        }
      },
      error: function () {
        showError("Error confirming booking");
      },
    });
  }
}

// Form Population Functions
function populateBookingDetails(data) {
  // Guest information
  $(".guest-name").text(data.guest.name);
  $(".guest-email").text(data.guest.email);
  $(".guest-phone").text(data.guest.phone);

  // Room details
  $(".room-number").text(data.room.number);
  $(".room-type").text(data.room.type);
  $(".room-floor").text(
    data.room.floor === 1 ? "Ground Floor" : "Second Floor"
  );

  // Booking information
  $(".check-in-date").text(data.booking.check_in);
  $(".check-out-date").text(data.booking.check_out);
  $(".total-amount").text("₱" + data.booking.total_price);
  updateStatusClasses(".booking-status", data.booking.status);
  updateStatusClasses(".payment-status", data.booking.payment_status);

  // Payment information
  $(".reference-no").text(data.payment.reference_no);
  const paymentScreenshotPath =
    "../../public/" + data.payment.payment_screenshot;
  $(".payment-proof").attr("src", paymentScreenshotPath);
  $(".payment-proof-link").attr("href", paymentScreenshotPath);
  $(".total-guests").text(data.booking.guests_count + " guests");

  // Special requests
  if (data.booking.special_requests) {
    $(".special-requests").text(data.booking.special_requests).show();
  } else {
    $(".special-requests").hide();
  }

  // Update confirm button state
  $("#confirmBookingBtn").css(
    "display",
    data.booking.payment_status.toLowerCase() !== "paid" ||
      data.booking.status.toLowerCase() === "confirmed" ||
      data.booking.status.toLowerCase() === "cancelled" ||
      data.booking.status.toLowerCase() === "checked_in"
      ? "none"
      : "block"
  );
  $("#confirmBookingBtn").data("booking-id", data.booking.booking_id);
}

function populateEditForm(data) {
  // Set hidden fields
  $("#edit_booking_id").val(data.booking.id);
  $("#original_check_in").val(data.booking.check_in);
  $("#original_check_out").val(data.booking.check_out);
  $("#original_total").val(data.booking.total_price);
  $("#room_base_price").val(data.room.base_price);

  // Guest information
  $(".edit-guest-name").text(data.guest.name);
  $(".edit-guest-email").text(data.guest.email);
  $(".edit-guest-phone").text(data.guest.phone);

  // Room information
  $(".edit-room-number").text(data.room.number);
  $(".edit-room-type").text(data.room.type);
  $(".edit-room-floor").text(data.room.floor);

  // Booking information
  $("#edit_check_in").val(data.booking.check_in.split(" ")[0]);
  $("#edit_check_out").val(data.booking.check_out.split(" ")[0]);
  $("#edit_guests_count").text(data.booking.guests_count);
  $("#edit_special_requests").val(data.booking.special_requests);
  $("#edit_booking_status").val(data.booking.status);
  $("#edit_payment_status").val(data.booking.payment_status);

  // Hide extension calculation initially
  $("#extensionCalculation, #additionalPaymentSection").hide();
}

// Utility Functions
function updateStatusClasses(selector, status) {
  $(selector)
    .text(status)
    .removeClass("text-success text-warning text-danger")
    .addClass(getStatusClass(status.toLowerCase()));
}

function getStatusClass(status) {
  switch (status) {
    case "confirmed":
    case "paid":
      return "text-success";
    case "pending":
    case "partial":
      return "text-warning";
    case "cancelled":
    case "refunded":
      return "text-danger";
    default:
      return "";
  }
}

function showModal(modalId) {
  new bootstrap.Modal(document.getElementById(modalId)).show();
}

function hideModal(modalId) {
  bootstrap.Modal.getInstance(document.getElementById(modalId)).hide();
}

function showSuccess(message) {
  alert(message);
}

function showError(message) {
  alert(message);
}

function toggleCancelButtonVisibility() {
  const bookingStatus = $(".booking-status").text().trim().toLowerCase();
  $("#cancelBookingBtn").toggle(
    bookingStatus === "confirmed" || bookingStatus === "pending"
  );
}

function formatDateForDisplay(dateString) {
  return new Date(dateString).toLocaleDateString(undefined, {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
}

// Policy Modal Functions
function initializePolicyModal() {
  const adminPolicyAgreement = $("#adminPolicyAgreement");
  const adminPolicyAccept = $("#adminPolicyAccept");
  const cancelBookingBtn = $("#cancelBookingBtn");
  let currentBookingId = null;

  adminPolicyAgreement.on("change", function () {
    adminPolicyAccept.prop("disabled", !this.checked);
  });

  cancelBookingBtn.on("click", function () {
    currentBookingId = $(this).data("booking-id");
    console.log("Cancel button clicked, booking ID:", currentBookingId);
    if (!currentBookingId) {
      console.error("No booking ID found on cancel button");
      showError("No booking ID found");
      return;
    }
    adminPolicyAgreement.prop("checked", false);
    adminPolicyAccept.prop("disabled", true);
    showModal("adminPolicyModal");
  });

  adminPolicyAccept.on("click", function () {
    console.log(
      "Policy accepted, proceeding with cancellation for booking ID:",
      currentBookingId
    );
    if (!currentBookingId) {
      console.error("No booking ID found when accepting policy");
      showError("No booking ID found");
      return;
    }
    hideModal("adminPolicyModal");
    proceedWithAdminCancellation(currentBookingId);
  });
}

function proceedWithAdminCancellation(bookingId) {
  console.log("Attempting to cancel booking with ID:", bookingId);

  if (!bookingId) {
    console.error("No booking ID provided for cancellation");
    showError("No booking ID provided");
    return;
  }

  if (
    confirm(
      "Are you sure you want to cancel this booking? This action cannot be undone."
    )
  ) {
    console.log("Sending cancellation request for booking ID:", bookingId);
    $.ajax({
      url: "../api/bookings/cancel_booking.php",
      method: "POST",
      data: { booking_id: bookingId },
      dataType: "json",
      success: function (data) {
        console.log("Cancellation response:", data);
        if (data.success) {
          showSuccess("Booking successfully cancelled");
          hideModal("viewBookingModal");
          location.reload();
        } else {
          showError("Error: " + data.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Cancellation error:", error);
        console.error("Status:", status);
        console.error("Response:", xhr.responseText);
        showError("An error occurred while cancelling the booking");
      },
    });
  }
}

function loadBookingDetails(bookingId) {
  $.ajax({
    url: "../api/bookings/get_booking_details.php",
    method: "POST",
    data: { booking_id: bookingId },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        populateEditForm(response.data);
      } else {
        showError(response.message || "Error loading booking details");
        $("#editBookingModal").modal("hide");
      }
    },
    error: function () {
      showError("Error loading booking details");
      $("#editBookingModal").modal("hide");
    },
  });
}

function saveBookingChanges() {
  const bookingId = $("#edit_booking_id").val();
  const specialRequests = $("#edit_special_requests").val();
  const bookingStatus = $("#edit_booking_status").val();
  const paymentStatus = $("#edit_payment_status").val();

  // Basic validation
  if (!bookingId) {
    showError("Missing booking ID");
    return;
  }

  // Prepare data for sending
  const formData = {
    booking_id: bookingId,
    special_requests: specialRequests,
    booking_status: bookingStatus,
    payment_status: paymentStatus
  };

  // Send update request
  $.ajax({
    url: "../api/bookings/update_booking.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function(response) {
      if (response.success) {
        showSuccess("Booking updated successfully");
        hideModal("editBookingModal");
        location.reload();
      } else {
        showError(response.message || "Error updating booking");
      }
    },
    error: function() {
      showError("Error connecting to the server");
    }
  });
}
// Function to calculate price difference when dates change
function calculatePriceDifference() {
  const checkIn = new Date($("#edit_check_in").val());
  const checkOut = new Date($("#edit_check_out").val());
  const originalCheckIn = new Date($("#original_check_in").val());
  const originalCheckOut = new Date($("#original_check_out").val());
  const roomBasePrice = parseFloat($("#room_base_price").val());
  const originalTotal = parseFloat($("#original_total").val());

  // Only calculate if dates are valid and have changed
  if (
    isNaN(checkIn.getTime()) || 
    isNaN(checkOut.getTime()) || 
    checkIn >= checkOut ||
    (checkIn.getTime() === originalCheckIn.getTime() && 
     checkOut.getTime() === originalCheckOut.getTime())
  ) {
    $("#extensionCalculation, #additionalPaymentSection").hide();
    return;
  }

  // Calculate nights for both date ranges
  const originalNights = Math.floor((originalCheckOut - originalCheckIn) / (1000 * 60 * 60 * 24));
  const newNights = Math.floor((checkOut - checkIn) / (1000 * 60 * 60 * 24));
  const nightDifference = newNights - originalNights;

  // Calculate price difference
  const priceDifference = nightDifference * roomBasePrice;

  // Update UI to show calculation
  if (nightDifference !== 0) {
    $("#extensionCalculation").show();
    $("#nightDifference").text(nightDifference > 0 ? `+${nightDifference}` : nightDifference);
    $("#priceDifference").text(`₱${priceDifference.toFixed(2)}`);
    $("#additionalPaymentSection").toggle(nightDifference > 0);
    $("#additional_payment").val(priceDifference > 0 ? priceDifference.toFixed(2) : 0);
  } else {
    $("#extensionCalculation, #additionalPaymentSection").hide();
  }
}

// Add date change event handlers
function initializeDateChangeHandlers() {
  $("#edit_check_in, #edit_check_out").on("change", calculatePriceDifference);
}

