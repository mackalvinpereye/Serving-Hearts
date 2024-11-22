<?php
session_start();

require_once('../USER-VERIFICATION/config/db.php');

if (isset($_GET['id'])) {
    $notificationId = $_GET['id'];

    // Prepare the statement to update the notification status
    $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Notification marked as read.";
    } else {
        echo "Error marking notification as read.";
    }

    $stmt->close();
} else {
    echo "No notification ID provided.";
}
?>
