<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

require_once('../USER-VERIFICATION/config/db.php');

// Get the logged-in user's ID
$user_id = $_SESSION['id'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="requeststyle.css">
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1200px;
            margin: auto;
            padding: 20px;
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .new-entry-btn {
            display: block;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
        }
        .new-entry-btn:hover {
            background-color: #0056b3;
        }
        .table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 10px;
            border: 1px solid #dee2e6;
            text-align: left;
        }
        .table th {
            background-color: #808080;
            color: white;
        }
        #bloodInventoryTable_filter {
            margin-bottom: 15px;
        }
        /* Status-specific styling */
        .processed {
            color: #28a745; /* Green for processed */
            font-weight: bold;
        }
        .still-processing {
            color: #dc3545; /* Red for still processing */
            font-weight: bold;
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
        // Initialize DataTable
        $('#bloodInventoryTable').DataTable({
            "paging": true,
            "pageLength": 5,
            "lengthMenu": [5, 10, 25, 50, 100],
            "scrollX": true,
            "autoWidth": false // Disable autoWidth to maintain manual width settings
        });
    });
</script>
</body>
</html>


