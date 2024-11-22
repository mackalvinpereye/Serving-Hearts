<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

date_default_timezone_set('Asia/Manila');

include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

// Fetch the count of blood components for each blood type (both positive and negative)
$bloodTypes = ['A+', 'B+', 'AB+', 'O+', 'A-', 'B-', 'AB-', 'O-'];
$bloodCounts = [];

// Fetching counts for each blood type and its components
foreach ($bloodTypes as $bloodType) {
    $components = ['Whole Blood', 'RBC', 'Plasma', 'Platelets'];
    $bloodCounts[$bloodType] = [];

    foreach ($components as $component) {
        // Query to fetch the count of each component for a specific blood type
        $stmt = $conn->prepare("SELECT COUNT(*) FROM blood_inventory WHERE blood_type = ? AND bloodcomponent = ?");
        $stmt->bind_param("ss", $bloodType, $component);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $bloodCounts[$bloodType][$component] = $row['COUNT(*)'] > 0 ? $row['COUNT(*)'] : 0; // Default to 0 if no records
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blood Inventory</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="requeststyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
        .container {
            width: 100%;
            max-width: 1100px;
            margin: 20px auto;
            padding: 0 20px;
            margin-left: 18.5rem;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .new-entry-btn, .export-btn {
            background-color: darkred;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 16px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .export-btn {
            background-color: green;
            margin-left: 5px;
        }

        .blood-type-container-positive{
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between; /* Ensures even spacing */
            margin-bottom: 20px;
            max-width: 1140px;
            margin-left: 18.5rem;
            margin-top: 20px;
        }

        .blood-type-container-negative {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: space-between; /* Ensures even spacing */
            margin-bottom: 20px;
            max-width: 1140px;
            margin-left: 18.5rem;
        }

        .blood-type-box{
            background-color: #F08080;
            color: white;
            padding: 15px;
            width: calc(25% - 15px); /* Adjust to ensure 4 boxes in a row */
            text-align: center;
            font-weight: 600;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            box-sizing: border-box;
        }

        .blood-type-box:hover {
            background-color: #f05454;
        }

        .component-list {
            margin-top: 10px;
        }

        .component-item {
            background-color: #f1f1f1;
            padding: 10px;
            border-radius: 5px;
            margin: 5px;
            color: black;
            font-size: 12px;
        }

        .component-item span {
            font-weight: 600;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .table th, .table td {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            font-size: 14px;
        }

        .table th {
            background-color: #F08080;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
        }

        .table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .table tr:hover {
            background-color: #f1f1f1;
        }

        .table td {
            font-weight: 400;
            color: #555;
        }

        @media (max-width: 768px) {
            .blood-type-box {
                width: calc(50% - 15px); /* Two boxes per row for smaller screens */
            }
        }

        @media (max-width: 480px) {
            .blood-type-box {
                width: 100%; /* One box per row for very small screens */
            }
        }
    </style>
</head>
<body>

<!-- Blood Type and Components Section -->
<div class="blood-type-container-positive">
        <?php 
        // Displaying the blood types and their components with the counts
        foreach (['A+', 'B+', 'AB+', 'O+'] as $bloodType) { 
            echo '<div class="blood-type-box">' . $bloodType . 
                '<div class="component-list">';

            // Loop through each component for the current blood type
            foreach (['Whole Blood', 'RBC', 'Plasma', 'Platelets'] as $component) {
                $count = $bloodCounts[$bloodType][$component];
                echo '<div class="component-item"><span>' . $component . ' (' . $count . ')</span></div>';
            }

            echo '</div></div>';
        }
        ?>
    </div>

    <div class="blood-type-container-negative">
        <?php 
        // Displaying the blood types and their components with the counts for negative types
        foreach (['A-', 'B-', 'AB-', 'O-'] as $bloodType) { 
            echo '<div class="blood-type-box">' . $bloodType . 
                '<div class="component-list">';

            // Loop through each component for the current blood type
            foreach (['Whole Blood', 'RBC', 'Plasma', 'Platelets'] as $component) {
                $count = $bloodCounts[$bloodType][$component];
                echo '<div class="component-item"><span>' . $component . ' (' . $count . ')</span></div>';
            }

            echo '</div></div>';
        }
        ?>
    </div>


<div class="container">

    <div class="header-flex">
        <h1>List of Blood Inventory</h1>
        <div>
            <a href="manage_donation.php" id="new_blood_entry" class="new-entry-btn">
                <i class="fa fa-plus"></i> New Entry
            </a>
            <a href="export_donation.php" id="export_data" class="export-btn">
                <i class="fa fa-download"></i> Export as Excel
            </a>
        </div>
    </div>

    <!-- Table for Blood Inventory -->
    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Unique Number</th>
                    <th>Donor Name</th>
                    <th>Blood Type</th>
                    <th>Status</th>
                    <th>Request UID</th>
                    <th>Collection Date</th>
                    <th>Expiration Date</th>
                    <th>Volume (ml)</th>
                    <th>Remarks</th>
                    <th>Additives</th>
                    <th>Blood Component</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $bloodInventory = $conn->query("SELECT * FROM blood_inventory ORDER BY expiration_date ASC");
                while($row = $bloodInventory->fetch_assoc()): 
                ?>
                <tr data-id="<?php echo $row['id']; ?>">
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['unique_number']; ?></td>
                    <td><?php echo $row['fullname']; ?></td>
                    <td><?php echo $row['blood_type']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo $row['request_uid']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['collection_date'])); ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['expiration_date'])); ?></td>
                    <td><?php echo $row['volume']; ?></td>
                    <td><?php echo $row['remarks']; ?></td>
                    <td><?php echo $row['additives']; ?></td>
                    <td><?php echo $row['bloodcomponent']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Initialize DataTable -->
<script>
$(document).ready(function() {
    $('#bloodInventoryTable').DataTable();
});
</script>

</body>
</html>
