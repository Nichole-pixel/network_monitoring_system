<?php
require_once __DIR__ . '/../includes/config.php';

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$id = $_GET['id'];

$result = $conn->query("SELECT * FROM client WHERE client_id=$id");
$data = $result->fetch_assoc();

$toast = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mac = trim($_POST['mac']);
    $pc = trim($_POST['pc']);

    if ($mac === '' || $pc === '') {
        $toast = 'error';
    } else {
        $stmt = $conn->prepare("UPDATE client SET mac_address=?, pc_no=? WHERE client_id=?");
        $stmt->bind_param("ssi", $mac, $pc, $id);
        $stmt->execute();

        header("Location: client_dashboard.php?updated=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Edit Client</title>

<style>
* {
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI', sans-serif;
}

body {
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:url('../images/background.jpg') no-repeat center/cover fixed;
    position:relative;
}

body::before {
    content:'';
    position:absolute;
    inset:0;
    background:rgba(0,0,0,0.45);
}

/* CONTAINER */
.container {
    width:400px;
    padding:30px;
    border-radius:18px;
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(14px);
    border:1px solid rgba(255,255,255,0.2);
    box-shadow:0 10px 40px rgba(0,0,0,0.4);
    color:#fff;
    position:relative;
    z-index:1;
}

h2 {
    text-align:center;
    margin-bottom:20px;
}

/* INPUT */
label {
    font-size:13px;
    opacity:0.9;
}

input {
    width:100%;
    padding:10px;
    margin-top:6px;
    margin-bottom:15px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.2);
    background:rgba(0,0,0,0.3);
    color:#fff;
    outline:none;
}

/* BUTTON */
.btn {
    width:100%;
    padding:10px;
    border-radius:10px;
    border:none;
    font-weight:bold;
    cursor:pointer;
    color:#fff;
    margin-top:10px;
    transition:.3s;
}

.save {
    background:rgba(0,255,120,0.2);
    border:1px solid rgba(0,255,120,0.6);
    box-shadow:0 0 12px rgba(0,255,120,0.3);
}

.back {
    display:block;
    text-align:center;
    margin-top:10px;
    text-decoration:none;
    color:#fff;
    padding:10px;
    border-radius:10px;
    background:rgba(255,255,255,0.12);
}

.btn:hover, .back:hover {
    transform:scale(1.05);
}

/* TOAST */
.toast {
    position:fixed;
    bottom:25px;
    right:25px;
    padding:14px;
    border-radius:10px;
    color:#fff;
    opacity:0;
    transform:translateY(20px);
    transition:.4s;
}

.toast.show {
    opacity:1;
    transform:translateY(0);
}

.toast.error {
    background:rgba(255,80,80,0.2);
    border:1px solid rgba(255,80,80,0.6);
}
</style>
</head>

<body>

<div class="container">
    <h2>Edit Client</h2>

    <form method="POST">
        <label>MAC Address</label>
        <input type="text" name="mac" value="<?= e($data['mac_address']) ?>">

        <label>PC No</label>
        <input type="text" name="pc" value="<?= e($data['pc_no']) ?>">

        <button type="submit" class="btn save">Update Client</button>
    </form>

    <a href="client_dashboard.php" class="back">⬅ Back</a>
</div>

<div id="toast" class="toast"></div>

<script>
function showToast(msg,type='error'){
    const t=document.getElementById('toast');
    t.textContent=msg;
    t.className='toast show '+type;

    setTimeout(()=>t.classList.remove('show'),3000);
}

<?php if ($toast === 'error'): ?>
showToast("All fields are required");
<?php endif; ?>
</script>

</body>
</html>