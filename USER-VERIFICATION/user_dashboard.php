<?php 
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['verified']) || !$_SESSION['verified']) {
    header('Location: http://localhost/Serving%20Hearts/USER-VERIFICATION/index.php');
    exit();
}

$userId = $_SESSION['id'];

// Initialize variables
$id = "";
$username = "";
$email = "";
$fullname = "";
$dateofbirth = "";
$gender = "";
$phonenumber = "";

// Database connection details
$servername = "localhost";
$dbUsername = "root";
$password = "";
$dbname = "serving-hearts";

// Create connection
$conn = new mysqli($servername, $dbUsername, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch user data
$sql = "SELECT id, username, email, fullname, dateofbirth, gender, phonenumber FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();

$id = $userData['id'] ?? '';
$username = $userData['username'] ?? '';
$email = $userData['email'] ?? '';
$fullname = $userData['fullname'] ?? '';
$dateofbirth = $userData['dateofbirth'] ?? '';
$gender = $userData['gender'] ?? '';
$phonenumber = $userData['phonenumber'] ?? '';

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Profile</title>

    <link rel="icon" type="image/x-icon" href="http://localhost/Serving%20Hearts/WEB/images/logo.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style type="text/css">
        @import url('https://fonts.googleapis.com/css2?family=Exo:wght@200;300;400;500;700&display=swap');
        @import url('https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700,800,900&display=swap');

        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            list-style-type: none;
            text-decoration: none;
            font-family: 'Poppins', sans-serif;
        }

        .sidebar {
            width: 345px;
            position: fixed;
            left: 0;
            top: 0;
            height: 100%;
            background-color: #900603;
            z-index: 100;
        }

        .sidebar-brand {
            height: 90px;
            padding: 1rem 0rem 1 rem 2rem;
            color: #fff;
        }

        .sidebar-brand img {
            margin-top: 5px;
            width: 160px;
            height: 100px;
            margin-left: 97px;
        }

        .sidebar-menu {
            margin-top: 3em;
        }

        .sidebar-menu li {
            width: 100%;
            margin-bottom: 2rem;
            padding-left: 2rem;
        }

        .sidebar-menu a {
            padding-left: 1rem;
            display: block;
            color: #fff;
            font-size: 1.1 rem;
        }

        .sidebar-menu a:hover {
            background-color: #fff;
            padding-top: 1rem;
            padding-bottom: 1rem;
            color: #900603;
            border-radius: 30px 0px 0px 30px;
        }

        .sidebar-menu a span:first-child {
            font-size: 2rem;
            padding-right: 1rem;
        }
        .logout {
            margin-top: 180px;
        }

        .main-content {
            margin-left: 345px;
        }

        header {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            position: fixed;
            left: 345px;
            width: calc(100% - 345px);
            top: 0;
            z-index: 100;
        }

        header h1 {
            color: #222;
        }

        header label span {
            font-size: 1.7rem;
            padding-right: 1rem;
        }

        .search-wrapper {
            border: 1px solid #ccc;
            border-radius: 30px;
            height: 50px;
            width: 500px;
            display: flex;
            align-items: center;
            overflow-x: hidden;
        }

        .search-wrapper span {
            display: inline-block;
            padding: 0rem 1rem;
            font-size: 1.5rem;
        }

        .search-wrapper input {
            height: 100%;
            padding: .5rem;
            border: none;
            outline: none;
            width: 500px;
        }

        .user-wrapper {
            display: flex;
            align-items: center;
            margin-right: 50px;
        }

        .user-wrapper img {
            border-radius: 50%;
            margin-right: 1rem;
        }

        .user-wrapper small {
            display: inline-block;
        }

        main {
            margin-top: 60px;
            padding: 3rem 5rem;
            background: #f1f5f9;
            min-height: calc(100vh - 90px);
            height: 60px;
            overflow: auto;
        }

        main h2 {
            font-size: 35px;
            color: #900603;
        }

        main .inside h1 {
            margin-top: 50px;
            margin-left: 50px;
            font-size: 30px;
            font-weight: 600;
        }

        main .inside h1 hr {
            background-color: #900603;
            height: 3px;
            border-width: 0;
        }

        main .inside .ucontent {
            margin-left: 50px;
            font-size: 25px;
            font-weight: 400;
        }

        main .inside .ucontent img {
            border-radius: 50%;
            width: 250px;
            height: 260px;
        }

        main .inside .ucontent table .ucontent-under th {
            text-align: left;
            padding-right: 1rem;
            padding-left: 1rem;
            padding-top: 2rem;
            padding-bottom: 2rem;
            font-weight: 500;
            background-color: #ccc;
            color: #900603;
        }

        main .inside .ucontent table .ucontent-under td {
            padding-top: 2rem;
            width: 200px;
            padding-left: 2rem;
            padding-bottom: 2rem;
        }
    </style>

