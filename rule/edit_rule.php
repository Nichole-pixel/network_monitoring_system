<?php  
require_once __DIR__ . '/../includes/config.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirectTo($url) {
    header("Location: $url");
    exit();
}

$rule_no = $_GET['id'] ?? '';
if ($rule_no === '') {
    die('Invalid rule ID.');
}

/* Fetch current rule */
$stmt = $conn->prepare("
    SELECT r.rule_no, r.client_id, r.policy_id, r.rule_status, c.mac_address
    FROM rules r
    LEFT JOIN client c ON r.client_id = c.client_id
    WHERE r.rule_no = ?
");
$stmt->bind_param("s", $rule_no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die('Rule not found.');
}

/* Fetch policies */
$policies = [];
$policyQuery = $conn->query("
    SELECT policy_id, policy_name
    FROM policy
    ORDER BY policy_name ASC
");

if ($policyQuery) {
    while ($p = $policyQuery->fetch_assoc()) {
        $policies[] = $p;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id   = $row['client_id'];
    $policy_id   = trim($_POST['policy_id'] ?? '');
    $rule_status = trim($_POST['rule_status'] ?? '');

    if ($policy_id === '' || $rule_status === '') {
        $error = 'All fields are required.';
    } else {

        $check = $conn->prepare("
            SELECT rule_no
            FROM rules
            WHERE client_id = ? AND policy_id = ? AND rule_no != ?
            LIMIT 1
        ");
        $check->bind_param("sss", $client_id, $policy_id, $rule_no);
        $check->execute();
        $duplicateResult = $check->get_result();

        if ($duplicateResult->num_rows > 0) {
            $error = 'This PC already has a rule for that policy.';
        } else {

            $update = $conn->prepare("
                UPDATE rules
                SET policy_id = ?, rule_status = ?
                WHERE rule_no = ?
            ");
            $update->bind_param("sss", $policy_id, $rule_status, $rule_no);

            if ($update->execute()) {
                redirectTo("rules_dashboard.php?updated=1");
            } else {
                $error = 'Error updating rule: ' . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Rule</title>

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
    background: rgba(0,0,0,0.35);
}

.container {
    width: 100%;
    max-width: 520px;
    padding: 30px;
    border-radius: 18px;
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    position: relative;
    z-index: 1;
}

h2 {
    color: #fff;
    text-align: center;
    margin-bottom: 20px;
}

/* ✅ FIXED TABLE */
.info-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
    border-radius: 12px;
    overflow: hidden;
}

.info-table th {
    background: rgba(255,255,255,0.25);
    color: #fff;
    padding: 12px;
    text-align: left;
    font-size: 13px;
}

.info-table td {
    background: rgba(255,255,255,0.08);
    color: #fff;
    padding: 12px;
    font-size: 14px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.info-table tr:last-child td {
    border-bottom: none;
}

/* MAC ADDRESS STYLE */
.mac {
    font-family: monospace;
    letter-spacing: 1px;
    word-break: break-all;
    color: #00e5ff;
}

label {
    color: #eee;
    font-size: 13px;
    display: block;
    margin-top: 10px;
}

input, select {
    width: 100%;
    height: 44px;
    padding: 10px;
    margin-top: 6px;
    border-radius: 10px;
    border: none;
    outline: none;
    background: rgba(255,255,255,0.2);
    color: #fff;
}

select option {
    color: #000;
}

.btn {
    margin-top: 15px;
    padding: 10px;
    border-radius: 10px;
    text-decoration: none;
    text-align: center;
    display: inline-block;
    font-size: 14px;
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    transition: 0.3s;
    width: 100%;
}

.btn:hover {
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

<h2>Edit Rule</h2>

<?php if ($error): ?>
    <div class="error"><?= e($error) ?></div>
<?php endif; ?>

<!-- ✅ FIXED TABLE -->
<table class="info-table">
<tr>
    <th>PC ID</th>
    <th>MAC Address</th>
</tr>
<tr>
    <td><?= e($row['client_id']) ?></td>
    <td class="mac">
        <?= e($row['mac_address'] ?: 'No MAC Address') ?>
    </td>
</tr>
</table>

<form method="POST">

<label>Rule No</label>
<input type="text" value="<?= e($row['rule_no']) ?>" readonly>

<label>Policy</label>
<select name="policy_id" required>
    <option value="">Select Policy</option>
    <?php foreach ($policies as $p): ?>
        <option value="<?= e($p['policy_id']) ?>"
            <?= $row['policy_id'] == $p['policy_id'] ? 'selected' : '' ?>>
            <?= e($p['policy_name']) ?>
        </option>
    <?php endforeach; ?>
</select>

<label>Rule Status</label>
<select name="rule_status" required>
    <option value="Active" <?= $row['rule_status'] === 'Active' ? 'selected' : '' ?>>Active</option>
    <option value="Inactive" <?= $row['rule_status'] === 'Inactive' ? 'selected' : '' ?>>Inactive</option>
</select>

<button type="submit" class="btn">Update Rule</button>
<a href="rules_dashboard.php" class="btn">Back</a>

</form>

</div>

</body>
</html>