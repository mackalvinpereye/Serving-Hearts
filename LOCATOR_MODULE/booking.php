<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

include '../UI/sidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

// Initialize event variable and user variable
$event = null;
$user = null;

// Retrieve event_id from the query string (passed when user clicks "Book a Donation")
if (isset($_GET['event_id'])) {
    $event_id = $_GET['event_id'];

    // Fetch event details from the database
    $query = "SELECT * FROM markers WHERE event_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        if ($result->num_rows > 0) {
            $event = $result->fetch_assoc();
            
            // Format the datetime for the input
            $formattedDateTime = date('Y-m-d\TH:i', strtotime($event['datetime']));
        } else {
            echo "Event not found.";
            exit();
        }
    }   
}

// Fetch user details using the session ID
$user_id = $_SESSION['id'];
$userQuery = "SELECT * FROM users WHERE id = ?"; // Adjust table name and column as needed
$stmt = $conn->prepare($userQuery);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$userResult = $stmt->get_result();

if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Initialize success message variable
$success_message = null;

// If the booking form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $datetime = isset($_POST['booking_date']) ? $_POST['booking_date'] : null;
    $unique_number = $_POST['unique_number'];
    $fullname = $_POST['fullname']; 
    $civilstatus = $_POST['civilstatus'];
    $contactnum = $_POST['contactnum'];
    $nationality = $_POST['nationality'];
    $occupation = $_POST['occupation'];
    $firsttime = $_POST['firsttime'];
    $noofdonation = $_POST['noofdonation'];
    $venue = $_POST['venue'];
    $healthy = $_POST['healthy'];
    $medication = $_POST['medication'];
    $medication_details = $_POST['medication_details'];
    $consent = isset($_POST['consent']) ? 1 : 0; // Check if consent is given

    // Check if user has already booked this event
    $checkBookingQuery = "SELECT * FROM booking WHERE user_id = ? AND event_id = ?";
    $checkStmt = $conn->prepare($checkBookingQuery);
    $checkStmt->bind_param('is', $user_id, $event_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $error_message = "You have already booked this event.";
    } else {
        // Insert booking into the database
        $insertQuery = "INSERT INTO booking (user_id, event_id, event_name, event_address, booking_date, unique_number, fullname, civil_status, contact_number, nationality, occupation, first_time, number_of_donations, venue, healthy, taking_medication, medication_details, consent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param('issssssssssssssssi', $user_id, $event_id, $event['name'], $event['address'], $datetime, $unique_number, $fullname, $civilstatus, $contactnum, $nationality, $occupation, $firsttime, $noofdonation, $venue, $healthy, $medication, $medication_details, $consent);

        if ($stmt->execute()) {
            // Success message
            $success_message = "Booking successful! We are hoping for your participation";
        } else {
            // Error handling
            $error_message = "Error booking the event: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Donation</title>
    <style>
        /* Add your styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin-top: 100px;
            margin-left: 30rem;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        h3 {
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 18px;
            border-bottom: 1px solid #C92A2A;
            padding-bottom: 5px;
        }
        label {
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box; /* Ensures padding is included in width */
        }
        input[type="radio"] {
            margin: 10px 5px 15px 5px;
        }
        input[type="submit"] {
            background-color: #C92A2A;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%; /* Make the button full width */
            font-size: 16px;
        }
        .checkbox-label {
            margin: 10px 0;
            font-size: 13px;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Book a Donation for <?= $event ? htmlspecialchars($event['name']) : 'Unknown Event' ?></h1>

        <?php if (isset($success_message)): ?>
            <div id="message" class="message success">
                <?= htmlspecialchars($success_message); ?>
            </div>
        <?php elseif (isset($error_message)): ?>
            <div id="message" class="message error">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <script>
            // Wait for the DOM to be fully loaded
            document.addEventListener("DOMContentLoaded", function() {
                // Select the message element
                var message = document.getElementById("message");
                // Check if the message element exists
                if (message) {
                    // Set a timeout to hide the message after 2 seconds
                    setTimeout(function() {
                        message.style.display = 'none'; // Hide the message
                    }, 2000); // 2000 milliseconds = 2 seconds
                }
            });
        </script>

        <form method="POST" action="">
            <input type="hidden" name="event_id" value="<?= $event ? htmlspecialchars($event['event_id']) : '' ?>">

            <h3>Event Information</h3>
            <label for="datetime">Event Date & Time</label>
            <input type="datetime-local" name="booking_date" value="<?= $event ? htmlspecialchars($event['datetime']) : '' ?>" readonly>

            <label for="address">Event Location</label>
            <input type="text" name="address" value="<?= $event ? htmlspecialchars($event['address']) : '' ?>" readonly>

            <h3>Personal Information</h3>
            <label for="unique_number">Unique Number</label>
            <input type="text" name="unique_number" value="<?= $user ? htmlspecialchars($user['unique_number']) : '' ?>" readonly>

            <label for="fullname">Name</label>
            <input type="text" name="fullname" value="<?= $user ? htmlspecialchars($user['fullname']) : '' ?>" readonly>

            <label for="dob">Date of Birth</label>
            <input type="text" name="dob" value="<?= $user ? htmlspecialchars($user['dateofbirth']) : '' ?>" readonly>

            <label for="gender">Sex</label>
            <input type="text" name="gender" value="<?= $user ? htmlspecialchars($user['gender']) : '' ?>" readonly>

            <label for="civilstatus">Civil Status</label>
            <select name="civilstatus" required>
                <option value="" disabled selected>Select your civil status</option>
                <option value="single">Single</option>
                <option value="married">Married</option>
                <option value="widowed">Widowed</option>
            </select>

            <label for="contactnum">Contact Number</label>
            <input type="text" name="contactnum" value="<?= $user ? htmlspecialchars($user['phonenumber']) : '' ?>" readonly>

            <label for="nationality">Nationality</label>
            <input type="text" name="nationality" required>

            <label for="occupation">Occupation</label>
            <input type="text" name="occupation" required>

            <h3>Donation Information</h3>
            <label>First Time</label>
            <input type="radio" name="firsttime" value="Yes"> Yes
            <input type="radio" name="firsttime" value="No"> No

            <label for="noofdonation">Number of times donated (including this donation):</label>
            <input type="text" name="noofdonation" required>

            <label for="venue">Venue</label>
            <input type="text" name="venue" required>

            <h3>Health Information</h3>
            <label>Are you feeling healthy today</label>
            <input type="radio" name="healthy" value="Yes"> Yes
            <input type="radio" name="healthy" value="No"> No

            <label>Are you currently taking medication</label>
            <input type="radio" name="medication" value="Yes"> Yes
            <input type="radio" name="medication" value="No"> No

            <label for="medication">If yes, please specify</label>
            <input type="text" name="medication_details">

            <h3>Consent</h3>
            <label class="checkbox-label">
                <input type="checkbox" name="consent" required>
                I hereby certify that I am the person referred to in the above and that all entries are read and well understood by me and that it is a free and voluntary act and deed to donate my blood.
            </label>

            <label class="checkbox-label">
                <input type="checkbox" name="consent" required>
                I have read and understood the information provided about the donation process, and I consent to the collection and use of my personal data in accordance with the Privacy Policy.
            </label>

            <input type="submit" value="Submit Booking">
        </form>
    </div>
</body>
</html>
