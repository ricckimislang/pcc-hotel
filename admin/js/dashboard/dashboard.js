// Dashboard JavaScript for PCC Hotel
document.addEventListener('DOMContentLoaded', function () {
    // Register chart plugins - do this only once
    registerChartPlugins();

    // Initialize filters and events
    initializeFilters();

    // Initialize charts and load data
    loadDashboardData('month'); // Default to monthly view

    // Setup auto-refresh if enabled
    if (document.getElementById('realtime-toggle').checked) {
        startAutoRefresh();
    }
});

/**
 * Initialize all dashboard filters and event listeners
 */
function initializeFilters() {
    // Initialize date pickers
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#start-date', {
            dateFormat: 'Y-m-d'
        });

        flatpickr('#end-date', {
            dateFormat: 'Y-m-d'
        });
    }

    // Handle period change
    document.getElementById('period-filter').addEventListener('change', function () {
        const dateRangeContainer = document.getElementById('date-range-container');
        if (this.value === 'custom') {
            dateRangeContainer.classList.remove('d-none');
        } else {
            dateRangeContainer.classList.add('d-none');
            refreshDashboardData();
        }
    });

    // Handle refresh button
    document.getElementById('refresh-data').addEventListener('click', refreshDashboardData);

    // Handle apply filter button
    document.getElementById('apply-filter').addEventListener('click', refreshDashboardData);

    // Handle floor filter buttons
    document.addEventListener('click', function (e) {
        if (e.target.closest('#floor-filter')) {
            const button = e.target.closest('button');
            if (button) {
                document.querySelectorAll('#floor-filter button').forEach(btn => {
                    btn.classList.remove('active');
                });
                button.classList.add('active');
                const floor = button.dataset.floor;
                filterRoomsByFloor(floor);
            }
        }
    });

    // Handle trend analysis buttons
    document.addEventListener('click', function (e) {
        if (e.target.dataset.trend) {
            document.querySelectorAll('[data-trend]').forEach(btn => {
                btn.classList.remove('active');
            });
            e.target.classList.add('active');
            loadOccupancyTrendData(e.target.dataset.trend);
        }
    });

    // Toggle real-time updates
    document.getElementById('realtime-toggle').addEventListener('change', function () {
        if (this.checked) {
            startAutoRefresh();
        } else {
            clearTimeout(refreshTimer);
        }
    });

    // Load initial data for dropdowns
    loadRoomTypes();
    loadFloors();
}

// Initialize timer for auto-refresh
let refreshTimer;

/**
 * Start auto-refresh timer
 */
function startAutoRefresh() {
    // Clear any existing timer
    clearTimeout(refreshTimer);

    // Set a new timer for 30 seconds
    refreshTimer = setTimeout(function () {
        refreshDashboardData();
        startAutoRefresh(); // Restart the timer
    }, 30000); // 30 seconds
}

/**
 * Refresh dashboard data using current filters
 */
function refreshDashboardData() {
    const period = document.getElementById('period-filter').value;
    const roomType = document.getElementById('room-type-filter').value;

    if (period === 'custom') {
        const startDate = document.getElementById('start-date').value;
        const endDate = document.getElementById('end-date').value;
        if (startDate && endDate) {
            loadDashboardData('custom', startDate, endDate, roomType);
        } else {
            showAlert('Please select both start and end dates', 'error');
        }
    } else {
        loadDashboardData(period, null, null, roomType);
    }

    updateLastUpdated();
}

/**
 * Register chart plugins for no data display
 */
