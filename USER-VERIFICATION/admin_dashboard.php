<?php 
session_start();
// Check if the user is logged in and is an admin
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'admin') {
    header('Location: ../USER-VERIFICATION/index.php');
    exit();
}

include '../UI/asidebar.php';
require_once('../USER-VERIFICATION/config/db.php');

// Initialize the SQL queries
$sql_total_users = "SELECT COUNT(*) as total_users FROM users";
$sql_new_users = "SELECT COUNT(*) as new_users FROM users WHERE created_at >= NOW() - INTERVAL 30 DAY";
$sql_today_requests = "SELECT COUNT(*) as today_requests FROM request WHERE DATE(request_date) = CURDATE()";
$sql_today_approved_requests = "SELECT COUNT(*) as approved_requests FROM handover_requests WHERE DATE(created_at) = CURDATE()";

// Execute the queries
$total_users = $conn->query($sql_total_users)->fetch_assoc()['total_users'] ?? 0;
$new_users = $conn->query($sql_new_users)->fetch_assoc()['new_users'] ?? 0;
$today_requests = $conn->query($sql_today_requests)->fetch_assoc()['today_requests'] ?? 0;
$approved_requests = $conn->query($sql_today_approved_requests)->fetch_assoc()['approved_requests'] ?? 0;

// Fetch blood counts for each type (positive and negative)
$blood_counts = [];
foreach (['A', 'B', 'AB', 'O'] as $type) {
    $sql_positive = "SELECT COUNT(*) as count FROM blood_inventory WHERE blood_type = '{$type}+'";
    $sql_negative = "SELECT COUNT(*) as count FROM blood_inventory WHERE blood_type = '{$type}-'";

    $blood_counts[$type] = [
        'positive' => $conn->query($sql_positive)->fetch_assoc()['count'] ?? 0,
        'negative' => $conn->query($sql_negative)->fetch_assoc()['count'] ?? 0
    ];
}

// Close the database connection
$conn->close();

