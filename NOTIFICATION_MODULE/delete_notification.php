<?php
session_start();
require_once('../USER-VERIFICATION/config/db.php');

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

// Handle DELETE request
if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['id'])) {
    $notificationId = intval($_GET['id']); // Ensure it's an integer

    // Prepare the statement to delete the notification
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $notificationId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        http_response_code(200); // Success
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404); // Not found or no rows affected
        echo json_encode(['success' => false, 'message' => 'Notification not found or already deleted']);
    }
    $stmt->close();
} else {
    http_response_code(400); // Bad request
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
$conn->close();
?>
