<?php
/**
 * Plugin Name: Pro Clean Quotation System
 * Plugin URI: https://wecleaning.com
 * Description: Automated quotation and booking system for façade and roof cleaning services. Integrates with MotoPress Appointment and WooCommerce.
 * Version: 1.3.0
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
define('PCQ_VERSION', '1.3.0');
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

// Add Settings link to plugin action links
add_filter('plugin_action_links_' . PCQ_PLUGIN_BASENAME, 'pcq_add_plugin_action_links');
function pcq_add_plugin_action_links($links) {
    $settings_link = sprintf(
        '<a href="%s">%s</a>',
        admin_url('admin.php?page=pcq-settings'),
        __('Settings', 'pro-clean-quotation')
    );
    array_unshift($links, $settings_link);
    return $links;
}

// Initialize plugin
add_action('plugins_loaded', 'pcq_init_plugin');
function pcq_init_plugin() {
    // Load text domain
    load_plugin_textdomain('pro-clean-quotation', false, dirname(PCQ_PLUGIN_BASENAME) . '/languages');
    
    // Run upgrade routines if needed
    pcq_maybe_upgrade();
    
    // Initialize main plugin class
    ProClean\Quotation\Plugin::getInstance();
    
    // Initialize Plugin Updater
    ProClean\Quotation\Admin\PluginUpdater::getInstance();
}

// Schedule backup cleanup action
add_action('pcq_cleanup_backup', 'pcq_cleanup_old_backup');
function pcq_cleanup_old_backup($backup_dir) {
    global $wp_filesystem;
    
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    
    if (!WP_Filesystem()) {
        return;
    }
    
    if ($wp_filesystem->is_dir($backup_dir)) {
        $wp_filesystem->delete($backup_dir, true);
    }
}

/**
 * Check if upgrade routines need to run and execute them
 * This ensures seamless updates from previous versions
 */
function pcq_maybe_upgrade() {
    $installed_version = get_option('pcq_version', '0.0.0');
    
    // If versions match, no upgrade needed
    if (version_compare($installed_version, PCQ_VERSION, '>=')) {
        return;
    }
    
    // Upgrade from versions before1.2.0
    if (version_compare($installed_version, '1.2.0', '<')) {
        pcq_upgrade_to_120();
    }
    
    // Upgrade to add new pricing fields (base_rate, rate_per_sqm)
    if (version_compare($installed_version, '1.3.0', '<')) {
        pcq_upgrade_to_add_pricing_fields();
    }
    
    // Update stored version
    update_option('pcq_version', PCQ_VERSION);
}

/**
 * Upgrade routine to add base_rate and rate_per_sqm columns
 * to existing services table
 */
function pcq_upgrade_to_add_pricing_fields() {
    global $wpdb;
    
    $services_table = $wpdb->prefix . 'pq_services';
    
    // Check if base_rate column exists
    $base_rate_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = %s AND table_name = %s AND column_name = %s",
            DB_NAME,
            $services_table,
            'base_rate'
        )
    );
    
    // Add base_rate column if it doesn't exist
    if (!$base_rate_exists) {
        $wpdb->query(
            "ALTER TABLE $services_table ADD COLUMN base_rate DECIMAL(10,2) DEFAULT 20.00 AFTER price"
        );
    }
    
    // Check if rate_per_sqm column exists
    $rate_per_sqm_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = %s AND table_name = %s AND column_name = %s",
            DB_NAME,
            $services_table,
            'rate_per_sqm'
        )
    );
    
    // Add rate_per_sqm column if it doesn't exist
    if (!$rate_per_sqm_exists) {
        $wpdb->query(
            "ALTER TABLE $services_table ADD COLUMN rate_per_sqm DECIMAL(10,2) DEFAULT 20.00 AFTER base_rate"
        );
    }
    
    // Update existing services with default values if they have NULL or 0 values
    $wpdb->query(
        "UPDATE $services_table SET base_rate = 20.00 WHERE base_rate IS NULL OR base_rate = 0"
    );
    $wpdb->query(
        "UPDATE $services_table SET rate_per_sqm = 20.00 WHERE rate_per_sqm IS NULL OR rate_per_sqm = 0"
    );
    
    // Log the upgrade
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('PCQ Plugin: Upgraded to add base_rate and rate_per_sqm columns to services table');
    }
}

/**
 * Upgrade routines for version1.2.0
 * - Fixes service status persistence bug
 * - Adds service name resolution for quotes
 */
function pcq_upgrade_to_120() {
    global $wpdb;
    
    // Fix any services that have status set to '0' or empty due to the bug
    // This resets them to 'active' as a safe default
    $services_table = $wpdb->prefix . 'pq_services';
    $wpdb->query(
        "UPDATE {$services_table} SET status = 'active' WHERE status = '0' OR status = '' OR status IS NULL"
    );
    
    // Ensure all services have valid status values
    $wpdb->query(
        "UPDATE {$services_table} SET status = 'active' WHERE status NOT IN ('active', 'inactive')"
    );
    
    // Log the upgrade
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('PCQ Plugin: Upgraded to version1.2.0 - Fixed service status values');
    }
}

// Activation hook
register_activation_hook(__FILE__, 'pcq_activate_plugin');
function pcq_activate_plugin() {
    // Create database tables
    require_once PCQ_PLUGIN_DIR . 'includes/Database/Installer.php';
    ProClean\Quotation\Database\Installer::createTables();
    
    // Migrate existing employee assignments to team system
    ProClean\Quotation\Database\Installer::migrateEmployeeAssignments();
    
    // Create required pages (booking page)
    ProClean\Quotation\Database\Installer::createRequiredPages();
    
    // Set default options
    ProClean\Quotation\Admin\Settings::setDefaults();
    
    // Set plugin version (for upgrade tracking)
    update_option('pcq_version', PCQ_VERSION);
    
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
    
    // Delete plugin-created pages
    $page_options = [
        'pcq_booking_page_id',
        'pcq_confirmation_page_id'
    ];
    
    foreach ($page_options as $option) {
        $page_id = get_option($option);
        if ($page_id) {
            wp_delete_post($page_id, true); // Force delete, bypass trash
            delete_option($option);
        }
    }
    
    // Clean up uploaded files (PDFs, avatars, etc.)
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/pro-clean-quotation';
    
    if (is_dir($plugin_upload_dir)) {
        // Recursively delete the directory
        pcq_recursive_rmdir($plugin_upload_dir);
    }
}

/**
 * Recursively remove a directory and its contents
 * 
 * @param string $dir Directory path
 */
function pcq_recursive_rmdir($dir) {
    if (!is_dir($dir)) {
        return;
    }
    
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object !== '.' && $object !== '..') {
            $path = $dir . '/' . $object;
            if (is_dir($path)) {
                pcq_recursive_rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
    rmdir($dir);
}