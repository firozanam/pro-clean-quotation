<?php
/**
 * Quick Verification Script - Test Postal Code Fix
 */

require_once('/Applications/XAMPP/xamppfiles/htdocs/wecleaning/wp-load.php');
require_once(__DIR__ . '/includes/Admin/Settings.php');
require_once(__DIR__ . '/includes/Services/ValidationService.php');

use ProClean\Quotation\Admin\Settings;
use ProClean\Quotation\Services\ValidationService;

$validator = ValidationService::getInstance();

// Test postal codes
$test_codes = ['29600', '26960', '28001', '01001', '52999'];

echo "Service Area Configuration Check:\n";
echo "====================================\n";
$areas = Settings::get('service_area_postcodes', []);
echo "Type: " . gettype($areas) . "\n";
echo "Count: " . count($areas) . "\n";
echo "Empty: " . (empty($areas) ? 'YES (All Spain enabled)' : 'NO') . "\n\n";

if (!empty($areas)) {
    echo "WARNING: Service area has restrictions:\n";
    print_r($areas);
    echo "\n";
}

echo "Postal Code Validation Tests:\n";
echo "====================================\n";
$all_passed = true;

foreach ($test_codes as $code) {
    $format_result = $validator->validatePostalCode($code, 'ES');
    $area_result = $validator->checkServiceArea($code);
    
    $passed = $format_result['valid'] && $area_result['available'];
    $all_passed = $all_passed && $passed;
    
    echo "Code: $code\n";
    echo "  Format Valid: " . ($format_result['valid'] ? 'YES ✓' : 'NO ✗') . "\n";
    echo "  Area Available: " . ($area_result['available'] ? 'YES ✓' : 'NO ✗') . "\n";
    
    if (!$area_result['available']) {
        echo "  Error: " . strip_tags($area_result['message']) . "\n";
    }
    
    echo "  Status: " . ($passed ? '✓ PASS' : '✗ FAIL') . "\n";
    echo "\n";
}

echo "====================================\n";
echo "Overall Result: " . ($all_passed ? '✓ ALL TESTS PASSED' : '✗ SOME TESTS FAILED') . "\n";
echo "====================================\n";

if ($all_passed) {
    echo "\n✓ Fix successful! All Spanish postal codes (01001-52999) are now accepted.\n";
    echo "You can now test the quote form with any valid Spanish postal code.\n";
} else {
    echo "\n✗ Fix incomplete. Please check the service area configuration.\n";
}
