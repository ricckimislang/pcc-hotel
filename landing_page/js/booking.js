document.addEventListener("DOMContentLoaded", function () {
  const bookingForm = document.getElementById("bookingForm");

  bookingForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(bookingForm);

    $.ajax({
      type: "POST",
      url: "../api/add_booking.php",
      data: formData,
      processData: false,
      contentType: false,
      success: function (data) {
        if (data.status) {
          Swal.fire({
            title: "Booking Successful!",
            text: "Please pay your reservation immediately or it will be canceled in two hours. View your booking and payment details for confirmation.",
            icon: "warning",
            confirmButtonText: "OK",
          }).then((result) => {
            if (result.isConfirmed) {
              window.location.href = "../pages/index.php";
            }
          });
          bookingForm.reset();
        } else {
          Swal.fire({
            title: "Booking Failed!",
            text: data.message,
            icon: "error",
            confirmButtonText: "OK",
          });
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });
  });
});