function registerChartPlugins() {
    try {
        // Safe way to check for plugin existence and register if needed
        let pluginExists = false;

        // Check if the plugin is already registered in a version-compatible way
        if (Chart.registry && Chart.registry.plugins) {
            // For Chart.js v3+
            pluginExists = Chart.registry.plugins.get('chartAreaNoDataText') !== undefined;
        } else if (Chart.plugins && Chart.plugins.getAll) {
            // For older Chart.js versions
            pluginExists = Chart.plugins.getAll().some(p => p.id === 'chartAreaNoDataText');
        }

        if (!pluginExists) {
            const noDataPlugin = {
                id: 'chartAreaNoDataText',
                beforeDraw: function (chart) {
                    if (chart.config.options && chart.config.options.elements && chart.config.options.elements.center) {
                        const ctx = chart.ctx;
                        const centerConfig = chart.config.options.elements.center;

                        ctx.save();
                        const fontSize = centerConfig.fontSize || 20;
                        ctx.font = fontSize + 'px ' + (centerConfig.fontStyle || 'Arial');
                        ctx.fillStyle = centerConfig.color || '#6c757d';
                        ctx.textAlign = 'center';
                        ctx.textBaseline = 'middle';

                        let centerX, centerY;

                        // Compatible way to get chart area center
                        if (chart.chartArea) {
                            centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                            centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
                        } else {
                            // Fallback for older versions
                            centerX = chart.width / 2;
                            centerY = chart.height / 2;
                        }

                        ctx.fillText(centerConfig.text, centerX, centerY);
                        ctx.restore();
                    }
                }
            };

            // Register plugin in a version-compatible way
            if (Chart.register) {
                // For Chart.js v3+
                Chart.register(noDataPlugin);
            } else if (Chart.plugins && Chart.plugins.register) {
                // For older Chart.js versions
                Chart.plugins.register(noDataPlugin);
            }
        }
    } catch (error) {
        console.error("Error registering chart plugins:", error);
    }
}

/**
 * Load dashboard data from API based on selected filters
 */
function loadDashboardData(period, startDate = '', endDate = '', roomType = 'all') {
    // Show loading indicators
    showLoading(true);

    // Build API URL with filters
    let apiUrl = '../api/dashboard/dashboard_data.php?period=' + period;
    if (period === 'custom') {
        apiUrl += '&start_date=' + startDate + '&end_date=' + endDate;
    }

    if (roomType && roomType !== 'all') {
        apiUrl += '&room_type=' + roomType;
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

                // Add these calls for the new charts
                if (response.data.occupancy_trend) {
                    renderOccupancyTrendChart(response.data.occupancy_trend, 'weekly');
                }
                if (response.data.revenue_occupancy) {
                    renderRevenueOccupancyChart(response.data.revenue_occupancy);
                }
                if (response.data.occupancy_forecast) {
                    renderOccupancyForecastChart(response.data.occupancy_forecast);
                }

                // Load real-time room grid
                loadRealTimeOccupancyGrid();

                // Update timestamp
                updateLastUpdated();
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
 * Function to load room types
 */
function loadRoomTypes() {
    // Placeholder for AJAX call to load room types
    const roomTypeSelect = document.getElementById('room-type-filter');

    fetch('../api/dashboard/get_room_types.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(type => {
                const option = document.createElement('option');
                option.value = type.id;
                option.textContent = type.name;
                roomTypeSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading room types:', error);
        });
}

/**
 * Function to load floors
 */
function loadFloors() {
    // Placeholder for AJAX call to load floors
    const floorFilter = document.getElementById('floor-filter');

    fetch('../api/dashboard/get_floors.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(floor => {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn btn-outline-primary';
                button.dataset.floor = floor.number;
                button.textContent = `Floor ${floor.number}`;
                floorFilter.appendChild(button);
            });
        })
        .catch(error => {
            console.error('Error loading floors:', error);
        });
}

/**
 * Function to filter rooms by floor
 */
function filterRoomsByFloor(floor) {
    const rooms = document.querySelectorAll('.room-block');

    if (floor === 'all') {
        rooms.forEach(room => {
            room.style.display = 'block';
        });
    } else {
        rooms.forEach(room => {
            if (room.dataset.floor === floor) {
                room.style.display = 'block';
            } else {
                room.style.display = 'none';
            }
        });
    }
}

/**
 * Function to load occupancy trend data
 */
function loadOccupancyTrendData(trendType) {
    // AJAX call to load trend data
    fetch(`../api/dashboard/get_occupancy_trends.php?type=${trendType}`)
        .then(response => response.json())
        .then(data => {
            updateOccupancyTrendChart(data, trendType);
        })
        .catch(error => {
            console.error('Error loading occupancy trend data:', error);
        });
}

/**
 * Update occupancy trend chart with new data
 */
function updateOccupancyTrendChart(data, trendType) {
    // If data is valid, render chart
    if (data && Object.keys(data).length > 0) {
        renderOccupancyTrendChart(data, trendType);
    } else {
        // Display no data message
        const canvas = document.getElementById('occupancy-trend-chart');
        if (canvas) {
            const ctx = canvas.getContext('2d');
            if (window.occupancyTrendChart) {
                window.occupancyTrendChart.destroy();
            }
            window.occupancyTrendChart = createNoDataChart(ctx, 'line');
        }
    }
}

