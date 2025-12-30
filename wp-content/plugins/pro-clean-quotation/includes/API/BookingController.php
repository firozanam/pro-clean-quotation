<?php

namespace ProClean\Quotation\API;

use ProClean\Quotation\Models\Quote;
use ProClean\Quotation\Services\BookingManager;
use ProClean\Quotation\Admin\Settings;

/**
 * Booking REST API Controller
 * 
 * @package ProClean\Quotation\API
 * @since 1.0.0
 */
class BookingController {
    
    /**
     * Controller instance
     * 
     * @var BookingController
     */
    private static $instance = null;
    
    /**
     * API namespace
     */
    const NAMESPACE = 'pq/v1';
    
    /**
     * Get instance
     * 
     * @return BookingController
     */
    public static function getInstance(): BookingController {
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
        // Get available time slots
        register_rest_route(self::NAMESPACE, '/available-slots', [
            'methods' => 'GET',
            'callback' => [$this, 'getAvailableSlots'],
            'permission_callback' => [$this, 'publicPermissionCallback'],
            'args' => [
                'date' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'date',
                    'validate_callback' => [$this, 'validateDate']
                ],
                'service_duration' => [
                    'type' => 'integer',
                    'minimum' => 1,
                    'maximum' => 8,
                    'default' => 2
                ],
                'service_type' => [
                    'type' => 'string',
                    'enum' => ['facade', 'roof', 'both'],
                    'default' => 'facade'
                ]
            ]
        ]);
        
        // Create booking from quote
        register_rest_route(self::NAMESPACE, '/create-booking', [
            'methods' => 'POST',
            'callback' => [$this, 'createBooking'],
            'permission_callback' => [$this, 'publicPermissionCallback'],
            'args' => $this->getBookingArgs()
        ]);
        
        // Get booking details
        register_rest_route(self::NAMESPACE, '/booking/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getBooking'],
            'permission_callback' => [$this, 'bookingPermissionCallback'],
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
        
