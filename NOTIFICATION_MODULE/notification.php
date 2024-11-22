<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

require_once('../USER-VERIFICATION/config/db.php');

$user = $_SESSION['unique_number'];

// Query to count unread notifications for the logged-in user
$unreadQuery = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE unique_number = ? AND is_read = 0");
$unreadQuery->bind_param("s", $user);
$unreadQuery->execute();
$unreadResult = $unreadQuery->get_result();
$unreadData = $unreadResult->fetch_assoc();
$unreadCount = $unreadData['unread_count'] ?? 0; // Set to 0 if unread count is not found

// Prepare the statement to fetch notifications for the logged-in user
$result = $conn->prepare("SELECT * FROM notifications WHERE unique_number = ? ORDER BY timestamp DESC");
$result->bind_param("s", $user);
$result->execute();
$notifications = $result->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <style>
        /* Notification icon styling */
        .notification-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
            margin-left: 1400px;
        }

        .notification-icon {
            width: 30px;
            height: 30px;
        }

        /* Badge styling for unread messages */
        .badge {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            position: absolute;
            top: -5px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 100%;
            padding: 5px 1px;
            font-size: 12px;
            line-height: 1;
            min-width: 20px;
            text-align: center;
            font-weight: bold;
        }

        /* Dropdown styling */
        .dropdown {
            font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
            display: none;
            position: absolute;
            top: 55px; 
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            width: 250px;
            z-index: 10;
        }

        /* Styling for each notification item */
        .dropdown-item {
            padding: 10px;
            border-bottom: 1px solid #f1f1f1;
            font-size: 14px;
            cursor: pointer;
        }

        .dropdown-item:last-child {
            border-bottom: none;
        }

        /* Timestamp styling */
        .timestamp {
            display: block;
            font-size: 12px;
            color: #888;
        }
    </style>

<script>
    // Function to toggle the dropdown visibility
    function toggleDropdown(event) {
        const dropdown = document.getElementById('notificationDropdown');
        const badge = document.querySelector('.badge');
        
        // Toggle dropdown visibility
        dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
        
        // If the dropdown is being opened, mark the notifications as read
        if (dropdown.style.display === 'block') {
            // Send AJAX request to mark notifications as read
            fetch('mark_as_read.php', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ id: <?php echo $notificationId; ?> })
            })
            .then(response => response.text())
            .then(data => {
                // After marking as read, remove the red badge (if there are no more unread notifications)
                badge.textContent = 0; // Assuming `unreadCount` will be updated accordingly
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });
        }
    }

    // Close the dropdown if clicked outside
    document.addEventListener('click', function (event) {
        const container = document.querySelector('.notification-container');
        const dropdown = document.getElementById('notificationDropdown');
        if (!container.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Function to redirect the user when a notification is clicked
    function redirectToView(notificationId) {
        // Send AJAX request to mark the notification as read
        fetch('mark_as_read.php?id=' + notificationId)
            .then(response => response.text())
            .then(data => {
                // Update the badge after the notification is marked as read
                const badge = document.querySelector('.badge');
                let unreadCount = parseInt(badge.textContent);
                unreadCount = unreadCount > 0 ? unreadCount - 1 : 0;
                badge.textContent = unreadCount;
            })
            .catch(error => {
                console.error('Error marking notification as read:', error);
            });

        // Redirect to the notification view page
        window.location.href = `view_notification.php?id=${notificationId}`;
    }
</script>


</head>
<body>
    <div class="notification-container" onclick="toggleDropdown()">
        <!-- Notification Icon and Unread Badge -->
        <span class="badge"><?php echo $unreadCount; ?></span> <!-- Display unread count -->

        <!-- Dropdown showing recent notifications -->
        <div id="notificationDropdown" class="dropdown">
            <?php
            // PHP to fetch and display recent notifications
            $recentNotifications = $conn->prepare("SELECT id, subject, timestamp FROM notifications WHERE unique_number = ? ORDER BY timestamp DESC LIMIT 5");
            $recentNotifications->bind_param("s", $user);
            $recentNotifications->execute();
            $recentResults = $recentNotifications->get_result();

            if ($recentResults->num_rows > 0) {
                while ($row = $recentResults->fetch_assoc()) {
                    echo "<div class='dropdown-item' onclick='redirectToView({$row['id']})'>";
                    echo "<strong>{$row['subject']}</strong>";
                    echo "<span class='timestamp'>{$row['timestamp']}</span>";
                    echo "</div>";
                }
            } else {
                // Wrap the "No new notifications" message in an anchor tag
                echo "<div class='dropdown-item'><a href='view_notification.php' class='no-notifications' onclick='event.stopPropagation();'>No new notifications</a></div>";
            }
            ?>
        </div>
    </div>
</body>
</html>

<script>
    // Periodically update the unread notification count
    setInterval(function() {
        fetchUnreadCount();
    }, 30000); // Refresh every 30 seconds

    // Fetch the current unread count from the server
    function fetchUnreadCount() {
        fetch('get_unread_count.php') // Assuming you create a separate PHP endpoint for fetching the count
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('notification-badge');
                badge.textContent = data.unread_count; // Update the badge count
            })
            .catch(error => console.error('Error fetching unread count:', error));
    }
</script>
