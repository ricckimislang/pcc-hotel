document.addEventListener("DOMContentLoaded", function () {
  const bookingForm = document.getElementById("bookingForm");

  bookingForm.addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(bookingForm);

    $.ajax({
      type: "POST",
      url: "api/add_booking.php",
      data: formData,
      processData: false,
      contentType: false,
      success: function (data) {
        if (data.status) {
          alert(data.message);
          bookingForm.reset();
        } else {
          alert(data.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("Error:", error);
      },
    });
  });
});

async function checkRoomAvailability(room_id, check_in_date, check_out_date) {
  // Get tomorrow's date
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  tomorrow.setHours(0,0,0,0);

  // Validate dates are not in the past
  const checkIn = new Date(check_in_date);
  const checkOut = new Date(check_out_date);

  if (checkIn < tomorrow || checkOut < tomorrow) {
    return {
      status: 'error',
      message: 'Please select dates starting from tomorrow onwards'
    };
  }

  try {
    const response = await $.ajax({
      url: "api/check_availability.php", 
      type: "POST",
      data: {
        room_id: room_id,
        check_in_date: check_in_date,
        check_out_date: check_out_date
      }
    });
    return response;
  } catch (error) {
    console.error("Error checking availability:", error);
    return {
      status: 'error',
      message: 'Failed to check room availability'
    };
  }
}
