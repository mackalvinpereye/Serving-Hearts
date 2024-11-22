<?php
require_once 'controllers/authController.php';

// Verify the user using token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    verifyUser($token);
}

if (isset($_GET['password-token'])) {
    $passwordToken = $_GET['password-token'];
    resetPassword($passwordToken);
}

// Redirect if the user is not logged in
if (!isset($_SESSION['id'])) {
    header('location: login.php');
    exit();
}

// Redirect if the user is an admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('location: admin_dashboard.php');
    exit();
}

// Redirect verified users
if ($_SESSION['verified']) {
    header('location: ../USER_DASHBOARD/user_dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">

    <?php
    if (!isset($pageTitle)) {
        $pageTitle = "Verify your Account";  // Only set a default title if none is set
    }
    ?>
    <title><?php echo $pageTitle; ?></title></head>
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
            height: 5rem;
            padding-left: 5px;
            padding-top: 10px;
        }

        /* Semi-circle background styling */
        .semi-circle-background {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120vw;
            height: 50vh;
            background-color: #a00; /* Red background */
            border-top-left-radius: 100%;
            border-top-right-radius: 100%;
            z-index: -1;
        }

        /* Form container styling */
        .form-div {
            background-color: aliceblue;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            padding: 40px 30px;
            text-align: center;
            width: 100%;
            max-width: 400px;
            z-index: 1;
            margin-bottom: 20vh;
            margin-top: 20vh;
        }

        /* Text paragraph styling */
        p {
            font-size: 20px;
            color: black;
            margin-bottom: 20px;
        }
        .back-to-home{
            display: block;
            margin-top: 20px;
            font-size: 0.9em;
            text-align: center;
            color: #580000;
            text-decoration: none;
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
        .logout {
            display: inline-block;
            padding: 5px 68px;
            background-color: #d9534f; /* Bootstrap's danger color */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
        }

        .logout:hover {
            background-color: #c9302c; /* Darker red on hover */
        }

        h3 {
            font-weight: bold;
            color: black !important; /* Use the parent’s color */
            text-decoration: none !important; /* Remove underline */
        }

        /* Darker blue for the button */
        .btn-blue {
            display: inline-block;
            padding: 10px 50px; /* Adjusted padding */
            background-color: #0056b3; /* Darker blue color */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            font-weight: bold;
            transition: background-color 0.3s ease;
            margin-bottom: 10px;
            white-space: nowrap; /* Prevents text from wrapping */
            max-width: 100%; /* Ensures button width adjusts to text */
            width: auto; /* Ensures button adjusts based on text width */
            line-height: 1; /* Ensure text is vertically centered */
        }

        /* Darker blue on hover */
        .btn-blue:hover {
            background-color: #003f87; /* Even darker blue on hover */
        }

        body, p, h3, strong, a {
            text-decoration: none; /* Remove underline */
            border-bottom: none;  /* Remove blue underline border */
        }
</style>
<body>
    
    <header>
        <div class="logoContent">
            <a class="logo"><img src="../WEB/images/logo.png" alt="Serving Hearts Logo"></>
        </div>
    </header>

    <div class="semi-circle-background"></div>

    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form-div login">

            <?php if(isset($_SESSION['message'])): ?>
                <div id="message" class="alert <?php echo $_SESSION['alert-class']; ?>">
                    <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['alert-class']);
                    ?>
                </div>
            <?php endif; ?>
                <body spellcheck=""> 
                    <h3>Welcome, <?php echo $_SESSION['username']; ?></h3>
                    
                    <?php if(!$_SESSION['verified']): ?>
                        <div class="alert alert-warning">
                            Hi <strong><?php echo $_SESSION['username']; ?></strong>, you need to verify your account first.
                            Sign in to your email account and click on the 
                            verification link we just emailed you at
                            <strong><?php echo $_SESSION['email']; ?></strong>
                        </div>
                    <?php endif; ?>

                    <?php if($_SESSION['verified']): {
                            header('location: user_dashboard.php');
                        }?>
                    <?php endif; ?>

                    <a href="https://mail.google.com/" class="btn btn-blue">Go to Gmail</a>

                    <a href="index.php?logout=1" class="btn btn-danger logout">Logout</a>
                </body>
            </div>
        </div>
    </div>

    <footer>
        &copy; 2024 Serving Hearts Charity Inc.
    </footer>
</body>

</html>
<script>
    // Wait for 2 seconds after the page load and hide the message
    setTimeout(function() {
        var messageDiv = document.getElementById('message');
        if (messageDiv) {
            messageDiv.style.display = 'none';
        }
    }, 2000); // 2000 milliseconds = 2 seconds

    // Store the original title
    var originalTitle = "<?php echo $pageTitle; ?>";  // PHP variable

    // Reset the title after including the content
    function resetTitle() {
        document.title = originalTitle;
    }

    // Call resetTitle() when needed
    window.onload = resetTitle; // Reset after the page has loaded
</script>