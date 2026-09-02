<?php  
session_start();
include('db.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

/* BODY */
body {
    min-height: 100vh;
    display: flex;
    overflow-x: hidden;
    background: url('images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
}

/* DARK OVERLAY (stronger for readability) */
body::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 0;
}

/* SIDEBAR */
#sidebar {
    width: 240px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 2;

    background: rgba(255,255,255,0.08);
    backdrop-filter: blur(14px);
    border-right: 1px solid rgba(255,255,255,0.2);

    padding: 20px;
    color: #fff;

    display: flex;
    flex-direction: column;
    transition: 0.3s;
}

/* HEADER */
#sidebar .sidebar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

#sidebar h2 {
    font-size: 16px;
    line-height: 1.3;
}

/* LINKS */
#sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    margin-bottom: 10px;

    color: #fff;
    text-decoration: none;

    border-radius: 10px;
    transition: 0.3s;
}

#sidebar a:hover {
    background: rgba(255,255,255,0.2);
    transform: translateX(5px);
}

/* LOGOUT */
#sidebar .logout {
    margin-top: auto;
    background: rgba(255,80,80,0.2);
}

#sidebar .logout:hover {
    background: rgba(255,80,80,0.4);
}

/* TOGGLE BUTTON */
.toggle-btn {
    background: rgba(255,255,255,0.2);
    border: none;
    padding: 6px 10px;
    border-radius: 6px;
    color: #fff;
    cursor: pointer;
}

/* COLLAPSED */
#sidebar.collapsed {
    width: 70px;
}

#sidebar.collapsed span,
#sidebar.collapsed h2 {
    display: none;
}

#sidebar.collapsed a {
    justify-content: center;
}

/* MAIN */
.main {
    flex: 1;
    margin-left: 240px;
    width: calc(100% - 240px);
    padding: 30px;
    position: relative;
    z-index: 1;
    transition: 0.3s;
}

#sidebar.collapsed + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* HEADER TEXT */
.header h1 {
    color: #fff;
    margin-bottom: 20px;
}

/* DASHBOARD CARD */
.dashboard {
    padding: 40px;
    border-radius: 18px;

    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(16px);

    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.4);

    text-align: center;

    animation: fadeIn 0.5s ease;
}

.dashboard h2 {
    color: #fff;
    margin-bottom: 10px;
}

.dashboard p {
    color: rgba(255,255,255,0.8);
}

/* MODAL */
.modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;

    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(5px);

    justify-content: center;
    align-items: center;
}

.modal-content {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255,255,255,0.25);

    border-radius: 14px;
    padding: 25px;

    text-align: center;
    color: #fff;
    width: 300px;
}

.modal-buttons {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 10px;
}

.modal-buttons button {
    padding: 8px 18px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

/* BUTTON COLORS */
#confirmLogout {
    background: rgba(255,80,80,0.8);
    color: #fff;
}

#confirmLogout:hover {
    background: red;
}

#cancelLogout {
    background: rgba(255,255,255,0.2);
    color: #fff;
}

#cancelLogout:hover {
    background: rgba(255,255,255,0.35);
}

/* ANIMATION */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* MOBILE */
@media (max-width: 768px) {
    #sidebar {
        left: -240px;
    }

    #sidebar.open {
        left: 0;
    }

    .main {
        margin-left: 0;
        width: 100%;
        padding: 20px;
    }
}
</style>
</head>
<body>

<!-- LOGOUT MODAL -->
<div id="logoutModal" class="modal">
    <div class="modal-content">
        <p>Are you sure you want to logout?</p>
        <div class="modal-buttons">
            <button id="confirmLogout">Yes</button>
            <button id="cancelLogout">Cancel</button>
        </div>
    </div>
</div>

<!-- SIDEBAR -->
<div id="sidebar">
    <div class="sidebar-header">
        <h2>Network Monitoring System</h2>
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <a href="account/account_dashboard.php">
        <i class="fa-solid fa-user-shield"></i> <span>Account Management</span>
    </a>

    <a href="client/client_dashboard.php">
        <i class="fa-solid fa-users"></i> <span>Client Dashboard</span>
    </a>

    <a href="policy/policy_dashboard.php">
    <i class="fa-solid fa-file-shield"></i> <span>Policy Dashboard</span>
</a>

<a href="rule/rules_dashboard.php">
    <i class="fa-solid fa-shield-halved"></i> <span>Rules Dashboard</span>
</a>
<a href="system_logs.php">
        <i class="fa-solid fa-clipboard-list"></i> <span>System Logs</span>
    </a>

    <a href="logout.php" class="logout" id="logoutLink">
        <i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
    </a>
</div>

<!-- MAIN CONTENT -->
<div class="main">
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    </div>

    <div class="dashboard">
        <h2>Dashboard</h2>
        <p>Use the sidebar to navigate through the system.</p>
    </div>
</div>

<!-- SCRIPTS -->
<script>
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("open");
        sidebar.classList.remove("collapsed");
    } else {
        sidebar.classList.toggle("collapsed");
        sidebar.classList.remove("open");
    }
}

// Logout modal
const logoutLink = document.querySelector('.logout');
const logoutModal = document.getElementById('logoutModal');
const confirmBtn = document.getElementById('confirmLogout');
const cancelBtn = document.getElementById('cancelLogout');

logoutLink.addEventListener('click', function(e) {
    e.preventDefault();
    logoutModal.style.display = 'flex';
});

confirmBtn.addEventListener('click', function() {
    window.location.href = logoutLink.href;
});

cancelBtn.addEventListener('click', function() {
    logoutModal.style.display = 'none';
});

window.addEventListener('click', function(e) {
    if (e.target == logoutModal) {
        logoutModal.style.display = 'none';
    }
});

// Close modal on Esc key
window.addEventListener('keydown', function(e) {
    if (e.key === "Escape") {
        logoutModal.style.display = 'none';
    }
});
</script>
</body>
</html>