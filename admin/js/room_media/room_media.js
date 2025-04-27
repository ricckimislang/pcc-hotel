/**
 * Room Media Management JavaScript
 * Handles uploading and managing room card images and 360° panoramas
 */

document.addEventListener("DOMContentLoaded", function () {
  // Initialize variables
  let selectedRoom = null;
  let selectedRoom360 = null;
  let galleryImages = [];
  let panoramaFile = null;
  let panoramaViewer = null;
  let modalPanoramaViewer = null;

  // DOM Elements
  const roomSelect = document.getElementById("roomSelect");
  const roomSelect360 = document.getElementById("roomSelect360");
  const galleryImageUpload = document.getElementById("galleryImageUpload");
  const panoramaUpload = document.getElementById("panoramaUpload");
  const galleryImageUploadArea = document.getElementById(
    "galleryImageUploadArea"
  );
  const panoramaUploadArea = document.getElementById("panoramaUploadArea");
  const galleryImageProgress = document.getElementById("galleryImageProgress");
  const panoramaProgress = document.getElementById("panoramaProgress");
  const saveGalleryImagesBtn = document.getElementById("saveGalleryImagesBtn");
  const savePanoramaBtn = document.getElementById("savePanoramaBtn");
  const galleryImagesContainer = document.getElementById(
    "galleryImagesContainer"
  );
  const galleryPreview = document.getElementById("galleryPreview");
  const imageCounter = document.getElementById("imageCounter");
  const panoramaContainer = document.getElementById("panoramaContainer");
  const panoramaPreview = document.getElementById("panoramaPreview");
  const roomMediaTable = document.getElementById("roomMediaTable");
  const modalPanoramaView = document.getElementById("modalPanoramaView");
  const galleryImagesModal = document.getElementById("galleryImagesModal");
  const noGalleryImages = document.getElementById("noGalleryImages");

  if (!modalPanoramaView || !galleryImagesModal || !noGalleryImages) {
    console.error("Required modal elements not found in DOM.");
    alert("Media modal structure is incomplete.");
    return;
  }

  // Initialize DataTable
  const mediaTable = $(roomMediaTable).DataTable({
    responsive: true,
    ajax: {
      url: "../api/room_media/get_all_room_media.php",
      dataSrc: "",
    },
    columns: [
      { data: "room_type" },
      {
        data: "gallery_images",
        render: function (data) {
          if (data && data.length > 0) {
            return `<span class="gallery-badge">${data.length} Images</span>`;
          } else {
            return '<span class="no-image-badge">No Images</span>';
          }
        },
      },
      {
        data: "panorama_image",
        render: function (data) {
          if (data) {
            return '<span class="panorama-badge">360° Available</span>';
          } else {
            return '<span class="no-image-badge">No 360° Image</span>';
          }
        },
      },
      { data: "last_updated" },
      {
        data: null,
        render: function (data, type, row) {
          return `
                        <button class="btn btn-sm btn-info view-media" data-id="${row.room_type_id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-media" data-id="${row.room_type_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
        },
      },
    ],
  });

  // Load rooms for dropdown
  function loadRooms() {
    fetch("../api/room_media/get_all_room_media.php")
      .then((response) => response.json())
      .then((data) => {
        // Populate both room select dropdowns
        [roomSelect, roomSelect360].forEach((select) => {
          // Clear existing options except the first one
          while (select.options.length > 1) {
            select.remove(1);
          }

          // Add room options
          data.forEach((room) => {
            const option = document.createElement("option");
            option.value = room.room_type_id;
            option.textContent = `Room ${room.room_type}`;
            select.appendChild(option);
          });
        });
      })
      .catch((error) => {
        console.error("Error loading rooms:", error);
        alert("Failed to load rooms. Please refresh the page and try again.");
      });
  }

  // Initialize room selects
  loadRooms();

  // Room select change event for gallery images
  roomSelect.addEventListener("change", function () {
    selectedRoom = this.value;
    galleryImages = [];

    if (selectedRoom) {
      // Fetch existing gallery images if any
      fetch(`../api/room_media/get_room_media.php?room_id=${selectedRoom}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.gallery_images && data.gallery_images.length > 0) {
            // Display existing gallery images
            displayGalleryImages(data.gallery_images);
            galleryImagesContainer.classList.remove("d-none");
          } else {
            galleryImagesContainer.classList.add("d-none");
            galleryPreview.innerHTML = "";
            updateImageCounter(0);
          }

          // Reset file input and progress bar
          galleryImageUpload.value = "";
          galleryImageProgress.classList.add("d-none");
          saveGalleryImagesBtn.classList.add("d-none");
        })
        .catch((error) => {
          console.error("Error fetching room media:", error);
        });
    }
  });

  // Function to display gallery images
  function displayGalleryImages(images) {
    galleryPreview.innerHTML = "";
    galleryImages = Array.isArray(images) ? images : [];

    galleryImages.forEach((image, index) => {
      const imageCol = document.createElement("div");
      imageCol.className = "col-md-4 mb-2";

      const imageCard = document.createElement("div");
      imageCard.className = "card h-100";

      const img = document.createElement("img");
      // Check if it's a new image (object with preview) or existing image (string)
      if (image.isNew && image.preview) {
        img.src = image.preview; // Use the preview data URL for new images
      } else {
        img.src = `../../public/room_images_details/${image}`; // Use path for existing images
      }
      img.className = "card-img-top";
      img.alt = "Gallery Image";

      const cardBody = document.createElement("div");
      cardBody.className = "card-body p-2";

      const removeBtn = document.createElement("button");
      removeBtn.className = "btn btn-sm btn-danger w-100";
      removeBtn.innerHTML = '<i class="fas fa-trash"></i> Remove';
      removeBtn.onclick = function () {
        galleryImages.splice(index, 1);
        displayGalleryImages(galleryImages);
      };

      cardBody.appendChild(removeBtn);
      imageCard.appendChild(img);
      imageCard.appendChild(cardBody);
      imageCol.appendChild(imageCard);
      galleryPreview.appendChild(imageCol);
    });

    updateImageCounter(galleryImages.length);
  }

  // Update image counter
  function updateImageCounter(count) {
    imageCounter.textContent = count;

    // Disable upload if we already have 3 images
    if (count >= 3) {
      galleryImageUploadArea.classList.add("disabled");
    } else {
      galleryImageUploadArea.classList.remove("disabled");
    }
  }

  // Room select change event for 360° image
  roomSelect360.addEventListener("change", function () {
    selectedRoom360 = this.value;
    if (selectedRoom360) {
      // Fetch existing 360° image if any
      fetch(`../api/room_media/get_room_media.php?room_id=${selectedRoom360}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.panorama_image) {
            // Initialize or update panorama viewer
            if (panoramaViewer) {
              panoramaViewer.setPanorama(
                `../public/panoramas/${data.panorama_image}`
              );
            } else {
              initPanoramaViewer(`../public/panoramas/${data.panorama_image}`);
            }
            panoramaContainer.classList.remove("d-none");
          } else {
            if (panoramaViewer) {
              panoramaViewer.destroy();
              panoramaViewer = null;
            }
            panoramaContainer.classList.add("d-none");
          }

          // Reset file input and progress bar
          panoramaUpload.value = "";
          panoramaFile = null;
          panoramaProgress.classList.add("d-none");
          savePanoramaBtn.classList.add("d-none");
        })
        .catch((error) => {
          console.error("Error fetching room media:", error);
        });
    }
  });

  // Initialize Panorama Viewer
  function initPanoramaViewer(imageUrl) {
    panoramaViewer = new PhotoSphereViewer.Viewer({
      container: panoramaPreview,
      panorama: imageUrl,
      size: {
        width: "100%",
        height: "100%",
      },
      navbar: ["autorotate", "zoom", "fullscreen"],
      defaultZoomLvl: 0,
    });
  }

  // Gallery Image Upload Area - Click event
  galleryImageUploadArea.addEventListener("click", function () {
    // Only allow click if we don't already have 3 images
    if (galleryImages.length < 3) {
      galleryImageUpload.click();
    } else {
      alert("You can only upload a maximum of 3 images per room.");
    }
  });

  // Panorama Upload Area - Click event
  panoramaUploadArea.addEventListener("click", function () {
    panoramaUpload.click();
  });

  // Gallery Image Upload - Change event
  galleryImageUpload.addEventListener("change", function (e) {
    // Only process if we don't already have 3 images
    if (galleryImages.length >= 3) {
      alert("You can only upload a maximum of 3 images per room.");
      return;
    }

    if (this.files.length > 0) {
      const newFiles = Array.from(this.files);

      // Check if adding these files would exceed the limit
      if (galleryImages.length + newFiles.length > 3) {
        alert(
          `You can only have a maximum of 3 images. You can add ${3 - galleryImages.length
          } more.`
        );
        return;
      }

      // Process each file
      newFiles.forEach((file) => {
        // Validate file type and size
        if (!file.type.match("image.*")) {
          alert("Please select image files only.");
          return;
        }

        if (file.size > 5 * 1024 * 1024) {
          // 5MB
          alert("Image file size should not exceed 5MB.");
          return;
        }

        // Add file to temporary array for preview
        const reader = new FileReader();
        reader.onload = function (e) {
          // Create a temporary preview object
          const tempImage = {
            file: file,
            preview: e.target.result,
            isNew: true,
          };

          // Add to gallery images array
          galleryImages.push(tempImage);

          // Update the preview
          displayGalleryImages(galleryImages);
          galleryImagesContainer.classList.remove("d-none");
          saveGalleryImagesBtn.classList.remove("d-none");
        };
        reader.readAsDataURL(file);
      });
    }
  });

  // Panorama Upload - Change event
  panoramaUpload.addEventListener("change", function (e) {
    if (this.files.length > 0) {
      panoramaFile = this.files[0];

      // Validate file type and size
      if (!panoramaFile.type.match("image.*")) {
        alert("Please select an image file.");
        return;
      }

      if (panoramaFile.size > 30 * 1024 * 1024) {
        // 30MB
        alert("Panorama file size should not exceed 30MB.");
        return;
      }

      // Preview the panorama
      const reader = new FileReader();
      reader.onload = function (e) {
        if (panoramaViewer) {
          panoramaViewer.destroy();
        }

        panoramaViewer = new PhotoSphereViewer.Viewer({
          container: panoramaPreview,
          panorama: e.target.result,
          size: {
            width: "100%",
            height: "100%",
          },
          navbar: ["autorotate", "zoom", "fullscreen"],
          defaultZoomLvl: 0,
        });

        panoramaContainer.classList.remove("d-none");
        savePanoramaBtn.classList.remove("d-none");
      };
      reader.readAsDataURL(panoramaFile);
    }
  });

  // Handle drag and drop for gallery image uploads
  galleryImageUploadArea.addEventListener("dragover", function (e) {
    e.preventDefault();
    e.stopPropagation();

    if (galleryImages.length < 3) {
      this.classList.add("dragover");
    }
  });

  galleryImageUploadArea.addEventListener("dragleave", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove("dragover");
  });

  galleryImageUploadArea.addEventListener("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove("dragover");

    // Only process if we don't already have 3 images
    if (galleryImages.length >= 3) {
      alert("You can only upload a maximum of 3 images per room.");
      return;
    }

    if (e.dataTransfer.files.length > 0) {
      galleryImageUpload.files = e.dataTransfer.files;
      // Trigger the change event manually
      const event = new Event("change");
      galleryImageUpload.dispatchEvent(event);
    }
  });

  // Handle drag and drop for panorama uploads
  panoramaUploadArea.addEventListener("dragover", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.add("dragover");
  });

  panoramaUploadArea.addEventListener("dragleave", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove("dragover");
  });

  panoramaUploadArea.addEventListener("drop", function (e) {
    e.preventDefault();
    e.stopPropagation();
    this.classList.remove("dragover");

    if (e.dataTransfer.files.length > 0) {
      panoramaUpload.files = e.dataTransfer.files;
      // Trigger the change event manually
      const event = new Event("change");
      panoramaUpload.dispatchEvent(event);
    }
  });

  // Save Gallery Images Button
  saveGalleryImagesBtn.addEventListener("click", function () {
    if (!selectedRoom || galleryImages.length === 0) {
      alert("Please select a room and at least one image.");
      return;
    }

    const formData = new FormData();
    formData.append("room_type_id", selectedRoom);

    // Add files to formData
    let newFileCount = 0;
    galleryImages.forEach((image, index) => {
      if (image.isNew) {
        formData.append(`gallery_image_${index}`, image.file);
        newFileCount++;
      } else {
        formData.append(`existing_image_${index}`, image);
      }
    });

    // If no new files, just update the existing ones
    if (newFileCount === 0) {
      formData.append("update_only", "true");
    }

    // Show progress bar
    galleryImageProgress.classList.remove("d-none");
    const progressBar = galleryImageProgress.querySelector(".progress-bar");
    progressBar.style.width = "0%";

    // Create and configure XHR for upload with progress
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../api/room_media/upload_gallery_images.php", true);

    xhr.upload.onprogress = function (e) {
      if (e.lengthComputable) {
        const percentComplete = (e.loaded / e.total) * 100;
        progressBar.style.width = percentComplete + "%";
      }
    };

    xhr.onload = function () {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            alert("Gallery images saved successfully!");
            // Refresh the table
            mediaTable.ajax.reload();
          } else {
            alert("Error: " + response.message);
          }
        } catch (e) {
          alert("Error processing response.");
        }
      } else {
        alert("Upload failed. Please try again.");
      }

      // Hide progress bar
      setTimeout(() => {
        galleryImageProgress.classList.add("d-none");
        saveGalleryImagesBtn.classList.add("d-none");
      }, 1000);
    };

    xhr.onerror = function () {
      alert("An error occurred during the upload. Please try again.");
      galleryImageProgress.classList.add("d-none");
    };

    xhr.send(formData);
  });

  // Save 360° Image Button
  savePanoramaBtn.addEventListener("click", function () {
    if (!selectedRoom360 || !panoramaFile) {
      alert("Please select a room and a 360° image file.");
      return;
    }

    const formData = new FormData();
    formData.append("room_type_id", selectedRoom360);
    formData.append("panorama_image", panoramaFile);

    // Show progress bar
    panoramaProgress.classList.remove("d-none");
    const progressBar = panoramaProgress.querySelector(".progress-bar");
    progressBar.style.width = "0%";

    // Create and configure XHR for upload with progress
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "../api/room_media/upload_panorama.php", true);

    xhr.upload.onprogress = function (e) {
      if (e.lengthComputable) {
        const percentComplete = (e.loaded / e.total) * 100;
        progressBar.style.width = percentComplete + "%";
      }
    };

    xhr.onload = function () {
      if (xhr.status === 200) {
        try {
          const response = JSON.parse(xhr.responseText);
          if (response.success) {
            alert("360° image uploaded successfully!");
            // Refresh the table
            mediaTable.ajax.reload();
          } else {
            alert("Error: " + response.message);
          }
        } catch (e) {
          alert("Error processing response.");
        }
      } else {
        alert("Upload failed. Please try again.");
      }

      // Hide progress bar
      setTimeout(() => {
        panoramaProgress.classList.add("d-none");
        savePanoramaBtn.classList.add("d-none");
      }, 1000);
    };

    xhr.onerror = function () {
      alert("An error occurred during the upload. Please try again.");
      panoramaProgress.classList.add("d-none");
    };

    xhr.send(formData);
  });

  // View Media button click
  $(roomMediaTable).on("click", ".view-media", function () {
    const roomTypeId = $(this).data("id");

    // Fetch room media data
    fetch(`../api/room_media/get_room_media.php?room_type_id=${roomTypeId}`)
      .then((response) => response.json())
      .then((data) => {
        // Show gallery images if available
        galleryImagesModal.innerHTML = "";
        if (data.gallery_images && data.gallery_images.length > 0) {
          noGalleryImages.classList.add("d-none");

          data.gallery_images.forEach((image) => {
            const img = document.createElement("img");
            img.src = `../../public/room_images_details/${image}`;
            img.alt = "Room Gallery Image";
            img.className = "img-fluid mb-2 col-md-5";
            galleryImagesModal.appendChild(img);
          });

          document.getElementById("gallery-tab").classList.remove("disabled");
        } else {
          noGalleryImages.classList.remove("d-none");
          document.getElementById("gallery-tab").classList.add("disabled");
        }

        // Initialize 360° viewer if panorama available
        if (data.panorama_image) {
          if (typeof modalPanoramaViewer !== 'undefined' && modalPanoramaViewer) {
            modalPanoramaViewer.destroy();
          }

          try {
            modalPanoramaViewer = new PhotoSphereViewer.Viewer({
              container: modalPanoramaView,
              panorama: `../../public/panoramas/${data.panorama_image}`,
              size: {
                width: "100%",
                height: "400px",
              },
              navbar: ["autorotate", "zoom", "fullscreen"],
              defaultZoomLvl: 0,
            });
          } catch (e) {
            console.error("Failed to init panorama viewer:", e);
          }

          document.getElementById("panorama-tab").classList.remove("disabled");
        } else {
          if (modalPanoramaViewer) {
            modalPanoramaViewer.destroy();
            modalPanoramaViewer = null;
          }

          modalPanoramaView.innerHTML = ''; // ✅ important cleanup
        }

        // Show the modal
        const viewMediaModal = new bootstrap.Modal(
          document.getElementById("viewMediaModal")
        );
        viewMediaModal.show();

        const viewMediaModalEl = document.getElementById("viewMediaModal");
        if (!viewMediaModalEl.dataset.resizeAttached) {
          viewMediaModalEl.addEventListener("shown.bs.modal", function () {
            if (modalPanoramaViewer) {
              setTimeout(() => {
                modalPanoramaViewer.resize();
              }, 200);
            }
          });
          viewMediaModalEl.dataset.resizeAttached = "true";
        }

      })
      .catch((error) => {
        console.error("Error fetching room media details:", error);
        alert("Failed to load room media details.");
      });
  });

  // Delete Media button click
  $(roomMediaTable).on("click", ".delete-media", function () {
    if (confirm("Are you sure you want to delete media for this room?")) {
      const roomTypeId = $(this).data("id");

      fetch("../api/room_media/delete_room_media.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: `room_type_id=${roomTypeId}`,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert("Room media deleted successfully!");
            mediaTable.ajax.reload();
          } else {
            alert("Error: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error deleting room media:", error);
          alert("Failed to delete room media. Please try again.");
        });
    }
  });

  // Handle tab changes in the modal
  document
    .getElementById("mediaTab")
    .addEventListener("shown.bs.tab", function (e) {
      if (e.target.id === "panorama-tab" && modalPanoramaViewer) {
        // Force resize when switching to panorama tab
        setTimeout(() => {
          modalPanoramaViewer.resize();
          modalPanoramaViewer.setPanorama(modalPanoramaViewer.config.panorama);
        }, 100);
      }
    });
});
