<?php
require_once __DIR__ . '/../includes/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/* Load clients */
$clients = [];
$clientResult = $conn->query("SELECT client_id, mac_address FROM client ORDER BY client_id ASC");
if ($clientResult) {
    while ($row = $clientResult->fetch_assoc()) {
        $clients[] = $row;
    }
}

/* Load policies */
$policies = [];
$policyResult = $conn->query("SELECT policy_id, policy_name, website FROM policy ORDER BY policy_name ASC");
if ($policyResult) {
    while ($row = $policyResult->fetch_assoc()) {
        $policies[] = $row;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $client_ids = $_POST['client_id'] ?? [];
    $policy_ids = $_POST['policy_id'] ?? [];
    $rule_status = trim($_POST['rule_status'] ?? '');

    if (empty($client_ids) || empty($policy_ids) || $rule_status === '') {
        $error = "All fields are required.";
    } else {

        $stmtCheck = $conn->prepare("
            SELECT rule_no 
            FROM rules 
            WHERE client_id = ? AND policy_id = ? 
            LIMIT 1
        ");

        $stmtInsert = $conn->prepare("
            INSERT INTO rules (client_id, policy_id, rule_status)
            VALUES (?, ?, ?)
        ");

        if (!$stmtCheck || !$stmtInsert) {
            $error = "Database prepare failed.";
        } else {

            $added = 0;
            $skipped = 0;

            foreach ($client_ids as $client_id) {
                foreach ($policy_ids as $policy_id) {

                    $client_id = (int)$client_id;
                    $policy_id = (int)$policy_id;

                    if ($client_id <= 0 || $policy_id <= 0) {
                        continue;
                    }

                    $stmtCheck->bind_param("ii", $client_id, $policy_id);
                    $stmtCheck->execute();
                    $duplicate = $stmtCheck->get_result()->fetch_assoc();

                    if ($duplicate) {
                        $skipped++;
                        continue;
                    }

                    $stmtInsert->bind_param("iis", $client_id, $policy_id, $rule_status);

                    if ($stmtInsert->execute()) {
                        $added++;
                    }
                }
            }

            if ($added > 0) {
                header("Location: rules_dashboard.php?added=$added&skipped=$skipped");
                exit();
            } else {
                $error = "No rules added. All selected rules already exist.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Add Multiple Rules</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    background: url('../images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
}

body::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.45);
}

.container {
    width: 500px;
    padding: 30px;
    border-radius: 18px;

    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(14px);

    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);

    position: relative;
    z-index: 1;
    color: #fff;
}

h2 {
    text-align: center;
    margin-bottom: 20px;
    color: #fff;
}

label {
    display: block;
    margin-top: 12px;
    font-weight: 600;
    font-size: 13px;
    color: #ddd;
}

small {
    display: block;
    margin-top: 5px;
    color: #ccc;
    font-size: 12px;
}

select {
    width: 100%;
    padding: 10px;
    margin-top: 6px;

    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.2);

    background: rgba(0,0,0,0.3);
    color: #fff;

    outline: none;
}

select[multiple] {
    height: 150px;
}

option {
    background: #222;
    color: #fff;
}

.btn {
    margin-top: 20px;
    padding: 10px 14px;

    border-radius: 10px;
    border: none;
    cursor: pointer;

    font-weight: bold;
    transition: 0.3s;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.save-btn {
    width: 100%;
    background: linear-gradient(135deg, #00c853, #64dd17);
    color: #fff;
    box-shadow: 0 0 15px rgba(0,255,120,0.3);
}

.save-btn:hover {
    transform: scale(1.03);
    box-shadow: 0 0 25px rgba(0,255,120,0.5);
}

.back-btn {
    width: 100%;
    margin-top: 10px;
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: 1px solid rgba(255,255,255,0.2);
}

.back-btn:hover {
    background: rgba(255,255,255,0.25);
    transform: scale(1.03);
}

.error {
    color: #ff5252;
    text-align: center;
    margin-bottom: 10px;
    font-weight: bold;
}
</style>
</head>

<body>

<div class="container">

    <h2>Add Multiple Rules</h2>

    <?php if ($error): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">

        <label>PC No</label>
        <select name="client_id[]" multiple required>
            <?php foreach ($clients as $c): ?>
                <option value="<?= e($c['client_id']) ?>">
                    <?= e($c['client_id']) ?> - <?= e($c['mac_address'] ?: 'No MAC') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small>Hold CTRL to select multiple PCs.</small>

        <label>Policy</label>
        <select name="policy_id[]" multiple required>
            <?php foreach ($policies as $p): ?>
                <option value="<?= e($p['policy_id']) ?>">
                    <?= e($p['policy_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small>Hold CTRL to select multiple policies.</small>

        <label>Status</label>
        <select name="rule_status" required>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>

        <button type="submit" class="btn save-btn">Add Rules</button>
        <a href="rules_dashboard.php" class="btn back-btn">Back</a>

    </form>
</div>

</body>
</html>