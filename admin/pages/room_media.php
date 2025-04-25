<?php
require_once '../includes/head.php';
?>
<link rel="stylesheet" href="../css/room_media.css">

<body>
    <?php require_once '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="page-header">
            <h1>Room Media Manager</h1>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Room Card Images</h2>
                        <p class="text-muted">Upload images that will appear on room cards in the landing page</p>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="roomSelect" class="form-label">Select Room</label>
                            <select class="form-select" id="roomSelect">
                                <option value="" selected disabled>Choose a room</option>
                                <!-- Rooms will be populated dynamically -->
                            </select>
                        </div>

                        <div id="cardImageContainer" class="text-center mb-3 d-none">
                            <img id="currentCardImage" src="" alt="Current Room Card Image" class="img-fluid mb-2 rounded shadow-sm">
                            <p class="text-muted">Current card image</p>
                        </div>

                        <div class="upload-area" id="cardImageUploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <h3>Drag & Drop Card Image</h3>
                            <p>or</p>
                            <label for="cardImageUpload" class="btn btn-primary">Browse Files</label>
                            <input type="file" id="cardImageUpload" accept="image/*" class="d-none">
                            <p class="text-muted mt-2">Recommended size: 800x600px, Max: 5MB</p>
                        </div>

                        <div class="progress mt-3 d-none" id="cardImageProgress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>

                        <div class="text-end mt-3">
                            <button class="btn btn-success d-none" id="saveCardImageBtn">
                                <i class="fas fa-save"></i> Save Card Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>360° Images</h2>
                        <p class="text-muted">Upload 360° panoramic images for interactive room views</p>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="roomSelect360" class="form-label">Select Room</label>
                            <select class="form-select" id="roomSelect360">
                                <option value="" selected disabled>Choose a room</option>
                                <!-- Rooms will be populated dynamically -->
                            </select>
                        </div>

                        <div id="panoramaContainer" class="text-center mb-3 d-none">
                            <div id="panoramaPreview" class="panorama-preview"></div>
                            <p class="text-muted">Current 360° panorama</p>
                        </div>

                        <div class="upload-area" id="panoramaUploadArea">
                            <div class="upload-icon">
                                <i class="fas fa-panorama"></i>
                            </div>
                            <h3>Drag & Drop 360° Image</h3>
                            <p>or</p>
                            <label for="panoramaUpload" class="btn btn-primary">Browse Files</label>
                            <input type="file" id="panoramaUpload" accept="image/*" class="d-none">
                            <p class="text-muted mt-2">Equirectangular format recommended, Max: 30MB</p>
                        </div>

                        <div class="progress mt-3 d-none" id="panoramaProgress">
                            <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                        </div>

                        <div class="text-end mt-3">
                            <button class="btn btn-success d-none" id="savePanoramaBtn">
                                <i class="fas fa-save"></i> Save 360° Image
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Room Media Gallery</h2>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="roomMediaTable" class="table table-hover display responsive nowrap" width="100%">
                        <thead>
                            <tr>
                                <th>Room Number</th>
                                <th>Room Type</th>
                                <th>Card Image</th>
                                <th>360° Image</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Dynamic content will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Room Media Modal -->
    <div class="modal fade" id="viewMediaModal" tabindex="-1" aria-labelledby="viewMediaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMediaModalLabel">Room Media Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs" id="mediaTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="card-tab" data-bs-toggle="tab" data-bs-target="#card-tab-pane" type="button" role="tab">Card Image</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="panorama-tab" data-bs-toggle="tab" data-bs-target="#panorama-tab-pane" type="button" role="tab">360° View</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="mediaTabContent">
                        <div class="tab-pane fade show active p-3" id="card-tab-pane" role="tabpanel" aria-labelledby="card-tab" tabindex="0">
                            <div class="text-center">
                                <img id="modalCardImage" src="" alt="Room Card Image" class="img-fluid rounded">
                            </div>
                        </div>
                        <div class="tab-pane fade p-3" id="panorama-tab-pane" role="tabpanel" aria-labelledby="panorama-tab" tabindex="0">
                            <div id="modalPanoramaView" class="panorama-viewer"></div>
                            <div class="text-center mt-2">
                                <p class="text-muted">Drag to explore the 360° view</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Create directory for JS files if it doesn't exist -->
    <script>
        // This is just a placeholder to notify about required JS setup
        console.log("Room Media Manager initialized");
    </script>

    <!-- Load the 360° viewer library (Photo Sphere Viewer) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/photo-sphere-viewer@4.6.0/dist/photo-sphere-viewer.min.css" />
    <!-- Load dependencies in the correct order -->
    <script src="https://cdn.jsdelivr.net/npm/three@0.137.0/build/three.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/uevent@2/browser.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/photo-sphere-viewer@4.6.0/dist/photo-sphere-viewer.min.js"></script>

    <!-- Load the room media management JS -->
    <script src="../js/room_media/room_media.js"></script>
</body>

</html>