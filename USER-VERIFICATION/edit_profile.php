<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

require_once('../USER-VERIFICATION/config/db.php');
include '../UI/sidebar.php';

$message = ""; // For displaying success or error messages
$messageType = ""; // For message type (success/error)

// Fetch current user data from the database
$user_id = $_SESSION['id'];
$query = $conn->prepare("SELECT password, profile_picture, phonenumber, email, address, username FROM users WHERE id = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
$query->free_result();
$query->close();

// Check if the profile picture exists
$profile_picture_path = !empty($user['profile_picture']) && file_exists($user['profile_picture']) 
    ? htmlspecialchars($user['profile_picture']) . '?' . time() 
    : '../USER-VERIFICATION/uploads/profile_picture/default-placeholder.png'; // Update with your default placeholder path

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle password update
    if (isset($_POST['update_password'])) {
        $old_password = $_POST['old_password'] ?? null;
        $new_password = $_POST['new_password'] ?? null;
        $confirm_password = $_POST['confirm_password'] ?? null;

        // Check if the old password matches the current password
        if ($old_password && password_verify($old_password, $user['password'])) {
            // Check if the new passwords match
            if ($new_password && $new_password === $confirm_password) {
                // Hash the new password
                $new_password_hashed = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password in the database
                $update_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_query->bind_param("si", $new_password_hashed, $user_id);

                // Execute password update
                if ($update_query->execute()) {
                    $message = "Password updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to update password.";
                    $messageType = "error";
                }
                $update_query->free_result();
                $update_query->close();
            } elseif (!empty($new_password) || !empty($confirm_password)) {
                $message = "New passwords do not match.";
                $messageType = "error";
            }
        } elseif (!empty($old_password)) {
            $message = "Current password is incorrect.";
            $messageType = "error";
        }
    }

    // Handle profile picture update
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {
        $profile_picture = $_FILES['profile_picture'];

        // Path to the target directory for profile pictures
        $target_dir = "../USER-VERIFICATION/uploads/profile_picture/";

        // Check if a new file has been uploaded
        if ($profile_picture && $profile_picture["error"] === UPLOAD_ERR_OK) {
            // Delete old profile picture if it exists
            if (!empty($user['profile_picture']) && file_exists($user['profile_picture'])) {
                unlink($user['profile_picture']); // Delete the old picture
            }

            // Set the target file name
            $fileName = pathinfo($profile_picture["name"], PATHINFO_FILENAME);
            $fileExtension = pathinfo($profile_picture["name"], PATHINFO_EXTENSION);
            $newFileName = $fileName . '_' . time() . '.' . $fileExtension; // Ensure unique file name
            $target_file = $target_dir . $newFileName; // New target file name

            // Move the uploaded file
            if (move_uploaded_file($profile_picture["tmp_name"], $target_file)) {
                // Update the profile picture path in the database
                $update_picture_query = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
                $update_picture_query->bind_param("si", $target_file, $user_id);
                if ($update_picture_query->execute()) {
                    $message .= " Profile picture updated successfully!";
                    $messageType = "success";
                
                    // Fetch the updated profile picture path
                    $query = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
                    $query->bind_param("i", $user_id);
                    $query->execute();
                    $result = $query->get_result();
                    $updated_user = $result->fetch_assoc();
                    $profile_picture_path = htmlspecialchars($updated_user['profile_picture']) . '?' . time();

                    // Free result and close query
                    $query->free_result();
                    $query->close();
                } else {
                    $message .= " Failed to update profile picture in the database.";
                    $messageType = "error";
                }
                $update_picture_query->free_result();
                $update_picture_query->close();
            } else {
                $message .= " Failed to upload new profile picture.";
                $messageType = "error";
            }
        } else {
            // If no image is uploaded, set a message indicating no change
            $message .= " No changes were made to the profile picture.";
            $messageType = "success";
        }
    }

    // Handle contact info update
    if (isset($_POST['update_contact'])) {
        $new_phonenumber = $_POST['new_phonenumber'] ?? null;
        $new_email = $_POST['new_email'] ?? null;
        $new_address = $_POST['new_address'] ?? null;

        // Initialize an array to hold the updated values
        $updated_values = [];
        $field_updates = []; // To track the specific fields being updated
        $updated_fields = []; // To keep track of the field names that are updated

        // Check each field and only update if it's not empty
        if (!empty($new_phonenumber)) {
            $updated_values['phonenumber'] = $new_phonenumber;
            $field_updates[] = "phonenumber = ?";
            $updated_fields[] = "Phone Number"; // Add field name to updated fields
        }
        if (!empty($new_email)) {
            $updated_values['email'] = $new_email;
            $field_updates[] = "email = ?";
            $updated_fields[] = "Email"; // Add field name to updated fields
        }
        if (!empty($new_address)) {
            $updated_values['address'] = $new_address;
            $field_updates[] = "address = ?";
            $updated_fields[] = "Address"; // Add field name to updated fields
        }

        // If no fields are filled, set an error message
        if (empty($updated_values)) {
            $message = "No fields were filled to update.";
            $messageType = "error";
        } else {
            // Build the update query dynamically
            $update_query_str = "UPDATE users SET " . implode(', ', $field_updates) . " WHERE id = ?";
            $update_contact_query = $conn->prepare($update_query_str);

            // Prepare the parameters for binding
            $update_query_params = array_values($updated_values); // Get the new values
            $update_query_params[] = $user_id; // Add user_id to the parameters

            // Bind parameters dynamically
            $types = str_repeat('s', count($updated_values)) . 'i'; // 's' for strings and 'i' for integer (user_id)
            $update_contact_query->bind_param($types, ...$update_query_params);

            // Execute the update
            if ($update_contact_query->execute()) {
                // Create a message identifying the updated fields
                $updated_field_list = implode(', ', $updated_fields);
                $message = "Successfully updated the $updated_field_list field.";
                $messageType = "success";
            } else {
                $message = "Failed to update contact information.";
                $messageType = "error";
            }
            $update_contact_query->free_result();
            $update_contact_query->close();
        }
    }

    // Handle username update
    if (isset($_POST['update_username'])) {
        $new_username = $_POST['new_username'] ?? null;

        // Check if the new username is not empty
        if (!empty($new_username)) {
            // Check if the username already exists
            $check_username_query = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $check_username_query->bind_param("s", $new_username);
            $check_username_query->execute();
            $check_username_query->bind_result($username_count);
            $check_username_query->fetch();

            // Close the query result and statement
            $check_username_query->free_result();
            $check_username_query->close();

            if ($username_count > 0) {
                $message = "Username is already taken.";
                $messageType = "error";
            } else {
                // Update the username
                $update_username_query = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
                $update_username_query->bind_param("si", $new_username, $user_id);

                if ($update_username_query->execute()) {
                    $message = "Username updated successfully!";
                    $messageType = "success";
                } else {
                    $message = "Failed to update username.";
                    $messageType = "error";
                }

                // Close the query result and statement
                $update_username_query->free_result();
                $update_username_query->close();
            }
        }
    }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">
    <title>Edit Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #EBEBEB;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .form-container {
            background-color: #EBEBEB;
            margin-left: 5rem;
            padding: 30px;
            width: 800px;
            height: 100vh;
        }
        .form-container h1 {
            font-size: 30px;
            margin-bottom: 30px;
            color: #333;
        }
        .form-container h2 {
            font-size: 20px;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }
        .form-group label {
            display: block;
            color: #555;
            font-weight: bold;
            margin-bottom: 5px;
            font-size: 17px;
        }
        .form-group input[type="file"],
        .form-group input[type="password"],
        .form-group input[type="text"],
        .form-group input[type="email"] {
            width: 100%;
            padding: 10px;
            font-size: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .image-preview-container {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            overflow: hidden;
            margin: 10px auto 20px;
            border: 3px solid white;
            background-color: white;
        }
        .image-preview-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .change-picture-container {
            flex-grow: 1;
            margin-left: 30px;
        }
        .btn-container {
            display: flex;
            justify-content: space-between;
        }
        .btn-submit {
            background-color: darkred;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
        }
        .btn-submit:hover {
            background-color: red;
        }
        .btn-submit-profile {
            background-color: darkred;
            color: white;
            border: none;
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-weight: bold;
            position: relative;
            right: 229px;
            top: 85px;
        }
        .btn-submit-profile:hover {
            background-color: red;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            display: none;
            width: 127.5%;
        }
        .message.success {
            background-color: #d4edda;
            color: #155724;
            width: 127.5%;
        }
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            width: 127.5%;
        }
        .upload-btn {
            display: inline-block;
            width: 40px;
            height: 40px;
            background-color: #007BFF;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 40px;
            border: 2px solid #EBEBEB;
            cursor: pointer;
            transition: background-color 0.3s;
            position: relative;
            right: 85px;
            top: 80px;
        }
        .upload-btn:hover {
            background-color: #0056b3;
        }
        .fa-pencil-alt {
            color: white;
            margin: 11px;
        }
        input[type="file"] {
            display: none;
        }
        .section-container {
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 45%;
        }
        .section-container h2 {
            margin-bottom: 20px;
        }
        .profile_section-container {
            background-image: url('../WEB/images/profile_bg.jpg');
            background-size: 100% 50%;  /* Set the width to 100% and the height to 50% */
            background-position: top center;  /* Align it to the top */
            background-repeat: no-repeat;
            padding: 30px;
            border-radius: 10px;
            background-color: white;
            margin-bottom: 10px;
            width: 65%;
        }          
        .profile {
            position: relative;
            top: 160px;
            left: 210px;
        }
        .password-section-container {
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 45%;
            position: relative;
            top: 9.8px;
        }
        .username-section-container {
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 50%;
            position: relative;
            bottom: 790px;
            left: 600px;
            height: 47%;
        }
        .contact-section-container {
            margin-bottom: 10px;
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            width: 72%;
            position: relative;
            bottom: 781px;
            left: 420px;
            height: 54%;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <?php
        if (!isset($pageTitle)) {
            $pageTitle = "Edit your Profile";
        }
        ?>
        <title><?php echo $pageTitle; ?></title>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>" style="display: block;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Profile Picture Section -->
        <div class="profile_section-container">
            <form method="POST" enctype="multipart/form-data">
            <h2 class="profile">Update Profile Picture</h2>
                <div class="form-group" style="display: flex; align-items: center;">
                    <div class="image-preview-container" style="margin-right: 20px;">
                        <img src="<?php echo $profile_picture_path ?: '../USER-VERIFICATION/uploads/profile_picture/usericon.png'; ?>" alt="Profile Picture">
                    </div>

                    <div class="change-picture-container">
                        <label for="profile_picture" class="upload-btn">
                            <i class="fas fa-pencil-alt"></i>
                        </label>
                        <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
                        <button type="submit" name="update_picture" class="btn-submit-profile">Update Profile Picture</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Password Update Section -->
        <div class="password-section-container">
            <form method="POST">
                <h2>Update Password</h2>
                <div class="form-group">
                    <label for="old_password">Current Password:</label>
                    <input type="password" name="old_password" placeholder="Enter your current password">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password:</label>
                    <input type="password" name="new_password" placeholder="Enter new password">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" name="confirm_password" placeholder="Confirm new password">
                </div>
                <div class="btn-container">
                    <button type="submit" name="update_password" class="btn-submit">Update Password</button>
                </div>
            </form>
        </div>

        <!-- Username Update Section -->
        <div class="username-section-container">
            <form method="POST">
                <h2>Update Username</h2>
                <div class="form-group">
                    <label for="new_username">New Username:</label>
                    <input type="text" name="new_username" placeholder="Enter new username" required>
                </div>
                <div class="btn-container">
                    <button type="submit" name="update_username" class="btn-submit">Update Username</button>
                </div>
            </form>
        </div>

        <!-- Contact Information Section -->
        <div class="contact-section-container">
            <form method="POST">
                <h2>Update Contact Information</h2>
                <div class="form-group">
                    <label for="new_phonenumber">Phone Number:</label>
                    <input type="text" name="new_phonenumber" placeholder=" New Phone Number">
                </div>
                <div class="form-group">
                    <label for="new_email">Email:</label>
                    <input type="email" name="new_email" placeholder="New Email">
                </div>
                <div class="form-group">
                    <label for="new_address">Address:</label>
                    <input type="text" name="new_address" placeholder="New Home Address">
                </div>
                <div class="btn-container">
                    <button type="submit" name="update_contact" class="btn-submit">Update Contact Info</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<script>
    // Add image preview functionality
    document.querySelector('input[name="profile_picture"]').addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const imagePreview = document.querySelector('.image-preview-container img');
            imagePreview.src = URL.createObjectURL(file);
        }
    });

    // Automatically hide message after 2 seconds
    const message = document.querySelector('.message');
    if (message) {
        setTimeout(() => {
            message.style.display = 'none';
        }, 2000);
    }
</script>

<script>
    // Store the original title
    var originalTitle = "<?php echo $pageTitle; ?>";

    // Reset the title after including the content
    function resetTitle() {
        document.title = originalTitle;
    }

    // Call resetTitle() when needed
    window.onload = resetTitle;
</script>
