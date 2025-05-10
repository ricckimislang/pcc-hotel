/**
 * Feedback Management JavaScript
 * Handles loading, displaying, and filtering feedback data
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize DataTable
    const feedbackTable = $('#feedback-table').DataTable({
        responsive: true,
        order: [[5, 'desc']], // Sort by date descending
        language: {
            emptyTable: "No feedback records found",
            info: "Showing _START_ to _END_ of _TOTAL_ feedback records",
            infoEmpty: "Showing 0 to 0 of 0 feedback records",
            search: "Search feedback:",
        },
        columns: [
            { data: 'customer_name' },
            {
                data: 'rating',
                render: function (data) {
                    return createStarRating(data);
                }
            },
            {
                data: 'comment',
                render: function (data) {
                    return `<div class="feedback-comment-cell">${data || 'No comment'}</div>`;
                }
            },
            { data: 'room_number' },
            {
                data: 'date',
                render: function (data) {
                    return formatDate(data);
                }
            },
            {
                data: 'id',
                orderable: false,
                searchable: false,
                render: function (data) {
                    return `<button class="btn btn-sm btn-primary view-feedback" data-id="${data}">
                                <i class="fas fa-eye"></i> View
                            </button>`;
                }
            }
        ],
        initComplete: function () {
            // After table is initialized, update dashboard stats
            updateDashboardStats();
        }
    });

    // Load feedback data on page load
    loadFeedbackData();

    // Set up date pickers with default values (last 30 days)
    const today = new Date();
    const thirtyDaysAgo = new Date();
    thirtyDaysAgo.setDate(today.getDate() - 30);

    document.getElementById('end-date').valueAsDate = today;
    document.getElementById('start-date').valueAsDate = thirtyDaysAgo;

    // Star rating filter buttons
    const filterButtons = document.querySelectorAll('[data-filter]');
    filterButtons.forEach(button => {
        button.addEventListener('click', function () {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));

            // Add active class to clicked button
            this.classList.add('active');

            // Apply the filter
            const filter = this.getAttribute('data-filter');
            applyRatingFilter(filter);
        });
    });

    // Date filter button
    document.getElementById('apply-date-filter').addEventListener('click', function () {
        applyDateFilter();
    });

    // View feedback details
    $('#feedback-table').on('click', '.view-feedback', function () {
        const feedbackId = $(this).data('id');
        viewFeedbackDetails(feedbackId);
    });

    // Initialize rating trend chart
    initRatingTrendChart();
});

/**
 * Creates a star rating display
 * @param {number} rating - Rating value (1-5)
 * @returns {string} HTML string for star rating
 */
function createStarRating(rating) {
    let stars = '<div class="star-rating">';

    for (let i = 5; i >= 1; i--) {
        if (i <= rating) {
            stars += '<i class="fas fa-star text-warning"></i>';
        } else {
            stars += '<i class="far fa-star text-muted"></i>';
        }
    }

    stars += '</div>';
    return stars;
}

/**
 * Format date for display
 * @param {string} dateString - ISO date string
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Load feedback data from the server
 */
function loadFeedbackData() {
    // Show loading indicator
    showLoading(true);

    // Fetch data from the server using jQuery AJAX
    $.ajax({
        url: '../api/feedback/get_all_feedback.php',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            // Update DataTable with fetched data
            $('#feedback-table').DataTable().clear().rows.add(data).draw();

            // Load statistics separately for the dashboard
            loadFeedbackStats();

            showLoading(false);
        },
        error: function (xhr, status, error) {
            console.error('Error fetching feedback data:', error);
            showLoading(false);

            // Show error message
            Swal.fire({
                icon: 'error',
                title: 'Failed to Load Data',
                text: 'Could not load feedback data. Please try again later.',
                confirmButtonColor: '#3498db'
            });
        }
    });
}

/**
 * Load feedback statistics from the server
 */
function loadFeedbackStats() {
    $.ajax({
        url: '../api/feedback/get_feedback_stats.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status) {
                // Update dashboard with statistics
                updateDashboardStats(response.data.stats);

                // Update chart with trend data
                updateRatingTrendChart(response.data.trend);
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching feedback statistics:', error);
        }
    });
}

