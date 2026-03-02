<?php
// Database Configuration
$servername = "localhost";  // Your local computer
$username = "root";         // Default XAMPP username
$password = "";            // Default XAMPP password (empty)
$database = "car_service"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set
$conn->set_charset("utf8mb4");

echo "Connection Successful!";
?>
