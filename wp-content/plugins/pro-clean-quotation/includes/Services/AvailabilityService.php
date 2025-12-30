<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Admin\Settings;

/**
 * Availability Service
 * Handles booking availability, conflicts, buffer times, and restrictions
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class AvailabilityService {
    
    /**
     * Service instance
     * 
     * @var AvailabilityService
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return AvailabilityService
     */
    public static function getInstance(): AvailabilityService {
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
     * Check if a time slot is available with all constraints
     * 
     * @param string $date Service date (Y-m-d)
     * @param string $time_start Start time (H:i)
     * @param string $time_end End time (H:i)
     * @param int $exclude_booking_id Booking ID to exclude from check
     * @return array Result with availability status and reason
     */
    public function checkSlotAvailability(string $date, string $time_start, string $time_end, int $exclude_booking_id = 0): array {
        // Check if date is in the past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            return [
                'available' => false,
                'reason' => 'date_past',
                'message' => __('Cannot book dates in the past.', 'pro-clean-quotation')
            ];
        }
        
        // Check if date is a holiday or blocked date
        if ($this->isBlockedDate($date)) {
            $blocked_date = $this->getBlockedDateInfo($date);
            return [
                'available' => false,
                'reason' => 'date_blocked',
                'message' => sprintf(
                    __('This date is blocked: %s', 'pro-clean-quotation'),
                    $blocked_date['reason'] ?? __('Not available', 'pro-clean-quotation')
                ),
                'blocked_info' => $blocked_date
            ];
        }
        
        // Check maximum bookings per day
        $daily_limit = Settings::get('max_bookings_per_day', 3);
        if ($daily_limit > 0) {
            $day_bookings = $this->getDayBookingCount($date, $exclude_booking_id);
            
            if ($day_bookings >= $daily_limit) {
                return [
                    'available' => false,
                    'reason' => 'daily_limit_reached',
                    'message' => sprintf(
                        __('Maximum bookings for this day (%d) has been reached.', 'pro-clean-quotation'),
                        $daily_limit
                    ),
                    'current_bookings' => $day_bookings,
                    'max_bookings' => $daily_limit
                ];
            }
        }
        
        // Check for booking conflicts
        $conflicts = $this->detectConflicts($date, $time_start, $time_end, $exclude_booking_id);
        
        if (!empty($conflicts)) {
            return [
                'available' => false,
                'reason' => 'time_conflict',
                'message' => __('This time slot conflicts with an existing booking.', 'pro-clean-quotation'),
                'conflicts' => $conflicts
            ];
        }
        
        // Check buffer time requirements
        if (!$this->checkBufferTime($date, $time_start, $time_end, $exclude_booking_id)) {
            $buffer_minutes = Settings::get('booking_buffer_time', 60);
            return [
                'available' => false,
                'reason' => 'insufficient_buffer',
                'message' => sprintf(
                    __('Insufficient buffer time. A minimum of %d minutes is required between bookings.', 'pro-clean-quotation'),
                    $buffer_minutes
                ),
                'buffer_required' => $buffer_minutes
            ];
        }
        
        // All checks passed
        return [
            'available' => true,
            'reason' => 'slot_available',
            'message' => __('Time slot is available.', 'pro-clean-quotation')
        ];
    }
    
    /**
     * Detect booking conflicts for a time slot
     * 
     * @param string $date Service date
     * @param string $time_start Start time
     * @param string $time_end End time
     * @param int $exclude_booking_id Booking ID to exclude
     * @return array Array of conflicting bookings
     */
    public function detectConflicts(string $date, string $time_start, string $time_end, int $exclude_booking_id = 0): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        // Find overlapping bookings
        $query = "SELECT id, booking_number, service_time_start, service_time_end, customer_name 
                  FROM {$table}
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
        
        $conflicts = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
        
        return $conflicts ?: [];
    }
    
    /**
     * Check buffer time requirements
     * 
     * @param string $date Service date
     * @param string $time_start Start time
     * @param string $time_end End time
     * @param int $exclude_booking_id Booking ID to exclude
     * @return bool Buffer time is sufficient
     */
    public function checkBufferTime(string $date, string $time_start, string $time_end, int $exclude_booking_id = 0): bool {
        global $wpdb;
        
        $buffer_minutes = Settings::get('booking_buffer_time', 60);
        $buffer_seconds = $buffer_minutes * 60;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        // Get all active bookings for the date
        $query = "SELECT service_time_start, service_time_end 
                  FROM {$table}
                  WHERE service_date = %s 
                  AND booking_status NOT IN ('cancelled', 'completed')";
        
        $params = [$date];
        
        if ($exclude_booking_id > 0) {
            $query .= " AND id != %d";
            $params[] = $exclude_booking_id;
        }
        
        $bookings = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
        
        if (empty($bookings)) {
            return true; // No other bookings, buffer time OK
        }
        
        $new_start = strtotime($time_start);
        $new_end = strtotime($time_end);
        
        foreach ($bookings as $booking) {
            $booking_start = strtotime($booking['service_time_start']);
            $booking_end = strtotime($booking['service_time_end']);
            
            // Check buffer before new booking
            if ($new_start < $booking_start && ($booking_start - $new_end) < $buffer_seconds) {
                return false;
            }
            
            // Check buffer after new booking
            if ($new_start > $booking_end && ($new_start - $booking_end) < $buffer_seconds) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get booking count for a specific day
     * 
     * @param string $date Date (Y-m-d)
     * @param int $exclude_booking_id Booking ID to exclude
     * @return int Number of bookings
     */
    public function getDayBookingCount(string $date, int $exclude_booking_id = 0): int {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        $query = "SELECT COUNT(*) FROM {$table} 
                  WHERE service_date = %s 
                  AND booking_status NOT IN ('cancelled', 'completed')";
        
        $params = [$date];
        
        if ($exclude_booking_id > 0) {
            $query .= " AND id != %d";
            $params[] = $exclude_booking_id;
        }
        
        return (int) $wpdb->get_var($wpdb->prepare($query, $params));
    }
    
    /**
     * Check if a date is blocked (holiday or blackout date)
     * 
     * @param string $date Date (Y-m-d)
     * @return bool Date is blocked
     */
    public function isBlockedDate(string $date): bool {
        $blocked_dates = $this->getBlockedDates();
        
        foreach ($blocked_dates as $blocked) {
            // Single date
            if (isset($blocked['date']) && $blocked['date'] === $date) {
                return true;
            }
            
            // Date range
            if (isset($blocked['start_date']) && isset($blocked['end_date'])) {
                if ($date >= $blocked['start_date'] && $date <= $blocked['end_date']) {
                    return true;
                }
            }
            
            // Recurring (e.g., every Sunday, or 25th December each year)
            if (isset($blocked['recurring'])) {
                if ($this->matchesRecurringPattern($date, $blocked['recurring'])) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Get blocked date information
     * 
     * @param string $date Date (Y-m-d)
     * @return array|null Blocked date info
     */
    public function getBlockedDateInfo(string $date): ?array {
        $blocked_dates = $this->getBlockedDates();
        
        foreach ($blocked_dates as $blocked) {
            // Single date
            if (isset($blocked['date']) && $blocked['date'] === $date) {
                return $blocked;
            }
            
            // Date range
            if (isset($blocked['start_date']) && isset($blocked['end_date'])) {
                if ($date >= $blocked['start_date'] && $date <= $blocked['end_date']) {
                    return $blocked;
                }
            }
            
            // Recurring
            if (isset($blocked['recurring']) && $this->matchesRecurringPattern($date, $blocked['recurring'])) {
                return $blocked;
            }
        }
        
        return null;
    }
    
    /**
     * Get all blocked dates
     * 
     * @return array Blocked dates
     */
    public function getBlockedDates(): array {
        $blocked_dates = Settings::get('blocked_dates', []);
        
        // Always include default holidays if not already defined
        if (empty($blocked_dates)) {
            $blocked_dates = $this->getDefaultHolidays();
        }
        
        return $blocked_dates;
    }
    
    /**
     * Add a blocked date
     * 
     * @param array $blocked_date Blocked date data
     * @return bool Success status
     */
    public function addBlockedDate(array $blocked_date): bool {
        $blocked_dates = $this->getBlockedDates();
        
        // Validate blocked date structure
        if (!isset($blocked_date['type'])) {
            return false;
        }
        
        $blocked_date['id'] = uniqid('blocked_');
        $blocked_date['created_at'] = current_time('mysql');
        
        $blocked_dates[] = $blocked_date;
        
        return Settings::update('blocked_dates', $blocked_dates);
    }
    
    /**
     * Remove a blocked date
     * 
     * @param string $blocked_id Blocked date ID
     * @return bool Success status
     */
    public function removeBlockedDate(string $blocked_id): bool {
        $blocked_dates = $this->getBlockedDates();
        
        $blocked_dates = array_filter($blocked_dates, function($item) use ($blocked_id) {
            return $item['id'] !== $blocked_id;
        });
        
        return Settings::update('blocked_dates', array_values($blocked_dates));
    }
    
    /**
     * Check if date matches a recurring pattern
     * 
     * @param string $date Date (Y-m-d)
     * @param array $pattern Recurring pattern
     * @return bool Matches pattern
     */
    private function matchesRecurringPattern(string $date, array $pattern): bool {
        $timestamp = strtotime($date);
        
        // Day of week (0 = Sunday, 6 = Saturday)
        if (isset($pattern['day_of_week'])) {
            if (date('w', $timestamp) == $pattern['day_of_week']) {
                return true;
            }
        }
        
        // Specific day of month (e.g., 25th of every month)
        if (isset($pattern['day_of_month'])) {
            if (date('j', $timestamp) == $pattern['day_of_month']) {
                return true;
            }
        }
        
        // Specific date each year (e.g., 25th December)
        if (isset($pattern['month_day'])) {
            if (date('m-d', $timestamp) == $pattern['month_day']) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get default holidays (Netherlands)
     * 
     * @return array Default holidays
     */
    private function getDefaultHolidays(): array {
        $current_year = date('Y');
        
        return [
            [
                'id' => 'default_newyear',
                'type' => 'holiday',
                'reason' => __('New Year\'s Day', 'pro-clean-quotation'),
                'recurring' => ['month_day' => '01-01']
            ],
            [
                'id' => 'default_kingsday',
                'type' => 'holiday',
                'reason' => __('King\'s Day', 'pro-clean-quotation'),
                'recurring' => ['month_day' => '04-27']
            ],
            [
                'id' => 'default_christmas',
                'type' => 'holiday',
                'reason' => __('Christmas Day', 'pro-clean-quotation'),
                'recurring' => ['month_day' => '12-25']
            ],
            [
                'id' => 'default_boxing',
                'type' => 'holiday',
                'reason' => __('Boxing Day', 'pro-clean-quotation'),
                'recurring' => ['month_day' => '12-26']
            ],
            [
                'id' => 'default_sundays',
                'type' => 'recurring',
                'reason' => __('Sundays - No service', 'pro-clean-quotation'),
                'recurring' => ['day_of_week' => 0]
            ]
        ];
    }
    
    /**
     * Get availability statistics for a date range
     * 
     * @param string $start_date Start date (Y-m-d)
     * @param string $end_date End date (Y-m-d)
     * @return array Availability statistics
     */
    public function getAvailabilityStats(string $start_date, string $end_date): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        $max_per_day = Settings::get('max_bookings_per_day', 3);
        
        // Get booking counts per day
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT service_date, COUNT(*) as booking_count
             FROM {$table}
             WHERE service_date BETWEEN %s AND %s
             AND booking_status NOT IN ('cancelled', 'completed')
             GROUP BY service_date",
            $start_date,
            $end_date
        ), ARRAY_A);
        
        $stats = [];
        $current = strtotime($start_date);
        $end = strtotime($end_date);
        
        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $booking_count = 0;
            
            // Find booking count for this date
            foreach ($results as $result) {
                if ($result['service_date'] === $date) {
                    $booking_count = (int) $result['booking_count'];
                    break;
                }
            }
            
            $is_blocked = $this->isBlockedDate($date);
            $available_slots = $is_blocked ? 0 : max(0, $max_per_day - $booking_count);
            
            $stats[$date] = [
                'date' => $date,
                'day_of_week' => date('l', $current),
                'is_blocked' => $is_blocked,
                'blocked_reason' => $is_blocked ? $this->getBlockedDateInfo($date)['reason'] ?? '' : '',
                'bookings' => $booking_count,
                'max_bookings' => $max_per_day,
                'available_slots' => $available_slots,
                'utilization' => $max_per_day > 0 ? round(($booking_count / $max_per_day) * 100, 1) : 0,
                'status' => $this->getDateStatus($is_blocked, $booking_count, $max_per_day)
            ];
            
            $current = strtotime('+1 day', $current);
        }
        
        return $stats;
    }
    
    /**
     * Get status for a date
     * 
     * @param bool $is_blocked Date is blocked
     * @param int $booking_count Current bookings
     * @param int $max_bookings Maximum bookings
     * @return string Status
     */
    private function getDateStatus(bool $is_blocked, int $booking_count, int $max_bookings): string {
        if ($is_blocked) {
            return 'blocked';
        }
        
        if ($booking_count >= $max_bookings) {
            return 'full';
        }
        
        if ($booking_count > 0) {
            return 'partial';
        }
        
        return 'available';
    }
    
    /**
     * Get next available slot
     * 
     * @param int $service_duration Duration in hours
     * @param int $days_ahead Number of days to check ahead (default: 30)
     * @return array|null Next available slot or null
     */
    public function getNextAvailableSlot(int $service_duration = 4, int $days_ahead = 30): ?array {
        $current_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$days_ahead} days"));
        
        $business_hours = Settings::getBusinessHours();
        
        $current = strtotime($current_date);
        $end = strtotime($end_date);
        
        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $day_of_week = strtolower(date('l', $current));
            
            // Skip if not a business day
            if (!isset($business_hours[$day_of_week]) || !$business_hours[$day_of_week]['enabled']) {
                $current = strtotime('+1 day', $current);
                continue;
            }
            
            // Skip if blocked
            if ($this->isBlockedDate($date)) {
                $current = strtotime('+1 day', $current);
                continue;
            }
            
            // Check daily limit
            $max_per_day = Settings::get('max_bookings_per_day', 3);
            if ($this->getDayBookingCount($date) >= $max_per_day) {
                $current = strtotime('+1 day', $current);
                continue;
            }
            
            // Try to find an available time slot
            $start_time = $business_hours[$day_of_week]['start'];
            $end_time = $business_hours[$day_of_week]['end'];
            
            $slots = $this->generatePossibleSlots($start_time, $end_time, $service_duration);
            
            foreach ($slots as $slot) {
                $check = $this->checkSlotAvailability($date, $slot['start'], $slot['end']);
                
                if ($check['available']) {
                    return [
                        'date' => $date,
                        'day_of_week' => $day_of_week,
                        'time_start' => $slot['start'],
                        'time_end' => $slot['end'],
                        'duration' => $service_duration
                    ];
                }
            }
            
            $current = strtotime('+1 day', $current);
        }
        
        return null; // No available slot found
    }
    
    /**
     * Generate possible time slots for a day
     * 
     * @param string $start_time Start time (H:i)
     * @param string $end_time End time (H:i)
     * @param int $duration Duration in hours
     * @return array Time slots
     */
    private function generatePossibleSlots(string $start_time, string $end_time, int $duration): array {
        $slots = [];
        $current = strtotime($start_time);
        $end = strtotime($end_time);
        $duration_seconds = $duration * 3600;
        $buffer_minutes = Settings::get('booking_buffer_time', 60);
        $buffer_seconds = $buffer_minutes * 60;
        
        while ($current + $duration_seconds <= $end) {
            $slots[] = [
                'start' => date('H:i', $current),
                'end' => date('H:i', $current + $duration_seconds)
            ];
            
            // Move to next slot (duration + buffer)
            $current += $duration_seconds + $buffer_seconds;
        }
        
        return $slots;
    }
}
