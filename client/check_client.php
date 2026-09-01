<?php
include('../db.php');

if (isset($_GET['mac'])) {
    $mac = strtoupper(str_replace('-', ':', $_GET['mac']));

    $stmt = $conn->prepare("SELECT client_id FROM client WHERE mac_address = ?");
    $stmt->bind_param("s", $mac);
    $stmt->execute();
    $res = $stmt->get_result();

    echo ($res->num_rows > 0) ? "⚠ MAC already exists" : "✔ MAC available";
}

if (isset($_GET['pc'])) {
    $pc = $_GET['pc'];

    $stmt = $conn->prepare("SELECT client_id FROM client WHERE pc_no = ?");
    $stmt->bind_param("s", $pc);
    $stmt->execute();
    $res = $stmt->get_result();

    echo ($res->num_rows > 0) ? "⚠ PC No already exists" : "✔ PC No available";
}
?>