<!-- SIDEBAR -->
<div id="sidebar">
    <div class="sidebar-header">
        <h2>Network Monitoring System</h2>
        <button class="toggle-btn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
    </div>

    <a href="<?= BASE_URL ?>account/account_dashboard.php">
        <i class="fa-solid fa-user-shield"></i> <span>Account Management</span>
    </a>

    <a href="<?= BASE_URL ?>client/client_dashboard.php">
        <i class="fa-solid fa-users"></i> <span>Client Dashboard</span>
    </a>

    <a href="<?= BASE_URL ?>policy/policy_dashboard.php">
        <i class="fa-solid fa-file-shield"></i> <span>Policy Dashboard</span>
    </a>

    <a href="<?= BASE_URL ?>rule/rules_dashboard.php">
        <i class="fa-solid fa-shield-halved"></i> <span>Rules Dashboard</span>
    </a>

    <a href="<?= BASE_URL ?>system_logs.php">
        <i class="fa-solid fa-clipboard-list"></i> <span>System Logs</span>
    </a>

    <a href="<?= BASE_URL ?>logout.php" class="logout" id="logoutLink">
        <i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span>
    </a>
</div>
