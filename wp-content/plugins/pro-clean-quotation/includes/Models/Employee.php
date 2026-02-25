<?php

namespace ProClean\Quotation\Models;

/**
 * Employee Model Class
 * 
 * @package ProClean\Quotation\Models
 * @since 1.0.0
 */
class Employee {
    
    /**
     * Employee ID
     * 
     * @var int
     */
    private $id;
    
    /**
     * Employee data
     * 
     * @var array
     */
    public $data = [];
    
    /**
     * Constructor
     * 
     * @param int|array $employee Employee ID or data array
     */
    public function __construct($employee = null) {
        if (is_numeric($employee)) {
            $this->load($employee);
        } elseif (is_array($employee)) {
            $this->data = $employee;
            $this->id = $employee['id'] ?? null;
        }
    }
    
    /**
     * Load employee from database
     * 
     * @param int $id Employee ID
     * @return bool Success status
     */
    public function load(int $id): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_employees';
        $employee = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id),
            ARRAY_A
        );
        
        if ($employee) {
            $this->id = $id;
            $this->data = $employee;
            return true;
        }
        
        return false;
    }
    
    /**
     * Save employee to database
     * 
     * @return bool Success status
     */
    public function save(): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_employees';
        
        if ($this->id) {
            // Update existing employee
            // Remove 'id' from data array - it should only be in WHERE clause
            $data = $this->data;
            unset($data['id']);
            $data['updated_at'] = current_time('mysql');
            
            $result = $wpdb->update(
                $table,
                $data,
                ['id' => $this->id],
                $this->getFormatsForData($data),
                ['%d']
            );
            
            return $result !== false;
        } else {
            // Insert new employee
            $this->data['created_at'] = current_time('mysql');
            
            $result = $wpdb->insert(
                $table,
                $this->data,
                $this->getFormatsForData($this->data)
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
     * Create new employee
     * 
     * @param array $data Employee data
     * @return Employee|false Employee object or false on failure
     */
    public static function create(array $data) {
        $employee = new self($data);
        
        if ($employee->save()) {
            return $employee;
        }
        
        return false;
    }
    
    /**
     * Get all employees
     * 
     * @param bool $active_only Get only active employees
     * @return array Array of Employee objects
     */
    public static function getAll(bool $active_only = true): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_employees';
        $where = $active_only ? "WHERE status = 'active'" : '';
        
        $employees_data = $wpdb->get_results(
            "SELECT * FROM $table $where ORDER BY name ASC",
            ARRAY_A
        );
        
        $employees = [];
        foreach ($employees_data as $employee_data) {
            $employees[] = new self($employee_data);
        }
        
        return $employees;
    }
    
    /**
     * Get employees for service
     * 
     * @param int $service_id Service ID
     * @return array Array of Employee objects
     */
    public static function getForService(int $service_id): array {
        global $wpdb;
        
        $employees_table = $wpdb->prefix . 'pq_employees';
        $services_table = $wpdb->prefix . 'pq_employee_services';
        
        $employees_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT e.* FROM $employees_table e 
                 INNER JOIN $services_table es ON e.id = es.employee_id 
                 WHERE es.service_id = %d AND e.status = 'active'
                 ORDER BY e.name ASC",
                $service_id
            ),
            ARRAY_A
        );
        
        $employees = [];
        foreach ($employees_data as $employee_data) {
            $employees[] = new self($employee_data);
        }
        
        return $employees;
    }
    
    /**
     * Get field formats for database operations
     *
     * @return array Field formats (keyed by field name)
     */
    private function getFieldFormats(): array {
        return [
            'name' => '%s',
            'email' => '%s',
            'phone' => '%s',
            'description' => '%s',
            'avatar_url' => '%s',
            'status' => '%s',
            'working_hours' => '%s',
            'created_at' => '%s',
            'updated_at' => '%s'
        ];
    }
    
    /**
     * Get formats array in the same order as $this->data keys
     * This is critical for $wpdb->update() which expects formats in data order
     *
     * @return array Indexed array of formats matching $this->data order
     */
    /**
     * Get formats array in the same order as data keys
     * This is critical for $wpdb->update() which expects formats in data order
     *
     * @param array $data The data array to get formats for
     * @return array Indexed array of formats matching data order
     */
    private function getFormatsForData(array $data): array {
        $field_formats = $this->getFieldFormats();
        $formats = [];
        
        foreach (array_keys($data) as $key) {
            // Use the defined format if available, otherwise default to '%s'
            $formats[] = $field_formats[$key] ?? '%s';
        }
        
        return $formats;
    }
    
    // Getter methods
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getName(): string {
        return $this->data['name'] ?? '';
    }
    
    public function getEmail(): string {
        return $this->data['email'] ?? '';
    }
    
    public function getPhone(): string {
        return $this->data['phone'] ?? '';
    }
    
    public function getDescription(): string {
        return $this->data['description'] ?? '';
    }
    
    public function getAvatarUrl(): string {
        return $this->data['avatar_url'] ?? '';
    }
    
    public function getStatus(): string {
        return $this->data['status'] ?? 'active';
    }
    
    public function getWorkingHours(): array {
        $working_hours = $this->data['working_hours'] ?? '{}';
        return json_decode($working_hours, true) ?: [];
    }
    
    public function getCreatedAt(): string {
        return $this->data['created_at'] ?? '';
    }
    
    public function getUpdatedAt(): string {
        return $this->data['updated_at'] ?? '';
    }
    
    // Setter methods
    public function setName(string $name): void {
        $this->data['name'] = $name;
    }
    
    public function setEmail(string $email): void {
        $this->data['email'] = $email;
    }
    
    public function setPhone(string $phone): void {
        $this->data['phone'] = $phone;
    }
    
    public function setStatus(string $status): void {
        $this->data['status'] = $status;
    }
    
    public function setWorkingHours(array $working_hours): void {
        $this->data['working_hours'] = json_encode($working_hours);
    }
    
    // Utility methods
    public function isActive(): bool {
        return $this->getStatus() === 'active';
    }
    
    public function isAvailable(string $date, string $time_start, string $time_end): bool {
        // Check working hours
        $working_hours = $this->getWorkingHours();
        $day_of_week = strtolower(date('l', strtotime($date)));
        
        if (!isset($working_hours[$day_of_week]) || !$working_hours[$day_of_week]['enabled']) {
            return false;
        }
        
        $work_start = $working_hours[$day_of_week]['start'];
        $work_end = $working_hours[$day_of_week]['end'];
        
        if ($time_start < $work_start || $time_end > $work_end) {
            return false;
        }
        
        // Check for existing appointments
        return !$this->hasConflictingAppointment($date, $time_start, $time_end);
    }
    
    public function hasConflictingAppointment(string $date, string $time_start, string $time_end): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_appointments';
        
        $conflicts = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $table 
                 WHERE employee_id = %d 
                 AND service_date = %s 
                 AND status NOT IN ('cancelled', 'no_show')
                 AND (
                     (service_time_start < %s AND service_time_end > %s) OR
                     (service_time_start < %s AND service_time_end > %s) OR
                     (service_time_start >= %s AND service_time_end <= %s)
                 )",
                $this->getId(),
                $date,
                $time_end,
                $time_start,
                $time_end,
                $time_start,
                $time_start,
                $time_end
            )
        );
        
        return $conflicts > 0;
    }
    
    public function getServices(): array {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'pq_services';
        $employee_services_table = $wpdb->prefix . 'pq_employee_services';
        
        $services_data = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT s.* FROM $services_table s 
                 INNER JOIN $employee_services_table es ON s.id = es.service_id 
                 WHERE es.employee_id = %d AND s.status = 'active'
                 ORDER BY s.name ASC",
                $this->getId()
            ),
            ARRAY_A
        );
        
        $services = [];
        foreach ($services_data as $service_data) {
            $services[] = new Service($service_data);
        }
        
        return $services;
    }
    
    public function toArray(): array {
        return $this->data;
    }
}