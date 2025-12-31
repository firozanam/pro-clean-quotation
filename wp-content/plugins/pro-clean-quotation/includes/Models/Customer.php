<?php

namespace ProClean\Quotation\Models;

/**
 * Customer Model Class
 * 
 * Aggregates customer information from quotes and appointments
 * 
 * @package ProClean\Quotation\Models
 * @since 1.0.0
 */
class Customer {
    
    /**
     * Customer data
     * 
     * @var array
     */
    public $data = [];
    
    /**
     * Constructor
     * 
     * @param array $data Customer data
     */
    public function __construct(array $data = []) {
        $this->data = $data;
    }
    
    /**
     * Get all unique customers with pagination and filters
     * 
     * @param int $page Page number
     * @param int $per_page Items per page
     * @param array $filters Filters array
     * @return array Array with customers and pagination data
     */
    public static function getAll(int $page = 1, int $per_page = 20, array $filters = []): array {
        global $wpdb;
        
        $quotes_table = $wpdb->prefix . 'pq_quotes';
        $appointments_table = $wpdb->prefix . 'pq_appointments';
        
        // Build WHERE clause
        $where_conditions = ['1=1'];
        $where_values = [];
        
        // Search filter
        if (!empty($filters['search'])) {
            $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
            $where_conditions[] = "(customer_name LIKE %s OR customer_email LIKE %s OR customer_phone LIKE %s OR property_address LIKE %s)";
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        // Status filter (based on recent activity)
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                // Has appointments or quotes in last 6 months
                $where_conditions[] = "(last_activity >= DATE_SUB(NOW(), INTERVAL 6 MONTH))";
            } elseif ($filters['status'] === 'inactive') {
                // No activity in last 6 months or NULL
                $where_conditions[] = "(last_activity < DATE_SUB(NOW(), INTERVAL 6 MONTH) OR last_activity IS NULL)";
            }
        }
        
        $where_clause = implode(' AND ', $where_conditions);
        
        // Build the unified customer query
        $offset = ($page - 1) * $per_page;
        
        // Aggregate customers from both quotes and appointments
        $query = "
            SELECT 
                customer_email,
                MAX(customer_name) as customer_name,
                MAX(customer_phone) as customer_phone,
                MAX(property_address) as property_address,
                COUNT(DISTINCT quote_id) as total_quotes,
                COUNT(DISTINCT appointment_id) as total_appointments,
                MAX(last_activity) as last_activity,
                SUM(total_spent) as total_spent,
                MIN(first_contact) as first_contact
            FROM (
                SELECT 
                    customer_email,
                    customer_name,
                    customer_phone,
                    property_address,
                    id as quote_id,
                    NULL as appointment_id,
                    created_at as last_activity,
                    total_price as total_spent,
                    created_at as first_contact
                FROM $quotes_table
                WHERE customer_email IS NOT NULL AND customer_email != ''
                
                UNION ALL
                
                SELECT 
                    customer_email,
                    customer_name,
                    customer_phone,
                    NULL as property_address,
                    NULL as quote_id,
                    id as appointment_id,
                    service_date as last_activity,
                    price as total_spent,
                    created_at as first_contact
                FROM $appointments_table
                WHERE customer_email IS NOT NULL AND customer_email != ''
            ) AS combined
            GROUP BY customer_email
            HAVING $where_clause
            ORDER BY last_activity DESC
            LIMIT %d OFFSET %d
        ";
        
        // Add pagination parameters
        $query_values = array_merge($where_values, [$per_page, $offset]);
        
        // Get customers
        if (!empty($where_values)) {
            $customers = $wpdb->get_results($wpdb->prepare($query, $query_values), ARRAY_A);
        } else {
            $customers = $wpdb->get_results($wpdb->prepare($query, $per_page, $offset), ARRAY_A);
        }
        
        // Get total count
        $count_query = "
            SELECT COUNT(DISTINCT customer_email) as total
            FROM (
                SELECT customer_email, customer_name, customer_phone, property_address, created_at as last_activity
                FROM $quotes_table
                WHERE customer_email IS NOT NULL AND customer_email != ''
                
                UNION ALL
                
                SELECT customer_email, customer_name, customer_phone, NULL as property_address, service_date as last_activity
                FROM $appointments_table
                WHERE customer_email IS NOT NULL AND customer_email != ''
            ) AS combined
            WHERE $where_clause
        ";
        