        // Cancel booking
        register_rest_route(self::NAMESPACE, '/booking/(?P<id>\d+)/cancel', [
            'methods' => 'POST',
            'callback' => [$this, 'cancelBooking'],
            'permission_callback' => [$this, 'bookingPermissionCallback'],
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
                ],
                'reason' => [
                    'type' => 'string',
                    'maxLength' => 500,
                    'sanitize_callback' => 'sanitize_textarea_field'
                ]
            ]
        ]);
        
        // Reschedule booking
        register_rest_route(self::NAMESPACE, '/booking/(?P<id>\d+)/reschedule', [
            'methods' => 'POST',
            'callback' => [$this, 'rescheduleBooking'],
            'permission_callback' => [$this, 'bookingPermissionCallback'],
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
                ],
                'new_date' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'date',
                    'validate_callback' => [$this, 'validateFutureDate']
                ],
                'new_time_start' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'time'
                ],
                'new_time_end' => [
                    'required' => true,
                    'type' => 'string',
                    'format' => 'time'
                ]
            ]
        ]);
    }
    
    /**
     * Get available time slots
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function getAvailableSlots(\WP_REST_Request $request): \WP_REST_Response {
        try {
            $date = $request->get_param('date');
            $service_duration = $request->get_param('service_duration');
            $service_type = $request->get_param('service_type');
            
            $booking_manager = BookingManager::getInstance();
            $slots = $booking_manager->getAvailableSlots($date, $service_duration, $service_type);
            
            return new \WP_REST_Response([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'available_slots' => $slots,
                    'service_duration' => $service_duration,
                    'business_hours' => $this->getBusinessHoursForDate($date)
                ]
            ], 200);
            
        } catch (\Exception $e) {
            error_log('PCQ API Get Available Slots Error: ' . $e->getMessage());
            
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('An error occurred while fetching available slots.', 'pro-clean-quotation')
            ], 500);
        }
    }
    
    /**
     * Create booking from quote
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function createBooking(\WP_REST_Request $request): \WP_REST_Response {
        try {
            $booking_manager = BookingManager::getInstance();
            $result = $booking_manager->createBookingFromQuote($request->get_params());
            
            if ($result['success']) {
                return new \WP_REST_Response($result, 201);
            } else {
                return new \WP_REST_Response($result, 400);
            }
            
        } catch (\Exception $e) {
            error_log('PCQ API Create Booking Error: ' . $e->getMessage());
            
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('An error occurred while creating the booking.', 'pro-clean-quotation')
            ], 500);
        }
    }
    
    /**
     * Get booking details
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function getBooking(\WP_REST_Request $request): \WP_REST_Response {
        $booking_id = $request->get_param('id');
        $token = $request->get_param('token');
        
        $booking_manager = BookingManager::getInstance();
        $booking = $booking_manager->getBookingById($booking_id);
        
        if (!$booking) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Booking not found.', 'pro-clean-quotation')
            ], 404);
        }
        
        if (!$this->verifyBookingToken($booking, $token)) {
            return new \WP_REST_Response([
                'success' => false,
                'message' => __('Invalid token.', 'pro-clean-quotation')
            ], 403);
        }
        
        return new \WP_REST_Response([
            'success' => true,
            'data' => $this->formatBookingForAPI($booking)
        ], 200);
    }
    
    /**
     * Cancel booking
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function cancelBooking(\WP_REST_Request $request): \WP_REST_Response {
        $booking_id = $request->get_param('id');
        $token = $request->get_param('token');
        $reason = $request->get_param('reason');
        
        $booking_manager = BookingManager::getInstance();
        $result = $booking_manager->cancelBooking($booking_id, $token, $reason);
        
        if ($result['success']) {
            return new \WP_REST_Response($result, 200);
        } else {
            $status_code = isset($result['code']) ? $result['code'] : 400;
            return new \WP_REST_Response($result, $status_code);
        }
    }
    
    /**
     * Reschedule booking
     * 
     * @param \WP_REST_Request $request Request object
     * @return \WP_REST_Response Response object
     */
    public function rescheduleBooking(\WP_REST_Request $request): \WP_REST_Response {
        $booking_id = $request->get_param('id');
        $token = $request->get_param('token');
        $new_date = $request->get_param('new_date');
        $new_time_start = $request->get_param('new_time_start');
        $new_time_end = $request->get_param('new_time_end');
        
        $booking_manager = BookingManager::getInstance();
        $result = $booking_manager->rescheduleBooking($booking_id, $token, [
            'date' => $new_date,
            'time_start' => $new_time_start,
            'time_end' => $new_time_end
        ]);
        
        if ($result['success']) {
            return new \WP_REST_Response($result, 200);
        } else {
            $status_code = isset($result['code']) ? $result['code'] : 400;
            return new \WP_REST_Response($result, $status_code);
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
     * Booking permission callback (requires valid token)
     * 
     * @param \WP_REST_Request $request Request object
     * @return bool Permission status
     */
    public function bookingPermissionCallback(\WP_REST_Request $request): bool {
        $booking_id = $request->get_param('id');
        $token = $request->get_param('token');
        
        if (!$booking_id || !$token) {
            return false;
        }
        
        $booking_manager = BookingManager::getInstance();
        $booking = $booking_manager->getBookingById($booking_id);
        
        return $booking && $this->verifyBookingToken($booking, $token);
    }
    
    /**
     * Validate date parameter
     * 
     * @param string $value Date value
     * @param \WP_REST_Request $request Request object
     * @param string $param Parameter name
     * @return bool|WP_Error Validation result
     */
    public function validateDate($value, $request, $param) {
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        
        if (!$date || $date->format('Y-m-d') !== $value) {
            return new \WP_Error('invalid_date', __('Invalid date format. Use YYYY-MM-DD.', 'pro-clean-quotation'));
        }
        
        // Check if date is not in the past
        $today = new \DateTime();
        if ($date < $today) {
            return new \WP_Error('past_date', __('Date cannot be in the past.', 'pro-clean-quotation'));
        }
        
        // Check if date is within booking window (e.g., max 90 days in advance)
        $max_advance = new \DateTime('+90 days');
        if ($date > $max_advance) {
            return new \WP_Error('too_far_future', __('Date is too far in the future.', 'pro-clean-quotation'));
        }
        
        return true;
    }
    
    /**
     * Validate future date parameter
     * 
     * @param string $value Date value
     * @param \WP_REST_Request $request Request object
     * @param string $param Parameter name
     * @return bool|WP_Error Validation result
     */
    public function validateFutureDate($value, $request, $param) {
        $validation = $this->validateDate($value, $request, $param);
        
        if (is_wp_error($validation)) {
            return $validation;
        }
        
        // Additional check for minimum lead time
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        $min_lead_time = Settings::get('min_lead_time_days', 1);
        $min_date = new \DateTime('+' . $min_lead_time . ' days');
        
        if ($date < $min_date) {
            return new \WP_Error('insufficient_lead_time', 
                sprintf(__('Booking must be at least %d days in advance.', 'pro-clean-quotation'), $min_lead_time)
            );
        }
        
        return true;
    }
    
    /**
     * Get booking arguments schema
     * 
     * @return array Arguments schema
     */
    private function getBookingArgs(): array {
        return [
            'quote_id' => [
                'required' => true,
                'type' => 'integer',
                'minimum' => 1
            ],
            'quote_token' => [
                'required' => true,
                'type' => 'string',
                'minLength' => 32
            ],
            'service_date' => [
                'required' => true,
                'type' => 'string',
                'format' => 'date',
                'validate_callback' => [$this, 'validateFutureDate']
            ],
            'service_time_start' => [
                'required' => true,
                'type' => 'string',
                'format' => 'time'
            ],
            'service_time_end' => [
                'required' => true,
                'type' => 'string',
                'format' => 'time'
            ],
            'customer_notes' => [
                'type' => 'string',
                'maxLength' => 500,
                'default' => '',
                'sanitize_callback' => 'sanitize_textarea_field'
            ],
            'payment_method' => [
                'type' => 'string',
                'enum' => ['cash', 'bank_transfer', 'card', 'online'],
                'default' => 'cash',
                'sanitize_callback' => 'sanitize_text_field'
            ],
            'deposit_payment' => [
                'type' => 'boolean',
                'default' => false,
                'description' => 'Only available if WooCommerce is active and online payments are enabled'
            ]
        ];
    }
    
    /**
     * Get business hours for specific date
     * 
     * @param string $date Date in Y-m-d format
     * @return array Business hours
     */
    private function getBusinessHoursForDate(string $date): array {
        $day_of_week = strtolower(date('l', strtotime($date)));
        $business_hours = Settings::getBusinessHours();
        
        return $business_hours[$day_of_week] ?? [
            'start' => '09:00',
            'end' => '17:00',
            'enabled' => false
        ];
    }
    
    /**
     * Verify booking token
     * 
     * @param array $booking Booking data
     * @param string $token Token to verify
     * @return bool Verification result
     */
    private function verifyBookingToken(array $booking, string $token): bool {
        $expected_token = md5($booking['id'] . $booking['booking_number'] . $booking['customer_email']);
        return hash_equals($expected_token, $token);
    }
    
    /**
     * Format booking for API response
     * 
     * @param array $booking Booking data
     * @return array Formatted booking data
     */
    private function formatBookingForAPI(array $booking): array {
        return [
            'id' => $booking['id'],
            'booking_number' => $booking['booking_number'],
            'quote_id' => $booking['quote_id'],
            'customer' => [
                'name' => $booking['customer_name'],
                'email' => $booking['customer_email'],
                'phone' => $booking['customer_phone']
            ],
            'service' => [
                'type' => $booking['service_type'],
                'date' => $booking['service_date'],
                'time_start' => $booking['service_time_start'],
                'time_end' => $booking['service_time_end'],
                'estimated_duration' => $booking['estimated_duration'],
                'details' => $booking['service_details']
            ],
            'property' => [
                'address' => $booking['property_address']
            ],
            'pricing' => [
                'total_amount' => $booking['total_amount'],
                'deposit_amount' => $booking['deposit_amount'],
                'deposit_paid' => (bool) $booking['deposit_paid'],
                'balance_due' => $booking['balance_due']
            ],
            'status' => [
                'booking' => $booking['booking_status'],
                'payment' => $booking['payment_status']
            ],
            'staff' => [
                'assigned_technician' => $booking['assigned_technician']
            ],
            'notes' => [
                'customer' => $booking['customer_notes'],
                'admin' => $booking['admin_notes']
            ],
            'cancellation' => [
                'reason' => $booking['cancellation_reason'],
                'cancelled_at' => $booking['cancelled_at']
            ],
            'timestamps' => [
                'created_at' => $booking['created_at'],
                'updated_at' => $booking['updated_at'],
                'completed_at' => $booking['completed_at']
            ],
            'reminder_sent' => (bool) $booking['reminder_sent']
        ];
    }
}