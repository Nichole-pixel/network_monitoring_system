<?php
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $down = $_POST['max_download'];
    $up = $_POST['max_upload'];
    $status = $_POST['bw_status'];

    $stmt = $conn->prepare("
        INSERT INTO bandwidth (max_download, max_upload, bw_status)
        VALUES (?, ?, ?)
    ");
    $stmt->bind_param("dds", $down, $up, $status);
    $stmt->execute();

    header("Location: bandwidth_dashboard.php");
}
?>

<h2>Add Bandwidth</h2>

<form method="POST">
    Max Download:<br>
    <input type="number" step="0.01" name="max_download" required><br><br>

    Max Upload:<br>
    <input type="number" step="0.01" name="max_upload" required><br><br>

    Status:<br>
    <select name="bw_status">
        <option value="Active">Active</option>
        <option value="Inactive">Inactive</option>
    </select><br><br>

    <button type="submit">Save</button>
</form>