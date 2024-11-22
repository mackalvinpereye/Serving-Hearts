<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input for forecast steps, ensuring it's an integer
    $forecast_steps = intval($_POST['forecast_steps']);

    // Define paths for Python and the forecast script
    $python_path = "C:\\Users\\Acer\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
    $script_path = realpath("C:\\XAMPP\\htdocs\\Serving Hearts\\Predictions\\o_blood_forecast_script.py"); // Absolute path to forecast script

    // Validate paths to Python and script
    if (!$script_path) {
        echo "<p>Error: Python script not found at the specified path.</p>";
        exit;
    }
    if (!file_exists($python_path)) {
        echo "<p>Error: Python executable not found at the specified path.</p>";
        exit;
    }

    // Prepare the command
    $command = "\"$python_path\" \"$script_path\" " . escapeshellarg($forecast_steps);

    // Use proc_open to execute the command with a timeout
    $descriptorspec = [
        0 => ["pipe", "r"], // stdin
        1 => ["pipe", "w"], // stdout
        2 => ["pipe", "w"], // stderr
    ];

    $process = proc_open($command, $descriptorspec, $pipes);

    if (is_resource($process)) {
        $start_time = time();
        $timeout = 10; // Timeout in seconds
        $is_timed_out = false;

        while (true) {
            $status = proc_get_status($process);

            if (!$status['running']) {
                // Process finished
                break;
            }

            if (time() - $start_time >= $timeout) {
                // Timeout reached, terminate the process
                proc_terminate($process);
                $is_timed_out = true;
                break;
            }

            usleep(100000); // Sleep for 0.1 seconds to avoid busy waiting
        }

        // Close pipes and process
        foreach ($pipes as $pipe) {
            fclose($pipe);
        }
        proc_close($process);

        if ($is_timed_out) {
            echo "<p>Error: Process timed out after $timeout seconds.</p>";
        } else {
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
        // Automatically refresh the page after 10 seconds
        setTimeout(function() {
            location.reload();
        }, 10000); // 10 seconds
    </script>
</head>
<body>
    <form method="post">
        <label for="forecast_steps">Enter Forecast Steps:</label>
        <input type="number" id="forecast_steps" name="forecast_steps" min="1" max="24" required>
        <button type="submit">Generate Forecast</button>
    </form>

    <?php if (file_exists("graphs/o_blood_handover_forecast.png")): ?>
        <h2>Blood Handover Forecast</h2>
        <!-- Add a timestamp to prevent image caching -->
        <img src="graphs/o_blood_handover_forecast.png?timestamp=<?php echo time(); ?>" alt="Blood Handover Forecast Graph">
    <?php else: ?>
        <p>No forecast available yet.</p>
    <?php endif; ?>
</body>
</html>
