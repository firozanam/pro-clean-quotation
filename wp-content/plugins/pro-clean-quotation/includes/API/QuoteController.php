<?php

namespace ProClean\Quotation\API;

use ProClean\Quotation\Services\QuoteCalculator;
use ProClean\Quotation\Frontend\FormHandler;
use ProClean\Quotation\Models\Quote;

/**
 * Quote REST API Controller
 * 
 * @package ProClean\Quotation\API
 * @since 1.0.0
 */
class QuoteController {
    
    /**
     * Controller instance
     * 
     * @var QuoteController
     */
    private static $instance = null;
    
    /**
     * API namespace
     */
    const NAMESPACE = 'pq/v1';
    
    /**
     * Get instance
     * 
     * @return QuoteController
     */
    public static function getInstance(): QuoteController {
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
     * Register REST API routes
     */
    public function registerRoutes(): void {
        // Calculate quote endpoint
        register_rest_route(self::NAMESPACE, '/calculate-quote', [
            'methods' => 'POST',
            'callback' => [$this, 'calculateQuote'],
            'permission_callback' => [$this, 'publicPermissionCallback'],
            'args' => $this->getCalculationArgs()
        ]);
        
        // Submit quote endpoint
        register_rest_route(self::NAMESPACE, '/submit-quote', [
            'methods' => 'POST',
            'callback' => [$this, 'submitQuote'],
            'permission_callback' => [$this, 'publicPermissionCallback'],
            'args' => $this->getSubmissionArgs()
        ]);
        
        // Get quote endpoint
        register_rest_route(self::NAMESPACE, '/quote/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getQuote'],
            'permission_callback' => [$this, 'quotePermissionCallback'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'minimum' => 1
                ],
                'token' => [
                    'required' => true,
                    'type' => 'string',
                    'minLength' => 32
                ]
            ]
        ]);
        
        // Admin: Get quotes list
        register_rest_route(self::NAMESPACE, '/admin/quotes', [
            'methods' => 'GET',
            'callback' => [$this, 'getQuotes'],
            'permission_callback' => [$this, 'adminPermissionCallback'],
            'args' => [
                'page' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'default' => 1
                ],
                'per_page' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 100,
                    'default' => 20
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['new', 'viewed', 'booked', 'expired', 'declined']
                ],
                'service_type' => [
                    'type' => 'string',
                    'enum' => ['facade', 'roof', 'both']
                ],
                'search' => [
                    'type' => 'string',
                    'maxLength' => 255
                ]
            ]
        ]);
        
        // Admin: Update quote status
        register_rest_route(self::NAMESPACE, '/admin/quote/(?P<id>\d+)/status', [
            'methods' => 'PATCH',
            'callback' => [$this, 'updateQuoteStatus'],
            'permission_callback' => [$this, 'adminPermissionCallback'],
            'args' => [
                'id' => [
                    'required' => true,
                    'type' => 'integer',
                    'minimum' => 1
                ],
                'status' => [
                    'required' => true,
                    'type' => 'string',
                    'enum' => ['new', 'viewed', 'booked', 'expired', 'declined']
                ]
            ]
        ]);
    }
    
    /**
     * Calculate quote endpoint
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function calculateQuote(\WP_REST_Request $request): \WP_REST_Response {
        try {
            $calculator = QuoteCalculator::getInstance();
            $result = $calculator->calculateQuote($request->get_params());
            
            if ($result['success']) {
                return new \WP_REST_Response($result, 200);
            } else {
                return new \WP_REST_Response($result, 400);
            }
            
        } catch (\Exception $e) {
            error_log('PCQ API Calculate Quote Error: ' . $e->getMessage());
            
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('An error occurred while calculating the quote.', 'pro-clean-quotation')
            ], 500);
        }
    }
    
    /**
     * Submit quote endpoint
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function submitQuote(\WP_REST_Request $request): \WP_REST_Response {
        try {
            $form_handler = FormHandler::getInstance();
            $result = $form_handler->submitQuote($request->get_params());
            
            if ($result['success']) {
                return new \WP_REST_Response($result, 201);
            } else {
                return new \WP_REST_Response($result, 400);
            }
            
        } catch (\Exception $e) {
            error_log('PCQ API Submit Quote Error: ' . $e->getMessage());
            
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('An error occurred while submitting the quote.', 'pro-clean-quotation')
            ], 500);
        }
    }
    
    /**
     * Get quote endpoint
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function getQuote(\WP_REST_Request $request): \WP_REST_Response {
        $quote_id = $request->get_param('id');
        $token = $request->get_param('token');
        
        $quote = new Quote($quote_id);
        
        if (!$quote->getId()) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Quote not found.', 'pro-clean-quotation')
            ], 404);
        }
        
        if (!$quote->verifyToken($token)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Invalid token.', 'pro-clean-quotation')
            ], 403);
        }
        
        // Mark as viewed if status is new
        if ($quote->getStatus() === 'new') {
            $quote->setStatus('viewed');
            $quote->save();
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $this->formatQuoteForAPI($quote)
        ], 200);
    }
    
    /**
     * Get quotes list (admin)
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function getQuotes(\WP_REST_Request $request): \WP_REST_Response {
        $page = $request->get_param('page');
        $per_page = $request->get_param('per_page');
        
        $filters = array_filter([
            'status' => $request->get_param('status'),
            'service_type' => $request->get_param('service_type'),
            'search' => $request->get_param('search')
        ]);
        
        $quotes_data = Quote::getAll($page, $per_page, $filters);
        
        $formatted_quotes = array_map([$this, 'formatQuoteForAPI'], $quotes_data['quotes']);
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $formatted_quotes,
            'pagination' => [
                'total' => $quotes_data['total'],
                'pages' => $quotes_data['pages'],
                'current_page' => $quotes_data['current_page'],
                'per_page' => $quotes_data['per_page']
            ]
        ], 200);
    }
    
    /**
     * Update quote status (admin)
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function updateQuoteStatus(\WP_REST_Request $request): \WP_REST_Response {
        $quote_id = $request->get_param('id');
        $status = $request->get_param('status');
        
        $quote = new Quote($quote_id);
        
        if (!$quote->getId()) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Quote not found.', 'pro-clean-quotation')
            ], 404);
        }
        
        $quote->setStatus($status);
        
        if ($quote->save()) {
            return new \WP_REST_Response([
                'success' => true,
                'message' => __('Quote status updated successfully.', 'pro-clean-quotation'),
                'data' => $this->formatQuoteForAPI($quote)
            ], 200);
        } else {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Failed to update quote status.', 'pro-clean-quotation')
            ], 500);
        }
    }
    
    /**
     * Public permission callback
     * 
     * @return bool Always true for public endpoints
     */
    public function publicPermissionCallback(): bool {
        return true;
    }
    
    /**
     * Quote permission callback (requires valid token)
     * 
     * @param \WP_REST_Request $request Request object
     * @return bool Permission status
     */
    public function quotePermissionCallback(\WP_REST_Request $request): bool {
        $quote_id = $request->get_param('id');
        $token = $request->get_param('token');
        
        if (!$quote_id || !$token) {
            return false;
        }
        
        $quote = new Quote($quote_id);
        return $quote->getId() && $quote->verifyToken($token);
    }
    
    /**
     * Admin permission callback
     * 
     * @return bool Permission status
     */
    public function adminPermissionCallback(): bool {
        return current_user_can('manage_options');
    }
    
    /**
     * Get calculation arguments schema
     * 
     * @return array Arguments schema
     */
    private function getCalculationArgs(): array {
        return [
            'service_type' => [
                'required' => true,
                'type' => 'string',
                'enum' => ['facade', 'roof', 'both'],
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'square_meters' => [
                'required' => true,
                'type' => 'number',
                'minimum' => 10,
                'maximum' => 10000
            ],
            'linear_meters' => [
                'type' => 'number',
                'minimum' => 0,
                'maximum' => 5000,
                'default' => 0
            ],
            'building_height' => [
                'type' => 'integer',
                'minimum' => 1,
                'maximum' => 20,
                'default' => 1
            ],
            'property_type' => [
                'type' => 'string',
                'enum' => ['residential', 'commercial', 'industrial'],
                'default' => 'residential',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'surface_material' => [
                'type' => 'string',
                'enum' => ['brick', 'stone', 'glass', 'metal', 'concrete', 'composite'],
                'default' => 'brick',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'roof_type' => [
                'type' => 'string',
                'enum' => ['', 'flat', 'pitched', 'complex'],
                'default' => '',
                'sanitize_callback' => 'sanitize_text_field'
            ]
        ];
    }
    
    /**
     * Get submission arguments schema
     * 
     * @return array Arguments schema
     */
    private function getSubmissionArgs(): array {
        $calculation_args = $this->getCalculationArgs();
        
        $submission_args = [
            'customer_name' => [
                'required' => true,
                'type' => 'string',
                'minLength' => 2,
                'maxLength' => 255,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'customer_email' => [
                'required' => true,
                'type' => 'string',
                'format' => 'email',
                'sanitize_callback' => 'sanitize_email'
            ],
            'customer_phone' => [
                'required' => true,
                'type' => 'string',
                'minLength' => 10,
                'maxLength' => 20,
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'property_address' => [
                'required' => true,
                'type' => 'string',
                'minLength' => 10,
                'maxLength' => 500,
                'sanitize_callback' => 'sanitize_textarea_field'
            ],
            'postal_code' => [
                'required' => true,
                'type' => 'string',
                'pattern' => '^[1-9][0-9]{3}\s?[A-Z]{2}$',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'special_requirements' => [
                'type' => 'string',
                'maxLength' => 500,
                'default' => '',
                'sanitize_callback' => 'sanitize_textarea_field'
            ],
            'last_cleaning_date' => [
                'type' => 'string',
                'format' => 'date',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'privacy_consent' => [
                'required' => true,
                'type' => 'boolean'
            ],
            'marketing_consent' => [
                'type' => 'boolean',
                'default' => false
            ]
        ];
        
        return array_merge($calculation_args, $submission_args);
    }
    
    /**
     * Format quote for API response
     * 
     * @param Quote $quote Quote object
     * @return array Formatted quote data
     */
    private function formatQuoteForAPI(Quote $quote): array {
        return [
            'id' => $quote->getId(),
            'quote_number' => $quote->getQuoteNumber(),
            'customer' => [
                'name' => $quote->getCustomerName(),
                'email' => $quote->getCustomerEmail(),
                'phone' => $quote->getCustomerPhone()
            ],
            'property' => [
                'address' => $quote->getPropertyAddress(),
                'postal_code' => $quote->getPostalCode(),
                'type' => $quote->getPropertyType(),
                'square_meters' => $quote->getSquareMeters(),
                'linear_meters' => $quote->getLinearMeters(),
                'building_height' => $quote->getBuildingHeight(),
                'surface_material' => $quote->getSurfaceMaterial(),
                'roof_type' => $quote->getRoofType()
            ],
            'service' => [
                'type' => $quote->getServiceType(),
                'special_requirements' => $quote->getSpecialRequirements(),
                'last_cleaning_date' => $quote->getLastCleaningDate()
            ],
            'pricing' => [
                'base_price' => $quote->getBasePrice(),
                'adjustments' => $quote->getAdjustments(),
                'subtotal' => $quote->getSubtotal(),
                'tax_amount' => $quote->getTaxAmount(),
                'total_price' => $quote->getTotalPrice(),
                'breakdown' => $quote->getPriceBreakdown()
            ],
            'status' => $quote->getStatus(),
            'valid_until' => $quote->getValidUntil(),
            'consent' => [
                'privacy' => $quote->hasPrivacyConsent(),
                'marketing' => $quote->hasMarketingConsent()
            ],
            'created_at' => $quote->getCreatedAt(),
            'updated_at' => $quote->getUpdatedAt(),
            'is_expired' => $quote->isExpired(),
            'can_be_booked' => $quote->canBeBooked()
        ];
    }
}