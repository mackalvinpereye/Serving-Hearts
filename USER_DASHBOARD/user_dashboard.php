<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id']) || !isset($_SESSION['unique_number'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

include "../UI/sidebar.php";
require_once('../USER-VERIFICATION/config/db.php');

// Get the logged-in user's unique number
$loggedInUserNumber = $_SESSION['unique_number'];

// Path to the ID card
$idCardPath = '../ID_GENERATOR/ids/' . $loggedInUserNumber . '_ID_Card.png';

// Check if the ID card exists
$idCardExists = file_exists($idCardPath);

// Fetch blood type from the database
$bloodType = "Unknown"; // Default value
$query = "SELECT blood_type FROM users WHERE unique_number = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $loggedInUserNumber);
$stmt->execute();
$stmt->bind_result($bloodType);
$stmt->fetch();
$stmt->close();

// Set bloodType to "Unknown" if it is NULL or empty
if (is_null($bloodType) || $bloodType === '') {
    $bloodType = "Unknown";
}

// Fetch confirmed bookings for the logged-in user
$bookingCount = 0; // Default value
$query = "SELECT COUNT(*) FROM booking WHERE unique_number = ? AND status = 'confirmed'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $loggedInUserNumber);
$stmt->execute();
$stmt->bind_result($bookingCount);
$stmt->fetch();
$stmt->close();

$milestoneClass = '';

if ($bookingCount >= 0 && $bookingCount <= 3) {
    $milestoneClass = 'yellow';
} elseif ($bookingCount >= 4 && $bookingCount <= 7) {
    $milestoneClass = 'orange';
} elseif ($bookingCount >= 8 && $bookingCount <= 10) {
    $milestoneClass = 'red';
} else {
    $milestoneClass = 'blue'; // For 10+ bookings
}

