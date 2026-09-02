<?php
require_once __DIR__ . '/routeros_api.class.php';

$API = new RouterosAPI();

if ($API->connect('192.168.88.1', 'phpapi', '123456', 8728)) {
    $API->write('/user/print');
    $users = $API->read();

    echo '<pre>';
    print_r($users);
    echo '</pre>';

    $API->disconnect();
} else {
    echo 'Failed to connect to MikroTik.';
}
?>