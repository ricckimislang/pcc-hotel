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
                                        <dd class="col-sm-7 room-number"></dd>
                                        <dt class="col-sm-4">Room Type:</dt>
                                        <dd class="col-sm-8 room-type"></dd>
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
                                        <dt class="col-sm-4">Payment Status:</dt>
                                        <dd class="col-sm-8 payment-status"></dd>
                                        <dt class="col-sm-4">Reference No:</dt>
                                        <dd class="col-sm-8 reference-no"></dd>
                                        <dt class="col-sm-4">Payment Proof:</dt>
                                        <dd class="col-sm-8">
                                            <div class="d-flex flex-column">
                                                <img src="" alt="Payment Screenshot"
                                                    class="payment-proof img-fluid mb-2" style="max-width: 200px;">
                                                <a href="" class="payment-proof-link btn btn-sm btn-primary"
                                                    target="_blank">
                                                    <i class="bi bi-eye"></i> View Full Image
                                                </a>
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="cancelBookingBtn">Cancel Booking</button>
                <button type="button" class="btn btn-success" id="confirmBookingBtn" disabled>Confirm Booking</button>
            </div>
        </div>
    </div>
</div>

<!-- Policy Modal for Admin -->
<div id="adminPolicyModal" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Booking Policy</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Important Notice:</strong> Once a booking is confirmed, it cannot be cancelled.
                </div>

                <div class="mb-3">
                    <p>Please review the following booking policies before proceeding:</p>
                    <ul>
                        <li>Confirmed bookings are final and non-refundable</li>
                        <li>Payments are processed immediately upon confirmation</li>
                        <li>Changes to confirmed bookings are not permitted</li>
                    </ul>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" id="adminPolicyAgreement" class="form-check-input" required>
                    <label for="adminPolicyAgreement" class="form-check-label">I have read and agree to the booking
                        policy</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Decline</button>
                <button type="button" class="btn btn-danger" id="adminPolicyAccept" disabled>Accept & Continue</button>
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Policy agreement checkbox handler
        const adminPolicyAgreement = document.getElementById('adminPolicyAgreement');
        const adminPolicyAccept = document.getElementById('adminPolicyAccept');
        const adminPolicyModal = document.getElementById('adminPolicyModal');
        const cancelBookingBtn = document.getElementById('cancelBookingBtn');
        let currentBookingId = null;

        // Enable/disable accept button based on checkbox
        if (adminPolicyAgreement) {
            adminPolicyAgreement.addEventListener('change', function () {
                adminPolicyAccept.disabled = !this.checked;
            });
        }

        // Show policy modal when cancel button is clicked
        if (cancelBookingBtn) {
            cancelBookingBtn.addEventListener('click', function () {
                // Store the booking ID from the modal data
                currentBookingId = this.getAttribute('data-booking-id');

                // Reset the checkbox state
                if (adminPolicyAgreement) {
                    adminPolicyAgreement.checked = false;
                }
                if (adminPolicyAccept) {
                    adminPolicyAccept.disabled = true;
                }

                // Show the policy modal
                const policyModalInstance = new bootstrap.Modal(adminPolicyModal);
                policyModalInstance.show();
            });
        }

        // Process cancellation when policy is accepted
        if (adminPolicyAccept) {
            adminPolicyAccept.addEventListener('click', function () {
                // Hide the policy modal
                bootstrap.Modal.getInstance(adminPolicyModal).hide();

                // Proceed with cancellation
                proceedWithAdminCancellation(currentBookingId);
            });
        }

        // Function to handle the actual cancellation
        function proceedWithAdminCancellation(bookingId) {
            // Confirm again with standard confirmation dialog
            if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
                // Send AJAX request to cancel the booking
                fetch('../api/cancel_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ booking_id: bookingId, admin_cancel: true })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Booking successfully cancelled');
                            // Refresh the bookings table or close the modal and reload
                            bootstrap.Modal.getInstance(document.getElementById('viewBookingModal')).hide();
                            if (typeof loadBookings === 'function') {
                                loadBookings(); // Reload the bookings table if function exists
                            } else {
                                location.reload(); // Otherwise reload the page
                            }
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while cancelling the booking');
                    });
            }
        }

        // Set booking ID when opening the modal
        $('#viewBookingModal').on('show.bs.modal', function (e) {
            const bookingId = $(e.relatedTarget).data('booking-id');
            $('#cancelBookingBtn').attr('data-booking-id', bookingId);

            // Only show cancel button for appropriate booking statuses
            const bookingStatus = $('.booking-status').text().trim().toLowerCase();
            if (bookingStatus === 'confirmed' || bookingStatus === 'pending') {
                $('#cancelBookingBtn').show();
            } else {
                $('#cancelBookingBtn').hide();
            }
        });
    });
</script>