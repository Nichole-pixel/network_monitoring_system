<?php
include('../db.php');

header('Content-Type: application/json');

$mac = trim($_GET['mac'] ?? '');

if (empty($mac)) {
    echo json_encode(["status" => "error", "message" => "MAC address required"]);
    exit();
}

// Update last_seen timestamp to show client is online
$stmt = $conn->prepare("UPDATE client SET last_seen = NOW() WHERE mac_address = ?");
$stmt->bind_param("s", $mac);
$stmt->execute();

// Check if client exists
$checkClient = $conn->prepare("SELECT client_id FROM client WHERE mac_address = ? LIMIT 1");
$checkClient->bind_param("s", $mac);
$checkClient->execute();
$clientResult = $checkClient->get_result();

if ($clientResult->num_rows === 0) {
    // Return empty list if client doesn't exist in DB
    echo json_encode(["status" => "success", "blocked_domains" => []]);
    exit();
}

$clientRow = $clientResult->fetch_assoc();
$client_id = $clientRow['client_id'];

// Fetch all blocked domains for this client
$domains = [];
$ruleStmt = $conn->prepare("
    SELECT p.website, pd.domain
    FROM rules r
    JOIN policy p ON r.policy_id = p.policy_id
    LEFT JOIN policy_domains pd ON p.policy_id = pd.policy_id
    WHERE r.client_id = ? AND r.rule_status = 'Active'
");
$ruleStmt->bind_param("i", $client_id);
$ruleStmt->execute();
$ruleResult = $ruleStmt->get_result();

while ($row = $ruleResult->fetch_assoc()) {
    if (!empty($row['website']) && !in_array($row['website'], $domains)) {
        $domains[] = $row['website'];
    }
    if (!empty($row['domain']) && !in_array($row['domain'], $domains)) {
        $domains[] = $row['domain'];
    }
}

echo json_encode(["status" => "success", "blocked_domains" => $domains]);
?>
