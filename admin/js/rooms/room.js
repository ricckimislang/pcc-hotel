let roomsTable = null;

function initRoomsTable() {
  if ($.fn.DataTable.isDataTable("#roomsTable")) {
    $("#roomsTable").DataTable().destroy();
    $("#roomsTable tbody").empty();
  }

  roomsTable = $("#roomsTable").DataTable({
    processing: true,
    ajax: {
      url: "../api/rooms/get_rooms.php",
      dataSrc: function (json) {
        console.log("Received JSON:", json);

        if (!json) {
          console.error("Invalid API response", json);
          return [];
        }

        try {
          if (!Array.isArray(json.rooms)) {
            console.error("Rooms data is not an array", json.rooms);
            return [];
          }
          loadRoomTypes(json.room_types);
          updateRoomSummary(json.rooms);
          return json.rooms;
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
      emptyTable: "No rooms data available",
      zeroRecords: "No matching rooms found",
      info: "Showing _START_ to _END_ of _TOTAL_ rooms",
      infoEmpty: "Showing 0 rooms",
      infoFiltered: "(filtered from _MAX_ total rooms)",
    },
    pageLength: 10,
    lengthMenu: [
      [10, 25, 50, -1],
      [10, 25, 50, "All"],
    ],
    columns: [
      { data: "room_number" },
      { data: "room_type" },
      { data: "floor" },
      {
        data: "status",
        render: function (data, type, row) {
          let statusClass = "";
          if (data === "available") statusClass = "success";
          else if (data === "occupied" || "reserved") statusClass = "danger";
          else if (data === "maintenance") statusClass = "warning";
          return `<span class="badge bg-${statusClass}">${data}</span>`;
        },
      },
      { data: "price" },
      { data: "capacity" },
      {
        data: null,
        render: function (data, type, row) {
          return `
          <div class="btn-group" role="group"></div>
            <!-- <button type="button" class="btn btn-primary view-btn" data-id="${data.id}">View</button> -->
            <button type="button" class="btn btn-warning edit-btn" data-id="${data.id}" onClick="openEditModal(${data.id})">Edit</button>
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
      if (!Array.isArray(json?.rooms) || json.rooms.length === 0) {
        console.warn("No Rooms received on init.");
      } else {
        console.log(`✅ ${json.rooms.length} room(s) received on init.`);
      }
    },
  });
}

// load room types
function loadRoomTypes(roomTypes) {
  const roomTypeSelect = $("#roomTypeFilter");
  const addRoomTypeSelect = $("#room_type_id");
  const editRoomTypeSelect = $("#edit_room_type_id");

  roomTypeSelect.empty();
  addRoomTypeSelect.empty();
  editRoomTypeSelect.empty();

  roomTypeSelect.append('<option value="">All</option>');
  addRoomTypeSelect.append('<option value="">Select Room Type</option>');
  editRoomTypeSelect.append('<option value="">Select Room Type</option>');

  roomTypes.forEach((roomType) => {
    roomTypeSelect.append(
      `<option value="${roomType.name}">${roomType.name}</option>`
    );
    addRoomTypeSelect.append(
      `<option value="${roomType.id}">${roomType.name}</option>`
    );
    editRoomTypeSelect.append(
      `<option value="${roomType.id}">${roomType.name}</option>`
    );
  });
}

// apply filters
$("#roomTypeFilter").change(function () {
  const roomType = $(this).val();

  $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
    const type = roomsTable.row(dataIndex).data();
    if (!type) return false;
    const typeMatch = !roomType || type.room_type === roomType;

    return typeMatch;
  });

  roomsTable.draw();
  $.fn.dataTable.ext.search.pop();
});

// Trigger once DOM is fully ready
$(function () {
  initRoomsTable();
});

// open edit modal
function openEditModal(roomId) {
  const editModal = $("#editRoomModal");
  $("#edit_room_id").val(roomId);
  editModal.modal("show");

  fetch(`../api/rooms/get_edit_room_data.php?id=${roomId}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.status) {
        const room = data.roomData[0];
        $("#edit_room_number").val(room.room_number);
        $("#edit_room_type_id").val(room.room_type_id);
        $("#edit_floor").val(room.floor);
        $("#edit_status").val(room.status);
        $("#edit_description").val(room.description);
      } else {
        alert("Failed to fetch room data for editing");
      }
    })
    .catch((e) => {
      console.error("Error during post-processing:", e);
    });
}

// EVENT LISTENERS
document.addEventListener("DOMContentLoaded", function () {
  // add of room
  $("#addRoomForm").submit(function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    $.ajax({
      url: "../api/rooms/add_room.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.status) {
          alert("Room added successfully");
          $("#addRoomModal").modal("hide");
          $("#addRoomForm")[0].reset();
          roomsTable.ajax.reload(null, false);
        } else {
          alert("Room not added");
        }
      },
      error: function () {
        alert("Error adding room");
      },
    });
  });

  // edit of room
  $("#editRoomForm").submit(function (e) {
    e.preventDefault();
    const formData = new FormData(this);
    $.ajax({
      url: "../api/rooms/update_room.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.status) {
          alert("Room updated successfully");
          $("#editRoomModal").modal("hide");
          $("#editRoomForm")[0].reset();
          roomsTable.ajax.reload(null, false);
        } else {
          alert("Room not updated");
        }
      },
      error: function () {
        alert("Error updating room");
      },
    });
  });
});

// Function to update room summary statistics
function updateRoomSummary(roomsData) {
  if (!Array.isArray(roomsData) || roomsData.length === 0) {
    return;
  }

  // Count total rooms
  const totalRooms = roomsData.length;

  // Count rooms by status
  const statusCounts = roomsData.reduce((counts, room) => {
    const status = room.status ? room.status.toLowerCase() : "unknown";
    counts[status] = (counts[status] || 0) + 1;
    return counts;
  }, {});

  // Update the summary cards
  document.getElementById("totalRooms").textContent = totalRooms;
  document.getElementById("availableRooms").textContent =
    statusCounts.available || 0;
  document.getElementById("occupiedRooms").textContent =
    (statusCounts.occupied || 0) + (statusCounts.reserved || 0);
}

// get the room detailed if occupied

document.addEventListener("click", function (e) {
  if (e.target.classList.contains("view-btn")) {
    const roomId = e.target.dataset.id;
    fetch(`../api/rooms/view_room_details.php?id=${roomId}`)
      .then((response) => response.json())
      .then((data) => {
        if (data.status) {
          const details = data.details;
          $("#roomNumber").text(details.room_number);
          $("#roomType").text(details.room_type);
          $("#floor").text(details.floor);
          $("#status").text(details.status);
          $("#guestName").val(details.fullname);
          $("#guestContact").val(details.bookingDate);
          $("#checkInDate").val(details.check_in_date);
          $("#checkOutDate").val(details.check_out_date);
          $("#viewGuestModal").modal("show");
        } else {
          alert("No guest information found");
        }
      })
      .catch((error) => console.error("Error:", error));
  }
});
