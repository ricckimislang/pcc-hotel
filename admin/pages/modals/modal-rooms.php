<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add New Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="addRoomForm">
                    <input type="hidden" name="action" value="create">

                    <!-- Room Details Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-door-open me-2"></i>Room Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="room_number" class="form-label small text-muted mb-1">ROOM NUMBER</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" id="room_number" name="room_number" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="room_type_id" class="form-label small text-muted mb-1">ROOM TYPE</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-bed"></i></span>
                                            <select class="form-control" id="room_type_id" name="room_type_id" required>
                                                <!-- dynamic -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Status Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location & Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="floor" class="form-label small text-muted mb-1">FLOOR</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-level-up-alt"></i></span>
                                            <input type="number" class="form-control" id="floor" name="floor">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="status" class="form-label small text-muted mb-1">STATUS</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                            <select class="form-control" id="status" name="status" required>
                                                <option value="available">Available</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control" id="description" name="description" rows="3" placeholder="Enter room description"></textarea>
                                <small class="form-text text-muted">Provide additional details about this room that guests should know.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addRoomForm" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Save Room
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Room</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editRoomForm">
                    <input type="hidden" id="edit_room_id" name="edit_room_id">
                    
                    <!-- Room Details Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-door-open me-2"></i>Room Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_room_number" class="form-label small text-muted mb-1">ROOM NUMBER</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                            <input type="text" class="form-control" id="edit_room_number" name="edit_room_number" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_room_type_id" class="form-label small text-muted mb-1">ROOM TYPE</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-bed"></i></span>
                                            <select class="form-control" id="edit_room_type_id" name="edit_room_type_id" required>
                                                <!-- dynamic -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Location & Status Card -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location & Status</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_floor" class="form-label small text-muted mb-1">FLOOR</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-level-up-alt"></i></span>
                                            <input type="number" class="form-control" id="edit_floor" name="edit_floor">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="edit_status" class="form-label small text-muted mb-1">STATUS</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                            <select class="form-control" id="edit_status" name="edit_status" required>
                                                <option value="available">Available</option>
                                                <option value="occupied">Occupied</option>
                                                <option value="maintenance">Maintenance</option>
                                                <option value="reserved">Reserved</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description Card -->
                    <div class="card mb-0 border-0 shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h6>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <textarea class="form-control" id="edit_description" name="edit_description" rows="3" placeholder="Enter room description"></textarea>
                                <small class="form-text text-muted">Provide additional details about this room that guests should know.</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editRoomForm" class="btn btn-warning">
                    <i class="fas fa-save me-1"></i>Update Room
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Guest Details Modal -->
<div class="modal fade" id="viewGuestModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Guest Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <dl class="row">
                    <dt class="col-sm-4">Guest Name:</dt>
                    <span class="col-sm-8" id="guestName"></span>

                    <dt class="col-sm-4">Contact Number:</dt>
                    <span class="col-sm-8" id="guestContact"></span>

                    <dt class="col-sm-4">Check-in Date:</dt>
                    <span class="col-sm-8" id="checkInDate"></span>

                    <dt class="col-sm-4">Check-out Date:</dt>
                    <span class="col-sm-8" id="checkOutDate"></span>
                </dl>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Room Modal -->
<div class="modal fade" id="viewRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-info-circle me-2"></i>Room Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Room Details Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-door-open me-2"></i>Room Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label small text-muted mb-1">ROOM NUMBER</label>
                                    <h5 id="view_room_number" class="mb-0 fw-bold"></h5>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label small text-muted mb-1">ROOM TYPE</label>
                                    <h5 id="view_room_type" class="mb-0 fw-bold"></h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location & Status Card -->
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>Location & Status</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label small text-muted mb-1">FLOOR</label>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-level-up-alt fs-4 me-2 text-secondary"></i>
                                        <h5 id="view_floor" class="mb-0 fw-bold"></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-0">
                                    <label class="form-label small text-muted mb-1">STATUS</label>
                                    <div id="view_status_container">
                                        <span id="view_status" class="badge rounded-pill"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description Card -->
                <div class="card mb-0 border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-align-left me-2"></i>Description</h6>
                    </div>
                    <div class="card-body">
                        <div id="view_description" class="py-2 px-3 bg-light rounded"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
