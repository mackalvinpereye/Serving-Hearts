<?php require_once 'controllers/authController.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">

    <title>Forgot Password</title>

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
            height: 5rem;
            padding-left: 5px;
            padding-top: 10px;
        }

        /* Adjusted arc background styling */
        .semi-circle-background {
            position: fixed;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 120vw;  /* Full width of viewport */
            height: 50vh;  /* Reduced height for a smaller arc */
            background-color: #a00; /* Red background */
            border-top-left-radius: 100%;
            border-top-right-radius: 100%;
            z-index: -1;
        }

        /* Form styling */
        .form-div {
            background-color: aliceblue;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            padding: 40px 30px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            z-index: 1;
            margin-bottom: 20vh;
            margin-top: 20vh;
        }

        /* Styling the form heading */
        .form-div h3 {
            margin-bottom: 15px;
            color: #343a40; /* Dark text color */
            font-weight: bold;
        }

        /* Form input fields */
        .form-group input {
            border-radius: 5px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ced4da;
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
        }

        .btn-primary:hover {
            background-color: #710C04;
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
    </style>

</head>
<body>
    <header>
        <div class="logoContent">
            <a href="../WEB/home.html" class="logo"><img src="../WEB/images/logo.png" alt="Serving Hearts Logo"></a>
        </div>
    </header>

    <!-- Semi-circle red background behind the form -->
    <div class="semi-circle-background"></div>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="form-div">
            <form action="forgot_password.php" method="post">
                <h3>Recover your Password</h3>

                <p>
                    Please enter the email address you used to sign up on this site,
                    and we will assist you in recovering your password.
                </p>

                <?php if(count($errors) > 0): ?>
                    <div class="alert alert-danger">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group mb-3">
                    <input type="email" name="email" class="form-control form-control-lg" placeholder="Email Address">
                </div>

                <div class="form-group mb-3">
                    <button type="submit" name="forgot-password" class="btn btn-primary btn-lg w-100">
                        Recover your Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer with copyright only -->
    <footer>
        &copy; 2024 Serving Hearts Charity Inc.
    </footer>
</body>
</html>
