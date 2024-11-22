<?php
session_start();

require 'config/db.php';
require_once 'emailController.php';
require 'libraries/phpqrcode/qrlib.php'; 

date_default_timezone_set('Asia/Manila');

$errors = array();
$username = "";
$email = "";
$fullname = "";
$dateofbirth = "";
$gender = "";
$phonenumber = "";

// Upon user signup
if (isset($_POST['signup-btn'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $passwordConf = $_POST['passwordConf'];
    $fullname = $_POST['fullname'];
    $dateofbirth = $_POST['dateofbirth'];
    $gender = $_POST['gender'];
    $phonenumber = $_POST['phonenumber'];
    $address = $_POST['address'];

    // Validate user input
    $errors = []; // Initialize the errors array
    if (empty($username)) {
        $errors['username'] = "Username required";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email address is invalid";
    }
    if (empty($email)) {
        $errors['email'] = "Email is required";
    }
    if (empty($password)) {
        $errors['password'] = "Password is required";
    }
    if ($password !== $passwordConf) {
        $errors['password'] = "Confirmation password does not match";
    }
    if (empty($fullname)) {
        $errors['fullname'] = "Full Name is required";
    }
    if (empty($dateofbirth)) {
        $errors['dateofbirth'] = "Date of Birth is required";
    } else {
        $dob = new DateTime($dateofbirth);
        $now = new DateTime();
        $age = $now->diff($dob)->y;

        if ($age < 18) {
            $errors['dateofbirth'] = "You must be at least 18 years old to register.";
        }
    }
    if (empty($gender) || $gender == "select") {
        $errors['gender'] = "Choose your appropriate gender";
    }
    if (empty($phonenumber)) {
        $errors['phonenumber'] = "Phone Number required";
    }
    if (empty($address)) {
        $errors['address'] = "Address required";
    }

    // Check if email or username already exists
    $userQuery = "SELECT * FROM users WHERE email=? OR username=? LIMIT 1";
    $stmt = $conn->prepare($userQuery);
    $stmt->bind_param('ss', $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        if ($user['email'] === $email) {
            $errors['email'] = "Email already exists";
        }
        if ($user['username'] === $username) {
            $errors['username'] = "Username already exists";
        }
    }

    // Format the unique number
    $currentYear = date('y'); // Get the current year (two digits)

    // Get the last incrementing number for the current year
    $lastNumberQuery = "SELECT MAX(CAST(SUBSTRING(unique_number, 1, 5) AS UNSIGNED)) as last_number 
                        FROM users 
                        WHERE unique_number LIKE '%-$currentYear'";

    $lastNumberResult = $conn->query($lastNumberQuery);
    $lastNumber = $lastNumberResult->fetch_assoc();
    $incrementingNumber = $lastNumber['last_number'] ? $lastNumber['last_number'] + 1 : 1; // Start at 1 if no previous number

    // Format the unique number with leading zeros
    $uniqueNumber = sprintf('%05d-%02d', $incrementingNumber, $currentYear);

    if (count($errors) === 0) {
        $password = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(50));
        $verified = false;
        $verificationTokenCreatedAt = date('Y-m-d H:i:s'); // Current date and time

        $sql = "INSERT INTO users (username, email, verified, token, verification_token_created_at, password, fullname, dateofbirth, gender, phonenumber, unique_number, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssbsssssssss', $username, $email, $verified, $token, $verificationTokenCreatedAt, $password, $fullname, $dateofbirth, $gender, $phonenumber, $uniqueNumber, $address);

        if ($stmt->execute()) {
            // After successful registration, send verification email
            $user_id = $conn->insert_id;
            $_SESSION['id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['verified'] = $verified;

            // Send verification email
            sendVerificationEmail($email, $token);

            // Set flash message
            $_SESSION['message'] = "You are now logged in!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();
        } else {
            $errors['db_error'] = "Database error: Failed to Register";
        }
    }
}

// Upon user login
if (isset($_POST['login-btn'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate user input
    if (empty($username)) {
        $errors['username'] = "Username or email required";
    }
    if (empty($password)) {
        $errors['password'] = "Password required";
    }

    if (count($errors) === 0) {
        // Check if the user or admin exists
        $sql = "SELECT * FROM users WHERE email=? OR username=? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // If user exists, handle login
        if ($user) {
            // Verify the password first
            if (password_verify($password, $user['password'])) {
                // Reset failed attempts and lockout time
                $stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, lockout_time = NULL WHERE id = ?");
                $stmt->bind_param("i", $user['id']);
                $stmt->execute();

                // Log in the user
                $_SESSION['id'] = $user['id'];
                $_SESSION['unique_number'] = $user['unique_number'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['verified'] = $user['verified'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['dateofbirth'] = $user['dateofbirth'];
                $_SESSION['gender'] = $user['gender'];
                $_SESSION['phonenumber'] = $user['phonenumber'];
                $_SESSION['qr_code_path'] = $user['qr_code_path'];
                $_SESSION['role'] = 'user';

                // Set flash message
                $_SESSION['message'] = "You are now logged in!";
                $_SESSION['alert-class'] = "alert-success";
                header('location: index.php');
                exit();
            } else {
                handleFailedAttempt($user);
            }
        } else {
            // Check if the admin exists
            $sql = "SELECT * FROM admins WHERE email=? OR username=? LIMIT 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            // If admin exists, handle login
            if ($admin && password_verify($password, $admin['password'])) {
                // Store admin information in the session
                $_SESSION['admin'] = $admin; // Store the entire admin array
                
                // Generate a 2FA code
                $twoFactorCode = rand(100000, 999999); // Generate a 6-digit code
                $_SESSION['two_factor_code'] = $twoFactorCode; // Store it in the session
                $_SESSION['two_factor_code_expiry'] = time() + 300; // Set expiration time (5 minutes)

                // Send the 2FA code to the admin's email
                sendTwoFactorCode($admin['email'], $twoFactorCode);

                // Redirect to a 2FA verification page
                header('Location: verify_2fa.php');
                exit();
            } else {
                $errors['login-fail'] = "No user found";
            }
        }
    }
}

function handleFailedAttempt($user) {
    global $conn, $errors; // Access the global variables

    // Check if the user is currently locked out
    if ($user['lockout_time'] && strtotime($user['lockout_time']) > time()) {
        // Calculate remaining lockout time
        $remaining_time = strtotime($user['lockout_time']) - time();
        $remaining_minutes = ceil($remaining_time / 60);
        
        // User is still locked out; do not increment failed attempts
        $errors['login-fail'] = "Account is locked. Please try again in $remaining_minutes minutes.";
    } else {
        // Increment failed attempts only if less than 5
        $failed_attempts = $user['failed_attempts'];

        if ($failed_attempts < 5) {
            $failed_attempts++; // Increment failed attempts

            // Check if the user has reached the maximum failed attempts
            if ($failed_attempts >= 5) {
                // Set lockout time
                $lockout_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

                // Update both failed attempts and lockout time
                $stmt = $conn->prepare("UPDATE users SET failed_attempts = ?, lockout_time = ? WHERE id = ?");
                $stmt->bind_param("isi", $failed_attempts, $lockout_time, $user['id']);
                $stmt->execute();

                $errors['login-fail'] = "Account locked due to too many failed attempts. Please try again in 10 minutes.";
            } else {
                // Just update the failed attempts
                $stmt = $conn->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
                $stmt->bind_param("ii", $failed_attempts, $user['id']);
                $stmt->execute();

                $errors['login-fail'] = "Wrong Credentials. Attempt $failed_attempts of 5.";
            }
        } else {
            // User has already reached the maximum attempts, so set the lockout time
            $lockout_time = date("Y-m-d H:i:s", strtotime("+10 minutes"));

            // Update only the lockout time if maximum attempts have been reached
            $stmt = $conn->prepare("UPDATE users SET lockout_time = ? WHERE id = ?");
            $stmt->bind_param("si", $lockout_time, $user['id']);
            $stmt->execute();

            $errors['login-fail'] = "Account locked due to too many failed attempts. Please try again in 10 minutes.";
        }
    }
}


// Logout Function
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['id']);
    unset($_SESSION['username']);
    unset($_SESSION['email']);
    unset($_SESSION['verified']);
    unset($_SESSION['role']); // Also unset the role
    header('location: login.php');
    exit();
}

// Verify user by token
function verifyUser($token) {
    global $conn;
    $sql = "SELECT * FROM users WHERE token='$token' LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $update_query = "UPDATE users SET verified=1 WHERE token='$token'";

        if (mysqli_query($conn, $update_query)) {
            // Generate QR code after successful email verification
            $user_id = $user['id'];
            $username = $user['username'];
            $unique_number = $user['unique_number']; // Assuming this column exists
            $fullname = $user['fullname']; // Assuming this column exists
            $email = $user['email'];
            $phonenumber = $user['phonenumber']; // Assuming this column exists

            // Define the QR code data with additional details
            $qrData = "User ID: " . $user_id . ",\n" .
                      "Unique Number: " . $unique_number . ",\n" .
                      "Full Name: " . $fullname . ",\n" .
                      "Email: " . $email . ",\n" .
                      "Phone Number: " . $phonenumber;
            $filePath = 'qrcodes/' . $username . '.png'; // Path where the QR code will be saved

            // Ensure the directory exists
            if (!file_exists('qrcodes')) {
                mkdir('qrcodes', 0777, true);
            }

            // Generate and save the QR code image
            QRcode::png($qrData, $filePath, QR_ECLEVEL_L, 10, 2);

            // Store the QR code path in the database
            $sql = "UPDATE users SET qr_code_path=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('si', $filePath, $user_id);
            $stmt->execute();

            // Log User In
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['verified'] = 1;

            // Set flash message
            $_SESSION['message'] = "Your email address was successfully verified!";
            $_SESSION['alert-class'] = "alert-success";
            header('location: index.php');
            exit();
        }
    } else {
        echo 'User not found';
    }
}


// If user clicks on forgot password button
if (isset($_POST['forgot-password'])) {
    $email = $_POST['email'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Email address is invalid";
    }
    if (empty($email)) {
        $errors['email'] = "Email required";
    }

    if (count($errors) == 0) {
        $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = mysqli_query($conn, $sql);
        $user = mysqli_fetch_assoc($result);
        $token = $user['token'];
        sendPasswordResetLink($email, $token);
        header('location: password_message.php');
        exit(0);
    }
}

// If user clicked on the reset password button
if (isset($_POST['reset-password-btn'])) {
    $password = $_POST['password'];
    $passwordConf = $_POST['passwordConf'];

    // Validation
    if (empty($password) || empty($passwordConf)) {
        $errors['password'] = "Password required";
    } elseif ($password !== $passwordConf) {
        $errors['password'] = "Confirmation password does not match";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors['password'] = "Password must be at least 8 characters long, include one uppercase letter, and one number";
    }

    // If no errors, proceed
    if (count($errors) === 0) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Ensure email is set in the session
        if (!isset($_SESSION['email'])) {
            header('location: login.php'); // Redirect to login if session expired
            exit(0);
        }

        $email = $_SESSION['email'];

        // Update the password securely using prepared statements
        $sql = "UPDATE users SET password=? WHERE email=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $passwordHash, $email);
        
        if ($stmt->execute()) {
            // Set session variable to indicate success
            $_SESSION['password_reset_success'] = true;

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Redirect back to reset_password.php to display the modal
            header('location: reset_password.php');
            exit(0);
        } else {
            $errors['db_error'] = "Failed to reset password. Please try again.";
        }
    }
}

function resetPassword($token) {
    global $conn;

    // Check if the token is valid and if it was generated recently
    $sql = "SELECT * FROM users WHERE token='$token' AND reset_token_generated_at >= NOW() - INTERVAL 15 MINUTE LIMIT 1"; // Token is valid for 15 minutes
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['email'] = $user['email'];
        header('location: reset_password.php');
        exit(0);
    } else {
        // Token is invalid or expired
        echo "This password reset link is invalid or has expired.";
    }
}