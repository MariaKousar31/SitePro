<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: landing.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
?>