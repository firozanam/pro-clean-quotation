<?php

namespace ProClean\Quotation\Admin;

/**
 * Settings Management Class
 * 
 * @package ProClean\Quotation\Admin
 * @since 1.0.0
 */
class Settings {
    
    /**
     * Settings instance
     * 
     * @var Settings
     */
    private static $instance = null;
    
    /**
     * Settings prefix
     */
    const PREFIX = 'pcq_';
    
    /**
     * Get instance
     * 
     * @return Settings
     */
    public static function getInstance(): Settings {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Settings are initialized in Plugin class
    }
    
    /**
     * Set default options
     */
    public static function setDefaults(): void {
        $defaults = [
            // Company Information
            'company_name' => get_bloginfo('name'),
            'company_email' => get_option('admin_email'),
            'company_phone' => '',
            'company_address' => '',
            'company_logo' => '',
            
            // Service Pricing - Base Rates
            'facade_base_rate' => 150.00,
            'facade_per_sqm' => 2.50,
            'facade_per_linear_meter' => 5.00,
            'roof_base_rate' => 200.00,
            'roof_per_sqm' => 3.00,
            'roof_per_linear_meter' => 6.00,
            
            // Property Type Multipliers
            'residential_multiplier' => 1.0,
            'commercial_multiplier' => 1.2,
            'industrial_multiplier' => 1.5,
            
            // Surface Material Multipliers
            'brick_multiplier' => 1.0,
            'stone_multiplier' => 1.1,
            'glass_multiplier' => 1.3,
            'metal_multiplier' => 1.2,
            'concrete_multiplier' => 1.0,
            'composite_multiplier' => 1.4,
            
            // Building Height Multipliers
            'height_multiplier_per_floor' => 0.05, // 5% per floor above ground
            
            // Complexity Factors
            'standard_complexity' => 1.0,
            'moderate_complexity' => 1.2,
            'complex_complexity' => 1.5,
            
            // Minimum Charges
            'minimum_quote_value' => 100.00,
            'call_out_fee' => 0.00,
            
            // Tax Configuration
            'tax_rate' => 21.0, // 21% VAT (IVA) for Spain
            'tax_inclusive' => false,
            
            // Country Configuration
            'phone_country_code' => 'ES',
            'postal_code_country' => 'ES',
            
            // Business Hours
            'business_hours' => [
                'monday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
                'tuesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
                'wednesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
                'thursday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
                'friday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
                'saturday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
                'sunday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => false],
            ],
            
            // Booking Configuration
            'booking_buffer_time' => 60, // minutes
            'max_daily_bookings' => 3,
            'min_lead_time_days' => 1,
            'quote_validity_days' => 30,
            
            // Service Area (Postal Codes) - All Spain (empty array = accept all)
            // To restrict to specific areas, add postal codes or ranges here
            // Examples: ['29600-29604', '28001-28099', '08001-08099']
            'service_area_postcodes' => [],
            
            // Email Settings
            'email_notifications_enabled' => true,
            'admin_notification_email' => get_option('admin_email'),
            'email_from_name' => get_bloginfo('name'),
            'email_from_address' => get_option('admin_email'),
            
            // Form Settings
            'form_fields_required' => [
                'customer_name' => true,
                'customer_email' => true,
                'customer_phone' => true,
                'property_address' => true,
                'postal_code' => true,
                'service_type' => true,
                'square_meters' => true,
                'privacy_consent' => true,
            ],
            
            // Security Settings
            'enable_recaptcha' => false,
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'rate_limit_submissions' => 5, // per 5 minutes per IP
            
            // Integration Settings
            'motopress_integration_enabled' => true,
            'woocommerce_integration_enabled' => false, // Optional, disabled by default
            'deposit_percentage' => 20.0,
            'enable_online_payments' => false, // Only if WooCommerce is available
            'payment_methods' => ['cash', 'bank_transfer'], // Default payment methods
            
            // Maintenance Mode
            'maintenance_mode' => false,
            'maintenance_message' => __('The quotation system is temporarily unavailable for maintenance.', 'pro-clean-quotation'),
        ];
        
        foreach ($defaults as $key => $value) {
            $option_name = self::PREFIX . $key;
            if (get_option($option_name) === false) {
                add_option($option_name, $value);
            }
        }
    }
    
    /**
     * Get setting value
     * 
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public static function get(string $key, $default = null) {
        return get_option(self::PREFIX . $key, $default);
    }
    
    /**
     * Update setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public static function update(string $key, $value): bool {
        return update_option(self::PREFIX . $key, $value);
    }
    
    /**
     * Delete setting
     * 
     * @param string $key Setting key
     * @return bool
     */
    public static function delete(string $key): bool {
        return delete_option(self::PREFIX . $key);
    }
    
    /**
     * Remove all plugin options
     */
    public static function removeOptions(): void {
        global $wpdb;
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                self::PREFIX . '%'
            )
        );
    }
    
    /**
     * Get all pricing settings
     * 
     * @return array
     */
    public static function getPricingSettings(): array {
        return [
            'facade_base_rate' => self::get('facade_base_rate', 150.00),
            'facade_per_sqm' => self::get('facade_per_sqm', 2.50),
            'facade_per_linear_meter' => self::get('facade_per_linear_meter', 5.00),
            'roof_base_rate' => self::get('roof_base_rate', 200.00),
            'roof_per_sqm' => self::get('roof_per_sqm', 3.00),
            'roof_per_linear_meter' => self::get('roof_per_linear_meter', 6.00),
            'residential_multiplier' => self::get('residential_multiplier', 1.0),
            'commercial_multiplier' => self::get('commercial_multiplier', 1.2),
            'industrial_multiplier' => self::get('industrial_multiplier', 1.5),
            'brick_multiplier' => self::get('brick_multiplier', 1.0),
            'stone_multiplier' => self::get('stone_multiplier', 1.1),
            'glass_multiplier' => self::get('glass_multiplier', 1.3),
            'metal_multiplier' => self::get('metal_multiplier', 1.2),
            'concrete_multiplier' => self::get('concrete_multiplier', 1.0),
            'composite_multiplier' => self::get('composite_multiplier', 1.4),
            'height_multiplier_per_floor' => self::get('height_multiplier_per_floor', 0.05),
            'standard_complexity' => self::get('standard_complexity', 1.0),
            'moderate_complexity' => self::get('moderate_complexity', 1.2),
            'complex_complexity' => self::get('complex_complexity', 1.5),
            'minimum_quote_value' => self::get('minimum_quote_value', 100.00),
            'call_out_fee' => self::get('call_out_fee', 0.00),
            'tax_rate' => self::get('tax_rate', 21.0),
            'tax_inclusive' => self::get('tax_inclusive', false),
        ];
    }
    
    /**
     * Get business hours
     * 
     * @return array
     */
    public static function getBusinessHours(): array {
        return self::get('business_hours', [
            'monday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'tuesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'wednesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'thursday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'friday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'saturday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
            'sunday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => false],
        ]);
    }
}