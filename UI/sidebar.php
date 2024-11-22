<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../USER-VERIFICATION/config/db.php'); // Ensure this path is correct and your database connection is working
include 'header.php';

// Check if the logout parameter is set
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    // Destroy the session and redirect to the login page or home page
    session_unset();
    session_destroy();
    header("Location: ../USER-VERIFICATION/login.php"); // Redirect to login page
    exit();
}

// Check if the user is logged in and fetch the user data
if (isset($_SESSION['unique_number'])) {
    $unique_number = $_SESSION['unique_number'];
    
    // Fetch user details from the database
    $query = "SELECT profile_picture, fullname FROM users WHERE unique_number = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $unique_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Set profile picture path and ensure it's not cached by adding a timestamp
    $profile_picture_path = !empty($user['profile_picture']) && file_exists($user['profile_picture']) 
        ? htmlspecialchars($user['profile_picture']) . '?' . time() 
        : '../USER-VERIFICATION/uploads/profile_picture/default-placeholder.png';
} else {
    $profile_picture_path = '../USER-VERIFICATION/uploads/profile_picture/default-placeholder.png';
}

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $upload_dir = '../USER-VERIFICATION/uploads/profile_picture/';
    $file_name = basename($file['name']);
    $target_path = $upload_dir . $file_name;
    
    // Ensure file upload is valid (add your validation checks here)
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        // Update the profile picture path in the database
        $query = "UPDATE users SET profile_picture = ? WHERE unique_number = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $target_path, $unique_number);
        $stmt->execute();

        // Update session and reload the page to reflect changes
        $_SESSION['profile_picture'] = $target_path;

        // Refresh the page to show updated picture
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $message = "Error uploading profile picture.";
        $messageType = "alert-danger";
    }
}
?>

<!-- Include the CSS for the sidebar -->
<link rel="stylesheet" href="../CSS/sidebar.css">
<link rel="stylesheet" href="../CSS/top-bar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="icon" type="image/png" href="../WEB/images/shlogo.png">

<!-- Sidebar navigation -->
<nav class="sidebar">
    <div class="user-icon">
        <img id="imagePreview" src="<?php echo $profile_picture_path; ?>" alt="Profile Picture Preview">
        <span class="user-fullname"><?php echo isset($_SESSION['fullname']) ? htmlspecialchars($_SESSION['fullname']) : ''; ?></span>
        <span class="user-unique-number"><?php echo isset($_SESSION['unique_number']) ? htmlspecialchars($_SESSION['unique_number']) : ''; ?></span>
    </div>

    <div class="divider"></div>

    <ul class="nav-links">
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'user_dashboard.php') ? 'active' : ''; ?>">
            <i class="fas fa-home nav-icon home"></i>
            <a href="../USER_DASHBOARD/user_dashboard.php">Dashboard</a>
        </li>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'edit_profile.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-user nav-icon profile"></i>
            <a href="../USER-VERIFICATION/edit_profile.php">Profile</a>
        </li>
        <div class="divider"></div>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'drive_locator.php' || basename($_SERVER['PHP_SELF']) == 'booking.php') ? 'active' : ''; ?>">
            <i class="fas fa-hand-holding-medical nav-icon"></i>
            <a href="../LOCATOR_MODULE/drive_locator.php">Donate</a>
        </li>
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_request.php' || basename($_SERVER['PHP_SELF']) == 'request_table.php') ? 'active' : ''; ?>">
            <i class="fa-solid fa-heart-pulse nav-icon receive"></i>
            <a href="../ADMIN_MODULE/manage_request.php">Receive</a>
        </li>
        
        <li class="<?php echo (basename($_SERVER['PHP_SELF']) == 'history.php') ? 'active' : ''; ?>">
            <i class="fas fa-history nav-icon history"></i>
            <a href="../USER-VERIFICATION/history.php">History</a>
        </li>
    </ul>

    <div class="divider"></div>

    <!-- Logout Link -->
    <div class="exit">
        <i class="fas fa-sign-out-alt nav-icon"></i>
        <a href="javascript:void(0);" onclick="showLogoutModal()">Logout</a>
    </div>
</nav>

<!-- Logout Confirmation Modal -->
<div class="modal" id="logoutModal">
    <div class="modal-content">
        <h2>Logout Confirmation</h2>
        <p>Are you sure you want to log out?</p>
        <div class="modal-actions">
            <button class="confirm-btn" onclick="confirmLogout()">Yes, Logout</button>
            <button class="cancel-btn" onclick="closeLogoutModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
    // Show the logout modal
    function showLogoutModal() {
        document.getElementById('logoutModal').style.display = 'flex';
    }

    // Close the logout modal
    function closeLogoutModal() {
        document.getElementById('logoutModal').style.display = 'none';
    }

    // Confirm logout and redirect to the logout URL
    function confirmLogout() {
        window.location.href = "../USER-VERIFICATION/index.php?logout=1";
    }
</script>

<script>
    // Image preview for file upload
    function previewImage(event) {
        const file = event.target.files[0];
        const reader = new FileReader();

        reader.onload = function (e) {
            // Update the image preview with the uploaded image
            document.getElementById('imagePreview').src = e.target.result;
        };

        if (file) {
            reader.readAsDataURL(file);
        }
    }
</script>
