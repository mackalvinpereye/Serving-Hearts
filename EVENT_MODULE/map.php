<?php
// Start the session
session_start();
include '../UI/asidebar.php';
require('db.php');

// Check if the session is admin or not
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Map and Add Location</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            height: 100vh;
            overflow: hidden;
        }
        #map {
            height: 100%;
            width: 100%;
        }
        .button-container {
            display: flex;
            justify-content: flex-start; /* Align items side by side */
            position: relative;
            left: 310px;
            top: -65px;
            gap: 10px; /* Optional: Add space between buttons */
        }
        #addEventButton, #editEventButton {
            font-size: 16px;
            width: 39%;
            padding: 15px;
            background-color: #b80012;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 8px;
        }
        #addEventButton:hover, #editEventButton:hover {
            background-color: #7d000c;
        }
        .modal {
            display: none; /* Keep modal hidden initially */
            flex-direction: row;
            align-items: flex-start;
            justify-content: flex-start;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Allow scrolling */
            background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
            z-index: 1000; /* Ensure this is higher than the sidebar's z-index */
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border: 1px solid #888;
            border-radius: 5px;
            width: 70%;
            max-width: 600px; /* Maximum width */
            height: 80%; /* Fixed height for modal */
            overflow-y: auto; /* Enable scrolling if needed */
            text-align: left; /* Align text to the left */
        }

        .close {
            color: #aaa; /* Gray color */
            float: right; /* Align to the right */
            font-size: 28px; /* Font size */
            font-weight: bold; /* Bold text */
        }

        .close:hover,
        .close:focus {
            color: black; /* Darker color on hover */
            text-decoration: none; /* No underline */
            cursor: pointer; /* Pointer cursor */
        }

        h1 {
            font-size: 24px; /* Font size for the header */
            margin-bottom: 20px; /* Spacing below the header */
        }

        label {
            display: block; /* Labels on new lines */
            margin: 10px 0 5px; /* Margins for spacing */
        }

        input[type="text"],
        input[type="datetime-local"],
        select {
            width: 100%; /* Full width */
            padding: 10px; /* Padding for comfort */
            border: 1px solid #ccc; /* Light gray border */
            border-radius: 4px; /* Slightly rounded corners */
            margin-bottom: 15px; /* Space below each input */
            box-sizing: border-box; /* Include padding and border in width calculation */
            height: 40px; /* Set a fixed height if needed */
        }

        label {
            display: block; /* Labels on new lines */
            margin: 10px 0 5px; /* Margins for spacing */
        }

        .custom-file-upload {
            box-sizing: border-box; /* Include padding and border in width calculation */
            margin-bottom: 15px; /* Space below each input */
            height: 40px; /* Set a fixed height if needed */
            width: 100%; /* Full width */
            cursor: pointer;
            padding: 10px; /* Padding for comfort */
            border: 1px solid #ccc;
            border-radius: 4px; /* Rounded corners */
            background-color: #f8f8f8; /* Light background */
            transition: background-color 0.3s;
        }

        .custom-file-upload:hover {
            background-color: #e0e0e0; /* Slightly darker on hover */
        }

        .custom-file-upload i {
            vertical-align: middle; /* Align icon with text */
        }

        input[type="file"] {
            display: none; /* Hide the default file input */
        }

        button[type="submit"] {
            margin-top: 10px;
            background-color: darkred; /* Green background */
            color: white; /* White text */
            padding: 10px 15px; /* Padding for comfort */
            border: none; /* No border */
            border-radius: 4px; /* Slightly rounded corners */
            cursor: pointer; /* Pointer cursor */
            width: 100%; /* Make it full width */
        }

        button[type="submit"]:hover {
            background-color: #570000; /* Darker green on hover */
        }
    </style>
