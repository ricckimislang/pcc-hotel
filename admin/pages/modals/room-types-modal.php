<!-- Add Room Type Modal -->
<div class="modal fade" id="addRoomTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add New Room Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addRoomTypeForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="type_name" class="form-label">Type Name</label>
                                <input type="text" class="form-control" id="type_name" name="type_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="base_price" class="form-label">Base Price</label>
                                <input type="number" step="0.01" class="form-control" id="base_price" name="base_price"
                                    required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="capacity" name="capacity" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="amenities" class="form-label">Amenities</label>
                        <textarea class="form-control" id="amenities" name="amenities" rows="3"
                            placeholder="Enter amenities separated by commas"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addRoomTypeForm" class="btn btn-primary">Save Room Type</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Room Type Modal -->
<div class="modal fade" id="editRoomTypeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Room Type</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editRoomTypeForm" action="../api/room_types.php" method="post">
                    <input type="hidden" id="edit_room_type_id" name="room_type_id">
                    <input type="hidden" name="action" value="update">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_type_name" class="form-label">Type Name</label>
                                <input type="text" class="form-control" id="edit_type_name" name="type_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_base_price" class="form-label">Base Price</label>
                                <input type="number" step="0.01" class="form-control" id="edit_base_price"
                                    name="base_price" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_capacity" class="form-label">Capacity</label>
                                <input type="number" class="form-control" id="edit_capacity" name="capacity" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_description" class="form-label">Description</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="edit_amenities" class="form-label">Amenities</label>
                        <textarea class="form-control" id="edit_amenities" name="amenities" rows="3"
                            placeholder="Enter amenities separated by commas"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="editRoomTypeForm" class="btn btn-primary">Update Room Type</button>
            </div>
        </div>
    </div>
</div>