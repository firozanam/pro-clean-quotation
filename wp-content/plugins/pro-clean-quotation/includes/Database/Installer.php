<?php

namespace ProClean\Quotation\Database;

/**
 * Database Installer Class
 * 
 * @package ProClean\Quotation\Database
 * @since 1.0.0
 */
class Installer {
    
    /**
     * Create database tables
     */
    public static function createTables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Quotes table
        $quotes_table = $wpdb->prefix . 'pq_quotes';
        $quotes_sql = "CREATE TABLE $quotes_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            quote_number VARCHAR(20) UNIQUE NOT NULL,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50),
            property_address TEXT,
            postal_code VARCHAR(20),
            service_type VARCHAR(50) NOT NULL,
            square_meters DECIMAL(10,2) NOT NULL,
            linear_meters DECIMAL(10,2),
            building_height INT,
            property_type VARCHAR(50),
            surface_material VARCHAR(50),
            roof_type VARCHAR(50),
            last_cleaning_date DATE,
            special_requirements TEXT,
            custom_field_data TEXT,
            base_price DECIMAL(10,2) NOT NULL,
            adjustments DECIMAL(10,2) DEFAULT 0,
            subtotal DECIMAL(10,2) NOT NULL,
            tax_amount DECIMAL(10,2) DEFAULT 0,
            total_price DECIMAL(10,2) NOT NULL,
            price_breakdown TEXT,
            status VARCHAR(20) DEFAULT 'new',
            valid_until DATE,
            user_ip VARCHAR(45),
            user_agent TEXT,
            marketing_consent TINYINT(1) DEFAULT 0,
            privacy_consent TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            INDEX idx_email (customer_email),
            INDEX idx_status (status),
            INDEX idx_created (created_at),
            INDEX idx_quote_number (quote_number)
        ) $charset_collate;";
        
        // Bookings table
        $bookings_table = $wpdb->prefix . 'pq_bookings';
        $bookings_sql = "CREATE TABLE $bookings_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            booking_number VARCHAR(20) UNIQUE NOT NULL,
            quote_id BIGINT UNSIGNED,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50),
            property_address TEXT NOT NULL,
            service_type VARCHAR(50) NOT NULL,
            service_date DATE NOT NULL,
            service_time_start TIME NOT NULL,
            service_time_end TIME NOT NULL,
            estimated_duration INT,
            service_details TEXT,
            total_amount DECIMAL(10,2) NOT NULL,
            deposit_amount DECIMAL(10,2) DEFAULT 0,
            deposit_paid TINYINT(1) DEFAULT 0,
            balance_due DECIMAL(10,2),
            payment_status VARCHAR(20) DEFAULT 'pending',
            booking_status VARCHAR(20) DEFAULT 'pending',
            assigned_technician VARCHAR(255),
            admin_notes TEXT,
            customer_notes TEXT,
            cancellation_reason TEXT,
            cancelled_at DATETIME,
            completed_at DATETIME,
            reminder_sent TINYINT(1) DEFAULT 0,
            reminder_sent_at DATETIME,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            FOREIGN KEY (quote_id) REFERENCES $quotes_table(id) ON DELETE SET NULL,
            INDEX idx_service_date (service_date),
            INDEX idx_status (booking_status),
            INDEX idx_email (customer_email),
            INDEX idx_booking_number (booking_number)
        ) $charset_collate;";
        
        // Email logs table
        $email_logs_table = $wpdb->prefix . 'pq_email_logs';
        $email_logs_sql = "CREATE TABLE $email_logs_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reference_type VARCHAR(20) NOT NULL,
            reference_id BIGINT UNSIGNED NOT NULL,
            email_type VARCHAR(50) NOT NULL,
            recipient_email VARCHAR(255) NOT NULL,
            subject VARCHAR(255) NOT NULL,
            sent_at DATETIME NOT NULL,
            status VARCHAR(20) DEFAULT 'sent',
            error_message TEXT,
            opened_at DATETIME,
            clicked_at DATETIME,
            INDEX idx_reference (reference_type, reference_id),
            INDEX idx_sent (sent_at),
            INDEX idx_recipient (recipient_email)
        ) $charset_collate;";
        
        // Settings table
        $settings_table = $wpdb->prefix . 'pq_settings';
        $settings_sql = "CREATE TABLE $settings_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value LONGTEXT,
            setting_type VARCHAR(20) DEFAULT 'string',
            updated_at DATETIME,
            INDEX idx_key (setting_key)
        ) $charset_collate;";
        
        // Services table
        $services_table = $wpdb->prefix . 'pq_services';
        $services_sql = "CREATE TABLE $services_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            duration INT DEFAULT 60,
            price DECIMAL(10,2) DEFAULT 0,
            capacity INT DEFAULT 1,
            buffer_time_before INT DEFAULT 0,
            buffer_time_after INT DEFAULT 0,
            category_id BIGINT UNSIGNED,
            color VARCHAR(7) DEFAULT '#2196F3',
            status VARCHAR(20) DEFAULT 'active',
            sort_order INT DEFAULT 0,
            min_advance_time INT DEFAULT 0,
            max_advance_time INT DEFAULT 0,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            INDEX idx_status (status),
            INDEX idx_category (category_id)
        ) $charset_collate;";
        
        // Employees table
        $employees_table = $wpdb->prefix . 'pq_employees';
        $employees_sql = "CREATE TABLE $employees_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            phone VARCHAR(50),
            description TEXT,
            avatar_url VARCHAR(500),
            status VARCHAR(20) DEFAULT 'active',
            working_hours TEXT,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            INDEX idx_status (status),
            INDEX idx_email (email)
        ) $charset_collate;";
        
        // Employee Services (many-to-many relationship)
        $employee_services_table = $wpdb->prefix . 'pq_employee_services';
        $employee_services_sql = "CREATE TABLE $employee_services_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED NOT NULL,
            service_id BIGINT UNSIGNED NOT NULL,
            created_at DATETIME NOT NULL,
            FOREIGN KEY (employee_id) REFERENCES $employees_table(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES $services_table(id) ON DELETE CASCADE,
            UNIQUE KEY unique_employee_service (employee_id, service_id)
        ) $charset_collate;";
        
        // Appointments table
        $appointments_table = $wpdb->prefix . 'pq_appointments';
        $appointments_sql = "CREATE TABLE $appointments_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            service_id BIGINT UNSIGNED NOT NULL,
            employee_id BIGINT UNSIGNED,
            quote_id BIGINT UNSIGNED,
            customer_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            customer_phone VARCHAR(50),
            service_date DATE NOT NULL,
            service_time_start TIME NOT NULL,
            service_time_end TIME NOT NULL,
            duration INT NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            notes TEXT,
            internal_notes TEXT,
            custom_field_data TEXT,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            FOREIGN KEY (service_id) REFERENCES $services_table(id) ON DELETE RESTRICT,
            FOREIGN KEY (employee_id) REFERENCES $employees_table(id) ON DELETE SET NULL,
            FOREIGN KEY (quote_id) REFERENCES $quotes_table(id) ON DELETE SET NULL,
            INDEX idx_service_date (service_date),
            INDEX idx_status (status),
            INDEX idx_employee (employee_id),
            INDEX idx_service (service_id)
        ) $charset_collate;";
        
        // Appointment Employees table (many-to-many relationship for team assignments)
        $appointment_employees_table = $wpdb->prefix . 'pq_appointment_employees';
        $appointment_employees_sql = "CREATE TABLE $appointment_employees_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            appointment_id BIGINT UNSIGNED NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            role VARCHAR(50) DEFAULT 'technician',
            created_at DATETIME NOT NULL,
            FOREIGN KEY (appointment_id) REFERENCES $appointments_table(id) ON DELETE CASCADE,
            FOREIGN KEY (employee_id) REFERENCES $employees_table(id) ON DELETE CASCADE,
            UNIQUE KEY unique_appointment_employee (appointment_id, employee_id),
            INDEX idx_appointment (appointment_id),
            INDEX idx_employee (employee_id)
        ) $charset_collate;";
        
        // Service Categories table
        $categories_table = $wpdb->prefix . 'pq_service_categories';
        $categories_sql = "CREATE TABLE $categories_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            color VARCHAR(7) DEFAULT '#2196F3',
            sort_order INT DEFAULT 0,
            status VARCHAR(20) DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            INDEX idx_status (status)
        ) $charset_collate;";
        
        // Webhook Logs table
        $webhook_logs_table = $wpdb->prefix . 'pq_webhook_logs';
        $webhook_logs_sql = "CREATE TABLE $webhook_logs_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            webhook_id INT UNSIGNED DEFAULT 0,
            url VARCHAR(500) NOT NULL,
            event VARCHAR(50) NOT NULL,
            payload LONGTEXT,
            attempt TINYINT DEFAULT 1,
            status VARCHAR(20) NOT NULL,
            response_code INT DEFAULT 0,
            response_body TEXT,
            created_at DATETIME NOT NULL,
            INDEX idx_webhook_id (webhook_id),
            INDEX idx_event (event),
            INDEX idx_status (status),
            INDEX idx_created (created_at)
        ) $charset_collate;";
        
        // Availability Overrides table
        $availability_overrides_table = $wpdb->prefix . 'pq_availability_overrides';
        $availability_overrides_sql = "CREATE TABLE $availability_overrides_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT UNSIGNED,
            override_date DATE NOT NULL,
            start_time TIME,
            end_time TIME,
            is_available TINYINT(1) DEFAULT 1,
            reason VARCHAR(255),
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            FOREIGN KEY (employee_id) REFERENCES $employees_table(id) ON DELETE CASCADE,
            INDEX idx_employee (employee_id),
            INDEX idx_date (override_date)
        ) $charset_collate;";
        
        // Service Meta table (for custom fields and other service metadata)
        $service_meta_table = $wpdb->prefix . 'pq_service_meta';
        $service_meta_sql = "CREATE TABLE $service_meta_table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            service_id BIGINT UNSIGNED NOT NULL,
            meta_key VARCHAR(255) NOT NULL,
            meta_value LONGTEXT,
            created_at DATETIME NOT NULL,
            updated_at DATETIME,
            FOREIGN KEY (service_id) REFERENCES $services_table(id) ON DELETE CASCADE,
            INDEX idx_service (service_id),
            INDEX idx_meta_key (meta_key),
            UNIQUE KEY unique_service_meta (service_id, meta_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($quotes_sql);
        dbDelta($bookings_sql);
        dbDelta($email_logs_sql);
        dbDelta($settings_sql);
        dbDelta($services_sql);
        dbDelta($service_meta_sql);
        dbDelta($employees_sql);
        dbDelta($employee_services_sql);
        dbDelta($appointments_sql);
        dbDelta($appointment_employees_sql);
        dbDelta($categories_sql);
        dbDelta($webhook_logs_sql);
        dbDelta($availability_overrides_sql);
        
        // Create default data
        self::createDefaultData();
        
        // Generate dummy data if in development mode
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::generateDummyData();
        }
        
        // Update database version
        update_option('pcq_db_version', PCQ_VERSION);
    }
    
    /**
     * Create default services and employees
     */
    private static function createDefaultData(): void {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'pq_services';
        $employees_table = $wpdb->prefix . 'pq_employees';
        $employee_services_table = $wpdb->prefix . 'pq_employee_services';
        
        // Check if data already exists
        $existing_services = $wpdb->get_var("SELECT COUNT(*) FROM $services_table");
        if ($existing_services > 0) {
            // Create required pages even if services exist
            self::createRequiredPages();
            return; // Data already exists
        }
        
        $current_time = current_time('mysql');
        
        // Create default services
        $default_services = [
            [
                'name' => 'Façade Cleaning',
                'description' => 'Professional façade cleaning service for residential and commercial properties',
                'duration' => 120,
                'price' => 150.00,
                'capacity' => 1,
                'buffer_time_before' => 15,
                'buffer_time_after' => 15,
                'color' => '#2196F3',
                'status' => 'active',
                'sort_order' => 1,
                'created_at' => $current_time
            ],
            [
                'name' => 'Roof Cleaning',
                'description' => 'Professional roof cleaning service with safety equipment',
                'duration' => 180,
                'price' => 200.00,
                'capacity' => 1,
                'buffer_time_before' => 30,
                'buffer_time_after' => 30,
                'color' => '#FF9800',
                'status' => 'active',
                'sort_order' => 2,
                'created_at' => $current_time
            ],
            [
                'name' => 'Complete Cleaning Package',
                'description' => 'Comprehensive façade and roof cleaning service',
                'duration' => 300,
                'price' => 320.00,
                'capacity' => 2,
                'buffer_time_before' => 30,
                'buffer_time_after' => 30,
                'color' => '#9C27B0',
                'status' => 'active',
                'sort_order' => 3,
                'created_at' => $current_time
            ]
        ];
        
        $service_ids = [];
        foreach ($default_services as $service) {
            $wpdb->insert($services_table, $service);
            $service_ids[] = $wpdb->insert_id;
        }
        
        // Create default employee
        $default_working_hours = json_encode([
            'monday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'tuesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'wednesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'thursday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'friday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'saturday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
            'sunday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => false]
        ]);
        
        $default_employee = [
            'name' => 'Cleaning Team',
            'email' => get_option('admin_email'),
            'phone' => '',
            'description' => 'Professional cleaning team available for all services',
            'status' => 'active',
            'working_hours' => $default_working_hours,
            'created_at' => $current_time
        ];
        
        $wpdb->insert($employees_table, $default_employee);
        $employee_id = $wpdb->insert_id;
        
        // Assign employee to all services
        foreach ($service_ids as $service_id) {
            $wpdb->insert($employee_services_table, [
                'employee_id' => $employee_id,
                'service_id' => $service_id,
                'created_at' => $current_time
            ]);
        }
        
        // Create required WordPress pages
        self::createRequiredPages();
    }
    
    /**
     * Create required WordPress pages
     */
    public static function createRequiredPages(): void {
        // Create booking page
        $booking_page_id = get_option('pcq_booking_page_id');
        
        // Check if page still exists
        if (!$booking_page_id || get_post_status($booking_page_id) === false) {
            // Check if a page with 'book-service' slug already exists
            $existing_page = get_page_by_path('book-service');
            if ($existing_page) {
                // Use existing page
                update_option('pcq_booking_page_id', $existing_page->ID);
            } else {
                // Create new booking page
                $booking_page = [
                    'post_title'    => __('Book Service', 'pro-clean-quotation'),
                    'post_content'  => '[pcq_booking_form]',
                    'post_status'   => 'publish',
                    'post_type'     => 'page',
                    'post_name'     => 'book-service',
                    'post_author'   => get_current_user_id() ?: 1,
                    'comment_status' => 'closed',
                    'ping_status'   => 'closed'
                ];
                
                $page_id = wp_insert_post($booking_page);
                
                if ($page_id && !is_wp_error($page_id)) {
                    // Save the page ID in options
                    update_option('pcq_booking_page_id', $page_id);
                }
            }
        }
        
        // Create booking confirmation page
        $confirmation_page_id = get_option('pcq_confirmation_page_id');
        
        // Check if page still exists
        if (!$confirmation_page_id || get_post_status($confirmation_page_id) === false) {
            // Check if a page with 'booking-confirmation' slug already exists
            $existing_confirmation = get_page_by_path('booking-confirmation');
            if ($existing_confirmation) {
                // Use existing page
                update_option('pcq_confirmation_page_id', $existing_confirmation->ID);
            } else {
                // Create new confirmation page
                $confirmation_page = [
                    'post_title'    => __('Booking Confirmation', 'pro-clean-quotation'),
                    'post_content'  => '[pcq_booking_confirmation]',
                    'post_status'   => 'publish',
                    'post_type'     => 'page',
                    'post_name'     => 'booking-confirmation',
                    'post_author'   => get_current_user_id() ?: 1,
                    'comment_status' => 'closed',
                    'ping_status'   => 'closed'
                ];
                
                $page_id = wp_insert_post($confirmation_page);
                
                if ($page_id && !is_wp_error($page_id)) {
                    // Save the page ID in options
                    update_option('pcq_confirmation_page_id', $page_id);
                }
            }
        }
        
        // Flush rewrite rules to ensure pages are accessible
        flush_rewrite_rules();
    }
    
    /**
     * Generate dummy data for development
     */
    private static function generateDummyData(): void {
        // Only generate if we don't have much data already
        global $wpdb;
        $quotes_table = $wpdb->prefix . 'pq_quotes';
        $existing_quotes = $wpdb->get_var("SELECT COUNT(*) FROM $quotes_table");
        
        if ($existing_quotes > 5) {
            return; // Skip if data already exists
        }
        
        // Load and run the dummy data generator
        require_once PCQ_PLUGIN_DIR . 'includes/Database/DummyDataGenerator.php';
        DummyDataGenerator::generateAll();
    }
    
    /**
     * Remove database tables
     */
    public static function removeTables(): void {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'pq_appointment_employees',
            $wpdb->prefix . 'pq_employee_services',
            $wpdb->prefix . 'pq_appointments',
            $wpdb->prefix . 'pq_availability_overrides',
            $wpdb->prefix . 'pq_email_logs',
            $wpdb->prefix . 'pq_bookings',
            $wpdb->prefix . 'pq_employees',
            $wpdb->prefix . 'pq_service_meta',
            $wpdb->prefix . 'pq_services',
            $wpdb->prefix . 'pq_service_categories',
            $wpdb->prefix . 'pq_webhook_logs',
            $wpdb->prefix . 'pq_quotes',
            $wpdb->prefix . 'pq_settings'
        ];
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
        
        delete_option('pcq_db_version');
    }
    
    /**
     * Force recreate tables (for development)
     */
    public static function forceRecreate(): void {
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            return;
        }
        
        // Drop and recreate tables
        self::removeTables();
        self::createTables();
    }
    
    /**
     * Check if database needs update
     */
    public static function needsUpdate(): bool {
        $current_version = get_option('pcq_db_version', '0.0.0');
        return version_compare($current_version, PCQ_VERSION, '<');
    }
    
    /**
     * Update database if needed
     */
    public static function maybeUpdate(): void {
        if (self::needsUpdate()) {
            self::createTables();
            self::migrateEmployeeAssignments();
        }
    }
    
    /**
     * Migrate single employee assignments to team assignments
     * This ensures backward compatibility when upgrading to team system
     */
    public static function migrateEmployeeAssignments(): void {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'pq_appointments';
        $junction_table = $wpdb->prefix . 'pq_appointment_employees';
        
        // Check if junction table exists
        $table_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                DB_NAME,
                $junction_table
            )
        );
        
        if (!$table_exists) {
            return; // Table doesn't exist yet, skip migration
        }
        
        // Get all appointments with employee_id set
        $appointments = $wpdb->get_results(
            "SELECT id, employee_id FROM $appointments_table WHERE employee_id IS NOT NULL AND employee_id > 0"
        );
        
        if (empty($appointments)) {
            return; // No data to migrate
        }
        
        $current_time = current_time('mysql');
        $migrated = 0;
        
        foreach ($appointments as $appointment) {
            // Check if already migrated
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $junction_table WHERE appointment_id = %d",
                $appointment->id
            ));
            
            if ($exists > 0) {
                continue; // Already migrated
            }
            
            // Insert into junction table
            $result = $wpdb->insert($junction_table, [
                'appointment_id' => $appointment->id,
                'employee_id' => $appointment->employee_id,
                'role' => 'technician',
                'created_at' => $current_time
            ]);
            
            if ($result !== false) {
                $migrated++;
            }
        }
        
        if ($migrated > 0) {
            error_log("Pro Clean Quotation: Successfully migrated {$migrated} employee assignments to team system");
        }
    }
}