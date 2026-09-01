<?php
session_start();

include('../db.php');

$error_message = '';
$success_message = '';

date_default_timezone_set('Asia/Manila');

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Invalid reset link.");
}


// RESET PASSWORD PROCESS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);


    if (empty($new_password) || empty($confirm_password)) {

        $error_message = "Please enter both password fields.";

    } elseif ($new_password !== $confirm_password) {

        $error_message = "Password do not match.";

    } else {


        // CHECK TOKEN
        $stmt = $conn->prepare("
            SELECT user_id
            FROM password_reset_tokens
            WHERE token = ?
            AND expires_at > NOW()
            LIMIT 1
        ");

        $stmt->bind_param("s", $token);
        $stmt->execute();

        $result = $stmt->get_result();

        $reset = $result->fetch_assoc();



        if (!$reset) {

            $error_message = "Invalid or expired reset link.";

        } else {


            // HASH PASSWORD
            $hashed_password = password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );


            // UPDATE PASSWORD
            $stmt = $conn->prepare("
                UPDATE account
                SET password = ?
                WHERE acc_id = ?
            ");


            $stmt->bind_param(
                "si",
                $hashed_password,
                $reset['user_id']
            );



            if ($stmt->execute()) {


                // DELETE TOKEN AFTER SUCCESS
                $stmt = $conn->prepare("
                    DELETE FROM password_reset_tokens
                    WHERE token = ?
                ");

                $stmt->bind_param("s", $token);
                $stmt->execute();



                $success_message = "Password reset successfully!";


            } else {

                $error_message = "Failed to update password.";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>

<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

/* BACKGROUND (same as login.php) */
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

/* GLASS CARD */
.container {
    width: 100%;
    max-width: 400px;
    padding: 30px;
    border-radius: 18px;

    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);

    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);

    z-index: 1;
    text-align: center;
}

/* TITLE */
h2 {
    color: #fff;
    margin-bottom: 20px;
}

label {
    display: block;
    text-align: left;
    color: #eee;
    font-size: 13px;
    margin-bottom: 5px;
}

.input-box {
    position: relative;
}

input {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;

    border-radius: 10px;
    border: none;
    outline: none;

    background: rgba(255,255,255,0.2);
    color: #fff;
}

input::placeholder {
    color: #ddd;
}

/* EYE ICON */
.toggle-password {
    position: absolute;
    right: 12px;
    top: 40%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #000;
}

/* BUTTON */
button {
    width: 100%;
    padding: 12px;

    background: linear-gradient(135deg, #00c853, #64dd17);
    color: white;

    border: none;
    border-radius: 10px;

    font-weight: bold;
    cursor: pointer;
    transition: 0.3s;
}

button:hover {
    transform: scale(1.03);
}

/* MESSAGES */
.error {
    background: rgba(255,0,0,0.2);
    color: #fff;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 10px;
}

.success {
    background: rgba(0,255,0,0.2);
    color: #fff;
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 10px;
}

.strength {
    font-size: 12px;
    margin-bottom: 10px;
    color: #fff;
}
</style>
</head>

<body>

<div class="container">

    <h2>Reset Password</h2>

    <?php if ($error_message): ?>
        <div class="error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="success"><?php echo $success_message; ?></div>
        <p style="color:white;">Redirecting...</p>

        <script>
            setTimeout(() => {
                window.location.href = "../login.php";
            }, 2000);
        </script>

    <?php else: ?>

    <form method="POST">

        <label>New Password</label>
        <div class="input-box">
            <input type="password" id="new_password" name="new_password"
                   onkeyup="checkStrength()" required>

            <i class="fa-solid fa-eye toggle-password"
               onclick="togglePassword('new_password', this)"></i>
        </div>

        <div class="strength" id="strengthText"></div>

        <label>Confirm Password</label>
        <div class="input-box">
            <input type="password" id="confirm_password" name="confirm_password" required>

            <i class="fa-solid fa-eye toggle-password"
               onclick="togglePassword('confirm_password', this)"></i>
        </div>

        <button type="submit">Reset Password</button>

    </form>

    <?php endif; ?>

</div>

<script>

// Toggle password
function togglePassword(id, icon) {
    let input = document.getElementById(id);

    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

// Strength checker
function checkStrength() {
    let pass = document.getElementById("new_password").value;
    let text = document.getElementById("strengthText");

    if (pass.length === 0) {
        text.innerHTML = "";
    }
    else if (pass.length < 6) {
        text.innerHTML = "Weak ❌";
        text.style.color = "red";
    }
    else if (pass.length < 10) {
        text.innerHTML = "Medium ⚠️";
        text.style.color = "orange";
    }
    else {
        text.innerHTML = "Strong ✅";
        text.style.color = "lightgreen";
    }
}

</script>

</body>
</html>

<?php $conn->close(); ?>