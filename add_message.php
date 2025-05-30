<?php
date_default_timezone_set('Europe/Warsaw');

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$now = time();

$nick = $_COOKIE['blog_nick'] 
      ?? trim($_POST['nick'] ?? '');

if (!isset($_COOKIE['blog_nick'])) {
    if (strlen($nick) >= 2 && strlen($nick) <= 20) {
        setcookie('blog_nick', $nick, time() + 30*24*3600, '/');
        $_COOKIE['blog_nick'] = $nick;
    } else {
        header("Location: index.php?msg_error=length#leave-message");
        exit;
    }
}

$message = trim($_POST['message'] ?? '');
if (strlen($message) < 3 || strlen($message) > 100) {
    header("Location: index.php?msg_error=length#leave-message");
    exit;
}

chdir(__DIR__);
$cdFile   = __DIR__ . '/messages/cooldowns.json';
$nickFile = __DIR__ . '/messages/ip_nicks.json';
$msgFile  = __DIR__ . '/messages/messages.json';

if (!is_dir(__DIR__ . '/messages')) {
    mkdir(__DIR__ . '/messages', 0755, true);
}

if (!file_exists($cdFile)) file_put_contents($cdFile, '{}');
$cooldowns = json_decode(file_get_contents($cdFile), true) ?: [];
if (isset($cooldowns[$ip]) && ($now - $cooldowns[$ip] < 5)) {
    header("Location: index.php?msg_error=delay#leave-message");
    exit;
}
$cooldowns[$ip] = $now;
file_put_contents($cdFile, json_encode($cooldowns, JSON_PRETTY_PRINT));

if (!file_exists($nickFile)) file_put_contents($nickFile, '{}');
$ipNicks = json_decode(file_get_contents($nickFile), true) ?: [];
if (isset($ipNicks[$ip]) && $ipNicks[$ip] !== $nick) {
    header("Location: index.php?msg_error=delay#leave-message");
    exit;
}
$ipNicks[$ip] = $nick;
file_put_contents($nickFile, json_encode($ipNicks, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if (!file_exists($msgFile)) file_put_contents($msgFile, '[]');
$messages = json_decode(file_get_contents($msgFile), true) ?: [];
$messages[] = [
    'nick' => htmlspecialchars($nick, ENT_QUOTES, 'UTF-8'),
    'text' => htmlspecialchars($message, ENT_QUOTES, 'UTF-8'),
    'date' => date('Y-m-d H:i:s'),
];
file_put_contents($msgFile, json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header("Location: index.php#leave-message");
exit;