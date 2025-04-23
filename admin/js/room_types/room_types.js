let roomTypeTable = null;

function initRoomTypeTable() {
  if ($.fn.DataTable.isDataTable("#roomTypeTable")) {
    $("#roomTypeTable").DataTable().destroy();
    $("#roomTypeTable tbody").empty();
  }

  roomTypeTable = $("#roomTypeTable").DataTable({
    processing: true,
    ajax: {
      url: "../api/room_types/get_room_type.php",
      dataSrc: function (json) {
        console.log("Received JSON:", json);

        if (!json) {
          console.error("Invalid API response", json);
          return [];
        }

        try {
          if (!Array.isArray(json.room_types)) {
            console.error("Room Types data is not an array", json.room_types);
            return [];
          }
          return json.room_types;
        } catch (e) {
          console.error("Error during post-processing:", e);
          return [];
        }
      },
      error: function (xhr, error, thrown) {
        if (error === "abort" || thrown === "abort") return;

        if (xhr.status === 0) {
          console.error("Network error");
          alert("Unable to connect to server. Check your internet connection.");
        } else {
          console.error("AJAX Error", {
            status: xhr.status,
            responseText: xhr.responseText,
            error,
            thrown,
          });
          alert("Error loading data. Check the console.");
        }

        return [];
      },
    },
    responsive: true,
    dom: "Bfrtlip",
    buttons: [
      {
        extend: "print",
        className: "btn btn-primary",
        exportOptions: { columns: ":not(:last-child)" },
      },
      {
        extend: "excel",
        className: "btn btn-primary",
        exportOptions: { columns: ":not(:last-child)" },
      },
    ],
    language: {
      processing: '<i class="fas fa-spinner fa-spin"></i> Loading...',
      emptyTable: "No room types data available",
      zeroRecords: "No matching room types found",
      info: "Showing _START_ to _END_ of _TOTAL_ room types",
      infoEmpty: "Showing 0 room types",
      infoFiltered: "(filtered from _MAX_ total room types)",
    },
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    columns: [
      { data: "type_name" },
      { data: "base_price" },
      { data: "capacity" },
      { data: "description", className: "col-description" },
      { data: "amenities", className: "col-amenities" },
      {
        data: null,
        render: function (data, type, row) {
          return `
          <div class="btn-group" role="group"></div>
            <button type="button" class="btn btn-primary view-btn" data-id="${data.id}">View</button>
            <button type="button" class="btn btn-warning edit-btn" data-id="${data.id}">Edit</button>
            <button type="button" class="btn btn-danger delete-btn" data-id="${data.id}">Delete</button>
          </div>
        `;
        },
      },
    ],
    drawCallback: function () {
      console.log("Table draw complete.");
    },
    initComplete: function (settings, json) {
      console.log("Table init complete", json);
      if (!Array.isArray(json?.room_types) || json.room_types.length === 0) {
        console.warn("No room types received on init.");
      } else {
        console.log(`✅ ${json.room_types.length} room(s) received on init.`);
      }
    },
  });
}

// Trigger once DOM is fully ready
$(function () {
  initRoomTypeTable();

  // Handle Add Room Type form submission
  $("#addRoomTypeForm").on("submit", function (e) {
    e.preventDefault();

    // Form validation
    const typeName = $("#type_name").val().trim();
    const basePrice = parseFloat($("#base_price").val());
    const capacity = parseInt($("#capacity").val());

    if (!typeName) {
      alert("Please enter a room type name");
      return false;
    }

    if (isNaN(basePrice) || basePrice <= 0) {
      alert("Please enter a valid base price");
      return false;
    }

    if (isNaN(capacity) || capacity <= 0) {
      alert("Please enter a valid capacity");
      return false;
    }

    // Create FormData object to send form data
    const formData = new FormData(this);

    $.ajax({
      url: "../api/room_types/add_room_type.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status) {
          // Show success message
          alert("Room type added successfully!");

          // Close the modal
          $("#addRoomTypeModal").modal("hide");

          // Reset the form
          $("#addRoomTypeForm")[0].reset();

          // Reload the DataTable
          roomTypeTable.ajax.reload();
        } else {
          alert("Error: " + (response.message || "Failed to add room type"));
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", xhr.responseText);
        alert("Error adding room type. Please try again.");
      },
    });
  });
});
