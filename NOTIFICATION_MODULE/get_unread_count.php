<?php
session_start();
require_once('../USER-VERIFICATION/config/db.php');

// Check if the user is logged in
if (isset($_SESSION['unique_number'])) {
    $user = $_SESSION['unique_number']; // Get the logged-in user's unique number
    
    // Query to count unread notifications for the logged-in user
    $unreadQuery = $conn->prepare("SELECT COUNT(*) as unread_count FROM notifications WHERE unique_number = ? AND is_read = 0");
    $unreadQuery->bind_param("s", $user);
    $unreadQuery->execute();
    $unreadResult = $unreadQuery->get_result();
    $unreadData = $unreadResult->fetch_assoc();
    
    // Return the unread count as a JSON response
    echo json_encode(['unread_count' => $unreadData['unread_count'] ?? 0]);
} else {
    // Return 0 if the user is not logged in
    echo json_encode(['unread_count' => 0]);
}
?>