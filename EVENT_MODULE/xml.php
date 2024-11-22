<?php
require("db.php");

// Check if the connection was successful
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}

function parseToXML($htmlStr) {
    $xmlStr = str_replace('<', '&lt;', $htmlStr);
    $xmlStr = str_replace('>', '&gt;', $xmlStr);
    $xmlStr = str_replace('"', '&quot;', $xmlStr);
    $xmlStr = str_replace("'", '&#39;', $xmlStr);
    $xmlStr = str_replace("&", '&amp;', $xmlStr);
    return $xmlStr;
}

// Select all the rows in the markers table
$query = "SELECT * FROM markers";
$result = mysqli_query($conn, $query);

if (!$result) {
    die('Invalid query: ' . mysqli_error($conn));
}

header("Content-type: text/xml");

// Start XML file, echo parent node
echo "<?xml version='1.0' encoding='UTF-8'?>";
echo '<markers>';

// Iterate through the rows, printing XML nodes for each
while ($row = mysqli_fetch_assoc($result)) {
    // Add to XML document node
    error_log("Image Path: " . $row['image_path']);
    echo '<marker ';
    echo 'id="' . $row['id'] . '" ';
    echo 'event_id="' . $row['event_id'] . '" ';
    echo 'name="' . parseToXML($row['name']) . '" ';
    echo 'address="' . parseToXML($row['address']) . '" ';
    echo 'lat="' . $row['lat'] . '" ';
    echo 'lng="' . $row['lng'] . '" ';
    echo 'status="' . $row['status'] . '" ';
    echo 'datetime="' . $row['datetime'] . '" ';
    echo 'image_path="../EVENT_MODULE/' . parseToXML($row['image_path']) . '" ';
    echo '/>';
}

// End XML file
echo '</markers>';

// Close the database connection
mysqli_close($conn);