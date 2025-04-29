<!-- <?php
        // Login Page for PCC Hotel Reservation
        require_once 'config/db.php';
        header("Location: landing_page/");
        ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCC Hotel</title>
    <link rel="stylesheet" href="admin/css/style.css">
    <link rel="stylesheet" href="login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

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
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById("loginForm");

            loginForm.addEventListener("submit", function (e) {
                e.preventDefault();

                const formData = new FormData(loginForm);
                fetch('login_process.php', {
                    method: 'POST',
                    body: formData
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error("Network response was not ok");
                        }
                        return response.json(); // parse JSON
                    })
                    .then(function (data) {
                        if (data.status) {
                            if(data.role === "customer") 
                                {
                                    window.location.href = "landing_page/";
                                }
                            else if(data.role === "admin") {
                                window.location.href = "admin/pages/rooms.php";
                            }
                            else{
                                alert("user does not have a role.");
                            }
                        } else {
                            // show your error message
                            alert(data.message || "Login failed");
                        }
                    })
                    .catch(function (error) {
                        console.error("Fetch error:", error);
                        alert("Something went wrong. Please try again.");
                    });
            });
        });
    </script>
</body>

</html> -->