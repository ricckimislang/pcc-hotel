<!-- Add Room Modal -->
<div class="modal fade" id="addRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addRoomForm">
                    <input type="hidden" name="action" value="create">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="room_number" class="form-label">Room Number</label>
                                <input type="text" class="form-control" id="room_number" name="room_number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="room_type_id" class="form-label">Room Type</label>
                                <select class="form-control" id="room_type_id" name="room_type_id" required>
                                    <!-- dynamic -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="floor" class="form-label">Floor</label>
                                <input type="number" class="form-control" id="floor" name="floor">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="available">Available</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addRoomForm" class="btn btn-primary">Save Room</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Room Modal -->
<div class="modal fade" id="editRoomModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editRoomForm">
                    <input type="hidden" id="edit_room_id" name="edit_room_id">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_room_number" class="form-label">Room Number</label>
                                <input type="text" class="form-control" id="edit_room_number" name="edit_room_number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_room_type_id" class="form-label">Room Type</label>
                                <select class="form-control" id="edit_room_type_id" name="edit_room_type_id" required>
                                    <!-- dynamic -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_floor" class="form-label">Floor</label>
                                <input type="text" class="form-control" id="edit_floor" name="edit_floor">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_status" class="form-label">Status</label>
                                <select class="form-control" id="edit_status" name="edit_status" required>
                                    <option value="available">Available</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="reserved">Reserved</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="edit_description" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editRoomForm" class="btn btn-primary">Update Room</button>
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