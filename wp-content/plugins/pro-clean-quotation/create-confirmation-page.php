#!/usr/bin/env php
<?php
/**
 * One-time script to create the booking confirmation page
 * 
 * Usage: 
 * 1. Via browser: http://localhost/wp-content/plugins/pro-clean-quotation/create-confirmation-page.php
 * 2. Via CLI: php create-confirmation-page.php
 * 
 * @package ProClean\Quotation
 */

// Load WordPress
$wp_load_paths = [
    __DIR__ . '/../../../../wp-load.php',     // From plugin dir to wp root
    __DIR__ . '/../../../wp-load.php',        // Alternative
    dirname(__DIR__, 5) . '/wp-load.php',     // Using dirname
];

$wp_loaded = false;
foreach ($wp_load_paths as $wp_load_path) {
    if (file_exists($wp_load_path)) {
        require_once $wp_load_path;
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die("Error: Could not find WordPress installation. Please run this script from the plugin directory.\n");
}

// Check if running via CLI or web
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    // Running via web - check if user is admin
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        die("Error: You must be logged in as an administrator to run this script.");
    }
    echo "<h1>Pro Clean Quotation - Create Confirmation Page</h1>";
    echo "<pre>";
}

echo "Creating booking confirmation page...\n\n";

// Check if page already exists
$existing_page = get_page_by_path('booking-confirmation');

if ($existing_page) {
    echo "✓ Booking confirmation page already exists!\n";
    echo "  - Page ID: " . $existing_page->ID . "\n";
    echo "  - Page URL: " . get_permalink($existing_page->ID) . "\n";
    
    // Update the option
    update_option('pcq_confirmation_page_id', $existing_page->ID);
    echo "  - Updated option 'pcq_confirmation_page_id'\n";
} else {
    // Create new confirmation page
    $confirmation_page = [
        'post_title'    => 'Booking Confirmation',
        'post_content'  => '[pcq_booking_confirmation]',
        'post_status'   => 'publish',
        'post_type'     => 'page',
        'post_name'     => 'booking-confirmation',
        'post_author'   => get_current_user_id() ?: 1,
        'comment_status' => 'closed',
        'ping_status'   => 'closed'
    ];
    
    $page_id = wp_insert_post($confirmation_page);
    
    if ($page_id && !is_wp_error($page_id)) {
        echo "✓ Successfully created booking confirmation page!\n";
        echo "  - Page ID: " . $page_id . "\n";
        echo "  - Page URL: " . get_permalink($page_id) . "\n";
        
        // Save the page ID in options
        update_option('pcq_confirmation_page_id', $page_id);
        echo "  - Saved page ID to option 'pcq_confirmation_page_id'\n";
    } else {
        echo "✗ Error creating page: ";
        if (is_wp_error($page_id)) {
            echo $page_id->get_error_message() . "\n";
        } else {
            echo "Unknown error\n";
        }
        if (!$is_cli) {
            echo "</pre>";
        }
        exit(1);
    }
}

// Flush rewrite rules
flush_rewrite_rules();
echo "\n✓ Flushed rewrite rules\n";

echo "\n================================\n";
echo "SUCCESS! Your booking confirmation page is ready.\n";
echo "================================\n\n";
echo "Next steps:\n";
echo "1. Try creating a new booking\n";
echo "2. You should now be redirected to: " . home_url('/booking-confirmation/') . "\n";
echo "3. You can customize the page by editing it in WordPress admin\n";

if (!$is_cli) {
    echo "</pre>";
    echo "<p><a href='" . admin_url('edit.php?post_type=page') . "'>View All Pages</a></p>";
    echo "<p><strong>You can safely delete this file now: create-confirmation-page.php</strong></p>";
}

echo "\n";
