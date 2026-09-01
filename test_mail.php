<?php
$to = "your_email@gmail.com"; // change this to your email
$subject = "Test Email";
$message = "This is a test email from PHP sendmail.";
$headers = "From: lagratapreciousnichole@gmail.com";

if(mail($to, $subject, $message, $headers)) {
    echo "Email sent successfully!";
} else {
    echo "Email failed to send.";
}
?>