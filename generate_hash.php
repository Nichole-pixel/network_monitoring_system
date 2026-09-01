<?php
// Password you want to hash
$password = 'admin123';  // Change this to the password you want

// Generate the hashed password
echo password_hash($password, PASSWORD_DEFAULT);
?>