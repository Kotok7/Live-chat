<?php
date_default_timezone_set('Europe/Warsaw');
$ipsFile = __DIR__ . '/ips.json';

if (file_exists($ipsFile)) {
    $ips = json_decode(file_get_contents($ipsFile), true);
    if (!is_array($ips)) {
        $ips = [];
    }
} else {
    $ips = [];
}

$visitorIp = $_SERVER['REMOTE_ADDR'];

if (!in_array($visitorIp, $ips, true)) {
    $ips[] = $visitorIp;
    file_put_contents(
        $ipsFile,
        json_encode($ips, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );
}

$uniqueVisitors = count($ips);

$city = 'Krasnik';
$weatherJson = @file_get_contents("https://wttr.in/{$city}?format=j1");
if ($weatherJson !== false) {
    $w = json_decode($weatherJson, true);
    $temp = $w['current_condition'][0]['temp_C'];
    $desc = $w['current_condition'][0]['weatherDesc'][0]['value'];
} else {
    $temp = null;
    $desc = '';
}

$localTime = date('H:i');
$storedNick = $_COOKIE['blog_nick'] ?? '';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Kotokk - Live chat</title>
  <meta name="description" content="Chat with people from around the world, share jokes, make friends, and enjoy real-time conversations.">
  <link rel="icon" href="photos/website-icon.png" type="image/png">
  <link rel="stylesheet" href="styles.css">
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" defer></script>
  <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
<header>
  <div class="header-content">
    <div class="visitor-counter">
      <p>Unique visitors counter:
      <span class="number"><?= htmlspecialchars($uniqueVisitors, ENT_QUOTES) ?></span></p>
    </div><br>
    <h1>Live chat</h1><small>BETA</small>
    <p>Created by @kotokk_dev (discord)</p>
  </div>
  <div id="codeParticles" class="header-animation"></div>
</header>
<main>
  <section class="leave-message" id="leave-message">
    <?php if (!empty($_GET['msg_error'])): ?>
      <p class="error">
        <?= $_GET['msg_error'] === 'length'
            ? 'The message must be between 3 and 100 characters long.'
            : 'You must wait 10 minutes before sending another message and wait for the verification to complete.' ?>
      </p>
    <?php endif; ?>
    <form action="add_message.php" method="post">
      <?php if ($storedNick): ?>
        <p><strong>Your nick:</strong> <?= htmlspecialchars($storedNick, ENT_QUOTES) ?></p>
      <?php else: ?>
        <label>Your nick:
          <input type="text" name="nick" required minlength="2" maxlength="20" placeholder="Enter your nickname here">
        </label>
      <?php endif; ?>
      <textarea name="message" required minlength="3" maxlength="100" placeholder="Type your message here"></textarea>
      <div class="cf-turnstile" data-sitekey="0x4AAAAAABd4n1oTvz_2DaHU"></div>
      <button type="submit">Add</button>
    </form>
    <div class="messages-wrapper">
      <?php
        $msgs2 = json_decode(file_get_contents(__DIR__ . '/messages/messages.json'), true) ?: [];
        foreach (array_reverse($msgs2) as $m) {
          echo '<blockquote><strong>'
               . htmlspecialchars($m['nick'], ENT_QUOTES)
               . '</strong>: '
               . nl2br(htmlspecialchars($m['text'], ENT_QUOTES))
               . '<br><small>'
               . htmlspecialchars($m['date'], ENT_QUOTES)
               . '</small></blockquote>';
        }
      ?>
    </div>
  </section>
</main>
<footer>
  <p>&copy; 2025 kotokk.dev<br>
    My main site: <a href="https://kotokk.dev" target="_blank">kotokk.dev</a><br>
  </p>
</footer>
</body>
</html>