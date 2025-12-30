<?php

namespace ProClean\Quotation\I18n;

/**
 * Polylang Compatibility Layer
 * 
 * Provides specific integration with Polylang multilingual plugin
 * 
 * @package ProClean\Quotation\I18n
 * @since 1.0.0
 */
class PolylangCompat {
    
    /**
     * Compatibility instance
     * 
     * @var PolylangCompat
     */
    private static $instance = null;
    
    /**
     * Get compatibility instance
     * 
     * @return PolylangCompat
     */
    public static function getInstance(): PolylangCompat {
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
    }
    
    /**
     * Initialize Polylang hooks
     */
    private function initHooks(): void {
        if (!$this->isPolylangActive()) {
            return;
        }
        
        // Filter quote data for translation
        add_filter('pcq_quote_data', [$this, 'translateQuoteData'], 10, 2);
        
        // Filter booking data for translation
        add_filter('pcq_booking_data', [$this, 'translateBookingData'], 10, 2);
        
        // Save language with quotes and bookings
        add_action('pcq_quote_created', [$this, 'saveQuoteLanguage'], 10, 2);
        add_action('pcq_booking_created', [$this, 'saveBookingLanguage'], 10, 2);
        
        // Add language column to admin tables
        add_filter('manage_pcq_quotes_columns', [$this, 'addLanguageColumn']);
        add_filter('manage_pcq_bookings_columns', [$this, 'addLanguageColumn']);
        
        // Register strings for translation (using WordPress .mo files)
        add_action('init', [$this, 'registerTranslations']);
    }
    
    /**
     * Check if Polylang is active
     * 
     * @return bool True if Polylang is active
     */
    public function isPolylangActive(): bool {
        return function_exists('pll_current_language') && function_exists('pll_the_languages');
    }
    
    /**
     * Get current language code
     * 
     * @return string Language code (e.g., 'en', 'nl', 'fr')
     */
    public function getCurrentLanguage(): string {
        if (!$this->isPolylangActive()) {
            return 'en';
        }
        
        $current = pll_current_language();
        return $current ?: 'en';
    }
    
    /**
     * Get default language code
     * 
     * @return string Default language code
     */
    public function getDefaultLanguage(): string {
        if (!$this->isPolylangActive() || !function_exists('pll_default_language')) {
            return 'en';
        }
        
        $default = pll_default_language();
        return $default ?: 'en';
    }
    
    /**
     * Get active languages
     * 
     * @return array Active languages with details
     */
    public function getActiveLanguages(): array {
        if (!$this->isPolylangActive() || !function_exists('pll_languages_list')) {
            return [];
        }
        
        $languages = pll_languages_list(['fields' => 'all']);
        $formatted = [];
        
        foreach ($languages as $lang) {
            $formatted[$lang->slug] = [
                'code' => $lang->slug,
                'name' => $lang->name,
                'locale' => $lang->locale ?? '',
                'flag' => $lang->flag ?? '',
                'default' => isset($lang->is_default) && $lang->is_default,
                'active' => true
            ];
        }
        
        return $formatted;
    }
    
    /**
     * Register translations (Polylang uses WordPress .mo files)
     */
    public function registerTranslations(): void {
        // Polylang relies on WordPress translation files (.mo/.po)
        // Ensure text domain is loaded
        load_plugin_textdomain(
            'pro-clean-quotation',
            false,
            dirname(plugin_basename(PCQ_PLUGIN_FILE)) . '/languages'
        );
    }
    
    /**
     * Translate a string (uses WordPress translation functions)
     * 
     * @param string $string Original string
     * @param string|null $language Target language (null = current)
     * @return string Translated string
     */
    public function translateString(string $string, ?string $language = null): string {
        if (!$this->isPolylangActive()) {
            return __($string, 'pro-clean-quotation');
        }
        
        // If specific language requested, switch temporarily
        if ($language && $language !== $this->getCurrentLanguage()) {
            $original_lang = $this->getCurrentLanguage();
            
            // Polylang doesn't have easy language switching API
            // Translation happens via .mo files which WordPress loads based on locale
            // For now, use WordPress translation with current language
            $translated = __($string, 'pro-clean-quotation');
            
            return $translated;
        }
        
        return __($string, 'pro-clean-quotation');
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
     * Translate quote data
     * 
     * @param array $data Quote data
     * @param string|null $language Target language
     * @return array Translated quote data
     */
    public function translateQuoteData(array $data, ?string $language = null): array {
        if (!$this->isPolylangActive()) {
            return $data;
        }
        
        // Translate service type label
        if (isset($data['service_type'])) {
            $data['service_type_label'] = $this->translateString(
                $data['service_type_label'] ?? ucfirst($data['service_type']),
                $language
            );
        }
        
        // Translate condition label
        if (isset($data['property_condition'])) {
            $data['condition_label'] = $this->translateString(
                $data['condition_label'] ?? ucfirst($data['property_condition']),
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
        return $this->translateQuoteData($data, $language);
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
     * Get translated post ID
     * 
     * @param int $post_id Original post ID
     * @param string|null $language Target language code
     * @return int|null Translated post ID or null if not found
     */
    public function getTranslatedPostId(int $post_id, ?string $language = null): ?int {
        if (!$this->isPolylangActive() || !function_exists('pll_get_post')) {
            return $post_id;
        }
        
        $target_lang = $language ?? $this->getCurrentLanguage();
        $translated_id = pll_get_post($post_id, $target_lang);
        
        return $translated_id ?: null;
    }
    
    /**
     * Get translated term ID
     * 
     * @param int $term_id Original term ID
     * @param string|null $language Target language code
     * @return int|null Translated term ID or null if not found
     */
    public function getTranslatedTermId(int $term_id, ?string $language = null): ?int {
        if (!$this->isPolylangActive() || !function_exists('pll_get_term')) {
            return $term_id;
        }
        
        $target_lang = $language ?? $this->getCurrentLanguage();
        $translated_id = pll_get_term($term_id, $target_lang);
        
        return $translated_id ?: null;
    }
    
    /**
     * Get language switcher HTML
     * 
     * @param array $args Switcher configuration
     * @return string HTML output
     */
    public function getLanguageSwitcher(array $args = []): string {
        if (!$this->isPolylangActive() || !function_exists('pll_the_languages')) {
            return '';
        }
        
        $defaults = [
            'show_flags' => true,
            'show_names' => true,
            'dropdown' => false,
            'echo' => false
        ];
        
        $args = array_merge($defaults, $args);
        
        ob_start();
        pll_the_languages([
            'show_flags' => $args['show_flags'],
            'show_names' => $args['show_names'],
            'dropdown' => $args['dropdown'],
            'echo' => true
        ]);
        return ob_get_clean();
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
     * Register post type for translation
     * 
     * @param string $post_type Post type name
     */
    public function registerPostType(string $post_type): void {
        if (!$this->isPolylangActive() || !function_exists('pll_register_string')) {
            return;
        }
        
        // Polylang automatically handles registered post types
        // No explicit registration needed like WPML
    }
    
    /**
     * Get Polylang configuration for plugin
     * 
     * @return array Polylang configuration
     */
    public function getConfig(): array {
        return [
            'plugin' => 'polylang',
            'version' => defined('POLYLANG_VERSION') ? POLYLANG_VERSION : null,
            'active' => $this->isPolylangActive(),
            'current_language' => $this->getCurrentLanguage(),
            'default_language' => $this->getDefaultLanguage(),
            'active_languages' => array_keys($this->getActiveLanguages()),
            'uses_mo_files' => true
        ];
    }
}
