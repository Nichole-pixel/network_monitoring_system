<?php
include('db.php');  // Include your database connection file

// Get the most recent 10 entries from Usage_Log
$sql = "SELECT * FROM Usage_Log ORDER BY log_datetime DESC LIMIT 10";
$result = $conn->query($sql);

$data = [];
if ($result->num_rows > 0) {
    // Fetch each row and prepare data for display
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'client_id' => $row['client_id'],
            'avg_download' => $row['ave_download'],
            'avg_upload' => $row['ave_upload'],
            'bw_status' => $row['bw_status'],
            'log_datetime' => $row['log_datetime']
        ];
    }
    echo json_encode($data);  // Return the data as a JSON response
} else {
    echo json_encode([]);  // Return an empty array if no data found
}

$conn->close();  // Close the database connection
?>