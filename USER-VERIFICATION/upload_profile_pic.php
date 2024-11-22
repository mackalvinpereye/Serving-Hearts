<?php
if (isset($_FILES['profile_picture']) && isset($_SESSION['unique_number'])) {
    $unique_number = $_SESSION['unique_number'];
    $upload_dir = '../USER-VERIFICATION/uploads/profile_picture/';
    
    // Debugging: Check if the session is active
    if (!isset($_SESSION['unique_number'])) {
        echo 'No user logged in';
        exit();
    }

    // Ensure the upload directory exists
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
    }

    $file_name = $_FILES['profile_picture']['name'];
    $file_tmp = $_FILES['profile_picture']['tmp_name'];
    $file_path = $upload_dir . basename($file_name);

    // Debugging: Check if file is uploaded
    if (empty($file_name)) {
        echo 'No file uploaded';
        exit();
    }

    // Check if the file is an image
    $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowed_extensions)) {
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Update the user's profile picture in the database
            $query = "UPDATE users SET profile_picture = ? WHERE unique_number = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $file_path, $unique_number);
            
            if ($stmt->execute()) {
                // Return the new image path to the AJAX request
                echo $file_path . '?' . time();
            } else {
                echo 'Error updating profile picture in the database.';
            }
        } else {
            echo 'Error uploading file.';
        }
    } else {
        echo 'Invalid file type.';
    }
} else {
    echo 'No file uploaded or user not logged in.';
}
?>
