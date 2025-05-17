document.addEventListener("DOMContentLoaded", function () {
  // Use delegated event handling for check-in buttons
  $(document).on("click", ".check-in-btn", function() {
    // Skip processing if button is disabled
    if (this.disabled) {
      return;
    }
    
    const bookingId = $(this).data("booking-id");

    // Show confirmation dialog
    Swal.fire({
      title: "Confirm Check-in",
      text: "Are you sure you want to check-in this guest?",
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "Yes, check-in",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        // Make AJAX call to update booking status
        $.ajax({
          url: "../api/bookings/update_booking_status.php",
          method: "POST",
          data: {
            booking_id: bookingId,
            status: "checked_in",
          },
          dataType: "json",
          success: function (response) {
            if (response.success) {
              Swal.fire({
                title: "Success!",
                text: "Guest checked in successfully",
                icon: "success",
                confirmButtonText: "OK",
              }).then(() => {
                // Reload the page to update the table
                location.reload();
              });
            } else {
              Swal.fire({
                title: "Error!",
                text: response.message || "Failed to check-in guest",
                icon: "error",
                confirmButtonText: "OK",
              });
            }
          },
          error: function () {
            Swal.fire({
              title: "Error!",
              text: "An error occurred while processing your request",
              icon: "error",
              confirmButtonText: "OK",
            });
          },
        });
      }
    });
  });
});
