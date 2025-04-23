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
        roomCard.innerHTML = `
                    <div class="activity-image">
                        <img src="../assets/images/luxury-twin.jpg" alt="Luxury Twin Matress">
                    </div>
                    <div class="activity-info">
                        <h3>${room.type_name}</h3>
                        <p>Good for: ${room.capacity} people</p>
                        <span class="description">
                            <p><i class="far fa-sticky-note"></i>${room.description}</p>
                            <p>Amenities: ${room.amenities}</p>
                        </span>
                         <div class="rating">
                            <span class="stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </span>
                        </div>
                         <div class="price"><p>Price: ₱${room.base_price}/night</p></div>
                    </div>
        `;

        // attach listener that “closes over” room.id
        roomCard.addEventListener("click", () => {
          onRoomClick(room.room_type_id);
        });

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
