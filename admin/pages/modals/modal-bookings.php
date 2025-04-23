<!-- View Booking Modal -->
<div id="viewBookingModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Guest Information</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-4">Name:</dt>
                                        <dd class="col-sm-8 guest-name"></dd>
                                        <dt class="col-sm-4">Email:</dt>
                                        <dd class="col-sm-8 guest-email"></dd>
                                        <dt class="col-sm-4">Phone:</dt>
                                        <dd class="col-sm-8 guest-phone"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Room Details</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-5">Room Number:</dt>
                                        <dd class="col-sm-4 room-number"></dd>
                                        <dt class="col-sm-8">Room Type:</dt>
                                        <dd class="col-sm-4 room-type"></dd>
                                        <dt class="col-sm-4">Floor:</dt>
                                        <dd class="col-sm-8 room-floor"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Booking Information</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-4">Check In:</dt>
                                        <dd class="col-sm-8 check-in-date"></dd>
                                        <dt class="col-sm-4">Check Out:</dt>
                                        <dd class="col-sm-8 check-out-date"></dd>
                                        <dt class="col-sm-6">Total guests:</dt>
                                        <dd class="col-sm-6 total-guests"></dd>
                                        <dt class="col-sm-6">Total Amount:</dt>
                                        <dd class="col-sm-6  total-amount"></dd>
                                        <dt class="col-sm-6">Status:</dt>
                                        <dd class="col-sm-6 booking-status"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Payment Information</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-6">Reservation Fee:</dt>
                                        <dd class="col-sm-6 reservation-fee"></dd>
                                        <dt class="col-sm-4">Payment Status:</dt>
                                        <dd class="col-sm-8 payment-status"></dd>
                                        <dt class="col-sm-4">Transaction ID:</dt>
                                        <dd class="col-sm-8 transaction-id"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Booking Modal -->
<div id="editBookingModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Booking</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editBookingForm">
                    <input type="hidden" id="edit_booking_id" name="booking_id">

                    <div class="container-fluid">
                        <div class="row g-4">
                            <!-- Guest Information (Read-only) -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="mb-0">Guest Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row mb-0 small">
                                            <dt class="col-sm-4">Name:</dt>
                                            <dd class="col-sm-8  edit-guest-name"></dd>
                                            <dt class="col-sm-4">Email:</dt>
                                            <dd class="col-sm-8 edit-guest-email"></dd>
                                            <dt class="col-sm-4">Phone:</dt>
                                            <dd class="col-sm-8 edit-guest-phone"></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <!-- Room Information (Read-only) -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="mb-0">Room Details</h6>
                                    </div>
                                    <div class="card-body">
                                        <dl class="row mb-0 small">
                                            <dt class="col-sm-4">Room Number:</dt>
                                            <dd class="col-sm-8 edit-room-number"></dd>
                                            <dt class="col-sm-4">Room Type:</dt>
                                            <dd class="col-sm-8 edit-room-type"></dd>
                                            <dt class="col-sm-4">Floor:</dt>
                                            <dd class="col-sm-8 edit-room-floor"></dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>

                            <!-- Editable Booking Information -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="mb-0">Booking Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="edit_check_in" class="form-label">Check In Date</label>
                                            <input type="date" class="form-control" id="edit_check_in"
                                                name="check_in_date" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_check_out" class="form-label">Check Out Date</label>
                                            <input type="date" class="form-control" id="edit_check_out"
                                                name="check_out_date" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_guests_count" class="form-label">Number of Guests</label>
                                            <input type="number" class="form-control" id="edit_guests_count"
                                                name="guests_count" min="1" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_special_requests" class="form-label">Special
                                                Requests</label>
                                            <textarea class="form-control" id="edit_special_requests"
                                                name="special_requests" rows="3"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Status Information -->
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header">
                                        <h6 class="mb-0">Status Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="edit_booking_status" class="form-label">Booking Status</label>
                                            <select class="form-select" id="edit_booking_status" name="booking_status"
                                                required>
                                                <option value="pending">Pending</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="checked_in">Checked In</option>
                                                <option value="checked_out">Checked Out</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_payment_status" class="form-label">Payment Status</label>
                                            <select class="form-select" id="edit_payment_status" name="payment_status"
                                                required>
                                                <option value="pending">Pending</option>
                                                <option value="partial">Partial</option>
                                                <option value="paid">Paid</option>
                                                <option value="refunded">Refunded</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveBookingChanges">Save Changes</button>
            </div>
        </div>
    </div>
</div>