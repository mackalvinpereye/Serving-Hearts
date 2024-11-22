<?php
session_start();
require_once('../USER-VERIFICATION/config/db.php');

// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

// Check if scanned_text is set
if (isset($_POST['scanned_text'])) {
    $scannedText = $_POST['scanned_text'];

    // Extract user details from the scanned QR code
    preg_match('/User ID:\s*(\d+)/', $scannedText, $userIdMatches);
    preg_match('/Unique Number:\s*([^\n]+)/', $scannedText, $uniqueNumberMatches);
    preg_match('/Full Name:\s*([^\n]+)/', $scannedText, $fullNameMatches);
    preg_match('/Email:\s*([^\n]+)/', $scannedText, $emailMatches);
    preg_match('/Phone Number:\s*([^\n]+)/', $scannedText, $phoneNumberMatches);

    if (!empty($userIdMatches[1])) {
        $userId = intval($userIdMatches[1]);

        // Prepare formatted details for display
        $formattedDetails = "User ID: " . $userIdMatches[1] . "<br>" .
                            "Unique Number: " . $uniqueNumberMatches[1] . "<br>" .
                            "Full Name: " . $fullNameMatches[1] . "<br>" .
                            "Email: " . $emailMatches[1] . "<br>" .
                            "Phone Number: " . $phoneNumberMatches[1];

        // Verify if the user has a valid booking
        $sql = "SELECT * FROM booking WHERE user_id = ? AND status = 'pending' LIMIT 1"; // Change to 'pending' to find the correct booking
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // User has a pending booking, mark attendance
            $booking = $result->fetch_assoc();
            $bookingId = $booking['id'];

            // Check for existing attendance confirmation to avoid duplicates
            if ($booking['attendance_confirmed'] == 0) {
                // Update attendance in the database
                $checkInTime = date('Y-m-d H:i:s'); // Get current time in Manila timezone
                $updateSql = "UPDATE booking SET attendance_confirmed = 1, status = 'confirmed', check_in_time = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param('si', $checkInTime, $bookingId);
                if ($updateStmt->execute()) {
                    // Attendance confirmed successfully
                    echo json_encode(['status' => 'success', 'message' => 'Attendance recorded successfully!', 'details' => $formattedDetails]);
                } else {
                    // Error updating attendance
                    echo json_encode(['status' => 'error', 'message' => 'Failed to record attendance.', 'details' => $formattedDetails]);
                }
            } else {
                // Attendance already confirmed
                echo json_encode(['status' => 'error', 'message' => 'Attendance already confirmed for this booking.', 'details' => $formattedDetails]);
            }
        } else {
            // No pending booking found
            echo json_encode(['status' => 'error', 'message' => 'No pending booking found for this QR code.', 'details' => $formattedDetails]);
        }
    } else {
        // Invalid QR code format
        echo json_encode(['status' => 'error', 'message' => 'Invalid QR code scanned.']);
    }
} else {
    // No scanned text received
    echo json_encode(['status' => 'error', 'message' => 'No QR code scanned.']);
}

// Close database connection
$conn->close();
?>
