<?php

namespace ProClean\Quotation\Frontend;

use ProClean\Quotation\Admin\Settings;

/**
 * Shortcode Manager Class
 * 
 * @package ProClean\Quotation\Frontend
 * @since 1.0.0
 */
class ShortcodeManager {
    
    /**
     * Shortcode manager instance
     * 
     * @var ShortcodeManager
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return ShortcodeManager
     */
    public static function getInstance(): ShortcodeManager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->registerShortcodes();
    }
    
    /**
     * Register shortcodes
     */
    private function registerShortcodes(): void {
        add_shortcode('pcq_quote_form', [$this, 'renderQuoteForm']);
        add_shortcode('pcq_booking_form', [$this, 'renderBookingForm']);
        add_shortcode('pcq_booking_confirmation', [$this, 'renderBookingConfirmation']);
        add_shortcode('pcq_quote_calculator', [$this, 'renderQuoteCalculator']);
    }
    
    /**
     * Render quote form shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function renderQuoteForm(array $atts = []): string {
        // Check maintenance mode
        if (Settings::get('maintenance_mode', false)) {
            return '<div class="pcq-maintenance-notice">' . 
                   esc_html(Settings::get('maintenance_message', __('The quotation system is temporarily unavailable.', 'pro-clean-quotation'))) . 
                   '</div>';
        }
        
        $atts = shortcode_atts([
            'title' => __('Get Your Free Quote', 'pro-clean-quotation'),
            'show_title' => 'true',
            'style' => 'default',
            'columns' => '2'
        ], $atts);
        
        ob_start();
        
        // Load template - now using physical template file
        $template_path = $this->getTemplatePath('quote-form.php');
        include $template_path;
        
        return ob_get_clean();
    }
    
    /**
     * Render booking form shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function renderBookingForm(array $atts = []): string {
        $atts = shortcode_atts([
            'quote_id' => '',
            'title' => __('Book Your Service', 'pro-clean-quotation'),
            'show_title' => 'true'
        ], $atts);
        
        ob_start();
        
        // Load template - now using physical template file
        $template_path = $this->getTemplatePath('booking-form.php');
        include $template_path;
        
        return ob_get_clean();
    }
    
    /**
     * Render booking confirmation shortcode
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function renderBookingConfirmation(array $atts = []): string {
        $atts = shortcode_atts([
            'title' => __('Booking Confirmed', 'pro-clean-quotation'),
            'show_title' => 'true'
        ], $atts);
        
        ob_start();
        
        // Load template - now using physical template file
        $template_path = $this->getTemplatePath('booking-confirmation.php');
        include $template_path;
        
        return ob_get_clean();
    }
    
    /**
     * Render quote calculator shortcode (simplified version)
     * 
     * @param array $atts Shortcode attributes
     * @return string HTML output
     */
    public function renderQuoteCalculator(array $atts = []): string {
        $atts = shortcode_atts([
            'title' => __('Quick Price Calculator', 'pro-clean-quotation'),
            'show_title' => 'true',
            'show_contact_form' => 'false'
        ], $atts);
        
        ob_start();
        
        // Load template - now using physical template file
        $template_path = $this->getTemplatePath('quote-calculator.php');
        include $template_path;
        
        return ob_get_clean();
    }
    
    /**
     * Get template path
     * 
     * @param string $template Template filename
     * @return string Template path
     */
    private function getTemplatePath(string $template): string {
        // Check theme override first
        $theme_template = get_stylesheet_directory() . '/pro-clean-quotation/' . $template;
        if (file_exists($theme_template)) {
            return $theme_template;
        }
        
        // Check parent theme
        $parent_template = get_template_directory() . '/pro-clean-quotation/' . $template;
        if (file_exists($parent_template)) {
            return $parent_template;
        }
        
        // Use plugin template
        return PCQ_PLUGIN_DIR . 'templates/frontend/' . $template;
    }
    
    /**
     * Get default quote form HTML
     * 
     * @param array $atts Attributes
     * @return string HTML
     */
    private function getDefaultQuoteFormHTML(array $atts): string {
        $form_id = 'pcq-quote-form-' . uniqid();
        $show_title = $atts['show_title'] === 'true';
        $columns = intval($atts['columns']);
        $column_class = $columns === 1 ? 'pcq-single-column' : 'pcq-two-columns';
        
        $html = '<div class="pcq-quote-form-container ' . esc_attr($atts['style']) . '">';
        
        if ($show_title) {
            $html .= '<h3 class="pcq-form-title">' . esc_html($atts['title']) . '</h3>';
        }
        
        $html .= '<form id="' . esc_attr($form_id) . '" class="pcq-quote-form ' . esc_attr($column_class) . '" method="post">';
        
        // Service Selection
        $html .= '<div class="pcq-form-section pcq-service-selection">';
        $html .= '<h4>' . __('Service Type', 'pro-clean-quotation') . ' <span class="required">*</span></h4>';
        $html .= '<div class="pcq-radio-group">';
        $html .= '<label><input type="radio" name="service_type" value="facade" required> ' . __('Façade Cleaning', 'pro-clean-quotation') . '</label>';
        $html .= '<label><input type="radio" name="service_type" value="roof" required> ' . __('Roof Cleaning', 'pro-clean-quotation') . '</label>';
        $html .= '<label><input type="radio" name="service_type" value="both" required> ' . __('Both Services', 'pro-clean-quotation') . '</label>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Property Measurements
        $html .= '<div class="pcq-form-section pcq-measurements">';
        $html .= '<h4>' . __('Property Measurements', 'pro-clean-quotation') . '</h4>';
        $html .= '<div class="pcq-form-row">';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="square_meters">' . __('Square Meters', 'pro-clean-quotation') . ' <span class="required">*</span></label>';
        $html .= '<input type="number" id="square_meters" name="square_meters" min="10" max="10000" step="0.1" required>';
        $html .= '<small>' . __('Total area to be cleaned (10-10,000 sqm)', 'pro-clean-quotation') . '</small>';
        $html .= '</div>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="linear_meters">' . __('Linear Meters', 'pro-clean-quotation') . '</label>';
        $html .= '<input type="number" id="linear_meters" name="linear_meters" min="5" max="5000" step="0.1">';
        $html .= '<small>' . __('Perimeter or edge length (optional)', 'pro-clean-quotation') . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="pcq-form-row">';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="building_height">' . __('Building Height (Floors)', 'pro-clean-quotation') . '</label>';
        $html .= '<select id="building_height" name="building_height">';
        for ($i = 1; $i <= 20; $i++) {
            $selected = $i === 1 ? ' selected' : '';
            $html .= '<option value="' . $i . '"' . $selected . '>' . $i . ' ' . ($i === 1 ? __('floor', 'pro-clean-quotation') : __('floors', 'pro-clean-quotation')) . '</option>';
        }
        $html .= '</select>';
        $html .= '</div>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="property_type">' . __('Property Type', 'pro-clean-quotation') . '</label>';
        $html .= '<select id="property_type" name="property_type">';
        $html .= '<option value="residential">' . __('Residential', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="commercial">' . __('Commercial', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="industrial">' . __('Industrial', 'pro-clean-quotation') . '</option>';
        $html .= '</select>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Surface Material
        $html .= '<div class="pcq-form-section pcq-surface-material">';
        $html .= '<h4>' . __('Surface Material', 'pro-clean-quotation') . '</h4>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<select id="surface_material" name="surface_material">';
        $html .= '<option value="brick">' . __('Brick', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="stone">' . __('Stone', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="glass">' . __('Glass', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="metal">' . __('Metal', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="concrete">' . __('Concrete', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="composite">' . __('Composite', 'pro-clean-quotation') . '</option>';
        $html .= '</select>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Roof Type (conditional)
        $html .= '<div class="pcq-form-section pcq-roof-type" style="display: none;">';
        $html .= '<h4>' . __('Roof Type', 'pro-clean-quotation') . '</h4>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<select id="roof_type" name="roof_type">';
        $html .= '<option value="">' . __('Select roof type', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="flat">' . __('Flat Roof', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="pitched">' . __('Pitched Roof', 'pro-clean-quotation') . '</option>';
        $html .= '<option value="complex">' . __('Complex Roof', 'pro-clean-quotation') . '</option>';
        $html .= '</select>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Contact Information
        $html .= '<div class="pcq-form-section pcq-contact-info">';
        $html .= '<h4>' . __('Contact Information', 'pro-clean-quotation') . '</h4>';
        $html .= '<div class="pcq-form-row">';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="customer_name">' . __('Full Name', 'pro-clean-quotation') . ' <span class="required">*</span></label>';
        $html .= '<input type="text" id="customer_name" name="customer_name" required>';
        $html .= '</div>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="customer_email">' . __('Email Address', 'pro-clean-quotation') . ' <span class="required">*</span></label>';
        $html .= '<input type="email" id="customer_email" name="customer_email" required>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="pcq-form-row">';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="customer_phone">' . __('Phone Number', 'pro-clean-quotation') . ' <span class="required">*</span></label>';
        $html .= '<input type="tel" id="customer_phone" name="customer_phone" required placeholder="' . __('Enter your phone number', 'pro-clean-quotation') . '">';
        $html .= '</div>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="postal_code">' . __('Postal Code', 'pro-clean-quotation') . ' <span class="required">*</span></label>';
        $html .= '<input type="text" id="postal_code" name="postal_code" pattern="^(0[1-9]|[1-4][0-9]|5[0-2])[0-9]{3}$" placeholder="28001" maxlength="5" title="' . __('Enter a valid Spanish postal code (5 digits, e.g., 28001, 29600). Valid range: 01001-52999', 'pro-clean-quotation') . '" required>';
        $html .= '<small class="pcq-field-help">' . __('Format: 5-digit code (e.g., 28001 for Madrid, 29600 for Marbella)', 'pro-clean-quotation') . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="property_address">' . __('Property Address', 'pro-clean-quotation') . ' <span class="required">*</span></label>';
        $html .= '<textarea id="property_address" name="property_address" rows="2" required></textarea>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Special Requirements
        $html .= '<div class="pcq-form-section pcq-special-requirements">';
        $html .= '<h4>' . __('Additional Information', 'pro-clean-quotation') . '</h4>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="special_requirements">' . __('Special Requirements', 'pro-clean-quotation') . '</label>';
        $html .= '<textarea id="special_requirements" name="special_requirements" rows="3" maxlength="500" placeholder="' . __('Any special requirements or notes...', 'pro-clean-quotation') . '"></textarea>';
        $html .= '<small>' . __('Maximum 500 characters', 'pro-clean-quotation') . '</small>';
        $html .= '</div>';
        $html .= '<div class="pcq-form-field">';
        $html .= '<label for="last_cleaning_date">' . __('Last Cleaning Date', 'pro-clean-quotation') . '</label>';
        $html .= '<input type="date" id="last_cleaning_date" name="last_cleaning_date">';
        $html .= '<small>' . __('When was the property last cleaned? (Optional)', 'pro-clean-quotation') . '</small>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Price Display
        $html .= '<div class="pcq-price-display" style="display: none;">';
        $html .= '<h4>' . __('Estimated Quote', 'pro-clean-quotation') . '</h4>';
        $html .= '<div class="pcq-price-breakdown"></div>';
        $html .= '</div>';
        
        // Legal Checkboxes
        $html .= '<div class="pcq-form-section pcq-legal">';
        $html .= '<div class="pcq-checkbox-field">';
        $html .= '<label>';
        $html .= '<input type="checkbox" name="privacy_consent" required>';
        $html .= sprintf(__('I agree to the %s and consent to the processing of my personal data.', 'pro-clean-quotation'), '<a href="' . get_privacy_policy_url() . '" target="_blank">' . __('Privacy Policy', 'pro-clean-quotation') . '</a>');
        $html .= ' <span class="required">*</span>';
        $html .= '</label>';
        $html .= '</div>';
        $html .= '<div class="pcq-checkbox-field">';
        $html .= '<label>';
        $html .= '<input type="checkbox" name="marketing_consent">';
        $html .= __('I would like to receive marketing communications and special offers.', 'pro-clean-quotation');
        $html .= '</label>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Submit Button
        $html .= '<div class="pcq-form-actions">';
        $html .= '<button type="submit" class="pcq-submit-btn">';
        $html .= '<span class="pcq-btn-text">' . __('Get My Quote', 'pro-clean-quotation') . '</span>';
        $html .= '<span class="pcq-btn-loading" style="display: none;">' . __('Calculating...', 'pro-clean-quotation') . '</span>';
        $html .= '</button>';
        $html .= '</div>';
        
        // Messages
        $html .= '<div class="pcq-form-messages"></div>';
        
        // Security
        $html .= wp_nonce_field('pcq_nonce', 'pcq_nonce', true, false);
        
        $html .= '</form>';
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Get default booking form HTML
     * 
     * @param array $atts Attributes
     * @return string HTML
     */
    private function getDefaultBookingFormHTML(array $atts): string {
        return '<div class="pcq-booking-form-container">' .
               '<p>' . __('Booking form integration with MotoPress Appointment will be displayed here.', 'pro-clean-quotation') . '</p>' .
               '</div>';
    }
    
    /**
     * Get default calculator HTML
     * 
     * @param array $atts Attributes
     * @return string HTML
     */
    private function getDefaultCalculatorHTML(array $atts): string {
        return '<div class="pcq-calculator-container">' .
               '<p>' . __('Quick price calculator will be displayed here.', 'pro-clean-quotation') . '</p>' .
               '</div>';
    }
}