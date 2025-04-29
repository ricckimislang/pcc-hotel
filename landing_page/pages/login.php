<?php
// Login Page for PCC Hotel Reservation
require_once '../../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/login.css">
<?php include_once '../includes/head.php'; ?>

<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Welcome Back</h1>
            <form id="loginForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-login">Sign In</button>
            </form>
            <div class="register-link">
                <span>Don't have an account? <a href="register.php">Register</a></span>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.getElementById("loginForm");

            loginForm.addEventListener("submit", function(e) {
                e.preventDefault();

                const formData = new FormData(loginForm);
                fetch('../api/login_process.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error("Network response was not ok");
                        }
                        return response.json(); // parse JSON
                    })
                    .then(function(data) {
                        if (data.status) {
                            if (data.role === "customer") {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Login successful!',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "index.php";
                                    }
                                });
                            } else if (data.role === "admin") {
                                Swal.fire({
                                    title: 'Success!',
                                    text: 'Login successful!',
                                    icon: 'success',
                                    confirmButtonText: 'OK'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "http://localhost/pcc-hotel/admin/pages/dashboard.php";
                                    }
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'User does not have a role.',
                                    icon: 'error',
                                    confirmButtonText: 'OK'
                                });
                            }
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Login failed',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(function(error) {
                        console.error("Fetch error:", error);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong. Please try again.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    });
            });
        });
    </script>
</body>

</html>