<?php
/**
 * Plugin Name: Pro Clean Quotation System
 * Plugin URI: https://wecleaning.com
 * Description: Automated quotation and booking system for façade and roof cleaning services. Integrates with MotoPress Appointment and WooCommerce.
 * Version: 1.0.5
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Pro Clean Development Team
 * License: GPL v2 or later
 * Text Domain: pro-clean-quotation
 * Domain Path: /languages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('PCQ_VERSION', '1.0.5');
define('PCQ_PLUGIN_FILE', __FILE__);
define('PCQ_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PCQ_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PCQ_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Minimum requirements check
if (version_compare(PHP_VERSION, '8.0', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo __('Pro Clean Quotation System requires PHP 8.0 or higher. Please update your PHP version.', 'pro-clean-quotation');
        echo '</p></div>';
    });
    return;
}

// Check for recommended plugins (not required)
add_action('admin_init', 'pcq_check_recommended_plugins');
function pcq_check_recommended_plugins() {
    $recommended_plugins = [
        'motopress-appointment-lite/motopress-appointment.php' => [
            'name' => 'MotoPress Appointment Lite',
            'description' => 'Enhanced booking management and calendar integration'
        ],
        'woocommerce/woocommerce.php' => [
            'name' => 'WooCommerce',
            'description' => 'Advanced payment processing for deposits and online payments'
        ]
    ];
    
    $missing_plugins = [];
    foreach ($recommended_plugins as $plugin_file => $plugin_info) {
        if (!is_plugin_active($plugin_file)) {
            $missing_plugins[] = $plugin_info;
        }
    }
    
    if (!empty($missing_plugins)) {
        add_action('admin_notices', function() use ($missing_plugins) {
            echo '<div class="notice notice-info is-dismissible"><p>';
            echo '<strong>' . __('Pro Clean Quotation System - Recommended Plugins:', 'pro-clean-quotation') . '</strong><br>';
            echo __('The following plugins are recommended for enhanced functionality but not required:', 'pro-clean-quotation') . '<br>';
            foreach ($missing_plugins as $plugin) {
                echo '• <strong>' . $plugin['name'] . '</strong>: ' . $plugin['description'] . '<br>';
            }
            echo '</p></div>';
        });
    }
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'ProClean\\Quotation\\';
    $base_dir = PCQ_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Initialize plugin
add_action('plugins_loaded', 'pcq_init_plugin');
function pcq_init_plugin() {
    // Load text domain
    load_plugin_textdomain('pro-clean-quotation', false, dirname(PCQ_PLUGIN_BASENAME) . '/languages');
    
    // Initialize main plugin class
    ProClean\Quotation\Plugin::getInstance();
}

// Activation hook
register_activation_hook(__FILE__, 'pcq_activate_plugin');
function pcq_activate_plugin() {
    // Create database tables
    require_once PCQ_PLUGIN_DIR . 'includes/Database/Installer.php';
    ProClean\Quotation\Database\Installer::createTables();
    
    // Migrate existing employee assignments to team system
    ProClean\Quotation\Database\Installer::migrateEmployeeAssignments();
    
    // Set default options
    ProClean\Quotation\Admin\Settings::setDefaults();
    
    // Schedule PDF cleanup cron job
    if (!wp_next_scheduled('pcq_cleanup_temp_pdfs')) {
        wp_schedule_event(time(), 'daily', 'pcq_cleanup_temp_pdfs');
    }
    
    // Schedule booking reminder cron job
    if (!wp_next_scheduled('pcq_send_booking_reminders')) {
        wp_schedule_event(time(), 'hourly', 'pcq_send_booking_reminders');
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'pcq_deactivate_plugin');
function pcq_deactivate_plugin() {
    // Clear scheduled cron jobs
    wp_clear_scheduled_hook('pcq_cleanup_temp_pdfs');
    wp_clear_scheduled_hook('pcq_send_booking_reminders');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

// Uninstall hook
register_uninstall_hook(__FILE__, 'pcq_uninstall_plugin');
function pcq_uninstall_plugin() {
    // Remove database tables and options
    require_once PCQ_PLUGIN_DIR . 'includes/Database/Installer.php';
    ProClean\Quotation\Database\Installer::removeTables();
    ProClean\Quotation\Admin\Settings::removeOptions();
}