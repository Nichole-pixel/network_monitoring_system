<?php
include('db.php');

// SIMPLE QUERY (NO EXTRA LOGIC)
$result = $conn->query("SELECT * FROM bandwidth");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bandwidth Dashboard</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .container { width:80%; margin:auto; background:white; padding:20px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:10px; border:1px solid #ddd; text-align:center; }
        th { background:#f2f2f2; }
        .btn { padding:6px 10px; text-decoration:none; color:white; border-radius:4px; }
        .add { background:green; }
        .edit { background:blue; }
        .delete { background:red; }
    </style>
</head>
<body>

<div class="container">
    <h2>Bandwidth Dashboard</h2>

    <a href="welcome_dashboard.php">⬅ Back</a><br><br>

    <a href="add_bandwidth.php" class="btn add">+ Add Bandwidth</a>

    <table>
        <thead>
            <tr>
                <th>Bandwidth ID</th>
                <th>Max Download (Mbps)</th>
                <th>Max Upload (Mbps)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['bandwidth_id']}</td>
                    <td>{$row['max_download']}</td>
                    <td>{$row['max_upload']}</td>
                    <td>{$row['bw_status']}</td>
                    <td>
                        <a href='edit_bandwidth.php?id={$row['bandwidth_id']}' class='btn edit'>Edit</a>
                        <a href='delete_bandwidth.php?id={$row['bandwidth_id']}' class='btn delete' onclick='return confirm(\"Delete?\")'>Delete</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No bandwidth data found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>