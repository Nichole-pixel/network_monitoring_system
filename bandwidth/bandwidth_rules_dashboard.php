<?php
include('db.php');

// SIMPLE QUERY (NO JOINS)
$result = $conn->query("SELECT * FROM bandwidth_rules");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bandwidth Rules Dashboard</title>
    <style>
        body { font-family: Arial; background:#f4f4f4; }
        .container { width:80%; margin:auto; background:white; padding:20px; }
        table { width:100%; border-collapse:collapse; margin-top:20px; }
        th, td { padding:10px; border:1px solid #ddd; text-align:center; }
        th { background:#f2f2f2; }
        .btn { padding:6px 10px; color:white; border-radius:4px; text-decoration:none; }
        .add { background:green; }
        .edit { background:blue; }
        .delete { background:red; }
    </style>
</head>
<body>

<div class="container">
    <h2>Bandwidth Rules Dashboard</h2>

    <a href="welcome_dashboard.php">⬅ Back</a><br><br>

    <a href="add_bandwidth_rule.php" class="btn add">+ Add Rule</a>

    <table>
        <thead>
            <tr>
                <th>Rule ID</th>
                <th>Client ID</th>
                <th>Bandwidth ID</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row['bwrule_id']}</td>
                    <td>{$row['client_id']}</td>
                    <td>{$row['bandwidth_id']}</td>
                    <td>{$row['bwrule_status']}</td>
                    <td>
                        <a href='edit_bandwidth_rule.php?id={$row['bwrule_id']}' class='btn edit'>Edit</a>
                        <a href='delete_bandwidth_rule.php?id={$row['bwrule_id']}' class='btn delete' onclick='return confirm(\"Delete?\")'>Delete</a>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No rules found</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

</body>
</html>