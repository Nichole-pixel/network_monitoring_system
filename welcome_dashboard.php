<?php  
session_start();
require_once __DIR__ . '/includes/config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>

<?php include BASE_PATH . 'includes/header.php'; ?>
<?php include BASE_PATH . 'includes/sidebar.php'; ?>
<div class="main">
    <div class="header">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
    </div>

    <div class="dashboard">
        <h2>Dashboard</h2>
        <p>Use the sidebar to navigate through the system.</p>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>