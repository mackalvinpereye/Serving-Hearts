<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

include '../UI/sidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

// Fetch user info from the users table
$userId = $_SESSION['id'];
$sql = "SELECT fullname, unique_number FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $name = $user['fullname'];
    $uniqueNumber = $user['unique_number'];
} else {
    $name = '';
    $donor = '';
    $uniqueNumber = '';
}

$message = '';  // Variable to store success or error messages

// Check if there is a pending request
$pendingRequestSql = "SELECT * FROM request WHERE unique_number = ? AND status = 'pending'";
$pendingRequestStmt = $conn->prepare($pendingRequestSql);
$pendingRequestStmt->bind_param("s", $uniqueNumber);
$pendingRequestStmt->execute();
$pendingRequestResult = $pendingRequestStmt->get_result();
$pendingRequest = $pendingRequestResult->num_rows > 0; // True if there is a pending request

function generateReferenceCode($length = 10) {
    return substr(str_shuffle(str_repeat("0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", $length)), 0, $length);
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Prepare and sanitize form data
    $requester = htmlspecialchars(trim($_POST['requester']));
    $group = htmlspecialchars(trim($_POST['group']));
    $donor = htmlspecialchars(trim($_POST['donor']));
    $unique_number = htmlspecialchars(trim($_POST['unique_number']));
    $shcim = htmlspecialchars(trim($_POST['shcim']));
    $patientname = htmlspecialchars(trim($_POST['patientname']));
    $dob = htmlspecialchars(trim($_POST['dob']));
    $ailments = htmlspecialchars(trim($_POST['ailments']));
    $hospital = htmlspecialchars(trim($_POST['hospital']));
    $bloodtype = htmlspecialchars(trim($_POST['bloodtype']));
    $bloodcomponent = htmlspecialchars(trim($_POST['bloodcomponent']));
    $bags = filter_var(trim($_POST['bags']), FILTER_VALIDATE_INT);
    $physician = htmlspecialchars(trim($_POST['physician']));
    $contactperson = htmlspecialchars(trim($_POST['contactperson']));
    $contactnum = htmlspecialchars(trim($_POST['contactnum']));
    $messviber = htmlspecialchars(trim($_POST['messviber']));

    // Check if bags is valid
    if ($bags === false || $bags < 1) {
        $message = "Invalid number of bags.";
        // Handle the error as needed
    } else {
        // Generate reference code
        $reference_code = generateReferenceCode();

        // Check for duplicate booking
        $checkSql = "SELECT * FROM request WHERE requester = ? AND hospital = ? AND patientname = ? AND dob = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ssss", $requester, $hospital, $patientname, $dob);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "Duplicate request detected! This request has already been submitted.";
        } else {
            // Handle the image upload
            $target_dir = "uploads/";
            $image_name = basename($_FILES["image"]["name"]);
            $target_file = $target_dir . time() . '_' . $image_name; // To avoid file name conflicts
            $uploadOk = 1;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check if image file is an actual image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $message = "File is not an image.";
                $uploadOk = 0;
            }

            // Check file size (limit: 5MB)
            if ($_FILES["image"]["size"] > 5000000) {
                $message = "Sorry, your file is too large.";
                $uploadOk = 0;
            }

            // Allow only specific image formats
            if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
                $message = "Sorry, only JPG, JPEG, and PNG files are allowed.";
                $uploadOk = 0;
            }

            // If everything is ok, try to upload file
            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    // Save data to database
                    $stmt = $conn->prepare("INSERT INTO request (requester, `group`, donor, unique_number, shcim, patientname, dob, ailments, hospital, bloodtype, bloodcomponent, bags, physician, contactperson, contactnum, messviber, image_path, reference_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                    // Bind parameters
                    $stmt->bind_param("sssssssssssissssss", $requester, $group, $donor, $unique_number, $shcim, $patientname, $dob, $ailments, $hospital, $bloodtype, $bloodcomponent, $bags, $physician, $contactperson, $contactnum, $messviber, $target_file, $reference_code);
                    if ($stmt->execute()) {
                        $message = "Request submitted successfully! Reference Code: $reference_code";
                    } else {
                        $message = "Error: " . $stmt->error;
                    }

                    $stmt->close();
                } else {
                    $message = "Sorry, there was an error uploading your file.";
                }
            }

            $conn->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receive Blood from Us</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #EBEBEB;
        }

        .container {
            max-width: 800px;
            margin-left: 30rem;
            margin-top: 6rem;
            background-color: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #C92A2A;
            margin-bottom: 20px;
        }

        h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #333;
            border-bottom: 2px solid #C92A2A;
            padding-bottom: 5px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="file"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .image-preview {
            width: 100%;
            height: 300px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 16px;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            background-color: #f0f0f0;
            position: relative;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: none; /* Initially hidden */
        }

        .image-preview span {
            color: #aaa;
            font-family: Arial, sans-serif;
            font-size: 14px;
            position: absolute;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .buttons button {
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .buttons .prev-btn {
            background-color: #6c757d;
            color: white;
        }

        .buttons .prev-btn:hover {
            background-color: #5a6268;
        }

        .buttons .next-btn {
            background-color: #C92A2A;
            color: white;
        }

        .buttons .next-btn:hover {
            background-color: darkred;
        }

        .buttons .submit-btn {
            background-color: #28a745;
            color: white;
        }

        .buttons .submit-btn:hover {
            background-color: #218838;
        }

        /* Disable the hover effects for disabled buttons */
        button:disabled {
            background-color: #ccc !important;
            color: #666 !important;
            cursor: not-allowed;
        }

        /* Prevent hover styles from applying to disabled buttons */
        button:disabled:hover {
            background-color: #ccc !important;
            color: #666 !important;
        }

        .disabled-btn:hover {
            background-color: #ccc;  /* Ensure it stays the same color when hovered */
            color: #666;  /* Keep the text color the same when hovered */
        }

        .progress-bar {
            height: 8px;
            background: #e0e0e0;
            border-radius: 5px;
            margin: 20px 0;
            overflow: hidden;
        }

        .progress-bar div {
            height: 100%;
            width: 0;
            background: #C92A2A;
            transition: width 0.4s ease-in-out;
        }
        .message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Blood Request Form</h2>
        <div class="progress-bar">
            <div id="progress"></div>
        </div>

        <!-- Display success or error messages -->
        <?php if (!empty($message)): ?>
            <div class="message <?= strpos($message, 'Duplicate request detected') !== false ? 'error' : 'success' ?>" id="message">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <!-- Step 1: Request Info -->
            <div class="step active" id="step-1">
                <h3>Reminders</h3>
                <label class="reminder-label">
                    <span class="red-text">PAUNAWA:</span> Pasagutan po lahat ng detalye at ihanda ang ORIGINAL na BLOOD REQUEST FORM <br> ng pasyente na dapat ay nasa bantay na tatangap ng dadaling dugo sa ospital.
                    <br><br>
                    <span class="red-text">PAUNAWA:</span> Ang dugo po ay LIBRE na ibabahagi at dadalin ng BLOOD  RIDER ng Serving Hearts Charity Inc. wala pong sinuman tao o grupo ang  maaring humingi ng kahit ano man kabayaran.
                    <br><br>
                    Pakiusap po na paki lagyan ng sagot lalo na ang numero ng tatangap sa dadalin na libreng dugo.
                </label>
                <h3>Request Information</h3>
                <label for="requester">Requester</label>
                <input type="text" name="requester" required>

                <label for="group">Group</label>
                <input type="text" name="group" required>

                <label for="donor">Donor</label>
                <input type="text" name="donor" value="<?= htmlspecialchars($name) ?>" readonly>

                <label for="unique_number">Unique Number</label>
                <input type="text" name="unique_number" value="<?= htmlspecialchars($uniqueNumber) ?>" readonly>

                <label for="shcim">SHCIM</label>
                <input type="text" name="shcim" value="Serving Heart Charity Inc Members" readonly>
            </div>

            <!-- Step 2: Patient Info -->
            <div class="step" id="step-2">
                <h3>Patient's Information</h3>
                <label for="patientname">Patient's Name</label>
                <input type="text" name="patientname" required>

                <label for="dob">Date of Birth</label>
                <input type="date" name="dob" required>

                <label for="ailments">Ailment</label>
                <input type="text" name="ailments" required>

                <label for="hospital">Hospital</label>
                <input type="text" name="hospital" required>

                <label for="bloodtype">Blood Type</label>
                <select name="bloodtype" required>
                    <option value="" disabled selected>Select Blood Type</option>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>

                <label for="bloodcomponent">Blood Component:</label>
                <select name="bloodcomponent" id="bloodcomponent" required>
                    <option value="" disabled selected>Select Blood Component</option>
                    <option value="rbc">RBC</option>
                    <option value="plasma">Plasma</option>
                    <option value="platelets">Platelets</option>
                </select>

                <label for="bags">How Many Bags?</label>
                <input type="number" name="bags" min="1" required>

                <label for="physician">Physician</label>
                <input type="text" name="physician" required>
            </div>

            <!-- Step 3: Contact Info -->
            <div class="step" id="step-3">
                <h3>Contact Person Information</h3>
                <label for="contactperson">Contact Person Name</label>
                <input type="text" name="contactperson" required>

                <label for="contactnum">Contact Number</label>
                <input type="text" name="contactnum" required>

                <label for="messviber">Messenger/Viber</label>
                <input type="text" name="messviber" required>
            </div>

            <!-- Step 4: Upload Request Form -->
            <div class="step" id="step-4">
                <label class="reminder-label">
                    <span class="red-text">PAUNAWA:</span> Paki-upload ang maayos na larawan ng orihinal na request mula sa ospital.
                </label>
                <h3>Upload Original Request Form</h3>
                <label for="imageUpload">Request Form from Hospital</label>
                <input type="file" id="imageUpload" name="image" accept="image/*" required>
                <div class="image-preview">
                    <img src="" class="preview-img" alt="Image Preview" style="display: none;">
                    <span>No Image Selected</span>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="buttons">
                <button type="button" class="prev-btn" onclick="changeStep(-1)" style="display: none;">Previous</button>
                <button type="button" class="next-btn <?php echo $pendingRequest ? 'disabled-btn' : ''; ?>" onclick="changeStep(1)" <?php echo $pendingRequest ? 'disabled' : ''; ?>>
                    <?php echo $pendingRequest ? 'There is a pending request' : 'Next'; ?>
                </button>      
                <button type="submit" class="submit-btn" style="display: none;">Submit</button>
                <button type="button" class="next-btn" onclick="window.location.href='request_table.php';">Check Request Table</button>
            </div>
        </form>
    </div>

    <script>
        let currentStep = 1; // Declare currentStep globally
        const steps = document.querySelectorAll('.step'); // Get all steps
        const prevBtn = document.querySelector('.prev-btn'); // Previous button
        const nextBtn = document.querySelector('.next-btn'); // Next button
        const submitBtn = document.querySelector('.submit-btn'); // Submit button
        const progressBar = document.getElementById('progress'); // Progress bar

        // Function to validate all required fields in the current step
        function validateStep() {
            const currentFields = steps[currentStep - 1].querySelectorAll('[required]');
            let allFilled = true;

            currentFields.forEach((field) => {
                if (!field.value.trim()) {
                    allFilled = false;
                }
            });

            nextBtn.disabled = !allFilled; // Enable or disable the Next button
        }

        // Attach validation listeners to all fields in the current step
        function attachValidation() {
            const currentFields = steps[currentStep - 1].querySelectorAll('[required]');
            currentFields.forEach((field) => {
                field.addEventListener('input', validateStep);
            });
        }

        // Function to handle step changes
        function changeStep(stepChange) {
            steps[currentStep - 1].classList.remove('active'); // Remove active class from the current step
            currentStep += stepChange; // Increment or decrement the step
            steps[currentStep - 1].classList.add('active'); // Add active class to the new step

            // Update button visibility
            prevBtn.style.display = currentStep === 1 ? 'none' : 'inline-block';
            nextBtn.style.display = currentStep === steps.length ? 'none' : 'inline-block';
            submitBtn.style.display = currentStep === steps.length ? 'inline-block' : 'none';

            // Update progress bar
            progressBar.style.width = ((currentStep - 1) / (steps.length - 1)) * 100 + '%';

            attachValidation(); // Attach validation for the new step
            validateStep();     // Validate fields in the new step
        }

        // Initialize buttons and validation
        changeStep(0);


        const fileInput = document.getElementById('imageUpload');
        const imagePreview = document.querySelector('.image-preview img');
        const placeholder = document.querySelector('.image-preview span');

        fileInput.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block'; // Show the image
                    placeholder.style.display = 'none'; // Hide the placeholder
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '';
                imagePreview.style.display = 'none'; // Hide the image
                placeholder.style.display = 'block'; // Show the placeholder
            }
        });



        // Hide the message after 2 seconds
        setTimeout(function() {
            const messageDiv = document.querySelector('.message');
            if (messageDiv) {
                messageDiv.style.display = 'none';
            }
        }, 2000);
    </script>
</body>
</html>
