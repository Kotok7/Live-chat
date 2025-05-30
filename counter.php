<?php

$ipListFile = __DIR__ . '/ips.json';
$ip         = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

if (file_exists($ipListFile)) {
    $ipList = json_decode(file_get_contents($ipListFile), true) ?: [];
} else {
    $ipList = [];
}

if (!in_array($ip, $ipList)) {
    $ipList[] = $ip;
    file_put_contents(
        $ipListFile,
        json_encode($ipList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        LOCK_EX
    );
}

$uniqueVisitors = count($ipList);