/**
 * Update the dashboard statistics from API data
 */
function updateDashboardStats(data) {
    try {
        // Define default values if properties are missing
        const occupancy = data.occupancy || {};

        // Calculate occupancy rate with fallbacks for missing data
        const occupiedRooms = occupancy.occupied || 0;
        const availableRooms = occupancy.available || 0;
        const totalRooms = data.total_rooms || 0;
        const occupancyRate = totalRooms > 0 ? ((occupiedRooms / totalRooms) * 100).toFixed(1) : 0;

        // Calculate total bookings with fallback for missing data
        const bookings = data.bookings || {};
        const totalBookings = Object.values(bookings).reduce((sum, count) => sum + count, 0);

        // Update stat cards - use safe DOM update
        safeSetElementText('total-rooms', totalRooms);
        safeSetElementText('occupied-rooms', occupiedRooms);
        safeSetElementText('available-rooms', availableRooms);
        safeSetElementText('occupancy-rate', occupancyRate + '%');

        // Update booking status counts
        safeSetElementText('pending-bookings', bookings.pending || 0);
        safeSetElementText('confirmed-bookings', bookings.confirmed || 0);
        safeSetElementText('checked-in-bookings', bookings.checked_in || 0);
        safeSetElementText('checked-out-bookings', bookings.checked_out || 0);
    } catch (error) {
        console.error('Error updating dashboard stats:', error);
    }
}

/**
 * Safely set text content of an element, checking if it exists first
 */
function safeSetElementText(elementId, text) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = text;
    }
}

/**
 * Render the room occupancy pie chart
 */
function renderOccupancyChart(occupancyData) {
    try {
        const canvas = document.getElementById('occupancy-chart');
        if (!canvas) {
            console.error('Occupancy chart canvas not found');
            return;
        }

        const ctx = canvas.getContext('2d');

        // If chart already exists, destroy it
        if (window.occupancyChart) {
            window.occupancyChart.destroy();
        }

        // Check if data is available
        const hasData = occupancyData && Object.values(occupancyData).some(value => value > 0);

        if (!hasData) {
            // Display "No Data" message
            window.occupancyChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['No Data'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#f2f2f2'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    elements: {
                        center: {
                            text: 'No Data',
                            color: '#6c757d',
                            fontStyle: 'Arial',
                            sidePadding: 20,
                            fontSize: 20
                        }
                    }
                }
            });

            return;
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
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
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
    } catch (error) {
        console.error('Error rendering occupancy chart:', error);
    }
}

/**
 * Load and render the real-time room occupancy grid
 */
