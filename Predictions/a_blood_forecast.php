<?php
$forecast_created = false; // Initialize the forecast status flag

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $forecast_steps = intval($_POST['forecast_steps']);
    $python_path = "C:\\Users\\Acer\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
    $script_path = realpath("C:\\XAMPP\\htdocs\\Serving Hearts\\Predictions\\a_blood_forecast_script.py");

    if (!$script_path) {
        echo "<p>Error: Python script not found at the specified path.</p>";
        exit;
    }
    if (!file_exists($python_path)) {
        echo "<p>Error: Python executable not found at the specified path.</p>";
        exit;
    }

    $command = "\"$python_path\" \"$script_path\" " . escapeshellarg($forecast_steps);
    $descriptorspec = [
        0 => ["pipe", "r"], 
        1 => ["pipe", "w"], 
        2 => ["pipe", "w"], 
    ];

    $process = proc_open($command, $descriptorspec, $pipes);

    if (is_resource($process)) {
        $start_time = time();
        $timeout = 10;
        $is_timed_out = false;

        while (true) {
            $status = proc_get_status($process);
            if (!$status['running']) break;

            if (time() - $start_time >= $timeout) {
                proc_terminate($process);
                $is_timed_out = true;
                break;
            }
            usleep(100000);
        }

        foreach ($pipes as $pipe) fclose($pipe);
        proc_close($process);

        if ($is_timed_out) {
            echo "<p>Error: Process timed out after $timeout seconds.</p>";
        } else {
            $forecast_created = true; // Set the flag to true if forecast is successfully created
            echo "<p>Forecast generated successfully!</p>";
        }
    } else {
        echo "<p>Error: Unable to start the process.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Blood Handover Forecast</title>
    <script>
        // PHP passes the `forecastCreated` flag to JavaScript
        const forecastCreated = <?php echo json_encode($forecast_created); ?>;

        if (forecastCreated) {
            // Refresh the page after 20 seconds if a new forecast was created
            setTimeout(function() {
                location.reload();
            }, 20000); // 20 seconds
        }
    </script>
</head>
<body>
    <form method="post">
        <label for="forecast_steps">Enter Forecast Steps:</label>
        <input type="number" id="forecast_steps" name="forecast_steps" min="1" max="24" required>
        <button type="submit">Generate Forecast</button>
    </form>

    <?php if (file_exists("graphs/a_blood_handover_forecast.png")): ?>
        <h2>Blood Handover Forecast</h2>
        <img src="graphs/a_blood_handover_forecast.png?timestamp=<?php echo time(); ?>" alt="Blood Handover Forecast Graph">
    <?php else: ?>
        <p>No forecast available yet.</p>
    <?php endif; ?>
</body>
</html>