</head>
<body>

    <input type="checkbox" id="nav-toggle">
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="http://localhost/Serving%20Hearts/WEB/images/logo.png">
        </div>

        <div class="sidebar-menu">
            <ul>
                <li>
                    <a href="http://localhost/Serving%20Hearts/USER-VERIFICATION/user_dashboard.php" class="active">
                        <span class="fas fa-home"></span>
                        <span>Profile</span></a>
                </li>
                <li>
                    <a href="http://localhost/Serving%20Hearts/BOOKING/user_booking.php">
                        <span class="fas fa-hand-holding-medical"></span>
                        <span>Book a Blood Donation</span></a>
                </li>
                <li>
                    <a href="http://localhost/Serving%20Hearts/RECIPIENT/user_receive.php">
                        <span class="fas fa-heartbeat"></span>
                        <span>Receive Blood</span></a>
                </li>

                <li>
                    <a href="http://localhost/Serving%20Hearts/BOOKING/user_drive_locator.php">
                        <span class="fas fa-map-marker-alt"></span>
                        <span>&nbsp;Drive Locator</span></a>
                </li>

                <li>
                    <a href="Profile-BookHistory.php">
                        <span class="fas fa-notes-medical"></span>
                        <span>&nbsp;Donation History</span></a>
                </li>
                
                <li class="logout">
                    <a href="index.php?logout=1">
                        <span style="font-size: 1.5rem">&nbsp;Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <header>
            <h1>
                Dashboard
            </h1>

            <div class="user-wrapper">
                <h4>Hello, <?php echo htmlspecialchars($username); ?></h4>
            </div>

        </header>

        <main>
            <h2>User Profile</h2>

            <div class="inside">
                <h1>INFORMATION<hr></h1><br><br>

                <div class="ucontent">
                    <table>
                        <tr>
                            <th></th>
                            <td>
                                <table class="ucontent-under">
                                    <tr>
                                        <th>Name:</th>
                                        <td><?php echo htmlspecialchars($unique_number); ?></td>
                                    </tr>

                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo htmlspecialchars($email); ?></td>
                                    </tr>

                                    <tr>
                                        <th>Date of Birth:</th>
                                        <td>
                                            <?php echo htmlspecialchars($dateofbirth); ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Age:</th>
                                        <td>
                                            <?php
                                                $birthDate = new DateTime($dateofbirth);
                                                $currentDate = new DateTime();
                                                $age = $currentDate->diff($birthDate)->y;
                                                echo htmlspecialchars($age);
                                            ?>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Phone Number:</th>
                                        <td><?php echo htmlspecialchars($phonenumber); ?></td>
                                    </tr>

                                    <tr>
                                        <th>Gender:</th>
                                        <td><?php echo htmlspecialchars($gender); ?></td>
                                    </tr>

                                </table>
                            </td>
                        </tr>
                        <tr>
                        </tr>
                    </table>
                    
                </div>

            </div>
        </main>

    </div>    

</body>
</html>

<?php 
$conn->close();
?>
