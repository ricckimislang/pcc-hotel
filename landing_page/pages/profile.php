<?php
session_start();
require_once '../../config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$successMessage = "";
$errorMessage = "";


// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = $conn->real_escape_string($_POST['first_name']);
    $lastName = $conn->real_escape_string($_POST['last_name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone_number']);

    // Update profile information
    $updateQuery = "UPDATE users SET 
                    first_name = '$firstName', 
                    last_name = '$lastName', 
                    email = '$email', 
                    phone_number = '$phone', 
                    updated_at = NOW() 
                    WHERE user_id = $userId";

    if ($conn->query($updateQuery) === TRUE) {
        $successMessage = "Profile updated successfully!";
    } else {
        $errorMessage = "Error updating profile: " . $conn->error;
    }

    // Handle profile image upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['profile_image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);

        // Verify file extension
        if (in_array(strtolower($filetype), $allowed)) {
            // Create unique filename
            $newFilename = 'profile_' . $userId . '_' . time() . '.' . $filetype;
            $uploadDir = '../../public/profile_images/';

            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $uploadPath = $uploadDir . $newFilename;

            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                // Update profile image in database
                $relativeImagePath = 'profile_images/' . $newFilename;
                $updateImageQuery = "UPDATE users SET profile_image = '$relativeImagePath' WHERE user_id = $userId";

                if ($conn->query($updateImageQuery) === TRUE) {
                    $successMessage .= " Profile image updated successfully!";
                    header("Location: profile.php");
                    exit();
                } else {
                    $errorMessage .= " Error updating profile image in database.";
                }
            } else {
                $errorMessage .= " Error uploading image.";
            }
        } else {
            $errorMessage .= " Invalid file type. Allowed types: " . implode(', ', $allowed);
        }
    }
}

// Fetch user data after potential updates
$query = "SELECT * FROM users WHERE user_id = $userId";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    die("User not found");
}

// Fetch customer profile data if exists
$customerQuery = "SELECT * FROM customer_profiles WHERE user_id = $userId";
$customerResult = $conn->query($customerQuery);
$customerProfile = null;

if ($customerResult->num_rows > 0) {
    $customerProfile = $customerResult->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | PCC Hotel</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Raleway:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/profile.css">
</head>

<body>

    <div class="container profile-container">
        <div class="text-start p-3">
            <a href="../index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Home
            </a>
        </div>
        <?php if (!empty($successMessage)): ?>
            <div class="alert alert-luxury-success alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo $successMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessage)): ?>
            <div class="alert alert-luxury-danger alert-dismissible fade show m-3" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $errorMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="profile-header text-center">
            <h1 class="mb-3">My Profile</h1>
            <div class="profile-image-container">
                <img src="<?php echo isset($user['profile_image']) && !empty($user['profile_image']) ? '../../public/' . $user['profile_image'] : '../../publoads/profile_images/default-profile.jpg'; ?>"
                    class="profile-image" id="profile_image_preview" alt="Profile Image">
                <label for="profile_image_input" class="image-upload-btn">
                    <i class="fas fa-camera"></i>
                </label>
            </div>
            <h2 class="mt-4"><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></h2>
            <?php if ($customerProfile && $customerProfile['frequent_guest']): ?>
                <span class="badge badge-luxury mt-2">
                    <i class="fas fa-crown me-1"></i> Frequent Guest
                </span>
            <?php endif; ?>
        </div>

        <div class="profile-info">
            <div class="row">
                <div class="col-md-8">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="file" id="profile_image_input" name="profile_image" style="display: none;" onchange="previewImage(this)">

                        <div id="image_selected" class="mb-3 small text-muted"></div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $user['first_name']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $user['last_name']; ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="phone_number" class="form-label">Phone Number</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo $user['phone_number']; ?>">
                        </div>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" readonly>
                            <small class="text-muted">Username cannot be changed</small>
                        </div>

                        <div class="mb-3">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#updatePasswordModal">
                                <i class="fas fa-key me-2"></i> Change Password
                            </button>
                        </div>

                        <div class="divider"></div>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Changes
                        </button>
                    </form>
                </div>

                <div class="col-md-4">
                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <h4>Bookings</h4>
                        <p class="mb-0">Total bookings: <strong>
                                <?php
                                // Get total bookings for display
                                $bookingQuery = "SELECT COUNT(*) as total FROM bookings WHERE user_id = $userId";
                                $bookingResult = $conn->query($bookingQuery);
                                $bookingCount = $bookingResult->fetch_assoc()['total'];
                                echo $bookingCount;
                                ?>
                            </strong></p>
                    </div>

                    <?php if ($customerProfile): ?>
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-gem"></i>
                            </div>
                            <h4>Loyalty Points</h4>
                            <p class="mb-0">Current points: <strong><?php echo $customerProfile['loyal_points']; ?></strong></p>

                            <?php
                            $nextLevel = 100; // Example threshold for next level
                            $progress = min(100, ($customerProfile['loyal_points'] / $nextLevel) * 100);
                            ?>

                            <div class="loyalty-progress">
                                <div class="loyalty-progress-bar" style="width: <?php echo $progress; ?>%"></div>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <?php echo $customerProfile['loyal_points']; ?> / <?php echo $nextLevel; ?> points to get 5% discount
                            </small>
                        </div>
                    <?php endif; ?>

                    <div class="stats-card">
                        <div class="stats-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h4>Member Since</h4>
                        <p class="mb-0">
                            <strong>
                                <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
                            </strong>
                        </p>
                        <small class="text-muted">
                            <?php
                            $created = new DateTime($user['created_at']);
                            $now = new DateTime();
                            $diff = $created->diff($now);
                            echo $diff->y > 0 ? $diff->y . ' years' : $diff->m . ' months';
                            ?> with us
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Password Update Modal -->
    <div class="modal fade" id="updatePasswordModal" tabindex="-1" aria-labelledby="updatePasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updatePasswordModalLabel">Update Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updatePasswordForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" name="update_password">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Password validation
        document.getElementById('updatePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const currentPassword = document.getElementById('currentPassword').value;

            if (newPassword !== confirmPassword) {
                alert('New passwords do not match!');
                return false;
            }

            $.ajax({
                url: '../api/change-pass.php',
                type: 'POST',
                data: {
                    current_password: currentPassword,
                    new_password: newPassword
                },
                success: function(response) {
                    if (response.success) {
                        alert('Password updated successfully!');
                        $('#updatePasswordModal').modal('hide');
                        document.getElementById('updatePasswordForm').reset();
                    } else {
                        alert('Error updating password: ' + response.message);
                    }
                },
                error: function() {
                    alert('An error occurred while updating the password.');
                }
            });
        });

        // Image preview function
        function previewImage(input) {
            const preview = document.getElementById('profile_image_preview');
            const imageSelected = document.getElementById('image_selected');

            if (input.files && input.files[0]) {
                const fileName = input.files[0].name;
                imageSelected.textContent = fileName;

                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</body>

</html>