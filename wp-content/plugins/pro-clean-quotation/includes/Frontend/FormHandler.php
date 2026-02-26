<?php

namespace ProClean\Quotation\Frontend;

use ProClean\Quotation\Models\Quote;
use ProClean\Quotation\Services\QuoteCalculator;
use ProClean\Quotation\Services\ValidationService;
use ProClean\Quotation\Email\EmailManager;
use ProClean\Quotation\Admin\Settings;

/**
 * Frontend Form Handler
 * 
 * @package ProClean\Quotation\Frontend
 * @since 1.0.0
 */
class FormHandler {
    
    /**
     * Form handler instance
     * 
     * @var FormHandler
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return FormHandler
     */
    public static function getInstance(): FormHandler {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor for singleton
    }
    
    /**
     * Submit quote form
     * 
     * @param array $data Form data
     * @return array Submission result
     */
    public function submitQuote(array $data): array {
        try {
            // Check maintenance mode
            if (Settings::get('maintenance_mode', false)) {
                return [
                    'success' => false,
                    'message' => Settings::get('maintenance_message', __('The quotation system is temporarily unavailable.', 'pro-clean-quotation'))
                ];
            }
            
            $validator = ValidationService::getInstance();
            
            // Rate limiting check
            $rate_limit = $validator->checkRateLimit(
                Settings::get('rate_limit_submissions', 5),
                Settings::get('rate_limit_window', 5)
            );
            
            if (!$rate_limit['allowed']) {
                return [
                    'success' => false,
                    'message' => $rate_limit['message']
                ];
            }
            
            // Duplicate submission check (5-minute cooldown per email)
            $email = sanitize_email($data['customer_email'] ?? '');
            if (!empty($email)) {
                $duplicate_check = $validator->checkDuplicateSubmission($email, 5);
                
                if ($duplicate_check['is_duplicate']) {
                    return [
                        'success' => false,
                        'message' => $duplicate_check['message'],
                        'data' => [
                            'is_duplicate' => true,
                            'previous_quote' => $duplicate_check['quote_number'],
                            'wait_time' => $duplicate_check['wait_time']
                        ]
                    ];
                }
            }
            
            // Validate form data
            $validation = $this->validateFormData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'errors' => $validation['errors']
                ];
            }
            
            // Check service area
            $postal_code = $data['postal_code'] ?? '';
            if (!empty($postal_code)) {
                $service_area = $validator->checkServiceArea($postal_code);
                
                if (!$service_area['available']) {
                    return [
                        'success' => false,
                        'message' => $service_area['message'],
                        'errors' => ['postal_code' => $service_area['message']]
                    ];
                }
            }
            
            // Calculate quote
            $calculator = QuoteCalculator::getInstance();
            $calculation = $calculator->calculateQuote($data);
            
            if (!$calculation['success']) {
                return $calculation;
            }
            
            // Create quote record
            $quote_data = $this->prepareQuoteData($data, $calculation['data']);
            $quote = Quote::create($quote_data);
            
            if (!$quote) {
                return [
                    'success' => false,
                    'message' => __('Failed to save quote. Please try again.', 'pro-clean-quotation')
                ];
            }
            
            // Send confirmation email (wrap in try-catch to prevent email failures from breaking submission)
            $email_sent = false;
            try {
                $email_manager = EmailManager::getInstance();
                $email_sent = $email_manager->sendQuoteConfirmation($quote);
                
                // Send admin notification
                $email_manager->sendAdminNotification($quote);
            } catch (\Throwable $emailError) {
                // Log email error but don't fail the submission
                error_log('PCQ: Email sending failed: ' . $emailError->getMessage());
                error_log('PCQ: Email error stack: ' . $emailError->getTraceAsString());
            }
            
            // Update rate limiting
            $validator->updateRateLimit(Settings::get('rate_limit_window', 5));
            
