<?php
include('../db.php');

// Function to check if PC is online using ping
function isOnline($ip) {
    if (!$ip) return false;
    $pingresult = exec("ping -n 1 -w 1000 $ip", $output, $status);
    return $status === 0;
}

$search = isset($_GET['search']) ? $_GET['search'] : "";

if ($search != "") {
    $stmt = $conn->prepare("SELECT * FROM client WHERE pc_no LIKE ? OR mac_address LIKE ?");
    $param = "%$search%";
    $stmt->bind_param("ss", $param, $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM client");
}

echo '<table>
<tr>
    <th>ID</th>
    <th>MAC Address</th>
    <th>PC No.</th>
    <th>Status</th>
    <th>Actions</th>
</tr>';

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Check status: use $row["ip_address"] if you store PC IP
        $status = isset($row['ip_address']) && isOnline($row['ip_address']) ? "Online" : "Offline";

        echo "<tr>
            <td>{$row['client_id']}</td>
            <td>{$row['mac_address']}</td>
            <td>{$row['pc_no']}</td>
            <td class='".($status=='Online'?'online':'offline')."'>
                ".($status=='Online'?'🟢 Online':'🔴 Offline')."
            </td>
            <td>
                <a href='edit_client.php?id={$row['client_id']}' class='btn edit'>Edit</a>
                <a href='delete_client.php?id={$row['client_id']}' class='btn delete' onclick='return confirm(\"Delete?\")'>Delete</a>
            </td>
        </tr>";
    }
} else {
    echo "<tr><td colspan='5'>No clients found</td></tr>";
}

echo '</table>';
?>