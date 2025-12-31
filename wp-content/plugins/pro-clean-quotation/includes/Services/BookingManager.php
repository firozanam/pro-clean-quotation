<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Models\Quote;
use ProClean\Quotation\Admin\Settings;
use ProClean\Quotation\Email\EmailManager;

/**
 * Booking Manager Service
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class BookingManager {
    
    /**
     * Booking manager instance
     * 
     * @var BookingManager
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return BookingManager
     */
    public static function getInstance(): BookingManager {
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
     * Get available time slots for a date
     * 
     * @param string $date Date in Y-m-d format
     * @param int $service_duration Duration in hours
     * @param string $service_type Service type
     * @return array Available time slots with availability status
     */
    public function getAvailableSlots(string $date, int $service_duration, string $service_type): array {
        // Get business hours for the date
        $day_of_week = strtolower(date('l', strtotime($date)));
        $business_hours = Settings::getBusinessHours();
        
        if (!isset($business_hours[$day_of_week]) || !$business_hours[$day_of_week]['enabled']) {
            return []; // No business hours for this day
        }
        
        $start_time = $business_hours[$day_of_week]['start'];
        $end_time = $business_hours[$day_of_week]['end'];
        
        // Generate time slots
        $slots = $this->generateTimeSlots($start_time, $end_time, $service_duration);
        
        // Use AvailabilityService to check each slot
        $availability_service = AvailabilityService::getInstance();
        $available_slots = [];
        
        foreach ($slots as $slot) {
            // Check comprehensive availability
            $check = $availability_service->checkSlotAvailability(
                $date,
                $slot['start'],
                $slot['end']
            );
            
            $available_slots[] = [
                'start_time' => $slot['start'],
                'end_time' => $slot['end'],
                'available' => $check['available'],
                'reason' => $check['available'] ? null : $check['message']
            ];
        }
        
        return $available_slots;
    }
    
    /**
     * Create booking from quote
     * 
     * @param array $data Booking data
     * @return array Result
     */
    public function createBookingFromQuote(array $data): array {
        try {
            // Validate quote
            $quote = new Quote($data['quote_id']);
            if (!$quote->getId()) {
                return [
                    'success' => false,
                    'message' => __('Quote not found.', 'pro-clean-quotation')
                ];
            }
            
            if (!$quote->verifyToken($data['quote_token'])) {
                return [
                    'success' => false,
                    'message' => __('Invalid quote token.', 'pro-clean-quotation')
                ];
            }
            
            if (!$quote->canBeBooked()) {
                $status = $quote->getStatus();
                $message = __('This quote cannot be booked.', 'pro-clean-quotation');
                
                if ($quote->isExpired()) {
                    $message = __('This quote has expired. Please request a new quote.', 'pro-clean-quotation');
                } elseif (in_array($status, ['cancelled', 'rejected'])) {
                    $message = sprintf(
                        __('This quote has been %s and cannot be used for booking.', 'pro-clean-quotation'),
                        $status
                    );
                }
                
                return [
                    'success' => false,
                    'message' => $message
                ];
            }
            
            // Check for duplicate booking (same quote, date, and time)
            $duplicate_check = $this->checkDuplicateBooking(
                $data['quote_id'],
                $data['service_date'],
                $data['service_time_start'],
                $data['service_time_end']
            );
            
            if ($duplicate_check['is_duplicate']) {
                return [
                    'success' => false,
                    'message' => __('You already have a booking for this date and time.', 'pro-clean-quotation'),
                    'duplicate_booking' => $duplicate_check['booking']
                ];
            }
            
            // Validate time slot availability
            $service_duration = $this->estimateServiceDuration($quote->getSquareMeters(), $quote->getServiceType());
            
            // Use AvailabilityService for comprehensive availability check
            $availability_service = AvailabilityService::getInstance();
            $availability_check = $availability_service->checkSlotAvailability(
                $data['service_date'],
                $data['service_time_start'],
                $data['service_time_end']
            );
            
            if (!$availability_check['available']) {
                return [
                    'success' => false,
                    'message' => $availability_check['message'],
                    'reason' => $availability_check['reason']
                ];
            }
            
            // Check if MotoPress Appointment is available (recommended but not required)
            $use_motopress = $this->isMotoPresssAvailable();
            $appointment_id = null;
            
            if ($use_motopress) {
                // Create MotoPress appointment
                $appointment_data = $this->prepareMotoPressData($quote, $data);
                $appointment_id = $this->createMotoPressAppointment($appointment_data);
                
                if (!$appointment_id) {
                    // Continue without MotoPress if it fails
                    error_log('PCQ: Failed to create MotoPress appointment, continuing with internal booking only');
                }
            }
            
            // Update quote status (only if first booking)
            if ($quote->getStatus() === 'new') {
                $quote->setStatus('booked');
                $quote->save();
            }
            
            // Create booking record in our system
            $booking_data = $this->createBookingRecord($quote, $data, $appointment_id);
            
            // Send confirmation emails
            $email_manager = EmailManager::getInstance();
            $email_manager->sendBookingConfirmation($booking_data);
            
            return [
                'success' => true,
                'message' => __('Booking created successfully!', 'pro-clean-quotation'),
                'data' => [
                    'booking_id' => $booking_data['id'],
                    'booking_number' => $booking_data['booking_number'],
                    'appointment_id' => $appointment_id,
                    'service_date' => $data['service_date'],
                    'service_time' => $data['service_time_start'] . ' - ' . $data['service_time_end'],
                    'total_amount' => $quote->getTotalPrice(),
                    'booking_token' => $this->generateBookingToken($booking_data)
                ]
            ];
            
        } catch (\Exception $e) {
            error_log('PCQ Booking creation error: ' . $e->getMessage());
            error_log('PCQ Booking trace: ' . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => __('An error occurred while creating the booking.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Get booking by ID
     * 
     * @param int $booking_id Booking ID
     * @return array|false Booking data or false
     */
    public function getBookingById(int $booking_id) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        $booking = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $booking_id),
            ARRAY_A
        );
        
        return $booking ?: false;
    }
    
    /**
     * Cancel booking
     * 
     * @param int $booking_id Booking ID
     * @param string $token Booking token
     * @param string $reason Cancellation reason
     * @return array Result
     */
    public function cancelBooking(int $booking_id, string $token, string $reason = ''): array {
        $booking = $this->getBookingById($booking_id);
        
        if (!$booking) {
            return [
                'success' => false,
                'message' => __('Booking not found.', 'pro-clean-quotation'),
                'code' => 404
            ];
        }
        
        if (!$this->verifyBookingToken($booking, $token)) {
            return [
                'success' => false,
                'message' => __('Invalid token.', 'pro-clean-quotation'),
                'code' => 403
            ];
        }
        
        if ($booking['booking_status'] === 'cancelled') {
            return [
                'success' => false,
                'message' => __('Booking is already cancelled.', 'pro-clean-quotation')
            ];
        }
        
        // Check cancellation policy (48 hours before)
        $service_datetime = $booking['service_date'] . ' ' . $booking['service_time_start'];
        $hours_until_service = (strtotime($service_datetime) - time()) / 3600;
        
        if ($hours_until_service < 48) {
            return [
                'success' => false,
                'message' => __('Bookings can only be cancelled up to 48 hours before the scheduled service.', 'pro-clean-quotation')
            ];
        }
        
        // Update booking status
        global $wpdb;
        $table = $wpdb->prefix . 'pq_bookings';
        
        $result = $wpdb->update(
            $table,
            [
                'booking_status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $booking_id]
        );
        
        if ($result !== false) {
            // Cancel MotoPress appointment if exists
            $this->cancelMotoPressAppointment($booking);
            
            return [
                'success' => true,
                'message' => __('Booking cancelled successfully.', 'pro-clean-quotation')
            ];
        } else {
            return [
                'success' => false,
                'message' => __('Failed to cancel booking.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Reschedule booking
     * 
     * @param int $booking_id Booking ID
     * @param string $token Booking token
     * @param array $new_schedule New schedule data
     * @return array Result
     */
    public function rescheduleBooking(int $booking_id, string $token, array $new_schedule): array {
        $booking = $this->getBookingById($booking_id);
        
        if (!$booking) {
            return [
                'success' => false,
                'message' => __('Booking not found.', 'pro-clean-quotation'),
                'code' => 404
            ];
        }
        
        if (!$this->verifyBookingToken($booking, $token)) {
            return [
                'success' => false,
                'message' => __('Invalid token.', 'pro-clean-quotation'),
                'code' => 403
            ];
        }
        
        if ($booking['booking_status'] !== 'pending' && $booking['booking_status'] !== 'confirmed') {
            return [
                'success' => false,
                'message' => __('This booking cannot be rescheduled.', 'pro-clean-quotation')
            ];
        }
        
        // Validate new time slot
        $availability_service = AvailabilityService::getInstance();
        $availability_check = $availability_service->checkSlotAvailability(
            $new_schedule['date'],
            $new_schedule['time_start'],
            $new_schedule['time_end'],
            $booking_id
        );
        
        if (!$availability_check['available']) {
            return [
                'success' => false,
                'message' => $availability_check['message'],
                'reason' => $availability_check['reason']
            ];
        }
        
        // Update booking
        global $wpdb;
        $table = $wpdb->prefix . 'pq_bookings';
        
        $result = $wpdb->update(
            $table,
            [
                'service_date' => $new_schedule['date'],
                'service_time_start' => $new_schedule['time_start'],
                'service_time_end' => $new_schedule['time_end'],
                'updated_at' => current_time('mysql')
            ],
            ['id' => $booking_id]
        );
        
        if ($result !== false) {
            // Update MotoPress appointment if exists
            $this->updateMotoPressAppointment($booking, $new_schedule);
            
            return [
                'success' => true,
                'message' => __('Booking rescheduled successfully.', 'pro-clean-quotation')
            ];
        } else {
            return [
                'success' => false,
                'message' => __('Failed to reschedule booking.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Get bookings for a specific date
     * 
     * @param string $date Date in Y-m-d format
     * @return array Existing bookings
     */
    private function getBookingsForDate(string $date): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        $bookings = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT service_time_start, service_time_end, estimated_duration 
                 FROM $table 
                 WHERE service_date = %s 
                 AND booking_status NOT IN ('cancelled', 'completed')",
                $date
            ),
            ARRAY_A
        );
        
        return $bookings ?: [];
    }
    
    /**
     * Check for duplicate booking
     * 
     * @param int $quote_id Quote ID
     * @param string $date Service date
     * @param string $time_start Start time
     * @param string $time_end End time
     * @return array Result with is_duplicate flag and booking data
     */
    private function checkDuplicateBooking(int $quote_id, string $date, string $time_start, string $time_end): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        // Check if exact same booking exists (same quote, date, and time)
        $booking = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table 
                 WHERE quote_id = %d 
                 AND service_date = %s 
                 AND service_time_start = %s 
                 AND service_time_end = %s 
                 AND booking_status NOT IN ('cancelled')
                 LIMIT 1",
                $quote_id,
                $date,
                $time_start,
                $time_end
            ),
            ARRAY_A
        );
        
        return [
            'is_duplicate' => !empty($booking),
            'booking' => $booking
        ];
    }
    
    /**
     * Generate time slots
     * 
     * @param string $start_time Start time (H:i format)
     * @param string $end_time End time (H:i format)
     * @param int $service_duration Service duration in hours
     * @return array Time slots
     */
    private function generateTimeSlots(string $start_time, string $end_time, int $service_duration): array {
        $slots = [];
        $current = strtotime($start_time);
        $end = strtotime($end_time);
        $duration_seconds = $service_duration * 3600;
        $buffer_time = Settings::get('booking_buffer_time', 60) * 60; // Convert minutes to seconds
        
        while ($current + $duration_seconds <= $end) {
            $slot_start = date('H:i', $current);
            $slot_end = date('H:i', $current + $duration_seconds);
            
            $slots[] = [
                'start' => $slot_start,
                'end' => $slot_end
            ];
            
            // Move to next slot (duration + buffer time)
            $current += $duration_seconds + $buffer_time;
        }
        
        return $slots;
    }
    
    /**
     * Check if a time slot is available
     * 
     * @param array $slot Time slot
     * @param array $existing_bookings Existing bookings
     * @param int $service_duration Service duration
     * @return bool Availability status
     */
    private function isSlotAvailable(array $slot, array $existing_bookings, int $service_duration): bool {
        $slot_start = strtotime($slot['start']);
        $slot_end = strtotime($slot['end']);
        
        foreach ($existing_bookings as $booking) {
            $booking_start = strtotime($booking['service_time_start']);
            $booking_end = strtotime($booking['service_time_end']);
            
            // Check for overlap
            if ($slot_start < $booking_end && $slot_end > $booking_start) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if a specific time slot is available
     * 
     * @param string $date Service date
     * @param string $time_start Start time
     * @param string $time_end End time
     * @param int $exclude_booking_id Booking ID to exclude from check
     * @return bool Availability status
     */
    private function isTimeSlotAvailable(string $date, string $time_start, string $time_end, int $exclude_booking_id = 0): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        $query = "SELECT COUNT(*) FROM $table 
                  WHERE service_date = %s 
                  AND booking_status NOT IN ('cancelled', 'completed')
                  AND (
                      (service_time_start < %s AND service_time_end > %s) OR
                      (service_time_start < %s AND service_time_end > %s) OR
                      (service_time_start >= %s AND service_time_end <= %s)
                  )";
        
        $params = [$date, $time_end, $time_start, $time_end, $time_start, $time_start, $time_end];
        
        if ($exclude_booking_id > 0) {
            $query .= " AND id != %d";
            $params[] = $exclude_booking_id;
        }
        
        $conflicts = $wpdb->get_var($wpdb->prepare($query, $params));
        
        return $conflicts == 0;
    }
    
    /**
     * Estimate service duration based on property size
     * 
     * @param float $square_meters Property size
     * @param string $service_type Service type
     * @return int Duration in hours
     */
    private function estimateServiceDuration(float $square_meters, string $service_type): int {
        // Basic duration estimation
        if ($square_meters < 100) {
            return 2; // Small jobs: 2 hours
        } elseif ($square_meters < 300) {
            return 4; // Medium jobs: 4 hours
        } else {
            return 8; // Large jobs: full day
        }
    }
    
    /**
     * Check if MotoPress Appointment is available
     * 
     * @return bool Availability status
     */
    private function isMotoPresssAvailable(): bool {
        return is_plugin_active('motopress-appointment-lite/motopress-appointment.php') && 
               Settings::get('motopress_integration_enabled', true);
    }
    
    /**
     * Check if WooCommerce is available for online payments
     * 
     * @return bool Availability status
     */
    private function isWooCommerceAvailable(): bool {
        return is_plugin_active('woocommerce/woocommerce.php') && 
               Settings::get('woocommerce_integration_enabled', false) &&
               Settings::get('enable_online_payments', false);
    }
    
    /**
     * Prepare data for MotoPress appointment
     * 
     * @param Quote $quote Quote object
     * @param array $booking_data Booking data
     * @return array MotoPress data
     */
    private function prepareMotoPressData(Quote $quote, array $booking_data): array {
        return [
            'service_id' => $this->getMotoPresssServiceId($quote->getServiceType()),
            'employee_id' => 0, // Auto-assign
            'customer_name' => $quote->getCustomerName(),
            'customer_email' => $quote->getCustomerEmail(),
            'customer_phone' => $quote->getCustomerPhone(),
            'date' => $booking_data['service_date'],
            'time' => $booking_data['service_time_start'],
            'duration' => $this->estimateServiceDuration($quote->getSquareMeters(), $quote->getServiceType()),
            'price' => $quote->getTotalPrice(),
            'notes' => $booking_data['customer_notes'] ?? '',
            'status' => 'confirmed'
        ];
    }
    
    /**
     * Get MotoPress service ID for service type
     * 
     * @param string $service_type Service type
     * @return int Service ID
     */
    private function getMotoPresssServiceId(string $service_type): int {
        // This would map to actual MotoPress service IDs
        // For now, return a default ID
        return 1;
    }
    
    /**
     * Create MotoPress appointment
     * 
     * @param array $appointment_data Appointment data
     * @return int|false Appointment ID or false on failure
     */
    private function createMotoPressAppointment(array $appointment_data) {
        // This would integrate with MotoPress Appointment API
        // For now, return a mock ID
        return rand(1000, 9999);
    }
    
    /**
     * Create booking record in our system
     * 
     * @param Quote $quote Quote object
     * @param array $booking_data Booking data
     * @param int $appointment_id MotoPress appointment ID
     * @return array Booking record
     */
    private function createBookingRecord(Quote $quote, array $booking_data, int $appointment_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        $booking_number = $this->generateBookingNumber();
        
        $deposit_amount = 0;
        $payment_methods = Settings::get('payment_methods', ['cash', 'bank_transfer']);
        $selected_payment_method = $booking_data['payment_method'] ?? 'cash';
        
        // Only calculate deposit if online payments are enabled and requested
        if ($booking_data['deposit_payment'] && $this->isWooCommerceAvailable()) {
            $deposit_amount = $quote->getTotalPrice() * (Settings::get('deposit_percentage', 20) / 100);
        }
        
        $record = [
            'booking_number' => $booking_number,
            'quote_id' => $quote->getId(),
            'customer_name' => $quote->getCustomerName(),
            'customer_email' => $quote->getCustomerEmail(),
            'customer_phone' => $quote->getCustomerPhone(),
            'property_address' => $quote->getPropertyAddress(),
            'service_type' => $quote->getServiceType(),
            'service_date' => $booking_data['service_date'],
            'service_time_start' => $booking_data['service_time_start'],
            'service_time_end' => $booking_data['service_time_end'],
            'estimated_duration' => $this->estimateServiceDuration($quote->getSquareMeters(), $quote->getServiceType()),
            'service_details' => json_encode([
                'square_meters' => $quote->getSquareMeters(),
                'linear_meters' => $quote->getLinearMeters(),
                'building_height' => $quote->getBuildingHeight(),
                'property_type' => $quote->getPropertyType(),
                'surface_material' => $quote->getSurfaceMaterial(),
                'appointment_id' => $appointment_id,
                'payment_method' => $selected_payment_method
            ]),
            'total_amount' => $quote->getTotalPrice(),
            'deposit_amount' => $deposit_amount,
            'deposit_paid' => 0,
            'balance_due' => $quote->getTotalPrice() - $deposit_amount,
            'payment_status' => $deposit_amount > 0 ? 'deposit_pending' : 'pending',
            'booking_status' => 'confirmed',
            'customer_notes' => $booking_data['customer_notes'] ?? '',
            'created_at' => current_time('mysql')
        ];
        
        $wpdb->insert($table, $record);
        $record['id'] = $wpdb->insert_id;
        
        return $record;
    }
    
    /**
     * Generate unique booking number
     * 
     * @return string Booking number
     */
    private function generateBookingNumber(): string {
        $prefix = 'PCB';
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $date . '-' . $random;
    }
    
    /**
     * Generate booking token
     * 
     * @param array $booking_data Booking data
     * @return string Booking token
     */
    private function generateBookingToken(array $booking_data): string {
        return md5($booking_data['id'] . $booking_data['booking_number'] . $booking_data['customer_email']);
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
     * Cancel MotoPress appointment
     * 
     * @param array $booking Booking data
     * @return bool Success status
     */
    private function cancelMotoPressAppointment(array $booking): bool {
        // This would integrate with MotoPress Appointment API
        // For now, return true
        return true;
    }
    
    /**
     * Update MotoPress appointment
     * 
     * @param array $booking Booking data
     * @param array $new_schedule New schedule
     * @return bool Success status
     */
    private function updateMotoPressAppointment(array $booking, array $new_schedule): bool {
        // This would integrate with MotoPress Appointment API
        // For now, return true
        return true;
    }
}