?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <?php
    if (!isset($pageTitle)) {
        $pageTitle = "User Dashboard";
    }
    ?>
    <title><?php echo $pageTitle; ?></title>

    </head>    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #EBEBEB;
            padding: 20px;
            height: 1vh;
        }
        /* Style for the ID card image container */
        .id-card-container {
            text-align: center;
            margin-bottom: 20px;
        }

        /* ID card image style */
        .id-card-image {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        /* No ID card text */
        .no-id-card {
            text-align: center;
            font-size: 18px;
            color: #555;
            margin-bottom: 20px;
        }

        /* Action buttons container */
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: -10px;
        }

        /* Icons inside buttons */
        button i {
            margin-right: 8px; /* Space between icon and text */
            font-size: 18px; /* Adjust icon size */
        }

        /* Optionally style buttons on hover */
        .generate-button:hover i {
            transform: scale(1.1); /* Slightly enlarge icon on hover */
        }

        .download-button:hover i {
            transform: scale(1.1); /* Slightly enlarge icon on hover */
        }

        .action-buttons button {
            font-size: 16px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            margin: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .generate-button {
            background-color: darkred;
            color: white;
            padding: 12px 25px;
        }

        .generate-button:hover {
            background-color: #57000d;
            transform: scale(1.05);
        }

        .download-button {
            background-color: #00a10b;
            color: white;
            padding: 12px 25px;
        }

        .download-button:hover {
            background-color: #006e07;
            transform: scale(1.05);
        }

        /* Optional: Make sure buttons are disabled */
        button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        span {
            font-weight: bold;
        }
        .container {
            max-width: 400px;
            height: 34rem;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0.3, 0.3);
            position: fixed;
            right: 745;
            top: 83;
        }
        h2 {
            text-align: center;
            margin-top: -5px;
            margin-bottom: 10px;
        }
        .milestone-info ul {
            padding-left: 0;
            list-style-type: none;
            margin-top: 10px;
            margin-left: 33px;
        }

        .milestone-info li {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            font-weight: bold;
        }

        .milestone-info .color-box {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 8px;
        }
        /* General styles for the milestone container */
        .milestone-info {
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s;
        }

        .milestone-info.yellow {
            background: linear-gradient(135deg, #c5a800, #ffd700, #ffeb3b); /* 3-color gradient from dark yellow to gold to light yellow */
        }

        .milestone-info.orange {
            background: linear-gradient(135deg, #e65100, #ff9800, #ff5722); /* 3-color gradient from dark orange to orange to red-orange */
        }

        .milestone-info.red {
            background: linear-gradient(135deg, #d32f2f, #f44336, #b71c1c); /* 3-color gradient from dark red to red to deep red */
        }

        .milestone-info.blue {
            background: linear-gradient(135deg, #283593, #4169E1, #1e3a5f); /* 3-color gradient from dark blue to blue to navy */
        }

        .milestone-info ul {
            list-style: none;
            padding: 0;
        }

        .milestone-info ul li {
            font-size: 16px;
            font-weight: bold;
            color: #fff;
            margin-bottom: 10px;
        }

        .color-box {
            width: 20px;
            height: 20px;
            display: inline-block;
            margin-right: 10px;
            vertical-align: middle;
        }
        .box-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            width: 100%;
            position: fixed;
            left: 776;
            bottom: 142;
            width: 700;
            height: 140;
        }

        .box {
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 35px;
            text-align: center;
            position: relative;
            flex: 1;
            width: 100px;
            height: 200px;
        }
    </style>
</head>
<body>
    <h1>Welcome to Your Dashboard</h1>

    <div class="container">
        <h2>Milestone Information</h2>
        <?php if ($idCardExists): ?>
            <div class="id-card-container">
                <img src="<?php echo $idCardPath; ?>" alt="ID Card" class="id-card-image">
            </div>
        <?php else: ?>
            <p class="no-id-card">No ID Card generated yet.</p>
        <?php endif; ?>

        <div class="action-buttons">
            <form action="../ID_GENERATOR/generate_id.php" method="get" target="_blank">
                <button type="submit" class="generate-button">
                    <i class="fa fa-sync icon"></i> 
                    <span class="text">Generate ID</span>
                </button>
            </form>
            <?php if ($idCardExists): ?>
                <a href="<?php echo $idCardPath; ?>" download>
                    <button class="download-button">
                        <i class="fa fa-download"></i> 
                        <span class="text">Download ID</span>
                    </button>
                </a>
            <?php endif; ?>
        </div>

        <div class="milestone-info <?php echo $milestoneClass; ?>">
            <ul>
                <li><span class="color-box" style="background-color: #fcf047;"></span>0-3 bookings: First Tier (Yellow)</li>
                <li><span class="color-box" style="background-color: #ff9800;"></span>4-7 bookings: Second Tier (Orange)</li>
                <li><span class="color-box" style="background-color: #f44336;"></span>8-10 bookings: Third Tier (Red)</li>
                <li><span class="color-box" style="background-color: #4169E1;"></span>10+ bookings: Volunteer Level (Blue)</li>
            </ul>
        </div>
    </div>

    <div class="box-container">
            <div class="box">
                <div class="label">Total User Count:</div>
                <i class="fa-solid fa-users icon"></i>
                <div class="sub">As of Today</div>
            </div>
            <div class="box">
                <div class="label">New Users:</div>
                <i class="fa-solid fa-user-plus icon"></i>
                <div class="sub">30 Days Interval</div>
            </div>
            <div class="box">
                <div class="label">Today's Requests:</div>
                <i class="fa-solid fa-file-alt icon"></i>
                <div class="sub">As of Today</div>
            </div>
    </div>







</body>
</html>
<script>
    // Store the original title
    var originalTitle = "<?php echo $pageTitle; ?>";  // PHP variable

    // Reset the title after including the content
    function resetTitle() {
        document.title = originalTitle;
    }

    // Call resetTitle() when needed
    window.onload = resetTitle; // Reset after the page has loaded
</script>


