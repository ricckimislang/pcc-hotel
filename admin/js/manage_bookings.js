$(document).ready(function () {
  // Initialize DataTable
  const table = $("#bookingsTable").DataTable({
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

  // Refresh table button
  $("#refreshTable").on("click", function () {
    table.ajax.reload();
  });

  // View booking details
  $(".view-btn").on("click", function () {
    const bookingId = $(this).data("booking-id");
    showBookingDetails(bookingId);
  });

  // Edit booking
  $(".edit-btn").on("click", function () {
    const bookingId = $(this).data("booking-id");
    showEditBookingModal(bookingId);
  });

  // Confirm booking
  $(".confirm-btn").on("click", function () {
    const bookingId = $(this).data("booking-id");
    updateBookingStatus(bookingId, "confirmed");
  });

  // Cancel booking
  $(".cancel-btn").on("click", function () {
    const bookingId = $(this).data("booking-id");
    updateBookingStatus(bookingId, "cancelled");
  });
});

function showBookingDetails(bookingId) {
  $.ajax({
    url: "../api/bookings/get_booking_details.php",
    method: "POST",
    data: { booking_id: bookingId },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        const data = response.data;

        // Populate guest information
        $(".guest-name").text(data.guest.name);
        $(".guest-email").text(data.guest.email);
        $(".guest-phone").text(data.guest.phone);

        // Populate room details
        $(".room-number").text(data.room.number);
        $(".room-type").text(data.room.type);
        $(".room-floor").text(data.room.floor);

        // Populate booking information
        $(".check-in-date").text(data.booking.check_in);
        $(".check-out-date").text(data.booking.check_out);
        $(".total-amount").text("₱" + data.booking.total_price);
        $(".booking-status").text(data.booking.status);
        $(".reservation-fee").text("₱" + data.booking.reservation_fee);
        $(".payment-status").text(data.booking.payment_status);
        $(".total-guests").text(data.booking.guests_count + " guests");

        // Show special requests if any
        if (data.booking.special_requests) {
          $(".special-requests").text(data.booking.special_requests).show();
        } else {
          $(".special-requests").hide();
        }

        // Show the modal using Bootstrap's modal method
        const viewBookingModal = new bootstrap.Modal(
          document.getElementById("viewBookingModal")
        );
        viewBookingModal.show();
      } else {
        alert(response.message || "Error loading booking details");
      }
    },
    error: function () {
      alert("Error loading booking details");
    },
  });
}

function updateBookingStatus(bookingId, status) {
  if (confirm(`Are you sure you want to ${status} this booking?`)) {
    $.ajax({
      url: "update_booking_status.php",
      method: "POST",
      data: {
        booking_id: bookingId,
        status: status,
      },
      success: function (response) {
        const result = JSON.parse(response);
        if (result.success) {
          alert(`Booking ${status} successfully!`);
          location.reload();
        } else {
          alert("Error updating booking status");
        }
      },
      error: function () {
        alert("Error updating booking status");
      },
    });
  }
}

function showEditBookingModal(bookingId) {
  $.ajax({
    url: "../api/bookings/get_booking_details.php",
    method: "POST",
    data: { booking_id: bookingId },
    dataType: "json",
    success: function (response) {
      if (response.success) {
        const data = response.data;

        // Set booking ID
        $("#edit_booking_id").val(bookingId);

        // Populate read-only guest information
        $(".edit-guest-name").text(data.guest.name);
        $(".edit-guest-email").text(data.guest.email);
        $(".edit-guest-phone").text(data.guest.phone);

        // Populate read-only room information
        $(".edit-room-number").text(data.room.number);
        $(".edit-room-type").text(data.room.type);
        $(".edit-room-floor").text(data.room.floor);

        // Populate editable fields
        $("#edit_check_in").val(data.booking.check_in.split(" ")[0]);
        $("#edit_check_out").val(data.booking.check_out.split(" ")[0]);
        $("#edit_guests_count").val(data.booking.guests_count);
        $("#edit_special_requests").val(data.booking.special_requests);
        $("#edit_booking_status").val(data.booking.status);
        $("#edit_payment_status").val(data.booking.payment_status);

        // Show the modal
        const editBookingModal = new bootstrap.Modal(
          document.getElementById("editBookingModal")
        );
        editBookingModal.show();
      } else {
        alert(response.message || "Error loading booking details");
      }
    },
    error: function () {
      alert("Error loading booking details");
    },
  });
}

// Save booking changes
$("#saveBookingChanges").on("click", function () {
  const formData = $("#editBookingForm").serialize();

  $.ajax({
    url: "../api/bookings/update_booking.php",
    method: "POST",
    data: formData,
    dataType: "json",
    success: function (response) {
      if (response.success) {
        alert("Booking updated successfully!");
        // Close the modal
        const editBookingModal = bootstrap.Modal.getInstance(
          document.getElementById("editBookingModal")
        );
        editBookingModal.hide();
        // Refresh the table
        table.ajax.reload();
      } else {
        alert(response.message || "Error updating booking");
      }
    },
    error: function () {
      alert("Error updating booking");
    },
  });
});
