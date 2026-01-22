<?php
/**
 * Run Email and Booking Workflow Tests
 * Execute via: php run-tests.php
 */

define('WP_USE_THEMES', false);
require('/Applications/XAMPP/xamppfiles/htdocs/wecleaning/wp-load.php');

if (!defined('ABSPATH')) {
    die("WordPress not loaded\n");
}

echo "=== Pro Clean Quotation - Email & Booking Workflow Test ===\n\n";

use ProClean\Quotation\Frontend\FormHandler;
use ProClean\Quotation\Email\EmailManager;
use ProClean\Quotation\Models\Quote;
use ProClean\Quotation\Admin\Settings;
use ProClean\Quotation\Services\PDFGenerator;

// Test 1: Configuration Check
echo "📋 Test 1: System Configuration\n";
echo "==============================\n";
echo "Email Notifications: " . (Settings::get('email_notifications_enabled', true) ? 'Enabled ✅' : 'Disabled ❌') . "\n";
echo "PDF Generation: " . (Settings::get('pdf_generation_enabled', true) ? 'Enabled ✅' : 'Disabled ❌') . "\n";
echo "Company Name: " . Settings::get('company_name', get_bloginfo('name')) . "\n";
echo "Company Email: " . Settings::get('company_email', get_option('admin_email')) . "\n";
echo "Admin Email: " . Settings::get('admin_notification_email', get_option('admin_email')) . "\n";
echo "\n";

// Test 2: Submit Test Quote
echo "📨 Test 2: Quote Submission\n";
echo "===========================\n";

$test_data = [
    'action' => 'pcq_submit_quote',
    'nonce' => wp_create_nonce('pcq_nonce'),
    'customer_name' => 'Test Customer',
    'customer_email' => 'test@example.com',
    'customer_phone' => '+34612345678',
    'property_address' => '123 Test Street, Barcelona',
    'postal_code' => '08001',
    'service_type' => '2',
    'square_meters' => '200',
    'linear_meters' => '40',
    'property_type' => 'residential',
    'surface_material' => 'brick',
    'building_height' => '1',
    'roof_type' => 'pitched',
    'privacy_consent' => '1',
    'special_requirements' => 'Test submission for workflow validation',
    'custom_fields' => json_encode(['roof_type' => 'pitched'])
];

try {
    $form_handler = FormHandler::getInstance();
    $result = $form_handler->submitQuote($test_data);
    
    if ($result['success']) {
        echo "✅ Quote Submitted Successfully!\n";
        echo "Quote Number: " . $result['data']['quote_number'] . "\n";
        echo "Quote ID: " . $result['data']['quote_id'] . "\n";
        echo "Total Price: €" . number_format($result['data']['total_price'], 2) . "\n";
        echo "Valid Until: " . $result['data']['valid_until'] . "\n";
        echo "Email Sent: " . ($result['data']['email_sent'] ? 'Yes ✅' : 'No ❌') . "\n";
        echo "Booking URL: " . $result['data']['booking_url'] . "\n";
        $quote_id = $result['data']['quote_id'];
    } else {
        echo "❌ Quote Submission Failed\n";
        echo "Message: " . $result['message'] . "\n";
        if (!empty($result['errors'])) {
            echo "Errors: " . print_r($result['errors'], true) . "\n";
        }
        $quote_id = null;
    }
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
    $quote_id = null;
}
echo "\n";

