<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

$message = '';
// Clear requestData on page load if accessed without a request reference code
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['requestData'] = null; // Clear request data
}

// Initialize requestData if it's not set
if (!isset($_SESSION['requestData'])) {
    $_SESSION['requestData'] = null;
}

// Check if the request reference code was submitted
if (isset($_POST['requestref_code'])) {
    // Sanitize and validate the input
    $referenceCode = trim($_POST['requestref_code']);
    $referenceCode = htmlspecialchars($referenceCode, ENT_QUOTES, 'UTF-8');

    // Fetch the details from the request table, including checking the status
    $stmt = $conn->prepare("SELECT * FROM request WHERE reference_code = ? AND status = 'Approved'");
    $stmt->bind_param("s", $referenceCode);
    $stmt->execute();
    $result = $stmt->get_result();
    $requestData = $result->fetch_assoc();

    // Check if the request was found and is approved
    if ($requestData) {
        // Check if the reference has already been processed in the handover_requests table
        $checkProcessedStmt = $conn->prepare("SELECT * FROM handover_requests WHERE reference_code = ? AND received_by IS NOT NULL");
        $checkProcessedStmt->bind_param("s", $referenceCode);
        $checkProcessedStmt->execute();
        $checkProcessedResult = $checkProcessedStmt->get_result();

        if ($checkProcessedResult->num_rows > 0) {
            $_SESSION['requestData'] = null; // Clear request data
            $message = "This reference has already been processed.";
            $messageType = 'error'; // Set message type to error
        } else {
            // If not processed, store request data in the session
            $_SESSION['requestData'] = $requestData; // Store in session
            $message = "Request details loaded successfully.";
        }
    } else {
        // If no request found or not approved, clear requestData and set error message
        $_SESSION['requestData'] = null; // Clear request data
        $message = "No approved request found with that reference code.";
        $messageType = 'error'; // Set message type to error
    }
}

