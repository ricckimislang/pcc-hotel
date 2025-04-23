document.addEventListener("DOMContentLoaded", function () {
  const checkOutButtons = document.querySelectorAll(".check-out-btn");

  checkOutButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const bookingId = button.getAttribute("data-booking-id");

      // Show confirmation dialog
      Swal.fire({
        title: "Confirm Check-out",
        text: "Are you sure you want to check-out this guest?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, check-out",
        cancelButtonText: "Cancel",
      }).then((result) => {
        if (result.isConfirmed) {
          // Make AJAX call to update booking status
          $.ajax({
            url: "../api/bookings/update_booking_status.php",
            method: "POST",
            data: {
              booking_id: bookingId,
              status: "completed",
            },
            dataType: "json",
            success: function (response) {
              if (response.success) {
                Swal.fire({
                  title: "Success!",
                  text: "Guest checked out successfully",
                  icon: "success",
                  confirmButtonText: "OK",
                }).then(() => {
                  // Reload the page to update the table
                  location.reload();
                });
              } else {
                Swal.fire({
                  title: "Error!",
                  text: response.message || "Failed to check-out guest",
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
}); 