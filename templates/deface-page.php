<?php
/** @var string $slug @var int $hours @var string $cssUrl @var string $jsUrl @var string $pluginVer */
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>HACKED BY HERE IS LEO // MOSCOW</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= esc_url($cssUrl) ?>">
</head>
<body>
<canvas id="moscow-matrix" aria-hidden="true"></canvas>

<main class="terminal" role="main">
    <header class="terminal__bar">
        <span class="dot dot--red"></span>
        <span class="dot dot--yellow"></span>
        <span class="dot dot--green"></span>
        <span class="terminal__title">root@moscow — /var/www/html — v<?= esc_html($pluginVer) ?></span>
    </header>

    <section class="terminal__body">
        <p class="prompt"><span class="prompt__user">root@moscow</span><span class="prompt__sep">:</span><span class="prompt__dir">~</span><span class="prompt__sign">$</span> whoami</p>
        <p class="prompt__output">&gt; here_is_leo</p>

        <h1 class="title glitch" data-text="HACKED BY HERE IS LEO">HACKED BY HERE IS LEO</h1>
        <p class="subtitle"><span>//</span> CYBER ACTIVIST <span>//</span> MOSCOW <span>//</span></p>

        <div class="alert">
            <div class="alert__line"><span class="alert__badge">WARNING</span> your system is <strong>compromised</strong>.</div>
            <div class="alert__line">harden your defenses. patch your systems. trust nothing.</div>
        </div>

        <div class="timer">
            <div class="timer__label">COUNTDOWN TO DESTRUCTION</div>
            <div class="timer__value" id="timer-value"><?= sprintf('%02d:00:00', $hours) ?></div>
            <div class="timer__bar"><div class="timer__fill" id="timer-fill"></div></div>
        </div>

        <div class="fact" id="fact" role="status" aria-live="polite">> press START to begin sequence...</div>

        <button class="btn" id="start-btn" type="button">
            <span class="btn__icon">▶</span> START SEQUENCE
        </button>

        <footer class="credit">
            <span>//</span> DEFACED <span>//</span> HERE IS LEO <span>//</span> <?= esc_html(date('Y')) ?>
        </footer>
    </section>
</main>

<script>
window.MOSCOW_DEFACE = {
    hours: <?= (int) $hours ?>,
    facts: <?= wp_json_encode([
        '123456 is not a password — it is a public statement.',
        'A strong password written on paper is still weak.',
        'Hackers rarely break in — users hand them the keys.',
        'Free Wi-Fi is never truly free.',
        'A misconfigured firewall is decoration.',
        'Antivirus is not a substitute for common sense.',
        'Skipping updates is an invitation to old bugs.',
        'An unused open port is an extra door.',
        'No backups means total faith in a single disk.',
        'Suspicious links usually have a good reason to be suspicious.',
        'Social engineering is often easier than technical hacking.',
        'One password for everything = one key for every house.',
        'A VPN is not a magic wand.',
        'Incognito does not make you invisible.',
        'A firewall cannot stop user mistakes.',
        'Every extra app is one more chance of a vulnerability.',
        'A server without logs is a camera without film.',
        'Security is not configured once — it is verified continuously.',
        'The strongest system still fails with a leaked password.',
        '"It was just a crack file" — the start of many sad stories.',
        'Over-privileged apps mean over-trusted apps.',
        'Short passwords make a hacker\'s day easier.',
        'Small pieces of personal data become dangerous together.',
        'Network security without monitoring is half done.',
        'The biggest vulnerability in some systems is sitting behind the keyboard.',
        'Tip: use a unique password for every important account.',
        'Tip: enable two-factor authentication.',
        'Tip: keep the OS and applications updated.',
        'Tip: do not click suspicious links.',
        'Tip: never run unknown files.',
        'Tip: never share your password.',
        'Tip: use a reputable password manager.',
        'Tip: add extra protection to your primary email.',
        'Tip: back up important data.',
        'Tip: change the router\'s default password.',
        'Tip: review app permissions.',
        'Tip: disable unused ports and services.',
        'Tip: review suspicious sign-ins on your accounts.',
        'Tip: never enter sensitive data on public networks.',
        'Tip: verify sender and destination before every click.',
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
};
</script>
<script src="<?= esc_url($jsUrl) ?>"></script>
</body>
</html>