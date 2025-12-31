# SMTP Configuration Guide for Pro Clean Quotation System

## Overview

This guide explains how to configure SMTP for the Pro Clean Quotation System to work with MailPit or other email services.

## Quick Setup for MailPit (Local Development)

### Step 1: Verify MailPit is Running

Make sure MailPit is installed and running on your system:

```bash
# Check if MailPit is running
ps aux | grep mailpit

# If not running, start MailPit (adjust path as needed)
mailpit
```

MailPit web interface should be accessible at: http://localhost:8025

### Step 2: Add Configuration to wp-config.php

Open your WordPress `wp-config.php` file (located in the WordPress root directory, **not** in the plugin directory) and add the following lines **just before** the line that says `require_once ABSPATH . 'wp-settings.php';`:

```php
/**
 * SMTP Configuration for Pro Clean Quotation System
 * Using MailPit for Local Development
 */

// Enable SMTP for Pro Clean Quotation
define('PCQ_MAIL_ENABLED', true);

// SMTP Server Configuration
define('PCQ_MAIL_HOST', 'localhost');          // MailPit SMTP server
define('PCQ_MAIL_PORT', 1025);                 // MailPit SMTP port

// SMTP Authentication (not required for MailPit)
define('PCQ_MAIL_AUTH', false);                // No authentication needed
define('PCQ_MAIL_USERNAME', '');               // Not needed for MailPit
define('PCQ_MAIL_PASSWORD', '');               // Not needed for MailPit

// SMTP Encryption (none for MailPit)
define('PCQ_MAIL_ENCRYPTION', '');             // No encryption for local development

// Email Sender Information
define('PCQ_MAIL_FROM_ADDRESS', 'info@webblymedia.se');
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
```

### Step 3: Save and Test

1. Save the `wp-config.php` file
2. Go to your WordPress admin dashboard
3. Navigate to **Pro Clean Quotation → Settings**
4. Trigger a quote submission to test email sending
5. Check MailPit web interface at http://localhost:8025 to see captured emails

## Alternative Setup Methods

### Method 1: Using Must-Use Plugin (Recommended for Development)

If you cannot edit `wp-config.php`, create a must-use plugin:

