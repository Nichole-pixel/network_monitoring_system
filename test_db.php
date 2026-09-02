<?php
require 'db.php';
$res = $conn->query('SELECT * FROM client');
echo "CLIENTS:\n";
while($row = $res->fetch_assoc()) print_r($row);

$res = $conn->query('SELECT * FROM policy');
echo "\nPOLICIES:\n";
while($row = $res->fetch_assoc()) print_r($row);

$res = $conn->query('SELECT * FROM rules');
echo "\nRULES:\n";
while($row = $res->fetch_assoc()) print_r($row);
?>