if (isset($_POST['save']) && isset($_SESSION['requestData'])) {
    $requestData = $_SESSION['requestData'];
    $receivedBy = htmlspecialchars(trim($_POST['receive']), ENT_QUOTES, 'UTF-8');

    if (empty($requestData['patientname'])) {
        $message = "Error: Patient name is empty.";
    } else {
        // Check for duplicate entry in handover_requests
        $checkStmt = $conn->prepare("SELECT * FROM handover_requests WHERE reference_code = ?");
        $checkStmt->bind_param("s", $requestData['reference_code']);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "Error: Duplicate entry for reference code.";
            $messageType = 'error';
        } else {
            // Check if the request is approved
            $stmtRequest = $conn->prepare("SELECT * FROM request WHERE reference_code = ? AND status = 'Approved'");
            $stmtRequest->bind_param("s", $requestData['reference_code']);
            $stmtRequest->execute();
            $requestResult = $stmtRequest->get_result();

            if ($requestResult->num_rows === 0) {
                $message = "Error: Request is not approved or does not exist.";
                $messageType = 'error';
            } else {
                // Get the requested blood component (Plasma, RBC, or Platelets)
                $requestedComponent = $requestData['bloodcomponent'];

                // Check if the requested component is available in the inventory
                $checkComponentStmt = $conn->prepare(
                    "SELECT id, blood_type, collection_date, expiration_date FROM blood_inventory 
                    WHERE blood_type = ? AND bloodcomponent = ? AND status = 'in_stock' 
                    ORDER BY expiration_date ASC 
                    LIMIT 1"
                );
                $checkComponentStmt->bind_param("ss", $requestData['bloodtype'], $requestedComponent);
                $checkComponentStmt->execute();
                $componentResult = $checkComponentStmt->get_result();
                $bloodComponentInventory = $componentResult->fetch_assoc();

                if (!$bloodComponentInventory) {
                    // If the requested component is not found, look for whole blood
                    $checkWholeBloodStmt = $conn->prepare(
                        "SELECT id, blood_type, collection_date, expiration_date FROM blood_inventory 
                        WHERE blood_type = ? AND bloodcomponent = 'Whole Blood' AND status = 'in_stock' 
                        ORDER BY expiration_date ASC 
                        LIMIT 1"
                    );
                    $checkWholeBloodStmt->bind_param("s", $requestData['bloodtype']);
                    $checkWholeBloodStmt->execute();
                    $wholeBloodResult = $checkWholeBloodStmt->get_result();
                    $wholeBloodInventory = $wholeBloodResult->fetch_assoc();

                    if (!$wholeBloodInventory) {
                        $message = "Error: No available blood (Whole Blood or $requestedComponent) for the requested blood type.";
                        $messageType = 'error';
                    } else {
                        // Whole blood found, process it
                        $bloodComponentInventory = $wholeBloodInventory; // Assign whole blood as the component

                        // Check if the blood component is expired
                        $currentDate = date('Y-m-d');
                        if ($bloodComponentInventory['expiration_date'] < $currentDate) {
                            $message = "Error: The blood component has expired and cannot be processed.";
                            $messageType = 'error';
                        } else {
                            // Logic for splitting whole blood into components (RBC, Plasma, Platelets)
                            if ($requestedComponent !== 'rbc') {
                                $rbcVolume = 200; // RBC volume
                                $rbcExpiration = date('Y-m-d', strtotime($bloodComponentInventory['collection_date'] . ' +42 days'));

                                // Insert RBC into inventory
                                $insertRBCStmt = $conn->prepare(
                                    "INSERT INTO blood_inventory (blood_type, status, expiration_date, bloodcomponent, volume) 
                                    VALUES (?, 'in_stock', ?, 'RBC', ?)"
                                );
                                $insertRBCStmt->bind_param("ssi", $requestData['bloodtype'], $rbcExpiration, $rbcVolume);
                                $insertRBCStmt->execute();
                            }

                            if ($requestedComponent !== 'platelets') {
                                $plateletsVolume = 50; // Platelets volume
                                $plateletsExpiration = date('Y-m-d', strtotime($bloodComponentInventory['collection_date'] . ' +5 days'));

                                // Insert Platelets into inventory
                                $insertPlateletsStmt = $conn->prepare(
                                    "INSERT INTO blood_inventory (blood_type, status, expiration_date, bloodcomponent, volume) 
                                    VALUES (?, 'in_stock', ?, 'Platelets', ?)"
                                );
                                $insertPlateletsStmt->bind_param("ssi", $requestData['bloodtype'], $plateletsExpiration, $plateletsVolume);
                                $insertPlateletsStmt->execute();
                            }

                            if ($requestedComponent !== 'plasma') {
                                $plasmaVolume = 150; // Plasma volume
                                $plasmaExpiration = date('Y-m-d', strtotime($bloodComponentInventory['collection_date'] . ' +1 year'));

                                // Insert Plasma into inventory
                                $insertPlasmaStmt = $conn->prepare(
                                    "INSERT INTO blood_inventory (blood_type, status, expiration_date, bloodcomponent, volume) 
                                    VALUES (?, 'in_stock', ?, 'Plasma', ?)"
                                );
                                $insertPlasmaStmt->bind_param("ssi", $requestData['bloodtype'], $plasmaExpiration, $plasmaVolume);
                                $insertPlasmaStmt->execute();
                            }

                            // Mark the used blood component or whole blood as out_of_stock
                            $updateBloodStatusStmt = $conn->prepare(
                                "UPDATE blood_inventory SET status = 'out_of_stock' WHERE id = ?"
                            );
                            $updateBloodStatusStmt->bind_param("i", $bloodComponentInventory['id']);
                            $updateBloodStatusStmt->execute();

                            // Proceed with saving the handover request for the requested component
                            $stmt = $conn->prepare("INSERT INTO handover_requests 
                                (reference_code, requester, donor, unique_number, patientname, bloodtype, bloodcomponent, bags, hospital, physician, received_by, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Delivered')"
                            );

                            $stmt->bind_param(
                                "sssssssssss",
                                $requestData['reference_code'],
                                $requestData['requester'],
                                $requestData['donor'],
                                $requestData['unique_number'],
                                $requestData['patientname'],
                                $requestData['bloodtype'],
                                $requestedComponent, // Use the requested component
                                $bags,
                                $requestData['hospital'],
                                $requestData['physician'],
                                $receivedBy
                            );

                            if ($stmt->execute()) {
                                $message = "Handover request processed successfully.";
                                $messageType = 'success';
                            } else {
                                $message = "Error: Unable to process the handover request.";
                                $messageType = 'error';
                            }
                        }
                    }
                }
            }
        }
    }
}

