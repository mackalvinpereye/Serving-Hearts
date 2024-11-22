<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: http://localhost/Serving%20Hearts/USER-VERIFICATION/index.php');
    exit();
}
include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Update the request status in the database
    $updateQuery = $conn->prepare("UPDATE request SET status = ? WHERE id = ?");
    $updateQuery->bind_param('si', $status, $id);
    
    if ($updateQuery->execute()) {
        echo "Success";
    } else {
        echo "Error";
    }

    $updateQuery->close();
    $conn->close();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handedover Request</title>
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="requeststyle.css">
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <style>
        .container{
            width: 1100px;
            margin-left: 18.5rem;
            margin-top: 2rem;
        }
        .new-entry-btn {
            background-color: darkred;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
        }

        .new-entry-btn:hover {
            background-color: darkred;
        }

        .export-btn {
            background-color: green; /* Green background for the Export button */
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 16px;
            margin-left: 5px;
        }

        .export-btn:hover {
            background-color: darkgreen; /* Darker green on hover */
        }

        .header-flex {
            display: flex;
            justify-content: space-between; /* Space between the title and buttons */
            align-items: center;  /* Vertically center the items */
            width: 100%;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 30px;
            color: #333;
            font-weight: 700;
            margin-right: auto; /* Pushes the title to the left */
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

        /* Button styles */
        .action-btn-container {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 13px;
        }

        .close:hover,
        .close:focus {
            color: #ff6b6b;
        }

        /* Message styling */
        .message {
            display: none;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 14px;
        }

        .success {
            background-color: #2ed573;
            color: white;
        }

        .error {
            background-color: #ff6b6b;
            color: white;
        }
    </style>

</head>
<body>

<div class="container">

    <div class="header-flex">
        <h1>List of Handedover Requests</h1>
        <a href="manage_handover.php" id="new_blood_entry" class="new-entry-btn">
            <i class="fa fa-plus"></i> New Entry
        </a>
    </div>

    <div id="message" class="message" style="display: none;"></div>

    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference Code</th>
                    <th>Unique Number</th>
                    <th>Patient Name</th>
                    <th>Blood Type</th>
                    <th>Blood Component</th>
                    <th>Information</th>
                    <th>Physician</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $requestList = $conn->query("SELECT * FROM handover_requests ORDER BY created_at DESC");
                while($row = $requestList->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['reference_code']; ?></td>
                    <td><?php echo $row['unique_number']; ?></td>
                    <td><?php echo $row['patientname']; ?></td>
                    <td><?php echo $row['bloodtype']; ?></td>
                    <td><?php echo $row['bloodcomponent']; ?></td>
                    <td>
                        <strong>Bags:</strong> <?php echo htmlspecialchars($row['bags']); ?><br>
                        <strong>Received By:</strong> <?php echo htmlspecialchars($row['received_by']); ?>
                    </td>
                    <td><?php echo $row['physician']; ?></td>
                    <td>
                        <?php if ($row['status'] == 'Delivered'): ?>
                            <span class="badge badge-success">Delivered</span>
                        <?php elseif ($row['status'] == 'Returned'): ?>
                            <span class="badge badge-danger">Returned</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Upon review</span>
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
    // Initialize DataTable once with all settings
    $('#bloodInventoryTable').DataTable({
        "paging": true,
        "pageLength": 5,
        "lengthMenu": [5, 10, 25, 50, 100],
        "scrollX": true,  // Enable horizontal scrolling
        "ordering": false  // Disable sorting
    });
});

</script>

</body>
</html>