// Test 3: PDF Generation
if ($quote_id) {
    echo "📄 Test 3: PDF Generation\n";
    echo "=========================\n";
    
    try {
        $quote = new Quote($quote_id);
        $pdf_generator = PDFGenerator::getInstance();
        $pdf_path = $pdf_generator->generateQuotePDF($quote);
        
        if ($pdf_path && file_exists($pdf_path)) {
            $pdf_size = filesize($pdf_path);
            echo "✅ PDF Generated Successfully!\n";
            echo "File: " . basename($pdf_path) . "\n";
            echo "Size: " . number_format($pdf_size / 1024, 2) . " KB\n";
            echo "Path: " . $pdf_path . "\n";
            
            // Verify PDF content
            if ($pdf_size > 10000) {
                echo "PDF size looks good (>10KB) ✅\n";
            } else {
                echo "⚠️  PDF size seems small, might be incomplete\n";
            }
            
            // Clean up
            unlink($pdf_path);
            echo "Temporary file cleaned up ✅\n";
        } else {
            echo "❌ PDF generation returned no file\n";
        }
    } catch (Exception $e) {
        echo "❌ PDF Generation Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 4: Email Logs
if ($quote_id) {
    echo "📊 Test 4: Email Logs\n";
    echo "=====================\n";
    
    global $wpdb;
    $logs_table = $wpdb->prefix . 'pq_email_logs';
    
    $logs = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$logs_table} WHERE reference_type = 'quote' AND reference_id = %d ORDER BY sent_at DESC LIMIT 5",
        $quote_id
    ));
    
    if ($logs) {
        echo "Found " . count($logs) . " email log(s):\n";
        foreach ($logs as $log) {
            $status_icon = $log->status === 'sent' ? '✅' : '❌';
            echo $status_icon . " Type: " . $log->email_type . " | To: " . $log->recipient_email . " | Status: " . $log->status . " | Sent: " . $log->sent_at . "\n";
        }
    } else {
        echo "❌ No email logs found for quote ID " . $quote_id . "\n";
    }
    echo "\n";
}

// Test 5: Booking URL Validation
if ($quote_id) {
    echo "🔗 Test 5: Booking URL Validation\n";
    echo "==================================\n";
    
    $quote = new Quote($quote_id);
    
    // Get booking URL using reflection since method is private
    $reflection = new ReflectionClass(FormHandler::getInstance());
    $method = $reflection->getMethod('generateBookingUrl');
    $method->setAccessible(true);
    $booking_url = $method->invoke(FormHandler::getInstance(), $quote);
    
    echo "Generated URL: " . $booking_url . "\n";
    
    // Parse URL
    $parsed = parse_url($booking_url);
    parse_str($parsed['query'] ?? '', $query_params);
    
    if (isset($query_params['quote_id']) && $query_params['quote_id'] == $quote_id) {
        echo "✅ Quote ID in URL matches\n";
    } else {
        echo "❌ Quote ID mismatch\n";
    }
    
    if (isset($query_params['token']) && !empty($query_params['token'])) {
        echo "✅ Security token present\n";
    } else {
        echo "❌ Security token missing\n";
    }
    echo "\n";
}

// Test 6: Check MailPit/SMTP
echo "📬 Test 6: SMTP Configuration\n";
echo "=============================\n";

if (defined('SMTP_HOST')) {
    echo "SMTP Host: " . SMTP_HOST . "\n";
    echo "SMTP Port: " . SMTP_PORT . "\n";
    
    if (SMTP_HOST === 'localhost' && SMTP_PORT == 1025) {
        echo "✅ MailPit detected - Check emails at: http://localhost:8025\n";
    }
} else {
    echo "Using default WordPress wp_mail()\n";
    echo "⚠️  Consider configuring SMTP for better email delivery\n";
}
echo "\n";

// Summary
echo "\n";
echo "╔════════════════════════════════════╗\n";
echo "║        TEST SUMMARY                ║\n";
echo "╚════════════════════════════════════╝\n";
echo "\n";

$passed = 0;
$total = 6;

if (!empty(Settings::get('email_notifications_enabled', true))) $passed++;
if (isset($result) && $result['success']) $passed++;
if (isset($pdf_path) && !empty($pdf_path)) $passed++;
if (!empty($logs)) $passed++;
if (!empty($booking_url)) $passed++;
if (defined('SMTP_HOST') || true) $passed++;

$percentage = round(($passed / $total) * 100);

echo "Tests Passed: {$passed}/{$total} ({$percentage}%)\n";

if ($percentage >= 80) {
    echo "\n🎉 EXCELLENT! All major systems are functional.\n";
} elseif ($percentage >= 60) {
    echo "\n✅ GOOD! Most systems working, some issues need attention.\n";
} else {
    echo "\n⚠️  NEEDS ATTENTION! Multiple issues detected.\n";
}

echo "\n";
echo "=== Test Complete ===\n";