// Handle the forecast generation request
$message = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['forecast_steps'])) {
    $forecast_steps = intval($_POST['forecast_steps']);

    $python_path = "C:\\Users\\Acer\\AppData\\Local\\Programs\\Python\\Python313\\python.exe";
    $script_path = realpath("C:\\XAMPP\\htdocs\\Serving Hearts\\Predictions\\blood_forecast_script.py");

    if (!$script_path) {
        $message = "<div class='alert alert-danger'>Error: Python script not found at the specified path.</div>";
    } elseif (!file_exists($python_path)) {
        $message = "<div class='alert alert-danger'>Error: Python executable not found at the specified path.</div>";
    } else {
        $command = escapeshellcmd("\"$python_path\" \"$script_path\" " . escapeshellarg($forecast_steps));
        exec($command . " 2>&1", $output, $status);

        if ($status === 0) {
            $message = "<div class='alert alert-success'>✓ Forecast generated successfully for {$forecast_steps} month(s)!</div>";
        } else {
            $message = "<div class='alert alert-danger'>✗ Error generating forecast. Please try again.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <link rel="icon" type="image/png" href="../WEB/images/shlogo.png">
    <title>Admin Dashboard | Blood Bank Management</title>
    
    <!-- Google Fonts + Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f8;
            margin: 0;
            padding: 24px 32px;
            min-height: 100vh;
            color: #1e293b;
        }

        /* modern scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #e2e8f0;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }

        /* Dashboard layout */
        .dashboard-wrapper {
            max-width: 1440px;
            margin: 0 auto;
        }

        /* header area */
        .dashboard-header {
            margin-bottom: 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }
        .dashboard-header h1 {
            font-size: 1.9rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e2a3e, #2c3e50);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.3px;
        }
        .admin-badge {
            background: white;
            padding: 8px 18px;
            border-radius: 60px;
            font-weight: 500;
            font-size: 0.9rem;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-badge i {
            color: #dc2626;
            font-size: 1rem;
        }
        .role-chip {
            background: #fee2e2;
            border-radius: 40px;
            padding: 4px 12px;
            font-size: 0.7rem;
            font-weight: 700;
            color: #b91c1c;
        }

        /* main grid: 2 columns on large screens */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 28px;
            margin-bottom: 40px;
            margin-left: 220px;
        }

        /* left side (stats boxes + extra) */
        .stats-section {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 24px;
        }
        .stat-card {
            background: white;
            border-radius: 28px;
            padding: 24px 20px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.02);
            transition: all 0.25s ease;
            border: 1px solid rgba(226, 232, 240, 0.6);
            backdrop-filter: blur(2px);
            display: flex;
            flex-direction: column;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 30px -12px rgba(0, 0, 0, 0.1);
            border-color: #cbd5e1;
        }
        .stat-label {
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            color: #5b6e8c;
            margin-bottom: 12px;
        }
        .stat-value {
            font-size: 2.7rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            margin-bottom: 8px;
        }
        .stat-sub {
            font-size: 0.75rem;
            color: #6c7a91;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .stat-icon {
            font-size: 2.2rem;
            color: #b91c1c;
            opacity: 0.7;
            margin-bottom: 12px;
        }

        /* right sidebar: blood inventory panel */
        .inventory-panel {
            background: white;
            border-radius: 32px;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            border: 1px solid #eef2ff;
            transition: all 0.2s;
            height: fit-content;
        }
        .panel-header {
            background: #fef9f9;
            padding: 20px 24px 12px 24px;
            border-bottom: 1px solid #fee2e2;
        }
        .panel-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #7f1a1a;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .blood-inventory-area {
            padding: 24px 24px 16px;
            background: #ffffff;
        }
        
        /* Blood type grid inside panel */
        .blood-type-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }
        .blood-type-item {
            background: #f8fafc;
            border-radius: 20px;
            padding: 16px;
            text-align: center;
            transition: all 0.2s;
            border: 1px solid #eef2ff;
        }
        .blood-type-item:hover {
            transform: scale(1.02);
            background: #fff5f5;
            border-color: #fecaca;
        }
        .blood-type-letter {
            font-size: 1.5rem;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .blood-type-letter.A { color: #dc2626; }
        .blood-type-letter.B { color: #dc2626; }
        .blood-type-letter.AB { color: #dc2626; }
        .blood-type-letter.O { color: #dc2626; }
        .blood-count-pair {
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .positive-count {
            color: #dc2626;
        }
        .negative-count {
            color: #3b82f6;
        }
        .blood-icon-small {
            font-size: 0.8rem;
            margin-right: 4px;
        }

        /* Forecast Module */
        .forecast-module {
            padding: 20px 24px 24px;
            border-top: 1px solid #f0eef2;
            background: #fefcfb;
        }
        .forecast-title {
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #2d3a4b;
        }
        .forecast-form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .input-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .input-group label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #5b6e8c;
        }
        .input-group input {
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 20px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .input-group input:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }
        .btn-generate {
            background: #991b1b;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 40px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-top: 8px;
        }
        .btn-generate:hover {
            background: #7f1a1a;
            transform: scale(1.02);
            box-shadow: 0 6px 12px -8px #991b1b70;
        }
        
        .graph-container {
            margin-top: 20px;
            background: white;
            border-radius: 24px;
            padding: 20px;
            text-align: center;
        }
        .graph-container img {
            max-width: 100%;
            border-radius: 16px;
            margin-top: 12px;
        }
        .graph-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #5b6e8c;
            margin-bottom: 12px;
        }

        /* Alert Messages */
        .alert {
            padding: 12px 16px;
            border-radius: 16px;
            margin-top: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .alert-success {
            background: #dcfce7;
            color: #166534;
            border-left: 4px solid #22c55e;
        }
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }

        /* responsive adjustments */
        @media (max-width: 950px) {
            body {
                padding: 20px;
            }
            .main-grid {
                grid-template-columns: 1fr;
                gap: 28px;
            }
            .stats-row {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 640px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
            .dashboard-header h1 {
                font-size: 1.5rem;
            }
            .blood-type-grid {
                grid-template-columns: 1fr;
            }
        }

        .footer-note {
            text-align: center;
            font-size: 0.7rem;
            color: #8e9aaf;
            margin-top: 40px;
            border-top: 1px solid #e2e8f0;
            padding-top: 24px;
        }
        hr {
            margin: 16px 0;
            border-color: #f1e5e5;
        }
        .inventory-stats {
            font-size: 0.7rem;
            color: #6c7a91;
            text-align: center;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #f0eef2;
        }
    </style>
</head>
<body>
<div class="dashboard-wrapper">
    <!-- Header with greeting and badge -->
    <div class="dashboard-header">
        <h1><i class="fas fa-tint" style="color: #dc2626; margin-right: 8px;"></i> Admin Dashboard</h1>
        <div class="admin-badge">
            <i class="fas fa-shield-alt"></i>
            <span>Administrator</span>
            <span class="role-chip"><i class="fas fa-crown"></i> Admin Access</span>
        </div>
    </div>

    <!-- Main content grid -->
    <div class="main-grid">
        <!-- LEFT COLUMN: analytics boxes -->
        <div class="stats-section">
            <div class="stats-row">
                <!-- total users box -->
                <div class="stat-card">
                    <i class="fas fa-users stat-icon"></i>
                    <div class="stat-label">Total Registered Users</div>
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                    <div class="stat-sub"><i class="fas fa-calendar-alt"></i> As of today</div>
                </div>
                <!-- new users (30 days) -->
                <div class="stat-card">
                    <i class="fas fa-user-plus stat-icon"></i>
                    <div class="stat-label">New Users (30d)</div>
                    <div class="stat-value"><?php echo number_format($new_users); ?></div>
                    <div class="stat-sub"><i class="fas fa-chart-line"></i> Last 30 days interval</div>
                </div>
                <!-- today's requests -->
                <div class="stat-card">
                    <i class="fas fa-file-alt stat-icon"></i>
                    <div class="stat-label">Today's Requests</div>
                    <div class="stat-value"><?php echo number_format($today_requests); ?></div>
                    <div class="stat-sub"><i class="fas fa-clock"></i> Pending approval</div>
                </div>
                <!-- today's handed over -->
                <div class="stat-card">
                    <i class="fas fa-check-circle stat-icon"></i>
                    <div class="stat-label">Handed Over Today</div>
                    <div class="stat-value"><?php echo number_format($approved_requests); ?></div>
                    <div class="stat-sub"><i class="fas fa-heartbeat"></i> Completed transactions</div>
                </div>
            </div>
            
            <!-- Graph Section -->
            <div class="graph-container">
                <div class="graph-label">
                    <i class="fas fa-chart-line"></i> Blood Request Forecast
                </div>
                <img src="../Predictions/graphs/blood_handover_forecast.png?timestamp=<?php echo time(); ?>" 
                     alt="Blood Handover Forecast Graph"
                     onerror="this.src='https://via.placeholder.com/800x400?text=Generate+Forecast+to+See+Graph'">
            </div>
        </div>

        <!-- RIGHT COLUMN: Blood Inventory + Forecast Panel -->
        <div class="inventory-panel">
            <div class="panel-header">
                <h3><i class="fas fa-database"></i> Blood Inventory Summary</h3>
            </div>
            <div class="blood-inventory-area">
                <div class="blood-type-grid">
                    <?php foreach ($blood_counts as $type => $counts): ?>
                    <div class="blood-type-item">
                        <div class="blood-type-letter <?php echo $type; ?>">
                            Type <?php echo $type; ?>
                        </div>
                        <div class="blood-count-pair">
                            <span class="positive-count">
                                <i class="fas fa-plus-circle blood-icon-small"></i> <?php echo $counts['positive']; ?>
                            </span>
                            <span class="negative-count">
                                <i class="fas fa-minus-circle blood-icon-small"></i> <?php echo $counts['negative']; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="inventory-stats">
                    <i class="fas fa-info-circle"></i> Total units in stock: 
                    <?php 
                        $total_units = 0;
                        foreach ($blood_counts as $counts) {
                            $total_units += $counts['positive'] + $counts['negative'];
                        }
                        echo $total_units;
                    ?> units
                </div>
            </div>
            
            <!-- Forecast Module -->
            <div class="forecast-module">
                <div class="forecast-title">
                    <i class="fas fa-chart-simple"></i> Generate Demand Forecast
                </div>
                <form method="post" class="forecast-form">
                    <div class="input-group">
                        <label for="forecast_steps">Forecast Period (Months)</label>
                        <input type="number" id="forecast_steps" name="forecast_steps" min="1" max="24" placeholder="Enter 1-24 months" required>
                    </div>
                    <button type="submit" class="btn-generate">
                        <i class="fas fa-chart-line"></i> Generate Forecast
                    </button>
                </form>
                
                <?php if ($message): ?>
                    <div id="forecastMessage">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <hr>
                <div style="font-size: 0.7rem; color: #6c7a91; text-align: center; margin-top: 12px;">
                    <i class="fas fa-chart-simple"></i> AI-powered demand prediction
                </div>
            </div>
        </div>
    </div>
    
    <div class="footer-note">
        <i class="fas fa-shield-alt"></i> Secure Admin Dashboard — Real-time blood bank analytics
    </div>
</div>

<script>
    // Auto-hide forecast message after 3 seconds
    window.onload = function() {
        var messageDiv = document.getElementById('forecastMessage');
        if (messageDiv) {
            setTimeout(function() {
                messageDiv.style.opacity = '0';
                setTimeout(function() {
                    if(messageDiv) messageDiv.style.display = 'none';
                }, 300);
            }, 3000);
        }
    };

    // Add loading effect on form submit
    document.querySelector('.btn-generate')?.addEventListener('click', function(e) {
        var btn = this;
        var originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;
        
        // Re-enable after 3 seconds (or form will submit naturally)
        setTimeout(function() {
            if(btn) {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }, 3000);
    });
</script>

</body>
</html>