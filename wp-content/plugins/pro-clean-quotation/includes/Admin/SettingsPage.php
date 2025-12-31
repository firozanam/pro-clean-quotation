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
                <a href="#form" class="pcq-nav-tab <?php echo $active_tab === 'form' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('Form', 'pro-clean-quotation'); ?>
                </a>
                <a href="#integration" class="pcq-nav-tab <?php echo $active_tab === 'integration' ? 'pcq-nav-tab-active' : ''; ?>">
                    <?php _e('Integration', 'pro-clean-quotation'); ?>
                </a>
            </nav>
            
            <form method="post" action="" class="pcq-settings-form">
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
                                <textarea name="service_area_postcodes" rows="3"><?php echo esc_textarea(implode(', ', Settings::get('service_area_postcodes', ['1000', '2000', '3000']))); ?></textarea>
                                <p class="description"><?php _e('Postal codes you service (comma-separated). Leave empty to serve all areas.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Pricing Settings -->
                <div id="pricing" class="pcq-tab-content" style="display: none;">
                    <h2><?php _e('Service Pricing', 'pro-clean-quotation'); ?></h2>
                    <table class="pcq-form-table">
                        <tr>
                            <th><?php _e('Façade Base Rate (€)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="facade_base_rate" value="<?php echo esc_attr(Settings::get('facade_base_rate', 150.00)); ?>" step="0.01" min="0" required>
                                <p class="description"><?php _e('Base price for façade cleaning services.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Façade Per SQM (€)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="facade_per_sqm" value="<?php echo esc_attr(Settings::get('facade_per_sqm', 2.50)); ?>" step="0.01" min="0" required>
                                <p class="description"><?php _e('Price per square meter for façade cleaning.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Roof Base Rate (€)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="roof_base_rate" value="<?php echo esc_attr(Settings::get('roof_base_rate', 200.00)); ?>" step="0.01" min="0" required>
                                <p class="description"><?php _e('Base price for roof cleaning services.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th><?php _e('Roof Per SQM (€)', 'pro-clean-quotation'); ?></th>
                            <td>
                                <input type="number" name="roof_per_sqm" value="<?php echo esc_attr(Settings::get('roof_per_sqm', 3.00)); ?>" step="0.01" min="0" required>
                                <p class="description"><?php _e('Price per square meter for roof cleaning.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
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
                
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Settings', 'pro-clean-quotation'); ?>">
                </p>
            </form>
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
        $postcodes = array_filter($postcodes);
        Settings::update('service_area_postcodes', $postcodes);
        
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