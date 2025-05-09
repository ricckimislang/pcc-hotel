<!-- Feedback Modal -->
<div class="modal fade" id="feedbackModal" tabindex="-1" aria-labelledby="feedbackModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feedbackModalLabel">Submit Your Feedback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="feedbackForm">
                    <input type="hidden" id="bookingId" name="booking_id">
                    <input type="hidden" id="customerId" name="customer_id">

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Rating</label>
                        <div class="rating d-flex justify-content-center gap-2">
                            <input type="radio" class="btn-check star-input" name="rating" value="1" id="star1" autocomplete="off">
                            <label class="btn btn-outline-warning star-label" for="star1" data-rating="1"><i class="fas fa-star"></i></label>

                            <input type="radio" class="btn-check star-input" name="rating" value="2" id="star2" autocomplete="off">
                            <label class="btn btn-outline-warning star-label" for="star2" data-rating="2"><i class="fas fa-star"></i></label>

                            <input type="radio" class="btn-check star-input" name="rating" value="3" id="star3" autocomplete="off">
                            <label class="btn btn-outline-warning star-label" for="star3" data-rating="3"><i class="fas fa-star"></i></label>

                            <input type="radio" class="btn-check star-input" name="rating" value="4" id="star4" autocomplete="off">
                            <label class="btn btn-outline-warning star-label" for="star4" data-rating="4"><i class="fas fa-star"></i></label>

                            <input type="radio" class="btn-check star-input" name="rating" value="5" id="star5" autocomplete="off">
                            <label class="btn btn-outline-warning star-label" for="star5" data-rating="5"><i class="fas fa-star"></i></label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label fw-semibold">Comments</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4"
                            placeholder="Share your experience with us..."
                            style="resize: none;"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submitFeedback">Submit Feedback</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Star rating behavior
    const starLabels = document.querySelectorAll('.star-label');
    const starInputs = document.querySelectorAll('.star-input');

    // Function to update star appearance
    function updateStars(selectedRating) {
        starLabels.forEach(label => {
            const rating = parseInt(label.getAttribute('data-rating'));
            if (rating <= selectedRating) {
                label.classList.add('active', 'btn-warning');
                label.classList.remove('btn-outline-warning');
            } else {
                label.classList.remove('active', 'btn-warning');
                label.classList.add('btn-outline-warning');
            }
        });
    }

    // Add click handlers to star inputs
    starInputs.forEach(input => {
        input.addEventListener('change', function() {
            if (this.checked) {
                const rating = parseInt(this.value);
                updateStars(rating);
            }
        });
    });

    // Reset stars when modal is closed
    document.querySelector('#feedbackModal').addEventListener('hidden.bs.modal', function() {
        starLabels.forEach(label => {
            label.classList.remove('active', 'btn-warning');
            label.classList.add('btn-outline-warning');
        });
        document.getElementById('feedbackForm').reset();
    });

    document.getElementById('submitFeedback').addEventListener('click', function() {
        const formData = {
            booking_id: document.getElementById('bookingId').value,
            customer_id: document.getElementById('customerId').value,
            rating: document.querySelector('input[name="rating"]:checked')?.value,
            comment: document.getElementById('comment').value
        };

        if (!formData.rating) {
            Swal.fire({
                icon: 'error',
                title: 'Rating Required',
                text: 'Please select a rating before submitting',
                confirmButtonClass: 'btn btn-primary'
            });
            return;
        }

        fetch('../api/submit_feedback.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thank You!',
                        text: 'Your feedback has been submitted successfully',
                        confirmButtonClass: 'btn btn-primary'
                    }).then(() => {
                        $('#feedbackModal').modal('hide');
                        document.getElementById('feedbackForm').reset();
                        updateStars(0); // Reset stars visual
                    });
                } else {
                    throw new Error(data.message || 'Failed to submit feedback');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message,
                    confirmButtonClass: 'btn btn-primary'
                });
            });
    });
</script>