// Check if requestData exists in the session
$requestData = $_SESSION['requestData'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handover</title>
    <!-- Add Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');

        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }

        .container {
            max-width: 800px;
            height: 560px;
            margin: 100px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow: auto;
            margin-left: 30rem;
            margin-top: 6.3rem;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            text-align: center;
            color: #bd2020;
        }

        label {
            font-size: 14px;
            margin-bottom: 5px;
            display: block;
            font-weight: 500;
            color: #444;
        }

        input[type="text"],
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus {
            border-color: #bd2020;
            box-shadow: 0 0 5px rgba(189, 32, 32, 0.3);
        }

        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 14px;
            text-align: center;
        }

        .error {
            background-color: #ffe6e6;
            color: #d9534f;
            border: 1px solid #d9534f;
        }

        .success {
            background-color: #e8f5e9;
            color: #28a745;
            border: 1px solid #28a745;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        input[type="submit"] {
            padding: 12px 18px;
            font-size: 14px;
            font-weight: 500;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .save-button {
            background-color: #28a745;
            color: #fff;
        }

        .save-button:hover {
            background-color: #218838;
        }

        .cancel-button {
            background-color: #6c757d;
            color: #fff;
        }

        .cancel-button:hover {
            background-color: #5a6268;
        }

        .check-button {
            background-color: #bd2020;
            color: #fff;
        }

        .check-button:hover {
            background-color: #961111;
        }

        .receive {
            padding-top: 20px;
            border-top: 1px solid #bd2020;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 15px;
            }

            .button-container {
                flex-direction: column;
                gap: 15px;
            }

            input[type="submit"] {
                width: 100%;
            }
        }
        .instruction {
                background-color: #ffffff;
                border: 3px solid #ddd; 
                border-radius: 8px;
                padding: 20px; 
                margin-top: 20px; 
                margin-bottom: 20px;
            }

            .instruction h3 {
                font-size: 20px; 
                font-weight: bold; 
                color: #bd2020;
                margin-bottom: 15px; 
                text-align: center; 
            }

            .instruction ul {
                list-style: none; 
                padding: 0;
            }

            .instruction li {
                font-size: 13px; 
                line-height: 1.3;
                margin-bottom: 10px;
                position: relative; 
                padding-left: 25px; 
            }

            .instruction li::before {
                content: "•"; 
                color: #bd2020; 
                font-size: 16px; 
                position: absolute; 
                left: 0; 
                top: 0;
                line-height: 1.3;
            }

            .instruction li strong {
                color: #333; /* Dark color for emphasized text */
            }
    </style>
</head>
<body>
    <div class="container">
        <form action="" method="POST">
            <h2>Blood Handover Panel</h2>
            <div class="instruction">
                <h3>Instructions for Using the Handover Module</h3>
                <ul>
                    <li><strong>Reference Code:</strong> Input the valid Request Reference Code in the provided field and click "Check". If valid, request details will load.</li>
                    <li><strong>Review Details:</strong> Verify the displayed information, including requester, donor, patient name, blood type, blood component, volume, hospital, and physician.</li>
                    <li><strong>Enter Receiver:</strong> Fill in the "Received By" field with the name of the person receiving the blood.</li>
                    <li><strong>Save or Cancel:</strong> Click "Save" to finalize and store the record, or "Cancel" to discard any changes.</li><br>
                    <strong>Important Notes: Ensure all data is correct before saving. For issues, contact technical support.</strong>
            </div>
            <?php if ($message): ?>
                <div class="message <?= isset($messageType) && $messageType === 'error' ? 'error' : 'success' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="refcode">
                <label for="requestref_code">Request Reference Code:</label>
                <input type="text" name="requestref_code" value="<?= isset($_POST['requestref_code']) ? htmlspecialchars($_POST['requestref_code']) : '' ?>" required>
                <input type="submit" class="check-button" value="Check">
            </div>
        </form>

        <?php if ($requestData): ?>
            <form action="" method="POST">
                <div class="section">
                    <h3>Requester Information</h3>
                    <label>Requester: <?= htmlspecialchars($requestData['requester']) ?></label>
                    <label>Donor: <?= htmlspecialchars($requestData['donor']) ?></label>
                    <label>Unique Number: <?= htmlspecialchars($requestData['unique_number']) ?></label>
                </div>

                <div class="section">
                    <h3>Donation Details</h3>
                    <label>Patient Name: <?= htmlspecialchars($requestData['patientname']) ?></label>
                    <label>Blood Type: <?= htmlspecialchars($requestData['bloodtype']) ?></label>
                    <label>Blood Component: <?= htmlspecialchars($requestData['bloodcomponent']) ?></label>
                    <label>Volume: <?= htmlspecialchars($requestData['bags']) ?></label>
                    <label>Hospital: <?= htmlspecialchars($requestData['hospital']) ?></label>
                    <label>Physician: <?= htmlspecialchars($requestData['physician']) ?></label>
                </div>

                <div class="receive">
                    <label for="received_by">Received By:</label>
                    <input type="text" name="receive" required>
                </div>

                <div class="button-container">
                    <input type="submit" class="save-button" name="save" value="Save">
                    <input type="submit" class="cancel-button" name="cancel" value="Cancel">
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Hide the message after 1.5 seconds
        window.onload = function() {
            const message = document.querySelector('.message');
            if (message) {
                setTimeout(() => {
                    message.style.display = 'none';
                }, 1500);
            }
        };

        // Confirm before saving
        document.querySelector('.save-button').addEventListener('click', function (e) {
            if (!confirm('Are you sure you want to save this record?')) {
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
