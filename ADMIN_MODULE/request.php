<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
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
    <title>Request</title>
    
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
        /* Modal Styling */
        #imageModal {
            display: none; /* Hidden by default */
            position: fixed;
            left: 0;
            top: -30px;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.9);
            z-index: 1000;
            overflow: auto;
        }

        #modalImage {
            margin: auto;
            display: block;
            width: 80%;  /* Adjust width as per your preference */
            max-width: 600px;  /* Set a max width */
            height: auto;
            border-radius: 10px;
            object-fit: contain;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
        }

        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
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
    </style>
</head>
<body>

<div class="container">

    <div class="header-flex">
        <h1>List of Requests</h1>
    </div>

    <div id="message" class="message" style="display: none;"></div>

    <div class="table-wrapper">
        <table class="table" id="bloodInventoryTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Reference Code</th>
                    <th>Requester</th>
                    <th>Group</th>
                    <th>Donor</th>
                    <th>Unique Number</th>
                    <th>SHCIM</th>
                    <th>Patient Name</th>
                    <th>Date of Birth</th>
                    <th>Ailments</th>
                    <th>Hospital</th>
                    <th>Blood Type</th>
                    <th>Blood Component</th>
                    <th>Bags</th>
                    <th>Physician</th>
                    <th>Contact Person</th>
                    <th>Contact Number</th>
                    <th>Messenger/Viber</th>
                    <th>Request Date</th>
                    <th>Original Request Form</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 1;
                $requestList = $conn->query("SELECT * FROM request ORDER BY request_date DESC");
                while($row = $requestList->fetch_assoc()): 
                ?>
                <tr>
                    <td><?php echo $i++ ?></td>
                    <td><?php echo $row['reference_code']; ?></td>
                    <td><?php echo $row['requester']; ?></td>
                    <td><?php echo $row['group']; ?></td>
                    <td><?php echo $row['donor']; ?></td>
                    <td><?php echo $row['unique_number']; ?></td>
                    <td><?php echo $row['shcim']; ?></td>
                    <td><?php echo $row['patientname']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['dob'])); ?></td>
                    <td><?php echo $row['ailments']; ?></td>
                    <td><?php echo $row['hospital']; ?></td>
                    <td><?php echo $row['bloodtype']; ?></td>
                    <td><?php echo $row['bloodcomponent']; ?></td>
                    <td><?php echo $row['bags']; ?></td>
                    <td><?php echo $row['physician']; ?></td>
                    <td><?php echo $row['contactperson']; ?></td>
                    <td><?php echo $row['contactnum']; ?></td>
                    <td><?php echo $row['messviber']; ?></td>
                    <td><?php echo date('M d, Y', strtotime($row['request_date'])); ?></td>
                    <td>
                        <?php if (!empty($row['image_path'])): ?>
                            <img src="<?php echo $row['image_path']; ?>" alt="Image" style="width:50px;cursor:pointer;" class="modal-trigger" data-src="<?php echo $row['image_path']; ?>">
                        <?php else: ?>
                            No image
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($row['status'] == 'Approved'): ?>
                            <span class="badge badge-success">Approved</span>
                        <?php elseif ($row['status'] == 'Rejected'): ?>
                            <span class="badge badge-danger">Rejected</span>
                        <?php else: ?>
                            <span class="badge badge-secondary">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btn-container">
                            <button class="action-btn approve-btn" type="button" data-id="<?php echo $row['id']; ?>">Approve</button>
                            <?php // if ($row['status'] != 'Pending') echo 'disabled'; ?><!--Approve</button>-->
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add the modal container to display the full-size image -->
<div id="imageModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="modalImage">
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTable
        $('#bloodInventoryTable').DataTable({
            "paging": true,
            "pageLength": 5,
            "lengthMenu": [5, 10, 25, 50, 100],
            "scrollX": true // Enable horizontal scrolling in the DataTable
        });

        // Modal image handling using event delegation
        var modal = document.getElementById("imageModal");
        var modalImg = document.getElementById("modalImage");
        var span = document.getElementsByClassName("close")[0];

        // Delegate click event to modal-trigger through document
        $(document).on('click', '.modal-trigger', function() {
            var imgSrc = $(this).attr('data-src'); // Get the image path from data-src attribute
            modal.style.display = "block"; // Show the modal
            modalImg.src = imgSrc; // Set the clicked image as modal content
        });

        // Close the modal when the user clicks on the "x" button
        span.onclick = function() { 
            modal.style.display = "none";
        }

        // Close the modal when the user clicks anywhere outside of the image
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Approve button functionality
        $('.approve-btn').on('click', function() {
            var requestId = $(this).data('id');
            updateRequestStatus(requestId, 'Approved');
        });

        // Reject button functionality
        $('.reject-btn').on('click', function() {
            var requestId = $(this).data('id');
            updateRequestStatus(requestId, 'Rejected');
        });

        function updateRequestStatus(requestId, status) {
            $.ajax({
                url: 'request.php', // Ensure this URL is correct
                type: 'POST',
                data: {
                    id: requestId,
                    status: status
                },
                success: function(response) {
                    // Set the message text and classes based on status
                    if (status === 'Approved') {
                        $('#message').text('Request has been approved.')
                            .removeClass('error').addClass('success') // Add success class for green
                            .show();
                    } else if (status === 'Rejected') {
                        $('#message').text('Request has been rejected.')
                            .removeClass('success').addClass('error') // Add error class for red
                            .show();
                    }

                    // Delay reload to allow the user to read the message
                    setTimeout(function() {
                        location.reload(); // Reload the page after a delay
                    }, 1000); // Adjust the time as needed (3000 ms = 3 seconds)
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error); // Log the error for debugging
                    $('#message').text('An error occurred. Please try again.')
                        .removeClass('success').addClass('error') // Ensure error class is added
                        .show();
                    
                    // Delay reload to allow the user to read the message
                    setTimeout(function() {
                        location.reload(); // Reload the page after a delay
                    }, 1000); // Adjust the time as needed (3000 ms = 3 seconds)
                }
            });
        }
    });
</script>

</body>
</html>
