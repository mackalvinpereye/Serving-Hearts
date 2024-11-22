<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

date_default_timezone_set('Asia/Manila');

include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$unique_number = '';
$fullname = '';
$collection_date = '';
$user_id = $_SESSION['id'];
$message = '';
$messageType = '';

// Fetch all bookings for the dropdown
$bookingQuery = "SELECT unique_number, fullname, check_in_time, user_id FROM booking WHERE blood_processed = 0 ORDER BY check_in_time DESC";
$bookingStmt = $conn->prepare($bookingQuery);
$bookingStmt->execute();
$bookings = $bookingStmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check if the form is submitted for fetching details or manual entry
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['manual_entry'])) {
        // Manual entry, show the form with empty fields
        $unique_number = '';
        $fullname = '';
        $collection_date = '';
        $bloodtype = '';
        $expiration_date = '';
        $volume = '';
        $remarks = '';
        $additives = '';
    } elseif (isset($_POST['selected_user'])) {
        $selected_user_id = $_POST['selected_user'];

        // Fetch booking details using the selected user ID
        $query = "SELECT unique_number, fullname, check_in_time FROM booking WHERE user_id = ? ORDER BY check_in_time DESC LIMIT 1"; 
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $selected_user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $booking = $result->fetch_assoc();
            $unique_number = htmlspecialchars($booking['unique_number']);
            $fullname = htmlspecialchars($booking['fullname']);
            $collection_date = $booking['check_in_time'];
        } else {
            $message = "No booking found for this user.";
            $messageType = "error";
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['volume'])) {
        // Prepare to insert blood information
        $bloodtype = $_POST['bloodtype'];
        $expiration_date = $_POST['expiration_date'];
        $volume = $_POST['volume'];
        $remarks = $_POST['remarks'];
        $additives = $_POST['additives'];
        $unique_number = $_POST['unique_number'];
        $collection_date = $_POST['collection_date'];
        $fullname = $_POST['fullname'];

        // Ensure dates are formatted correctly
        $collection_date = date('Y-m-d H:i:s', strtotime($collection_date));
        $expiration_date = date('Y-m-d', strtotime($expiration_date));

        // Check if the record already exists
        $checkQuery = "SELECT * FROM blood_inventory WHERE unique_number = ? AND collection_date = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param('ss', $unique_number, $collection_date);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "This entry already exists. Please check your data.";
            $messageType = "error";
        } else {
            // Insert blood information
            $insertQuery = "INSERT INTO blood_inventory (unique_number, blood_type, collection_date, expiration_date, volume, remarks, additives, fullname)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param('ssssssss', $unique_number, $bloodtype, $collection_date, $expiration_date, $volume, $remarks, $additives, $fullname);

            if ($insertStmt->execute()) {
                // Blood information saved successfully, now update the booking record
                $updateBookingQuery = "UPDATE booking SET blood_processed = 1 WHERE unique_number = ? AND check_in_time = ?";
                $updateBookingStmt = $conn->prepare($updateBookingQuery);

                // Ensure collection_date is formatted correctly
                $formattedCollectionDate = date('Y-m-d H:i:s', strtotime($collection_date));

                $updateBookingStmt->bind_param('ss', $unique_number, $formattedCollectionDate);

                if ($updateBookingStmt->execute()) {
                    if ($updateBookingStmt->affected_rows > 0) {
                        $message = "Blood information saved, and booking status updated successfully.";
                        $messageType = "success";

                        // Clear the fields after successful submission
                        $unique_number = '';
                        $fullname = '';
                        $collection_date = '';
                        $bloodtype = '';
                        $expiration_date = '';
                        $volume = '';
                        $remarks = '';
                        $additives = '';
                    } else {
                        $message = "Blood information saved successfully, but row (Unique Number) were not updated in the table.";
                        $messageType = "success";
                    }
                } else {
                    $message = "Error executing update: " . $updateBookingStmt->error;
                    $messageType = "error";
                }
            } else {
                $message = "Error saving blood information: " . $insertStmt->error; // Detailed error message
                $messageType = "error";
            }                
        }
    }

    // Update blood_inventory if an update request is made (add your update logic here)
    if (isset($_POST['update'])) {
        // Ensure dates are formatted correctly
        $collection_date = date('Y-m-d H:i:s', strtotime($_POST['collection_date']));
        $expiration_date = date('Y-m-d', strtotime($_POST['expiration_date']));

        $query = "UPDATE blood_inventory SET 
                    unique_number = ?, 
                    blood_type = ?, 
                    volume = ?, 
                    expiration_date = ?, 
                    remarks = ?, 
                    collection_date = ?, 
                    additives = ?, 
                    fullname = ?, 
                    status = ? 
                WHERE id = ?";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssssssi", $_POST['unique_number'], $_POST['blood_type'], $_POST['volume'], $expiration_date, $_POST['remarks'], $collection_date, $_POST['additives'], $_POST['fullname'], $_POST['status'], $id);

        if ($stmt->execute()) {
            $message = "Record updated successfully.";
            $messageType = "success";
        } else {
            $message = "Error updating record: " . $stmt->error;
            $messageType = "error";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Information Entry</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin-top: 110px;
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
        input[type="date"],
        input[type="datetime-local"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box; 
        }
        input[type="submit"] {
            background-color: #C92A2A;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
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
        .fetch {
            margin-bottom: 10px;
        }
        .manual {
            flex-direction: start;

        }
    </style>
    <script>
        // JavaScript to calculate expiration date based on selected additive
        document.addEventListener("DOMContentLoaded", function() {
            const additiveSelect = document.getElementById("additives");
            const expirationDateInput = document.getElementById("expiration_date");
            const collectionDateInput = document.getElementById("collection_date");

            const expirationPeriods = {
                'CDPA-1': 35,
                'AS-1': 42,
                'AS-3': 42,
            };

            additiveSelect.addEventListener("change", function() {
                const selectedAdditive = this.value;
                const collectionDate = new Date(collectionDateInput.value); // Get collection date from the input

                if (expirationPeriods[selectedAdditive]) {
                    let daysToExpire = expirationPeriods[selectedAdditive];
                    const expirationDate = new Date(collectionDate);
                    expirationDate.setDate(collectionDate.getDate() + daysToExpire);
                    expirationDateInput.value = expirationDate.toISOString().split('T')[0]; // Set value in 'YYYY-MM-DD'
                } else {
                    expirationDateInput.value = ''; // Clear the expiration date if no additive is selected
                }
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h1>Blood Information</h1>

        <?php if (!empty($message)): ?>
            <div class="message <?= $messageType ?>" id="message">
                <?= htmlspecialchars($message) ?>
            </div>
            <script>
                setTimeout(function() {
                    var messageElement = document.getElementById("message");
                    if (messageElement) {
                        messageElement.style.display = 'none'; // Hide the message
                    }
                }, 2000); // 2000 milliseconds = 2 seconds
            </script>
        <?php endif; ?>


        <form action="" method="POST">
            <h3>Select Donor</h3>
            <label for="selected_user">Select a Donor:</label>
            <select name="selected_user" id="selected_user" required>
                <option value="" selected disabled>-Choose on the list-</option>
                <?php foreach ($bookings as $booking): ?>
                    <option value="<?= htmlspecialchars($booking['user_id']) ?>">
                        <?= htmlspecialchars($booking['fullname']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="fetch">
                <input type="submit" value="Fetch Details from Donors">
            </div>
        </form>

        <!-- New Button for Manual Entry -->
        <form action="" method="POST">
            <input type="hidden" name="manual_entry" value="1">
            <div class="manual">
                <input type="submit" value="Enter Blood Information Manually">
            </div>          
        </form>

        <?php if (!empty($unique_number) || isset($_POST['manual_entry'])): ?>
            <form action="" method="POST">
                <h3>Donor's Information</h3>
                <label for="unique_number">Donor's ID Code:</label>
                <input type="text" id="unique_number" name="unique_number" value="<?= $unique_number ?>" <?= empty($unique_number) ? '' : 'readonly' ?>>

                <label for="name">Donor's Name:</label>
                <input type="text" id="fullname" name="fullname" value="<?= $fullname ?>" <?= empty($fullname) ? '' : 'readonly' ?>>

                <label for="bloodtype">Blood Type:</label>
                <select name="bloodtype" id="bloodtype">
                    <option value="N/A" selected>-I do not know my blood type-</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>

                <label for="collection_date">Collection Date:</label>
                <input type="datetime-local" id="collection_date" name="collection_date" value="<?= empty($collection_date) ? '' : date('Y-m-d\TH:i:s', strtotime($collection_date)) ?>" <?= empty($collection_date) ? '' : 'readonly' ?>>

                <label for="expiration_date">Expiration Date:</label>
                <input type="date" id="expiration_date" name="expiration_date" required>

                <label for="volume">Volume:</label>
                <input type="text" id="volume" name="volume" required>

                <label for="remarks">Remarks:</label>
                <input type="text" id="remarks" name="remarks" required>

                <label for="additives">Additives:</label>
                <select name="additives" id="additives" required>
                    <option value="" selected disabled>-Select Additive-</option>
                    <option value="CDPA-1">CDPA-1</option>
                    <option value="AS-1">AS-1</option>
                    <option value="AS-3">AS-3</option>
                </select>

                <input type="submit" value="Save Blood Information">
            </form>
            <?php endif; ?>
    </div>
</body>
</html>