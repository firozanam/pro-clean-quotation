# SMTP Configuration - Quick Start Guide

## ✅ MailPit Status

Your MailPit is **running** and accessible at: http://localhost:8025

## 🚀 Quick Setup (Choose One Option)

### Option 1: Must-Use Plugin (Recommended - Easiest)

1. **Copy the configuration file:**
   ```bash
   cp pcq-smtp-config.php ../../mu-plugins/
   ```
   
   Or manually:
   - Copy file: `pcq-smtp-config.php`
   - To: `wp-content/mu-plugins/pcq-smtp-config.php`
   - Create `mu-plugins` folder if it doesn't exist

2. **Done!** SMTP is now configured. No other changes needed.

### Option 2: wp-config.php Configuration

1. **Open your wp-config.php** (located in WordPress root directory)

2. **Add these lines** before `require_once ABSPATH . 'wp-settings.php';`:

```php
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

3. **Save the file**

## 🧪 Testing

1. Navigate to your WordPress site
2. Create a test quote through the Pro Clean Quotation form
3. Open MailPit at http://localhost:8025
4. You should see the email in MailPit's inbox!

## 📧 Email Configuration Summary

- **SMTP Host:** localhost
- **SMTP Port:** 1025
- **Encryption:** None (local development)
- **Authentication:** Not required
- **From Email:** info@webblymedia.se
- **From Name:** We Cleaning

## 🔍 Verification

To verify SMTP is configured correctly, check your WordPress debug log:

```bash
tail -f ../../../../debug.log
```

Look for a line like:
```
PCQ SMTP: Configured with Host=localhost, Port=1025, Auth=No, Encryption=None
```

## 🐛 Troubleshooting

### Emails not appearing in MailPit?

1. **Verify MailPit is running:**
   - Open http://localhost:8025 in your browser
   - You should see MailPit's web interface

2. **Check WordPress debug log:**
   ```bash
   tail -f ../../../../debug.log
   ```

3. **Enable WordPress debugging** (in wp-config.php):
   ```php
   define('WP_DEBUG', true);
   define('WP_DEBUG_LOG', true);
   define('WP_DEBUG_DISPLAY', false);
   ```

### Need more help?

- See full documentation: `SMTP_SETUP_GUIDE.md`
- Check plugin files:
  - `includes/Email/SMTPConfig.php` - SMTP configuration class
  - `includes/Email/EmailManager.php` - Email handling

## 🌐 Production Configuration

When moving to production, update the configuration to use a real email service:

### Gmail Example:
```php
define('PCQ_MAIL_ENABLED', true);
define('PCQ_MAIL_HOST', 'smtp.gmail.com');
define('PCQ_MAIL_PORT', 587);
define('PCQ_MAIL_AUTH', true);
define('PCQ_MAIL_USERNAME', 'your-email@gmail.com');
define('PCQ_MAIL_PASSWORD', 'your-app-password');
define('PCQ_MAIL_ENCRYPTION', 'tls');
define('PCQ_MAIL_FROM_ADDRESS', 'your-email@gmail.com');
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
```

### SendGrid Example:
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

See `SMTP_SETUP_GUIDE.md` for more production email service examples.
