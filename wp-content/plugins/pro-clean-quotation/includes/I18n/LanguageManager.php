<?php

namespace ProClean\Quotation\I18n;

use ProClean\Quotation\Admin\Settings;

/**
 * Language Manager Class
 * 
 * Handles multi-language support including WPML and Polylang compatibility
 * 
 * @package ProClean\Quotation\I18n
 * @since 1.0.0
 */
class LanguageManager {
    
    /**
     * Manager instance
     * 
     * @var LanguageManager
     */
    private static $instance = null;
    
    /**
     * Multi-language plugin detected
     * 
     * @var string|null 'wpml'|'polylang'|null
     */
    private $ml_plugin = null;
    
    /**
     * Current language code
     * 
     * @var string
     */
    private $current_language = '';
    
    /**
     * Available languages
     * 
     * @var array
     */
    private $available_languages = [];
    
    /**
     * Default language
     * 
     * @var string
     */
    private $default_language = 'en';
    
    /**
     * Get manager instance
     * 
     * @return LanguageManager
     */
    public static function getInstance(): LanguageManager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->detectMultilingualPlugin();
        $this->loadCurrentLanguage();
        $this->loadAvailableLanguages();
        $this->initHooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function initHooks(): void {
        // Filter translatable strings
        add_filter('pcq_translate_string', [$this, 'translateString'], 10, 3);
        
        // Filter translatable content
        add_filter('pcq_translate_content', [$this, 'translateContent'], 10, 2);
        
        // Admin language settings
        add_action('admin_init', [$this, 'registerLanguageSettings']);
        
        // Switch language in AJAX responses
        add_filter('pcq_ajax_response', [$this, 'filterAjaxResponse'], 10, 2);
    }
    
    /**
     * Detect active multilingual plugin
     */
    private function detectMultilingualPlugin(): void {
        // Check for WPML
        if (defined('ICL_SITEPRESS_VERSION') && class_exists('SitePress')) {
            $this->ml_plugin = 'wpml';
            return;
        }
        
        // Check for Polylang
        if (function_exists('pll_current_language')) {
            $this->ml_plugin = 'polylang';
            return;
        }
        
        // No multilingual plugin detected
        $this->ml_plugin = null;
    }
    
    /**
     * Load current language
     */
    private function loadCurrentLanguage(): void {
        switch ($this->ml_plugin) {
            case 'wpml':
                global $sitepress;
                $this->current_language = $sitepress ? $sitepress->get_current_language() : $this->default_language;
                break;
                
            case 'polylang':
                $this->current_language = pll_current_language() ?: $this->default_language;
                break;
                
            default:
                $this->current_language = get_locale();
                // Convert locale to language code (e.g., en_US -> en)
                if (strpos($this->current_language, '_') !== false) {
                    $parts = explode('_', $this->current_language);
                    $this->current_language = $parts[0];
                }
                break;
        }
    }
    
    /**
     * Load available languages
     */
    private function loadAvailableLanguages(): void {
        switch ($this->ml_plugin) {
            case 'wpml':
                global $sitepress;
                if ($sitepress) {
                    $languages = $sitepress->get_active_languages();
                    foreach ($languages as $code => $lang) {
                        $this->available_languages[$code] = [
                            'code' => $code,
                            'name' => $lang['native_name'] ?? $lang['display_name'],
                            'flag' => $lang['country_flag_url'] ?? '',
                            'default' => $lang['default_locale'] ?? false
                        ];
                    }
                }
                break;
                
            case 'polylang':
                if (function_exists('pll_languages_list')) {
                    $languages = pll_languages_list(['fields' => 'all']);
                    foreach ($languages as $lang) {
                        $this->available_languages[$lang->slug] = [
                            'code' => $lang->slug,
                            'name' => $lang->name,
                            'flag' => $lang->flag ?? '',
                            'default' => $lang->is_default ?? false
                        ];
                    }
                }
                break;
                
            default:
                // WordPress default language only
                $locale = get_locale();
                $lang_code = strpos($locale, '_') !== false ? explode('_', $locale)[0] : $locale;
                $this->available_languages[$lang_code] = [
                    'code' => $lang_code,
                    'name' => $locale,
                    'flag' => '',
                    'default' => true
                ];
                break;
        }
        
        // Set default language
        foreach ($this->available_languages as $code => $lang) {
            if ($lang['default']) {
                $this->default_language = $code;
                break;
            }
        }
    }
    
