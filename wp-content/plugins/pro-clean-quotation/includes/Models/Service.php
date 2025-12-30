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
            // Insert new service
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
     * @return array Field formats
     */
    private function getFieldFormats(): array {
        return [
            'name' => '%s',
            'description' => '%s',
            'duration' => '%d',
            'price' => '%f',
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
    
    public function getDuration(): int {
        return (int) ($this->data['duration'] ?? 60);
    }
    
    public function getPrice(): float {
        return (float) ($this->data['price'] ?? 0);
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
    
    public function setDuration(int $duration): void {
        $this->data['duration'] = $duration;
    }
    
    public function setPrice(float $price): void {
        $this->data['price'] = $price;
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
}