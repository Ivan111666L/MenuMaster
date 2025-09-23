<?php
// Check if session is not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/db.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create necessary tables for system analysis
        $queries = [
            "CREATE TABLE IF NOT EXISTS system_metrics (
                id INT AUTO_INCREMENT PRIMARY KEY,
                metric_name VARCHAR(100) NOT NULL,
                metric_value FLOAT NOT NULL,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS performance_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                operation_type VARCHAR(50) NOT NULL,
                execution_time FLOAT NOT NULL,
                memory_usage INT NOT NULL,
                user_id INT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )",
            
            "CREATE TABLE IF NOT EXISTS error_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                error_type VARCHAR(50) NOT NULL,
                error_message TEXT NOT NULL,
                stack_trace TEXT,
                user_id INT,
                timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )"
        ];

        // Execute each query
        foreach ($queries as $query) {
            $db->query($query);
        }

        // Create initial configuration
        $initial_config = [
            'performance_monitoring' => true,
            'error_logging' => true,
            'metric_collection_interval' => 300 // 5 minutes
        ];

        // Save configuration to database or config file
        file_put_contents('config/analysis_config.json', json_encode($initial_config));

        $_SESSION['success_message'] = "System analysis components installed successfully!";
        header('Location: admin_dashboard.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Installation failed: " . $e->getMessage();
        header('Location: admin_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install System Analysis Components - MenuMaster</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h1>Install System Analysis Components</h1>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="installation-form">
            <p>This will install the following components:</p>
            <ul>
                <li>System metrics tracking</li>
                <li>Performance monitoring</li>
                <li>Error logging system</li>
            </ul>

            <form method="POST" action="">
                <div class="form-group">
                    <input type="checkbox" id="confirm" name="confirm" required>
                    <label for="confirm">I understand this will modify the database structure</label>
                </div>

                <button type="submit" class="btn btn-primary">Install Components</button>
                <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>

    <script src="js/scripts.js"></script>
</body>
</html>
