<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Admin\Settings;

/**
 * Validation Service
 * Handles all form validation, rate limiting, and duplicate prevention
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class ValidationService {
    
    /**
     * Service instance
     * 
     * @var ValidationService
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return ValidationService
     */
    public static function getInstance(): ValidationService {
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
     * Check for duplicate submission
     * Prevents same email from submitting within cooldown period
     * 
     * @param string $email Customer email
     * @param int $cooldown_minutes Cooldown period in minutes (default: 5)
     * @return array Result with success status and message
     */
    public function checkDuplicateSubmission(string $email, int $cooldown_minutes = 5): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_quotes';
        $cooldown_time = date('Y-m-d H:i:s', strtotime("-{$cooldown_minutes} minutes"));
        
        // Check if email exists in recent submissions
        $recent_quote = $wpdb->get_row($wpdb->prepare(
            "SELECT id, quote_number, created_at FROM {$table} 
             WHERE customer_email = %s 
             AND created_at > %s 
             ORDER BY created_at DESC 
             LIMIT 1",
            $email,
            $cooldown_time
        ));
        
        if ($recent_quote) {
            $time_diff = time() - strtotime($recent_quote->created_at);
            $minutes_left = $cooldown_minutes - floor($time_diff / 60);
            
            return [
                'is_duplicate' => true,
                'message' => sprintf(
                    __('You recently submitted a quote (Reference: %s). Please wait %d minutes before submitting another quote.', 'pro-clean-quotation'),
                    $recent_quote->quote_number,
                    $minutes_left
                ),
                'quote_number' => $recent_quote->quote_number,
                'wait_time' => $minutes_left
            ];
        }
        
        return [
            'is_duplicate' => false,
            'message' => ''
        ];
    }
    
    /**
     * Check rate limiting by IP address
     * 
     * @param int $max_submissions Maximum submissions allowed
     * @param int $window_minutes Time window in minutes
     * @return array Result with allowed status and message
     */
    public function checkRateLimit(int $max_submissions = 5, int $window_minutes = 5): array {
        $ip = $this->getClientIP();
        $transient_key = 'pcq_rate_limit_' . md5($ip);
        
        $submissions = get_transient($transient_key);
        
        if ($submissions === false) {
            // No previous submissions in this window
            return [
                'allowed' => true,
                'remaining' => $max_submissions,
                'message' => ''
            ];
        }
        
        $submissions = intval($submissions);
        
        if ($submissions >= $max_submissions) {
            return [
                'allowed' => false,
                'remaining' => 0,
                'message' => sprintf(
                    __('Too many submissions from your IP address. Please wait %d minutes before trying again.', 'pro-clean-quotation'),
                    $window_minutes
                ),
                'retry_after' => $window_minutes * 60
            ];
        }
        
        return [
            'allowed' => true,
            'remaining' => $max_submissions - $submissions,
            'message' => ''
        ];
    }
    
    /**
     * Update rate limiting counter
     * 
     * @param int $window_minutes Time window in minutes
     */
    public function updateRateLimit(int $window_minutes = 5): void {
        $ip = $this->getClientIP();
        $transient_key = 'pcq_rate_limit_' . md5($ip);
        
        $submissions = get_transient($transient_key);
        $submissions = $submissions === false ? 0 : intval($submissions);
        
        set_transient($transient_key, $submissions + 1, $window_minutes * 60);
        
        // Log submission for monitoring
        error_log(sprintf(
            'PCQ: Rate limit updated for IP %s - Submissions: %d/%d',
            $ip,
            $submissions + 1,
            Settings::get('rate_limit_submissions', 5)
        ));
    }
    
    /**
     * Validate postal code format
     * 
     * @param string $postal_code Postal code to validate
     * @param string $country Country code (default: NL for Netherlands)
     * @return array Validation result
     */
    public function validatePostalCode(string $postal_code, string $country = 'NL'): array {
        $postal_code = trim($postal_code);
        
        // Country-specific validation patterns
        $patterns = [
            'NL' => '/^[1-9][0-9]{3}\s?[A-Z]{2}$/i',  // Netherlands: 1234 AB or 1234AB
            'BE' => '/^[1-9][0-9]{3}$/i',              // Belgium: 1234
            'DE' => '/^[0-9]{5}$/i',                   // Germany: 12345
            'FR' => '/^[0-9]{5}$/i',                   // France: 12345
            'UK' => '/^[A-Z]{1,2}[0-9]{1,2}[A-Z]?\s?[0-9][A-Z]{2}$/i', // UK: SW1A 1AA
        ];
        
        $pattern = $patterns[$country] ?? $patterns['NL'];
        
        if (!preg_match($pattern, $postal_code)) {
            return [
                'valid' => false,
                'message' => __('Invalid postal code format.', 'pro-clean-quotation'),
                'formatted' => ''
            ];
        }
        
        // Format postal code (add space for NL format if missing)
        $formatted = $postal_code;
        if ($country === 'NL' && !preg_match('/\s/', $postal_code)) {
            $formatted = substr($postal_code, 0, 4) . ' ' . strtoupper(substr($postal_code, 4, 2));
        }
        
        return [
            'valid' => true,
            'message' => '',
            'formatted' => strtoupper($formatted)
        ];
    }
    
    /**
     * Check if postal code is in service area
     * 
     * @param string $postal_code Postal code
     * @return array Result with service availability
     */
    public function checkServiceArea(string $postal_code): array {
        $service_areas = Settings::get('service_area_postcodes', []);
        
        // If no service areas configured, allow all
        if (empty($service_areas)) {
            return [
                'available' => true,
                'message' => ''
            ];
        }
        
        // Extract numeric part of postal code (first 4 digits for NL)
        $numeric_part = substr(preg_replace('/[^0-9]/', '', $postal_code), 0, 4);
        
        foreach ($service_areas as $area) {
            $area = trim($area);
            
            // Support range format: 1000-2000
            if (strpos($area, '-') !== false) {
                list($start, $end) = explode('-', $area);
                $start = intval(trim($start));
                $end = intval(trim($end));
                $postal_num = intval($numeric_part);
                
                if ($postal_num >= $start && $postal_num <= $end) {
                    return [
                        'available' => true,
                        'message' => '',
                        'area' => $area
                    ];
                }
            } else {
                // Prefix match
                if (strpos($numeric_part, $area) === 0) {
                    return [
                        'available' => true,
                        'message' => '',
                        'area' => $area
                    ];
                }
            }
        }
        
        return [
            'available' => false,
            'message' => sprintf(
                __('Sorry, we currently do not service postal code %s. Please contact us for more information.', 'pro-clean-quotation'),
                $postal_code
            ),
            'postal_code' => $postal_code
        ];
    }
    
    /**
     * Validate phone number
     * 
     * @param string $phone Phone number
     * @param string $country Country code
     * @return array Validation result
     */
    public function validatePhone(string $phone, string $country = 'NL'): array {
        // Remove all whitespace and special characters except +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        
        // Country-specific patterns
        $patterns = [
            'NL' => '/^(\+31|0031|0)[1-9][0-9]{8}$/',  // Netherlands: +31612345678 or 0612345678
            'BE' => '/^(\+32|0032|0)[1-9][0-9]{8}$/',  // Belgium
            'DE' => '/^(\+49|0049|0)[1-9][0-9]{9,10}$/', // Germany
            'FR' => '/^(\+33|0033|0)[1-9][0-9]{8}$/',  // France
            'UK' => '/^(\+44|0044|0)[1-9][0-9]{9,10}$/', // UK
        ];
        
        $pattern = $patterns[$country] ?? $patterns['NL'];
        
        if (!preg_match($pattern, $cleaned)) {
            return [
                'valid' => false,
                'message' => __('Invalid phone number format.', 'pro-clean-quotation'),
                'formatted' => ''
            ];
        }
        
        // Format phone number for NL
        if ($country === 'NL') {
            if (substr($cleaned, 0, 1) === '0') {
                // Convert 0612345678 to +31612345678
                $cleaned = '+31' . substr($cleaned, 1);
            } elseif (substr($cleaned, 0, 4) === '0031') {
                // Convert 0031612345678 to +31612345678
                $cleaned = '+31' . substr($cleaned, 4);
            }
        }
        
        return [
            'valid' => true,
            'message' => '',
            'formatted' => $cleaned
        ];
    }
    
    /**
     * Validate email address with additional checks
     * 
     * @param string $email Email address
     * @return array Validation result
     */
    public function validateEmail(string $email): array {
        $email = trim(strtolower($email));
        
        // Basic WordPress email validation
        if (!is_email($email)) {
            return [
                'valid' => false,
                'message' => __('Invalid email address format.', 'pro-clean-quotation'),
                'sanitized' => ''
            ];
        }
        
        // Check for disposable email domains (common spam sources)
        $disposable_domains = [
            'tempmail.com', 'throwaway.email', '10minutemail.com', 
            'guerrillamail.com', 'mailinator.com', 'maildrop.cc',
            'temp-mail.org', 'getnada.com', 'fakeinbox.com'
        ];
        
        $domain = substr(strrchr($email, "@"), 1);
        
        if (Settings::get('block_disposable_emails', true) && in_array($domain, $disposable_domains)) {
            return [
                'valid' => false,
                'message' => __('Disposable email addresses are not allowed. Please use a permanent email address.', 'pro-clean-quotation'),
                'sanitized' => '',
                'reason' => 'disposable_domain'
            ];
        }
        
        return [
            'valid' => true,
            'message' => '',
            'sanitized' => sanitize_email($email)
        ];
    }
    
    /**
     * Check for spam patterns in text
     * 
     * @param string $text Text to check
     * @return array Spam detection result
     */
    public function checkSpamPatterns(string $text): array {
        if (empty($text)) {
            return [
                'is_spam' => false,
                'score' => 0,
                'patterns' => []
            ];
        }
        
        $spam_patterns = [
            '/viagra|cialis|pharmacy/i' => 10,
            '/casino|poker|gambling/i' => 10,
            '/SEO services|link building/i' => 8,
            '/bitcoin|cryptocurrency|investment/i' => 7,
            '/http[s]?:\/\/.*http[s]?:\/\//i' => 8, // Multiple URLs
            '/\b([A-Z]{3,})\b.*\b([A-Z]{3,})\b/i' => 5, // Multiple ALL CAPS words
            '/\$\$\$|\!\!\!/i' => 6, // Multiple special characters
        ];
        
        $spam_score = 0;
        $detected_patterns = [];
        
        foreach ($spam_patterns as $pattern => $score) {
            if (preg_match($pattern, $text)) {
                $spam_score += $score;
                $detected_patterns[] = $pattern;
            }
        }
        
        return [
            'is_spam' => $spam_score >= 10,
            'score' => $spam_score,
            'patterns' => $detected_patterns,
            'message' => $spam_score >= 10 
                ? __('Your submission contains content that appears to be spam.', 'pro-clean-quotation')
                : ''
        ];
    }
    
    /**
     * Get client IP address (handles proxies and load balancers)
     * 
     * @return string Client IP address
     */
    private function getClientIP(): string {
        $ip_keys = [
            'HTTP_CF_CONNECTING_IP',  // Cloudflare
            'HTTP_X_FORWARDED_FOR',   // Proxy
            'HTTP_X_REAL_IP',         // Nginx proxy
            'REMOTE_ADDR'             // Direct connection
        ];
        
        foreach ($ip_keys as $key) {
            if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                
                // Handle comma-separated IPs (from proxies)
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Validate IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Get rate limit statistics for admin dashboard
     * 
     * @return array Rate limit statistics
     */
    public function getRateLimitStats(): array {
        global $wpdb;
        
        // Get all rate limit transients from options table
        $results = $wpdb->get_results(
            "SELECT option_name, option_value 
             FROM {$wpdb->options} 
             WHERE option_name LIKE '_transient_pcq_rate_limit_%'",
            ARRAY_A
        );
        
        $total_ips = count($results);
        $total_submissions = 0;
        
        foreach ($results as $row) {
            $total_submissions += intval($row['option_value']);
        }
        
        return [
            'active_ips' => $total_ips,
            'total_submissions' => $total_submissions,
            'max_per_ip' => Settings::get('rate_limit_submissions', 5),
            'window_minutes' => 5
        ];
    }
}
