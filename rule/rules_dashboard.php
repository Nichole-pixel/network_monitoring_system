<?php  
require_once __DIR__ . '/../includes/config.php';

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');
$toast = '';

if (isset($_GET['deleted'])) $toast = 'deleted';
elseif (isset($_GET['updated'])) $toast = 'updated';
elseif (isset($_GET['added'])) $toast = 'added';

/* FETCH RULES */
if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT r.rule_no, p.policy_name, r.rule_status
        FROM rules r
        LEFT JOIN policy p ON r.policy_id = p.policy_id
        WHERE r.rule_no LIKE ? OR p.policy_name LIKE ?
        ORDER BY r.rule_no ASC
    ");
    $searchParam = "%{$search}%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT r.rule_no, p.policy_name, r.rule_status
        FROM rules r
        LEFT JOIN policy p ON r.policy_id = p.policy_id
        ORDER BY r.rule_no ASC
    ");
}

$rules = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $rules[] = $row;
    }
}

$toastMessages = [
    'deleted' => 'Rule deleted successfully',
    'updated' => 'Rule updated successfully',
    'added'   => 'Rule added successfully',
];

/* ✅ FIXED STATUS FUNCTION */
function getStatusClass($status) {
    return strtolower(trim($status)) === 'active' ? 'active' : 'inactive';
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Rules Dashboard</title>

<style>
* { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', sans-serif; }

body {
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    padding:40px;
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
}

h2 {
    color:#fff;
    text-align:center;
    margin-bottom:20px;
}

.top-bar {
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
    gap:10px;
}

input {
    flex:1;
    padding:10px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,0.2);
    background:rgba(0,0,0,0.3);
    color:#fff;
}

.btn {
    padding:9px 14px;
    border-radius:10px;
    text-decoration:none;
    font-weight:bold;
    font-size:13px;
    color:#fff;
    border:1px solid rgba(255,255,255,0.2);
    background:rgba(255,255,255,0.12);
    transition:0.3s;
}

.btn:hover {
    transform:scale(1.05);
    background:rgba(255,255,255,0.25);
}

.add { border-color: rgba(0,255,120,0.6); box-shadow:0 0 12px rgba(0,255,120,0.3); }
.update { border-color: rgba(0,150,255,0.6); box-shadow:0 0 12px rgba(0,150,255,0.3); }
.delete { border-color: rgba(255,80,80,0.6); box-shadow:0 0 12px rgba(255,80,80,0.3); }

table {
    width:100%;
    border-collapse:collapse;
    border-radius:12px;
    overflow:hidden;
}

th {
    background:rgba(255,255,255,0.2);
    color:#fff;
    padding:12px;
}

td {
    padding:12px;
    color:#fff;
    text-align:center;
    border-bottom:1px solid rgba(255,255,255,0.1);
}

/* ✅ STATUS BADGES */
.active {
    color: #00ff90;
    background: rgba(0,255,120,0.15);
    border: 1px solid rgba(0,255,120,0.6);
    padding: 5px 10px;
    border-radius: 8px;
    font-weight: bold;
    display: inline-block;
}

.inactive {
    color: #ff5252;
    background: rgba(255,80,80,0.15);
    border: 1px solid rgba(255,80,80,0.6);
    padding: 5px 10px;
    border-radius: 8px;
    font-weight: bold;
    display: inline-block;
}

/* MODAL */
.modal {
    display:none;
    position:fixed;
    inset:0;
    background:rgba(0,0,0,0.6);
    backdrop-filter:blur(6px);
    justify-content:center;
    align-items:center;
    z-index:9999;
}

.modal-content {
    background:rgba(255,255,255,0.12);
    backdrop-filter:blur(16px);
    padding:30px;
    border-radius:14px;
    text-align:center;
    color:#fff;
}

.modal-buttons {
    margin-top:15px;
    display:flex;
    justify-content:center;
    gap:10px;
}

/* TOAST */
.toast {
    position:fixed;
    bottom:25px;
    right:25px;
    padding:14px 18px;
    border-radius:12px;
    color:#fff;
    font-weight:bold;
    opacity:0;
    transform:translateY(20px);
    transition:0.4s;
}

.toast.show {
    opacity:1;
    transform:translateY(0);
}
</style>
</head>

<body>

<div class="container">
    <h2>Rules Dashboard</h2>

    <div class="top-bar">
        <a href="../welcome_dashboard.php" class="btn">⬅ Back</a>
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search rules..." value="<?= e($search) ?>">
        <a href="add_rule.php" class="btn add">+ Add Rule</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Rule No</th>
                <th>Policy Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($rules)): ?>
                <?php foreach ($rules as $row): ?>
                    <tr>
                        <td><?= e($row['rule_no']) ?></td>
                        <td><?= e($row['policy_name'] ?: 'No Policy') ?></td>

                        <td>
                            <span class="<?= getStatusClass($row['rule_status']) ?>">
                                <?= ucfirst(strtolower(e($row['rule_status']))) ?>
                            </span>
                        </td>

                        <td>
                            <a href="edit_rule.php?id=<?= $row['rule_no'] ?>" class="btn update">Edit</a>
                            <a href="delete_rule.php?id=<?= $row['rule_no'] ?>" class="btn delete deleteBtn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">No rules found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- MODAL -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <p>Are you sure you want to delete this rule?</p>
        <div class="modal-buttons">
            <button id="confirmDelete" class="btn delete">Yes</button>
            <button id="cancelDelete" class="btn">Cancel</button>
        </div>
    </div>
</div>

<!-- TOAST -->
<div id="toast" class="toast"></div>

<script>
let deleteUrl = '';

document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function(e){
        e.preventDefault();
        deleteUrl = this.href;
        document.getElementById('deleteModal').style.display = 'flex';
    });
});

document.getElementById('confirmDelete').onclick = () => {
    window.location.href = deleteUrl;
};

document.getElementById('cancelDelete').onclick = () => {
    document.getElementById('deleteModal').style.display = 'none';
};

function showToast(msg){
    const t = document.getElementById('toast');
    t.innerText = msg;
    t.classList.add('show');
    setTimeout(()=>t.classList.remove('show'),3000);
}

function filterTable(){
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row=>{
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}

<?php if ($toast !== '' && isset($toastMessages[$toast])): ?>
showToast(<?= json_encode($toastMessages[$toast]) ?>);
<?php endif; ?>
</script>

</body>
</html>