<?php
session_start();
require('db.php');

// Check if user is logged in as admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

// Check if event_id is provided
if (isset($_GET['event_id'])) {
    $eventId = $_GET['event_id'];

    // Prepare the statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM markers WHERE event_id = ?");
    $stmt->bind_param("s", $eventId); // assuming event_id is a string

    // Execute the statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $event = $result->fetch_assoc();

        // Check if event was found
        if ($event) {
            echo json_encode($event); // Return the event details
        } else {
            echo json_encode(['error' => 'Event not found']);
        }
    } else {
        echo json_encode(['error' => 'Database query failed']);
    }
} else {
    // If no specific event_id is provided, fetch all event IDs
    $stmt = $conn->prepare("SELECT event_id FROM markers");

    // Execute the statement
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $eventIds = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($eventIds); // Return all event IDs
    } else {
        echo json_encode(['error' => 'Database query failed']);
    }
}

$conn->close();
?>
