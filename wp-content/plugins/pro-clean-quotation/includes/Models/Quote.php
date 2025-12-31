<?php

namespace ProClean\Quotation\Models;

/**
 * Quote Model Class
 * 
 * @package ProClean\Quotation\Models
 * @since 1.0.0
 */
class Quote {
    
    /**
     * Quote ID
     * 
     * @var int
     */
    private $id;
    
    /**
     * Quote data
     * 
     * @var array
     */
    public $data = [];
    
    /**
     * Constructor
     * 
     * @param int|array $quote Quote ID or data array
     */
    public function __construct($quote = null) {
        if (is_numeric($quote)) {
            $this->load($quote);
        } elseif (is_array($quote)) {
            $this->data = $quote;
            $this->id = $quote['id'] ?? null;
        }
    }
    
    /**
     * Load quote from database
     * 
     * @param int $id Quote ID
     * @return bool Success status
     */
    public function load(int $id): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_quotes';
        $quote = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE id = %d", $id),
            ARRAY_A
        );
        
        if ($quote) {
            $this->id = $id;
            $this->data = $quote;
            return true;
        }
        
        return false;
    }
    
    /**
     * Save quote to database
     * 
     * @return bool Success status
     */
    public function save(): bool {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_quotes';
        
        if ($this->id) {
            // Update existing quote
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
            // Insert new quote
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
     * Delete quote from database
     * 
     * @return bool Success status
     */
    public function delete(): bool {
        if (!$this->id) {
            return false;
        }
        
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_quotes';
        $result = $wpdb->delete($table, ['id' => $this->id], ['%d']);
        
        if ($result) {
            $this->id = null;
            $this->data = [];
            return true;
        }
        
        return false;
    }
    
    /**
     * Create new quote
     * 
     * @param array $data Quote data
     * @return Quote|false Quote object or false on failure
     */
    public static function create(array $data) {
        $quote = new self($data);
        
        if ($quote->save()) {
            return $quote;
        }
        
        return false;
    }
    
    /**
     * Find quote by quote number
     * 
     * @param string $quote_number Quote number
     * @return Quote|false Quote object or false if not found
     */
    public static function findByQuoteNumber(string $quote_number) {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_quotes';
        $quote = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE quote_number = %s", $quote_number),
            ARRAY_A
        );
        
        if ($quote) {
            return new self($quote);
        }
        
        return false;
    }
    
    /**
     * Find quotes by email
     * 
     * @param string $email Customer email
     * @return array Array of Quote objects
     */
    public static function findByEmail(string $email): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_quotes';
        $quotes = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table WHERE customer_email = %s ORDER BY created_at DESC", $email),
            ARRAY_A
        );
        
        $result = [];
        foreach ($quotes as $quote_data) {
            $result[] = new self($quote_data);
        }
        
        return $result;
    }
    
    /**
     * Get all quotes with pagination
     * 
     * @param int $page Page number
     * @param int $per_page Items per page
     * @param array $filters Filters
     * @return array Array with quotes and total count
     */
    public static function getAll(int $page = 1, int $per_page = 20, array $filters = []): array {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_quotes';
        $where_clauses = ['1=1'];
        $where_values = [];
        
        // Apply filters
        if (!empty($filters['status'])) {
            $where_clauses[] = 'status = %s';
            $where_values[] = $filters['status'];
        }
        
        if (!empty($filters['service_type'])) {
            $where_clauses[] = 'service_type = %s';
            $where_values[] = $filters['service_type'];
        }
        
        if (!empty($filters['date_from'])) {
            $where_clauses[] = 'DATE(created_at) >= %s';
            $where_values[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where_clauses[] = 'DATE(created_at) <= %s';
            $where_values[] = $filters['date_to'];
        }
        
        if (!empty($filters['search'])) {
            $where_clauses[] = '(customer_name LIKE %s OR customer_email LIKE %s OR quote_number LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_values[] = $search_term;
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
        
        // Get quotes with pagination
        $offset = ($page - 1) * $per_page;
        $quotes_sql = "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $quotes_values = array_merge($where_values, [$per_page, $offset]);
        $quotes_sql = $wpdb->prepare($quotes_sql, $quotes_values);
        
        $quotes_data = $wpdb->get_results($quotes_sql, ARRAY_A);
        
        $quotes = [];
        foreach ($quotes_data as $quote_data) {
            $quotes[] = new self($quote_data);
        }
        
        return [
            'quotes' => $quotes,
            'total' => (int) $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page,
            'per_page' => $per_page
        ];
    }
    
    /**
     * Get field formats for database operations
     * 
     * @return array Field formats
     */
    private function getFieldFormats(): array {
        return [
            'quote_number' => '%s',
            'customer_name' => '%s',
            'customer_email' => '%s',
            'customer_phone' => '%s',
            'property_address' => '%s',
            'postal_code' => '%s',
            'service_type' => '%s',
            'square_meters' => '%f',
            'linear_meters' => '%f',
            'building_height' => '%d',
            'property_type' => '%s',
            'surface_material' => '%s',
            'roof_type' => '%s',
            'last_cleaning_date' => '%s',
            'special_requirements' => '%s',
            'base_price' => '%f',
            'adjustments' => '%f',
            'subtotal' => '%f',
            'tax_amount' => '%f',
            'total_price' => '%f',
            'price_breakdown' => '%s',
            'status' => '%s',
            'valid_until' => '%s',
            'user_ip' => '%s',
            'user_agent' => '%s',
            'marketing_consent' => '%d',
            'privacy_consent' => '%d',
            'created_at' => '%s',
            'updated_at' => '%s'
        ];
    }
    
    // Getter methods
    public function getId(): ?int {
        return $this->id;
    }
    
    public function getQuoteNumber(): string {
        return $this->data['quote_number'] ?? '';
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
    
    public function getPropertyAddress(): string {
        return $this->data['property_address'] ?? '';
    }
    
    public function getPostalCode(): string {
        return $this->data['postal_code'] ?? '';
    }
    
    public function getServiceType(): string {
        return $this->data['service_type'] ?? '';
    }
    
    public function getSquareMeters(): float {
        return (float) ($this->data['square_meters'] ?? 0);
    }
    
    public function getLinearMeters(): float {
        return (float) ($this->data['linear_meters'] ?? 0);
    }
    
    public function getBuildingHeight(): int {
        return (int) ($this->data['building_height'] ?? 1);
    }
    
    public function getPropertyType(): string {
        return $this->data['property_type'] ?? 'residential';
    }
    
    public function getSurfaceMaterial(): string {
        return $this->data['surface_material'] ?? 'brick';
    }
    
    public function getRoofType(): string {
        return $this->data['roof_type'] ?? '';
    }
    
    public function getLastCleaningDate(): ?string {
        return $this->data['last_cleaning_date'] ?? null;
    }
    
    public function getSpecialRequirements(): string {
        return $this->data['special_requirements'] ?? '';
    }
    
    public function getBasePrice(): float {
        return (float) ($this->data['base_price'] ?? 0);
    }
    
    public function getAdjustments(): float {
        return (float) ($this->data['adjustments'] ?? 0);
    }
    
    public function getSubtotal(): float {
        return (float) ($this->data['subtotal'] ?? 0);
    }
    
    public function getTaxAmount(): float {
        return (float) ($this->data['tax_amount'] ?? 0);
    }
    
    public function getTotalPrice(): float {
        return (float) ($this->data['total_price'] ?? 0);
    }
    
    public function getPriceBreakdown(): array {
        $breakdown = $this->data['price_breakdown'] ?? '[]';
        return json_decode($breakdown, true) ?: [];
    }
    
    public function getStatus(): string {
        return $this->data['status'] ?? 'new';
    }
    
    public function getValidUntil(): string {
        return $this->data['valid_until'] ?? '';
    }
    
    public function getUserIp(): string {
        return $this->data['user_ip'] ?? '';
    }
    
    public function getUserAgent(): string {
        return $this->data['user_agent'] ?? '';
    }
    
    public function hasMarketingConsent(): bool {
        return !empty($this->data['marketing_consent']);
    }
    
    public function hasPrivacyConsent(): bool {
        return !empty($this->data['privacy_consent']);
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
    
    public function setCustomerName(string $name): void {
        $this->data['customer_name'] = $name;
    }
    
    public function setCustomerEmail(string $email): void {
        $this->data['customer_email'] = $email;
    }
    
    public function setCustomerPhone(string $phone): void {
        $this->data['customer_phone'] = $phone;
    }
    
    // Utility methods
    public function isExpired(): bool {
        if (empty($this->data['valid_until'])) {
            return false;
        }
        
        return strtotime($this->data['valid_until']) < time();
    }
    
    public function canBeBooked(): bool {
        // Allow booking if quote is not expired and not explicitly cancelled/rejected
        // This allows multiple bookings from the same quote (different dates/times)
        $blocked_statuses = ['cancelled', 'rejected', 'expired'];
        return !in_array($this->getStatus(), $blocked_statuses) && !$this->isExpired();
    }
    
    public function getToken(): string {
        return md5($this->getId() . $this->getQuoteNumber() . $this->getCustomerEmail());
    }
    
    public function verifyToken(string $token): bool {
        return hash_equals($this->getToken(), $token);
    }
    
    public function toArray(): array {
        return $this->data;
    }
}