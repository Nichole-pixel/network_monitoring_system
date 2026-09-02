<?php
require_once __DIR__ . '/includes/config.php';

function normalizeDomain($input) {
    $input = trim(strtolower($input));

    if ($input === '') {
        return '';
    }

    $input = preg_replace('/\s+/', '', $input);

    if (!preg_match('/^[a-z][a-z0-9+\-.]*:\/\//i', $input)) {
        $input = 'http://' . $input;
    }

    $host = parse_url($input, PHP_URL_HOST);

    if (!$host) {
        return '';
    }

    $host = preg_replace('/:\d+$/', '', $host);
    $host = rtrim($host, '.');

    if (!filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
        return '';
    }

    return $host;
}

function isBlocked($conn, $domain) {
    $host = normalizeDomain($domain);

    if ($host === '') {
        return false;
    }

    $result = $conn->query("SELECT website FROM policy");

    if (!$result) {
        return false;
    }

    while ($row = $result->fetch_assoc()) {
        $blocked = normalizeDomain($row['website']);

        if ($blocked === '') {
            continue;
        }

        if ($host === $blocked || str_ends_with($host, '.' . $blocked)) {
            return true;
        }
    }

    return false;
}

/* ===== TEST ===== */
$test = $_GET['site'] ?? '';

if ($test != '') {
    if (isBlocked($conn, $test)) {
        echo "BLOCKED";
    } else {
        echo "ALLOWED";
    }
}
?>