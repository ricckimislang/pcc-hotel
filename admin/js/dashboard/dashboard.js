// Dashboard JavaScript for PCC Hotel
document.addEventListener('DOMContentLoaded', function() {
    // Register chart plugins - do this only once
    registerChartPlugins();
    
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
    if (typeof flatpickr !== 'undefined') {
        flatpickr('#start-date', {
            dateFormat: 'Y-m-d',
            maxDate: 'today'
        });
        
        flatpickr('#end-date', {
            dateFormat: 'Y-m-d',
            maxDate: 'today'
        });
    }
});

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
                beforeDraw: function(chart) {
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
    try {
        // Calculate occupancy rate
        const occupiedRooms = data.occupancy.occupied || 0;
        const occupancyRate = data.total_rooms > 0 ? ((occupiedRooms / data.total_rooms) * 100).toFixed(1) : 0;
        
        // Calculate total bookings
        const totalBookings = Object.values(data.bookings).reduce((sum, count) => sum + count, 0);
        
        // Update stat cards - use safe DOM update
        safeSetElementText('total-rooms', data.total_rooms);
        safeSetElementText('occupied-rooms', occupiedRooms);
        safeSetElementText('available-rooms', data.occupancy.available || 0);
        safeSetElementText('occupancy-rate', occupancyRate + '%');
        safeSetElementText('total-bookings', totalBookings);
        
        // Update booking status counts
        safeSetElementText('pending-bookings', data.bookings.pending || 0);
        safeSetElementText('confirmed-bookings', data.bookings.confirmed || 0);
        safeSetElementText('checked-in-bookings', data.bookings.checked_in || 0);
        safeSetElementText('checked-out-bookings', data.bookings.checked_out || 0);
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
    } catch (error) {
        console.error('Error rendering occupancy chart:', error);
    }
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