function loadRealTimeOccupancyGrid() {
    const gridContainer = document.getElementById('room-occupancy-grid');

    if (!gridContainer) {
        console.error('Room occupancy grid container not found');
        return;
    }

    // Show loading indicator
    gridContainer.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;

    // Fetch real-time room data
    fetch('../api/rooms/current_status.php')
        .then(response => response.json())
        .then(response => {
            if (response.success) {
                renderRoomGrid(response.data);
            } else {
                gridContainer.innerHTML = `<div class="alert alert-danger">Error loading room data: ${response.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error fetching room status data:', error);
            gridContainer.innerHTML = `<div class="alert alert-danger">Failed to load room data. Please try again.</div>`;
        });
}

/**
 * Render the room grid with the fetched data
 */
function renderRoomGrid(rooms) {
    const gridContainer = document.getElementById('room-occupancy-grid');

    if (!gridContainer || !rooms || !rooms.length) {
        if (gridContainer) {
            gridContainer.innerHTML = `<div class="alert alert-info">No room data available</div>`;
        }
        return;
    }

    // Clear previous content
    gridContainer.innerHTML = '';

    // Sort rooms by floor and number for organized display
    rooms.sort((a, b) => {
        if (a.floor !== b.floor) {
            return a.floor - b.floor;
        }
        return a.room_number.localeCompare(b.room_number, undefined, { numeric: true });
    });

    // Create room blocks
    rooms.forEach(room => {
        const roomBlock = document.createElement('div');
        roomBlock.className = `room-block ${room.status.toLowerCase()}`;
        roomBlock.dataset.floor = room.floor;
        roomBlock.dataset.roomId = room.id;

        // Add guest info if occupied
        let guestInfo = '';
        if (room.status.toLowerCase() === 'occupied' && room.guest) {
            guestInfo = `
                <div class="guest-info">
                    <strong>Guest:</strong> ${room.guest.name}<br>
                    <strong>Check-in:</strong> ${room.guest.check_in}<br>
                    <strong>Check-out:</strong> ${room.guest.check_out}
                </div>
            `;
        }

        roomBlock.innerHTML = `
            <div class="room-number">${room.room_number}</div>
            <div class="room-type">${room.type}</div>
            ${guestInfo}
        `;

        // Add click handler for room details
        roomBlock.addEventListener('click', () => showRoomDetails(room));

        gridContainer.appendChild(roomBlock);
    });
}

/**
 * Show detailed room information in a modal
 */
function showRoomDetails(room) {
    // Create modal HTML
    const modalContent = `
    <div class="modal-header">
        <h5 class="modal-title">Room ${room.room_number} Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
    </div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Floor:</strong> ${room.floor}</p>
                <p><strong>Type:</strong> ${room.type}</p>
                <p><strong>Status:</strong> <span class="badge bg-${getStatusColor(room.status)}">${room.status}</span></p>
                <p><strong>Last Cleaned:</strong> ${room.last_cleaned || 'N/A'}</p>
            </div>
            <div class="col-md-6">
                ${room.status.toLowerCase() === 'occupied' && room.guest ? `
                    <h6>Current Guest</h6>
                    <p><strong>Name:</strong> ${room.guest.name}</p>
                    <p><strong>Check-in:</strong> ${room.guest.check_in}</p>
                    <p><strong>Check-out:</strong> ${room.guest.check_out}</p>
                    <p><strong>Booking ID:</strong> ${room.guest.booking_id}</p>
                ` : ''}
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a href="../pages/rooms.php?room_id=${room.id}" class="btn btn-primary">Manage Room</a>
    </div>
    `;

    // Create modal element
    let modal = document.getElementById('room-details-modal');

    if (!modal) {
        modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.id = 'room-details-modal';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('aria-labelledby', 'room-details-modal-label');
        modal.setAttribute('aria-hidden', 'true');

        modal.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    ${modalContent}
                </div>
            </div>
        `;

        document.body.appendChild(modal);
    } else {
        const modalContentEl = modal.querySelector('.modal-content');
        if (modalContentEl) {
            modalContentEl.innerHTML = modalContent;
        }
    }

    // Initialize and show the modal
    const bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}

/**
 * Get the Bootstrap color class based on room status
 */
function getStatusColor(status) {
    const statusColors = {
        'available': 'success',
        'occupied': 'danger',
        'maintenance': 'secondary',
        'reserved': 'warning',
        'dirty': 'info'
    };

    return statusColors[status.toLowerCase()] || 'primary';
}

/**
 * Update last updated timestamp
 */
function updateLastUpdated() {
    const lastUpdated = document.getElementById('last-updated');
    if (lastUpdated) {
        const now = new Date();
        lastUpdated.textContent = 'Last updated: ' + now.toLocaleTimeString();
    }
}

/**
 * Render occupancy trend chart
 */
function renderOccupancyTrendChart(trendData, trendType) {
    const canvas = document.getElementById('occupancy-trend-chart');
    if (!canvas) {
        console.error('Occupancy trend chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');

    // Destroy existing chart if it exists
    if (window.occupancyTrendChart) {
        window.occupancyTrendChart.destroy();
    }

    // Check if data is available
    if (!trendData || Object.keys(trendData).length === 0) {
        // Display "No Data" message
        window.occupancyTrendChart = createNoDataChart(ctx, 'line');
        return;
    }

    // Prepare data for chart
    const labels = Object.keys(trendData);
    const occupancyRates = Object.values(trendData);

    // Calculate trend line (simple moving average)
    const trendLine = calculateMovingAverage(occupancyRates, 3);

    window.occupancyTrendChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Occupancy Rate (%)',
                    data: occupancyRates,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                    tension: 0.3
                },
                {
                    label: 'Trend',
                    data: trendLine,
                    backgroundColor: 'rgba(255, 99, 132, 0.1)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Occupancy Rate (%)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: getTrendTimeLabel(trendType)
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Get appropriate time label based on trend type
 */
function getTrendTimeLabel(trendType) {
    switch (trendType) {
        case 'weekly': return 'Week';
        case 'monthly': return 'Month';
        case 'quarterly': return 'Quarter';
        default: return 'Period';
    }
}

/**
 * Calculate moving average for trend line
 */
function calculateMovingAverage(data, window) {
    if (!data || data.length < window) return Array(data.length).fill(null);

    const result = [];

    // Start with nulls for the first window-1 points
    for (let i = 0; i < window - 1; i++) {
        result.push(null);
    }

    // Calculate moving average for the rest
    for (let i = window - 1; i < data.length; i++) {
        let sum = 0;
        for (let j = 0; j < window; j++) {
            sum += data[i - j];
        }
        result.push(sum / window);
    }

    return result;
}

/**
 * Render revenue vs occupancy chart
 */
function renderRevenueOccupancyChart(data) {
    const canvas = document.getElementById('revenue-occupancy-chart');
    if (!canvas) {
        console.error('Revenue occupancy chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');

    // Destroy existing chart if it exists
    if (window.revenueOccupancyChart) {
        window.revenueOccupancyChart.destroy();
    }

    // Check if data is available
    if (!data || !data.dates || !data.revenue || !data.occupancy) {
        // Display "No Data" message
        window.revenueOccupancyChart = createNoDataChart(ctx, 'line');
        return;
    }

    // Create chart
    window.revenueOccupancyChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.dates,
            datasets: [
                {
                    label: 'Revenue',
                    data: data.revenue,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    yAxisID: 'y',
                    tension: 0.3
                },
                {
                    label: 'Occupancy (%)',
                    data: data.occupancy,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 2,
                    yAxisID: 'y1',
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Revenue'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    min: 0,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Occupancy (%)'
                    },
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.dataset.label;
                            const value = context.raw;

                            if (label === 'Revenue') {
                                return label + ': $' + value.toLocaleString();
                            } else {
                                return label + ': ' + value.toFixed(1) + '%';
                            }
                        }
                    }
                }
            }
        }
    });
}

