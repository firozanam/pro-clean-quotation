<?php

namespace ProClean\Quotation\Email;

/**
 * SMTP Configuration Class
 * 
 * Configures WordPress wp_mail() to use SMTP (e.g., MailPit, Gmail, etc.)
 * 
 * @package ProClean\Quotation\Email
 * @since 1.1.2
 */
class SMTPConfig {
    
    /**
     * SMTP configuration instance
     * 
     * @var SMTPConfig
     */
    private static $instance = null;
    
    /**
     * SMTP settings
     * 
     * @var array
     */
    private $settings = [];
    
    /**
     * Get instance
     * 
     * @return SMTPConfig
     */
    public static function getInstance(): SMTPConfig {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->loadSettings();
        $this->initHooks();
    }
    
    /**
     * Load SMTP settings
     */
    private function loadSettings(): void {
        // Load from wp-config.php constants first (highest priority)
        if (defined('PCQ_MAIL_HOST')) {
            $this->settings = [
                'enabled' => defined('PCQ_MAIL_ENABLED') ? PCQ_MAIL_ENABLED : true,
                'host' => PCQ_MAIL_HOST,
                'port' => defined('PCQ_MAIL_PORT') ? PCQ_MAIL_PORT : 1025,
                'encryption' => defined('PCQ_MAIL_ENCRYPTION') ? PCQ_MAIL_ENCRYPTION : '',
                'auth' => defined('PCQ_MAIL_AUTH') ? PCQ_MAIL_AUTH : false,
                'username' => defined('PCQ_MAIL_USERNAME') ? PCQ_MAIL_USERNAME : '',
                'password' => defined('PCQ_MAIL_PASSWORD') ? PCQ_MAIL_PASSWORD : '',
                'from_email' => defined('PCQ_MAIL_FROM_ADDRESS') ? PCQ_MAIL_FROM_ADDRESS : '',
                'from_name' => defined('PCQ_MAIL_FROM_NAME') ? PCQ_MAIL_FROM_NAME : '',
            ];
            return;
        }
        
        // Load from WordPress options (lower priority)
        $this->settings = [
            'enabled' => get_option('pcq_smtp_enabled', false),
            'host' => get_option('pcq_smtp_host', 'localhost'),
            'port' => get_option('pcq_smtp_port', 1025),
            'encryption' => get_option('pcq_smtp_encryption', ''),
            'auth' => get_option('pcq_smtp_auth', false),
            'username' => get_option('pcq_smtp_username', ''),
            'password' => get_option('pcq_smtp_password', ''),
            'from_email' => get_option('pcq_smtp_from_email', ''),
            'from_name' => get_option('pcq_smtp_from_name', ''),
        ];
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function initHooks(): void {
        if ($this->isEnabled()) {
            add_action('phpmailer_init', [$this, 'configurePHPMailer']);
            add_filter('wp_mail_from', [$this, 'setMailFrom']);
            add_filter('wp_mail_from_name', [$this, 'setMailFromName']);
        }
    }
    
    /**
     * Check if SMTP is enabled
     * 
     * @return bool
     */
    public function isEnabled(): bool {
        return (bool) $this->settings['enabled'] && !empty($this->settings['host']);
    }
    
    /**
     * Configure PHPMailer to use SMTP
     * 
     * @param \PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance
     */
    public function configurePHPMailer($phpmailer): void {
        // Set mailer to use SMTP
        $phpmailer->isSMTP();
        
        // SMTP configuration
        $phpmailer->Host = $this->settings['host'];
        $phpmailer->Port = $this->settings['port'];
        
        // Enable SMTP authentication if credentials provided
        if ($this->settings['auth'] && !empty($this->settings['username'])) {
            $phpmailer->SMTPAuth = true;
            $phpmailer->Username = $this->settings['username'];
            $phpmailer->Password = $this->settings['password'];
        } else {
            $phpmailer->SMTPAuth = false;
        }
        
        // Set encryption type
        if (!empty($this->settings['encryption'])) {
            $phpmailer->SMTPSecure = $this->settings['encryption'];
        } else {
            // No encryption for local development (MailPit, MailHog, etc.)
            $phpmailer->SMTPSecure = '';
            $phpmailer->SMTPAutoTLS = false;
        }
        
        // Debug level (0 = off, 1 = client messages, 2 = client and server messages)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            $phpmailer->SMTPDebug = 2;
            $phpmailer->Debugoutput = function($str, $level) {
                error_log("PCQ SMTP Debug ($level): $str");
            };
        } else {
            $phpmailer->SMTPDebug = 0;
        }
        
        // Set timeout
        $phpmailer->Timeout = 10;
        
        // Set charset
        $phpmailer->CharSet = 'UTF-8';
        
        // Log configuration for debugging
        error_log(sprintf(
            'PCQ SMTP: Configured with Host=%s, Port=%d, Auth=%s, Encryption=%s',
            $this->settings['host'],
            $this->settings['port'],
            $this->settings['auth'] ? 'Yes' : 'No',
            $this->settings['encryption'] ?: 'None'
        ));
    }
    
