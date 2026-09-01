<?php 

// =============================
// EXISTING FUNCTION (UNCHANGED)
// =============================
function addUsageLog($conn, $client_id, $ave_download, $ave_upload, $bw_status) {
    $stmt = $conn->prepare("
        INSERT INTO usage_log (client_id, ave_download, ave_upload, bw_status, log_datetime)
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->bind_param("idds", $client_id, $ave_download, $ave_upload, $bw_status);

    return $stmt->execute();
}


// =============================
// CLEAN WEBSITE INPUT
// =============================
function cleanWebsite($url) {
    $url = strtolower(trim($url));

    // remove http:// or https://
    $url = preg_replace('#^https?://#', '', $url);

    // remove www.
    $url = preg_replace('#^www\.#', '', $url);

    // remove anything after /
    $url = explode('/', $url)[0];

    return $url;
}


// =============================
// CHECK IF WEBSITE IS BLOCKED (SUBDOMAIN SUPPORT)
// =============================
function isBlocked($conn, $domain) {
    $domain = strtolower(trim($domain));

    $stmt = $conn->prepare("
        SELECT * FROM policy 
        WHERE ? = website 
        OR ? LIKE CONCAT('%.', website)
    ");

    $stmt->bind_param("ss", $domain, $domain);
    $stmt->execute();
    $result = $stmt->get_result();

    return ($result->num_rows > 0);
}


// ==================================================
// 🔥 NEW: BANDWIDTH → BANDWIDTH_RULES → CLIENT
// ==================================================


// =============================
// ASSIGN BANDWIDTH TO CLIENT
// =============================
function assignBandwidthToClient($conn, $client_id, $bandwidth_id, $status = "Active") {
    $stmt = $conn->prepare("
        INSERT INTO bandwidth_rules (client_id, bandwidth_id, bwrule_status)
        VALUES (?, ?, ?)
    ");

    $stmt->bind_param("iis", $client_id, $bandwidth_id, $status);

    return $stmt->execute();
}


// =============================
// GET CLIENT BANDWIDTH
// =============================
function getClientBandwidth($conn, $client_id) {
    $stmt = $conn->prepare("
        SELECT b.max_download, b.max_upload, b.bw_status
        FROM bandwidth b
        JOIN bandwidth_rules br ON b.bandwidth_id = br.bandwidth_id
        WHERE br.client_id = ? AND br.bwrule_status = 'Active'
        LIMIT 1
    ");

    $stmt->bind_param("i", $client_id);
    $stmt->execute();
    $result = $stmt->get_result();

    return $result->fetch_assoc(); // returns array or null
}


// =============================
// SHOW CLIENT BANDWIDTH STATUS
// =============================
function checkClientBandwidthStatus($conn, $client_id) {
    $bw = getClientBandwidth($conn, $client_id);

    if (!$bw) {
        return "No Limit";
    }

    if ($bw['bw_status'] == 'Inactive') {
        return "Disabled";
    }

    return "Active (" . $bw['max_download'] . "↓ / " . $bw['max_upload'] . "↑ Mbps)";
}


// =============================
// UPDATE BANDWIDTH RULE STATUS
// =============================
function updateBandwidthRuleStatus($conn, $bwrule_id, $status) {
    $stmt = $conn->prepare("
        UPDATE bandwidth_rules 
        SET bwrule_status = ?
        WHERE bwrule_id = ?
    ");

    $stmt->bind_param("si", $status, $bwrule_id);

    return $stmt->execute();
}

?>