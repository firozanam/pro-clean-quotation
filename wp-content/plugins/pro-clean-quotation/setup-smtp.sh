#!/bin/bash
# SMTP Setup Helper Script for Pro Clean Quotation System

echo "========================================"
echo "Pro Clean Quotation SMTP Setup Helper"
echo "========================================"
echo ""

# Check if MailPit is running
echo "Checking if MailPit is running..."
if ps aux | grep -v grep | grep -q mailpit; then
    echo "✓ MailPit is running"
else
    echo "✗ MailPit is not running"
    echo "  Please start MailPit first:"
    echo "  mailpit"
    echo ""
fi

# Check if MailPit web interface is accessible
echo "Checking MailPit web interface..."
if curl -s http://localhost:8025 > /dev/null 2>&1; then
    echo "✓ MailPit web interface is accessible at http://localhost:8025"
else
    echo "✗ MailPit web interface is not accessible"
fi

echo ""

# Find WordPress root directory
WP_ROOT=$(pwd)
while [ ! -f "$WP_ROOT/wp-config.php" ] && [ "$WP_ROOT" != "/" ]; do
    WP_ROOT=$(dirname "$WP_ROOT")
done

if [ ! -f "$WP_ROOT/wp-config.php" ]; then
    echo "WordPress installation not found."
    echo "Please run this script from within your WordPress directory."
    exit 1
fi

echo "Found WordPress installation at: $WP_ROOT"
echo ""

# Check if wp-config.php has SMTP configuration
if grep -q "PCQ_MAIL_ENABLED" "$WP_ROOT/wp-config.php" 2>/dev/null; then
    echo "✓ SMTP configuration found in wp-config.php"
else
    echo "✗ SMTP configuration not found in wp-config.php"
    echo ""
    echo "To configure SMTP, add the following to wp-config.php"
    echo "(before the line: require_once ABSPATH . 'wp-settings.php';):"
    echo ""
    echo "---------------------------------------------------"
    cat << 'EOF'
// Enable SMTP for Pro Clean Quotation
define('PCQ_MAIL_ENABLED', true);
define('PCQ_MAIL_HOST', 'localhost');
define('PCQ_MAIL_PORT', 1025);
define('PCQ_MAIL_AUTH', false);
define('PCQ_MAIL_USERNAME', '');
define('PCQ_MAIL_PASSWORD', '');
define('PCQ_MAIL_ENCRYPTION', '');
define('PCQ_MAIL_FROM_ADDRESS', 'info@webblymedia.se');
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
EOF
    echo "---------------------------------------------------"
    echo ""
    
    # Offer to create mu-plugin instead
    read -p "Would you like to create a must-use plugin for SMTP configuration instead? (y/n) " -n 1 -r
    echo ""
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        MU_PLUGIN_DIR="$WP_ROOT/wp-content/mu-plugins"
        MU_PLUGIN_FILE="$MU_PLUGIN_DIR/pcq-smtp-config.php"
        
        # Create mu-plugins directory if it doesn't exist
        if [ ! -d "$MU_PLUGIN_DIR" ]; then
            mkdir -p "$MU_PLUGIN_DIR"
            echo "Created mu-plugins directory"
        fi
        
        # Create mu-plugin file
        cat > "$MU_PLUGIN_FILE" << 'EOF'
<?php
/**
 * Plugin Name: Pro Clean SMTP Configuration
 * Description: Configures SMTP for Pro Clean Quotation System (MailPit)
 * Version: 1.0
 * Author: Pro Clean Development Team
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Enable SMTP for Pro Clean Quotation
if (!defined('PCQ_MAIL_ENABLED')) {
    define('PCQ_MAIL_ENABLED', true);
}

if (!defined('PCQ_MAIL_HOST')) {
    define('PCQ_MAIL_HOST', 'localhost');
}

if (!defined('PCQ_MAIL_PORT')) {
    define('PCQ_MAIL_PORT', 1025);
}

if (!defined('PCQ_MAIL_AUTH')) {
    define('PCQ_MAIL_AUTH', false);
}

if (!defined('PCQ_MAIL_USERNAME')) {
    define('PCQ_MAIL_USERNAME', '');
}

if (!defined('PCQ_MAIL_PASSWORD')) {
    define('PCQ_MAIL_PASSWORD', '');
}

if (!defined('PCQ_MAIL_ENCRYPTION')) {
    define('PCQ_MAIL_ENCRYPTION', '');
}

if (!defined('PCQ_MAIL_FROM_ADDRESS')) {
    define('PCQ_MAIL_FROM_ADDRESS', 'info@webblymedia.se');
}

if (!defined('PCQ_MAIL_FROM_NAME')) {
    define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
}
EOF
        
        echo "✓ Must-use plugin created at: $MU_PLUGIN_FILE"
        echo ""
        echo "SMTP is now configured to use MailPit!"
        echo "No changes to wp-config.php required."
    fi
fi

echo ""
echo "========================================"
echo "Setup Summary"
echo "========================================"
echo ""
echo "SMTP Configuration:"
echo "  Host: localhost"
echo "  Port: 1025"
echo "  Encryption: None"
echo "  Authentication: No"
echo "  From Email: info@webblymedia.se"
echo "  From Name: We Cleaning"
echo ""
echo "MailPit Web Interface: http://localhost:8025"
echo ""
echo "Next Steps:"
echo "1. Ensure MailPit is running"
echo "2. Test email sending from WordPress"
echo "3. Check MailPit web interface for received emails"
echo ""
echo "For more information, see:"
echo "  SMTP_SETUP_GUIDE.md"
echo ""