            return [
                'success' => true,
                'message' => __('Your quote has been generated successfully! Check your email for details.', 'pro-clean-quotation'),
                'data' => [
                    'quote_id' => $quote->getId(),
                    'quote_number' => $quote->getQuoteNumber(),
                    'total_price' => $calculation['data']['total'],
                    'valid_until' => $calculation['data']['valid_until'],
                    'email_sent' => $email_sent,
                    'booking_url' => $this->generateBookingUrl($quote)
                ]
            ];
            
        } catch (\Throwable $e) {
            error_log('PCQ Form submission error: ' . $e->getMessage());
            error_log('PCQ: Stack trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => __('An error occurred while processing your request. Please try again.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Validate form data
     * 
     * @param array $data Form data
     * @return array Validation result
     */
    private function validateFormData(array $data): array {
        $errors = [];
        $required_fields = Settings::get('form_fields_required', []);
        $validator = ValidationService::getInstance();
        
        // Required field validation
        $field_labels = [
            'customer_name' => __('Full Name', 'pro-clean-quotation'),
            'customer_email' => __('Email Address', 'pro-clean-quotation'),
            'customer_phone' => __('Phone Number', 'pro-clean-quotation'),
            'property_address' => __('Property Address', 'pro-clean-quotation'),
            'postal_code' => __('Postal Code', 'pro-clean-quotation'),
            'service_type' => __('Service Type', 'pro-clean-quotation'),
            'square_meters' => __('Square Meters', 'pro-clean-quotation'),
            'privacy_consent' => __('Privacy Policy Agreement', 'pro-clean-quotation'),
        ];
        
        foreach ($required_fields as $field => $is_required) {
            if ($is_required && empty($data[$field])) {
                $errors[$field] = sprintf(__('%s is required.', 'pro-clean-quotation'), $field_labels[$field] ?? $field);
            }
        }
        
        // Email validation with enhanced checks
        if (!empty($data['customer_email'])) {
            $email_check = $validator->validateEmail($data['customer_email']);
            
            if (!$email_check['valid']) {
                $errors['customer_email'] = $email_check['message'];
            }
        }
        
        // Phone validation with formatting
        if (!empty($data['customer_phone'])) {
            $phone_check = $validator->validatePhone(
                $data['customer_phone'],
                Settings::get('phone_country_code', 'ES')
            );
            
            if (!$phone_check['valid']) {
                $errors['customer_phone'] = $phone_check['message'];
            }
        }
        
        // Postal code validation with formatting
        if (!empty($data['postal_code'])) {
            $postal_check = $validator->validatePostalCode(
                $data['postal_code'],
                Settings::get('postal_code_country', 'ES')
            );
            
            if (!$postal_check['valid']) {
                $errors['postal_code'] = $postal_check['message'];
            }
        }
        
        // Service type validation - validate against database services
        if (!empty($data['service_type'])) {
            $service_id = intval($data['service_type']);
            $service = new \ProClean\Quotation\Models\Service($service_id);
            
            if (!$service->getId() || !$service->isActive()) {
                $errors['service_type'] = __('Invalid service type selected.', 'pro-clean-quotation');
            }
        }
        
        // Measurements validation
        $square_meters = floatval($data['square_meters'] ?? 0);
        if ($square_meters < 10 || $square_meters > 10000) {
            $errors['square_meters'] = __('Square meters must be between 10 and 10,000.', 'pro-clean-quotation');
        }
        
        if (!empty($data['linear_meters'])) {
            $linear_meters = floatval($data['linear_meters']);
            if ($linear_meters < 5 || $linear_meters > 5000) {
                $errors['linear_meters'] = __('Linear meters must be between 5 and 5,000.', 'pro-clean-quotation');
            }
        }
        
        // Building height validation
        $building_height = intval($data['building_height'] ?? 1);
        if ($building_height < 1 || $building_height > 20) {
            $errors['building_height'] = __('Building height must be between 1 and 20 floors.', 'pro-clean-quotation');
        }
        
        // Privacy consent validation
        if (!empty($required_fields['privacy_consent']) && empty($data['privacy_consent'])) {
            $errors['privacy_consent'] = __('You must agree to the privacy policy to continue.', 'pro-clean-quotation');
        }
        
        // Special requirements validation with spam checking
        if (!empty($data['special_requirements'])) {
            if (strlen($data['special_requirements']) > 500) {
                $errors['special_requirements'] = __('Special requirements must be less than 500 characters.', 'pro-clean-quotation');
            }
            
            // Check for spam patterns
            $spam_check = $validator->checkSpamPatterns($data['special_requirements']);
            if ($spam_check['is_spam']) {
                $errors['special_requirements'] = $spam_check['message'];
                error_log('PCQ: Spam detected in special requirements - Score: ' . $spam_check['score']);
            }
        }
        
        // reCAPTCHA validation
        if (Settings::get('enable_recaptcha', false)) {
            if (!$this->validateRecaptcha($data['recaptcha_response'] ?? '')) {
                $errors['recaptcha'] = __('Please complete the reCAPTCHA verification.', 'pro-clean-quotation');
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : __('Please correct the following errors:', 'pro-clean-quotation')
        ];
    }
    
    /**
     * Validate reCAPTCHA
     * 
     * @param string $response reCAPTCHA response
     * @return bool
     */
    private function validateRecaptcha(string $response): bool {
        if (empty($response)) {
            return false;
        }
        
        $secret_key = Settings::get('recaptcha_secret_key', '');
        if (empty($secret_key)) {
            return true; // Skip validation if not configured
        }
        
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = [
            'secret' => $secret_key,
            'response' => $response,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        $response = wp_remote_post($verify_url, [
            'body' => $data,
            'timeout' => 10
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $result = json_decode($body, true);
        
        return isset($result['success']) && $result['success'] === true;
    }
    
    /**
     * Prepare quote data for database
     * 
     * @param array $form_data Form data
     * @param array $calculation_data Calculation data
     * @return array Quote data
     */
    private function prepareQuoteData(array $form_data, array $calculation_data): array {
        // Process custom fields data
        $custom_field_data = [];
        if (!empty($form_data['custom_fields']) && is_array($form_data['custom_fields'])) {
            foreach ($form_data['custom_fields'] as $field_id => $value) {
                if (!empty($value)) {
                    // Handle both array format (from JS) and simple string format
                    if (is_array($value)) {
                        $custom_field_data[sanitize_key($field_id)] = sanitize_text_field($value['value'] ?? '');
                    } else {
                        $custom_field_data[sanitize_key($field_id)] = sanitize_text_field($value);
                    }
                }
            }
        }
        
        return [
            'quote_number' => $this->generateQuoteNumber(),
            'customer_name' => sanitize_text_field($form_data['customer_name'] ?? ''),
            'customer_email' => sanitize_email($form_data['customer_email'] ?? ''),
            'customer_phone' => sanitize_text_field($form_data['customer_phone'] ?? ''),
            'property_address' => sanitize_textarea_field($form_data['property_address'] ?? ''),
            'postal_code' => sanitize_text_field($form_data['postal_code'] ?? ''),
            'service_type' => sanitize_text_field($form_data['service_type'] ?? ''),
            'square_meters' => floatval($form_data['square_meters'] ?? 0),
            'linear_meters' => floatval($form_data['linear_meters'] ?? 0),
            'building_height' => intval($form_data['building_height'] ?? 1),
            'property_type' => sanitize_text_field($form_data['property_type'] ?? 'residential'),
            'surface_material' => sanitize_text_field($form_data['surface_material'] ?? 'brick'),
            'roof_type' => sanitize_text_field($form_data['roof_type'] ?? ''),
            'last_cleaning_date' => !empty($form_data['last_cleaning_date']) ? sanitize_text_field($form_data['last_cleaning_date']) : null,
            'special_requirements' => sanitize_textarea_field($form_data['special_requirements'] ?? ''),
            'custom_field_data' => !empty($custom_field_data) ? json_encode($custom_field_data) : null,
            'base_price' => $calculation_data['base_rate'] ?? 0,
            'adjustments' => $calculation_data['adjustments'] ?? 0,
            'subtotal' => $calculation_data['subtotal'] ?? 0,
            'tax_amount' => $calculation_data['tax_amount'] ?? 0,
            'total_price' => $calculation_data['total'] ?? 0,
            'price_breakdown' => json_encode($calculation_data['breakdown'] ?? []),
            'status' => 'new',
            'valid_until' => $calculation_data['valid_until'] ?? date('Y-m-d', strtotime('+30 days')),
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'marketing_consent' => !empty($form_data['marketing_consent']) ? 1 : 0,
            'privacy_consent' => !empty($form_data['privacy_consent']) ? 1 : 0,
            'created_at' => current_time('mysql'),
        ];
    }
    
    /**
     * Generate unique quote number
     * 
     * @return string Quote number
     */
    private function generateQuoteNumber(): string {
        $prefix = 'PCQ';
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $date . '-' . $random;
    }
    
    /**
     * Generate booking URL
     * 
     * @param Quote $quote Quote object
     * @return string Booking URL
     */
    private function generateBookingUrl(Quote $quote): string {
        $booking_page = get_option('pcq_booking_page_id');
        
        if ($booking_page) {
            return add_query_arg([
                'quote_id' => $quote->getId(),
                'token' => $quote->getToken()
            ], get_permalink($booking_page));
        }
        
        return home_url('/book-service/?quote_id=' . $quote->getId() . '&token=' . $quote->getToken());
    }
}