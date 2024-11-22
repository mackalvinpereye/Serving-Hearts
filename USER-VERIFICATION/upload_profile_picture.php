<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_picture'])) {
    $upload_dir = '../USER-VERIFICATION/uploads/profile_picture/';
    $file_name = basename($_FILES['profile_picture']['name']);
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_file)) {
        // Update the database with the new profile picture path
        $unique_number = $_SESSION['unique_number'];
        $query = "UPDATE users SET profile_picture = ? WHERE unique_number = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $target_file, $unique_number);
        $stmt->execute();

        // Return the new profile picture path
        echo $target_file;
    } else {
        echo 'Error uploading file';
    }
}
?>