<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Email\EmailManager;
use ProClean\Quotation\Admin\Settings;

/**
 * Reminder Manager Service
 * Handles automated reminder emails for upcoming bookings
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class ReminderManager {
    
    /**
     * Reminder manager instance
     * 
     * @var ReminderManager
     */
    private static $instance = null;
    
    /**
     * Cron hook name
     * 
     * @var string
     */
    const CRON_HOOK = 'pcq_send_booking_reminders';
    
    /**
     * Get instance
     * 
     * @return ReminderManager
     */
    public static function getInstance(): ReminderManager {
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
     * Initialize WordPress hooks
     */
    private function initHooks(): void {
        // Register cron hook
        add_action(self::CRON_HOOK, [$this, 'sendBookingReminders']);
        
        // Schedule cron on plugin activation if not already scheduled
        if (!wp_next_scheduled(self::CRON_HOOK)) {
            wp_schedule_event(time(), 'hourly', self::CRON_HOOK);
        }
        
        // Add custom cron interval for testing (every 5 minutes)
        add_filter('cron_schedules', [$this, 'addCustomCronIntervals']);
    }
    
    /**
     * Add custom cron intervals
     * 
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public function addCustomCronIntervals(array $schedules): array {
        // Add 5-minute interval for testing
        $schedules['every_five_minutes'] = [
            'interval' => 5 * 60,
            'display' => __('Every 5 Minutes', 'pro-clean-quotation')
        ];
        
        // Add 15-minute interval
        $schedules['every_fifteen_minutes'] = [
            'interval' => 15 * 60,
            'display' => __('Every 15 Minutes', 'pro-clean-quotation')
        ];
        
        return $schedules;
    }
    
    /**
     * Send booking reminders (main cron job function)
     * 
     * @return void
     */
    public function sendBookingReminders(): void {
        // Check if reminders are enabled
        if (!Settings::get('reminder_enabled', true)) {
            error_log('PCQ: Booking reminders are disabled');
            return;
        }
        
        // Get reminder hours before booking (default: 24 hours)
        $reminder_hours = Settings::get('reminder_hours_before', 24);
        
        // Get bookings that need reminders
        $bookings = $this->getBookingsNeedingReminders($reminder_hours);
        
        if (empty($bookings)) {
            error_log('PCQ: No bookings need reminders at this time');
            return;
        }
        
        error_log(sprintf('PCQ: Found %d booking(s) that need reminders', count($bookings)));
        
        $email_manager = EmailManager::getInstance();
        $sent_count = 0;
        $failed_count = 0;
        
        foreach ($bookings as $booking) {
            try {
                // Send reminder email
                $sent = $email_manager->sendReminder($booking);
                
                if ($sent) {
                    // Mark reminder as sent
                    $this->markReminderSent($booking['id']);
                    $sent_count++;
                    
                    error_log(sprintf('PCQ: Reminder sent for booking #%s', $booking['booking_number']));
                } else {
                    $failed_count++;
                    error_log(sprintf('PCQ: Failed to send reminder for booking #%s', $booking['booking_number']));
                }
                
            } catch (\Exception $e) {
                $failed_count++;
                error_log(sprintf('PCQ: Error sending reminder for booking #%s: %s', $booking['booking_number'], $e->getMessage()));
            }
        }
        
        error_log(sprintf('PCQ: Reminder batch complete - Sent: %d, Failed: %d', $sent_count, $failed_count));
    }
    
    /**
     * Get bookings that need reminders
     * 
     * @param int $hours_before Hours before service
     * @return array Bookings array
     */
    private function getBookingsNeedingReminders(int $hours_before = 24): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        // Calculate the target datetime range
        // We want bookings that are between X and X+1 hours in the future
        $target_time_start = date('Y-m-d H:i:s', strtotime("+{$hours_before} hours"));
        $target_time_end = date('Y-m-d H:i:s', strtotime("+{$hours_before} hours +1 hour"));
        
        $query = "
            SELECT * FROM $table 
            WHERE reminder_sent = 0
            AND booking_status IN ('pending', 'confirmed')
            AND CONCAT(service_date, ' ', service_time_start) BETWEEN %s AND %s
            ORDER BY service_date ASC, service_time_start ASC
        ";
        
        $bookings = $wpdb->get_results(
            $wpdb->prepare($query, $target_time_start, $target_time_end),
            ARRAY_A
        );
        
        return $bookings ?: [];
    }
    
    /**
     * Mark reminder as sent for a booking
     * 
     * @param int $booking_id Booking ID
     * @return bool Success status
     */
    private function markReminderSent(int $booking_id): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        $result = $wpdb->update(
            $table,
            [
                'reminder_sent' => 1,
                'reminder_sent_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ],
            ['id' => $booking_id],
            ['%d', '%s', '%s'],
            ['%d']
        );
        
        return $result !== false;
    }
    
    /**
     * Send immediate reminder for a specific booking (manual trigger)
     * 
     * @param int $booking_id Booking ID
     * @return array Result
     */
    public function sendImmediateReminder(int $booking_id): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        $booking = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $booking_id),
            ARRAY_A
        );
        
        if (!$booking) {
            return [
                'success' => false,
                'message' => __('Booking not found.', 'pro-clean-quotation')
            ];
        }
        
        if ($booking['booking_status'] === 'cancelled') {
            return [
                'success' => false,
                'message' => __('Cannot send reminder for cancelled booking.', 'pro-clean-quotation')
            ];
        }
        
        try {
            $email_manager = EmailManager::getInstance();
            $sent = $email_manager->sendReminder($booking);
            
            if ($sent) {
                $this->markReminderSent($booking_id);
                
                return [
                    'success' => true,
                    'message' => __('Reminder sent successfully.', 'pro-clean-quotation')
                ];
            } else {
                return [
                    'success' => false,
                    'message' => __('Failed to send reminder email.', 'pro-clean-quotation')
                ];
            }
            
        } catch (\Exception $e) {
            error_log('PCQ: Error sending immediate reminder: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => __('An error occurred while sending reminder.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Get reminder statistics
     * 
     * @return array Statistics data
     */
    public function getReminderStatistics(): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        // Total bookings with reminders sent
        $total_sent = $wpdb->get_var("
            SELECT COUNT(*) FROM $table 
            WHERE reminder_sent = 1
        ");
        
        // Bookings pending reminders (within next 48 hours)
        $pending_reminders = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table 
            WHERE reminder_sent = 0
            AND booking_status IN ('pending', 'confirmed')
            AND CONCAT(service_date, ' ', service_time_start) BETWEEN %s AND %s
        ", current_time('mysql'), date('Y-m-d H:i:s', strtotime('+48 hours'))));
        
        // Upcoming bookings (next 7 days)
        $upcoming_bookings = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*) FROM $table 
            WHERE booking_status IN ('pending', 'confirmed')
            AND service_date BETWEEN %s AND %s
        ", date('Y-m-d'), date('Y-m-d', strtotime('+7 days'))));
        
        // Next scheduled cron run
        $next_run = wp_next_scheduled(self::CRON_HOOK);
        
        return [
            'total_reminders_sent' => (int) $total_sent,
            'pending_reminders' => (int) $pending_reminders,
            'upcoming_bookings' => (int) $upcoming_bookings,
            'next_cron_run' => $next_run ? date('Y-m-d H:i:s', $next_run) : null,
            'next_cron_run_relative' => $next_run ? human_time_diff($next_run) : null,
            'cron_schedule' => $this->getCronSchedule(),
            'reminders_enabled' => Settings::get('reminder_enabled', true),
            'reminder_hours_before' => Settings::get('reminder_hours_before', 24)
        ];
    }
    
    /**
     * Get current cron schedule
     * 
     * @return string Schedule name
     */
    private function getCronSchedule(): string {
        $crons = _get_cron_array();
        
        foreach ($crons as $timestamp => $cron) {
            foreach ($cron as $hook => $details) {
                if ($hook === self::CRON_HOOK) {
                    foreach ($details as $detail) {
                        return $detail['schedule'] ?? 'unknown';
                    }
                }
            }
        }
        
        return 'not_scheduled';
    }
    
    /**
     * Reschedule cron job with new interval
     * 
     * @param string $schedule Schedule name (hourly, twicedaily, daily, etc.)
     * @return bool Success status
     */
    public function rescheduleCron(string $schedule = 'hourly'): bool {
        // Clear existing schedule
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
        
        // Schedule with new interval
        return wp_schedule_event(time(), $schedule, self::CRON_HOOK);
    }
    
    /**
     * Manually trigger cron job (for testing)
     * 
     * @return array Result with statistics
     */
    public function triggerManualRun(): array {
        $start_time = microtime(true);
        
        ob_start();
        $this->sendBookingReminders();
        $output = ob_get_clean();
        
        $execution_time = microtime(true) - $start_time;
        
        return [
            'success' => true,
            'message' => __('Manual reminder run completed.', 'pro-clean-quotation'),
            'execution_time' => round($execution_time, 2) . 's',
            'output' => $output,
            'statistics' => $this->getReminderStatistics()
        ];
    }
    
    /**
     * Clear all scheduled cron events (cleanup on deactivation)
     * 
     * @return void
     */
    public static function clearScheduledEvents(): void {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }
    
    /**
     * Get bookings that missed reminders (for recovery)
     * 
     * @return array Missed bookings
     */
    public function getMissedReminders(): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        $reminder_hours = Settings::get('reminder_hours_before', 24);
        
        // Get bookings where service is in the future but reminder wasn't sent
        // and we're past the reminder time
        $cutoff_time = date('Y-m-d H:i:s', strtotime("+{$reminder_hours} hours"));
        
        $query = "
            SELECT * FROM $table 
            WHERE reminder_sent = 0
            AND booking_status IN ('pending', 'confirmed')
            AND CONCAT(service_date, ' ', service_time_start) > NOW()
            AND CONCAT(service_date, ' ', service_time_start) < %s
            ORDER BY service_date ASC, service_time_start ASC
        ";
        
        $bookings = $wpdb->get_results(
            $wpdb->prepare($query, $cutoff_time),
            ARRAY_A
        );
        
        return $bookings ?: [];
    }
}
