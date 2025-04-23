
<div class="sidebar">
    <div class="sidebar-header">
        <h3>PCC Hotel</h3>
        <div class="sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </div>
    </div>

    <div class="sidebar-user">
        <div class="user-image">
            <img src="" alt="Admin">
        </div>
        <div class="user-info">
            <h5>Admin User</h5>
            <span>Administrator</span>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <a href="../pages/dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="menu-header">Room Management</li>

        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : ''; ?>">
            <a href="../pages/rooms.php">
                <i class="fas fa-door-open"></i>
                <span>Rooms</span>
            </a>
        </li>

        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'room_types.php' ? 'active' : ''; ?>">
            <a href="../pages/room_types.php">
                <i class="fas fa-tags"></i>
                <span>Room Types</span>
            </a>
        </li>

        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'room_media.php' ? 'active' : ''; ?>">
            <a href="../pages/room_media.php">
                <i class="fas fa-images"></i>
                <span>Room Media</span>
            </a>
        </li>

        <li class="menu-header">Booking Management</li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
            <a href="../pages/bookings.php">
                <i class="fas fa-calendar-check"></i>
                <span>Bookings</span>
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'payments.php' ? 'active' : ''; ?>">
            <a href="../pages/payments.php">
                <i class="fas fa-money-bill-wave"></i>
                <span>Payments</span>
            </a>
        </li>

        <li class="menu-header">Customer Management</li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">
            <a href="../pages/customers.php">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'feedback.php' ? 'active' : ''; ?>">
            <a href="../pages/feedback.php">
                <i class="fas fa-comment-alt"></i>
                <span>Feedback</span>
            </a>
        </li>

        <li class="menu-header">Reports</li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'analytics.php' ? 'active' : ''; ?>">
            <a href="../pages/analytics.php">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
        </li>

        <li class="menu-header">Settings</li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
            <a href="../pages/profile.php">
                <i class="fas fa-user-cog"></i>
                <span>Profile</span>
            </a>
        </li>
        <li>
            <a href="../index.php?logout=1">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<script>
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('collapsed');
        document.querySelector('.main-content').classList.toggle('expanded');
    });
</script>