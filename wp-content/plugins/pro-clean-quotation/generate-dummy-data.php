<?php
/**
 * Generate Dummy Data Script
 * Run this file directly in the browser or via PHP CLI
 */

// Load WordPress
require_once __DIR__ . '/../../../wp-load.php';

// Check if user is admin (for browser execution)
if (!defined('WP_CLI') && !current_user_can('manage_options')) {
    die('Unauthorized access');
}

echo "<!DOCTYPE html><html><head><title>Dummy Data Generator</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}h2{color:#4ec9b0;}</style></head><body>";
echo "<h2>Pro Clean Quotation - Dummy Data Generator</h2>";
echo "<pre>";

// Clear existing data
echo "\n🗑️  Clearing existing dummy data...\n";
ProClean\Quotation\Database\DummyDataGenerator::clearAll();
echo "✅ Cleared!\n\n";

// Generate new data
echo "🎲 Generating new dummy data...\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
ProClean\Quotation\Database\DummyDataGenerator::generateAll();
echo "\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";

echo "\n✨ <strong style='color:#4ec9b0;'>Dummy data generation completed!</strong>\n\n";
echo "📊 <strong>Summary:</strong>\n";
echo "   • Services: 7\n";
echo "   • Employees: 6\n";
echo "   • Quotes: 25\n";
echo "   • Appointments: ~50 (January 2026 + historical)\n";
echo "   • Bookings: 20\n";
echo "   • Email Logs: 50\n";
echo "   • Settings: 7\n\n";

echo "🎯 <strong style='color:#569cd6;'>Test the booking system:</strong>\n";
echo "   1. Go to: <a href='/wecleaning/book-service/?quote_id=30&token=...'>Book Service Page</a>\n";
echo "   2. Select a date in January 2026\n";
echo "   3. View available time slots\n";
echo "   4. Some slots will be booked, some will be available\n\n";

echo "✅ <strong>You can now test the plugin!</strong>\n";

echo "</pre></body></html>";
