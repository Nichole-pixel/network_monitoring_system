<?php
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: welcome_dashboard.php");
    exit();
}

header("Location: login.php");
exit();
?>
