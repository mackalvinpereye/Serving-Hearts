<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}
include '../UI/sidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

$user = $_SESSION['unique_number'];

// Prepare the statement to fetch notifications for the logged-in user
$result = $conn->prepare("SELECT * FROM notifications WHERE unique_number = ? ORDER BY timestamp DESC");
$result->bind_param("s", $user); // Bind the unique_number parameter
$result->execute();
$notifications = $result->get_result(); // Get the result set

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Notifications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            padding-top: 50px; /* Adjust this value to move everything down */
        }

        .notification {
            position: relative;
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 1000px;
            margin-bottom: 10px;
            margin-left: 23rem;
            transition: background-color 0.3s;
            cursor: pointer;
        }

        .notification:hover {
            background-color: #BEBEBE;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
        }

        .notification-header strong {
            font-size: 1.1em;
            color: #333;
        }

        .notification-body {
            display: none; /* Initially hide the message body */
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
        }

        .timestamp {
            font-size: 0.8em;
            color: #888;
        }

        .badge {
            position: absolute;
            top: -3px;
            right: -10px;
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px;
            font-size: 10px;
            min-width: 10px;
            text-align: center;
        }

        button {
            background-color: #d9534f;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }   
    </style>
</head>
<body>

<h2>User Notifications</h2>

<?php
// Fetch and display notifications
if ($notifications->num_rows > 0) {
    while ($row = $notifications->fetch_assoc()) {
        echo "<div class='notification' onclick=\"toggleMessage(this, {$row['id']});\">";
        
        // Show red badge for unread notifications
        if ($row['is_read'] == 0) {
            echo "<span class='badge'>!</span>"; // Added a character for visibility
        }
        
        echo "<div class='notification-header'>";
        echo "<strong>{$row['subject']}</strong>";
        echo "<span class='timestamp'>{$row['timestamp']}</span>";
        echo "</div>";
        echo "<div class='notification-body'>";
        echo "<p>{$row['message']}</p>";
        echo "<button onclick=\"deleteNotification({$row['id']}, this.closest('.notification')); event.stopPropagation();\">Delete</button>";
        echo "</div>";
        echo "</div>";
    }
} else {
    echo "<p><a href='view_notification.php' class='no-notifications'>No new notifications</a></p>";
}

// Close the statement
$result->close();
?>


<script>
// Toggle message display and mark as read when the notification is clicked
function toggleMessage(notification, notificationId) {
    const body = notification.querySelector('.notification-body');
    const badge = notification.querySelector('.badge');
    
    // Check if the body is currently displayed
    const isDisplayed = body.style.display === 'block';

    // Toggle visibility of the body
    body.style.display = isDisplayed ? 'none' : 'block'; // Hide if currently displayed, show if not

    // If the message is not displayed, mark notification as read
    if (!isDisplayed) {
        // Mark notification as read
        fetch('mark_as_read.php?id=' + notificationId)
            .then(response => {
                if (!response.ok) {
                    console.error('Error marking notification as read.');
                } else {
                    // Hide red badge for unread notifications
                    if (badge) badge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    }
}

// Delete notification and remove it from the DOM
function deleteNotification(notificationId, notificationElement) {
    fetch('delete_notification.php?id=' + notificationId, {
        method: 'DELETE' // Specify the method
    })
    .then(response => response.json()) // Parse the JSON response
    .then(data => {
        if (data.success) {
            // Hide the notification element from the DOM
            notificationElement.remove();
        } else {
            alert('Error deleting notification: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>


</body>
</html>
