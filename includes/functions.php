<?php 
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


?>