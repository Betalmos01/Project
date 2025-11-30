<?php
// Database configuration for XAMPP
// Make sure MySQL is running in XAMPP Control Panel before using this application
$host = "localhost";
$username = "root";
$password = ""; // XAMPP default is empty password
$database = "emergency_comm_db";
$port = 3307; // MySQL port

// Disable error reporting temporarily to catch connection errors manually
mysqli_report(MYSQLI_REPORT_OFF);

// Attempt to connect to MySQL with the database
$conn = @mysqli_connect($host, $username, $password, $database, $port);

if (!$conn) {
    $error_code = mysqli_connect_errno();
    $error_message = mysqli_connect_error();
    
    // Provide helpful XAMPP-specific error messages
    if ($error_code == 2002 || strpos($error_message, "actively refused") !== false || strpos($error_message, "Connection refused") !== false) {
        die("
        <h2>MySQL Service Not Running</h2>
        <p><strong>Error:</strong> Cannot connect to MySQL database.</p>
        <p><strong>Connection Details:</strong></p>
        <ul>
            <li>Host: {$host}</li>
            <li>Port: {$port}</li>
            <li>Username: {$username}</li>
            <li>Database: {$database}</li>
        </ul>
        <p><strong>Error Code:</strong> {$error_code}</p>
        <p><strong>Error Message:</strong> {$error_message}</p>
        <p><strong>Solution for XAMPP:</strong></p>
        <ol>
            <li>Open <strong>XAMPP Control Panel</strong></li>
            <li>Click the <strong>Start</strong> button next to <strong>MySQL</strong></li>
            <li>Wait until MySQL shows as 'Running' (green)</li>
            <li>Refresh this page</li>
        </ol>
        <p>If MySQL fails to start, check the XAMPP Control Panel logs for errors.</p>
        ");
    } elseif ($error_code == 1045 || strpos($error_message, "Access denied") !== false) {
        die("
        <h2>Database Access Denied</h2>
        <p><strong>Error:</strong> Access denied for user '{$username}'.</p>
        <p>Please check your database credentials in config.php</p>
        ");
    } elseif ($error_code == 1049 || strpos($error_message, "Unknown database") !== false) {
        die("
        <h2>Database Not Found</h2>
        <p><strong>Error:</strong> Database '{$database}' does not exist.</p>
        <p><strong>Solution:</strong> Create the database in phpMyAdmin or run this SQL:</p>
        <pre>CREATE DATABASE {$database};</pre>
        ");
    } else {
        die("
        <h2>Database Connection Error</h2>
        <p><strong>Error Code:</strong> {$error_code}</p>
        <p><strong>Error Message:</strong> {$error_message}</p>
        <p>Please check your XAMPP MySQL service and database configuration.</p>
        ");
    }
}

// Re-enable error reporting for queries
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Set charset to utf8mb4 for better character support
mysqli_set_charset($conn, "utf8mb4");
?>