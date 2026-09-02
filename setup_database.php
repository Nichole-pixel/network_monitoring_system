<?php
require 'db.php';

$queries = [
    "CREATE TABLE IF NOT EXISTS account (
        acc_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100),
        role VARCHAR(20) DEFAULT 'admin',
        acc_status VARCHAR(20) DEFAULT 'active'
    )",
    "CREATE TABLE IF NOT EXISTS client (
        client_id INT AUTO_INCREMENT PRIMARY KEY,
        mac_address VARCHAR(50) NOT NULL,
        pc_no VARCHAR(50) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS usage_log (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        ave_download VARCHAR(50),
        ave_upload VARCHAR(50),
        bw_status VARCHAR(50),
        log_datetime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS system_log (
        log_id INT AUTO_INCREMENT PRIMARY KEY,
        acc_id INT NOT NULL,
        transaction VARCHAR(255) NOT NULL,
        date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS policy (
        policy_id INT AUTO_INCREMENT PRIMARY KEY,
        policy_name VARCHAR(100) NOT NULL,
        website VARCHAR(255) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS rules (
        rule_no INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        policy_id INT NOT NULL,
        rule_status VARCHAR(50) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS policy_domains (
        id INT AUTO_INCREMENT PRIMARY KEY,
        policy_id INT NOT NULL,
        domain VARCHAR(255) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS bandwidth (
        bandwidth_id INT AUTO_INCREMENT PRIMARY KEY,
        max_download VARCHAR(50) NOT NULL,
        max_upload VARCHAR(50) NOT NULL,
        bw_status VARCHAR(50) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS bandwidth_rules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        bandwidth_id INT NOT NULL,
        bwrule_status VARCHAR(50) NOT NULL
    )",
    "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL
    )"
];

foreach ($queries as $query) {
    if ($conn->query($query) === TRUE) {
        echo "Table created successfully or already exists.<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Create default admin if not exists
$check_admin = $conn->query("SELECT * FROM account WHERE username = 'admin'");
if ($check_admin->num_rows == 0) {
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $conn->query("INSERT INTO account (username, password, email, role, acc_status) VALUES ('admin', '$password', 'admin@example.com', 'admin', 'active')");
    echo "Default admin account created: username: admin / password: admin123<br>";
}

echo "<h3>Database setup complete! You can now use the application.</h3>";
?>
