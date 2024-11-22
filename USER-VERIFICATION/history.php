<?php
session_start();


// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}
include '../UI/sidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

// Get the logged-in user's ID
$user_id = $_SESSION['id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance History</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="requeststyle.css">
    
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

        /* Icon styling */
        .processed::before, .still-processing::before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .processed::before {
            background-color: #28a745;
        }

        .still-processing::before {
            background-color: #dc3545;
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
        <h1>List of Bookings Attended</h1>
    </div>

    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Event ID</th>
                    <th>Event Name</th>
                    <th>Booking Date</th>
                    <th>Check In Time</th>
                    <th>Status</th>
                    <th>Blood Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $requestList = $conn->query("SELECT * FROM booking WHERE user_id = '$user_id' ORDER BY created_at ASC");
                while($row = $requestList->fetch_assoc()): 
                    $bloodStatusClass = $row['blood_processed'] == 1 ? 'processed' : 'still-processing';
                    $bloodStatusText = $row['blood_processed'] == 1 ? 'Processed' : 'Still Processing';
                ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['event_id']; ?></td>
                    <td><?php echo $row['event_name']; ?></td>
                    <td><?php echo $row['booking_date']; ?></td>
                    <td><?php echo $row['check_in_time']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td class="<?php echo $bloodStatusClass; ?>"><?php echo $bloodStatusText; ?></td>  
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
        "autoWidth": false, // Disable autoWidth to maintain manual width settings
        "ordering": false // Disable sorting for all columns
    });
});

</script>
</body>
</html>