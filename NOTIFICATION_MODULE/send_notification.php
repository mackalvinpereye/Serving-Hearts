Leah Delgado
<?php
session_start();

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../USER-VERIFICATION/config/db.php');
include '../UI/asidebar.php';

$success = false; // Variable to track if notifications were sent successfully

if (isset($_POST['send'])) {
    $users = $_POST['users']; // Array of selected users
    $subject = htmlspecialchars(trim($_POST['subject']), ENT_QUOTES, 'UTF-8'); // Get subject
    $message = htmlspecialchars(trim($_POST['message']), ENT_QUOTES, 'UTF-8'); // Get message

    // Prepare the statement to insert subject and message
    $stmt = $conn->prepare("INSERT INTO notifications (unique_number, subject, message) VALUES (?, ?, ?)");

    foreach ($users as $user) {
        // Check if a notification for this user, subject, and message already exists
        $checkStmt = $conn->prepare("SELECT id FROM notifications WHERE unique_number = ? AND subject = ? AND message = ?");
        $checkStmt->bind_param("sss", $user, $subject, $message);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        // Insert only if no duplicate exists
        if ($result->num_rows === 0) {
            $stmt->bind_param("sss", $user, $subject, $message);
            $stmt->execute();
        }
        $checkStmt->close();
    }
    $stmt->close();

    $success = true; // Set success flag to true when notifications are sent
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Notification Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
        }

        .container {
            display: flex;
            gap: 30px;
            width: 100%;
            max-width: 1200px;
            margin-top: 5rem;
            margin-left: 13.5rem;
            height: 85vh;
        }

        .form-container, .notifications-container {
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            flex: 1;
            overflow-y: auto;
            max-height: 100%;
        }

        h2 {
            color: #1f2937;
            font-size: 1.5rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 5px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #4b5563;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 14px;
            background-color: #f9fafb;
            box-sizing: border-box;
        }

        input:focus, textarea:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 5px rgba(37, 99, 235, 0.3);
        }

        button {
            background-color: darkred;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
            width: 100%;
        }

        button:hover {
            background-color: #D21404;
        }

        .dropdown-checkbox {
            position: relative;
            margin-bottom: 15px;
        }

        .dropdown-checkbox-content {
            display: none;
            position: absolute;
            background-color: #ffffff;
            min-width: 100%;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            padding: 10px;
            z-index: 1;
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #d1d5db;
            border-radius: 6px;
        }

        .dropdown-checkbox:hover .dropdown-checkbox-content {
            display: block;
        }

        .notification {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: transform 0.2s, background-color 0.3s;
        }

        .notification:hover {
            background-color: #e5e7eb;
            transform: scale(1.02);
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
        }

        .notification-header strong {
            font-size: 1.1em;
            color: #111827;
        }

        .timestamp {
            font-size: 0.9em;
            color: #6b7280;
        }

        .notification-body {
            margin-top: 10px;
            color: #4b5563;
            display: none;
        }

        .notification.open .notification-body {
            display: block;
        }

        #selectAllBtn{
            background-color: #6b7280;
            margin-bottom: 10px;
        }

        /* Modal styles */
        .modal {
            display: none; 
            position: fixed;
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        /* Adjusted modal content */
        .modal-content {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            width: 30%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            position: fixed;
            top: 45vh; /* Adjusted to move it lower */
            left: 50%;
            transform: translateX(-50%);
        }

        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            top: 10px;
            right: 25px;
            cursor: pointer;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }

        /* Success Modal specific styles */

        .success-modal h2 {
            color: #28a745;
        }

        .success-modal p {
            color:#28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Send Notification Form -->
        <div class="form-container">
            <form method="POST" action="send_notification.php">
                <h2>Send Notification</h2>
                
                <label for="users">Select Users:</label>
                <div class="dropdown-checkbox">
                    <button type="button" onclick="toggleDropdown()">Select Users <i class="fas fa-chevron-down"></i></button>
                    <div class="dropdown-checkbox-content" id="userDropdown">
                        <button type="button" id="selectAllBtn" onclick="selectAll()">Select All</button>
                        <?php
                        $result = $conn->query("SELECT unique_number, fullname FROM users");
                        while ($row = $result->fetch_assoc()) {
                            echo "<label><input type='checkbox' name='users[]' value='{$row['unique_number']}'> {$row['fullname']}</label>";
                        }
                        ?>
                    </div>
                </div>

                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" required>

                <label for="message">Message:</label>
                <textarea id="message" name="message" required rows="9"></textarea>
                
                <button type="submit" name="send">Send Notification</button>
            </form>
        </div>

        <!-- Notifications -->
        <div class="notifications-container">
            <h2>Admin Notifications</h2>
            <?php
            $result = $conn->query("SELECT DISTINCT subject, message, timestamp FROM notifications ORDER BY timestamp DESC");

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='notification' onclick=\"toggleNotification(this)\">
                            <div class='notification-header'>
                                <strong>{$row['subject']}</strong>
                                <span class='timestamp'>{$row['timestamp']}</span>
                            </div>
                            <div class='notification-body'>{$row['message']}</div>
                        </div>";
                }
            }
            ?>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Success!</h2>
            <p>Your notification has been successfully sent.</p>
        </div>
    </div>

    <script>
        // Show modal if notifications are successfully sent
        <?php if ($success): ?>
            document.getElementById("successModal").classList.add("success-modal");
            document.getElementById("successModal").style.display = "block";
        <?php endif; ?>

        // Close the modal
        function closeModal() {
            document.getElementById("successModal").style.display = "none";
        }

        // Toggle notification details
        function toggleNotification(notification) {
            notification.classList.toggle('open');
        }

        // Toggle the user dropdown
        function toggleDropdown() {
            document.getElementById("userDropdown").classList.toggle("show");
        }

        // Select all users
        function selectAll() {
            let checkboxes = document.querySelectorAll("input[type='checkbox']");
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = true;
            });
        }

        // Close the modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target === document.getElementById("successModal")) {
                closeModal();
            }
        }
    </script>
</body>
</html>