<?php
include('../db.php');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (isset($_GET['id'])) {

    $acc_id = intval($_GET['id']); // ✅ prevent SQL injection

    // 1️⃣ Delete related logs first
    $stmt1 = $conn->prepare("DELETE FROM system_log WHERE acc_id = ?");
    $stmt1->bind_param("i", $acc_id);
    $stmt1->execute();
    $stmt1->close();

    // 2️⃣ Delete account
    $stmt2 = $conn->prepare("DELETE FROM account WHERE acc_id = ?");
    $stmt2->bind_param("i", $acc_id);

    if ($stmt2->execute()) {
        $_SESSION['success'] = "Account deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete account.";
    }

    $stmt2->close();
}

$conn->close();

header("Location: account_dashboard.php");
exit();
?>