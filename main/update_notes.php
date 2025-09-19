<?php
session_start();
include 'includes/db_connection.php';
include 'includes/functions.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['user_plant_id'])) {
    header("Location: my_garden.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_plant_id = (int)$_POST['user_plant_id'];
$notes = trim($_POST['notes']);

update_user_plant_notes($user_plant_id, $notes, $user_id);

header("Location: my_garden.php");
exit();
