<?php require_once 'controllers/authController.php';

if (isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php'); 
    exit();
} ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">
    
    <title>Register A New Account</title>

<style>
    body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Arial', sans-serif;
            background: url(../WEB/images/backbg.png) no-repeat center center;
            background-size: cover;
    }

    .split-container {
        display: flex;
        width: 50%; /* Decrease width of the whole panel */
        max-height: 77vh; /* Decrease height slightly */
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        margin-top: 50px;
    }

    .left-panel, .right-panel {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 15px; /* Decrease padding */
        color: #fff;
    }

    .left-panel {
        background: #580000;
        flex-direction: column;
        text-align: center;
    }

    .right-panel {
        background: rgba(255, 255, 255, 0.95);
        color: #333;
        overflow: auto; /* Ensure content flows properly */
        display: flex;
        flex-direction: column; /* Stack children vertically */
        align-items: center; /* Center horizontally */
        justify-content: center; /* Center vertically */
        padding: 15px; /* Padding to give some space */
        box-sizing: border-box; /* Include padding in total size */
    }

    .container {
        max-width: 300px; /* Further decrease width */
        width: 100%;
    }
    .input-group {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        overflow: hidden;
        position: relative; /* Add position relative */
    }
    .input-group label {
        margin-right: 10px; /* Space between label and select */
        font-size: 14px; /* Consistent font size */
        color: #333; /* Text color for label */
    }

    .input-group select {
        flex: 1; /* Allow the select to take available space */
        border: 1px solid #ccc; /* Border style for select */
        border-radius: 5px; /* Match the border radius */
        padding: 6px; /* Padding for select */
        outline: none; /* Remove outline */
        font-size: 14px; /* Consistent font size */
    }


        .input-group i {
            padding: 6px; /* Decrease padding */
        }

        .input-group input {
            border: none;
            outline: none;
            padding: 6px; /* Decrease padding */
            flex: 1;
            background: transparent;
            font-size: 14px; /* Decrease font size */
        }

        .error-message {
            color: #9a3b3b;
            background: #FDBABA;
            padding: 8px;
            margin: 10px 0; /* Add vertical spacing */
            border-radius: 5px;
            text-align: center; /* Center-align error message */
            display: block; /* Ensure it takes full width */
            max-width: 100%; /* Prevent overflow */
            word-wrap: break-word; /* Break text if too long */
        }

        .btn {
            width: 100%;
            padding: 6px;
            margin-bottom: 10px;
            border: none;
            border-radius: 5px;
            background-color: #8B0000; /* Dark red color */
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 15px; /* Decrease font size */
        }
        
        h2,.left-panel p{
            font-weight: bold;
        }

        p {
            font-size: 13px;
            font-weight: bold;
        }

        .btn:hover {
            background-color: #DC143C;
            color: white;
        }

        h3 {
            font-size: 20px; /* Adjust font size if too large */
            color: #580000;
            margin: 10px;/* Avoid excessive margins */
            margin-top: 40px;
            font-weight: bold;
            text-align: center;
            word-wrap: break-word; /* Allow wrapping of long text */
        }

        a {
            font-weight: bold;
        }

        .back-to-home {
            display: block;
            margin-top: -15px; /* Decrease margin */
            font-size: 0.8em; /* Decrease font size */
            text-align: center;
            color: #580000;
            text-decoration: none;
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

                    /* Default color for the eye icon */
        .toggle-password i {
                color: #6c757d; /* Gray color */
            }

            /* When password is visible (eye icon) */
            .toggle-password i.fa-eye {
                color: #495057; /* Darker gray when the password is visible */
            }

            /* When password is hidden (eye-slash icon) */
            .toggle-password i.fa-eye-slash {
                color: #6c757d; /* Lighter gray when the password is hidden */
            }

            /* Optional: Change icon color when hovering over it */
            .toggle-password i:hover {
                color: #343a40; /* Slightly darker gray when hovered */
            }
            .toggle-password {
                position: absolute;
                right: 10px; /* Adjust the position */
                top: 50%;
                transform: translateY(-50%);
                z-index: 10; /* Ensure it's above other elements */
                cursor: pointer;
            }
            .input-group input:focus,
            .input-group select:focus {
                border: none;
                outline: none; /* Removes focus outline */
                box-shadow: none; /* Ensures no shadow appears */
                background: transparent;
            }
    </style>
</head>
<body>

    <header>
        <div class="logoContent">
            <a href="../WEB/index.html" class="logo"><img src="../WEB/images/logo.png" alt="Serving Hearts Logo"></a>
        </div>
    </header>

    <div class="split-container">
        <div class="left-panel">
            <h2>Welcome to Serving Hearts Inc.</h2>
            <p>Create your account to access all features.</p>
        </div>
        <div class="right-panel">
            <div class="container">
                <form action="signup.php" method="post">
                    <h3>Sign Up</h3>

                    <?php if(count($errors) > 0): ?>
                                <div class="alert alert-danger">
                                    <?php foreach($errors as $error): ?>
                                        <li><?php echo $error; ?></li>
                                    <?php endforeach; ?>
                                </div>
                    <?php endif; ?>

                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" class="form-control" placeholder="Username" 
                            value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-user"></i>
                        <input type="text" name="fullname" class="form-control" placeholder="Full Name" 
                            value="<?php echo isset($fullname) ? htmlspecialchars($fullname) : ''; ?>">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" class="form-control" placeholder="Email"
                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-phone"></i>
                        <input type="tel" name="phonenumber" class="form-control" placeholder="Phonenumber"
                            value="<?php echo isset($phonenumber) ? htmlspecialchars($phonenumber) : ''; ?>">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-home"></i>
                        <input type="text" name="address" placeholder="Address"
                            value="<?php echo isset($address) ? htmlspecialchars($address) : ''; ?>">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-calendar"></i>
                        <input type="date" name="dateofbirth" class="form-control"
                            value="<?php echo isset($dateofbirth) ? htmlspecialchars($dateofbirth) : ''; ?>">
                    </div>

                    <div class="input-group">
                        <i class="fas fa-venus-mars"></i>
                        <select name="gender" class="form-control">
                            <option value="select">--Select Gender--</option>
                            <option value="male" <?php echo (isset($gender) && $gender === 'male') ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo (isset($gender) && $gender === 'female') ? 'selected' : ''; ?>>Female</option>
                        </select>
                    </div>

                    <div class="input-group">
                    <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Password">
                        <span class="position-absolute end-0 top-50 translate-middle-y me-3 toggle-password" style="cursor: pointer;">
                            <i class="fas fa-eye-slash" id="togglePassword1"></i>
                        </span>
                    </div>

                    <div class="input-group">
                    <i class="fas fa-lock"></i>
                        <input type="password" id="passwordConf" name="passwordConf" class="form-control form-control-lg" placeholder="Re-enter Password">
                        <span class="position-absolute end-0 top-50 translate-middle-y me-3 toggle-password" style="cursor: pointer;">
                            <i class="fas fa-eye-slash" id="togglePassword2"></i>
                        </span>
                    </div>

                    <button type="submit" name="signup-btn" class="btn">Sign Up</button>

                    <p class="text-center">Already a member? <a href="../USER-VERIFICATION/login.php">Login</a></p>
                    <a href="../WEB/index.html" class="back-to-home">Back to Home</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<script>
    document.querySelectorAll('.toggle-password').forEach((toggleIcon) => {
        const inputField = toggleIcon.closest('.input-group').querySelector('input');
        
        // Initially hide the icon if the field is empty
        if (!inputField.value) {
            toggleIcon.style.display = 'none'; // Hide the icon if the input is empty
        }

        // Event listener for when the user types in the password field
        inputField.addEventListener('input', function () {
            // Show the eye icon if the input has text
            if (inputField.value.trim() !== '') {
                toggleIcon.style.display = 'block';
            } else {
                toggleIcon.style.display = 'none'; // Hide the icon if the input is empty
            }
        });

        // Toggle the visibility of the password when the eye icon is clicked
        toggleIcon.addEventListener('click', function () {
            const isPasswordVisible = inputField.type === 'text';

            // Toggle input field type
            inputField.type = isPasswordVisible ? 'password' : 'text';

            // Toggle icon class
            toggleIcon.querySelector('i').classList.toggle('fa-eye-slash', isPasswordVisible);
            toggleIcon.querySelector('i').classList.toggle('fa-eye', !isPasswordVisible);
        });
    });
</script>
