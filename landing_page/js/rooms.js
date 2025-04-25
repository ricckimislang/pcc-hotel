async function getRooms() {
  try {
    const response = await fetch("../api/get_rooms.php");
    const data = await response.json();
    if (data.status) {
      const roomsContainer = document.querySelector(".activities-grid");
      roomsContainer.innerHTML = "";
      data.data.forEach((room) => {
        const roomCard = document.createElement("div");
        roomCard.className = "activity-card";
        roomCard.dataset.roomId = room.room_type_id;

        // Use the room's image if available, otherwise use a default image
        const roomImg = room.image_path 
          ? room.image_path 
          : "../assets/images/luxury-twin.jpg";

        roomCard.innerHTML = `
          <div class="activity-image">
            <img src="${roomImg}" alt="${room.type_name}">
          </div>
          <div class="activity-info">
            <h3>${room.type_name}</h3>
            <div class="rating">
              <span class="stars">
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
                <i class="fas fa-star"></i>
              </span>
            </div>
            <div class="room-highlights">
              <p class="amenities"><i class="fas fa-concierge-bell"></i> ${room.amenities
                .split(",")
                .slice(0, 3)
                .join(", ")}</p>
              <p class="description"><i class="far fa-sticky-note"></i> ${room.description.substring(
                0,
                100
              )}${room.description.length > 100 ? "..." : ""}</p>
            </div>
            <div class="room-footer">
              <div class="price">
                <span class="price-label">From</span>
                <span class="price-value">₱${room.base_price}</span>
                <span class="price-unit">/night</span>
              </div>
              <button class="view-room-btn">View Details</button>
            </div>
          </div>
        `;

        // attach listener that "closes over" room.id
        roomCard.addEventListener("click", (e) => {
          // Don't navigate if clicking on the button (button has its own handler)
          if (e.target.classList.contains("view-room-btn")) {
            e.preventDefault();
            e.stopPropagation();
            onRoomClick(room.room_type_id);
          } else {
            onRoomClick(room.room_type_id);
          }
        });

        // Add a specific handler for the button
        const viewButton = roomCard.querySelector(".view-room-btn");
        if (viewButton) {
          viewButton.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();
            onRoomClick(room.room_type_id);
          });
        }

        roomsContainer.appendChild(roomCard);
      });

      // Add promo card
      const promoCard = document.createElement("div");
      promoCard.className = "activity-promo";
      promoCard.innerHTML = `
        <div class="promo-content">
          <h3>Exclusive Offers</h3>
          <p>Discover our special packages and seasonal promotions for an unforgettable stay</p>
          <button class="promo-button">
            <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      `;
      roomsContainer.appendChild(promoCard);
    } else {
      console.error("Error:", data.message);
    }
  } catch (error) {
    console.error("Error fetching rooms:", error);
  }
}

function onRoomClick(roomId) {
  // Redirect to the room details page
  window.location.href = `room_details.php?room_type_id=${roomId}`;
}

function initRooms() {
  getRooms();
}

window.onload = initRooms;
