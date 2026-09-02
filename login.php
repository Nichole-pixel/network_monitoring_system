<?php 
session_start();
require_once __DIR__ . '/includes/config.php';

// ================== LOGIN ATTEMPT SYSTEM ==================

// Initialize session values
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

if (!isset($_SESSION['lock_until'])) {
    $_SESSION['lock_until'] = 0;
}

$current_time = time();
$isLocked = false;

// Check if currently locked
if ($_SESSION['lock_until'] > $current_time) {
    $remaining = $_SESSION['lock_until'] - $current_time;
    $error = "Too many failed attempts. Try again in $remaining seconds.";
    $isLocked = true;
}

// ================== LOGIN PROCESS ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLocked) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM account WHERE username = '$username'";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['password'])) {

            if ($user['role'] === 'admin' && $user['acc_status'] === 'active') {

                // Reset attempts on success
                $_SESSION['login_attempts'] = 0;
                $_SESSION['lock_until'] = 0;

                $_SESSION['user_id'] = $user['acc_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                // $_SESSION['is_current_admin'] = $user['is_current_admin'];

                $acc_id = $_SESSION['user_id'];
                $transaction = 'Login successful';
                $sql_log = "INSERT INTO system_log (acc_id, transaction) VALUES ('$acc_id', '$transaction')";
                $conn->query($sql_log);

                header("Location: welcome_dashboard.php");
                exit();

            } else {
                $error = "You are not authorized to access this page.";
            }

        } else {
            // WRONG PASSWORD
            $_SESSION['login_attempts']++;

            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lock_until'] = time() + 30;
                $_SESSION['login_attempts'] = 0;
                $error = "Too many failed attempts. Locked for 30 seconds.";
                $isLocked = true;
            } else {
                $remaining_attempts = 3 - $_SESSION['login_attempts'];
                $error = "Incorrect password! $remaining_attempts attempts remaining.";
            }
        }

    } else {
        // USER NOT FOUND
        $_SESSION['login_attempts']++;

        if ($_SESSION['login_attempts'] >= 3) {
            $_SESSION['lock_until'] = time() + 30;
            $_SESSION['login_attempts'] = 0;
            $error = "Too many failed attempts. Locked for 30 seconds.";
            $isLocked = true;
        } else {
            $remaining_attempts = 3 - $_SESSION['login_attempts'];
            $error = "User not found! $remaining_attempts attempts remaining.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
/* Reset */
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: 'Segoe UI', sans-serif;
}

/* Body */
body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: url('images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
}

body::before {
    content: '';
    position: absolute;
    inset: 0;

    background: linear-gradient(
        rgba(0,0,0,0.45),
        rgba(0,0,0,0.20)
    );

    z-index: 0;
}

/* Wrapper */
.page-wrapper {
    text-align: center;
    z-index: 1;
    animation: fadeIn 0.8s ease;
}

/* Title */
.page-title {
    color: #fff;
    font-size: 30px;
    margin-bottom: 25px;
    text-shadow: 0 4px 10px rgba(0,0,0,0.6);
}

/* Glass Card */
.login-container {
    width: 100%;
    max-width: 400px;
    padding: 30px;
    border-radius: 18px;

    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);

    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);

    animation: slideUp 0.6s ease;
    transition: 0.3s;
}

.login-container:hover {
    transform: translateY(-5px);
}

/* Headings */
h2 {
    color: #fff;
    margin-bottom: 20px;
}

/* Labels */
label {
    display: block;
    text-align: left;
    color: #eee;
    margin-bottom: 5px;
    font-size: 13px;
}

/* Inputs */
input {
    width: 100%;
    height: 44px;
    padding: 0 12px;
    margin-bottom: 15px;

    border-radius: 10px;
    border: none;
    outline: none;

    background: rgba(255,255,255,0.2);
    color: #fff;

    backdrop-filter: blur(5px);
    transition: 0.2s;
}

input::placeholder {
    color: #ddd;
}

input:focus {
    background: rgba(255,255,255,0.3);
    box-shadow: 0 0 6px rgba(255,255,255,0.5);
}

/* Password icon */
.password-wrapper {
    position: relative;
}

.toggle-password {
    position: absolute;
    right: 12px;
    top: 40%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #000;
}

/* Button */
button {
    width: 100%;
    height: 45px;
    border: none;
    border-radius: 10px;

    background: linear-gradient(135deg, #00c853, #64dd17);
    color: white;
    font-weight: bold;
    font-size: 15px;

    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    transform: scale(1.03);
    opacity: 0.9;
}

/* Error */
.error {
    background: rgba(255,0,0,0.2);
    color: #fff;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
}

/* Forgot */
.forgot-password {
    margin-top: 12px;
}

.forgot-password a {
    color: #ddd;
    font-size: 13px;
    text-decoration: none;
}

.forgot-password a:hover {
    text-decoration: underline;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        transform: translateY(40px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* 🌙 Optional Dark Mode (auto) */
@media (prefers-color-scheme: dark) {
    body::before {
        background: rgba(0,0,0,0.6);
    }
}
</style>
</head>

<body>

<div class="login-container">
    <div class="page-wrapper">
        <h1 class="page-title">Network Monitoring and Bandwidth Management System</h1>

    <?php if (isset($error) && $error != ''): ?>
<p class="error" id="errorText"><?php echo $error; ?></p>
<?php endif; ?>

    <form method="POST">
        <label>Username:</label>
        <input type="text" id="username" name="username" required 
        <?= $isLocked ? 'disabled' : '' ?> onkeyup="checkUsername()">

        <label>Password:</label>
        <div class="password-wrapper">
    <input type="password" id="password" name="password" required 
    <?= $isLocked ? 'disabled' : '' ?> disabled>

    <i class="fa-solid fa-eye toggle-password" onclick="togglePassword()"></i>
</div>

        <button type="submit" <?= $isLocked ? 'disabled' : '' ?>>Login</button>
    </form>

    <div class="forgot-password">
        <a href="password/forgot_password.php">Forgot Password?</a>
    </div>
</div>

<script>
// Enable password when username is filled
function checkUsername() {
    var username = document.getElementById("username").value;
    var passwordField = document.getElementById("password");

    if (username !== "") {
        passwordField.disabled = false;
    } else {
        passwordField.disabled = true;
    }
}
function togglePassword() {
    var password = document.getElementById("password");
    var icon = document.querySelector(".toggle-password");

    if (password.type === "password") {
        password.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        password.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>

<?php if ($isLocked): ?>
<script>
// ================= COUNTDOWN TIMER =================
let lockUntil = <?= $_SESSION['lock_until'] ?> * 1000;

function updateTimer() {
    let now = new Date().getTime();
    let remaining = Math.floor((lockUntil - now) / 1000);

    if (remaining <= 0) {
        location.reload(); // auto unlock
    } else {
        document.getElementById("errorText").innerText =
            "Too many failed attempts. Try again in " + remaining + " seconds.";
    }
}

setInterval(updateTimer, 1000);
updateTimer();
</script>
<?php endif; ?>

</body>
</html>

<?php $conn->close(); ?>