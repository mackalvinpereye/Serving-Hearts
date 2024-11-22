<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['unique_number'])) {
    header('Location: http://localhost/Serving%20Hearts/USER-VERIFICATION/index.php');
    exit();
}

// Get the logged-in user's unique number
$loggedInUserNumber = $_SESSION['unique_number'];

require_once('../USER-VERIFICATION/config/db.php');

// Fetch user details from the database
$query = "SELECT fullname, unique_number, blood_type, dateofbirth, phonenumber, email, profile_picture, qr_code_path FROM users WHERE unique_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $loggedInUserNumber);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

// Check if user data is retrieved
if ($userData) {
    // Count confirmed bookings for the user
    $bookingQuery = "SELECT COUNT(*) as confirmed_count FROM booking WHERE unique_number = ? AND status = 'confirmed'";
    $bookingStmt = $conn->prepare($bookingQuery);
    $bookingStmt->bind_param("s", $loggedInUserNumber);
    $bookingStmt->execute();
    $bookingResult = $bookingStmt->get_result();
    $bookingData = $bookingResult->fetch_assoc();
    $confirmedCount = $bookingData['confirmed_count'];

    // Load the appropriate background image based on confirmed bookings
    $backgroundImagePath = '';
    if ($confirmedCount > 10) {
        $backgroundImagePath = '../ID_GENERATOR/background/Member.png'; // Tier 4
    } elseif ($confirmedCount == 7) {
        $backgroundImagePath = '../ID_GENERATOR/background/Member.png'; // Tier 3
    } elseif ($confirmedCount == 4) {
        $backgroundImagePath = '../ID_GENERATOR/background/Tier2.png'; // Tier 2
    } else {
        $backgroundImagePath = '../ID_GENERATOR/background/Tier1.png'; // Tier 1
    }

    // Load the selected background image
    $background = imagecreatefrompng($backgroundImagePath);

    // Add text to indicate tier membership
    if ($confirmedCount > 10) {
        $tierText = "Member";
    } elseif ($confirmedCount == 8) {
        $tierText = "Tier 3";
    } elseif ($confirmedCount == 4) {
        $tierText = "Tier 2";
    } else {
        $tierText = "Tier 1";
    }

    // Set the dimensions
    $width = imagesx($background);
    $height = imagesy($background);

    // Create a blank image
    $idCard = imagecreatetruecolor($width, $height);

    // Copy the background onto the blank image
    imagecopy($idCard, $background, 0, 0, 0, 0, $width, $height);

    // Set the text color
    $textColorBlack = imagecolorallocate($idCard, 0, 0, 0);

    // Set the font path and sizes
    $fontPath = '../ID_GENERATOR/Aller/Aller_Bd.ttf';

    // Add text to the image using fetched data
    imagettftext($idCard, 45, 0, 330, 120, $textColorBlack, $fontPath, $userData['fullname']);
    imagettftext($idCard, 30, 0, 330, 170, $textColorBlack, $fontPath, $userData['unique_number']);
    imagettftext($idCard, 25, 0, 330, 250, $textColorBlack, $fontPath, $userData['dateofbirth']);
    imagettftext($idCard, 20, 0, 885, 160, $textColorBlack, $fontPath, $tierText);
    imagettftext($idCard, 16, 0, 862, 220, $textColorBlack, $fontPath, "Blood Type:");

    // Adjust the position based on blood type
    if ($userData['blood_type'] === "AB+" || $userData['blood_type'] === "AB-") {
        imagettftext($idCard, 45, 0, 870, 280, $textColorBlack, $fontPath, $userData['blood_type']);
    } else {
        imagettftext($idCard, 45, 0, 882, 280, $textColorBlack, $fontPath, $userData['blood_type']);
    }

    imagettftext($idCard, 25, 0, 330, 390, $textColorBlack, $fontPath, $userData['phonenumber']);
    imagettftext($idCard, 25, 0, 330, 440, $textColorBlack, $fontPath, $userData['email']);
    imagettftext($idCard, 20, 0, 570, 580, $textColorBlack, $fontPath, "Signature");

    // Check for the user's profile picture
    $profileImagePath = !empty($userData['profile_picture']) ? $userData['profile_picture'] : '../ID_GENERATOR/logo/logo.png';

    // Detect the file extension and load the profile image accordingly
    $profileImageExtension = strtolower(pathinfo($profileImagePath, PATHINFO_EXTENSION));
    switch ($profileImageExtension) {
        case 'jpeg':
        case 'jpg':
            $profileImage = imagecreatefromjpeg($profileImagePath);
            break;
        case 'png':
            $profileImage = imagecreatefrompng($profileImagePath);
            break;
        case 'gif':
            $profileImage = imagecreatefromgif($profileImagePath);
            break;
        default:
            $profileImage = imagecreatefrompng('../ID_GENERATOR/logo/logo.png');
            break;
    }

    // Set the desired position and size for the profile image
    $profileX = 58;
    $profileY = 60;
    $profileWidth = 230;
    $profileHeight = 230;

    // Resize and copy the profile image onto the ID card
    imagecopyresampled($idCard, $profileImage, $profileX, $profileY, 0, 0, $profileWidth, $profileHeight, imagesx($profileImage), imagesy($profileImage));

    // Check for the user's QR code
    $qrCodePath = !empty($userData['qr_code_path']) ? '../USER-VERIFICATION/' . $userData['qr_code_path'] : '../ID_GENERATOR/default_qr.png';

    // Detect the file extension and load the QR code image accordingly
    $qrCodeExtension = strtolower(pathinfo($qrCodePath, PATHINFO_EXTENSION));
    switch ($qrCodeExtension) {
        case 'jpeg':
        case 'jpg':
            $qrCodeImage = imagecreatefromjpeg($qrCodePath);
            break;
        case 'png':
            $qrCodeImage = imagecreatefrompng($qrCodePath);
            break;
        case 'gif':
            $qrCodeImage = imagecreatefromgif($qrCodePath);
            break;
        default:
            $qrCodeImage = imagecreatefrompng('../ID_GENERATOR/default_qr.png');
            break;
    }

    // Set the desired position and size for the QR code
    $qrX = 59;
    $qrY = 333;
    $qrWidth = 230;
    $qrHeight = 230;

    // Resize and copy the QR code onto the ID card
    imagecopyresampled($idCard, $qrCodeImage, $qrX, $qrY, 0, 0, $qrWidth, $qrHeight, imagesx($qrCodeImage), imagesy($qrCodeImage));

    // Set the file path to save the ID card
    $savePath = '../ID_GENERATOR/ids/' . $userData['unique_number'] . '_ID_Card.png';

    // Save the generated ID card image
    imagepng($idCard, $savePath);
    
    // Destroy the image resources
    imagedestroy($idCard);
    imagedestroy($background);
    imagedestroy($profileImage);
    imagedestroy($qrCodeImage);

    // Set a session variable to indicate ID card generation
    $_SESSION['id_generated'] = true;

    // Clear the cache to force a hard reload
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Force a hard refresh with a unique query parameter
    header('Location: ../USER_DASHBOARD/user_dashboard.php?' . time());
    exit();

} else {
    echo "User data not found.";
}

$stmt->close();
$conn->close();
?>
