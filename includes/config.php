<?php
// Define the absolute base path for the NMS project
define('BASE_PATH', realpath(__DIR__ . '/../') . '/');

// Define the base URL for HTML assets (assuming server root is NMS folder)
define('BASE_URL', '/');

// Use this config file to safely include other core files
require_once BASE_PATH . 'includes/db.php';
require_once BASE_PATH . 'includes/functions.php';
?>
