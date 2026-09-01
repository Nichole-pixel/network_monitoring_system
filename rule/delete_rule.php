<?php
include('../db.php');

$id = intval($_GET['id']); // SAFE

$conn->query("DELETE FROM rules WHERE rule_no = $id");

header("Location: rules_dashboard.php?deleted=1");
exit();
?>