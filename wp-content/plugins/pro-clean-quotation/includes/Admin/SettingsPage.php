<?php

namespace ProClean\Quotation\Admin;

/**
 * Settings Page Class
 * 
 * @package ProClean\Quotation\Admin
 * @since 1.0.0
 */
class SettingsPage {
    
    /**
     * Render settings page
     */
    public function render(): void {
        $active_tab = $_GET['tab'] ?? 'general';
        
        ?>
        <div class="wrap pcq-settings-page">
            <h1><?php _e('Pro Clean Quotation Settings', 'pro-clean-quotation'); ?></h1>
            
            <nav class="pcq-nav-tab-wrapper">
                <a href="#general" class="pcq-nav-tab <?php echo $active_tab === 'general' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('General', 'pro-clean-quotation'); ?>
                </a>
                <a href="#pricing" class="pcq-nav-tab <?php echo $active_tab === 'pricing' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('Pricing', 'pro-clean-quotation'); ?>
                </a>
                <a href="#email" class="pcq-nav-tab <?php echo $active_tab === 'email' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('Email', 'pro-clean-quotation'); ?>
                </a>
                <a href="#smtp" class="pcq-nav-tab <?php echo $active_tab === 'smtp' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('SMTP', 'pro-clean-quotation'); ?>
                </a>
                <a href="#form" class="pcq-nav-tab <?php echo $active_tab === 'form' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('Form', 'pro-clean-quotation'); ?>
                </a>
                <a href="#integration" class="pcq-nav-tab <?php echo $active_tab === 'integration' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('Integration', 'pro-clean-quotation'); ?>
                </a>
                <a href="#update" class="pcq-nav-tab <?php echo $active_tab === 'update' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('Update', 'pro-clean-quotation'); ?>
                </a>
            </nav>
            
            <form method="post" action="" class="pcq-settings-form" id="pcq-main-settings-form">
                <?php wp_nonce_field('pcq_save_settings'); ?>
                <input type="hidden" name="action" value="save">
                
                <!-- General Settings -->
                <div id="general" class="pcq-tab-content">
                    <h2><?php _e('Company Information', 'pro-clean-quotation'); ?></h2>
                    <table class="pcq-form-table">
                        <tr>
                            <th><?php _e('Company Name', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="text" name="company_name" value="<?php echo esc_attr(Settings::get('company_name', get_bloginfo('name'))); ?>" required>
                                <p class="description"><?php _e('Your company name as it appears on quotes and emails.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Company Email', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="email" name="company_email" value="<?php echo esc_attr(Settings::get('company_email', get_option('admin_email'))); ?>" required>
                                <p class="description"><?php _e('Main contact email address.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Company Phone', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="tel" name="company_phone" value="<?php echo esc_attr(Settings::get('company_phone', '')); ?>">
                                <p class="description"><?php _e('Contact phone number for customer inquiries.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Service Area', 'pro-clean-quotation'); ?></th>
                            <td>
                                <?php 
                                $service_areas = Settings::get('service_area_postcodes', []);
                                $service_areas_text = !empty($service_areas) ? implode(', ', $service_areas) : '';
                                $total_areas = count($service_areas);
                                ?>
                                <textarea name="service_area_postcodes" rows="3" placeholder="<?php esc_attr_e('Leave empty to accept all Spanish postal codes (01001-52999)', 'pro-clean-quotation'); ?>"><?php echo esc_textarea($service_areas_text); ?></textarea>
                                <p class="description">
                                    <?php _e('Enter postal codes or ranges you service (comma-separated). Leave empty to serve all of Spain.', 'pro-clean-quotation'); ?><br>
                                    <strong><?php _e('Examples:', 'pro-clean-quotation'); ?></strong><br>
                                    • <?php _e('Specific code:', 'pro-clean-quotation'); ?> <code>28001</code><br>
                                    • <?php _e('Range:', 'pro-clean-quotation'); ?> <code>29600-29699</code><br>
                                    • <?php _e('Wildcard:', 'pro-clean-quotation'); ?> <code>296**</code> <?php _e('(matches 29600-29699)', 'pro-clean-quotation'); ?><br>
                                    • <?php _e('Multiple areas:', 'pro-clean-quotation'); ?> <code>28***, 29***, 08***</code>
                                </p>
                                
                                <?php if ($total_areas > 0): ?>
                                <div class="pcq-service-area-status" style="margin-top: 10px; padding: 10px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                                    <strong><?php _e('⚠️ Service Restricted:', 'pro-clean-quotation'); ?></strong>
                                    <?php printf(__('Currently serving only %d specific postal code(s)/range(s). All other postal codes will be rejected.', 'pro-clean-quotation'), $total_areas); ?>
                                    <br>
                                    <button type="button" id="pcq-clear-service-areas" class="button button-secondary" style="margin-top: 8px;">
                                        <?php _e('Clear All - Accept All Spain', 'pro-clean-quotation'); ?>
                                    </button>
                                </div>
                                <?php else: ?>
                                <div class="pcq-service-area-status" style="margin-top: 10px; padding: 10px; background: #d4edda; border-left: 4px solid #28a745; border-radius: 4px;">
                                    <strong><?php _e('✓ All Spain Enabled:', 'pro-clean-quotation'); ?></strong>
                                    <?php _e('Accepting all valid Spanish postal codes (01001-52999).', 'pro-clean-quotation'); ?>
                                </div>
                                <?php endif; ?>
                                
                                <script>
                                jQuery(document).ready(function($) {
                                    $('#pcq-clear-service-areas').on('click', function(e) {
                                        e.preventDefault();
                                        if (confirm('<?php echo esc_js(__('Are you sure you want to clear all service area restrictions? This will allow quotes from all of Spain.', 'pro-clean-quotation')); ?>')) {
                                            $('textarea[name="service_area_postcodes"]').val('');
                                            $(this).closest('.pcq-service-area-status').fadeOut();
                                            alert('<?php echo esc_js(__('Service area cleared. Please click "Save Settings" to apply changes.', 'pro-clean-quotation')); ?>');
                                        }
                                    });
                                });
                                </script>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Pricing Settings -->
                <div id="pricing" class="pcq-tab-content" style="display: none;">
                    <h2><?php _e('Service Pricing', 'pro-clean-quotation'); ?></h2>
                    <table class="pcq-form-table">
                        <tr>
                            <th><?php _e('Minimum Quote Value (€)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="minimum_quote_value" value="<?php echo esc_attr(Settings::get('minimum_quote_value', 100.00)); ?>" step="0.01" min="0" required>
                                <p class="description"><?php _e('Minimum amount for any quote.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('VAT Rate (%)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="tax_rate" value="<?php echo esc_attr(Settings::get('tax_rate', 21.0)); ?>" step="0.1" min="0" max="100" required>
                                <p class="description"><?php _e('Tax rate to apply to quotes (e.g., 21 for 21% VAT).', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Email Settings -->
                <div id="email" class="pcq-tab-content" style="display: none;">
                    <h2><?php _e('Email Configuration', 'pro-clean-quotation'); ?></h2>
                    <table class="pcq-form-table">
                        <tr>
                            <th><?php _e('Enable Email Notifications', 'pro-clean-quotation'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="email_notifications_enabled" value="1" <?php checked(Settings::get('email_notifications_enabled', true)); ?>>
                                    <?php _e('Send automated email notifications', 'pro-clean-quotation'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('From Name', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="text" name="email_from_name" value="<?php echo esc_attr(Settings::get('email_from_name', get_bloginfo('name'))); ?>" required>
                                <p class="description"><?php _e('Name that appears as the sender of emails.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('From Email', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="email" name="email_from_address" value="<?php echo esc_attr(Settings::get('email_from_address', get_option('admin_email'))); ?>" required>
                                <p class="description"><?php _e('Email address that appears as the sender.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Admin Notification Email', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="email" name="admin_notification_email" value="<?php echo esc_attr(Settings::get('admin_notification_email', get_option('admin_email'))); ?>" required>
                                <p class="description"><?php _e('Email address to receive new quote notifications.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- SMTP Settings -->
                <div id="smtp" class="pcq-tab-content" style="display: none;">
                    <?php
                    $smtp_config = \ProClean\Quotation\Email\SMTPConfig::getInstance();
                    $smtp_settings = $smtp_config->getSettings();
                    $smtp_enabled = $smtp_config->isEnabled();
                    $configured_via_constants = defined('PCQ_MAIL_HOST');
                    ?>
                    
                    <div class="pcq-smtp-settings">
                        <!-- Header with Status Badge -->
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 15px; border-bottom: 2px solid #e5e7eb;">
                            <h2 style="margin: 0; font-size: 20px; color: #1f2937;"><?php _e('SMTP Configuration', 'pro-clean-quotation'); ?></h2>
                            <div>
                                <?php if ($smtp_enabled): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #d1fae5; color: #065f46; border-radius: 6px; font-size: 13px; font-weight: 600;">
                                        <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></span>
                                        <?php _e('Active', 'pro-clean-quotation'); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #fee2e2; color: #991b1b; border-radius: 6px; font-size: 13px; font-weight: 600;">
                                        <span style="width: 8px; height: 8px; background: #ef4444; border-radius: 50%;"></span>
                                        <?php _e('Disabled', 'pro-clean-quotation'); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if ($configured_via_constants): ?>
                        <div style="background: #eff6ff; border-left: 4px solid #3b82f6; padding: 12px 16px; margin-bottom: 20px; border-radius: 4px;">
                            <p style="margin: 0; font-size: 13px; color: #1e40af;">
                                <strong><?php _e('Configuration locked:', 'pro-clean-quotation'); ?></strong> 
                                <?php _e('Settings are managed via wp-config.php constants', 'pro-clean-quotation'); ?>
                            </p>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Main Configuration Card -->
                        <div style="background: #ffffff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 24px; margin-bottom: 16px;">
                            <!-- Grid Layout for Form Fields -->
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                                <!-- Enable SMTP -->
                                <div style="grid-column: 1 / -1;">
                                    <label style="display: flex; align-items: center; cursor: pointer;">
                                        <input type="checkbox" name="smtp_enabled" value="1" 
                                            <?php checked($smtp_settings['enabled']); ?>
                                            <?php disabled($configured_via_constants); ?>
                                            style="margin-right: 8px;">
                                        <span style="font-weight: 600; color: #374151;"><?php _e('Use SMTP for sending emails', 'pro-clean-quotation'); ?></span>
                                    </label>
                                </div>
                                
                                <!-- Host -->
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                                        <?php _e('SMTP Host', 'pro-clean-quotation'); ?>
                                    </label>
                                    <input type="text" name="smtp_host" 
                                        value="<?php echo esc_attr($smtp_settings['host']); ?>"
                                        placeholder="localhost"
                                        <?php disabled($configured_via_constants); ?>
                                        style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                </div>
                                
                                <!-- Port -->
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                                        <?php _e('Port', 'pro-clean-quotation'); ?>
                                    </label>
                                    <input type="number" name="smtp_port" 
                                        value="<?php echo esc_attr($smtp_settings['port']); ?>"
                                        placeholder="1025"
                                        min="1" max="65535"
                                        <?php disabled($configured_via_constants); ?>
                                        style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                </div>
                                
                                <!-- Encryption -->
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                                        <?php _e('Encryption', 'pro-clean-quotation'); ?>
                                    </label>
                                    <select name="smtp_encryption" <?php disabled($configured_via_constants); ?>
                                        style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white;">
                                        <option value="" <?php selected($smtp_settings['encryption'], ''); ?>><?php _e('None', 'pro-clean-quotation'); ?></option>
                                        <option value="tls" <?php selected($smtp_settings['encryption'], 'tls'); ?>>TLS</option>
                                        <option value="ssl" <?php selected($smtp_settings['encryption'], 'ssl'); ?>>SSL</option>
                                    </select>
                                </div>
                                
                                <!-- Authentication -->
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                                        <?php _e('Authentication', 'pro-clean-quotation'); ?>
                                    </label>
                                    <label style="display: flex; align-items: center; padding: 8px 0; cursor: pointer;">
                                        <input type="checkbox" name="smtp_auth" value="1" 
                                            <?php checked($smtp_settings['auth']); ?>
                                            <?php disabled($configured_via_constants); ?>
                                            style="margin-right: 8px;">
                                        <span style="font-size: 14px; color: #6b7280;"><?php _e('Enable authentication', 'pro-clean-quotation'); ?></span>
                                    </label>
                                </div>
                                
                                <!-- Username -->
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                                        <?php _e('Username', 'pro-clean-quotation'); ?>
                                    </label>
                                    <input type="text" name="smtp_username" 
                                        value="<?php echo esc_attr($smtp_settings['username']); ?>"
                                        autocomplete="off"
                                        <?php disabled($configured_via_constants); ?>
                                        style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                </div>
                                
                                <!-- Password -->
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                                        <?php _e('Password', 'pro-clean-quotation'); ?>
                                    </label>
                                    <input type="password" name="smtp_password" 
                                        value="<?php echo esc_attr($smtp_settings['password']); ?>"
                                        autocomplete="new-password"
                                        placeholder="<?php echo !empty($smtp_settings['password']) ? '••••••••' : ''; ?>"
                                        <?php disabled($configured_via_constants); ?>
                                        style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                </div>
                                
                                <!-- From Email -->
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                                        <?php _e('From Email', 'pro-clean-quotation'); ?>
                                    </label>
                                    <input type="email" name="smtp_from_email" 
                                        value="<?php echo esc_attr($smtp_settings['from_email']); ?>"
                                        placeholder="info@webblymedia.se"
                                        <?php disabled($configured_via_constants); ?>
                                        style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                </div>
                                
                                <!-- From Name -->
                                <div>
                                    <label style="display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px;">
                                        <?php _e('From Name', 'pro-clean-quotation'); ?>
                                    </label>
                                    <input type="text" name="smtp_from_name" 
                                        value="<?php echo esc_attr($smtp_settings['from_name']); ?>"
                                        placeholder="We Cleaning"
                                        <?php disabled($configured_via_constants); ?>
                                        style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px;">
                                </div>
                            </div>
                            
                            <?php if (!$configured_via_constants): ?>
                            <!-- Test Email Button -->
                            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                <button type="button" id="pcq-test-smtp" class="button button-secondary" 
                                    style="padding: 8px 16px; font-size: 13px;">
                                    <?php _e('Send Test Email', 'pro-clean-quotation'); ?>
                                </button>
                                <span id="pcq-smtp-test-result" style="margin-left: 12px; font-size: 13px;"></span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Quick Reference Cards -->
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px;">
                            <!-- MailPit Card -->
                            <div style="background: #fef3c7; border: 1px solid #fbbf24; border-radius: 6px; padding: 16px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                    <span style="font-size: 16px;">🧪</span>
                                    <strong style="font-size: 13px; color: #92400e;"><?php _e('Local Development', 'pro-clean-quotation'); ?></strong>
                                </div>
                                <div style="font-size: 12px; color: #78350f; line-height: 1.6;">
                                    <div><code style="background: rgba(255,255,255,0.5); padding: 2px 6px; border-radius: 3px;">localhost:1025</code> • No encryption</div>
                                    <a href="http://localhost:8025" target="_blank" style="color: #92400e; text-decoration: underline; margin-top: 6px; display: inline-block;"><?php _e('Open MailPit', 'pro-clean-quotation'); ?> →</a>
                                </div>
                            </div>
                            
                            <!-- Production Examples -->
                            <div style="background: #dbeafe; border: 1px solid #60a5fa; border-radius: 6px; padding: 16px;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                    <span style="font-size: 16px;">🚀</span>
                                    <strong style="font-size: 13px; color: #1e40af;"><?php _e('Production', 'pro-clean-quotation'); ?></strong>
                                </div>
                                <div style="font-size: 12px; color: #1e3a8a; line-height: 1.6;">
                                    <div><strong>Gmail:</strong> smtp.gmail.com:587 (TLS)</div>
                                    <div><strong>SendGrid:</strong> smtp.sendgrid.net:587 (TLS)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!$configured_via_constants): ?>
                    <script>
                    jQuery(document).ready(function($) {
                        $('#pcq-test-smtp').on('click', function(e) {
                            e.preventDefault();
                            var $button = $(this);
                            var $result = $('#pcq-smtp-test-result');
                            
                            $button.prop('disabled', true).text('<?php echo esc_js(__('Sending...', 'pro-clean-quotation')); ?>');
                            $result.html('');
                            
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'pcq_test_smtp',
                                    nonce: '<?php echo wp_create_nonce('pcq_test_smtp'); ?>'
                                },
                                success: function(response) {
                                    if (response.success) {
                                        $result.html('<span style="color: #059669; font-weight: 600;">✓ ' + response.data.message + '</span>');
                                    } else {
                                        $result.html('<span style="color: #dc2626; font-weight: 600;">✗ ' + response.data.message + '</span>');
                                    }
                                },
                                error: function() {
                                    $result.html('<span style="color: #dc2626; font-weight: 600;">✗ <?php echo esc_js(__('AJAX error occurred', 'pro-clean-quotation')); ?></span>');
                                },
                                complete: function() {
                                    $button.prop('disabled', false).text('<?php echo esc_js(__('Send Test Email', 'pro-clean-quotation')); ?>');
                                }
                            });
                        });
                    });
                    </script>
                    <?php endif; ?>
                </div>
                
                <!-- Form Settings -->
                <div id="form" class="pcq-tab-content" style="display: none;">
                    <h2><?php _e('Quote Form Configuration', 'pro-clean-quotation'); ?></h2>
                    <table class="pcq-form-table">
                        <tr>
                            <th><?php _e('Rate Limiting', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="rate_limit_submissions" value="<?php echo esc_attr(Settings::get('rate_limit_submissions', 5)); ?>" min="1" max="50" required>
                                <p class="description"><?php _e('Maximum submissions per 5 minutes per IP address.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Quote Validity (Days)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="quote_validity_days" value="<?php echo esc_attr(Settings::get('quote_validity_days', 30)); ?>" min="1" max="365" required>
                                <p class="description"><?php _e('Number of days a quote remains valid.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Integration Settings -->
                <div id="integration" class="pcq-tab-content" style="display: none;">
                    <h2><?php _e('Plugin Integration', 'pro-clean-quotation'); ?></h2>
                    <table class="pcq-form-table">
                        <tr>
                            <th><?php _e('MotoPress Appointment', 'pro-clean-quotation'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="motopress_integration_enabled" value="1" <?php checked(Settings::get('motopress_integration_enabled', true)); ?>>
                                    <?php _e('Enable MotoPress Appointment integration', 'pro-clean-quotation'); ?>
                                </label>
                                <p class="description">
                                    <?php if (is_plugin_active('motopress-appointment-lite/motopress-appointment.php')): ?>
                                        <span style="color: green;">✓ <?php _e('MotoPress Appointment Lite is active', 'pro-clean-quotation'); ?></span>
                                    <?php else: ?>
                                        <span style="color: orange;">⚠ <?php _e('MotoPress Appointment Lite is not installed', 'pro-clean-quotation'); ?></span>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('WooCommerce (Optional)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="woocommerce_integration_enabled" value="1" <?php checked(Settings::get('woocommerce_integration_enabled', false)); ?>>
                                    <?php _e('Enable WooCommerce integration for online payments', 'pro-clean-quotation'); ?>
                                </label>
                                <p class="description">
                                    <?php if (is_plugin_active('woocommerce/woocommerce.php')): ?>
                                        <span style="color: green;">✓ <?php _e('WooCommerce is active - Online payments available', 'pro-clean-quotation'); ?></span>
                                    <?php else: ?>
                                        <span style="color: #666;">ℹ <?php _e('WooCommerce not installed - Cash/Bank transfer payments only', 'pro-clean-quotation'); ?></span>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                        <?php if (is_plugin_active('woocommerce/woocommerce.php')): ?>
                        <tr>
                            <th><?php _e('Online Payments', 'pro-clean-quotation'); ?></th>
                            <td>
                                <label>
                                    <input type="checkbox" name="enable_online_payments" value="1" <?php checked(Settings::get('enable_online_payments', false)); ?>>
                                    <?php _e('Allow customers to pay deposits online', 'pro-clean-quotation'); ?>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Deposit Percentage (%)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="deposit_percentage" value="<?php echo esc_attr(Settings::get('deposit_percentage', 20.0)); ?>" min="0" max="100" step="0.1">
                                <p class="description"><?php _e('Percentage of total amount required as deposit.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <p class="submit" id="pcq-main-submit" style="<?php echo $active_tab === 'update' ? 'display: none;' : ''; ?>">
                    <input type="submit" class="button-primary" value="<?php _e('Save Settings', 'pro-clean-quotation'); ?>">
                </p>
            </form>
            
            <!-- Update Settings (outside main form due to multipart/form-data) -->
            <div id="update" class="pcq-tab-content" style="display: <?php echo $active_tab === 'update' ? 'block' : 'none'; ?>; padding: 0 20px;">
                <?php
                // Initialize the plugin updater
                $updater = PluginUpdater::getInstance();
                $plugin_version = PCQ_VERSION;
                
                // Check for update success message
                if (isset($_GET['updated']) && $_GET['updated'] == '1') {
                    echo '<div class="notice notice-success is-dismissible"><p>' .
                         __('Plugin updated successfully!', 'pro-clean-quotation') .
                         '</p></div>';
                }
                ?>
                
                <h2><?php _e('Manual Plugin Update', 'pro-clean-quotation'); ?></h2>
                
                <div class="pcq-update-info" style="background: #f8fafc; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 8px; max-width: 800px;">
                    <h3 style="margin-top: 0; color: #1e293b;"><?php _e('Current Installation', 'pro-clean-quotation'); ?></h3>
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row" style="width: 180px; color: #64748b;"><?php _e('Plugin Version', 'pro-clean-quotation'); ?></th>
                            <td><code style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 14px;"><?php echo esc_html($plugin_version); ?></code></td>
                        </tr>
                        <tr>
                            <th scope="row" style="color: #64748b;"><?php _e('Plugin Directory', 'pro-clean-quotation'); ?></th>
                            <td><code style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px; font-size: 12px;"><?php echo esc_html(PCQ_PLUGIN_DIR); ?></code></td>
                        </tr>
                    </table>
                </div>
                
                <div class="pcq-upload-form" style="background: #fff; padding: 20px; margin: 20px 0; border: 1px solid #e2e8f0; border-radius: 8px; max-width: 800px;">
                    <h3 style="margin-top: 0; color: #1e293b;"><?php _e('Upload Plugin Package', 'pro-clean-quotation'); ?></h3>
                    <p class="description" style="color: #64748b; margin-bottom: 20px;">
                        <?php _e('Upload a ZIP file containing the plugin package to update or reinstall the plugin. The ZIP file must contain a "pro-clean-quotation" folder at the root level.', 'pro-clean-quotation'); ?>
                    </p>
                    
                    <form method="post" enctype="multipart/form-data" id="pcq-update-form">
                        <?php wp_nonce_field('pcq_plugin_update', 'pcq_plugin_update_nonce'); ?>
                        
                        <table class="form-table" role="presentation">
                            <tr>
                                <th scope="row" style="width: 180px;">
                                    <label for="pcq_plugin_zip"><?php _e('Plugin ZIP File', 'pro-clean-quotation'); ?></label>
                                </th>
                                <td>
                                    <input type="file"
                                           name="pcq_plugin_zip"
                                           id="pcq_plugin_zip"
                                           accept=".zip,application/zip,application/x-zip-compressed"
                                           required
                                           class="regular-text"
                                           style="padding: 8px; border: 1px solid #e2e8f0; border-radius: 4px;">
                                    <p class="description" style="color: #64748b; margin-top: 8px;">
                                        <?php _e('Maximum file size: 50MB. Accepted format: ZIP', 'pro-clean-quotation'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit" style="margin-top: 20px;">
                            <button type="submit"
                                    name="pcq_plugin_upload"
                                    value="1"
                                    class="button button-primary"
                                    onclick="return confirm('<?php echo esc_js(__('Are you sure you want to update the plugin? This will overwrite all existing plugin files.', 'pro-clean-quotation')); ?>');">
                                <?php _e('Update Plugin', 'pro-clean-quotation'); ?>
                            </button>
                        </p>
                    </form>
                </div>
                
                <div class="pcq-update-warning" style="background: #fffbeb; padding: 20px; margin: 20px 0 40px 0; border: 1px solid #fbbf24; border-radius: 8px; max-width: 800px;">
                    <h3 style="color: #92400e; margin-top: 0;">
                        <span class="dashicons dashicons-warning" style="color: #f59e0b;"></span>
                        <?php _e('Important Notes', 'pro-clean-quotation'); ?>
                    </h3>
                    <ul style="list-style-type: disc; margin-left: 20px; color: #78350f;">
                        <li><?php _e('This will completely replace all plugin files in the current installation.', 'pro-clean-quotation'); ?></li>
                        <li><?php _e('Your settings and database data will be preserved.', 'pro-clean-quotation'); ?></li>
                        <li><?php _e('A backup of the current plugin will be created before updating.', 'pro-clean-quotation'); ?></li>
                        <li><?php _e('The ZIP file must contain the "pro-clean-quotation" folder at its root.', 'pro-clean-quotation'); ?></li>
                        <li><?php _e('After update, you may need to clear any caching (OPcache, etc.).', 'pro-clean-quotation'); ?></li>
                    </ul>
                </div>
                
                <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $('#pcq-update-form').on('submit', function() {
                        var fileInput = $('#pcq_plugin_zip')[0];
                        if (fileInput.files.length > 0) {
                            var file = fileInput.files[0];
                            var maxSize = 50 * 1024 * 1024; // 50MB
                            
                            if (file.size > maxSize) {
                                alert('<?php echo esc_js(__('File size exceeds the maximum limit of 50MB.', 'pro-clean-quotation')); ?>');
                                return false;
                            }
                            
                            if (!file.name.endsWith('.zip')) {
                                alert('<?php echo esc_js(__('Please select a ZIP file.', 'pro-clean-quotation')); ?>');
                                return false;
                            }
                        }
                    });
                });
                </script>
            </div>
        </div>
        <?php
    }
    
    /**
     * Save settings
     * 
     * @param array $data Form data
     */
    public function save(array $data): void {
        // Company Information
        Settings::update('company_name', sanitize_text_field($data['company_name'] ?? ''));
        Settings::update('company_email', sanitize_email($data['company_email'] ?? ''));
        Settings::update('company_phone', sanitize_text_field($data['company_phone'] ?? ''));
        
        // Service Area
        $postcodes = array_map('trim', explode(',', $data['service_area_postcodes'] ?? ''));
        // Filter out empty strings and reindex array to ensure clean data
        $postcodes = array_values(array_filter($postcodes, function($code) {
            return $code !== '' && $code !== null;
        }));
        Settings::update('service_area_postcodes', $postcodes);
        
        // Log service area update for debugging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PCQ: Service area updated - Count: ' . count($postcodes) . ' - Data: ' . json_encode($postcodes));
        }
        
        // Pricing
        Settings::update('facade_base_rate', floatval($data['facade_base_rate'] ?? 150));
        Settings::update('facade_per_sqm', floatval($data['facade_per_sqm'] ?? 2.50));
        Settings::update('roof_base_rate', floatval($data['roof_base_rate'] ?? 200));
        Settings::update('roof_per_sqm', floatval($data['roof_per_sqm'] ?? 3.00));
        Settings::update('minimum_quote_value', floatval($data['minimum_quote_value'] ?? 100));
        Settings::update('tax_rate', floatval($data['tax_rate'] ?? 21));
        
        // Email
        Settings::update('email_notifications_enabled', !empty($data['email_notifications_enabled']));
        Settings::update('email_from_name', sanitize_text_field($data['email_from_name'] ?? ''));
        Settings::update('email_from_address', sanitize_email($data['email_from_address'] ?? ''));
        Settings::update('admin_notification_email', sanitize_email($data['admin_notification_email'] ?? ''));
        
        // SMTP Configuration (only save if not configured via constants)
        if (!defined('PCQ_MAIL_HOST')) {
            // Log for debugging
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PCQ: Saving SMTP settings');
                error_log('PCQ: SMTP enabled checkbox value: ' . (isset($data['smtp_enabled']) ? 'checked' : 'unchecked'));
            }
            
            $smtp_config = \ProClean\Quotation\Email\SMTPConfig::getInstance();
            $smtp_result = $smtp_config->updateSettings([
                'enabled' => !empty($data['smtp_enabled']),
                'host' => sanitize_text_field($data['smtp_host'] ?? 'localhost'),
                'port' => intval($data['smtp_port'] ?? 1025),
                'encryption' => sanitize_text_field($data['smtp_encryption'] ?? ''),
                'auth' => !empty($data['smtp_auth']),
                'username' => sanitize_text_field($data['smtp_username'] ?? ''),
                'password' => $data['smtp_password'] ?? '',
                'from_email' => sanitize_email($data['smtp_from_email'] ?? ''),
                'from_name' => sanitize_text_field($data['smtp_from_name'] ?? ''),
            ]);
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('PCQ: SMTP settings save result: ' . ($smtp_result ? 'success' : 'failed'));
            }
        }
        
        // Form
        Settings::update('rate_limit_submissions', intval($data['rate_limit_submissions'] ?? 5));
        Settings::update('quote_validity_days', intval($data['quote_validity_days'] ?? 30));
        
        // Integration
        Settings::update('motopress_integration_enabled', !empty($data['motopress_integration_enabled']));
        Settings::update('woocommerce_integration_enabled', !empty($data['woocommerce_integration_enabled']));
        Settings::update('enable_online_payments', !empty($data['enable_online_payments']));
        Settings::update('deposit_percentage', floatval($data['deposit_percentage'] ?? 20));
        
        // Clear pricing cache
        \ProClean\Quotation\Services\PricingEngine::getInstance()->clearCache();
    }
}