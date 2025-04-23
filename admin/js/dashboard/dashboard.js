// Dashboard JavaScript for PCC Hotel
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts and load data
    loadDashboardData('today');
    
    // Event listeners for filter changes
    document.getElementById('period-filter').addEventListener('change', function() {
        const period = this.value;
        if (period === 'custom') {
            document.getElementById('date-range-container').classList.remove('d-none');
        } else {
            document.getElementById('date-range-container').classList.add('d-none');
            loadDashboardData(period);
        }
    });
    
    document.getElementById('apply-filter').addEventListener('click', function() {
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;
        
        if (!startDate || !endDate) {
            showAlert('Please select both start and end dates', 'error');
            return;
        }
        
        loadDashboardData('custom', startDate, endDate);
    });
    
    // Initialize date pickers
    flatpickr('#start-date', {
        dateFormat: 'Y-m-d',
        maxDate: 'today'
    });
    
    flatpickr('#end-date', {
        dateFormat: 'Y-m-d',
        maxDate: 'today'
    });
});

/**
 * Load dashboard data from API based on selected filters
 */
function loadDashboardData(period, startDate = '', endDate = '') {
    // Show loading indicators
    showLoading(true);
    
    // Build API URL with filters
    let apiUrl = '../api/dashboard/dashboard_data.php?period=' + period;
    if (period === 'custom') {
        apiUrl += '&start_date=' + startDate + '&end_date=' + endDate;
    }
    
    // Fetch data from API
    fetch(apiUrl)
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                updateDashboardStats(response.data);
                renderOccupancyChart(response.data.occupancy);
                renderDailyOccupancyChart(response.data.daily_occupancy);
                renderRoomTypeDistribution(response.data.room_types);
                renderBookingStatusChart(response.data.bookings);
            } else {
                showAlert('Error loading dashboard data: ' + response.message, 'error');
            }
            showLoading(false);
        })
        .catch(error => {
            console.error('Error fetching dashboard data:', error);
            showAlert('Failed to load dashboard data. Please try again.', 'error');
            showLoading(false);
        });
}

/**
 * Update the dashboard statistics from API data
 */
function updateDashboardStats(data) {
    // Calculate occupancy rate
    const occupiedRooms = data.occupancy.occupied || 0;
    const occupancyRate = data.total_rooms > 0 ? ((occupiedRooms / data.total_rooms) * 100).toFixed(1) : 0;
    
    // Calculate total bookings
    const totalBookings = Object.values(data.bookings).reduce((sum, count) => sum + count, 0);
    
    // Update stat cards
    document.getElementById('total-rooms').textContent = data.total_rooms;
    document.getElementById('occupied-rooms').textContent = occupiedRooms;
    document.getElementById('available-rooms').textContent = data.occupancy.available || 0;
    document.getElementById('occupancy-rate').textContent = occupancyRate + '%';
    document.getElementById('total-bookings').textContent = totalBookings;
    
    // Update booking status counts
    document.getElementById('pending-bookings').textContent = data.bookings.pending || 0;
    document.getElementById('confirmed-bookings').textContent = data.bookings.confirmed || 0;
    document.getElementById('checked-in-bookings').textContent = data.bookings.checked_in || 0;
    document.getElementById('checked-out-bookings').textContent = data.bookings.checked_out || 0;
}

/**
 * Render the room occupancy pie chart
 */