    /**
     * Set mail from address
     * 
     * @param string $from_email Default from email
     * @return string From email
     */
    public function setMailFrom(string $from_email): string {
        if (!empty($this->settings['from_email'])) {
            return $this->settings['from_email'];
        }
        return $from_email;
    }
    
    /**
     * Set mail from name
     * 
     * @param string $from_name Default from name
     * @return string From name
     */
    public function setMailFromName(string $from_name): string {
        if (!empty($this->settings['from_name'])) {
            return $this->settings['from_name'];
        }
        return $from_name;
    }
    
    /**
     * Update SMTP settings
     * 
     * @param array $new_settings New settings
     * @return bool Success status
     */
    public function updateSettings(array $new_settings): bool {
        $updated = true;
        
        // Log for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PCQ SMTP: Updating settings - ' . json_encode($new_settings));
        }
        
        foreach ($new_settings as $key => $value) {
            if (isset($this->settings[$key])) {
                $option_key = 'pcq_smtp_' . $key;
                $result = update_option($option_key, $value);
                $updated = $updated && $result;
                $this->settings[$key] = $value;
                
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log("PCQ SMTP: Updated {$option_key} = " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . " (result: " . ($result ? 'success' : 'failed') . ")");
                }
            }
        }
        
        return $updated;
    }
    
    /**
     * Get current SMTP settings
     * 
     * @return array Settings
     */
    public function getSettings(): array {
        return $this->settings;
    }
    
    /**
     * Test SMTP connection
     * 
     * @return array Result with 'success' and 'message' keys
     */
    public function testConnection(): array {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => __('SMTP is not enabled.', 'pro-clean-quotation')
            ];
        }
        
        $test_email = get_option('admin_email');
        $subject = __('SMTP Test Email - Pro Clean Quotation', 'pro-clean-quotation');
        $message = $this->getTestEmailTemplate();
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        $sent = wp_mail($test_email, $subject, $message, $headers);
        
        if ($sent) {
            return [
                'success' => true,
                'message' => sprintf(
                    __('Test email sent successfully to %s. Please check your inbox.', 'pro-clean-quotation'),
                    $test_email
                )
            ];
        } else {
            return [
                'success' => false,
                'message' => __('Failed to send test email. Please check your SMTP settings and error logs.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Get test email HTML template
     * 
     * @return string HTML template
     */
    private function getTestEmailTemplate(): string {
        $company_name = \ProClean\Quotation\Admin\Settings::get('company_name', get_bloginfo('name'));
        $test_time = current_time('F j, Y g:i A');
        
        $html = '<!DOCTYPE html>';
        $html .= '<html lang="en">';
        $html .= '<head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        $html .= '<title>SMTP Test Email</title>';
        $html .= '</head>';
        $html .= '<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f4f7fa;">';
        
        // Main container
        $html .= '<table role="presentation" style="width: 100%; border-collapse: collapse; background-color: #f4f7fa;" cellpadding="0" cellspacing="0">';
        $html .= '<tr><td style="padding: 40px 20px;" align="center">';
        
        // Content card
        $html .= '<table role="presentation" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;" cellpadding="0" cellspacing="0">';
        
        // Header with gradient
        $html .= '<tr>';
        $html .= '<td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">';
        $html .= '<h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 600; letter-spacing: -0.5px;">✓ SMTP Test Successful</h1>';
        $html .= '<p style="margin: 10px 0 0; color: rgba(255, 255, 255, 0.9); font-size: 14px;">' . esc_html($company_name) . '</p>';
        $html .= '</td>';
        $html .= '</tr>';
        
        // Success message
        $html .= '<tr>';
        $html .= '<td style="padding: 40px 30px; text-align: center;">';
        $html .= '<div style="display: inline-block; background-color: #d4edda; border: 2px solid #28a745; border-radius: 50%; width: 80px; height: 80px; line-height: 76px; margin-bottom: 20px;">';
        $html .= '<span style="color: #28a745; font-size: 48px; font-weight: bold;">✓</span>';
        $html .= '</div>';
        $html .= '<h2 style="margin: 0 0 10px; color: #2c3e50; font-size: 24px; font-weight: 600;">Email Configuration Working!</h2>';
        $html .= '<p style="margin: 0; color: #6c757d; font-size: 16px; line-height: 1.6;">Your SMTP settings have been configured correctly and emails are being delivered successfully.</p>';
        $html .= '</td>';
        $html .= '</tr>';
        
        // Configuration details
        $html .= '<tr>';
        $html .= '<td style="padding: 0 30px 30px;">';
        $html .= '<div style="background-color: #f8f9fa; border-left: 4px solid #667eea; border-radius: 6px; padding: 20px;">';
        $html .= '<h3 style="margin: 0 0 15px; color: #495057; font-size: 16px; font-weight: 600;">SMTP Configuration Details</h3>';
        $html .= '<table style="width: 100%; font-size: 14px; line-height: 1.8;" cellpadding="0" cellspacing="0">';
        $html .= '<tr><td style="color: #6c757d; padding: 4px 0;"><strong>Host:</strong></td><td style="color: #495057; text-align: right; padding: 4px 0;">' . esc_html($this->settings['host']) . '</td></tr>';
        $html .= '<tr><td style="color: #6c757d; padding: 4px 0;"><strong>Port:</strong></td><td style="color: #495057; text-align: right; padding: 4px 0;">' . esc_html($this->settings['port']) . '</td></tr>';
        $html .= '<tr><td style="color: #6c757d; padding: 4px 0;"><strong>Encryption:</strong></td><td style="color: #495057; text-align: right; padding: 4px 0;">' . ($this->settings['encryption'] ?: 'None') . '</td></tr>';
        $html .= '<tr><td style="color: #6c757d; padding: 4px 0;"><strong>Authentication:</strong></td><td style="color: #495057; text-align: right; padding: 4px 0;">' . ($this->settings['auth'] ? 'Enabled' : 'Disabled') . '</td></tr>';
        $html .= '<tr><td style="color: #6c757d; padding: 4px 0;"><strong>Test Time:</strong></td><td style="color: #495057; text-align: right; padding: 4px 0;">' . esc_html($test_time) . '</td></tr>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
        
        // Next steps
        $html .= '<tr>';
        $html .= '<td style="padding: 0 30px 30px;">';
        $html .= '<div style="background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 6px; padding: 20px;">';
        $html .= '<h3 style="margin: 0 0 10px; color: #856404; font-size: 14px; font-weight: 600;">📋 Next Steps</h3>';
        $html .= '<p style="margin: 0; color: #856404; font-size: 13px; line-height: 1.6;">Your email system is now ready to send quote confirmations, booking notifications, and customer communications automatically.</p>';
        $html .= '</div>';
        $html .= '</td>';
        $html .= '</tr>';
        
        // Footer
        $html .= '<tr>';
        $html .= '<td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #dee2e6;">';
        $html .= '<p style="margin: 0 0 10px; color: #6c757d; font-size: 13px;"><strong>' . esc_html($company_name) . '</strong></p>';
        $html .= '<p style="margin: 0; color: #adb5bd; font-size: 12px;">This is an automated test email from Pro Clean Quotation System</p>';
        $html .= '<p style="margin: 10px 0 0; color: #adb5bd; font-size: 11px;">Powered by Pro Clean Quotation v' . PCQ_VERSION . '</p>';
        $html .= '</td>';
        $html .= '</tr>';
        
        $html .= '</table>';
        $html .= '</td></tr>';
        $html .= '</table>';
        
        $html .= '</body>';
        $html .= '</html>';
        
        return $html;
    }
    
    /**
     * Configure SMTP from wp-config.php constants
     * 
     * Usage in wp-config.php:
     * 
     * define('PCQ_MAIL_ENABLED', true);
     * define('PCQ_MAIL_HOST', 'localhost');
     * define('PCQ_MAIL_PORT', 1025);
     * define('PCQ_MAIL_ENCRYPTION', ''); // '', 'ssl', or 'tls'
     * define('PCQ_MAIL_AUTH', false);
     * define('PCQ_MAIL_USERNAME', 'null');
     * define('PCQ_MAIL_PASSWORD', 'null');
     * define('PCQ_MAIL_FROM_ADDRESS', 'info@webblymedia.se');
     * define('PCQ_MAIL_FROM_NAME', 'We Cleaning');
     */
    public static function getConfigurationInstructions(): string {
        return <<<'EOT'
To configure SMTP for Pro Clean Quotation System, add the following to your wp-config.php file:

// Enable SMTP for Pro Clean Quotation
define('PCQ_MAIL_ENABLED', true);
define('PCQ_MAIL_HOST', 'localhost');          // SMTP server address
define('PCQ_MAIL_PORT', 1025);                 // SMTP port (1025 for MailPit, 587 for TLS, 465 for SSL)
define('PCQ_MAIL_ENCRYPTION', '');             // '' (none), 'tls', or 'ssl'
define('PCQ_MAIL_AUTH', false);                // true if authentication required
define('PCQ_MAIL_USERNAME', '');               // SMTP username (if auth required)
define('PCQ_MAIL_PASSWORD', '');               // SMTP password (if auth required)
define('PCQ_MAIL_FROM_ADDRESS', 'info@webblymedia.se');  // From email address
define('PCQ_MAIL_FROM_NAME', 'We Cleaning');   // From name

MailPit Configuration (for local development):
- Host: localhost
- Port: 1025
- Encryption: none
- Auth: false

Gmail SMTP Configuration:
- Host: smtp.gmail.com
- Port: 587
- Encryption: tls
- Auth: true
- Username: your-email@gmail.com
- Password: your-app-password

SendGrid SMTP Configuration:
- Host: smtp.sendgrid.net
- Port: 587
- Encryption: tls
- Auth: true
- Username: apikey
- Password: your-sendgrid-api-key
EOT;
    }
}
