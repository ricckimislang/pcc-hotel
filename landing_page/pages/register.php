<?php
// Register Page for PCC Hotel Reservation
require_once '../../config/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="../css/register.css">
<?php include_once '../includes/head.php'; ?>

<style>
    .password-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.password-wrapper input {
    width: 100%;
    padding-right: 40px; /* Space for the icon */
    box-sizing: border-box;
}

.password-wrapper .toggle-password {
    position: absolute;
    right: 10px;
    cursor: pointer;
    color: #555;
    font-size: 1rem;
}

</style>
<body>
    <div class="register-container">
        <div class="register-box">
            <h1>Create Account</h1>
            <form id="registerForm">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-section">
                            <h2>Personal Information</h2>
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
                                <input type="tel" id="contact_number" name="contact_number" pattern="[0-9]{11}" placeholder="09XXXXXXXXX" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email" id="email" name="email" required>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-section">
                            <h2>Account Security</h2>
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="password" name="password" required>
                                    <i class="fas fa-eye toggle-password" data-target="password"></i>
                                </div>
                                <div class="password-strength"></div>
                                <div class="password-requirements">
                                    <ul class="requirements-list">
                                        <li id="length" class="requirement"><i class="fas fa-times"></i> At least 8 characters</li>
                                        <li id="uppercase" class="requirement"><i class="fas fa-times"></i> One uppercase letter</li>
                                        <li id="lowercase" class="requirement"><i class="fas fa-times"></i> One lowercase letter</li>
                                        <li id="number" class="requirement"><i class="fas fa-times"></i> One number</li>
                                        <li id="special" class="requirement"><i class="fas fa-times"></i> One special character</li>
                                    </ul>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="confirm_password">Confirm Password</label>
                                <div class="password-wrapper">
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                    <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-register">Create Account</button>
            </form>
            <div class="login-link">
                <span>Already have an account? <a href="login.php">Login</a></span>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById("registerForm");
            const password = document.getElementById("password");
            const confirmPassword = document.getElementById("confirm_password");
            const passwordStrength = document.querySelector(".password-strength");

            // Password requirements elements
            const requirements = {
                length: document.getElementById("length"),
                uppercase: document.getElementById("uppercase"),
                lowercase: document.getElementById("lowercase"),
                number: document.getElementById("number"),
                special: document.getElementById("special")
            };

            // Password validation patterns
            const patterns = {
                length: /.{8,}/,
                uppercase: /[A-Z]/,
                lowercase: /[a-z]/,
                number: /[0-9]/,
                special: /[!@#$%^&*(),.?":{}|<>]/
            };

            // Update requirement status
            function updateRequirement(requirement, isValid) {
                const icon = requirement.querySelector('i');
                if (isValid) {
                    icon.className = 'fas fa-check';
                    requirement.classList.add('valid');
                    requirement.classList.remove('invalid');
                } else {
                    icon.className = 'fas fa-times';
                    requirement.classList.add('invalid');
                    requirement.classList.remove('valid');
                }
            }

            // Check password strength
            function checkPasswordStrength(value) {
                let strength = 0;
                let validRequirements = 0;

                // Check each requirement
                for (const [key, pattern] of Object.entries(patterns)) {
                    const isValid = pattern.test(value);
                    updateRequirement(requirements[key], isValid);
                    if (isValid) validRequirements++;
                }

                // Calculate strength based on valid requirements
                strength = (validRequirements / Object.keys(patterns).length) * 100;

                // Update strength indicator
                passwordStrength.className = "password-strength";
                if (strength <= 40) {
                    passwordStrength.classList.add("weak");
                } else if (strength <= 80) {
                    passwordStrength.classList.add("medium");
                } else {
                    passwordStrength.classList.add("strong");
                }

                return validRequirements === Object.keys(patterns).length;
            }

            // Password input handler
            password.addEventListener("input", function() {
                const value = password.value;
                checkPasswordStrength(value);
            });

            // Confirm password validation
            confirmPassword.addEventListener("input", function() {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords do not match");
                } else {
                    confirmPassword.setCustomValidity("");
                }
            });

            // Form submission handler
            registerForm.addEventListener("submit", function(e) {
                e.preventDefault();

                // Check if password meets all requirements
                if (!checkPasswordStrength(password.value)) {
                    Swal.fire({
                        title: 'Password Requirements Not Met',
                        text: 'Please ensure your password meets all the requirements.',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

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

            const togglePasswordIcons = document.querySelectorAll('.toggle-password');

            togglePasswordIcons.forEach(icon => {
                icon.addEventListener('click', function() {
                    const target = document.getElementById(this.dataset.target);
                    if (target.type === 'password') {
                        target.type = 'text';
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    } else {
                        target.type = 'password';
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    }
                });
            });
        });
    </script>
</body>

</html>