/**
 * Show or hide loading spinner
 * @param {boolean} show - Whether to show or hide the spinner
 */
function showLoading(show) {
    // Implement loading spinner logic here
    const loadingElement = document.createElement('div');
    loadingElement.id = 'loading-spinner';
    loadingElement.className = 'loading-spinner';
    loadingElement.innerHTML = `
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    `;

    if (show) {
        // Don't add if already exists
        if (!document.getElementById('loading-spinner')) {
            document.querySelector('.table-responsive').prepend(loadingElement);
        }
    } else {
        const existingSpinner = document.getElementById('loading-spinner');
        if (existingSpinner) {
            existingSpinner.remove();
        }
    }
}

/**
 * Update dashboard statistics based on feedback data
 * @param {Object} stats - Feedback statistics object
 */
function updateDashboardStats(stats = null) {
    if (!stats) {
        // If no stats provided, get from datatable (less accurate)
        const data = $('#feedback-table').DataTable().data().toArray();

        if (data.length === 0) {
            document.getElementById('avg-rating-value').textContent = '0.0';
            document.getElementById('total-ratings').textContent = '0';

            // Reset all star counts and bars
            for (let i = 1; i <= 5; i++) {
                document.getElementById(`${getNumberWord(i)}-star-count`).textContent = '0';
                document.getElementById(`${getNumberWord(i)}-star-bar`).style.width = '0%';
            }

            return;
        }

        // Calculate average rating
        let totalRating = 0;
        const starCounts = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };

        data.forEach(item => {
            totalRating += parseFloat(item.rating);
            starCounts[item.rating]++;
        });

        const avgRating = totalRating / data.length;
        const totalRatings = data.length;

        // Update average rating display
        document.getElementById('avg-rating-value').textContent = avgRating.toFixed(1);
        document.getElementById('total-ratings').textContent = totalRatings;

        // Update stars display
        updateStarsDisplay(avgRating);

        // Update rating distribution
        for (let i = 1; i <= 5; i++) {
            const count = starCounts[i];
            const percentage = (count / totalRatings) * 100;

            document.getElementById(`${getNumberWord(i)}-star-count`).textContent = count;
            document.getElementById(`${getNumberWord(i)}-star-bar`).style.width = `${percentage}%`;
        }
    } else {
        // Update with provided statistics
        document.getElementById('avg-rating-value').textContent = stats.average_rating;
        document.getElementById('total-ratings').textContent = stats.total_count;

        // Update stars display based on average rating
        updateStarsDisplay(parseFloat(stats.average_rating));

        // Update rating distribution
        document.getElementById('five-star-count').textContent = stats.five_star;
        document.getElementById('five-star-bar').style.width = `${stats.five_star_percent}%`;

        document.getElementById('four-star-count').textContent = stats.four_star;
        document.getElementById('four-star-bar').style.width = `${stats.four_star_percent}%`;

        document.getElementById('three-star-count').textContent = stats.three_star;
        document.getElementById('three-star-bar').style.width = `${stats.three_star_percent}%`;

        document.getElementById('two-star-count').textContent = stats.two_star;
        document.getElementById('two-star-bar').style.width = `${stats.two_star_percent}%`;

        document.getElementById('one-star-count').textContent = stats.one_star;
        document.getElementById('one-star-bar').style.width = `${stats.one_star_percent}%`;
    }
}

/**
 * Update the stars display based on the average rating
 * @param {number} rating - Average rating value
 */
function updateStarsDisplay(rating) {
    const starsContainer = document.querySelector('.rating-display .stars-display');
    starsContainer.innerHTML = '';

    for (let i = 1; i <= 5; i++) {
        let starIcon = document.createElement('i');

        if (i <= Math.floor(rating)) {
            // Full star
            starIcon.className = 'fas fa-star text-warning';
        } else if (i === Math.ceil(rating) && rating % 1 !== 0) {
            // Half star (if decimal part exists)
            starIcon.className = 'fas fa-star-half-alt text-warning';
        } else {
            // Empty star
            starIcon.className = 'far fa-star text-warning';
        }

        starsContainer.appendChild(starIcon);
    }
}

/**
 * Convert number to word for element IDs
 * @param {number} num - Number to convert
 * @returns {string} Word representation of number
 */
