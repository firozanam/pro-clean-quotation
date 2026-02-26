<?php

namespace ProClean\Quotation\Models;

/**
 * Service Model Class
 * 
 * @package ProClean\Quotation\Models
 * @since 1.0.0
 */
class Service {
    
    /**
     * Service ID
     * 
     * @var int
     */
    private $id;
    
    /**
     * Service data
     * 
     * @var array
     */
    public $data = [];
    
    /**
     * Constructor
     * 
     * @param int|array $service Service ID or data array
     */
    public function __construct($service = null) {
        if (is_numeric($service)) {
            $this->load($service);
        } elseif (is_array($service)) {
            $this->data = $service;
            $this->id = $service['id'] ?? null;
        }
    }
    
    /**
     * Load service from database
     * 
     * @param int $id Service ID
     * @return bool Success status
     */
    public function load(int $id): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_services';
        $service = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id),
            ARRAY_A
        );
        
        if ($service) {
            $this->id = $id;
            $this->data = $service;
            return true;
        }
        
        return false;
    }
    
    /**
     * Save service to database
     *
     * @return bool Success status
     */
    public function save(): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_services';
        
        if ($this->id) {
            // Update existing service
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
            // Insert new service
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
     * Create new service
     * 
     * @param array $data Service data
     * @return Service|false Service object or false on failure
     */
    public static function create(array $data) {
        $service = new self($data);
        
        if ($service->save()) {
            return $service;
        }
        
        return false;
    }
    
    /**
     * Get all services
     * 
     * @param bool $active_only Get only active services
     * @return array Array of Service objects
     */
    public static function getAll(bool $active_only = true): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_services';
        $where = $active_only ? "WHERE status = 'active'" : '';
        
        $services_data = $wpdb->get_results(
            "SELECT * FROM $table $where ORDER BY sort_order ASC, name ASC",
            ARRAY_A
        );
        
        $services = [];
        foreach ($services_data as $service_data) {
            $services[] = new self($service_data);
        }
        
        return $services;
    }
    
    /**
     * Get field formats for database operations
     *
     * @return array Field formats (keyed by field name)
     */
    private function getFieldFormats(): array {
        return [
            'name' => '%s',
            'description' => '%s',
            'duration' => '%d',  // Can be null for optional scheduling
            'price' => '%f',
            'base_rate' => '%f',
            'rate_per_sqm' => '%f',
            'rate_per_linear_meter' => '%f',
            'capacity' => '%d',
            'buffer_time_before' => '%d',
            'buffer_time_after' => '%d',
            'category_id' => '%d',
            'color' => '%s',
            'status' => '%s',
            'sort_order' => '%d',
            'min_advance_time' => '%d',
            'max_advance_time' => '%d',
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
    
    public function getDescription(): string {
        return $this->data['description'] ?? '';
    }
    
    public function getDuration(): ?int {
        return isset($this->data['duration']) && $this->data['duration'] !== null && $this->data['duration'] !== ''
            ? (int) $this->data['duration']
            : null;
    }
    
    public function getPrice(): float {
        return (float) ($this->data['price'] ?? 0);
    }
    
    public function getBaseRate(): float {
        return (float) ($this->data['base_rate'] ?? 20.00);
    }
    
    public function getRatePerSqm(): float {
        return (float) ($this->data['rate_per_sqm'] ?? 20.00);
    }
    
    public function getRatePerLinearMeter(): float {
        return (float) ($this->data['rate_per_linear_meter'] ?? 5.00);
    }
    
    public function getCapacity(): int {
        return (int) ($this->data['capacity'] ?? 1);
    }
    
    public function getBufferTimeBefore(): int {
        return (int) ($this->data['buffer_time_before'] ?? 0);
    }
    
    public function getBufferTimeAfter(): int {
        return (int) ($this->data['buffer_time_after'] ?? 0);
    }
    
    public function getCategoryId(): ?int {
        return $this->data['category_id'] ? (int) $this->data['category_id'] : null;
    }
    
    public function getColor(): string {
        return $this->data['color'] ?? '#2196F3';
    }
    
    public function getStatus(): string {
        return $this->data['status'] ?? 'active';
    }
    
    public function getSortOrder(): int {
        return (int) ($this->data['sort_order'] ?? 0);
    }
    
    public function getMinAdvanceTime(): int {
        return (int) ($this->data['min_advance_time'] ?? 0);
    }
    
    public function getMaxAdvanceTime(): int {
        return (int) ($this->data['max_advance_time'] ?? 0);
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
    
    public function setDescription(string $description): void {
        $this->data['description'] = $description;
    }
    
    public function setDuration(?int $duration): void {
        $this->data['duration'] = $duration;
    }
    
    public function setPrice(float $price): void {
        $this->data['price'] = $price;
    }
    
    public function setBaseRate(float $base_rate): void {
        $this->data['base_rate'] = $base_rate;
    }
    
    public function setRatePerSqm(float $rate_per_sqm): void {
        $this->data['rate_per_sqm'] = $rate_per_sqm;
    }
    
    public function setRatePerLinearMeter(float $rate_per_linear_meter): void {
        $this->data['rate_per_linear_meter'] = $rate_per_linear_meter;
    }
    
    public function setCapacity(int $capacity): void {
        $this->data['capacity'] = $capacity;
    }
    
    public function setStatus(string $status): void {
        $this->data['status'] = $status;
    }
    
    public function setColor(string $color): void {
        $this->data['color'] = $color;
    }
    
    // Utility methods
    public function isActive(): bool {
        return $this->getStatus() === 'active';
    }
    
    public function toArray(): array {
        return $this->data;
    }
    
    // ===== Meta Methods =====
    
    /**
     * Get meta value
     * 
     * @param string $meta_key Meta key
     * @param mixed $default Default value if meta doesn't exist
     * @return mixed Meta value or default
     */
    public function getMeta(string $meta_key, $default = null) {
        if (!$this->id) {
            return $default;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'pq_service_meta';
        
        $meta_value = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $table WHERE service_id = %d AND meta_key = %s",
            $this->id,
            $meta_key
        ));
        
        if ($meta_value === null) {
            return $default;
        }
        
        // Try to decode as JSON
        $decoded = json_decode($meta_value, true);
        return (json_last_error() === JSON_ERROR_NONE) ? $decoded : $meta_value;
    }
    
    /**
     * Set meta value
     * 
     * @param string $meta_key Meta key
     * @param mixed $meta_value Meta value (will be JSON encoded if array/object)
     * @return bool Success status
     */
    public function setMeta(string $meta_key, $meta_value): bool {
        if (!$this->id) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'pq_service_meta';
        
        // Encode arrays and objects as JSON
        if (is_array($meta_value) || is_object($meta_value)) {
            $meta_value = wp_json_encode($meta_value);
        }
        
        // Check if meta already exists
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE service_id = %d AND meta_key = %s",
            $this->id,
            $meta_key
        ));
        
        $current_time = current_time('mysql');
        
        if ($existing) {
            // Update existing meta
            $result = $wpdb->update(
                $table,
                [
                    'meta_value' => $meta_value,
                    'updated_at' => $current_time
                ],
                [
                    'service_id' => $this->id,
                    'meta_key' => $meta_key
                ],
                ['%s', '%s'],
                ['%d', '%s']
            );
        } else {
            // Insert new meta
            $result = $wpdb->insert(
                $table,
                [
                    'service_id' => $this->id,
                    'meta_key' => $meta_key,
                    'meta_value' => $meta_value,
                    'created_at' => $current_time,
                    'updated_at' => $current_time
                ],
                ['%d', '%s', '%s', '%s', '%s']
            );
        }
        
        return $result !== false;
    }
    
    /**
     * Delete meta
     * 
     * @param string $meta_key Meta key
     * @return bool Success status
     */
    public function deleteMeta(string $meta_key): bool {
        if (!$this->id) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'pq_service_meta';
        
        $result = $wpdb->delete(
            $table,
            [
                'service_id' => $this->id,
                'meta_key' => $meta_key
            ],
            ['%d', '%s']
        );
        
        return $result !== false;
    }
    
    /**
     * Get all meta for this service
     * 
     * @return array Associative array of meta_key => meta_value
     */
    public function getAllMeta(): array {
        if (!$this->id) {
            return [];
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'pq_service_meta';
        
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT meta_key, meta_value FROM $table WHERE service_id = %d",
            $this->id
        ), ARRAY_A);
        
        $meta = [];
        foreach ($results as $row) {
            $value = $row['meta_value'];
            // Try to decode JSON
            $decoded = json_decode($value, true);
            $meta[$row['meta_key']] = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $value;
        }
        
        return $meta;
    }
    
    // ===== Custom Fields Methods =====
    
    /**
     * Get custom fields configuration
     * 
     * @return array Array of custom field definitions
     */
    public function getCustomFields(): array {
        $fields = $this->getMeta('custom_fields', []);
        return is_array($fields) ? $fields : [];
    }
    
    /**
     * Set custom fields configuration
     * 
     * @param array $fields Array of custom field definitions
     * @return bool Success status
     */
    public function setCustomFields(array $fields): bool {
        return $this->setMeta('custom_fields', $fields);
    }
    
    /**
     * Check if service has custom fields
     * 
     * @return bool True if service has custom fields configured
     */
    public function hasCustomFields(): bool {
        $fields = $this->getCustomFields();
        return !empty($fields);
    }
}