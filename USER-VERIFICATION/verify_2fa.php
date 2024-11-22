<?php
session_start();

// Redirect user if they try to access this page without needing 2FA
if (!isset($_SESSION['two_factor_code'])) {
    header('Location: admin_dashboard.php'); // Redirect to the dashboard
    exit();
}

// Check if the user is trying to verify the 2FA code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $inputCode = $_POST['two_factor_code'];

    // Check if the input code matches the generated code
    if (isset($_SESSION['two_factor_code']) && $inputCode == $_SESSION['two_factor_code']) {
        // Successful 2FA verification
        unset($_SESSION['two_factor_code']); // Clear the code from the session

        // Log in the admin using the information from the session
        if (isset($_SESSION['admin'])) {
            $admin = $_SESSION['admin'];
            $_SESSION['id'] = $admin['id'];
            $_SESSION['username'] = $admin['username'];
            $_SESSION['email'] = $admin['email'];
            $_SESSION['role'] = 'admin'; // Set role to admin

            // Set flash message
            $_SESSION['message'] = "You are now logged in!";
            $_SESSION['alert-class'] = "alert-success";
            header('Location: admin_dashboard.php'); // Redirect to admin dashboard
            exit();
        }
    } else {
        // Failed 2FA
        $errors['2fa'] = "Invalid verification code.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">

    <title>2FA Verification</title>
    <style>
        /* General page styling */
        body {
            background: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        /* Header styling */
        header {
            width: 100%;
            position: fixed;
            left: 0;
            top: 0;
            padding: 5px 2%;
            display: flex;
            justify-content: space-between;
            z-index: 10;
            background-color: white;
        }

        .logoContent {
            display: flex;
            align-items: center;
        }

        .logo img {
            height: 70px;
            padding-left: 5px;
            padding-top: 10px;
        }

        /* Semi-circle background styling */
        .semi-circle-background {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120vw; /* Full width of the viewport */
            height: 50vh; /* Adjust height for the semi-circle */
            background-color: #a00; /* Red color */
            border-top-left-radius: 100%;
            border-top-right-radius: 100%;
            z-index: -1; /* Ensure it stays behind all content */
        }

        .container {
            display: flex;
            justify-content: center;  /* Horizontally centers the form */
            align-items: center;      /* Vertically centers the form */
            min-height: 100vh;         /* Ensures full viewport height */
        }

        /* Form styling */
        .form-div {
            background-color: aliceblue;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            padding: 50px 40px;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .form-group {
            position: relative;
        }

        /* Styling the form heading */
        .form-div h3 {
            margin-bottom: 10px;
            color: #343a40; /* Dark text color */
            font-weight: bold;
            font-size: calc(1.3rem + .6vw);
            margin-top: 0px;
        }

        /* Form input fields */
        .form-group input {
            border-radius: 5px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ced4da;
            width: 92%;
        }


        /* Submit button */
        .btn-primary {
            background-color: #A1A1A1;
            color: #343a40;
            border: none;
            font-size: 18px;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background-color: #710C04;
            color: white;
        }

        /* Error alert styling */
        .alert-danger {
            font-size: 0.9rem;
            border-radius: 5px;
            padding: 10px;
            margin-top: 10px;
        }

        /* Text paragraph styling */
        p {
            font-size: 0.95rem;
            color: #6c757d; /* Soft text color */
            margin-bottom: 20px;
            line-height: 1.6; /* Adjust line height for more space between lines */
        }

        /* Footer styling */
        footer {
            position: fixed;
            bottom: 10px;
            width: 100%;
            text-align: center;
            font-size: 0.85rem;
            color: #C5C6D0;
        }

        .error {
            font-size: 0.9rem;
            color: #8b0000; /* Dark red color for error text */
            background-color: #f8d7da; /* Light red background */
            border: 1px solid #f5c6cb; /* Red border */
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            margin-bottom: 10px;
            text-align: center;
        }
    </style>
</head>
<body>

    <header>
        <div class="logoContent">
            <a class="logo"><img src="../WEB/images/logo.png" alt="Serving Hearts Logo"></a>
        </div>
    </header>

    <div class="semi-circle-background"></div>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="form-div">
            <form method="POST">
                <h3>Enter the 2FA Code</h3>

                <p>
                    The 2FA Code has been sent to the <br>
                    registered email of this admin account.
                </p>

                <?php if (!empty($errors['2fa'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['2fa']) ?></div>
                <?php endif; ?>

                <div class="form-group mb-3 position-relative">
                    <input type="text" id="code" name="two_factor_code" class="form-control form-control-lg" placeholder="OTP Code">
                </div>

                <div class="form-group mb-3">
                    <button type="submit" name="verify-2fa-code" class="btn btn-primary btn-lg w-100">
                        Verify Code
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        &copy; 2024 Serving Hearts Charity Inc.
    </footer>
</body>
</html>