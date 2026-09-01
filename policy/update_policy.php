<?php 
include('../db.php');

$error = '';

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function normalizeDomain($input) {
    $input = trim(strtolower($input));

    if ($input === '') return '';

    $input = preg_replace('/\s+/', '', $input);

    if (!preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $input)) {
        $input = 'http://' . $input;
    }

    $host = parse_url($input, PHP_URL_HOST);

    if (!$host) return '';

    $host = preg_replace('/:\d+$/', '', $host);
    $host = rtrim($host, '.');

    if (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        return '';
    }

    return $host;
}

function getBaseDomain($host) {
    $parts = explode('.', $host);

    if (count($parts) >= 2) {
        return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
    }

    return $host;
}

// ✅ GET ID
$id = $_GET['id'] ?? '';

if (!$id) {
    header("Location: policy_dashboard.php");
    exit();
}

// ✅ FETCH EXISTING DATA
$stmt = $conn->prepare("SELECT * FROM policy WHERE policy_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$policy = $result->fetch_assoc();

if (!$policy) {
    header("Location: policy_dashboard.php");
    exit();
}

// ✅ UPDATE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['policy_name'] ?? '');
    $websiteInput = $_POST['website'] ?? '';

    $website = normalizeDomain($websiteInput);

    if ($name === '' || $website === '') {
        $error = "Policy name and valid website are required.";
    } else {

        $baseWebsite = getBaseDomain($website);

        // 🔍 CHECK DUPLICATE (EXCLUDE CURRENT ID)
        $checkStmt = $conn->prepare("
            SELECT policy_id 
            FROM policy 
            WHERE (LOWER(policy_name) = LOWER(?) 
                OR LOWER(website) = LOWER(?))
              AND policy_id != ?
            LIMIT 1
        ");

        $checkStmt->bind_param("ssi", $name, $baseWebsite, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            header("Location: policy_dashboard.php?exists=1");
            exit();
        }

        // ✅ UPDATE
        $updateStmt = $conn->prepare("
            UPDATE policy 
            SET policy_name = ?, website = ?
            WHERE policy_id = ?
        ");

        $updateStmt->bind_param("ssi", $name, $baseWebsite, $id);

        if ($updateStmt->execute()) {
            header("Location: policy_dashboard.php?updated=1");
            exit();
        } else {
            $error = "Failed to update policy.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Policy</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif; }

        body {
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:url('../images/background.jpg') no-repeat center center fixed;
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
            width:500px;
            padding:30px;
            border-radius:18px;
            background:rgba(255,255,255,0.12);
            backdrop-filter:blur(14px);
            border:1px solid rgba(255,255,255,0.2);
            box-shadow:0 10px 40px rgba(0,0,0,0.4);
            position:relative;
            z-index:1;
            color:#fff;
        }

        h2 { text-align:center; margin-bottom:20px; }

        label {
            display:block;
            margin-top:12px;
            font-weight:600;
            font-size:13px;
            color:#ddd;
        }

        input {
            width:100%;
            padding:10px;
            margin-top:6px;
            border-radius:10px;
            border:1px solid rgba(255,255,255,0.2);
            background:rgba(0,0,0,0.3);
            color:#fff;
        }

        button {
            width:100%;
            margin-top:20px;
            padding:12px;
            border:none;
            border-radius:10px;
            font-weight:bold;
            cursor:pointer;
            background:linear-gradient(135deg, #2196f3, #00b0ff);
            color:#fff;
            box-shadow:0 0 15px rgba(0,150,255,0.3);
            transition:0.3s;
        }

        button:hover {
            transform:scale(1.03);
            box-shadow:0 0 25px rgba(0,150,255,0.5);
        }

        .error {
            color:#ff5252;
            text-align:center;
            margin-bottom:10px;
            font-weight:bold;
        }

        .back {
            display:block;
            margin-top:15px;
            text-align:center;
            color:#fff;
            text-decoration:none;
            opacity:0.7;
        }

        .back:hover { opacity:1; }
    </style>
</head>
<body>

<div class="container">
    <h2>Update Policy</h2>

    <?php if ($error !== ''): ?>
        <div class="error"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <label>Policy Name</label>
        <input type="text" name="policy_name" value="<?= e($policy['policy_name']) ?>" required>

        <label>Website</label>
        <input type="text" name="website" value="<?= e($policy['website']) ?>" required>

        <button type="submit">Update Policy</button>
    </form>

    <a class="back" href="policy_dashboard.php">⬅ Back to Dashboard</a>
</div>

</body>
</html>