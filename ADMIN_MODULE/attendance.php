<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="requeststyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>


    <style>
        .container{
            width: 1100px;
            margin-left: 18.5rem;
            margin-top: 2rem;
        }
        .badge {
            padding: 5px 10px;
            color: white;
            border-radius: 5px;
            font-size: 0.9em;
            text-align: center;
        }
        .badge-consented { background-color: green; }
        .badge-not-consented { background-color: red; }
        .badge-processed { background-color: green; }
        .badge-not-processed { background-color: orange; }

        .new-entry-btn {
            background-color: darkred;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }
        .new-entry-btn:hover{
            background-color: darkred;
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
        .filter-container {
            display: flex;
            gap: 40px;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            align-items: center;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
        }

        .filter-item label {
            font-weight: bold;
            font-size: 14px;
            color: #555;
            margin-bottom: 5px;
        }
        .styled-select {
            padding: 8px 12px;
            font-size: 14px;
            width: 180px; /* Adjust the value as needed */
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: 0.2s ease-in-out;
            background-color: #fff;
            color: #333;
            appearance: none; /* Removes the default dropdown arrow */
            background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 140 140'%3E%3Cpath d='M70 105L25 35h90L70 105z' fill='%23777'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px 12px;
            margin-right: 10px;
        }

        .styled-select:hover {
            border-color: #888;
            background-color: #f9f9f9;
        }

        .styled-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .styled-select option {
            padding: 10px;
            background-color: #fff;
            color: #333;
        }

        .filter-container {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .filter-label {
            font-size: 16px;
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="filter-container">
        <div class="right">
            <label for="eventIDFilter" class="filter-label">Filter by Event ID:</label>
            <select id="eventIDFilter" class="styled-select">
                <option value="">All</option>
                <?php
                $eventIDs = $conn->query("SELECT DISTINCT event_id FROM booking WHERE status = 'confirmed'");
                while ($event = $eventIDs->fetch_assoc()) {
                    echo '<option value="' . $event['event_id'] . '">' . $event['event_id'] . '</option>';
                }
                ?>
            </select>
        </div>

        <div>
            <label for="eventDateFilter" class="filter-label">Filter by Event Date:</label>
            <select id="eventDateFilter" class="styled-select">
                <option value="">All</option>
                <?php
                    $eventDates = $conn->query("SELECT DISTINCT DATE(booking_date) as event_date FROM booking WHERE status = 'confirmed'");
                    while ($date = $eventDates->fetch_assoc()) {
                        $formattedDate = date('M d, Y', strtotime($date['event_date'])); // Display format
                        echo '<option value="' . $formattedDate . '">' . $formattedDate . '</option>';
                    }
                ?>


            </select>
        </div>

    </div>

    <div class="header-flex">
        <h1>List of Confirmed Attendance</h1>
    </div>

    <div id="message" class="message" style="display: none;"></div>

    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Event ID</th>
                    <th>Event Name</th>
                    <th>Event Address</th>
                    <th>Unique Number</th>
                    <th>Donor Name</th>
                    <th>Booking Date</th>
                    <th>Status</th>
                    <th>Check In Time</th>
                    <th>Consent</th>
                    <th>Blood Processed </th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                // Query only rows with confirmed status
                    $requestList = $conn->query("SELECT *, DATE(booking_date) AS event_date FROM booking WHERE status = 'confirmed' ORDER BY booking_date DESC");
                while($row = $requestList->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['event_id']; ?></td>
                    <td><?php echo $row['event_name']; ?></td>
                    <td><?php echo $row['event_address']; ?></td>
                    <td><?php echo $row['unique_number']; ?></td>
                    <td><?php echo $row['fullname']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['booking_date'])); ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['check_in_time'])); ?></td>
                    <td>
                        <?php if ($row['consent'] == 1): ?>
                            <span class="badge badge-consented">Consented</span>
                        <?php else: ?>
                            <span class="badge badge-not-consented">Not Consented</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['blood_processed'] == 1): ?>
                            <span class="badge badge-processed">Processed</span>
                        <?php else: ?>
                            <span class="badge badge-not-processed">Not Yet Processed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>

$(document).ready(function() {
    var table = $('#bloodInventoryTable').DataTable();

    // Event ID filter
    $('#eventIDFilter').on('change', function() {
        table.column(1) // Event ID column (index 1)
            .search(this.value) // Search by dropdown value
            .draw();
    });

    // Event Date filter
    $('#eventDateFilter').on('change', function() {
        var selectedDate = $(this).val();
        console.log("Selected Date:", selectedDate);  // Debugging line to check the selected date

        if (selectedDate) {
            // Filter by exact match for the event date column (index 6)
            table.column(6).search('^' + selectedDate + '$', true, false).draw();
        } else {
            // Reset the filter if no date is selected
            table.column(6).search('').draw();
        }
    });
});



</script>

</body>
</html>