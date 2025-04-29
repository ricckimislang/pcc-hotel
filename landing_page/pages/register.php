<?php
// Register Page for PCC Hotel Reservation
require_once '../../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/login.css">
<?php include_once '../includes/head.php'; ?>

<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Create Account</h1>
            <form id="registerForm">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="number" id="contact_number" name="contact_number" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn-login">Register</button>
            </form>
            <div class="register-link">
                <span>Already have an account? <a href="login.php">Login</a></span>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById("registerForm");
            const password = document.getElementById("password");
            const confirmPassword = document.getElementById("confirm_password");

            confirmPassword.addEventListener("input", function() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords do not match");
                } else {
                    confirmPassword.setCustomValidity("");
                }
            });

            registerForm.addEventListener("submit", function(e) {
                e.preventDefault();

                const formData = new FormData(registerForm);
                fetch('../api/register_process.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        if (!response.ok) {
                            throw new Error("Network response was not ok");
                        }
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.status) {
                            Swal.fire({
                                title: 'Success!',
                                text: 'Registration successful! Please login.',
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "login.php";
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message || 'Registration failed',
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