<?php
// Start the session
session_start();
require('db.php');

// Check if the session is admin or not
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $event_id = $_POST['event_id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    $datetime = $_POST['datetime'];
    $status = $_POST['status'];

    // Handle existing image path
    $existing_image_path = isset($_POST['existing_image_path']) ? $_POST['existing_image_path'] : '';
    $image_path = $existing_image_path; // Default to existing image path

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // If a new image is uploaded, delete the existing image
        if (!empty($existing_image_path) && file_exists($existing_image_path)) {
            unlink($existing_image_path); // Delete the existing image
        }

        // Sanitize and process the image file
        $fileTmpPath = $_FILES['image']['tmp_name'];
        $fileName = $_FILES['image']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Define allowed file extensions and directory for uploads
        $allowedExts = array("jpg", "jpeg", "png", "gif");
        $uploadFileDir = './uploads/';
        $newFileName = uniqid() . '.' . $fileExtension; // Ensure unique file name
        $dest_path = $uploadFileDir . $newFileName;

        if (in_array($fileExtension, $allowedExts)) {
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $image_path = $dest_path; // Update image path if upload is successful
            }
        }
    }

    // Ensure the image path is not removed if no new image is uploaded
    if (empty($image_path)) {
        $image_path = $existing_image_path; // Keep the existing image if no new image is uploaded
    }

    // Update event details in the database
    $stmt = $conn->prepare("UPDATE markers SET name=?, address=?, lat=?, lng=?, datetime=?, status=?, image_path=? WHERE event_id=?");
    $stmt->bind_param("ssssssss", $name, $address, $lat, $lng, $datetime, $status, $image_path, $event_id);
    
    if ($stmt->execute()) {
        // Redirect to map.php after successful update
        header('Location: map.php?message=Event updated successfully.');
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
?>
