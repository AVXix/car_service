<?php
// ------------------------------------------------------------
// config.php
// ------------------------------------------------------------
// Shared database bootstrap used by entry pages (`index.php`, `admin.php`).
// Keep this file focused on connection setup only.

// Database connection configuration.
$servername = "localhost";  // Local MySQL host (XAMPP default)
$username = "root";         // MySQL username (XAMPP default)
$password = "";             // MySQL password (XAMPP default is empty)
$database = "car_service";  // Application database name

// Open MySQLi connection.
$conn = new mysqli($servername, $username, $password, $database);

// Fail safely: log internals, show generic message to client.
if ($conn->connect_error) {
    error_log('Database connection failed: ' . $conn->connect_error);
    die('Database connection failed. Please try again later.');
}

// Enforce UTF-8 for safe multi-language and emoji handling.
$conn->set_charset("utf8mb4");
?>
