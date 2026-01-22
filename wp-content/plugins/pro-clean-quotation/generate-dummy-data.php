#!/usr/bin/env php
<?php
/**
 * Pro Clean Quotation - Dummy Data Generator CLI Script
 * 
 * Standalone script for generating test data in development environments.
 * Run from command line: php generate-dummy-data.php [command]
 * 
 * Commands:
 *   generate  - Generate dummy data (default)
 *   clear     - Clear all dummy data
 *   recreate  - Clear and regenerate all data
 *   status    - Show current database record counts
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from the command line.');
}

// Display header
echo "\n";
echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║     Pro Clean Quotation - Dummy Data Generator CLI          ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// Find and load WordPress
$wp_load_path = find_wp_load();

if (!$wp_load_path) {
    echo "❌ Error: Could not locate wp-load.php\n";
    echo "   Make sure this script is in the plugin directory within a WordPress installation.\n\n";
    exit(1);
}

echo "📂 Loading WordPress from: " . dirname($wp_load_path) . "\n";

// Load WordPress
define('WP_USE_THEMES', false);
require_once($wp_load_path);

// Check if plugin is active
if (!defined('PCQ_PLUGIN_DIR')) {
    echo "❌ Error: Pro Clean Quotation plugin is not active.\n";
    echo "   Please activate the plugin in WordPress admin first.\n\n";
    exit(1);
}

echo "✅ WordPress loaded successfully\n";
echo "✅ Plugin version: " . PCQ_VERSION . "\n\n";

// Load the DummyDataGenerator class
require_once PCQ_PLUGIN_DIR . 'includes/Database/DummyDataGenerator.php';

// Parse command line arguments
$command = isset($argv[1]) ? strtolower($argv[1]) : 'generate';
$force = in_array('--force', $argv) || in_array('-f', $argv);

// Execute command
switch ($command) {
    case 'generate':
        generate_dummy_data($force);
        break;
        
    case 'clear':
        clear_dummy_data();
        break;
        
    case 'recreate':
        recreate_database();
        break;
        
    case 'status':
        show_status();
        break;
        
    case 'help':
    case '--help':
    case '-h':
        show_help();
        break;
        
    default:
        echo "❌ Unknown command: {$command}\n\n";
        show_help();
        exit(1);
}

echo "\n";
exit(0);

/**
 * Find wp-load.php by traversing up the directory tree
 */
function find_wp_load() {
    $dir = dirname(__FILE__);
    
    // Try common paths first
    $common_paths = [
        $dir . '/../../../../wp-load.php',           // plugins/plugin-name/
        $dir . '/../../../wp-load.php',              // plugins/
        $dir . '/../../wp-load.php',                 // wp-content/
        $dir . '/../wp-load.php',                    // WordPress root
    ];
    
    foreach ($common_paths as $path) {
        $real_path = realpath($path);
        if ($real_path && file_exists($real_path)) {
            return $real_path;
        }
    }
    
    // Search up to 10 levels up
    for ($i = 0; $i < 10; $i++) {
        $check_path = $dir . '/wp-load.php';
        if (file_exists($check_path)) {
            return realpath($check_path);
        }
        $dir = dirname($dir);
    }
    
    return false;
}

/**
 * Generate dummy data
 */
function generate_dummy_data($force = false) {
    global $wpdb;
    
    echo "🔄 Generating dummy data...\n\n";
    
    // Check existing data
    $counts = get_table_counts();
    $total = array_sum($counts);
    
    if ($total > 10 && !$force) {
        echo "⚠️  Warning: Database already contains {$total} records.\n";
        echo "   Use --force or -f flag to add more data anyway.\n";
        echo "   Or use 'recreate' command to start fresh.\n\n";
        show_status();
        return;
    }
    
    // Suppress output buffering for real-time feedback
    ob_implicit_flush(true);
    
    try {
        \ProClean\Quotation\Database\DummyDataGenerator::generateAll();
        
        echo "\n✅ Dummy data generated successfully!\n\n";
        show_status();
        
    } catch (Exception $e) {
        echo "❌ Error generating data: " . $e->getMessage() . "\n";
    }
}

