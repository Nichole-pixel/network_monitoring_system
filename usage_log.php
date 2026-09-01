<?php
include('db.php');

if (!isset($_GET['id'])) {
    die("No client selected.");
}

$client_id = $_GET['id'];

$view = isset($_GET['view']) && $_GET['view'] === 'monthly' ? 'monthly' : 'hourly';

if ($view === 'hourly') {
    $stmt = $conn->prepare("
        SELECT * FROM usage_log 
        WHERE client_id = ? 
        ORDER BY log_datetime DESC
    ");
    $stmt->bind_param("i", $client_id);
} else {
    $stmt = $conn->prepare("
        SELECT 
            YEAR(log_datetime) AS log_year,
            MONTH(log_datetime) AS log_month,
            AVG(ave_download) AS avg_download,
            AVG(ave_upload) AS avg_upload,
            bw_status
        FROM usage_log
        WHERE client_id = ?
        GROUP BY YEAR(log_datetime), MONTH(log_datetime)
        ORDER BY log_year DESC, log_month DESC
    ");
    $stmt->bind_param("i", $client_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
<title>Usage Logs</title>

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', sans-serif;
}

body {
    min-height: 100vh;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    padding:40px;

    background:url('images/background.jpg') no-repeat center center fixed;
    background-size:cover;
    position:relative;
}

body::before {
    content:'';
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.45);
}

.container {
    width:95%;
    max-width:1100px;
    padding:25px;
    border-radius:18px;

    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(14px);

    border:1px solid rgba(255,255,255,0.2);
    box-shadow:0 10px 40px rgba(0,0,0,0.4);

    position:relative;
    z-index:1;
    color:#fff;
}

h2 {
    text-align:center;
    margin-bottom:15px;
}

/* BACK BUTTON */
.back {
    display:inline-block;
    margin-bottom:15px;
    padding:8px 12px;

    background:rgba(255,255,255,0.12);
    border:1px solid rgba(255,255,255,0.2);
    border-radius:10px;

    color:#fff;
    text-decoration:none;
    transition:0.3s;
}

.back:hover {
    transform:scale(1.05);
    background:rgba(255,255,255,0.25);
}

/* VIEW SWITCH */
.view-selector {
    margin-bottom:15px;
    display:flex;
    gap:10px;
}

.view-selector a {
    padding:8px 12px;
    border-radius:10px;

    text-decoration:none;
    color:#fff;

    background:rgba(255,255,255,0.12);
    border:1px solid rgba(255,255,255,0.2);

    transition:0.3s;
}

.view-selector a:hover {
    transform:scale(1.05);
    background:rgba(255,255,255,0.25);
}

.view-selector a.active {
    border:1px solid rgba(0,255,120,0.5);
    box-shadow:0 0 12px rgba(0,255,120,0.3);
}

/* TABLE */
table {
    width:100%;
    border-collapse:collapse;
    overflow:hidden;
    border-radius:12px;
}

th {
    background:rgba(255,255,255,0.2);
    padding:12px;
    text-transform:uppercase;
    font-size:13px;
}

td {
    padding:12px;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

tr {
    background:rgba(255,255,255,0.08);
}

tr:nth-child(even) {
    background:rgba(255,255,255,0.12);
}

/* STATUS COLORS */
.status-good { color:#00ff7f; font-weight:bold; }
.status-bad { color:#ff5252; font-weight:bold; }
.status-mid { color:#ffd54f; font-weight:bold; }
</style>

</head>

<body>

<div class="container">

    <a href="client/client_dashboard.php" class="back">⬅ Back</a>

    <h2>Usage Logs (Client ID: <?= htmlspecialchars($client_id) ?>)</h2>

    <div class="view-selector">
        <a href="?id=<?= $client_id ?>&view=hourly" class="<?= $view === 'hourly' ? 'active' : '' ?>">Hourly</a>
        <a href="?id=<?= $client_id ?>&view=monthly" class="<?= $view === 'monthly' ? 'active' : '' ?>">Monthly</a>
    </div>

    <table>
        <thead>
            <tr>
                <?php if ($view === 'hourly'): ?>
                    <th>ID</th>
                    <th>Download</th>
                    <th>Upload</th>
                    <th>Status</th>
                    <th>Date</th>
                <?php else: ?>
                    <th>Year</th>
                    <th>Month</th>
                    <th>Download</th>
                    <th>Upload</th>
                    <th>Status</th>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>
        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <?php if ($view === 'hourly'): ?>
                        <td><?= $row['usage_id'] ?></td>
                        <td><?= $row['ave_download'] ?> Mbps</td>
                        <td><?= $row['ave_upload'] ?> Mbps</td>
                        <td><?= $row['bw_status'] ?></td>
                        <td><?= $row['log_datetime'] ?></td>
                    <?php else: ?>
                        <td><?= $row['log_year'] ?></td>
                        <td><?= date('F', mktime(0,0,0,$row['log_month'],1)) ?></td>
                        <td><?= number_format($row['avg_download'],2) ?> Mbps</td>
                        <td><?= number_format($row['avg_upload'],2) ?> Mbps</td>
                        <td><?= $row['bw_status'] ?></td>
                    <?php endif; ?>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No logs found</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>

</body>
</html>