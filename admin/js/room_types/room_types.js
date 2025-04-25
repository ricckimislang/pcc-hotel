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
    autoWidth: false,
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
      { data: "type_name", width: "10%" },
      { data: "base_price", width: "5%" },
      { data: "capacity", width: "5%" },
      {
        data: "floor_type",
        width: "5%",
        render: function (data, type, row) {
          if (data == 1) return "Ground Floor";
          if (data == 2) return "Second Floor";
          if (data == 3) return "Function Hall";
          return data; // Return original value if it doesn't match conditions
        },
      },
      { 
        data: "description", 
        className: "col-description", 
        width: "10%",
        render: function(data, type) {
          if (type === 'display' && data) {
            return data.length > 100 ? data.substr(0, 100) + '...' : data;
          }
          return data;
        }
      },
      { 
        data: "amenities", 
        className: "col-amenities", 
        width: "10%",
        render: function(data, type) {
          if (type === 'display' && data) {
            return data.length > 80 ? data.substr(0, 80) + '...' : data;
          }
          return data;
        }
      },
      {
        data: null,
        width: "10%",
        className: "text-center",
        render: function (data, type, row) {
          return `
          <div class="btn-group" role="group"></div>
            <button type="button" class="btn btn-sm btn-primary view-btn" data-id="${data.id}">View</button>
            <button type="button" class="btn btn-sm btn-warning edit-btn" data-id="${data.id}">Edit</button>
            <button type="button" class="btn btn-sm btn-danger delete-btn" data-id="${data.id}">Delete</button>
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

  // Handle View Room Type button clicks
  $(document).on("click", ".view-btn", function () {
    const roomTypeId = $(this).data("id");

    // Fetch room type details
    $.ajax({
      url: "../api/room_types/get_room_type_by_id.php",
      type: "GET",
      data: { id: roomTypeId },
      dataType: "json",
      success: function (response) {
        if (response.status && response.room_type) {
          const roomType = response.room_type;

          // Populate modal with room type details
          $("#view_type_name").text(roomType.type_name);
          $("#view_base_price").text(
            parseFloat(roomType.base_price).toLocaleString("en-PH", {
              style: "currency",
              currency: "PHP",
            })
          );
          $("#view_capacity").text(roomType.capacity);

          // Format description with fallback
          if (roomType.description && roomType.description.trim().length > 0) {
            $("#view_description").html(
              roomType.description.replace(/\n/g, "<br>")
            );
          } else {
            $("#view_description").html(
              '<em class="text-muted">No description available</em>'
            );
          }

          // Handle amenities with better formatting
          if (roomType.amenities && roomType.amenities.trim().length > 0) {
            const amenitiesArray = roomType.amenities
              .split(",")
              .map((item) => item.trim());
            let amenitiesHTML = '<div class="d-flex flex-wrap gap-2">';

            // Map of common amenities to icons
            const amenityIcons = {
              wifi: "fa-wifi",
              tv: "fa-tv",
              bathroom: "fa-bath",
              shower: "fa-shower",
              air: "fa-wind",
              conditioning: "fa-wind",
              refrigerator: "fa-refrigerator",
              fridge: "fa-refrigerator",
              kitchen: "fa-utensils",
              breakfast: "fa-coffee",
              parking: "fa-parking",
              pool: "fa-swimming-pool",
              spa: "fa-spa",
              gym: "fa-dumbbell",
              fitness: "fa-dumbbell",
              balcony: "fa-door-open",
              view: "fa-mountain",
              minibar: "fa-glass-martini",
              safe: "fa-vault",
              desk: "fa-desk",
              phone: "fa-phone",
              hairdryer: "fa-wind",
            };

            amenitiesArray.forEach((amenity) => {
              // Determine icon based on amenity text
              let iconClass = "fa-check-circle";

              // Check if any keywords in the amenity match our icon map
              for (const [keyword, icon] of Object.entries(amenityIcons)) {
                if (amenity.toLowerCase().includes(keyword)) {
                  iconClass = icon;
                  break;
                }
              }

              amenitiesHTML += `
                <div class="amenity-badge px-3 py-2 bg-light rounded d-flex align-items-center">
                  <i class="fas ${iconClass} text-primary me-2"></i>
                  <span>${amenity}</span>
                </div>
              `;
            });

            amenitiesHTML += "</div>";
            $("#view_amenities").html(amenitiesHTML);
          } else {
            $("#view_amenities").html(
              '<em class="text-muted">No amenities listed</em>'
            );
          }

          // Show the modal
          $("#viewRoomTypeModal").modal("show");
        } else {
          alert(
            "Error: " +
              (response.message || "Failed to retrieve room type details")
          );
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", xhr.responseText);
        alert("Error retrieving room type details. Please try again.");
      },
    });
  });

  // Handle Edit Room Type button clicks
  $(document).on("click", ".edit-btn", function () {
    const roomTypeId = $(this).data("id");

    // Show loading indicator
    $("#editRoomTypeModal")
      .find(".modal-content")
      .append(
        '<div class="position-absolute w-100 h-100 d-flex justify-content-center align-items-center bg-white bg-opacity-75" id="loadingOverlay"><div class="text-center"><i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i><p>Loading room details...</p></div></div>'
      );

    // Show the modal with loading state
    $("#editRoomTypeModal").modal("show");

    // Fetch room type details
    $.ajax({
      url: "../api/room_types/get_room_type_by_id.php",
      type: "GET",
      data: { id: roomTypeId },
      dataType: "json",
      success: function (response) {
        // Remove loading overlay
        $("#loadingOverlay").remove();

        if (response.status && response.room_type) {
          const roomType = response.room_type;

          // Populate form with room type details
          $("#edit_room_type_id").val(roomType.id);
          $("#edit_type_name").val(roomType.type_name);
          $("#edit_base_price").val(roomType.base_price);
          $("#edit_capacity").val(roomType.capacity);
          $("#edit_description").val(roomType.description);
          $("#edit_amenities").val(roomType.amenities);
        } else {
          $("#editRoomTypeModal").modal("hide");
          alert(
            "Error: " +
              (response.message || "Failed to retrieve room type details")
          );
        }
      },
      error: function (xhr, status, error) {
        // Remove loading overlay
        $("#loadingOverlay").remove();

        $("#editRoomTypeModal").modal("hide");
        console.error("AJAX Error:", xhr.responseText);
        alert("Error retrieving room type details. Please try again.");
      },
    });
  });

  // Handle Edit Room Type form submission
  $("#editRoomTypeForm").on("submit", function (e) {
    e.preventDefault();

    // Form validation
    const typeName = $("#edit_type_name").val().trim();
    const basePrice = parseFloat($("#edit_base_price").val());
    const capacity = parseInt($("#edit_capacity").val());

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
      url: "../api/room_types/update_room_type.php",
      type: "POST",
      data: formData,
      processData: false,
      contentType: false,
      dataType: "json",
      success: function (response) {
        if (response.status) {
          // Show success message with standard alert
          alert("Room type updated successfully!");

          // Close the modal
          $("#editRoomTypeModal").modal("hide");

          // Reload the DataTable
          roomTypeTable.ajax.reload();
        } else {
          alert("Error: " + (response.message || "Failed to update room type"));
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX Error:", xhr.responseText);
        alert("Error updating room type. Please try again.");
      },
    });
  });

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
