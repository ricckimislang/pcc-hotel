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
                            if(data.role === "customer"){
                                window.location.href = "index.php";
                            }
                            else if(data.role === "admin"){
                                window.location.href = "http://localhost/pcc-hotel/admin/pages/dashboard.php";
                            }
                            else{
                                alert("user does not have a role.");
                            }
                        } else {
                            // show your error message
                            alert(data.message || "Login failed");
                        }
                    })
                    .catch(function(error) {
                        console.error("Fetch error:", error);
                        alert("Something went wrong. Please try again.");
                    });
            });
        });
    </script>
</body>

</html>