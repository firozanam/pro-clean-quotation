<?php

namespace ProClean\Quotation\I18n;

/**
 * WPML Compatibility Layer
 * 
 * Provides specific integration with WPML (WordPress Multilingual Plugin)
 * 
 * @package ProClean\Quotation\I18n
 * @since 1.0.0
 */
class WPMLCompat {
    
    /**
     * Compatibility instance
     * 
     * @var WPMLCompat
     */
    private static $instance = null;
    
    /**
     * WPML string context
     * 
     * @var string
     */
    private const STRING_CONTEXT = 'pro-clean-quotation';
    
    /**
     * Get compatibility instance
     * 
     * @return WPMLCompat
     */
    public static function getInstance(): WPMLCompat {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->initHooks();
        $this->registerStrings();
    }
    
    /**
     * Initialize WPML hooks
     */
    private function initHooks(): void {
        if (!$this->isWPMLActive()) {
            return;
        }
        
        // Register custom post types and taxonomies for translation
        add_action('init', [$this, 'registerTranslatableElements'], 20);
        
        // Filter quote data for translation
        add_filter('pcq_quote_data', [$this, 'translateQuoteData'], 10, 2);
        
        // Filter booking data for translation
        add_filter('pcq_booking_data', [$this, 'translateBookingData'], 10, 2);
        
        // Filter email content for translation
        add_filter('pcq_email_content', [$this, 'translateEmailContent'], 10, 3);
        
        // Register admin notice strings
        add_action('admin_init', [$this, 'registerAdminStrings']);
        
        // Add language column to admin tables
        add_filter('manage_pcq_quotes_columns', [$this, 'addLanguageColumn']);
        add_filter('manage_pcq_bookings_columns', [$this, 'addLanguageColumn']);
    }
    
    /**
     * Check if WPML is active
     * 
     * @return bool True if WPML is active
     */
    public function isWPMLActive(): bool {
        return defined('ICL_SITEPRESS_VERSION') && class_exists('SitePress');
    }
    
    /**
     * Get WPML instance
     * 
     * @return object|null WPML SitePress instance
     */
    private function getWPML(): ?object {
        global $sitepress;
        return $sitepress ?? null;
    }
    
    /**
     * Register translatable elements with WPML
     */
    public function registerTranslatableElements(): void {
        if (!$this->isWPMLActive()) {
            return;
        }
        
        // Register custom database tables for WPML
        // This allows WPML to track language for quotes and bookings
        
        // Note: WPML primarily handles post types and taxonomies
        // For custom tables, we need to add language metadata
        add_action('pcq_quote_created', [$this, 'saveQuoteLanguage'], 10, 2);
        add_action('pcq_booking_created', [$this, 'saveBookingLanguage'], 10, 2);
    }
    
    /**
     * Save language information when quote is created
     * 
     * @param int $quote_id Quote ID
     * @param array $data Quote data
     */
    public function saveQuoteLanguage(int $quote_id, array $data): void {
        $current_lang = $this->getCurrentLanguage();
        
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'pq_quotes',
            ['language' => $current_lang],
            ['id' => $quote_id],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * Save language information when booking is created
     * 
     * @param int $booking_id Booking ID
     * @param array $data Booking data
     */
    public function saveBookingLanguage(int $booking_id, array $data): void {
        $current_lang = $this->getCurrentLanguage();
        
        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'pq_bookings',
            ['language' => $current_lang],
            ['id' => $booking_id],
            ['%s'],
            ['%d']
        );
    }
    
    /**
     * Get current language code
     * 
     * @return string Language code (e.g., 'en', 'nl', 'fr')
     */
    public function getCurrentLanguage(): string {
        $wpml = $this->getWPML();
        return $wpml ? $wpml->get_current_language() : 'en';
    }
    
    /**
     * Get default language code
     * 
     * @return string Default language code
     */
    public function getDefaultLanguage(): string {
        $wpml = $this->getWPML();
        return $wpml ? $wpml->get_default_language() : 'en';
    }
    
