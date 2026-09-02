<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Logs Dashboard</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

/* BACKGROUND */
body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px;

    background: url('images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
}

/* DARK OVERLAY */
body::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.45);
}

/* WRAPPER */
.wrapper {
    position: relative;
    z-index: 1;
    width: 95%;
    max-width: 1200px;
}

/* TITLE */
h1 {
    color: #fff;
    text-align: center;
    margin-bottom: 20px;
    text-shadow: 0 6px 18px rgba(0,0,0,0.6);
}

/* BUTTONS */
.btn-back {
    display: inline-block;
    margin-bottom: 15px;

    padding: 10px 14px;
    border-radius: 12px;

    text-decoration: none;
    color: #fff;

    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);

    backdrop-filter: blur(10px);
    transition: 0.3s;
}

.btn-back:hover {
    transform: scale(1.05);
    background: rgba(255,255,255,0.25);
}

/* GLASS TABLE CONTAINER */
.table-container {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);

    border-radius: 18px;
    padding: 20px;

    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);

    max-height: 650px;
    overflow-y: auto;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

/* HEADER */
th {
    position: sticky;
    top: 0;

    background: rgba(255,255,255,0.18);
    color: #fff;

    padding: 12px;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 1px;
}

/* ROWS */
td {
    padding: 12px;
    text-align: center;
    color: #fff;

    border-bottom: 1px solid rgba(255,255,255,0.08);
}

tr {
    transition: 0.2s;
}

tr:hover {
    background: rgba(255,255,255,0.08);
    transform: scale(1.01);
}

/* EMPTY STATE */
.empty {
    text-align: center;
    padding: 20px;
    color: rgba(255,255,255,0.8);
    font-style: italic;
}

/* SCROLLBAR (nice touch) */
.table-container::-webkit-scrollbar {
    width: 8px;
}

.table-container::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.2);
    border-radius: 10px;
}
</style>
</head>

<body>

<div class="wrapper">

    <h1>System Logs Dashboard</h1>

    <a href="welcome_dashboard.php" class="btn-back">⬅ Back to Dashboard</a>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Log ID</th>
                    <th>Username</th>
                    <th>Transaction</th>
                    <th>Date & Time</th>
                </tr>
            </thead>

            <tbody>
            <?php
            require_once __DIR__ . '/includes/config.php';
            session_start();

            if(!isset($_SESSION['user_id'])) {
                header("Location: ../login.php");
                exit();
            }

            $sql = "
                SELECT system_log.log_id, account.username, system_log.transaction, system_log.date_time
                FROM system_log
                JOIN account ON system_log.acc_id = account.acc_id
                ORDER BY system_log.date_time DESC
            ";

            $result = $conn->query($sql);

            if($result && $result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    echo "<tr>
                        <td>{$row['log_id']}</td>
                        <td>{$row['username']}</td>
                        <td>{$row['transaction']}</td>
                        <td>{$row['date_time']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='empty'>No logs found</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

</div>

</body>
</html>