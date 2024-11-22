<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

require_once('../USER-VERIFICATION/config/db.php');

// Get the unique number of the logged-in user
$user = $_SESSION['unique_number'];

// Query to count unread notifications for the logged-in user
$unreadQuery = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE unique_number = ? AND is_read = 0");
$unreadQuery->bind_param("s", $user);
$unreadQuery->execute();
$unreadResult = $unreadQuery->get_result();
$unreadData = $unreadResult->fetch_assoc();
$unreadCount = $unreadData['unread_count'] ?? 0; // Set to 0 if unread count is not found
?>

<link rel="stylesheet" href="../CSS/top-bar.css">
<link rel="icon" type="image/png" href="../WEB/images/shlogo.png">

<header class="main-header">
    <div class="logo-container">
        <img src="../WEB/images/logo.png" alt="Serving Hearts Logo" class="logo">
    </div>
    <div class="notification-container" onclick="toggleDropdown()">
        <i class="fa-solid fa-bell notification-icon"></i>
        <?php if ($unreadCount > 0): ?>
            <span class="badge"><?php echo $unreadCount; ?></span> <!-- Display unread count only if greater than zero -->
        <?php endif; ?>

        <div class="dropdown" id="notification-dropdown">
            <?php
            // Fetch and display recent unread notifications
            $recentNotifications = $conn->prepare("SELECT id, subject, timestamp FROM notifications WHERE unique_number = ? AND is_read = 0 ORDER BY timestamp DESC LIMIT 5");
            $recentNotifications->bind_param("s", $user);
            $recentNotifications->execute();
            $recentResults = $recentNotifications->get_result();

            if ($recentResults->num_rows > 0) {
                while ($row = $recentResults->fetch_assoc()) {
                    echo "<div class='dropdown-item' onclick=\"window.location.href='/Serving%20Hearts/NOTIFICATION_MODULE/view_notification.php?id={$row['id']}';\">";
                    echo "<strong>{$row['subject']}</strong>";
                    echo "<span class='timestamp'>{$row['timestamp']}</span>";
                    echo "</div>";
                }
            } else {
                echo "<div class='dropdown-item'>No new notifications</div>";
            }
            ?>
        </div>
    </div>
</header>

<script>
    // Function to toggle the dropdown visibility
    function toggleDropdown() {
        var dropdown = document.getElementById('notification-dropdown');
        dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
    }

    // Close the dropdown if clicked outside of the notification container
    window.onclick = function(event) {
        const dropdown = document.getElementById('notification-dropdown');
        const notificationContainer = document.querySelector('.notification-container');
        if (!notificationContainer.contains(event.target)) {
            dropdown.style.display = "none";
        }
    }
</script>

<script>
    // Function to fetch the unread notification count
    function fetchUnreadCount() {
        fetch('../NOTIFICATION_MODULE/get_unread_count.php')
            .then(response => response.json())
            .then(data => {
                const unreadCount = data.unread_count || 0; // Default to 0 if not found
                const badge = document.querySelector('.badge');
                
                if (badge) {
                    if (unreadCount > 0) {
                        badge.textContent = unreadCount; // Update the badge with the new unread count
                        badge.style.display = "inline-block"; // Ensure the badge is visible
                    } else {
                        badge.style.display = "none"; // Hide the badge if unread count is 0
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching unread count:', error);
            });
    }

    // Set up the interval to check for unread notifications every 2 seconds
    setInterval(fetchUnreadCount, 100);
</script>
