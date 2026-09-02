<?php
session_start();
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../includes/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    if (empty($username) || empty($email)) {
        $error_message = "Please enter both username and email.";
    } else {

        $stmt = $conn->prepare("SELECT acc_id, email, username FROM account WHERE username = ? AND email = ? LIMIT 1");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) {
            $error_message = "Username and email do not match any account.";
        } else {
            $reset_token = bin2hex(random_bytes(16));
            $expiry_time = date('Y-m-d H:i:s', strtotime('+10 minute')); 

            $stmt = $conn->prepare("
                 INSERT INTO password_reset_tokens (user_id, token, created_at, expires_at)
                    VALUES (?, ?, NOW(), ?)
            ");
            $stmt->bind_param("iss", $user['acc_id'], $reset_token, $expiry_time);
            $stmt->execute();

            // Insert new token
            $stmt = $conn->prepare("
                INSERT INTO password_reset_tokens (user_id, token, created_at, expires_at)
                VALUES (?, ?, NOW(), ?)
            ");
            $stmt->bind_param("iss", $user['acc_id'], $reset_token, $expiry_time);

            if ($stmt->execute()) {

                $base_url = "https://custodian-cloud-surging.ngrok-free.dev/NETWORK_MONITORING_SYSTEM";
                $reset_link = $base_url . "/password/reset_password.php?token=" . urlencode($reset_token);

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'lagratapreciousnichole@gmail.com';
                    $mail->Password = 'lfan vucx rpjm wrsk';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('tjesnetworkmonitoring@gmail.com', 'TJES Network Monitoring System');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Request';
                    $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto; color: #333;'>
                        <h2 style='text-align:center; color:#2c3e50;'>Password Reset Request</h2>
                        <p>Hello,</p>
                        <p>We received a request to reset your password. Click the button below to proceed:</p>
                        <div style='text-align:center; margin: 30px 0;'>
                            <a href='$reset_link' 
                             style='background-color:#007BFF; color:#ffffff; padding:12px 20px; 
                             text-decoration:none; border-radius:5px; display:inline-block; font-weight:bold;'>
                                Reset Your Password
                         </a>
                         </div>
                        <p>If the button above doesn’t work, copy and paste this link into your browser:</p>
                        <p style='word-break: break-all; color:#555;'>$reset_link</p>
                        <hr style='margin: 30px 0;'>
                        <p style='font-size: 13px; color: #777;'>
                            If you did not request a password reset, you can safely ignore this email. 
                        </p>
                    </div>
                    ";

                    $mail->send();
                    $success_message = "Reset link sent successfully. Check your email!";

                } catch (Exception $e) {
                    $error_message = "Mailer Error: " . $mail->ErrorInfo;
                }

            } else {
                $error_message = "Failed to generate reset token.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

/* BACKGROUND */
body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    background: url('../images/background.jpg') no-repeat center center fixed;
    background-size: cover;
    position: relative;
}

/* DARK OVERLAY */
body::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(0,0,0,0.35);
}

/* GLASS CONTAINER */
.container {
    width: 100%;
    max-width: 380px;
    padding: 30px;
    border-radius: 18px;

    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);

    border: 1px solid rgba(255,255,255,0.2);
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);

    position: relative;
    z-index: 1;

    text-align: center;
}

/* TITLE */
h2 {
    color: #fff;
    margin-bottom: 20px;
}

/* INPUT */
input {
    width: 100%;
    height: 44px;
    padding: 10px;
    margin: 10px 0;

    border-radius: 10px;
    border: none;
    outline: none;

    background: rgba(255,255,255,0.2);
    color: #fff;
}

input::placeholder {
    color: #ddd;
}

/* BUTTON */
button {
    width: 100%;
    height: 45px;

    border: none;
    border-radius: 10px;

    background: linear-gradient(135deg, #00c853, #64dd17);
    color: white;
    font-weight: bold;
    cursor: pointer;

    transition: 0.3s;
}

button:hover {
    transform: scale(1.03);
}

/* MESSAGES */
.error { color: #ff5252; margin-bottom: 10px; }
.success { color: #00e676; margin-bottom: 10px; }

.back-btn {
    display: block;
    width: 100%;
    height: 45px;
    line-height: 45px;
    margin-top: 10px;
    border-radius: 10px;
    text-decoration: none;
    text-align: center;
    font-weight: bold;
    background: rgba(255,255,255,0.2);
    color: #fff;
    transition: 0.3s;
}

.back-btn:hover {
    transform: scale(1.03);
    background: rgba(255,255,255,0.3);
}

</style>
</head>

<body>

<div class="container">

<h2>Forgot Password</h2>

<?php if ($error_message): ?>
    <p class="error"><?= $error_message ?></p>
<?php endif; ?>

<?php if ($success_message): ?>
    <p class="success"><?= $success_message ?></p>
<?php endif; ?>

<form method="POST">

    <input type="text" name="username" placeholder="Enter Username" required>
    <input type="email" name="email" placeholder="Enter Email" required>

    <button type="submit">Send Reset Link</button>
     <a href="../login.php" class="back-btn">Back to Login</a>


</form>

</div>

</body>
</html>

<?php $conn->close(); ?>