    /**
     * Get current language code
     * 
     * @return string Language code (e.g., 'en', 'nl', 'fr')
     */
    public function getCurrentLanguage(): string {
        return $this->current_language;
    }
    
    /**
     * Get default language code
     * 
     * @return string Default language code
     */
    public function getDefaultLanguage(): string {
        return $this->default_language;
    }
    
    /**
     * Get available languages
     * 
     * @return array Available languages with metadata
     */
    public function getAvailableLanguages(): array {
        return $this->available_languages;
    }
    
    /**
     * Get active multilingual plugin name
     * 
     * @return string|null 'wpml'|'polylang'|null
     */
    public function getMultilingualPlugin(): ?string {
        return $this->ml_plugin;
    }
    
    /**
     * Check if multilingual plugin is active
     * 
     * @return bool True if WPML or Polylang is active
     */
    public function isMultilingualActive(): bool {
        return $this->ml_plugin !== null;
    }
    
    /**
     * Translate string using active multilingual plugin
     * 
     * @param string $string String to translate
     * @param string $context Translation context/domain
     * @param string|null $language Target language code (null = current)
     * @return string Translated string
     */
    public function translateString(string $string, string $context = 'pro-clean-quotation', ?string $language = null): string {
        if (!$this->isMultilingualActive()) {
            return $string;
        }
        
        $target_lang = $language ?? $this->current_language;
        
        switch ($this->ml_plugin) {
            case 'wpml':
                // WPML string translation
                if (function_exists('icl_t')) {
                    return icl_t($context, $string, $string, null, false, $target_lang);
                }
                break;
                
            case 'polylang':
                // Polylang doesn't have direct string translation
                // Falls back to WordPress __() with mo file
                return __($string, $context);
                
            default:
                return __($string, $context);
        }
        
        return $string;
    }
    
    /**
     * Translate content (for post content, page content, etc.)
     * 
     * @param string $content Content to translate
     * @param string|null $language Target language code
     * @return string Translated content
     */
    public function translateContent(string $content, ?string $language = null): string {
        if (!$this->isMultilingualActive()) {
            return $content;
        }
        
        // WPML and Polylang handle content translation automatically
        // This method is for manual translation if needed
        return $content;
    }
    
    /**
     * Get translated post ID
     * 
     * @param int $post_id Original post ID
     * @param string|null $language Target language code
     * @return int|null Translated post ID or null if not found
     */
    public function getTranslatedPostId(int $post_id, ?string $language = null): ?int {
        if (!$this->isMultilingualActive()) {
            return $post_id;
        }
        
        $target_lang = $language ?? $this->current_language;
        
        switch ($this->ml_plugin) {
            case 'wpml':
                if (function_exists('icl_object_id')) {
                    $translated_id = icl_object_id($post_id, 'post', false, $target_lang);
                    return $translated_id ?: null;
                }
                break;
                
            case 'polylang':
                if (function_exists('pll_get_post')) {
                    $translated_id = pll_get_post($post_id, $target_lang);
                    return $translated_id ?: null;
                }
                break;
        }
        
        return $post_id;
    }
    
    /**
     * Get translated term ID
     * 
     * @param int $term_id Original term ID
     * @param string $taxonomy Taxonomy name
     * @param string|null $language Target language code
     * @return int|null Translated term ID or null if not found
     */
    public function getTranslatedTermId(int $term_id, string $taxonomy, ?string $language = null): ?int {
        if (!$this->isMultilingualActive()) {
            return $term_id;
        }
        
        $target_lang = $language ?? $this->current_language;
        
        switch ($this->ml_plugin) {
            case 'wpml':
                if (function_exists('icl_object_id')) {
                    $translated_id = icl_object_id($term_id, $taxonomy, false, $target_lang);
                    return $translated_id ?: null;
                }
                break;
                
            case 'polylang':
                if (function_exists('pll_get_term')) {
                    $translated_id = pll_get_term($term_id, $target_lang);
                    return $translated_id ?: null;
                }
                break;
        }
        
        return $term_id;
    }
    
    /**
     * Switch to specific language temporarily
     * 
     * @param string $language Language code to switch to
     * @return bool Success status
     */
    public function switchLanguage(string $language): bool {
        if (!$this->isMultilingualActive()) {
            return false;
        }
        
        switch ($this->ml_plugin) {
            case 'wpml':
                global $sitepress;
                if ($sitepress && method_exists($sitepress, 'switch_lang')) {
                    $sitepress->switch_lang($language);
                    return true;
                }
                break;
                
            case 'polylang':
                // Polylang handles language switching via URL/session
                // Manual switching is more complex
                if (function_exists('pll_current_language')) {
                    // Store language preference in session/cookie
                    return true;
                }
                break;
        }
        
        return false;
    }
    