/**
 * Render occupancy forecast chart
 */
function renderOccupancyForecastChart(data) {
    const canvas = document.getElementById('occupancy-forecast-chart');
    if (!canvas) {
        console.error('Occupancy forecast chart canvas not found');
        return;
    }

    const ctx = canvas.getContext('2d');

    // Destroy existing chart if it exists
    if (window.occupancyForecastChart) {
        window.occupancyForecastChart.destroy();
    }

    // Check if data is available
    if (!data || !data.dates || !data.predicted || !data.confirmed) {
        // Display "No Data" message
        window.occupancyForecastChart = createNoDataChart(ctx, 'bar');
        return;
    }

    // Create chart
    window.occupancyForecastChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.dates,
            datasets: [
                {
                    label: 'Confirmed',
                    data: data.confirmed,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Predicted',
                    data: data.predicted,
                    backgroundColor: 'rgba(255, 206, 86, 0.8)',
                    borderColor: 'rgba(255, 206, 86, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    title: {
                        display: true,
                        text: 'Occupancy Rate (%)'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Date'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            return context.dataset.label + ': ' + context.parsed.y.toFixed(1) + '%';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Create a chart that displays "No Data"
 */
function createNoDataChart(ctx, chartType = 'line') {
    return new Chart(ctx, {
        type: chartType,
        data: {
            labels: ['No Data'],
            datasets: [{
                data: [0],
                backgroundColor: 'rgba(200, 200, 200, 0.2)',
                borderColor: 'rgba(200, 200, 200, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            },
            scales: {
                y: {
                    display: false
                },
                x: {
                    display: false
                }
            },
            elements: {
                center: {
                    text: 'No Data',
                    color: '#6c757d',
                    fontStyle: 'Arial',
                    fontSize: 20
                }
            }
        }
    });
}

/**
 * Render the daily occupancy line chart
 */
function renderDailyOccupancyChart(occupancyData) {
    try {
        const canvas = document.getElementById('daily-occupancy-chart');
        if (!canvas) {
            console.error('Daily occupancy chart canvas not found');
            return;
        }

        const ctx = canvas.getContext('2d');

        // If chart already exists, destroy it
        if (window.dailyOccupancyChart) {
            window.dailyOccupancyChart.destroy();
        }

        // Check if data is available
        const hasData = occupancyData && occupancyData.length > 0 &&
            (occupancyData.some(item => item.checkins > 0) ||
                occupancyData.some(item => item.checkouts > 0));

        if (!hasData) {
            // Display "No Data" message
            window.dailyOccupancyChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['No Data'],
                    datasets: [{
                        data: [0],
                        backgroundColor: '#f2f2f2',
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 1
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    elements: {
                        center: {
                            text: 'No Data',
                            color: '#6c757d',
                            fontStyle: 'Arial',
                            fontSize: 20
                        }
                    }
                }
            });

            return;
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
    } catch (error) {
        console.error('Error rendering daily occupancy chart:', error);
    }
}

/**
 * Render the room type distribution bar chart
 */
function renderRoomTypeDistribution(roomTypeData) {
    try {
        const canvas = document.getElementById('room-type-chart');
        if (!canvas) {
            console.error('Room type chart canvas not found');
            return;
        }

        const ctx = canvas.getContext('2d');

        // If chart already exists, destroy it
        if (window.roomTypeChart) {
            window.roomTypeChart.destroy();
        }

        // Check if data is available
        const hasData = roomTypeData && roomTypeData.length > 0;

        if (!hasData) {
            // Display "No Data" message
            window.roomTypeChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['No Data'],
                    datasets: [{
                        data: [0],
                        backgroundColor: '#f2f2f2',
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 1
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    elements: {
                        center: {
                            text: 'No Data',
                            color: '#6c757d',
                            fontStyle: 'Arial',
                            fontSize: 20
                        }
                    }
                }
            });

            return;
        }

        // Prepare data for chart
        const labels = roomTypeData.map(item => item.type);
        const totalData = roomTypeData.map(item => item.total);
        const occupiedData = roomTypeData.map(item => item.occupied);
        const availableData = roomTypeData.map(item => item.available);

        // Create chart
        window.roomTypeChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Rooms',
                        data: totalData,
                        backgroundColor: 'rgba(108, 117, 125, 0.7)',
                        borderColor: 'rgba(108, 117, 125, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Occupied',
                        data: occupiedData,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Available',
                        data: availableData,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
    } catch (error) {
        console.error('Error rendering room type distribution chart:', error);
    }
}

/**
 * Render the booking status pie chart
 */
function renderBookingStatusChart(bookingData) {
    try {
        const canvas = document.getElementById('booking-status-chart');
        if (!canvas) {
            console.error('Booking status chart canvas not found');
            return;
        }

        const ctx = canvas.getContext('2d');

        // If chart already exists, destroy it
        if (window.bookingStatusChart) {
            window.bookingStatusChart.destroy();
        }

        // Check if data is available
        const hasData = bookingData && Object.values(bookingData).some(value => value > 0);

        if (!hasData) {
            // Display "No Data" message
            window.bookingStatusChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['No Data'],
                    datasets: [{
                        data: [1],
                        backgroundColor: ['#f2f2f2'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    elements: {
                        center: {
                            text: 'No Data',
                            color: '#6c757d',
                            fontStyle: 'Arial',
                            fontSize: 20
                        }
                    }
                }
            });

            return;
        }

        // Prepare data for chart
        const labels = [];
        const data = [];
        const colors = [];

        // Map status to colors and labels
        const statusInfo = {
            'pending': { color: 'rgba(255, 193, 7, 0.7)', label: 'Pending' },
            'confirmed': { color: 'rgba(23, 162, 184, 0.7)', label: 'Confirmed' },
            'checked_in': { color: 'rgba(40, 167, 69, 0.7)', label: 'Checked In' },
            'checked_out': { color: 'rgba(108, 117, 125, 0.7)', label: 'Checked Out' },
            'cancelled': { color: 'rgba(220, 53, 69, 0.7)', label: 'Cancelled' }
        };

        // Add data for each status
        for (const status in bookingData) {
            const info = statusInfo[status] || { color: 'rgba(108, 117, 125, 0.7)', label: status.charAt(0).toUpperCase() + status.slice(1) };
            labels.push(info.label);
            data.push(bookingData[status]);
            colors.push(info.color);
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
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
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
    } catch (error) {
        console.error('Error rendering booking status chart:', error);
    }
}

/**
 * Show or hide loading indicators for charts
 */
function showLoading(isLoading) {
    const loadingElements = document.querySelectorAll('.chart-loading');
    loadingElements.forEach(element => {
        element.style.display = isLoading ? 'flex' : 'none';
    });
}

/**
 * Show alert message
 */
function showAlert(message, type = 'success') {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: type.charAt(0).toUpperCase() + type.slice(1),
            text: message,
            icon: type,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    } else {
        alert(message);
    }
}