/**
 * Clear all dummy data
 */
function clear_dummy_data() {
    echo "🗑️  Clearing all data...\n\n";
    
    try {
        \ProClean\Quotation\Database\DummyDataGenerator::clearAll();
        
        echo "\n✅ All data cleared successfully!\n\n";
        show_status();
        
    } catch (Exception $e) {
        echo "❌ Error clearing data: " . $e->getMessage() . "\n";
    }
}

/**
 * Recreate database with fresh data
 */
function recreate_database() {
    echo "🔄 Recreating database with fresh dummy data...\n\n";
    
    echo "Step 1: Clearing existing data...\n";
    clear_dummy_data();
    
    echo "\nStep 2: Generating fresh dummy data...\n";
    generate_dummy_data(true);
    
    echo "\n✅ Database recreation completed!\n";
}

/**
 * Show current database status
 */
function show_status() {
    $counts = get_table_counts();
    
    echo "📊 Current Database Status:\n";
    echo "   ─────────────────────────────────────\n";
    
    $max_label_length = max(array_map('strlen', array_keys($counts)));
    
    foreach ($counts as $table => $count) {
        $label = str_pad(ucfirst(str_replace('_', ' ', $table)), $max_label_length + 2);
        $value = str_pad(number_format($count), 6, ' ', STR_PAD_LEFT);
        $bar = str_repeat('█', min(30, $count));
        echo "   {$label}: {$value}  {$bar}\n";
    }
    
    echo "   ─────────────────────────────────────\n";
    $total = array_sum($counts);
    $total_label = str_pad('TOTAL', $max_label_length + 2);
    $total_value = str_pad(number_format($total), 6, ' ', STR_PAD_LEFT);
    echo "   {$total_label}: {$total_value}\n";
}

/**
 * Get record counts for all tables
 */
function get_table_counts() {
    global $wpdb;
    
    $tables = [
        'services' => $wpdb->prefix . 'pq_services',
        'employees' => $wpdb->prefix . 'pq_employees',
        'quotes' => $wpdb->prefix . 'pq_quotes',
        'appointments' => $wpdb->prefix . 'pq_appointments',
        'bookings' => $wpdb->prefix . 'pq_bookings',
        'email_logs' => $wpdb->prefix . 'pq_email_logs',
        'settings' => $wpdb->prefix . 'pq_settings'
    ];
    
    $counts = [];
    foreach ($tables as $name => $table) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $counts[$name] = (int)$count;
    }
    
    return $counts;
}

/**
 * Show help information
 */
function show_help() {
    echo "Usage: php generate-dummy-data.php [command] [options]\n\n";
    
    echo "Commands:\n";
    echo "  generate   Generate dummy data (default command)\n";
    echo "  clear      Clear all dummy data from the database\n";
    echo "  recreate   Clear and regenerate all data (fresh start)\n";
    echo "  status     Show current database record counts\n";
    echo "  help       Show this help message\n\n";
    
    echo "Options:\n";
    echo "  --force, -f   Force generate data even if records exist\n\n";
    
    echo "Examples:\n";
    echo "  php generate-dummy-data.php                  # Generate data\n";
    echo "  php generate-dummy-data.php generate -f     # Force generate\n";
    echo "  php generate-dummy-data.php status          # Check counts\n";
    echo "  php generate-dummy-data.php clear           # Clear all\n";
    echo "  php generate-dummy-data.php recreate        # Fresh start\n\n";
    
    echo "Data Generated:\n";
    echo "  • 7 Services (Façade, Roof, Windows, Emergency, etc.)\n";
    echo "  • 6 Employees with different specializations\n";
    echo "  • 25 Customer Quotes with various statuses\n";
    echo "  • 30+ Appointments (past, present, future)\n";
    echo "  • 20 Bookings with payment tracking\n";
    echo "  • 50 Email Logs with engagement metrics\n";
    echo "  • Business Settings and Configuration\n";
}
