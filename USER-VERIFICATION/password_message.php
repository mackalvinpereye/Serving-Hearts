<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">

    <title>Reset Successful</title>

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
    </style>
</head>
<body>

    <header>
        <div class="logoContent">
            <a href="home.html" class="logo"><img src="../WEB/images/logo.png" alt="Serving Hearts Logo"></a>
        </div>
    </header>

    <!-- Semi-circle red background behind the form -->
    <div class="semi-circle-background"></div>

    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="form-div">
            <p>
                An email has been sent to your email address with a link to reset your
                password.
            </p>
            <a href="../USER-VERIFICATION/login.php" class="back-to-home">Back to Home</a>
        </div>
    </div>

    <footer>
        &copy; 2024 Serving Hearts Charity Inc.
    </footer>
</body>
</html>
