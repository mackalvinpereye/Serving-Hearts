<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input for forecast steps, ensuring it's an integer
    $forecast_steps = intval($_POST['forecast_steps']);

    // Define paths for Python and the forecast script
    $python_path = "C:\\Users\\Acer\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
    $script_path = realpath("C:\\XAMPP\\htdocs\\Serving Hearts\\Predictions\\forecast_script.py"); // Absolute path to forecast script

    // Validate paths to Python and script
    if (!$script_path) {
        echo "<p>Error: Python script not found at the specified path.</p>";
        exit;
    }
    if (!file_exists($python_path)) {
        echo "<p>Error: Python executable not found at the specified path.</p>";
        exit;
    }

    // Prepare and run the Python command with forecast steps
    $command = escapeshellcmd("\"$python_path\" \"$script_path\" " . escapeshellarg($forecast_steps));
    exec($command . " 2>&1", $output, $status);

    // Display the result with enhanced error handling
    if ($status === 0) {
        echo "<p>Forecast generated successfully!</p>";
    } else {
        echo "<p>Error generating forecast. Status Code: $status</p>";
        echo "<pre>Debug Output:\n" . htmlspecialchars(implode("\n", $output)) . "</pre>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Handover Forecast</title>
</head>
<body>
    <form method="post">
        <label for="forecast_steps">Enter Forecast Steps:</label>
        <input type="number" id="forecast_steps" name="forecast_steps" min="1" max="24" required>
        <button type="submit">Generate Forecast</button>
    </form>

    <?php if (file_exists("graphs/blood_handover_forecast.png")): ?>
        <h2>Blood Handover Forecast</h2>
        <!-- Add a timestamp to prevent image caching -->
        <img src="graphs/blood_handover_forecast.png?timestamp=<?php echo time(); ?>" alt="Blood Handover Forecast Graph">
    <?php else: ?>
        <p>No forecast available yet.</p>
    <?php endif; ?>
</body>
</html>
