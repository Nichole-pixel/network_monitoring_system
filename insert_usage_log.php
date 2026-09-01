<?php
include('db.php');

// Sample data for bandwidth usage
$client_id = 1;  // Example client ID
$ave_download = 20.5;  // Example download speed
$ave_upload = 10.5;  // Example upload speed
$bw_status = 'active';

// Insert into Usage Log
$sql = "INSERT INTO Usage_Log (client_id, ave_download, ave_upload, bw_status) 
        VALUES ('$client_id', '$ave_download', '$ave_upload', '$bw_status')";

if ($conn->query($sql) === TRUE) {
    echo "New usage log created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>