</head>
<body>
    <div id="map"></div>

    <!-- Buttons for Add and Edit -->
    <div class="button-container">
        <button id="addEventButton">Add Event</button>
        <button id="editEventButton">Edit Event</button>
    </div>

    <!-- Modal for Adding Event -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h1>Add New Event</h1>
            <form id="addEventForm" enctype="multipart/form-data">
                <label for="event_id">Event ID:</label>
                <input type="text" id="event_id" name="event_id" required>

                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required>

                <label for="lat">Latitude:</label>
                <input type="text" id="lat" name="lat" required>

                <label for="lng">Longitude:</label>
                <input type="text" id="lng" name="lng" required>

                <label for="datetime">Date and Time:</label>
                <input type="datetime-local" id="datetime" name="datetime">

                <label for="status">Status:</label>
                <select id="status" name="status">
                    <option value="active">Active</option>
                    <option value="ended">Ended</option>
                </select>

                <label for="image">Image Preview:</label>
                <label for="image" class="custom-file-upload">
                    <i class="fas fa-images" style="margin-right: 5px;"></i>
                    Choose Image
                </label>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                <img id="imagePreview" src="" alt="Image Preview" style="display: none; max-width: 100%; margin-top: 10px;">

                <button type="submit">Add Event</button>
                    
                </div>
            </form>
        </div>
    </div>

    <!-- Modal for Editing Event -->
    <div id="editEventModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Event</h2>
            <form id="editEventForm" method="POST" action="submit_event_edit.php" enctype="multipart/form-data">
                <label for="edit_event_id">Event ID:</label>
                <select id="edit_event_id" name="event_id">
                    <!-- Options will be populated dynamically -->
                </select>
                <label for="edit_name">Name:</label>
                <input type="text" id="edit_name" name="name" required>
                <label for="edit_address">Address:</label>
                <input type="text" id="edit_address" name="address" required>
                <label for="edit_lat">Latitude:</label>
                <input type="text" id="edit_lat" name="lat" required>
                <label for="edit_lng">Longitude:</label>
                <input type="text" id="edit_lng" name="lng" required>
                <label for="edit_datetime">Date & Time:</label>
                <input type="datetime-local" id="edit_datetime" name="datetime" required>
                <label for="edit_status">Status:</label>
                <select id="edit_status" name="status">
                    <option value="active">Active</option>
                    <option value="ended">Ended</option>
                </select>

                <label for="edit_image">Image Preview:</label>
                <label for="edit_image" class="custom-file-upload">
                    <i class="fas fa-images" style="margin-right: 5px;"></i>
                    Choose Image
                </label>
                <input type="file" id="edit_image" name="image" accept="image/*" onchange="previewEditImage(event)">
                <input type="hidden" id="existing_image_path" name="existing_image_path">
                <img id="edit_imagePreview" src="" style="display: none; max-width: 100%; margin-top: 10px;" alt="Edit Image Preview">

                <button type="submit">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        // Modal handling for Add Event
        var addEventModal = document.getElementById("myModal");
        var addEventBtn = document.getElementById("addEventButton");
        var closeModal = document.getElementsByClassName("close");

        addEventBtn.onclick = function() {
            addEventModal.style.display = "flex";
        }

        // Close modals
        for (let i = 0; i < closeModal.length; i++) {
            closeModal[i].onclick = function() {
                addEventModal.style.display = "none";
                editEventModal.style.display = "none";
            }
        }

        // Modal handling for Edit Event
        var editEventModal = document.getElementById("editEventModal");
        var editEventBtn = document.getElementById("editEventButton");

        // Call the function to load event IDs when the page is loaded
        document.addEventListener('DOMContentLoaded', function() {
            loadEventIds();
        });

        // Fetch Event IDs and populate the select element
        function loadEventIds() {
            fetch('get_event.php') // Replace with your PHP script to fetch event IDs
                .then(response => response.json())
                .then(data => {
                    const select = document.getElementById('edit_event_id');
                    data.forEach(event => {
                        const option = document.createElement('option');
                        option.value = event.event_id; // Assuming 'event_id' is the field name
                        option.textContent = event.event_id; // Display the event_id or any other description
                        select.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching event IDs:', error));
        }

        // Event listener for when an Event ID is selected
        document.getElementById('edit_event_id').addEventListener('change', function() {
            const eventId = this.value;
            if (eventId) {
                fetch(`get_event.php?event_id=${eventId}`) // Fetch details based on the selected event_id
                    .then(response => response.json())
                    .then(data => {
                        // Assuming data returns an object with event details
                        document.getElementById('edit_name').value = data.name;
                        document.getElementById('edit_address').value = data.address;
                        document.getElementById('edit_lat').value = data.lat;
                        document.getElementById('edit_lng').value = data.lng;
                        document.getElementById('edit_datetime').value = data.datetime;
                        document.getElementById('edit_status').value = data.status;
                        document.getElementById('existing_image_path').value = data.image_path;

                        // Update the image preview if available
                        // Set the image preview if it exists
                        const imagePreview = document.getElementById('edit_imagePreview');
                        imagePreview.src = data.image_path ? data.image_path : '';
                        imagePreview.style.display = data.image_path ? 'block' : 'none';
                    })
                    .catch(error => console.error('Error fetching event details:', error));
            }
        });

        document.getElementById("editEventForm").addEventListener("submit", function(event) {
            console.log("Selected status: " + document.getElementById('edit_status').value);

        // Collect form data
        var formData = new FormData(this);
        formData.append('event_id', document.getElementById('edit_event_id').value); // Add the selected event ID

        // Send form data to the server via AJAX
        fetch('submit_event_edit.php', { // Update to your PHP script that handles the update
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            console.log(data); // Handle the response from the server
            editEventModal.style.display = "none"; // Close the edit modal
            loadMarkers(); // Reload markers to reflect changes
        })
        .catch(error => console.error('Error:', error));
    });

    // Function to clear the form after submission
    function clearForm() {
        // Reset the form fields
        document.getElementById('editEventForm').reset();

        // Clear the image preview
        const imagePreview = document.getElementById('edit_imagePreview');
        imagePreview.src = '';
        imagePreview.style.display = 'none';

        // Optionally close the modal after submission
        closeModal(); // Uncomment this if you want to close the modal after submission

        return true; // Allow the form to submit
    }
    </script>

    <script>
        function previewImage(event) {
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.src = URL.createObjectURL(event.target.files[0]);
            imagePreview.style.display = 'block'; // Show the image preview
        }

        function resetForm() {
            document.getElementById("editEventForm").reset();
            document.getElementById('imagePreview').style.display = 'none'; // Hide preview
        }
    </script>

    <script>
        var map;
        var markers = [];

        function initMap() {
            map = new google.maps.Map(document.getElementById('map'), {
                center: new google.maps.LatLng(14.5850, 121.0680), // Pasig, Philippines
                zoom: 13
            });

            loadMarkers();
        }

        function loadMarkers() {
            var infoWindow = new google.maps.InfoWindow();

            // Fetch XML data
            downloadUrl('../EVENT_MODULE/xml.php', function(data, status) {
                if (status === 200) {
                    var xml = data.responseXML;
                    var markersData = xml.documentElement.getElementsByTagName('marker');
                    Array.prototype.forEach.call(markersData, function(markerElem) {
                        var id = markerElem.getAttribute('id');
                        var name = markerElem.getAttribute('name');
                        var event_id = markerElem.getAttribute('event_id');
                        var address = markerElem.getAttribute('address');
                        var datetime = markerElem.getAttribute('datetime');
                        var imagePath = markerElem.getAttribute('image_path');
                        var status = markerElem.getAttribute('status');
                        var point = new google.maps.LatLng(
                            parseFloat(markerElem.getAttribute('lat')),
                            parseFloat(markerElem.getAttribute('lng'))
                        );

                        var iconUrl = (status === 'ended') ? 
                            '../EVENT_MODULE/markers/grey_marker.png' : 
                            '../EVENT_MODULE/markers/red_marker.png';

                        var marker = new google.maps.Marker({
                            map: map,
                            position: point,
                            icon: {
                                url: iconUrl,
                                scaledSize: new google.maps.Size(50, 50),
                                origin: new google.maps.Point(0, 0),
                                anchor: new google.maps.Point(15, 30)
                            }
                        });

                        var infowincontent = createInfoWindowContent(name, event_id, address, datetime, imagePath, status);
                        marker.addListener('click', function() {
                            infoWindow.setContent(infowincontent);
                            infoWindow.open(map, marker);
                        });

                        markers.push(marker);
                    });
                } else {
                    console.error('Error loading XML data');
                }
            });
        }

        function createInfoWindowContent(name, event_id, address, datetime, imagePath, status) {
            var infowincontent = document.createElement('div');
            infowincontent.style.cssText = 'font-family: Arial, sans-serif; font-size: 16px; padding: 10px; width: 300px; height: 200px; overflow: auto;';

            if (imagePath) {
                var img = document.createElement('img');
                img.src = imagePath;
                img.style.width = '280px';
                img.style.height = 'auto';
                infowincontent.appendChild(img);
            }

            infowincontent.innerHTML += `<strong>Event ID:</strong> ${event_id}<br>
                                          <strong>Event Name:</strong> ${name}<br>
                                          <strong>Status:</strong> <span style="color:${status === 'active' ? 'green' : 'red'};">${status}</span><br>
                                          <strong>Address:</strong> ${address}<br>
                                          <strong>Date and Time:</strong> ${datetime}<br>`;

            return infowincontent;
        }

        function downloadUrl(url, callback) {
            var request = new XMLHttpRequest();
            request.onreadystatechange = function() {
                if (request.readyState === 4) {
                    if (request.status === 200) {
                        callback(request, request.status);
                    } else {
                        console.error('HTTP error: ' + request.status);
                    }
                }
            };
            request.open('GET', url, true);
            request.send(null);
        }

        // Modal functionality
        var modal = document.getElementById("myModal");
        var btn = document.getElementById("addEventButton");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "flex"; // Use flexbox for modal display
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Handle form submission
        document.getElementById("addEventForm").addEventListener("submit", function(event) {
            event.preventDefault(); // Prevent default form submission

            // Collect form data
            var formData = new FormData(this);

            // Send form data to the server via AJAX
            fetch('submit_location.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log(data); // Handle the response from the server
                modal.style.display = "none"; // Close the modal after submission
                loadMarkers(); // Reload markers to show the newly added event

                // Clear the form inputs
                document.getElementById("addEventForm").reset();
                
                // Reset the image preview
                document.getElementById('imagePreview').src = '';
                document.getElementById('imagePreview').style.display = 'none';
            })
            .catch(error => console.error('Error:', error));
        });

        window.addEventListener('load', initMap);

        // Handle Edit Event Button
        document.getElementById('editEventButton').addEventListener('click', function() {
            editEventModal.style.display = "flex"; // Show the edit modal
        });
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=YOUR=API=HERE&callback=initMap"></script>

    <script>
        function previewImage(event) {
            const imagePreview = document.getElementById('imagePreview');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block'; // Show the preview
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '';
                imagePreview.style.display = 'none'; // Hide the preview
            }
        }

        function previewEditImage(event) {
            console.log("File selected:", event.target.files);
            const imagePreview = document.getElementById('edit_imagePreview');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    console.log("Image loaded:", e.target.result);
                    imagePreview.src = e.target.result;
                    imagePreview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                imagePreview.src = '';
                imagePreview.style.display = 'none';
            }
        }
    </script>

<script>
    function openEditEventModal(eventId) {
        // Fetch the event details via AJAX
        fetch(`get_event.php?event_id=${eventId}`)
            .then(response => response.json())
            .then(data => {
                // Fill form inputs with event data
                document.getElementById('edit_event_id').value = data.event_id;
                document.getElementById('edit_name').value = data.name;
                document.getElementById('edit_address').value = data.address;
                document.getElementById('edit_lat').value = data.lat;
                document.getElementById('edit_lng').value = data.lng;
                document.getElementById('edit_datetime').value = data.datetime;
                document.getElementById('edit_status').value = data.status;

                // Set the existing image path in the hidden field
                document.getElementById('existing_image_path').value = data.image_path;

                // Update image preview if the image path exists
                if (data.image_path) {
                    document.getElementById('edit_imagePreview').src = data.image_path;
                    document.getElementById('edit_imagePreview').style.display = 'block';
                } else {
                    document.getElementById('edit_imagePreview').style.display = 'none';
                }

                // Open the modal
                document.getElementById('editEventModal').style.display = 'block';
            })
            .catch(error => {
                console.error('Error fetching event data:', error);
            });
    }
</script>
</body>
</html>

