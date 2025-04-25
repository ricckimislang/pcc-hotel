<!-- Add Room Type Modal -->
<div class="modal fade" id="addRoomTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Room Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addRoomTypeForm" enctype="multipart/form-data">

                    <!-- Basic Info Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-bed me-2"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type_name" class="form-label small text-muted mb-1">TYPE
                                            NAME</label>
                                        <input type="text" class="form-control" id="type_name" name="type_name"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="base_price" class="form-label small text-muted mb-1">BASE
                                            PRICE</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" step="0.01" class="form-control" id="base_price"
                                                name="base_price" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Room Image Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-image me-2"></i>Room Image</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="room_image" class="form-label small text-muted mb-1">UPLOAD ROOM IMAGE</label>
                                <input type="file" class="form-control" id="room_image" name="room_image" accept="image/*">
                                <small class="form-text text-muted">Upload a high-quality image of the room (JPEG, PNG, WebP). Recommended size: 1200x800px.</small>
                                <div class="mt-3 current-image-preview rounded bg-light d-flex justify-content-center" style="display:none;">
                                    <img id="add_image_preview" src="" alt="Room image preview" class="img-fluid rounded" style="max-height: 200px;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Floor Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-building me-2"></i>Floor</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="floor" class="form-label small text-muted mb-1">FLOOR</label>
                                <select name="floor" id="floor" class="form-control">
                                    <option value="">Select Floor</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Enter room type description"></textarea>
                                <small class="form-text text-muted">Provide a detailed description of the room type and
                                    its features.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Capacity Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Capacity</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="form-group">
                                    <label for="capacity" class="form-label small text-muted mb-1">MAXIMUM
                                        OCCUPANCY</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="number" class="form-control" id="capacity" name="capacity"
                                            required>
                                        <span class="input-group-text">person(s)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-concierge-bell me-2"></i>Amenities</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control" id="amenities" name="amenities" rows="3"
                                    placeholder="Enter amenities separated by commas (e.g., WiFi, TV, Mini Bar)"></textarea>
                                <small class="form-text text-muted">List all amenities provided with this room type.
                                    Separate each amenity with a comma.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addRoomTypeForm" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Save Room Type
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Edit Room Type Modal -->
<div class="modal fade" id="editRoomTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Room Type</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editRoomTypeForm" action="../api/room_types/update_room_type.php" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="edit_room_type_id" name="room_type_id">
                    <input type="hidden" name="action" value="update">

                    <!-- Basic Info Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-bed me-2"></i>Basic Information</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_type_name" class="form-label small text-muted mb-1">TYPE
                                            NAME</label>
                                        <input type="text" class="form-control" id="edit_type_name" name="type_name"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_base_price" class="form-label small text-muted mb-1">BASE
                                            PRICE</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" step="0.01" class="form-control" id="edit_base_price"
                                                name="base_price" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Room Image Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-image me-2"></i>Room Image</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="mb-3" id="current_image_container">
                                    <label class="form-label small text-muted mb-1">CURRENT IMAGE</label>
                                    <div class="current-image-preview rounded bg-light d-flex justify-content-center mb-2">
                                        <img id="edit_current_image" src="" alt="Current room image" class="img-fluid rounded" style="max-height: 200px;">
                                    </div>
                                </div>
                                <label for="edit_room_image" class="form-label small text-muted mb-1">CHANGE ROOM IMAGE</label>
                                <input type="file" class="form-control" id="edit_room_image" name="room_image" accept="image/*">
                                <small class="form-text text-muted">Upload a new image to replace the current one (JPEG, PNG, WebP). Leave blank to keep the existing image.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Capacity Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-users me-2"></i>Capacity</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_capacity" class="form-label small text-muted mb-1">MAXIMUM
                                            OCCUPANCY</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            <input type="number" class="form-control" id="edit_capacity" name="capacity"
                                                required>
                                            <span class="input-group-text">person(s)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control" id="edit_description" name="description" rows="3"
                                    placeholder="Enter room type description"></textarea>
                                <small class="form-text text-muted">Provide a detailed description of the room type and
                                    its features.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities Card -->
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-concierge-bell me-2"></i>Amenities</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control" id="edit_amenities" name="amenities" rows="3"
                                    placeholder="Enter amenities separated by commas (e.g., WiFi, TV, Mini Bar)"></textarea>
                                <small class="form-text text-muted">List all amenities provided with this room type.
                                    Separate each amenity with a comma.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editRoomTypeForm" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>Update Room Type
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Room Type Modal -->
<div class="modal fade" id="viewRoomTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Room Type Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Basic Info Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-bed me-2"></i>Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label small text-muted mb-1">TYPE NAME</label>
                                    <h5 id="view_type_name" class="mb-0 fw-bold"></h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label small text-muted mb-1">BASE PRICE</label>
                                    <h5 id="view_base_price" class="mb-0 fw-bold text-primary"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Image Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-image me-2"></i>Room Image</h6>
                    </div>
                    <div class="card-body text-center">
                        <div id="view_room_image_container" class="mb-0">
                            <img id="view_room_image" src="" alt="Room image" class="img-fluid rounded shadow-sm" style="max-height: 300px;">
                        </div>
                    </div>
                </div>

                <!-- Capacity Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Capacity</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label small text-muted mb-1">MAXIMUM OCCUPANCY</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user fs-4 me-2 text-secondary"></i>
                                        <h5 id="view_capacity" class="mb-0 fw-bold"></h5>
                                        <span class="ms-2 text-muted">person(s)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h6>
                    </div>
                    <div class="card-body">
                        <div id="view_description" class="py-2 px-3 bg-light rounded"></div>
                    </div>
                </div>

                <!-- Amenities Card -->
                <div class="card mb-0 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-concierge-bell me-2"></i>Amenities</h6>
                    </div>
                    <div class="card-body">
                        <div id="view_amenities" class="py-2"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>