function getNumberWord(num) {
    const words = ['zero', 'one', 'two', 'three', 'four', 'five'];
    return words[num];
}

/**
 * Apply rating filter to the data table
 * @param {string} rating - Rating to filter by ('all' or rating number)
 */
function applyRatingFilter(rating) {
    const table = $('#feedback-table').DataTable();

    if (rating === 'all') {
        table.column(2).search('').draw();
    } else {
        // Create a regex to match the exact star rating (using the stars in the rating column)
        table.column(2).search(`${rating} star|${rating} stars`, true, false).draw();
    }
}

/**
 * Apply date filter to the data table
 */
function applyDateFilter() {
    const startDate = new Date(document.getElementById('start-date').value);
    const endDate = new Date(document.getElementById('end-date').value);

    // Add one day to end date to include the end date in the range
    endDate.setDate(endDate.getDate() + 1);

    if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Date Range',
            text: 'Please select a valid date range',
            confirmButtonColor: '#3498db'
        });
        return;
    }

    if (startDate > endDate) {
        Swal.fire({
            icon: 'warning',
            title: 'Invalid Date Range',
            text: 'Start date cannot be after end date',
            confirmButtonColor: '#3498db'
        });
        return;
    }

    // Filter the table
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        const dateStr = data[5]; // Date is in column 5
        const itemDate = new Date(dateStr);

        return itemDate >= startDate && itemDate <= endDate;
    });

    $('#feedback-table').DataTable().draw();

    // Remove the filter after drawing
    $.fn.dataTable.ext.search.pop();
}

/**
 * View detailed feedback information
 * @param {number} feedbackId - ID of the feedback to view
 */
function viewFeedbackDetails(feedbackId) {
    $.ajax({
        url: `../api/feedback/get_feedback_details.php`,
        type: 'GET',
        data: { id: feedbackId },
        dataType: 'json',
        success: function (data) {
            // Populate modal with data
            document.getElementById('modal-customer-name').textContent = data.customer_name;
            document.getElementById('modal-customer-email').textContent = data.email;
            document.getElementById('modal-customer-phone').textContent = data.phone || 'N/A';

            document.getElementById('modal-room-number').textContent = data.room_number;
            document.getElementById('modal-room-type').textContent = data.room_type;
            document.getElementById('modal-stay-period').textContent = `${formatDate(data.check_in_date)} - ${formatDate(data.check_out_date)}`;

            document.getElementById('modal-rating').textContent = data.rating;
            updateModalStars(data.rating);

            document.getElementById('modal-comment').textContent = data.comment || 'No comment provided';
            document.getElementById('modal-date').textContent = formatDate(data.date);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('viewFeedbackModal'));
            modal.show();
        },
        error: function (xhr, status, error) {
            console.error('Error fetching feedback details:', error);

            Swal.fire({
                icon: 'error',
                title: 'Failed to Load Details',
                text: 'Could not load feedback details. Please try again later.',
                confirmButtonColor: '#3498db'
            });
        }
    });
}

/**
 * Update stars in the modal based on rating
 * @param {number} rating - Rating value (1-5)
 */
function updateModalStars(rating) {
    const starsContainer = document.querySelector('.feedback-rating .stars-display');
    const stars = starsContainer.querySelectorAll('i');

    stars.forEach((star, index) => {
        if (index < rating) {
            star.className = 'fas fa-star text-warning';
        } else {
            star.className = 'far fa-star text-warning';
        }
    });
}

/**
 * Initialize the rating trend chart
 */
function initRatingTrendChart() {
    const ctx = document.getElementById('rating-trend-chart').getContext('2d');

    window.ratingTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Average Rating',
                data: [],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: '#3498db'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: false,
                    min: 1,
                    max: 5,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return `Average Rating: ${context.raw.toFixed(1)}`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Update the rating trend chart with feedback data
 * @param {Array} data - Feedback trend data array
 */
function updateRatingTrendChart(data) {
    if (!data || !data.length) return;

    // Extract data for chart
    const labels = data.map(item => item.month_name);
    const chartData = data.map(item => parseFloat(item.average_rating));

    // Update chart
    window.ratingTrendChart.data.labels = labels;
    window.ratingTrendChart.data.datasets[0].data = chartData;
    window.ratingTrendChart.update();
} 