<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">
    <title>Yoo hoo! Are you still here?</title>
</head>
<style>
    /* Custom modal styling */
    .custom-modal .modal-content {
        background-color: #f0f8ff;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        padding: 35px;
        text-align: center;
        border: none;
    }

    .custom-modal .modal-header {
        border-bottom: 1px solid #ddd;
        padding-bottom: 15px;
    }

    .custom-modal .modal-title {
        font-size: 1.5rem;
        color: #343a40;
        font-weight: bold;
    }

    .custom-modal .modal-body {
        color: #555;
        font-size: 1rem;
    }

    .custom-modal .btn-primary {
        background-color: #007bff;
        color: #fff;
        border: none;
        font-size: 1.1rem;
        padding: 12px;
        border-radius: 8px;
        width: 100%;
        transition: background-color 0.3s, transform 0.3s;
    }

    .custom-modal .btn-primary:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }

    .custom-modal {
        display: none;
    }

    .modal-body p {
        margin-bottom: 0px;
    }
</style>

<body>

    <!-- Modal for inactivity -->
    <div class="modal fade custom-modal" id="activityModal" tabindex="-1" aria-labelledby="activityModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="activityModalLabel">Are you still there?</h5>
                </div>
                <div class="modal-body">
                    <p>It looks like you've been inactive for a while. Are you still there?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="stillHereBtn">Yes, I'm here!</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for logout -->
    <div class="modal fade custom-modal" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">You have been logged out</h5>
                </div>
                <div class="modal-body">
                    <p>Your session has expired due to inactivity. Please log in again to continue.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="loginAgainBtn">Log In Again</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Inactivity timeout and modal JavaScript -->
    <script>
        // Get the modals using their IDs
        var activityModal = new bootstrap.Modal(document.getElementById('activityModal'));
        var logoutModal = new bootstrap.Modal(document.getElementById('logoutModal'));

        // Inactivity timeout duration (in milliseconds)
        const inactivityLimit = 1000 * 60 * 5;  // Set timeout for 5 minutes
        const modalTimeoutLimit = 100000;  // Modal timeout for 30 seconds
        let inactivityTimer;
        let modalTimer;

        // Function to log the user out
        function logout() {
            // Show the logout modal
            logoutModal.show();
            
            // Delay the redirect for a set time (e.g., 2 seconds), allowing the modal to show first
            setTimeout(function() {
                window.location.href = "../USER-VERIFICATION/index.php?logout=1"; // Redirect after 2 seconds
            }, 2000); // Adjust the time as needed
        }

        // Function to reset the inactivity timer
        function resetInactivityTimer() {
            clearTimeout(inactivityTimer); // Clear the previous inactivity timer
            clearTimeout(modalTimer); // Clear any existing modal logout timer

            // Start the inactivity timer
            inactivityTimer = setTimeout(function() {
                // Show the inactivity modal
                activityModal.show();

                // Start the modal timer once the modal is shown
                modalTimer = setTimeout(function() {
                    logout(); // Log out the user if they don't click within the timeout
                }, modalTimeoutLimit); // Timeout to log out after 30 seconds if no action is taken
            }, inactivityLimit); // Start the inactivity timer for 5 minutes of inactivity
        }

        // Event listener for user activity (mouse movements, keypresses, etc.)
        document.addEventListener('mousemove', resetInactivityTimer);
        document.addEventListener('keydown', resetInactivityTimer);
        document.addEventListener('scroll', resetInactivityTimer);
        document.addEventListener('click', resetInactivityTimer);

        // When the user clicks "Yes, I'm here!", reset the inactivity timer
        document.getElementById('stillHereBtn').addEventListener('click', function() {
            // Close the inactivity modal
            activityModal.hide();

            // Reset the inactivity timer
            resetInactivityTimer();

            // Clear the modal timer as the user is still here
            clearTimeout(modalTimer);
        });

        // When the user clicks "Log In Again" after being logged out
        document.getElementById('loginAgainBtn').addEventListener('click', function() {
            // Redirect to login page or perform your login logic
            window.location.href = '../USER-VERIFICATION/login.php'; // Example: redirect to login page
        });

        // Initialize the inactivity timer when the page loads
        window.onload = resetInactivityTimer;
    </script>

</body>
</html>
