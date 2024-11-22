<?php
require_once('../USER-VERIFICATION/config/db.php');

date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id'])) {
        $id = $_POST['id'];
        error_log("ID received on server: " . $id);  // Log the ID received on the server

        // Check if request contains updated information to save
        if (isset($_POST['blood_type'], $_POST['volume'], $_POST['expiration_date'], $_POST['remarks'], $_POST['collection_date'], $_POST['additives'])) {

            // Log the unique_number input
            error_log("Unique Number: " . (isset($_POST['unique_number']) ? $_POST['unique_number'] : 'Not provided'));

            // Fetch the record first to check if unique_number already exists
            $fetch_query = "SELECT unique_number FROM blood_inventory WHERE id = ?";
            if ($fetch_stmt = $conn->prepare($fetch_query)) {
                $fetch_stmt->bind_param("i", $id); // 'i' for integer
                $fetch_stmt->execute();
                $fetch_result = $fetch_stmt->get_result();
                
                if ($fetch_result->num_rows > 0) {
                    $record = $fetch_result->fetch_assoc();
                    
                    // Always update the unique_number, even if it's empty
                    // Ensure dates are formatted correctly when updating
                    $collection_date = date('Y-m-d H:i:s', strtotime($_POST['collection_date']));
                    $expiration_date = date('Y-m-d', strtotime($_POST['expiration_date']));

                    $query = "UPDATE blood_inventory SET 
                                unique_number = ?, 
                                blood_type = ?, 
                                volume = ?, 
                                expiration_date = ?, 
                                remarks = ?, 
                                collection_date = ?, 
                                additives = ?, 
                                fullname = ?, 
                                status = ? 
                            WHERE id = ?";

                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssssssssi", $_POST['unique_number'], $_POST['blood_type'], $_POST['volume'], $expiration_date, $_POST['remarks'], $collection_date, $_POST['additives'], $_POST['fullname'], $_POST['status'], $id);

                    
                    // Execute the update query
                    if ($stmt->execute()) {
                        // Fetch the updated record to return it in the response
                        $updated_query = "SELECT * FROM blood_inventory WHERE id = ?";
                        if ($updated_stmt = $conn->prepare($updated_query)) {
                            $updated_stmt->bind_param("i", $id);
                            $updated_stmt->execute();
                            $updated_result = $updated_stmt->get_result();
                            $updated_record = $updated_result->fetch_assoc();
                            
                            // Return the updated record along with the success message
                            echo json_encode([
                                'success' => true,
                                'message' => 'Record updated successfully.',
                                'record' => $updated_record
                            ]);
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Failed to fetch updated record.'
                            ]);
                        }
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Failed to update record.'
                        ]);
                    }
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No record found.'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to fetch record.'
                ]);
            }
        } else {
            // If it's not a POST request for saving data, it's for fetching the record for viewing
            $query = "SELECT * FROM blood_inventory WHERE id = ?";
            if ($stmt = $conn->prepare($query)) {
                $stmt->bind_param("i", $id); // 'i' for integer
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $record = $result->fetch_assoc();
                    echo json_encode([
                        'success' => true,
                        'record' => $record
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'No record found.'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Database query failed.'
                ]);
            }
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'ID not provided.'
        ]);
    }
}
?>
