<?php
include('../db.php');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {

    // Delete dependent rules first
    $stmt1 = $conn->prepare("DELETE FROM rules WHERE policy_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();

    // Then delete policy
    $stmt2 = $conn->prepare("DELETE FROM policy WHERE policy_id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
}

header("Location: policy_dashboard.php?deleted=1");
exit();
?>