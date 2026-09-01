<?php
include('../db.php');

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

$search = trim($_GET['search'] ?? '');
$toast = '';

if (isset($_GET['deleted'])) {
    $toast = 'deleted';
} elseif (isset($_GET['updated'])) {
    $toast = 'updated';
} elseif (isset($_GET['added'])) {
    $toast = 'added';
}

if ($search !== '') {
    $stmt = $conn->prepare("
        SELECT policy_id, policy_name, website
        FROM policy
        WHERE policy_name LIKE ? OR website LIKE ?
        ORDER BY policy_id ASC
    ");
    $searchParam = "%{$search}%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("
        SELECT policy_id, policy_name, website
        FROM policy
        ORDER BY policy_id ASC
    ");
}

$policies = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $policies[] = $row;
    }
}

$toastMessages = [
    'deleted' => 'Deleted successfully',
    'updated' => 'Updated successfully',
    'added'   => 'Added successfully',
];
?>
<!DOCTYPE html>
<html>
<head>
<title>Policy Dashboard</title>

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
    align-items: flex-start;
    padding: 40px;
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
    width: 95%;
    max-width: 1100px;
    padding: 25px;
    border-radius: 18px;
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);
    position: relative;
    z-index: 1;
}

h2 {
    color: #fff;
    text-align: center;
    margin-bottom: 20px;
}

.top-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    gap: 10px;
}

input {
    flex: 1;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.2);
    background: rgba(0,0,0,0.3);
    color: #fff;
}

input::placeholder {
    color: rgba(255,255,255,0.7);
}

.btn {
    display: inline-block;
    padding: 9px 14px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    font-size: 13px;
    color: #fff;
    transition: 0.3s;
    border: 1px solid rgba(255,255,255,0.2);
    background: rgba(255,255,255,0.12);
    cursor: pointer;
}

.btn:hover {
    transform: scale(1.05);
    background: rgba(255,255,255,0.25);
}

.add {
    border-color: rgba(0,255,120,0.6);
    box-shadow: 0 0 12px rgba(0,255,120,0.3);
}

.update {
    border-color: rgba(0,150,255,0.6);
    box-shadow: 0 0 12px rgba(0,150,255,0.3);
}

.delete {
    border-color: rgba(255,80,80,0.6);
    box-shadow: 0 0 12px rgba(255,80,80,0.3);
}

table {
    width: 100%;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
}

th {
    background: rgba(255,255,255,0.2);
    color: #fff;
    padding: 12px;
}

td {
    padding: 12px;
    color: #fff;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.no-data {
    font-style: italic;
    opacity: 0.9;
}

.modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(6px);
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.modal-content {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,0.25);
    padding: 30px;
    border-radius: 14px;
    text-align: center;
    color: #fff;
    width: 320px;
}

.modal-buttons {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 10px;
}

.toast {
    position: fixed;
    bottom: 25px;
    right: 25px;
    padding: 14px 18px;
    border-radius: 12px;
    color: #fff;
    font-weight: bold;
    font-size: 14px;
    opacity: 0;
    transform: translateY(20px);
    transition: 0.4s;
    z-index: 99999;
    backdrop-filter: blur(10px);
}

.toast.success {
    background: rgba(0, 255, 120, 0.15);
    border: 1px solid rgba(0, 255, 120, 0.6);
    box-shadow: 0 0 20px rgba(0,255,120,0.3);
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
}
</style>
</head>

<body>

<div class="container">
    <h2>Policy Dashboard</h2>

    <div class="top-bar">
        <a href="../welcome_dashboard.php" class="btn">⬅ Back</a>
        <input type="text" id="searchInput" onkeyup="filterTable()" placeholder="Search policy or website" value="<?= e($search) ?>">
        <a href="add_policy.php" class="btn add">+ Add Policy</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Policy Name</th>
                <th>Website</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($policies)): ?>
                <?php foreach ($policies as $policy): ?>
                    <tr>
                        <td><?= e($policy['policy_id']) ?></td>
                        <td><?= e($policy['policy_name']) ?></td>
                        <td><?= e($policy['website']) ?></td>
                        <td>Active</td>
                        <td>
                            <a href="update_policy.php?id=<?= urlencode($policy['policy_id']) ?>" class="btn update">Update</a>
                            <a href="delete_policy.php?id=<?= urlencode($policy['policy_id']) ?>" class="btn delete deleteBtn">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="no-data">No policies found</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal" id="deleteModal">
    <div class="modal-content">
        <p>Are you sure you want to delete this policy?</p>
        <div class="modal-buttons">
            <button type="button" id="confirmDelete" class="btn delete">Yes</button>
            <button type="button" id="cancelDelete" class="btn">Cancel</button>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<script>
let deleteUrl = '';

const deleteModal = document.getElementById('deleteModal');
const confirmDeleteBtn = document.getElementById('confirmDelete');
const cancelDeleteBtn = document.getElementById('cancelDelete');

document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function (e) {
        e.preventDefault();
        deleteUrl = this.href;
        deleteModal.style.display = 'flex';
    });
});

confirmDeleteBtn.addEventListener('click', () => {
    if (deleteUrl) {
        window.location.href = deleteUrl;
    }
});

cancelDeleteBtn.addEventListener('click', () => {
    deleteModal.style.display = 'none';
    deleteUrl = '';
});

deleteModal.addEventListener('click', (e) => {
    if (e.target === deleteModal) {
        deleteModal.style.display = 'none';
        deleteUrl = '';
    }
});

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type} show`;

    setTimeout(() => {
        toast.className = 'toast';
    }, 3000);
}

function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(input) ? '' : 'none';
    });
}
</script>

<?php if ($toast !== '' && isset($toastMessages[$toast])): ?>
<script>
showToast(<?= json_encode($toastMessages[$toast]) ?>, 'success');

if (window.history.replaceState) {
    window.history.replaceState({}, document.title, 'policy_dashboard.php');
}
</script>
<?php endif; ?>

</body>
</html>