    /**
     * Get active languages
     * 
     * @return array Active languages with details
     */
    public function getActiveLanguages(): array {
        $wpml = $this->getWPML();
        
        if (!$wpml) {
            return [];
        }
        
        $languages = $wpml->get_active_languages();
        $formatted = [];
        
        foreach ($languages as $code => $lang) {
            $formatted[$code] = [
                'code' => $code,
                'name' => $lang['native_name'] ?? $lang['display_name'],
                'flag' => $lang['country_flag_url'] ?? '',
                'default' => $lang['default_locale'] ?? false,
                'active' => $lang['active'] ?? false
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Register plugin strings for translation
     */
    public function registerStrings(): void {
        if (!$this->isWPMLActive() || !function_exists('icl_register_string')) {
            return;
        }
        
        // Service type labels
        $service_types = [
            'facade_cleaning' => 'Façade Cleaning',
            'roof_cleaning' => 'Roof Cleaning',
            'gutter_cleaning' => 'Gutter Cleaning',
            'complete_exterior' => 'Complete Exterior Cleaning'
        ];
        
        foreach ($service_types as $key => $label) {
            icl_register_string(self::STRING_CONTEXT, 'service_type_' . $key, $label);
        }
        
        // Property conditions
        $conditions = [
            'light' => 'Light Dirt',
            'moderate' => 'Moderate Dirt',
            'heavy' => 'Heavy Dirt/Stains'
        ];
        
        foreach ($conditions as $key => $label) {
            icl_register_string(self::STRING_CONTEXT, 'condition_' . $key, $label);
        }
        
        // Email subjects
        $email_subjects = [
            'quote_confirmation' => 'Your Cleaning Quote - {quote_number}',
            'booking_confirmation' => 'Booking Confirmed - {booking_number}',
            'booking_reminder' => 'Reminder: Service Tomorrow - {booking_number}',
            'admin_notification' => 'New Quote Request - {quote_number}'
        ];
        
        foreach ($email_subjects as $key => $subject) {
            icl_register_string(self::STRING_CONTEXT, 'email_subject_' . $key, $subject);
        }
        
        // Common form labels and messages
        $form_strings = [
            'get_quote_button' => 'Get My Free Quote',
            'processing' => 'Processing...',
            'success_message' => 'Thank you! Your quote request has been submitted.',
            'error_message' => 'An error occurred. Please try again.',
            'required_field' => 'This field is required',
            'invalid_email' => 'Please enter a valid email address',
            'invalid_phone' => 'Please enter a valid phone number',
            'invalid_postal_code' => 'Invalid postal code format'
        ];
        
        foreach ($form_strings as $key => $string) {
            icl_register_string(self::STRING_CONTEXT, 'form_' . $key, $string);
        }
    }
    
    /**
     * Register admin interface strings
     */
    public function registerAdminStrings(): void {
        if (!$this->isWPMLActive() || !function_exists('icl_register_string')) {
            return;
        }
        
        // Admin menu labels
        $admin_strings = [
            'menu_quotations' => 'Quotations',
            'menu_dashboard' => 'Dashboard',
            'menu_quotes' => 'Quotes',
            'menu_bookings' => 'Bookings',
            'menu_appointments' => 'Appointments',
            'menu_calendar' => 'Calendar',
            'menu_settings' => 'Settings'
        ];
        
        foreach ($admin_strings as $key => $string) {
            icl_register_string(self::STRING_CONTEXT, 'admin_' . $key, $string);
        }
    }
    
    /**
     * Translate a string using WPML
     * 
     * @param string $string Original string
     * @param string $name String name/key
     * @param string|null $language Target language (null = current)
     * @return string Translated string
     */
    public function translateString(string $string, string $name, ?string $language = null): string {
        if (!$this->isWPMLActive() || !function_exists('icl_t')) {
            return $string;
        }
        
        return icl_t(self::STRING_CONTEXT, $name, $string, null, false, $language);
    }
    
    /**
     * Translate quote data
     * 
     * @param array $data Quote data
     * @param string|null $language Target language
     * @return array Translated quote data
     */
    public function translateQuoteData(array $data, ?string $language = null): array {
        if (!$this->isWPMLActive()) {
            return $data;
        }
        
        // Translate service type label
        if (isset($data['service_type'])) {
            $service_key = 'service_type_' . $data['service_type'];
            $data['service_type_label'] = $this->translateString(
                $data['service_type_label'] ?? ucfirst($data['service_type']),
                $service_key,
                $language
            );
        }
        
        // Translate condition label
        if (isset($data['property_condition'])) {
            $condition_key = 'condition_' . $data['property_condition'];
            $data['condition_label'] = $this->translateString(
                $data['condition_label'] ?? ucfirst($data['property_condition']),
                $condition_key,
                $language
            );
        }
        
        return $data;
    }
    
    /**
     * Translate booking data
     * 
     * @param array $data Booking data
     * @param string|null $language Target language
     * @return array Translated booking data
     */
    public function translateBookingData(array $data, ?string $language = null): array {
        // Similar to translateQuoteData
        return $this->translateQuoteData($data, $language);
    }
    
    /**
     * Translate email content
     * 
     * @param string $content Email content
     * @param string $type Email type
     * @param string|null $language Target language
     * @return string Translated email content
     */
    public function translateEmailContent(string $content, string $type, ?string $language = null): string {
        if (!$this->isWPMLActive()) {
            return $content;
        }
        
        // WPML automatically translates content through WordPress translation functions
        // This filter allows for additional custom translations if needed
        
        return $content;
    }
    
    /**
     * Add language column to admin tables
     * 
     * @param array $columns Existing columns
     * @return array Modified columns
     */
    public function addLanguageColumn(array $columns): array {
        $columns['language'] = __('Language', 'pro-clean-quotation');
        return $columns;
    }
    
    /**
     * Switch to a specific language temporarily
     * 
     * @param string $language Language code
     * @return bool Success status
     */
    public function switchLanguage(string $language): bool {
        $wpml = $this->getWPML();
        
        if (!$wpml || !method_exists($wpml, 'switch_lang')) {
            return false;
        }
        
        $wpml->switch_lang($language);
        return true;
    }
    
    /**
     * Get language for a specific quote
     * 
     * @param int $quote_id Quote ID
     * @return string Language code
     */
    public function getQuoteLanguage(int $quote_id): string {
        global $wpdb;
        
        $language = $wpdb->get_var($wpdb->prepare(
            "SELECT language FROM {$wpdb->prefix}pq_quotes WHERE id = %d",
            $quote_id
        ));
        
        return $language ?: $this->getDefaultLanguage();
    }
    
    /**
     * Get language for a specific booking
     * 
     * @param int $booking_id Booking ID
     * @return string Language code
     */
    public function getBookingLanguage(int $booking_id): string {
        global $wpdb;
        
        $language = $wpdb->get_var($wpdb->prepare(
            "SELECT language FROM {$wpdb->prefix}pq_bookings WHERE id = %d",
            $booking_id
        ));
        
        return $language ?: $this->getDefaultLanguage();
    }
    
    /**
     * Get WPML configuration for plugin
     * 
     * @return array WPML configuration
     */
    public function getConfig(): array {
        return [
            'plugin' => 'wpml',
            'version' => defined('ICL_SITEPRESS_VERSION') ? ICL_SITEPRESS_VERSION : null,
            'active' => $this->isWPMLActive(),
            'current_language' => $this->getCurrentLanguage(),
            'default_language' => $this->getDefaultLanguage(),
            'active_languages' => array_keys($this->getActiveLanguages()),
            'string_context' => self::STRING_CONTEXT
        ];
    }
}
