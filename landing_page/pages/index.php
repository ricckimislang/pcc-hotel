<?php session_start();

$user_id = isset($_SESSION['user_id'])
    ? $_SESSION['user_id']
    : null;
?>
<!DOCTYPE html>
<html lang="en">

<?php include_once '../includes/head.php'; ?>

<body>
    <header>
        <div class="header-container">
            <div class="menu-button">
                <button id="menuToggle"><i class="fas fa-bars"></i> Menu</button>
                <span class="notification-dot" style="display: none;"></span>
                <div class="dropdown-menu" id="navDropdown">
                    <ul>
                        <?php if ($user_id) { ?>
                            <li><a href="profile.php">My Profile</a></li>
                            <li><a href="my_bookings.php">My Bookings</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
            <div class="logo">
                <span class="logo-full">PCC HOME SUITE HOME</span>
                <span class="logo-short">PCC HOME</span>
            </div>
            <?php if (!$user_id) {
                echo '
                <div class="login-button">
                <a href="login.php"><i class="fas fa-user"></i> Login</a>
                </div>
                    ';
            } else {
            ?>
                <div class="user-container">
                    <span><?php echo $_SESSION['username']; ?></span>
                </div>
            <?php } ?>

        </div>
    </header>

    <main>
        <section class="hero">
            <div class="hero-bg"></div>
            <div class="hero-content">
                <p class="hero-subtitle">Experience Luxury & Comfort</p>
                <h1 class="hero-title">PCC HOME SUITE HOME</h1>
                <!-- <div class="search-container">
                    <div class="search-box">
                        <div class="search-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <div class="search-text">
                                <span class="search-label">City or Destination</span>
                                <p>Oxford, Great Britain</p>
                            </div>
                        </div>
                        <div class="search-item">
                            <i class="far fa-calendar-alt"></i>
                            <div class="search-text">
                                <span class="search-label">Booking Dates</span>
                                <p>29 Oct 24 - 30 Oct 24</p>
                            </div>
                        </div>
                        <div class="search-item">
                            <i class="fas fa-user"></i>
                            <div class="search-text">
                                <span class="search-label">Guest & Rooms</span>
                                <p>2 Adults, 1 Room</p>
                            </div>
                        </div>
                        <button class="search-button" title="Search for available rooms">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div> -->
            </div>
        </section>

        <section class="travel-options">
            <h2>Exceptional Stays Await You</h2>
            <div class="booking-cta">
                <p>Discover the epitome of luxury accommodations at PCC Home Suite Home. Our meticulously designed rooms
                    and suites offer unparalleled comfort and elegance for your perfect getaway.</p>
            </div>
        </section>

        <section class="activities">
            <div class="activities-grid">
                <!-- dynamic cards -->
            </div>
        </section>
    </main>

    <footer class="luxury-footer">
        <div class="footer-content">
            <div class="footer-logo">
                <div class="logo">PCC HOME SUITE HOME</div>
                <p>Experience luxury beyond expectations</p>
            </div>
            <div class="footer-links">
                <div class="footer-column">
                    <h4>Rooms & Suites</h4>
                    <ul>
                        <li><a href="#">Luxury Twin</a></li>
                        <li><a href="#">Executive Suite</a></li>
                        <li><a href="#">Deluxe King</a></li>
                        <li><a href="#">Penthouse</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h4>Contact Us</h4>
                    <ul>
                        <li><a href="#">+63 123-456-7890</a></a></li>
                        <li><a href="#">codingriccki@gmail.com</a></li>
                        <div class="social-icons">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                        </div>
                    </ul>
                </div>
            </div>

        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 PCC Home Suite Home. All rights reserved.</p>
            <span>codingriccki@gmail.com </span>
            <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        </div>
    </footer>

    <script src="../js/main.js"></script>
    <script src="../js/rooms.js"></script>
</body>

</html>