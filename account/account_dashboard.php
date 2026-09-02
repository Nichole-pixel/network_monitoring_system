<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../includes/config.php';

function maskEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $email;
    }

    list($name, $domain) = explode('@', $email);

    $first = substr($name, 0, 1);
    $last  = substr($name, -2);

    return $first . '****' . $last . '@' . $domain;
}

function maskUsername($username) {
    $len = strlen($username);

    if ($len <= 2) {
        return str_repeat('*', $len);
    }

    $first = $username[0];
    $last  = $username[$len - 1];

    return $first . str_repeat('*', $len - 2) . $last;
}

// Only allow admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' ) {
    header("Location: ../login.php");
    exit();
}

// Fetch accounts
$sql = "SELECT * FROM Account";
$result = $conn->query($sql);

if (!$result) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Account Management</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    background: url('../images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    padding: 40px;
    position: relative;
}

body::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.30);
    z-index: 0;
}

/* WRAPPER */
.wrapper {
    position: relative;
    z-index: 1;
    max-width: 1100px;
    width: 100%;
}

/* TITLE */
h1 {
    text-align: center;
    color: #fff;
    margin-bottom: 25px;
    text-shadow: 0 4px 10px rgba(0,0,0,0.6);
}

/* TABLE CONTAINER */
.table-container {
    background: rgba(255,255,255,0.10);
    backdrop-filter: blur(14px);
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.25);
    padding: 25px;
    overflow-x: auto;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
}

/* BUTTONS */
.btn-back,
.btn-add {
    padding: 10px 16px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: bold;
    color: white;
    transition: 0.3s;
}

.btn-back {
    background: rgba(255,255,255,0.15);
}

.btn-add {
    background: linear-gradient(135deg, #00c853, #64dd17);
}

.btn-back:hover,
.btn-add:hover {
    transform: scale(1.05);
    opacity: 0.9;
}

/* TABLE */
table {
    width: 100%;
    border-collapse: collapse;
}

/* HEADER */
th {
    background: rgba(255,255,255,0.20);
    color: #fff;
    padding: 12px;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 13px;
}

/* DATA CELLS */
td {
    padding: 12px;
    color: #fff;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

/* ROW HOVER */
tr:hover {
    background: rgba(255,255,255,0.08);
}

/* LINKS */
a {
    color: #fff;
    font-weight: bold;
    text-decoration: none;
    transition: 0.3s;
}

a:hover {
    color: #00e5ff;
}

/* DELETE BUTTON */
.deleteBtn {
    color: #ff4d4d;
    font-weight: bold;
    cursor: pointer;
}

/* STATUS COLORS */
.online {
    color: #00ff7f;
    font-weight: bold;
}

.offline {
    color: #ff5252;
    font-weight: bold;
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(5px);
    justify-content: center;
    align-items: center;
}

.modal-content {
    background: rgba(255,255,255,0.10);
    backdrop-filter: blur(14px);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    color: #fff;
    width: 320px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.4);
}

.modal-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: space-around;
}

.modal-buttons button {
    padding: 8px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

#confirmDelete {
    background: #ff5252;
    color: #fff;
}

#cancelDelete {
    background: rgba(255,255,255,0.2);
    color: #fff;
}

.modal-buttons button:hover {
    transform: scale(1.05);
}
</style>
</head>

<body>

<div class="wrapper">

    <h1>Account Management</h1>

    <div style="margin-bottom:15px; display:flex; gap:10px; flex-wrap: wrap;">
        <a href="../welcome_dashboard.php" class="btn-back">⬅ Back</a>
        <a href="add_account.php" class="btn-add">+ Add Account</a>
    </div>

    <div class="table-container">

        <!-- FILTER & SEARCH -->
        <div style="margin-bottom: 15px; display: flex; gap: 10px; flex-wrap: wrap;">
            <select id="statusFilter" style="padding:5px; border-radius:5px;">
                <option value="all">All</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            <input type="text" id="searchBar" placeholder="Search..." style="padding:5px; border-radius:5px; flex-grow:1;">
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Email</th>
                    <th>Username</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['acc_id'] ?></td>
                        <td><?= $row['fname'] ?></td>
                        <td><?= $row['lname'] ?></td>
                        <td><?= maskEmail($row['email']) ?></td>
                        <td><?= maskUsername($row['username']) ?></td>
                        <td><?= $row['position'] ?></td>
                        <td><?= $row['acc_status'] ?></td>
                        <td><?= $row['role'] ?></td>
                        <td>
                            <a href="edit_account.php?id=<?= $row['acc_id'] ?>">Update</a> |
                            <a href="delete_account.php?id=<?= $row['acc_id'] ?>" class="deleteBtn">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" style="text-align:center;">No accounts found</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <p>Are you sure you want to delete this account?</p>

        <div class="modal-buttons">
            <button id="confirmDelete">Yes</button>
            <button id="cancelDelete">Cancel</button>
        </div>
    </div>
</div>

<script>
let deleteUrl = "";

// Open modal
document.querySelectorAll('.deleteBtn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        deleteUrl = this.href;
        document.getElementById('deleteModal').style.display = 'flex';
    });
});

// Confirm delete
document.getElementById('confirmDelete').addEventListener('click', function() {
    window.location.href = deleteUrl;
});

// Cancel delete
document.getElementById('cancelDelete').addEventListener('click', function() {
    document.getElementById('deleteModal').style.display = 'none';
});

// Click outside modal
window.addEventListener('click', function(e) {
    const modal = document.getElementById('deleteModal');
    if (e.target === modal) {
        modal.style.display = 'none';
    }
});

// FILTER & SEARCH
const statusFilter = document.getElementById('statusFilter');
const searchBar = document.getElementById('searchBar');
const tableRows = document.querySelectorAll('tbody tr');

function filterTable() {
    const statusValue = statusFilter.value.toLowerCase();
    const searchValue = searchBar.value.toLowerCase();

    tableRows.forEach(row => {
        const statusText = row.cells[6].textContent.toLowerCase(); // Status column
        const rowText = row.textContent.toLowerCase();

        const statusMatch = (statusValue === 'all') || (statusText === statusValue);
        const searchMatch = rowText.includes(searchValue);

        row.style.display = (statusMatch && searchMatch) ? '' : 'none';
    });
}

statusFilter.addEventListener('change', filterTable);
searchBar.addEventListener('input', filterTable);
</script>

</body>
</html>