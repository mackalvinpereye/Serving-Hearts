<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['unique_number'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

include '../UI/sidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

// Get the logged-in user's unique_number
$unique_number = $_SESSION['unique_number'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Status</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <!--<link rel="stylesheet" href="requeststyle.css">-->
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin-left: 18.7rem;
            background-color: #EBEBEB;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1000px;
            padding: 30px;
            margin-top: 100px;
            margin-left: 24rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #333;
            font-weight: 700;
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
            overflow: hidden;
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

        .processed {
            color: #28a745; /* Green for processed */
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        .still-processing {
            color: #dc3545; /* Red for still processing */
            font-weight: bold;
            display: flex;
            align-items: center;
        }

        /* Correct the class names to match the PHP logic */
        .approved::before, .pending::before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .approved::before {
            background-color: #28a745; /* Green for approved */
        }

        .pending::before {
            background-color: #dc3545; /* Red for pending */
        }

        /* Search box styling */
        #bloodInventoryTable_filter {
            margin-bottom: 15px;
            text-align: right;
        }

        #bloodInventoryTable_filter label {
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }

        #bloodInventoryTable_filter input {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            width: 200px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 5px 10px;
            margin: 5px;
            border-radius: 4px;
            background-color: #007bff;
            color: white !important;
            font-size: 14px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background-color: #0056b3;
        }

    </style>
</head>

<body>
<div class="container">
    <div class="header-flex">
        <h1>List of Blood Request</h1>
    </div>

    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference Code</th>
                    <th>Hospital</th>
                    <th>Patient Name</th>
                    <th>Ailments</th>
                    <th>Blood Type</th>
                    <th>Blood Component</th>
                    <th>Request Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                $i = 1;

                // Prepare and execute the query to fetch user's requests, sorted by request_date
                $stmt = $conn->prepare("SELECT * FROM request WHERE unique_number = ? ORDER BY request_date DESC");
                $stmt->bind_param("s", $unique_number); // Use unique_number from session
                $stmt->execute();
                $result = $stmt->get_result();

                while($row = $result->fetch_assoc()): 
                    // Set the status class and text based on the 'status' column
                    $bookingStatusClass = $row['status'] == 'Approved' ? 'approved' : 'pending';
                    $bookingStatusText = $row['status'] == 'Approved' ? 'Approve' : 'Pending';
                ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['reference_code']; ?></td>
                    <td><?php echo $row['hospital']; ?></td>
                    <td><?php echo $row['patientname']; ?></td>
                    <td><?php echo $row['ailments']; ?></td>
                    <td><?php echo $row['bloodtype']; ?></td>
                    <td><?php echo $row['bloodcomponent']; ?></td>
                    <td><?php echo $row['request_date']; ?></td>
                    <td class="<?php echo $bookingStatusClass; ?>"><?php echo $bookingStatusText; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
        $('#bloodInventoryTable').DataTable({
            "paging": true,
            "pageLength": 5,
            "lengthMenu": [5, 10, 25, 50, 100],
            "scrollX": true,
            "autoWidth": false,
            "ordering": false, // Disable sorting
            "order": [[7, 'desc']] // Ensure no column is sorted by default
        });
    });
</script>
</body>
</html>
