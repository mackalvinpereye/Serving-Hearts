<?php require_once 'controllers/authController.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">

    <title>Reset Password</title>

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
        }

        .form-group {
            position: relative;
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
            margin-top: 10px;
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

        /* Modal styling to match form */
        .custom-modal .modal-content {
            background-color: aliceblue;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
            padding: 40px 30px;
            text-align: center;
        }

        .custom-modal .modal-header {
            border-bottom: none;
            color: #343a40;
            font-weight: bold;
        }

        .custom-modal .modal-body {
            color: #6c757d;
        }

        .custom-modal .btn-primary {
            background-color: #A1A1A1;
            color: #343a40;
            border: none;
            font-size: 18px;
            padding: 10px;
            border-radius: 5px;
            width: 100%;
        }

        .custom-modal .btn-primary:hover {
            background-color: #710C04;
        }

        #togglePassword,
        #togglePasswordSlash {
            cursor: pointer;
            padding: 10px;
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
    
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="form-div">
            <form action="reset_password.php" method="post">
                <h3>Reset your Password</h3>

                <p>
                    Enter your new password here. The change will take effect 
                    immediately after inputting your new password.
                </p>

                <?php if(count($errors) > 0): ?>
                    <div class="alert alert-danger">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group mb-3 position-relative">
                    <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="New Password">
                    <span class="position-absolute end-0 top-50 translate-middle-y me-3 toggle-password" style="cursor: pointer;">
                        <i class="fas fa-eye-slash" id="togglePassword1"></i>
                    </span>
                </div>

                <div class="form-group mb-3 position-relative">
                    <input type="password" id="passwordConf" name="passwordConf" class="form-control form-control-lg" placeholder="Re-enter New Password">
                    <span class="position-absolute end-0 top-50 translate-middle-y me-3 toggle-password" style="cursor: pointer;">
                        <i class="fas fa-eye-slash" id="togglePassword2"></i>
                    </span>
                </div>

                <div class="form-group mb-3">
                    <button type="submit" name="reset-password-btn" class="btn btn-primary btn-lg w-100">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <footer>
        &copy; 2024 Serving Hearts Charity Inc.
    </footer>

    <!-- Semi-circle background -->
    <div class="semi-circle-background"></div>

    <!-- Success Modal -->
    <?php if (isset($_SESSION['password_reset_success'])): ?>
    <div class="modal fade custom-modal" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="successModalLabel">Password Reset Successful</h5>
                </div>
                <div class="modal-body">
                    Your password has been successfully changed. Please use your new password to log in.
                </div>
                <div class="modal-footer">
                    <a href="login.php" class="btn btn-primary w-100">Go to Login</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Trigger the modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();
        });
    </script>
    <?php unset($_SESSION['password_reset_success']); ?>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle password visibility function
    document.querySelectorAll('.toggle-password').forEach((toggleIcon) => {
        // Initially hide the icon if the field is empty
        const inputField = toggleIcon.closest('.form-group').querySelector('input');
        if (!inputField.value) {
            toggleIcon.style.display = 'none'; // Hide the icon if the input is empty
        }

        // Event listener for when user types in the password field
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
            this.querySelector('i').classList.toggle('fa-eye-slash', isPasswordVisible);
            this.querySelector('i').classList.toggle('fa-eye', !isPasswordVisible);
        });
    });
</script>
</body>
</html>