        if (!empty($where_values)) {
            $total = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
        } else {
            $total = $wpdb->get_var($count_query);
        }
        
        $total_pages = ceil($total / $per_page);
        
        // Convert to Customer objects
        $customer_objects = [];
        foreach ($customers as $customer_data) {
            // Determine status based on last activity
            $last_activity = $customer_data['last_activity'];
            $status = 'inactive';
            if ($last_activity) {
                $six_months_ago = date('Y-m-d H:i:s', strtotime('-6 months'));
                $status = ($last_activity >= $six_months_ago) ? 'active' : 'inactive';
            }
            $customer_data['status'] = $status;
            
            $customer_objects[] = new self($customer_data);
        }
        
        return [
            'customers' => $customer_objects,
            'total' => $total,
            'pages' => $total_pages,
            'current_page' => $page,
            'per_page' => $per_page
        ];
    }
    
    /**
     * Find customer by email
     * 
     * @param string $email Customer email
     * @return Customer|false Customer object or false if not found
     */
    public static function findByEmail(string $email) {
        global $wpdb;
        
        $quotes_table = $wpdb->prefix . 'pq_quotes';
        $appointments_table = $wpdb->prefix . 'pq_appointments';
        
        $query = "
            SELECT 
                customer_email,
                MAX(customer_name) as customer_name,
                MAX(customer_phone) as customer_phone,
                MAX(property_address) as property_address,
                COUNT(DISTINCT quote_id) as total_quotes,
                COUNT(DISTINCT appointment_id) as total_appointments,
                MAX(last_activity) as last_activity,
                SUM(total_spent) as total_spent,
                MIN(first_contact) as first_contact
            FROM (
                SELECT 
                    customer_email,
                    customer_name,
                    customer_phone,
                    property_address,
                    id as quote_id,
                    NULL as appointment_id,
                    created_at as last_activity,
                    total_price as total_spent,
                    created_at as first_contact
                FROM $quotes_table
                WHERE customer_email = %s
                
                UNION ALL
                
                SELECT 
                    customer_email,
                    customer_name,
                    customer_phone,
                    NULL as property_address,
                    NULL as quote_id,
                    id as appointment_id,
                    service_date as last_activity,
                    price as total_spent,
                    created_at as first_contact
                FROM $appointments_table
                WHERE customer_email = %s
            ) AS combined
            GROUP BY customer_email
        ";
        
        $customer_data = $wpdb->get_row($wpdb->prepare($query, $email, $email), ARRAY_A);
        
        if ($customer_data) {
            // Determine status
            $last_activity = $customer_data['last_activity'];
            $status = 'inactive';
            if ($last_activity) {
                $six_months_ago = date('Y-m-d H:i:s', strtotime('-6 months'));
                $status = ($last_activity >= $six_months_ago) ? 'active' : 'inactive';
            }
            $customer_data['status'] = $status;
            
            return new self($customer_data);
        }
        
        return false;
    }
    
    // Getter methods
    public function getEmail(): string {
        return $this->data['customer_email'] ?? '';
    }
    
    public function getName(): string {
        return $this->data['customer_name'] ?? '';
    }
    
    public function getPhone(): string {
        return $this->data['customer_phone'] ?? '';
    }
    
    public function getAddress(): string {
        return $this->data['property_address'] ?? '';
    }
    
    public function getTotalQuotes(): int {
        return (int) ($this->data['total_quotes'] ?? 0);
    }
    
    public function getTotalAppointments(): int {
        return (int) ($this->data['total_appointments'] ?? 0);
    }
    
    public function getLastActivity(): string {
        return $this->data['last_activity'] ?? '';
    }
    
    public function getTotalSpent(): float {
        return (float) ($this->data['total_spent'] ?? 0);
    }
    
    public function getFirstContact(): string {
        return $this->data['first_contact'] ?? '';
    }
    
    public function getStatus(): string {
        return $this->data['status'] ?? 'inactive';
    }
    
    /**
     * Get customer's quotes
     * 
     * @return array Array of Quote objects
     */
    public function getQuotes(): array {
        return Quote::findByEmail($this->getEmail());
    }
    
    /**
     * Get customer's appointments
     * 
     * @return array Array of Appointment objects
     */
    public function getAppointments(): array {
        return Appointment::findByEmail($this->getEmail());
    }
}
