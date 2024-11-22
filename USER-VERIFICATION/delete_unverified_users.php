<?php
require 'config/db.php';

// Get the current date and time
$currentDate = new DateTime();
$currentDate->modify('-7 days');
$formattedDate = $currentDate->format('Y-m-d H:i:s');

// SQL query to delete users whose verification token was created more than 7 days ago
$sql = "DELETE FROM users WHERE verified = 0 AND verification_token_created_at < ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $formattedDate);

if ($stmt->execute()) {
    echo "Unverified users deleted successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>