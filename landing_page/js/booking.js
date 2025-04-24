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
            text: "Please view your booking and payment details for confirmation",
            icon: "success",
            confirmButtonText: "OK",
          });
          bookingForm.reset();
          setTimeout(() => {
            window.location.href = "../pages/index.php";
          }, 2000);
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
