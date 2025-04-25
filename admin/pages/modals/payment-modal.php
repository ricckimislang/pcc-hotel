<!-- Process Payment Modal -->
<div class="modal fade" id="processPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Process Check-out</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="processPaymentForm">
                    <input type="hidden" id="payment_booking_id" name="booking_id">
                    <input type="hidden" id="payment_amount" name="payment_amount" value="0.01">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Booking Details</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-5">Guest Name:</dt>
                                        <dd class="col-sm-7 guest-name"></dd>
                                        <dt class="col-sm-5">Room:</dt>
                                        <dd class="col-sm-7 room-number"></dd>
                                        <dt class="col-sm-5">Check In:</dt>
                                        <dd class="col-sm-7 check-in-date"></dd>
                                        <dt class="col-sm-5">Check Out:</dt>
                                        <dd class="col-sm-7 check-out-date"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">Amount Details</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row mb-0 small">
                                        <dt class="col-sm-6">Room Total:</dt>
                                        <dd class="col-sm-6 room-total"></dd>
                                        <dt class="col-sm-6">Additional Fees:</dt>
                                        <dd class="col-sm-6 additional-fees">₱0.00</dd>
                                        <dt class="col-sm-6">Total Amount:</dt>
                                        <dd class="col-sm-6 total-amount"></dd>
                                        <dt class="col-sm-6">Amount Paid:</dt>
                                        <dd class="col-sm-6 amount-paid"></dd>
                                        <dt class="col-sm-6">Balance Due:</dt>
                                        <dd class="col-sm-6 balance-due"></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="card mb-3">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Additional Fees</h6>
                                    <button type="button" class="btn btn-sm btn-primary" id="addFeeBtn">
                                        <i class="bi bi-plus"></i> Add Item
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered" id="additionalFeesTable">
                                            <thead>
                                                <tr>
                                                    <th>Item</th>
                                                    <th>Price</th>
                                                    <th>Qty</th>
                                                    <th>Subtotal</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <!-- Additional fee items will be added here dynamically -->
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="3" class="text-end">Total Additional Fees:</th>
                                                    <th id="totalAdditionalFees">₱0.00</th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div id="noFeesMessage" class="text-muted small fst-italic">
                                        No additional items added yet.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" form="processPaymentForm" class="btn btn-primary" id="processPaymentBtn"></button>
            </div>
        </div>
    </div>
</div>

<!-- Add Fee Item Modal -->
<div class="modal fade" id="addFeeItemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Additional Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addFeeItemForm">
                    <div class="mb-3">
                        <label for="fee_item_name" class="form-label">Item Name</label>
                        <select class="form-select" id="fee_item_name" required>
                            <option value="" selected disabled>Select a additional fee</option>
                            <option value="AdditionalGuest">Additional Guest</option>
                            <option value="Towel">Towel</option>
                            <option value="Soap">Soap</option>
                            <option value="Shampoo">Shampoo</option>
                            <option value="Toothbrush">Toothbrush</option>
                            <option value="Toothpaste">Toothpaste</option>
                            <option value="Slippers">Slippers</option>
                            <option value="Extra Bed">Extra Bed</option>
                            <option value="Laundry">Laundry</option>
                            <option value="Room Service">Room Service</option>
                            <option value="Late Checkout">Late Checkout</option>
                            <option value="custom">Custom Item...</option>
                        </select>
                    </div>
                    
                    <div class="mb-3" id="customItemNameContainer" style="display: none;">
                        <label for="custom_item_name" class="form-label">Custom Item Name</label>
                        <input type="text" class="form-control" id="custom_item_name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="fee_item_price" class="form-label">Item Price (₱)</label>
                        <input type="number" step="0.01" class="form-control" id="fee_item_price" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fee_item_quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="fee_item_quantity" min="1" value="1" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddFeeBtn">Add Item</button>
            </div>
        </div>
    </div>
</div>

<!-- View Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Booking Information</h6>
                        <span class="badge payment-status-badge"></span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-5">Guest Name:</dt>
                                    <dd class="col-sm-7 guest-name"></dd>
                                    <dt class="col-sm-5">Room:</dt>
                                    <dd class="col-sm-7 room-number"></dd>
                                </dl>
                            </div>
                            <div class="col-md-6">
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-6">Total Amount:</dt>
                                    <dd class="col-sm-6 total-amount"></dd>
                                    <dt class="col-sm-6">Balance Due:</dt>
                                    <dd class="col-sm-6 balance-due"></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Additional Items</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="paymentHistoryTableBody">
                            <!-- Payment history will be loaded dynamically -->
                        </tbody>
                    </table>
                </div>
                
                <div id="noPaymentsMessage" class="alert alert-info" style="display: none;">
                    No payment records found for this booking.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="addNewPaymentBtn">Add New Payment</button>
            </div>
        </div>
    </div>
</div>

<!-- Receipt View Modal -->
<div class="modal fade" id="viewReceiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" class="img-fluid receipt-image" alt="Payment Receipt">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="" class="btn btn-primary download-receipt" download>Download</a>
            </div>
        </div>
    </div>
</div>

<!-- Check-out Confirmation Modal -->
<div class="modal fade" id="checkoutConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">Confirm Check-out</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                </div>
                <p>Are you sure you want to check out guest <strong class="guest-name-confirm"></strong> from <strong class="room-number-confirm"></strong>?</p>
                <p>Please make sure all charges have been added before proceeding with check-out.</p>
                <div class="alert alert-info small">
                    <i class="fas fa-info-circle"></i> Once you proceed, you will be able to add any additional charges before finalizing the check-out.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmCheckoutBtn">Proceed with Check-out</button>
            </div>
        </div>
    </div>
</div>

