<?php
// Start the session to manage user login status
session_start();

// Include the database connection and functions files
include 'includes/db_connection.php';
include 'includes/functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id'];
    $plant_id = $_POST['plant_id'];
    $type = $_POST['type'];
    $reminder_date = $_POST['reminder_date'];

    // Call the function to add a new reminder
    if (add_reminder($user_id, $plant_id, $type, $reminder_date)) {
        header("Location: reminders.php?success=added");
        exit();
    } else {
        header("Location: reminders.php?error=add_failed");
        exit();
    }
} else {
    // If a direct access or invalid request, redirect back
    header("Location: reminders.php");
    exit();
}
?>