    /**
     * Register translatable strings for WPML
     * 
     * @param array $strings Array of strings to register
     * @param string $context Context/domain
     */
    public function registerStrings(array $strings, string $context = 'pro-clean-quotation'): void {
        if ($this->ml_plugin !== 'wpml') {
            return;
        }
        
        if (function_exists('icl_register_string')) {
            foreach ($strings as $name => $value) {
                icl_register_string($context, $name, $value);
            }
        }
    }
    
    /**
     * Register language settings in admin
     */
    public function registerLanguageSettings(): void {
        // Add language-specific settings if needed
        // This can be extended based on requirements
    }
    
    /**
     * Filter AJAX responses to include language information
     * 
     * @param array $response AJAX response data
     * @param string $action AJAX action name
     * @return array Modified response
     */
    public function filterAjaxResponse(array $response, string $action): array {
        // Add current language to response
        $response['language'] = $this->current_language;
        
        // Add multilingual info
        $response['ml_info'] = [
            'plugin' => $this->ml_plugin,
            'default_lang' => $this->default_language,
            'available_langs' => array_keys($this->available_languages)
        ];
        
        return $response;
    }
    
    /**
     * Get language switcher HTML
     * 
     * @param array $args Switcher configuration
     * @return string HTML output
     */
    public function getLanguageSwitcher(array $args = []): string {
        if (!$this->isMultilingualActive()) {
            return '';
        }
        
        $defaults = [
            'show_flags' => true,
            'show_names' => true,
            'dropdown' => false,
            'echo' => false
        ];
        
        $args = array_merge($defaults, $args);
        
        switch ($this->ml_plugin) {
            case 'wpml':
                if (function_exists('icl_get_languages')) {
                    $languages = icl_get_languages('skip_missing=0&orderby=code');
                    return $this->renderLanguageSwitcher($languages, $args);
                }
                break;
                
            case 'polylang':
                if (function_exists('pll_the_languages')) {
                    ob_start();
                    pll_the_languages([
                        'show_flags' => $args['show_flags'],
                        'show_names' => $args['show_names'],
                        'dropdown' => $args['dropdown']
                    ]);
                    return ob_get_clean();
                }
                break;
        }
        
        return '';
    }
    
    /**
     * Render custom language switcher
     * 
     * @param array $languages Available languages
     * @param array $args Display arguments
     * @return string HTML output
     */
    private function renderLanguageSwitcher(array $languages, array $args): string {
        $html = '<div class="pcq-language-switcher">';
        
        if ($args['dropdown']) {
            $html .= '<select class="pcq-lang-select" onchange="window.location.href=this.value">';
            foreach ($languages as $lang) {
                $selected = $lang['active'] ? ' selected' : '';
                $html .= '<option value="' . esc_url($lang['url']) . '"' . $selected . '>';
                if ($args['show_flags'] && !empty($lang['country_flag_url'])) {
                    $html .= '🏳️ '; // Fallback emoji
                }
                if ($args['show_names']) {
                    $html .= esc_html($lang['native_name']);
                }
                $html .= '</option>';
            }
            $html .= '</select>';
        } else {
            $html .= '<ul class="pcq-lang-list">';
            foreach ($languages as $lang) {
                $active_class = $lang['active'] ? ' class="active"' : '';
                $html .= '<li' . $active_class . '>';
                $html .= '<a href="' . esc_url($lang['url']) . '">';
                if ($args['show_flags'] && !empty($lang['country_flag_url'])) {
                    $html .= '<img src="' . esc_url($lang['country_flag_url']) . '" alt="' . esc_attr($lang['native_name']) . '" class="pcq-lang-flag">';
                }
                if ($args['show_names']) {
                    $html .= '<span class="pcq-lang-name">' . esc_html($lang['native_name']) . '</span>';
                }
                $html .= '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get language info for debugging
     * 
     * @return array Language configuration info
     */
    public function getDebugInfo(): array {
        return [
            'ml_plugin' => $this->ml_plugin ?? 'none',
            'current_language' => $this->current_language,
            'default_language' => $this->default_language,
            'available_languages' => $this->available_languages,
            'wp_locale' => get_locale(),
            'wpml_active' => defined('ICL_SITEPRESS_VERSION'),
            'polylang_active' => function_exists('pll_current_language')
        ];
    }
}
