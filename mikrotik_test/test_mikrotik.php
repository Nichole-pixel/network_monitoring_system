<?php
require_once __DIR__ . '/routeros_api.class.php';

$API = new RouterosAPI();

if ($API->connect('192.168.88.1', 'phpapi', '123456', 8728)) {
    echo "Connected to MikroTik!";
    $API->disconnect();
} else {
    echo "Failed to connect to MikroTik.";
}
?>