async function getRooms() {
  try {
    // Fetch both rooms and ratings data
    const [roomsResponse, ratingsResponse] = await Promise.all([
      fetch("../api/get_rooms.php"),
      fetch("../api/get_room_ratings.php")
    ]);

    const roomsData = await roomsResponse.json();
    const ratingsData = await ratingsResponse.json();

    if (roomsData.status) {
      const roomsContainer = document.querySelector(".activities-grid");
      roomsContainer.innerHTML = "";

      // Create a map of room type ratings for easy lookup
      const ratingsMap = {};
      if (ratingsData.status) {
        ratingsData.data.forEach(rating => {
          ratingsMap[rating.room_type_id] = {
            average: rating.average_rating,
            total: rating.total_ratings
          };
        });
      }

      roomsData.data.forEach((room) => {
        const roomCard = document.createElement("div");
        roomCard.className = "activity-card";
        roomCard.dataset.roomId = room.room_type_id;

        // Get rating info for this room type
        const rating = ratingsMap[room.room_type_id] || { average: 0, total: 0 };
        const stars = generateStarRating(rating.average);

        // Use the room's image if available, otherwise use a default image
        const roomImg = room.image_path 
          ? room.image_path 
          : "../assets/images/luxury-twin.jpg";

        roomCard.innerHTML = `
          <div class="activity-image">
            <img src="../../${roomImg}" alt="${room.type_name}">
          </div>
          <div class="activity-info">
            <h3>${room.type_name}</h3>
            <div class="rating">
              <span class="stars">
                ${stars}
              </span>
              <span class="rating-count">(${rating.total} reviews)</span>
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
      console.error("Error:", roomsData.message);
    }
  } catch (error) {
    console.error("Error fetching rooms:", error);
  }
}

function generateStarRating(rating) {
  const fullStars = Math.floor(rating);
  const hasHalfStar = rating % 1 >= 0.5;
  let stars = '';

  // Add full stars
  for (let i = 0; i < fullStars; i++) {
    stars += '<i class="fas fa-star text-warning"></i>';
  }

  // Add half star if needed
  if (hasHalfStar) {
    stars += '<i class="fas fa-star-half-alt text-warning"></i>';
  }

  // Add empty stars to make total of 5
  const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
  for (let i = 0; i < emptyStars; i++) {
    stars += '<i class="far fa-star text-warning"></i>';
  }

  return stars;
}

function onRoomClick(roomId) {
  // Redirect to the room details page
  window.location.href = `room_details.php?room_type_id=${roomId}`;
}

function initRooms() {
  getRooms();
}

window.onload = initRooms;
