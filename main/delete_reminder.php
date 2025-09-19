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

// Check if a reminder ID is provided in the URL
if (isset($_GET['id'])) {
    $user_id = $_SESSION['user_id'];
    $reminder_id = $_GET['id'];

    // Call the function to delete the reminder
    if (delete_reminder($user_id, $reminder_id)) {
        header("Location: reminders.php?success=deleted");
        exit();
    } else {
        header("Location: reminders.php?error=delete_failed");
        exit();
    }
} else {
    header("Location: reminders.php");
    exit();
}
?>