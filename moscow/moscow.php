<?php
/**
 * Plugin Name:       Moscow
 * Plugin URI:        https://github.com/here-is-leo/moscow
 * Description:       Advanced WordPress security research plugin.
 * Version:           4.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            Here Is Leo
 * Author URI:        https://github.com/here-is-leo
 * License:           GPL-2.0-or-later
 * Text Domain:       moscow
 *
 * @package Moscow
 */

declare(strict_types=1);

namespace Moscow;

if (!defined('ABSPATH')) {
    exit;
}

define('MOSCOW_VERSION', '4.0.0');
define('MOSCOW_FILE', __FILE__);
define('MOSCOW_DIR', plugin_dir_path(__FILE__));
define('MOSCOW_URL', plugin_dir_url(__FILE__));
define('MOSCOW_BASENAME', plugin_basename(__FILE__));

// PSR-4 Autoloader
spl_autoload_register(static function (string $class): void {
    $prefix = 'Moscow\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = MOSCOW_DIR . 'includes/' . str_replace('\\', '/', $relative) . '.php';
    if (is_readable($file)) {
        require_once $file;
    }
});

register_activation_hook(__FILE__, [Plugin::class, 'on_activate']);
register_deactivation_hook(__FILE__, [Plugin::class, 'on_deactivate']);

add_action('plugins_loaded', static function (): void {
    Plugin::instance()->boot();
}, 5);