1. Create directory: `wp-content/mu-plugins/` (if it doesn't exist)
2. Create file: `wp-content/mu-plugins/pcq-smtp-config.php`
3. Add the following content:

```php
<?php
/**
 * Plugin Name: Pro Clean SMTP Configuration
 * Description: Configures SMTP for Pro Clean Quotation System
 * Version: 1.0
 */

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
```

### Method 2: Using WordPress Database Options

You can also configure SMTP through WordPress options:

```php
// Add to your theme's functions.php or a custom plugin
add_action('init', function() {
    update_option('pcq_smtp_enabled', true);
    update_option('pcq_smtp_host', 'localhost');
    update_option('pcq_smtp_port', 1025);
    update_option('pcq_smtp_auth', false);
    update_option('pcq_smtp_username', '');
    update_option('pcq_smtp_password', '');
    update_option('pcq_smtp_encryption', '');
    update_option('pcq_smtp_from_email', 'info@webblymedia.se');
    update_option('pcq_smtp_from_name', 'We Cleaning');
}, 1);
```

## Configuration for Production Email Services

### Gmail SMTP

```php
define('PCQ_MAIL_ENABLED', true);
define('PCQ_MAIL_HOST', 'smtp.gmail.com');
define('PCQ_MAIL_PORT', 587);
define('PCQ_MAIL_AUTH', true);
define('PCQ_MAIL_USERNAME', 'your-email@gmail.com');
define('PCQ_MAIL_PASSWORD', 'your-app-password');  // Use App Password, not regular password
define('PCQ_MAIL_ENCRYPTION', 'tls');
define('PCQ_MAIL_FROM_ADDRESS', 'your-email@gmail.com');
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
```

**Note:** For Gmail, you need to generate an App Password:
1. Go to Google Account Settings → Security
2. Enable 2-Step Verification
3. Generate an App Password for "Mail"

### SendGrid SMTP

```php
define('PCQ_MAIL_ENABLED', true);
define('PCQ_MAIL_HOST', 'smtp.sendgrid.net');
define('PCQ_MAIL_PORT', 587);
define('PCQ_MAIL_AUTH', true);
define('PCQ_MAIL_USERNAME', 'apikey');
define('PCQ_MAIL_PASSWORD', 'your-sendgrid-api-key');
define('PCQ_MAIL_ENCRYPTION', 'tls');
define('PCQ_MAIL_FROM_ADDRESS', 'info@webblymedia.se');
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
```

### Mailgun SMTP

```php
define('PCQ_MAIL_ENABLED', true);
define('PCQ_MAIL_HOST', 'smtp.mailgun.org');
define('PCQ_MAIL_PORT', 587);
define('PCQ_MAIL_AUTH', true);
define('PCQ_MAIL_USERNAME', 'postmaster@your-domain.mailgun.org');
define('PCQ_MAIL_PASSWORD', 'your-mailgun-password');
define('PCQ_MAIL_ENCRYPTION', 'tls');
define('PCQ_MAIL_FROM_ADDRESS', 'info@webblymedia.se');
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
```

### Amazon SES SMTP

```php
define('PCQ_MAIL_ENABLED', true);
define('PCQ_MAIL_HOST', 'email-smtp.us-east-1.amazonaws.com');  // Change region as needed
define('PCQ_MAIL_PORT', 587);
define('PCQ_MAIL_AUTH', true);
define('PCQ_MAIL_USERNAME', 'your-ses-smtp-username');
define('PCQ_MAIL_PASSWORD', 'your-ses-smtp-password');
define('PCQ_MAIL_ENCRYPTION', 'tls');
define('PCQ_MAIL_FROM_ADDRESS', 'info@webblymedia.se');
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
```

### Microsoft 365 / Outlook SMTP

```php
define('PCQ_MAIL_ENABLED', true);
define('PCQ_MAIL_HOST', 'smtp.office365.com');
define('PCQ_MAIL_PORT', 587);
define('PCQ_MAIL_AUTH', true);
define('PCQ_MAIL_USERNAME', 'your-email@outlook.com');
define('PCQ_MAIL_PASSWORD', 'your-password');
define('PCQ_MAIL_ENCRYPTION', 'tls');
define('PCQ_MAIL_FROM_ADDRESS', 'your-email@outlook.com');
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
```

## Configuration Priority

The SMTP configuration follows this priority order:

1. **wp-config.php constants** (Highest priority)
   - Uses `PCQ_MAIL_*` constants
   - Recommended for production

2. **WordPress options** (Lower priority)
   - Uses `pcq_smtp_*` options
   - Can be set programmatically or through database

## Troubleshooting

### Emails Not Sending

1. **Check if SMTP is enabled:**
   - Verify `PCQ_MAIL_ENABLED` is set to `true`
   - Check error logs for SMTP configuration messages

2. **Verify MailPit is running:**
   ```bash
   # Check MailPit process
   ps aux | grep mailpit
   
   # Check MailPit web interface
   curl http://localhost:8025
   ```

3. **Check WordPress debug logs:**
   - Enable WP_DEBUG in wp-config.php
   - Check wp-content/debug.log for SMTP errors

4. **Test SMTP connection:**
   - Use the test email function in plugin settings
   - Check MailPit web interface at http://localhost:8025

### Common Issues

#### Port Already in Use
If port 1025 is already in use:
```bash
# Find process using port 1025
lsof -i :1025

# Start MailPit on different port
mailpit --smtp-bind-addr 127.0.0.1:1026
```

Then update configuration:
```php
define('PCQ_MAIL_PORT', 1026);
```

#### Connection Timeout
- Check firewall settings
- Verify SMTP server is reachable
- Increase timeout in configuration

#### Authentication Failed
- Verify username and password are correct
- For Gmail, use App Password instead of regular password
- Check if 2FA is required

## Debugging SMTP

To enable detailed SMTP debugging, add to wp-config.php:

```php
// Enable WordPress debugging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

SMTP debug messages will be logged to `wp-content/debug.log`.

## Verifying Configuration

### Check Current Configuration

You can verify your SMTP configuration is loaded correctly by checking the debug log:

```bash
tail -f wp-content/debug.log
```

Look for lines like:
```
PCQ SMTP: Configured with Host=localhost, Port=1025, Auth=No, Encryption=None
```

### Test Email Sending

1. Navigate to WordPress admin
2. Create a test quote through the Pro Clean Quotation form
3. Check MailPit at http://localhost:8025
4. Email should appear in MailPit inbox

## Security Best Practices

1. **Never commit credentials to version control**
   - Keep wp-config.php out of git
   - Use environment variables for sensitive data

2. **Use App Passwords for Gmail**
   - Never use your main Google password
   - Generate App Passwords for each application

3. **Rotate credentials regularly**
   - Change SMTP passwords periodically
   - Revoke unused App Passwords

4. **Use TLS/SSL encryption in production**
   - Always use encryption for production email
   - Only disable encryption for local development

## Support

For additional help:
- Check plugin documentation
- Review WordPress debug logs
- Verify MailPit is running correctly
- Test SMTP settings with command-line tools

## Environment Variables (Optional)

For advanced setups using environment variables:

1. Install `vlucas/phpdotenv` if not already installed
2. Create `.env` file in WordPress root:

```env
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=
MAIL_FROM_ADDRESS=info@webblymedia.se
MAIL_FROM_NAME="We Cleaning"
```

3. Load in wp-config.php:

```php
// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    
    define('PCQ_MAIL_ENABLED', true);
    define('PCQ_MAIL_HOST', $_ENV['MAIL_HOST']);
    define('PCQ_MAIL_PORT', $_ENV['MAIL_PORT']);
    define('PCQ_MAIL_USERNAME', $_ENV['MAIL_USERNAME']);
    define('PCQ_MAIL_PASSWORD', $_ENV['MAIL_PASSWORD']);
    define('PCQ_MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION']);
    define('PCQ_MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS']);
    define('PCQ_MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME']);
}
```
