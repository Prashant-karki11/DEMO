<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$database = "career_guidance_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . 
        ". Please check your database configuration in config/database.php");
}

// Set charset to UTF-8
$conn->set_charset("utf8mb4");

// You can test the connection with this (comment out after testing):
// echo "Database connected successfully!";
?>