function renderOccupancyChart(occupancyData) {
    const ctx = document.getElementById('occupancy-chart').getContext('2d');
    
    // If chart already exists, destroy it
    if (window.occupancyChart) {
        window.occupancyChart.destroy();
    }
    
    // Prepare data for chart
    const labels = [];
    const data = [];
    const colors = [];
    
    // Map status to colors
    const statusColors = {
        'available': 'rgba(40, 167, 69, 0.7)',
        'occupied': 'rgba(220, 53, 69, 0.7)',
        'maintenance': 'rgba(255, 193, 7, 0.7)',
        'reserved': 'rgba(23, 162, 184, 0.7)'
    };
    
    // Add data for each status
    for (const status in occupancyData) {
        labels.push(status.charAt(0).toUpperCase() + status.slice(1));
        data.push(occupancyData[status]);
        colors.push(statusColors[status] || 'rgba(108, 117, 125, 0.7)');
    }
    
    // Create chart
    window.occupancyChart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            height: 250,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Render the daily occupancy line chart
 */
function renderDailyOccupancyChart(occupancyData) {
    const ctx = document.getElementById('daily-occupancy-chart').getContext('2d');
    
    // If chart already exists, destroy it
    if (window.dailyOccupancyChart) {
        window.dailyOccupancyChart.destroy();
    }
    
    // Prepare data for chart
    const labels = occupancyData.map(item => {
        const date = new Date(item.date);
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    });
    
    const checkinsData = occupancyData.map(item => item.checkins);
    const checkoutsData = occupancyData.map(item => item.checkouts);
    
    // Create chart
    window.dailyOccupancyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Check-ins',
                    data: checkinsData,
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 2,
                    tension: 0.4
                },
                {
                    label: 'Check-outs',
                    data: checkoutsData,
                    backgroundColor: 'rgba(220, 53, 69, 0.2)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 2,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            height: 250,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            }
        }
    });
}

/**
 * Render the room type distribution bar chart
 */
function renderRoomTypeDistribution(roomTypeData) {
    const ctx = document.getElementById('room-type-chart').getContext('2d');
    
    // If chart already exists, destroy it
    if (window.roomTypeChart) {
        window.roomTypeChart.destroy();
    }
    
    // Prepare data for chart
    const labels = roomTypeData.map(item => item.type);
    const totalData = roomTypeData.map(item => item.total);
    const occupiedData = roomTypeData.map(item => item.occupied);
    
    // Create chart
    window.roomTypeChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Rooms',
                    data: totalData,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Occupied Rooms',
                    data: occupiedData,
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            height: 250,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Rooms'
                    },
                    ticks: {
                        precision: 0
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Room Type'
                    }
                }
            }
        }
    });
}

/**
 * Render the booking status doughnut chart
 */
function renderBookingStatusChart(bookingData) {
    const ctx = document.getElementById('booking-status-chart').getContext('2d');
    
    // If chart already exists, destroy it
    if (window.bookingStatusChart) {
        window.bookingStatusChart.destroy();
    }
    
    // Prepare data for chart
    const labels = [];
    const data = [];
    const colors = [];
    
    // Map status to colors
    const statusColors = {
        'pending': 'rgba(255, 193, 7, 0.7)',
        'confirmed': 'rgba(23, 162, 184, 0.7)',
        'checked_in': 'rgba(40, 167, 69, 0.7)',
        'checked_out': 'rgba(108, 117, 125, 0.7)',
        'cancelled': 'rgba(220, 53, 69, 0.7)'
    };
    
    // Format status labels
    const statusLabels = {
        'pending': 'Pending',
        'confirmed': 'Confirmed',
        'checked_in': 'Checked In',
        'checked_out': 'Checked Out',
        'cancelled': 'Cancelled'
    };
    
    // Add data for each status
    for (const status in bookingData) {
        labels.push(statusLabels[status] || status);
        data.push(bookingData[status]);
        colors.push(statusColors[status] || 'rgba(108, 117, 125, 0.7)');
    }
    
    // Create chart
    window.bookingStatusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            height: 250,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((sum, val) => sum + val, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

/**
 * Show loading state on dashboard
 */
function showLoading(isLoading) {
    const loadingOverlays = document.querySelectorAll('.chart-loading');
    loadingOverlays.forEach(overlay => {
        overlay.style.display = isLoading ? 'flex' : 'none';
    });
}

/**
 * Show alerts to user
 */
function showAlert(message, type = 'success') {
    Swal.fire({
        title: type === 'success' ? 'Success' : 'Error',
        text: message,
        icon: type,
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000
    });
}
