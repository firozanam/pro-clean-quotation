<?php

namespace ProClean\Quotation\Models;

/**
 * Appointment Model Class
 * 
 * @package ProClean\Quotation\Models
 * @since 1.0.0
 */
class Appointment {
    
    /**
     * Appointment ID
     * 
     * @var int
     */
    private $id;
    
    /**
     * Appointment data
     * 
     * @var array
     */
    public $data = [];
    
    /**
     * Constructor
     * 
     * @param int|array $appointment Appointment ID or data array
     */
    public function __construct($appointment = null) {
        if (is_numeric($appointment)) {
            $this->load($appointment);
        } elseif (is_array($appointment)) {
            $this->data = $appointment;
            $this->id = $appointment['id'] ?? null;
        }
    }
    
    /**
     * Load appointment from database
     * 
     * @param int $id Appointment ID
     * @return bool Success status
     */
    public function load(int $id): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_appointments';
        $appointment = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id),
            ARRAY_A
        );
        
        if ($appointment) {
            $this->id = $id;
            $this->data = $appointment;
            return true;
        }
        
        return false;
    }
    
    /**
     * Save appointment to database
     * 
     * @return bool Success status
     */
    public function save(): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_appointments';
        
        if ($this->id) {
            // Update existing appointment
            $this->data['updated_at'] = current_time('mysql');
            
            $result = $wpdb->update(
                $table,
                $this->data,
                ['id' => $this->id],
                $this->getFieldFormats(),
                ['%d']
            );
            
            return $result !== false;
        } else {
            // Insert new appointment
            $this->data['created_at'] = current_time('mysql');
            
            $result = $wpdb->insert(
                $table,
                $this->data,
                $this->getFieldFormats()
            );
            
            if ($result) {
                $this->id = $wpdb->insert_id;
                $this->data['id'] = $this->id;
                return true;
            }
            
            return false;
        }
    }
    
    /**
     * Create new appointment
     * 
     * @param array $data Appointment data
     * @return Appointment|false Appointment object or false on failure
     */
    public static function create(array $data) {
        $appointment = new self($data);
        
        if ($appointment->save()) {
            return $appointment;
        }
        
        return false;
    }
    
    /**
     * Get appointments with filters
     * 
     * @param array $filters Filters
     * @param int $page Page number
     * @param int $per_page Items per page
     * @return array Appointments data with pagination
     */
    public static function getAll(array $filters = [], int $page = 1, int $per_page = 20): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_appointments';
        $where_clauses = ['1=1'];
        $where_values = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $filters['status'];
        }
        
        if (!empty($filters['service_id'])) {
            $where_clauses[] = 'service_id = %d';
            $where_values[] = $filters['service_id'];
        }
        
        if (!empty($filters['employee_id'])) {
            $where_clauses[] = 'employee_id = %d';
            $where_values[] = $filters['employee_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'service_date >= %s';
            $where_values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'service_date <= %s';
            $where_values[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where_clauses[] = '(customer_name LIKE %s OR customer_email LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_sql = implode(' AND ', $where_clauses);
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM $table WHERE $where_sql";
        if (!empty($where_values)) {
            $count_sql = $wpdb->prepare($count_sql, $where_values);
        }
        $total = $wpdb->get_var($count_sql);
        
        // Get appointments with pagination
        $offset = ($page - 1) * $per_page;
        $appointments_sql = "SELECT * FROM $table WHERE $where_sql ORDER BY service_date DESC, service_time_start DESC LIMIT %d OFFSET %d";
        $appointments_values = array_merge($where_values, [$per_page, $offset]);
        $appointments_sql = $wpdb->prepare($appointments_sql, $appointments_values);
        
        $appointments_data = $wpdb->get_results($appointments_sql, ARRAY_A);
        
        $appointments = [];
        foreach ($appointments_data as $appointment_data) {
            $appointments[] = new self($appointment_data);
        }
        
        return [
            'appointments' => $appointments,
            'total' => (int) $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page,
            'per_page' => $per_page
        ];
    }
    
    /**
     * Find appointments by customer email
     * 
     * @param string $email Customer email
     * @return array Array of Appointment objects
     */
    public static function findByEmail(string $email): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_appointments';
        $appointments = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE customer_email = %s ORDER BY service_date DESC", $email),
            ARRAY_A
        );
        
        $result = [];
        foreach ($appointments as $appointment_data) {
            $result[] = new self($appointment_data);
        }
        
        return $result;
    }
    
    /**
     * Get appointments for date range
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param int|null $employee_id Employee ID (optional)
     * @return array Array of Appointment objects
     */
    public static function getForDateRange(string $start_date, string $end_date, ?int $employee_id = null): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_appointments';
        $where = "service_date BETWEEN %s AND %s AND status NOT IN ('cancelled', 'no_show')";
        $params = [$start_date, $end_date];
        
        if ($employee_id) {
            $where .= " AND employee_id = %d";
            $params[] = $employee_id;
        }
        
        $appointments_data = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE $where ORDER BY service_date ASC, service_time_start ASC", $params),
            ARRAY_A
        );
        
        $appointments = [];
        foreach ($appointments_data as $appointment_data) {
            $appointments[] = new self($appointment_data);
        }
        
        return $appointments;
    }
    
    /**
     * Get field formats for database operations
     * 
     * @return array Field formats
     */
    private function getFieldFormats(): array {
        return [
            'service_id' => '%d',
            'employee_id' => '%d',
            'quote_id' => '%d',
            'customer_name' => '%s',
            'customer_email' => '%s',
            'customer_phone' => '%s',
            'service_date' => '%s',
            'service_time_start' => '%s',
            'service_time_end' => '%s',
            'duration' => '%d',
            'price' => '%f',
            'status' => '%s',
            'notes' => '%s',
            'internal_notes' => '%s',
            'created_at' => '%s',
            'updated_at' => '%s'
        ];
    }
    
    // Getter methods
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getServiceId(): int {
        return (int) ($this->data['service_id'] ?? 0);
    }
    
    public function getEmployeeId(): ?int {
        return $this->data['employee_id'] ? (int) $this->data['employee_id'] : null;
    }
    
    public function getQuoteId(): ?int {
        return $this->data['quote_id'] ? (int) $this->data['quote_id'] : null;
    }
    
    public function getCustomerName(): string {
        return $this->data['customer_name'] ?? '';
    }
    
    public function getCustomerEmail(): string {
        return $this->data['customer_email'] ?? '';
    }
    
    public function getCustomerPhone(): string {
        return $this->data['customer_phone'] ?? '';
    }
    
    public function getServiceDate(): string {
        return $this->data['service_date'] ?? '';
    }
    
    public function getServiceTimeStart(): string {
        return $this->data['service_time_start'] ?? '';
    }
    
    public function getServiceTimeEnd(): string {
        return $this->data['service_time_end'] ?? '';
    }
    
    public function getDuration(): int {
        return (int) ($this->data['duration'] ?? 60);
    }
    
    public function getPrice(): float {
        return (float) ($this->data['price'] ?? 0);
    }
    
    public function getStatus(): string {
        return $this->data['status'] ?? 'pending';
    }
    
    public function getNotes(): string {
        return $this->data['notes'] ?? '';
    }
    
    public function getInternalNotes(): string {
        return $this->data['internal_notes'] ?? '';
    }
    
    public function getCreatedAt(): string {
        return $this->data['created_at'] ?? '';
    }
    
    public function getUpdatedAt(): string {
        return $this->data['updated_at'] ?? '';
    }
    
    // Setter methods
    public function setStatus(string $status): void {
        $this->data['status'] = $status;
    }
    
    public function setEmployeeId(?int $employee_id): void {
        $this->data['employee_id'] = $employee_id;
    }
    
    public function setNotes(string $notes): void {
        $this->data['notes'] = $notes;
    }
    
    public function setInternalNotes(string $notes): void {
        $this->data['internal_notes'] = $notes;
    }
    
    // Utility methods
    public function getService(): ?Service {
        if ($this->getServiceId()) {
            return new Service($this->getServiceId());
        }
        return null;
    }
    
    public function getEmployee(): ?Employee {
        if ($this->getEmployeeId()) {
            return new Employee($this->getEmployeeId());
        }
        return null;
    }
    
    /**
     * Get all assigned employees for this appointment
     * 
     * @return array Array of Employee objects
     */
    public function getEmployees(): array {
        global $wpdb;
        
        if (!$this->getId()) {
            return [];
        }
        
        $table = $wpdb->prefix . 'pq_appointment_employees';
        
        // Check if table exists before querying
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table
            )
        );
        
        if (!$table_exists) {
            // Fallback: return primary employee if set
            if ($this->getEmployeeId()) {
                $employee = new Employee($this->getEmployeeId());
                if ($employee->getId()) {
                    return [$employee];
                }
            }
            return [];
        }
        
        $employee_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT employee_id FROM $table WHERE appointment_id = %d ORDER BY created_at ASC",
            $this->getId()
        ));
        
        // Fallback to primary employee if junction table is empty
        if (empty($employee_ids) && $this->getEmployeeId()) {
            $employee_ids = [$this->getEmployeeId()];
        }
        
        $employees = [];
        foreach ($employee_ids as $employee_id) {
            $employee = new Employee($employee_id);
            if ($employee->getId()) {
                $employees[] = $employee;
            }
        }
        
        return $employees;
    }
    
    /**
     * Get employee IDs assigned to this appointment
     * 
     * @return array Array of employee IDs
     */
    public function getEmployeeIds(): array {
        global $wpdb;
        
        if (!$this->getId()) {
            return [];
        }
        
        $table = $wpdb->prefix . 'pq_appointment_employees';
        
        // Check if table exists before querying
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table
            )
        );
        
        if (!$table_exists) {
            // Fallback: return primary employee_id if set
            if ($this->getEmployeeId()) {
                return [$this->getEmployeeId()];
            }
            return [];
        }
        
        $employee_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT employee_id FROM $table WHERE appointment_id = %d ORDER BY created_at ASC",
            $this->getId()
        ));
        
        if (empty($employee_ids) && $this->getEmployeeId()) {
            // Fallback: return primary employee_id if junction table is empty
            return [$this->getEmployeeId()];
        }
        
        return array_map('intval', $employee_ids);
    }
    
    /**
     * Set multiple employees for this appointment
     * 
     * @param array $employee_ids Array of employee IDs
     * @param string $role Employee role (technician, lead, supervisor)
     * @return bool Success status
     */
    public function setEmployees(array $employee_ids, string $role = 'technician'): bool {
        global $wpdb;
        
        if (!$this->getId()) {
            return false;
        }
        
        $table = $wpdb->prefix . 'pq_appointment_employees';
        
        // Check if table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table
            )
        );
        
        if (!$table_exists) {
            // Table doesn't exist, just update primary employee_id field
            if (!empty($employee_ids)) {
                $this->data['employee_id'] = (int) $employee_ids[0];
                return $this->save();
            }
            return false;
        }
        
        // Remove existing assignments
        $wpdb->delete($table, ['appointment_id' => $this->getId()], ['%d']);
        
        // Add new assignments
        $success = true;
        foreach ($employee_ids as $employee_id) {
            $result = $wpdb->insert($table, [
                'appointment_id' => $this->getId(),
                'employee_id' => (int) $employee_id,
                'role' => $role,
                'created_at' => current_time('mysql')
            ]);
            
            if ($result === false) {
                $success = false;
            }
        }
        
        // Also update the primary employee_id field (for backward compatibility)
        if (!empty($employee_ids)) {
            $this->data['employee_id'] = (int) $employee_ids[0];
            $this->save();
        }
        
        return $success;
    }
    
    /**
     * Add employee to appointment
     * 
     * @param int $employee_id Employee ID
     * @param string $role Employee role
     * @return bool Success status
     */
    public function addEmployee(int $employee_id, string $role = 'technician'): bool {
        global $wpdb;
        
        if (!$this->getId()) {
            return false;
        }
        
        $table = $wpdb->prefix . 'pq_appointment_employees';
        
        // Check if table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table
            )
        );
        
        if (!$table_exists) {
            // Table doesn't exist, just update primary employee_id field if not set
            if (!$this->getEmployeeId()) {
                $this->data['employee_id'] = $employee_id;
                return $this->save();
            }
            return true; // Already has an employee assigned
        }
        
        // Check if already assigned
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE appointment_id = %d AND employee_id = %d",
            $this->getId(),
            $employee_id
        ));
        
        if ($exists > 0) {
            return true; // Already assigned
        }
        
        return $wpdb->insert($table, [
            'appointment_id' => $this->getId(),
            'employee_id' => $employee_id,
            'role' => $role,
            'created_at' => current_time('mysql')
        ]) !== false;
    }
    
    /**
     * Remove employee from appointment
     * 
     * @param int $employee_id Employee ID
     * @return bool Success status
     */
    public function removeEmployee(int $employee_id): bool {
        global $wpdb;
        
        if (!$this->getId()) {
            return false;
        }
        
        $table = $wpdb->prefix . 'pq_appointment_employees';
        
        // Check if table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $table
            )
        );
        
        if (!$table_exists) {
            // Table doesn't exist, just clear primary employee_id if it matches
            if ($this->getEmployeeId() === $employee_id) {
                $this->data['employee_id'] = null;
                return $this->save();
            }
            return true; // Employee not assigned
        }
        
        return $wpdb->delete($table, [
            'appointment_id' => $this->getId(),
            'employee_id' => $employee_id
        ], ['%d', '%d']) !== false;
    }
    
    /**
     * Get team size (number of employees assigned)
     * 
     * @return int Number of employees
     */
    public function getTeamSize(): int {
        return count($this->getEmployeeIds());
    }
    
    public function getQuote(): ?Quote {
        if ($this->getQuoteId()) {
            return new Quote($this->getQuoteId());
        }
        return null;
    }
    
    public function getDateTime(): \DateTime {
        return new \DateTime($this->getServiceDate() . ' ' . $this->getServiceTimeStart());
    }
    
    public function getEndDateTime(): \DateTime {
        return new \DateTime($this->getServiceDate() . ' ' . $this->getServiceTimeEnd());
    }
    
    public function isPast(): bool {
        return $this->getDateTime() < new \DateTime();
    }
    
    public function isFuture(): bool {
        return $this->getDateTime() > new \DateTime();
    }
    
    public function isToday(): bool {
        return $this->getServiceDate() === date('Y-m-d');
    }
    
    public function canBeCancelled(): bool {
        $hours_until = ($this->getDateTime()->getTimestamp() - time()) / 3600;
        return $hours_until >= 24 && in_array($this->getStatus(), ['pending', 'confirmed']);
    }
    
    public function canBeRescheduled(): bool {
        return $this->canBeCancelled();
    }
    
    public function toArray(): array {
        return $this->data;
    }
}