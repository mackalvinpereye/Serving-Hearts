<?php
// Start the session
session_start();
require('db.php');

// Check if the session is admin or not
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

// Get form data and sanitize inputs
$event_id = htmlspecialchars($_POST['event_id']);
$name = htmlspecialchars($_POST['name']);
$address = htmlspecialchars($_POST['address']);
$lat = htmlspecialchars($_POST['lat']);
$lng = htmlspecialchars($_POST['lng']);
$datetime = htmlspecialchars($_POST['datetime']);
$status = htmlspecialchars($_POST['status']);

// Handle the image file upload (if any)
$imagePath = null; // Default to null
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $targetDir = 'uploads/';
    $imagePath = $targetDir . basename($_FILES['image']['name']);
    if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
        // Handle error in moving the uploaded file
        $imagePath = null; // Reset if there's an error
    }
}

// Check if the event already exists
$stmt = $conn->prepare("SELECT id FROM markers WHERE event_id = ?");
$stmt->bind_param("s", $event_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing event
    $stmt = $conn->prepare("UPDATE markers SET name = ?, address = ?, lat = ?, lng = ?, datetime = ?, status = ?, image_path = ? WHERE event_id = ?");
    $stmt->bind_param("ssddssss", $name, $address, $lat, $lng, $datetime, $status, $imagePath, $event_id);
    if ($stmt->execute()) {
        // Successful update
    } else {
        // Log error or show a message
    }
} else {
    // Insert new event
    $stmt = $conn->prepare("INSERT INTO markers (event_id, name, address, lat, lng, datetime, status, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssddsss", $event_id, $name, $address, $lat, $lng, $datetime, $status, $imagePath);
    if ($stmt->execute()) {
        // Successful insert
    } else {
        // Log error or show a message
    }
}

// Close statement and connection
$stmt->close();
$conn->close();

// Redirect to the desired page
header('Location: ../EVENT_MODULE/map.php');
exit();
?>
