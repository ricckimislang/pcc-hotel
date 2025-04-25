/**
 * Room Media Management JavaScript
 * Handles uploading and managing room card images and 360° panoramas
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let selectedRoom = null;
    let selectedRoom360 = null;
    let cardImageFile = null;
    let panoramaFile = null;
    let panoramaViewer = null;
    let modalPanoramaViewer = null;
    
    // DOM Elements
    const roomSelect = document.getElementById('roomSelect');
    const roomSelect360 = document.getElementById('roomSelect360');
    const cardImageUpload = document.getElementById('cardImageUpload');
    const panoramaUpload = document.getElementById('panoramaUpload');
    const cardImageUploadArea = document.getElementById('cardImageUploadArea');
    const panoramaUploadArea = document.getElementById('panoramaUploadArea');
    const cardImageProgress = document.getElementById('cardImageProgress');
    const panoramaProgress = document.getElementById('panoramaProgress');
    const saveCardImageBtn = document.getElementById('saveCardImageBtn');
    const savePanoramaBtn = document.getElementById('savePanoramaBtn');
    const cardImageContainer = document.getElementById('cardImageContainer');
    const currentCardImage = document.getElementById('currentCardImage');
    const panoramaContainer = document.getElementById('panoramaContainer');
    const panoramaPreview = document.getElementById('panoramaPreview');
    const roomMediaTable = document.getElementById('roomMediaTable');
    
    // Initialize DataTable
    const mediaTable = $(roomMediaTable).DataTable({
        responsive: true,
        ajax: {
            url: '../api/room_media/get_all_room_media.php',
            dataSrc: ''
        },
        columns: [
            { data: 'room_number' },
            { data: 'room_type' },
            { 
                data: 'card_image',
                render: function(data) {
                    if (data) {
                        return `<img src="../uploads/room_images/${data}" class="thumbnail" alt="Card Image">`;
                    } else {
                        return '<span class="no-image-badge">No Image</span>';
                    }
                }
            },
            { 
                data: 'panorama_image',
                render: function(data) {
                    if (data) {
                        return '<span class="panorama-badge">360° Available</span>';
                    } else {
                        return '<span class="no-image-badge">No 360° Image</span>';
                    }
                }
            },
            { data: 'last_updated' },
            { 
                data: null,
                render: function (data, type, row) {
                    return `
                        <button class="btn btn-sm btn-info view-media" data-id="${row.room_id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-media" data-id="${row.room_id}">
                            <i class="fas fa-trash"></i>
                        </button>
                    `;
                }
            }
        ]
    });
    
    // Load rooms for dropdown
    function loadRooms() {
        fetch('../api/room_media/get_all_room_media.php')
            .then(response => response.json())
            .then(data => {
                // Populate both room select dropdowns
                [roomSelect, roomSelect360].forEach(select => {
                    // Clear existing options except the first one
                    while (select.options.length > 1) {
                        select.remove(1);
                    }
                    
                    // Add room options
                    data.forEach(room => {
                        const option = document.createElement('option');
                        option.value = room.room_id;
                        option.textContent = `Room ${room.room_number} (${room.room_type})`;
                        select.appendChild(option);
                    });
                });
            })
            .catch(error => {
                console.error('Error loading rooms:', error);
                alert('Failed to load rooms. Please refresh the page and try again.');
            });
    }
    
    // Initialize room selects
    loadRooms();
    
    // Room select change event for card image
    roomSelect.addEventListener('change', function() {
        selectedRoom = this.value;
        if (selectedRoom) {
            // Fetch existing card image if any
            fetch(`../api/room_media/get_room_media.php?room_id=${selectedRoom}`)
                .then(response => response.json())
                .then(data => {
                    if (data.card_image) {
                        currentCardImage.src = `../uploads/room_images/${data.card_image}`;
                        cardImageContainer.classList.remove('d-none');
                    } else {
                        cardImageContainer.classList.add('d-none');
                    }
                    
                    // Reset file input and progress bar
                    cardImageUpload.value = '';
                    cardImageFile = null;
                    cardImageProgress.classList.add('d-none');
                    saveCardImageBtn.classList.add('d-none');
                })
                .catch(error => {
                    console.error('Error fetching room media:', error);
                });
        }
    });
    
    // Room select change event for 360° image
    roomSelect360.addEventListener('change', function() {
        selectedRoom360 = this.value;
        if (selectedRoom360) {
            // Fetch existing 360° image if any
            fetch(`../api/room_media/get_room_media.php?room_id=${selectedRoom360}`)
                .then(response => response.json())
                .then(data => {
                    if (data.panorama_image) {
                        // Initialize or update panorama viewer
                        if (panoramaViewer) {
                            panoramaViewer.setPanorama(`../public/panoramas/${data.panorama_image}`);
                        } else {
                            initPanoramaViewer(`../public/panoramas/${data.panorama_image}`);
                        }
                        panoramaContainer.classList.remove('d-none');
                    } else {
                        if (panoramaViewer) {
                            panoramaViewer.destroy();
                            panoramaViewer = null;
                        }
                        panoramaContainer.classList.add('d-none');
                    }
                    
                    // Reset file input and progress bar
                    panoramaUpload.value = '';
                    panoramaFile = null;
                    panoramaProgress.classList.add('d-none');
                    savePanoramaBtn.classList.add('d-none');
                })
                .catch(error => {
                    console.error('Error fetching room media:', error);
                });
        }
    });
    
    // Initialize Panorama Viewer
    function initPanoramaViewer(imageUrl) {
        panoramaViewer = new PhotoSphereViewer.Viewer({
            container: panoramaPreview,
            panorama: imageUrl,
            size: {
                width: '100%',
                height: '100%'
            },
            navbar: ['autorotate', 'zoom', 'fullscreen'],
            defaultZoomLvl: 0
        });
    }
    
    // Card Image Upload Area - Click event
    cardImageUploadArea.addEventListener('click', function() {
        cardImageUpload.click();
    });
    
    // Panorama Upload Area - Click event
    panoramaUploadArea.addEventListener('click', function() {
        panoramaUpload.click();
    });
    
    // Card Image Upload - Change event
    cardImageUpload.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            cardImageFile = this.files[0];
            
            // Validate file type and size
            if (!cardImageFile.type.match('image.*')) {
                alert('Please select an image file.');
                return;
            }
            
            if (cardImageFile.size > 5 * 1024 * 1024) { // 5MB
                alert('Image file size should not exceed 5MB.');
                return;
            }
            
            // Preview the image
            const reader = new FileReader();
            reader.onload = function(e) {
                currentCardImage.src = e.target.result;
                cardImageContainer.classList.remove('d-none');
                saveCardImageBtn.classList.remove('d-none');
            };
            reader.readAsDataURL(cardImageFile);
        }
    });
    
    // Panorama Upload - Change event
    panoramaUpload.addEventListener('change', function(e) {
        if (this.files.length > 0) {
            panoramaFile = this.files[0];
            
            // Validate file type and size
            if (!panoramaFile.type.match('image.*')) {
                alert('Please select an image file.');
                return;
            }
            
            if (panoramaFile.size > 20 * 1024 * 1024) { // 20MB
                alert('Panorama file size should not exceed 20MB.');
                return;
            }
            
            // Preview the panorama
            const reader = new FileReader();
            reader.onload = function(e) {
                if (panoramaViewer) {
                    panoramaViewer.destroy();
                }
                
                panoramaViewer = new PhotoSphereViewer.Viewer({
                    container: panoramaPreview,
                    panorama: e.target.result,
                    size: {
                        width: '100%',
                        height: '100%'
                    },
                    navbar: ['autorotate', 'zoom', 'fullscreen'],
                    defaultZoomLvl: 0
                });
                
                panoramaContainer.classList.remove('d-none');
                savePanoramaBtn.classList.remove('d-none');
            };
            reader.readAsDataURL(panoramaFile);
        }
    });
    
    // Save Card Image Button
    saveCardImageBtn.addEventListener('click', function() {
        if (!selectedRoom || !cardImageFile) {
            alert('Please select a room and an image file.');
            return;
        }
        
        const formData = new FormData();
        formData.append('room_id', selectedRoom);
        formData.append('card_image', cardImageFile);
        
        // Show progress bar
        cardImageProgress.classList.remove('d-none');
        const progressBar = cardImageProgress.querySelector('.progress-bar');
        progressBar.style.width = '0%';
        
        // Create and configure XHR for upload with progress
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../api/room_media/upload_card_image.php', true);
        
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
            }
        };
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Card image uploaded successfully!');
                        // Refresh the table
                        mediaTable.ajax.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                } catch (e) {
                    alert('Error processing response.');
                }
            } else {
                alert('Upload failed. Please try again.');
            }
            
            // Hide progress bar
            setTimeout(() => {
                cardImageProgress.classList.add('d-none');
                saveCardImageBtn.classList.add('d-none');
            }, 1000);
        };
        
        xhr.onerror = function() {
            alert('An error occurred during the upload. Please try again.');
            cardImageProgress.classList.add('d-none');
        };
        
        xhr.send(formData);
    });
    
    // Save 360° Image Button
    savePanoramaBtn.addEventListener('click', function() {
        if (!selectedRoom360 || !panoramaFile) {
            alert('Please select a room and a 360° image file.');
            return;
        }
        
        const formData = new FormData();
        formData.append('room_id', selectedRoom360);
        formData.append('panorama_image', panoramaFile);
        
        // Show progress bar
        panoramaProgress.classList.remove('d-none');
        const progressBar = panoramaProgress.querySelector('.progress-bar');
        progressBar.style.width = '0%';
        
        // Create and configure XHR for upload with progress
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../api/room_media/upload_panorama.php', true);
        
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                progressBar.style.width = percentComplete + '%';
            }
        };
        
        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('360° image uploaded successfully!');
                        // Refresh the table
                        mediaTable.ajax.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                } catch (e) {
                    alert('Error processing response.');
                }
            } else {
                alert('Upload failed. Please try again.');
            }
            
            // Hide progress bar
            setTimeout(() => {
                panoramaProgress.classList.add('d-none');
                savePanoramaBtn.classList.add('d-none');
            }, 1000);
        };
        
        xhr.onerror = function() {
            alert('An error occurred during the upload. Please try again.');
            panoramaProgress.classList.add('d-none');
        };
        
        xhr.send(formData);
    });
    
    // Handle drag and drop for card image uploads
    cardImageUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('dragover');
    });
    
    cardImageUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
    });
    
    cardImageUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
        
        if (e.dataTransfer.files.length > 0) {
            cardImageUpload.files = e.dataTransfer.files;
            // Trigger the change event manually
            const event = new Event('change');
            cardImageUpload.dispatchEvent(event);
        }
    });
    
    // Handle drag and drop for panorama uploads
    panoramaUploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.add('dragover');
    });
    
    panoramaUploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
    });
    
    panoramaUploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.remove('dragover');
        
        if (e.dataTransfer.files.length > 0) {
            panoramaUpload.files = e.dataTransfer.files;
            // Trigger the change event manually
            const event = new Event('change');
            panoramaUpload.dispatchEvent(event);
        }
    });
    
    // View Media button click
    $(roomMediaTable).on('click', '.view-media', function() {
        const roomId = $(this).data('id');
        
        // Fetch room media data
        fetch(`../api/room_media/get_room_media.php?room_id=${roomId}`)
            .then(response => response.json())
            .then(data => {
                const modalCardImage = document.getElementById('modalCardImage');
                const modalPanoramaView = document.getElementById('modalPanoramaView');
                
                // Show card image if available
                if (data.card_image) {
                    modalCardImage.src = `../uploads/room_images/${data.card_image}`;
                    document.getElementById('card-tab').classList.remove('disabled');
                } else {
                    modalCardImage.src = '../assets/img/no-image.png';
                }
                
                // Initialize 360° viewer if panorama available
                if (data.panorama_image) {
                    if (modalPanoramaViewer) {
                        modalPanoramaViewer.destroy();
                    }
                    
                    modalPanoramaViewer = new PhotoSphereViewer.Viewer({
                        container: modalPanoramaView,
                        panorama: `../public/panoramas/${data.panorama_image}`,
                        size: {
                            width: '100%',
                            height: '100%'
                        },
                        navbar: ['autorotate', 'zoom', 'fullscreen'],
                        defaultZoomLvl: 0
                    });
                    
                    document.getElementById('panorama-tab').classList.remove('disabled');
                } else {
                    if (modalPanoramaViewer) {
                        modalPanoramaViewer.destroy();
                        modalPanoramaViewer = null;
                    }
                    
                    modalPanoramaView.innerHTML = '<div class="text-center p-5"><p>No 360° image available</p></div>';
                    document.getElementById('panorama-tab').classList.add('disabled');
                }
                
                // Show the modal
                const viewMediaModal = new bootstrap.Modal(document.getElementById('viewMediaModal'));
                viewMediaModal.show();
            })
            .catch(error => {
                console.error('Error fetching room media details:', error);
                alert('Failed to load room media details.');
            });
    });
    
    // Delete Media button click
    $(roomMediaTable).on('click', '.delete-media', function() {
        if (confirm('Are you sure you want to delete media for this room?')) {
            const roomId = $(this).data('id');
            
            fetch('../api/room_media/delete_room_media.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `room_id=${roomId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Room media deleted successfully!');
                    mediaTable.ajax.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error deleting room media:', error);
                alert('Failed to delete room media. Please try again.');
            });
        }
    });
    
    // Handle tab changes in the modal
    document.getElementById('mediaTab').addEventListener('shown.bs.tab', function (e) {
        if (e.target.id === 'panorama-tab' && modalPanoramaViewer) {
            // Force resize when switching to panorama tab
            modalPanoramaViewer.resize();
        }
    });
}); 