<?php
// includes/db_connection.php

// Database credentials
$servername = "localhost";
$username = "root"; // Replace with your MySQL username
$password = ""; // Replace with your MySQL password
$dbname = "green_leaf";    // Replace with your database name

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->query("SET time_zone = '+05:00'"); // Pakistan time

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>