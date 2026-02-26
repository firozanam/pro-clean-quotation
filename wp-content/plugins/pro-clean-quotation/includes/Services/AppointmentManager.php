<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Models\Appointment;
use ProClean\Quotation\Models\Service;
use ProClean\Quotation\Models\Employee;
use ProClean\Quotation\Models\Quote;
use ProClean\Quotation\Admin\Settings;
use ProClean\Quotation\Email\EmailManager;

/**
 * Appointment Manager Service
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class AppointmentManager {
    
    /**
     * Appointment manager instance
     * 
     * @var AppointmentManager
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return AppointmentManager
     */
    public static function getInstance(): AppointmentManager {
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
     * Create appointment from quote
     * 
     * @param Quote $quote Quote object
     * @param array $appointment_data Appointment data
     * @return array Result
     */
    public function createAppointmentFromQuote(Quote $quote, array $appointment_data): array {
        try {
            // Validate appointment data
            $validation = $this->validateAppointmentData($appointment_data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'errors' => $validation['errors']
                ];
            }
            
            // Get or create service
            $service = $this->getOrCreateServiceFromQuote($quote);
            if (!$service) {
                return [
                    'success' => false,
                    'message' => __('Failed to create service for appointment.', 'pro-clean-quotation')
                ];
            }
            
            // Find available employee
            $employee = $this->findAvailableEmployee(
                $service->getId(),
                $appointment_data['service_date'],
                $appointment_data['service_time_start'],
                $appointment_data['service_time_end']
            );
            
            // Create appointment
            $appointment = Appointment::create([
                'service_id' => $service->getId(),
                'employee_id' => $employee ? $employee->getId() : null,
                'quote_id' => $quote->getId(),
                'customer_name' => $quote->getCustomerName(),
                'customer_email' => $quote->getCustomerEmail(),
                'customer_phone' => $quote->getCustomerPhone(),
                'service_date' => $appointment_data['service_date'],
                'service_time_start' => $appointment_data['service_time_start'],
                'service_time_end' => $appointment_data['service_time_end'],
                'duration' => $this->calculateDuration($appointment_data['service_time_start'], $appointment_data['service_time_end']),
                'price' => $quote->getTotalPrice(),
                'status' => 'confirmed',
                'notes' => $appointment_data['notes'] ?? ''
            ]);
            
            if (!$appointment) {
                return [
                    'success' => false,
                    'message' => __('Failed to create appointment.', 'pro-clean-quotation')
                ];
            }
            
            // Update quote status
            $quote->setStatus('booked');
            $quote->save();
            
            // Send confirmation emails
            $email_manager = EmailManager::getInstance();
            $email_manager->sendAppointmentConfirmation($appointment);
            
            return [
                'success' => true,
                'message' => __('Appointment created successfully!', 'pro-clean-quotation'),
                'data' => [
                    'appointment_id' => $appointment->getId(),
                    'service_date' => $appointment->getServiceDate(),
                    'service_time' => $appointment->getServiceTimeStart() . ' - ' . $appointment->getServiceTimeEnd(),
                    'employee' => $employee ? $employee->getName() : __('Auto-assigned', 'pro-clean-quotation'),
                    'status' => $appointment->getStatus()
                ]
            ];
            
        } catch (\Exception $e) {
            error_log('PCQ Appointment creation error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => __('An error occurred while creating the appointment.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Get available time slots for a service
     * 
     * @param int $service_id Service ID
     * @param string $date Date in Y-m-d format
     * @param int $employee_id Employee ID (optional)
     * @return array Available time slots
     */
    public function getAvailableTimeSlots(int $service_id, string $date, int $employee_id = null): array {
        $service = new Service($service_id);
        if (!$service->getId()) {
            return [];
        }
        
        // Get business hours for the date
        $day_of_week = strtolower(date('l', strtotime($date)));
        $business_hours = Settings::getBusinessHours();
        
        if (!isset($business_hours[$day_of_week]) || !$business_hours[$day_of_week]['enabled']) {
            return [];
        }
        
        $work_start = $business_hours[$day_of_week]['start'];
        $work_end = $business_hours[$day_of_week]['end'];
        
        // Get employees for this service
        $employees = $employee_id ? [new Employee($employee_id)] : Employee::getForService($service_id);
        if (empty($employees)) {
            $employees = Employee::getAll(); // Fallback to all employees
        }
        
        // Generate time slots
        $duration = $service->getDuration() ?? 60; // Default to60 minutes if not set
        $buffer_before = $service->getBufferTimeBefore();
        $buffer_after = $service->getBufferTimeAfter();
        
        $slots = [];
        $current = strtotime($work_start);
        $end = strtotime($work_end);
        
        while ($current + ($duration * 60) <= $end) {
            $slot_start = date('H:i', $current);
            $slot_end = date('H:i', $current + ($duration * 60));
            
            // Check if any employee is available for this slot
            $available_employees = [];
            foreach ($employees as $employee) {
                if ($employee->isAvailable($date, $slot_start, $slot_end)) {
                    $available_employees[] = $employee;
                }
            }
            
            if (!empty($available_employees)) {
                $slots[] = [
                    'start' => $slot_start,
                    'end' => $slot_end,
                    'available_employees' => count($available_employees),
                    'employees' => array_map(function($emp) {
                        return [
                            'id' => $emp->getId(),
                            'name' => $emp->getName()
                        ];
                    }, $available_employees)
                ];
            }
            
            // Move to next slot
            $current += ($duration + $buffer_before + $buffer_after) * 60;
        }
        
        return $slots;
    }
    
    /**
     * Reschedule appointment
     * 
     * @param int $appointment_id Appointment ID
     * @param array $new_schedule New schedule data
     * @return array Result
     */
    public function rescheduleAppointment(int $appointment_id, array $new_schedule): array {
        $appointment = new Appointment($appointment_id);
        
        if (!$appointment->getId()) {
            return [
                'success' => false,
                'message' => __('Appointment not found.', 'pro-clean-quotation')
            ];
        }
        
        if (!$appointment->canBeRescheduled()) {
            return [
                'success' => false,
                'message' => __('This appointment cannot be rescheduled.', 'pro-clean-quotation')
            ];
        }
        
        // Validate new schedule
        $validation = $this->validateRescheduleData($new_schedule);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => $validation['message'],
                'errors' => $validation['errors']
            ];
        }
        
        // Check availability for new time
        $employee = $appointment->getEmployee();
        if ($employee && !$employee->isAvailable(
            $new_schedule['service_date'],
            $new_schedule['service_time_start'],
            $new_schedule['service_time_end']
        )) {
            // Try to find another available employee
            $service = $appointment->getService();
            $new_employee = $this->findAvailableEmployee(
                $service->getId(),
                $new_schedule['service_date'],
                $new_schedule['service_time_start'],
                $new_schedule['service_time_end']
            );
            
            if (!$new_employee) {
                return [
                    'success' => false,
                    'message' => __('No employees available for the selected time slot.', 'pro-clean-quotation')
                ];
            }
            
            $appointment->setEmployeeId($new_employee->getId());
        }
        
        // Update appointment
        $appointment->data['service_date'] = $new_schedule['service_date'];
        $appointment->data['service_time_start'] = $new_schedule['service_time_start'];
        $appointment->data['service_time_end'] = $new_schedule['service_time_end'];
        $appointment->data['duration'] = $this->calculateDuration(
            $new_schedule['service_time_start'],
            $new_schedule['service_time_end']
        );
        
        if ($appointment->save()) {
            // Send notification emails
            $email_manager = EmailManager::getInstance();
            $email_manager->sendAppointmentRescheduled($appointment);
            
            return [
                'success' => true,
                'message' => __('Appointment rescheduled successfully.', 'pro-clean-quotation')
            ];
        } else {
            return [
                'success' => false,
                'message' => __('Failed to reschedule appointment.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Cancel appointment
     * 
     * @param int $appointment_id Appointment ID
     * @param string $reason Cancellation reason
     * @return array Result
     */
    public function cancelAppointment(int $appointment_id, string $reason = ''): array {
        $appointment = new Appointment($appointment_id);
        
        if (!$appointment->getId()) {
            return [
                'success' => false,
                'message' => __('Appointment not found.', 'pro-clean-quotation')
            ];
        }
        
        if (!$appointment->canBeCancelled()) {
            return [
                'success' => false,
                'message' => __('This appointment cannot be cancelled.', 'pro-clean-quotation')
            ];
        }
        
        $appointment->setStatus('cancelled');
        $appointment->setInternalNotes($appointment->getInternalNotes() . "\nCancelled: " . $reason);
        
        if ($appointment->save()) {
            // Send notification emails
            $email_manager = EmailManager::getInstance();
            $email_manager->sendAppointmentCancelled($appointment, $reason);
            
            return [
                'success' => true,
                'message' => __('Appointment cancelled successfully.', 'pro-clean-quotation')
            ];
        } else {
            return [
                'success' => false,
                'message' => __('Failed to cancel appointment.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Get calendar events for date range
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int $employee_id Employee ID (optional)
     * @return array Calendar events
     */
    public function getCalendarEvents(string $start_date, string $end_date, int $employee_id = null): array {
        $appointments = Appointment::getForDateRange($start_date, $end_date, $employee_id);
        
        $events = [];
        foreach ($appointments as $appointment) {
            $service = $appointment->getService();
            $employee = $appointment->getEmployee();
            
            $events[] = [
                'id' => $appointment->getId(),
                'title' => $service ? $service->getName() : 'Service',
                'start' => $appointment->getServiceDate() . 'T' . $appointment->getServiceTimeStart(),
                'end' => $appointment->getServiceDate() . 'T' . $appointment->getServiceTimeEnd(),
                'backgroundColor' => $service ? $service->getColor() : '#2196F3',
                'borderColor' => $service ? $service->getColor() : '#2196F3',
                'textColor' => '#ffffff',
                'extendedProps' => [
                    'appointment_id' => $appointment->getId(),
                    'customer_name' => $appointment->getCustomerName(),
                    'customer_email' => $appointment->getCustomerEmail(),
                    'customer_phone' => $appointment->getCustomerPhone(),
                    'service_name' => $service ? $service->getName() : '',
                    'employee_name' => $employee ? $employee->getName() : 'Unassigned',
                    'price' => $appointment->getPrice(),
                    'status' => $appointment->getStatus(),
                    'notes' => $appointment->getNotes()
                ]
            ];
        }
        
        return $events;
    }
    
    /**
     * Validate appointment data
     * 
     * @param array $data Appointment data
     * @return array Validation result
     */
    private function validateAppointmentData(array $data): array {
        $errors = [];
        
        // Service date validation
        if (empty($data['service_date'])) {
            $errors['service_date'] = __('Service date is required.', 'pro-clean-quotation');
        } elseif (strtotime($data['service_date']) < strtotime(date('Y-m-d'))) {
            $errors['service_date'] = __('Service date cannot be in the past.', 'pro-clean-quotation');
        }
        
        // Time validation
        if (empty($data['service_time_start'])) {
            $errors['service_time_start'] = __('Start time is required.', 'pro-clean-quotation');
        }
        
        if (empty($data['service_time_end'])) {
            $errors['service_time_end'] = __('End time is required.', 'pro-clean-quotation');
        }
        
        if (!empty($data['service_time_start']) && !empty($data['service_time_end'])) {
            if (strtotime($data['service_time_start']) >= strtotime($data['service_time_end'])) {
                $errors['service_time_end'] = __('End time must be after start time.', 'pro-clean-quotation');
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : __('Please correct the following errors:', 'pro-clean-quotation')
        ];
    }
    
    /**
     * Validate reschedule data
     * 
     * @param array $data Reschedule data
     * @return array Validation result
     */
    private function validateRescheduleData(array $data): array {
        return $this->validateAppointmentData($data);
    }
    
    /**
     * Get or create service from quote
     * 
     * @param Quote $quote Quote object
     * @return Service|null Service object or null
     */
    private function getOrCreateServiceFromQuote(Quote $quote): ?Service {
        // Try to find existing service
        $services = Service::getAll();
        foreach ($services as $service) {
            if (strtolower($service->getName()) === strtolower($quote->getServiceType() . ' cleaning')) {
                return $service;
            }
        }
        
        // Create new service
        $service_name = ucfirst($quote->getServiceType()) . ' Cleaning';
        $duration = $this->estimateServiceDuration($quote->getSquareMeters(), $quote->getServiceType());
        
        return Service::create([
            'name' => $service_name,
            'description' => 'Professional ' . $quote->getServiceType() . ' cleaning service',
            'duration' => $duration,
            'price' => $quote->getTotalPrice(),
            'capacity' => 1,
            'buffer_time_before' => 15,
            'buffer_time_after' => 15,
            'color' => $this->getServiceColor($quote->getServiceType()),
            'status' => 'active',
            'sort_order' => 0
        ]);
    }
    
    /**
     * Find available employee for time slot
     * 
     * @param int $service_id Service ID
     * @param string $date Service date
     * @param string $time_start Start time
     * @param string $time_end End time
     * @return Employee|null Available employee or null
     */
    private function findAvailableEmployee(int $service_id, string $date, string $time_start, string $time_end): ?Employee {
        $employees = Employee::getForService($service_id);
        
        if (empty($employees)) {
            $employees = Employee::getAll(); // Fallback to all employees
        }
        
        foreach ($employees as $employee) {
            if ($employee->isAvailable($date, $time_start, $time_end)) {
                return $employee;
            }
        }
        
        return null;
    }
    
    /**
     * Calculate duration between two times
     * 
     * @param string $start_time Start time
     * @param string $end_time End time
     * @return int Duration in minutes
     */
    private function calculateDuration(string $start_time, string $end_time): int {
        $start = strtotime($start_time);
        $end = strtotime($end_time);
        
        return ($end - $start) / 60;
    }
    
    /**
     * Estimate service duration based on property size
     * 
     * @param float $square_meters Property size
     * @param string $service_type Service type
     * @return int Duration in minutes
     */
    private function estimateServiceDuration(float $square_meters, string $service_type): int {
        $base_duration = match($service_type) {
            'facade' => 120, // 2 hours
            'roof' => 180,   // 3 hours
            'both' => 300,   // 5 hours
            default => 120
        };
        
        // Adjust based on size
        if ($square_meters > 500) {
            $base_duration += 120; // Add 2 hours for large properties
        } elseif ($square_meters > 200) {
            $base_duration += 60;  // Add 1 hour for medium properties
        }
        
        return $base_duration;
    }
    
    /**
     * Get service color based on type
     * 
     * @param string $service_type Service type
     * @return string Color hex code
     */
    private function getServiceColor(string $service_type): string {
        return match($service_type) {
            'facade' => '#2196F3', // Blue
            'roof' => '#FF9800',   // Orange
            'both' => '#9C27B0',   // Purple
            default => '#2196F3'
        };
    }
}