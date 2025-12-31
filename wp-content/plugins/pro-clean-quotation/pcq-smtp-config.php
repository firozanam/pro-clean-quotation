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
