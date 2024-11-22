<?php
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

include '../UI/asidebar.php'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Scanner</title>
    <script src="../SCANNER_MODULE/html5-qrcode.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .qr-scanner-wrapper {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
            padding: 15px;
            max-width: 500px;
            width: 90%;
            margin: 10px auto;
            text-align: center;
            margin-left: 38em;
            margin-top: 7rem;
        }

        .scanner-container {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        #reader {
            width: 100%;
            min-height: 200px;
            border-radius: 8px;
        }

        h2 {
            color: #007bff;
            font-size: 1.2em;
            margin-bottom: 8px;
        }

        p {
            font-size: 0.9em;
            color: #555;
            margin: 0 0 8px 0;
        }

        #scanned-result-display {
            font-weight: bold;
            color: #333;
            margin: 8px 0;
        }

        .message-container {
            margin-top: 15px;
        }

        .message {
            padding: 8px;
            border-radius: 4px;
            font-size: 0.85em;
            margin-top: 8px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
        }

        .button-container {
            margin-top: 15px;
        }

        button {
            padding: 8px 16px;
            font-size: 0.9em;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background: #007bff;
            color: #ffffff;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #0056b3;
        }

        button:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }

        @media (max-width: 600px) {
            .scanner-container, #reader {
                min-height: 200px;
            }

            h2 {
                font-size: 1.2em;
            }

            button {
                font-size: 0.9em;
            }
        }
        /* Style for the QR code scanner dashboard */
        #reader__dashboard_section_csr {
            display: block;
            text-align: center;
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }

        /* Style for the zoom input */
        #html5-qrcode-input-range-zoom {
            width: 60%;
            height: 8px;
            margin: 10px 0;
            border-radius: 5px;
            background: #007bff;
            outline: none;
            opacity: 0.8;
            -webkit-appearance: none;
            appearance: none;
        }

        #html5-qrcode-input-range-zoom:hover {
            opacity: 1;
        }

        /* Style for the zoom label */
        #reader__dashboard_section_csr span {
            font-size: 0.9em;
            color: #333;
            margin-bottom: 5px;
            display: inline-block;
        }

        /* Style for the camera select dropdown */
        #html5-qrcode-select-camera {
            font-size: 1em;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: #fff;
            color: #333;
        }

        #html5-qrcode-select-camera:focus {
            outline: none;
            border-color: #007bff;
        }

        /* Style for the buttons */
        #reader__dashboard_section_csr button {
            padding: 8px 16px;
            font-size: 1em;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease, color 0.3s ease;
            margin: 0 5px;
        }

        #html5-qrcode-button-camera-start {
            background: #28a745;
            color: #fff;
        }

        #html5-qrcode-button-camera-start:hover {
            background: #218838;
        }

        #html5-qrcode-button-camera-stop {
            background: #dc3545;
            color: #fff;
        }

        #html5-qrcode-button-camera-stop:hover {
            background: #c82333;
        }

        /* Add hover effect to dropdown options */
        #html5-qrcode-select-camera option:hover {
            text-decoration: none !important;
            background-color: #007bff;
            color: white;
        }
        /* Style for the "Scan an Image File" span element */
        #html5-qrcode-anchor-scan-type-change {
            text-decoration: none !important;
            cursor: pointer;
            display: inline-block;
            padding: 8px 12px;
            font-size: 1rem;
            font-weight: bold;
            color: #007bff;
            border: 1px solid #007bff;
            border-radius: 5px;
            background: #ffffff;
            transition: background 0.3s ease, color 0.3s ease, box-shadow 0.3s ease;
        }

        #html5-qrcode-anchor-scan-type-change:hover {
            text-decoration: none;
            color: #ffffff;
            background: #007bff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        #html5-qrcode-anchor-scan-type-change:active {
            background: #0056b3;
            box-shadow: inset 0 3px 6px rgba(0, 0, 0, 0.2);
        }

    </style>
</head>
<body>

<div class="qr-scanner-wrapper">
    <div class="scanner-container">
        <div id="reader"></div>
    </div>

    <div class="qr-scanner-container">
        <h2>Scanned Result:</h2>
        <p id="scanned-result-display">No QR code scanned yet.</p>

        <div id="message-container" class="message-container"></div>

        <form id="qr-result-form" method="POST" style="display:none;">
            <input type="hidden" name="scanned_text" id="scanned_text" />
        </form>

        <div class="button-container">
            <button id="restart-scan-button" style="display: none;">Scan Again</button>
        </div>
    </div>
</div>

<script>
    let html5QrcodeScanner;

    function onScanSuccess(decodedText, decodedResult) {
        // Replace newline characters with <br> for line-by-line display
        const formattedText = decodedText.replace(/\n/g, '<br>');
        
        // Display the formatted text in the result element
        document.getElementById("scanned-result-display").innerHTML = formattedText;

        // Set the raw decoded text value for submission
        document.getElementById("scanned_text").value = decodedText;

        // Play a beep sound
        const beep = new Audio('../WEB/images/beep.mp3');
        beep.play();

        // Submit form via AJAX
        submitQRCode();

        // Disable scanner and show "Scan Again" button
        stopScanning();
        document.getElementById("restart-scan-button").style.display = 'inline-block';
    }


    function onScanFailure(error) {
        console.warn(`QR error = ${error}`);
    }

    function startScanning() {
        document.getElementById("restart-scan-button").style.display = 'none';
        html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 });
        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    }

    function stopScanning() {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
            html5QrcodeScanner = null;
        }
    }

    function submitQRCode() {
        const formData = new FormData(document.getElementById("qr-result-form"));

        fetch('process.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            const messageContainer = document.getElementById("message-container");
            const messageType = data.status === 'success' ? 'success' : 'error';
            messageContainer.innerHTML = `<div class="message ${messageType}">${data.message}</div>`;
        })
        .catch(error => {
            const messageContainer = document.getElementById("message-container");
            messageContainer.innerHTML = `<div class="message error">Error: ${error.message}</div>`;
        });
    }

    document.getElementById("restart-scan-button").addEventListener("click", () => {
        document.getElementById("scanned-result-display").textContent = "No QR code scanned yet.";
        document.getElementById("message-container").innerHTML = "";
        startScanning();
    });

    startScanning();
</script>

</body>
</html>
