<?php

namespace ProClean\Quotation\Database;

/**
 * Dummy Data Generator Class
 * 
 * @package ProClean\Quotation\Database
 * @since 1.0.0
 */
class DummyDataGenerator {
    
    /**
     * Generate all dummy data
     */
    public static function generateAll(): void {
        global $wpdb;
        
        // Check if dummy data already exists
        $quotes_table = $wpdb->prefix . 'pq_quotes';
        $existing_quotes = $wpdb->get_var("SELECT COUNT(*) FROM $quotes_table");
        
        if ($existing_quotes > 5) {
            return; // Dummy data already exists
        }
        
        echo "Generating dummy data...\n";
        
        // Generate in order due to foreign key dependencies
        self::generateServices();
        self::generateEmployees();
        self::generateEmployeeServices();
        self::generateQuotes();
        self::generateAppointments();
        self::generateBookings();
        self::generateEmailLogs();
        self::generateSettings();
        
        echo "Dummy data generation completed!\n";
    }
    
    /**
     * Generate dummy services
     */
    private static function generateServices(): void {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'pq_services';
        $current_time = current_time('mysql');
        
        $services = [
            [
                'name' => 'Façade Cleaning - Basic',
                'description' => 'Standard façade cleaning service for residential properties. Includes pressure washing and basic cleaning solutions.',
                'duration' => 120,
                'price' => 150.00,
                'capacity' => 1,
                'buffer_time_before' => 15,
                'buffer_time_after' => 15,
                'color' => '#2196F3',
                'status' => 'active',
                'sort_order' => 1,
                'min_advance_time' => 24,
                'max_advance_time' => 30,
                'created_at' => $current_time
            ],
            [
                'name' => 'Façade Cleaning - Premium',
                'description' => 'Premium façade cleaning with specialized equipment and eco-friendly solutions. Includes detailed cleaning and protective coating.',
                'duration' => 180,
                'price' => 250.00,
                'capacity' => 2,
                'buffer_time_before' => 30,
                'buffer_time_after' => 30,
                'color' => '#4CAF50',
                'status' => 'active',
                'sort_order' => 2,
                'min_advance_time' => 48,
                'max_advance_time' => 45,
                'created_at' => $current_time
            ],
            [
                'name' => 'Roof Cleaning - Standard',
                'description' => 'Professional roof cleaning service with safety equipment. Removes moss, algae, and debris.',
                'duration' => 240,
                'price' => 300.00,
                'capacity' => 2,
                'buffer_time_before' => 30,
                'buffer_time_after' => 30,
                'color' => '#FF9800',
                'status' => 'active',
                'sort_order' => 3,
                'min_advance_time' => 48,
                'max_advance_time' => 60,
                'created_at' => $current_time
            ],
            [
                'name' => 'Roof Cleaning - Deep Clean',
                'description' => 'Comprehensive roof cleaning with treatment and protective coating. Includes gutter cleaning and minor repairs.',
                'duration' => 360,
                'price' => 450.00,
                'capacity' => 3,
                'buffer_time_before' => 45,
                'buffer_time_after' => 45,
                'color' => '#9C27B0',
                'status' => 'active',
                'sort_order' => 4,
                'min_advance_time' => 72,
                'max_advance_time' => 90,
                'created_at' => $current_time
            ],
            [
                'name' => 'Complete Cleaning Package',
                'description' => 'Full exterior cleaning package including façade, roof, gutters, and windows. Best value for complete property maintenance.',
                'duration' => 480,
                'price' => 650.00,
                'capacity' => 3,
                'buffer_time_before' => 60,
                'buffer_time_after' => 60,
                'color' => '#F44336',
                'status' => 'active',
                'sort_order' => 5,
                'min_advance_time' => 72,
                'max_advance_time' => 120,
                'created_at' => $current_time
            ],
            [
                'name' => 'Window Cleaning - Residential',
                'description' => 'Professional window cleaning for residential properties. Interior and exterior cleaning included.',
                'duration' => 90,
                'price' => 80.00,
                'capacity' => 1,
                'buffer_time_before' => 15,
                'buffer_time_after' => 15,
                'color' => '#00BCD4',
                'status' => 'active',
                'sort_order' => 6,
                'min_advance_time' => 12,
                'max_advance_time' => 14,
                'created_at' => $current_time
            ],
            [
                'name' => 'Emergency Cleaning Service',
                'description' => 'Emergency cleaning service for urgent situations. Available with short notice for immediate cleaning needs.',
                'duration' => 120,
                'price' => 200.00,
                'capacity' => 2,
                'buffer_time_before' => 0,
                'buffer_time_after' => 15,
                'color' => '#FF5722',
                'status' => 'active',
                'sort_order' => 7,
                'min_advance_time' => 2,
                'max_advance_time' => 7,
                'created_at' => $current_time
            ]
        ];
        
        foreach ($services as $service) {
            $wpdb->insert($services_table, $service);
        }
        
        echo "Generated " . count($services) . " services\n";
    }
    
    /**
     * Generate dummy employees
     */
    private static function generateEmployees(): void {
        global $wpdb;
        
        $employees_table = $wpdb->prefix . 'pq_employees';
        $current_time = current_time('mysql');
        
        $default_working_hours = json_encode([
            'monday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'tuesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'wednesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'thursday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'friday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'saturday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
            'sunday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => false]
        ]);
        
        $weekend_working_hours = json_encode([
            'monday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'tuesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'wednesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'thursday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'friday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
            'saturday' => ['start' => '08:00', 'end' => '16:00', 'enabled' => true],
            'sunday' => ['start' => '10:00', 'end' => '14:00', 'enabled' => true]
        ]);
        
        $part_time_hours = json_encode([
            'monday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
            'tuesday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
            'wednesday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
            'thursday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
            'friday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
            'saturday' => ['start' => '09:00', 'end' => '13:00', 'enabled' => false],
            'sunday' => ['start' => '09:00', 'end' => '13:00', 'enabled' => false]
        ]);
        
        $employees = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@proclean.com',
                'phone' => '+1 (555) 123-4567',
                'description' => 'Senior cleaning technician with 8 years of experience. Specializes in façade cleaning and high-rise work.',
                'status' => 'active',
                'working_hours' => $default_working_hours,
                'created_at' => $current_time
            ],
            [
                'name' => 'Maria Garcia',
                'email' => 'maria.garcia@proclean.com',
                'phone' => '+1 (555) 234-5678',
                'description' => 'Expert roof cleaning specialist with safety certifications. Team leader for complex projects.',
                'status' => 'active',
                'working_hours' => $weekend_working_hours,
                'created_at' => $current_time
            ],
            [
                'name' => 'David Johnson',
                'email' => 'david.johnson@proclean.com',
                'phone' => '+1 (555) 345-6789',
                'description' => 'Window cleaning expert and equipment maintenance specialist. Available for emergency services.',
                'status' => 'active',
                'working_hours' => $default_working_hours,
                'created_at' => $current_time
            ],
            [
                'name' => 'Sarah Wilson',
                'email' => 'sarah.wilson@proclean.com',
                'phone' => '+1 (555) 456-7890',
                'description' => 'Part-time cleaning technician specializing in residential properties and detailed work.',
                'status' => 'active',
                'working_hours' => $part_time_hours,
                'created_at' => $current_time
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael.brown@proclean.com',
                'phone' => '+1 (555) 567-8901',
                'description' => 'Commercial cleaning specialist with experience in large-scale projects and industrial equipment.',
                'status' => 'active',
                'working_hours' => $weekend_working_hours,
                'created_at' => $current_time
            ],
            [
                'name' => 'Lisa Anderson',
                'email' => 'lisa.anderson@proclean.com',
                'phone' => '+1 (555) 678-9012',
                'description' => 'Quality control supervisor and customer service specialist. Handles premium service appointments.',
                'status' => 'inactive',
                'working_hours' => $default_working_hours,
                'created_at' => $current_time
            ]
        ];
        
        foreach ($employees as $employee) {
            $wpdb->insert($employees_table, $employee);
        }
        
        echo "Generated " . count($employees) . " employees\n";
    }
    
    /**
     * Generate employee-service relationships
     */
    private static function generateEmployeeServices(): void {
        global $wpdb;
        
        $employee_services_table = $wpdb->prefix . 'pq_employee_services';
        $current_time = current_time('mysql');
        
        // Get all service and employee IDs
        $services = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}pq_services ORDER BY id");
        $employees = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}pq_employees ORDER BY id");
        
        $assignments = [
            // John Smith - Façade specialist
            [1, [1, 2, 5, 6]], // Basic façade, Premium façade, Complete package, Windows
            // Maria Garcia - Roof specialist  
            [2, [3, 4, 5]], // Standard roof, Deep roof, Complete package
            // David Johnson - Window and emergency specialist
            [3, [6, 7, 1]], // Windows, Emergency, Basic façade
            // Sarah Wilson - Residential specialist
            [4, [1, 6]], // Basic façade, Windows
            // Michael Brown - All services (senior)
            [5, [1, 2, 3, 4, 5, 6, 7]], // All services
            // Lisa Anderson - Premium services only
            [6, [2, 4, 5]] // Premium façade, Deep roof, Complete package
        ];
        
        foreach ($assignments as [$employee_idx, $service_indices]) {
            if (isset($employees[$employee_idx - 1])) {
                $employee_id = $employees[$employee_idx - 1]->id;
                
                foreach ($service_indices as $service_idx) {
                    if (isset($services[$service_idx - 1])) {
                        $service_id = $services[$service_idx - 1]->id;
                        
                        $wpdb->insert($employee_services_table, [
                            'employee_id' => $employee_id,
                            'service_id' => $service_id,
                            'created_at' => $current_time
                        ]);
                    }
                }
            }
        }
        
        echo "Generated employee-service assignments\n";
    }
    
    /**
     * Generate dummy quotes
     */
    private static function generateQuotes(): void {
        global $wpdb;
        
        $quotes_table = $wpdb->prefix . 'pq_quotes';
        $current_time = current_time('mysql');
        
        $customers = [
            ['Robert Johnson', 'robert.johnson@email.com', '+1 (555) 111-2222'],
            ['Emily Davis', 'emily.davis@email.com', '+1 (555) 222-3333'],
            ['James Wilson', 'james.wilson@email.com', '+1 (555) 333-4444'],
            ['Jennifer Brown', 'jennifer.brown@email.com', '+1 (555) 444-5555'],
            ['William Jones', 'william.jones@email.com', '+1 (555) 555-6666'],
            ['Jessica Miller', 'jessica.miller@email.com', '+1 (555) 666-7777'],
            ['Christopher Davis', 'christopher.davis@email.com', '+1 (555) 777-8888'],
            ['Amanda Wilson', 'amanda.wilson@email.com', '+1 (555) 888-9999'],
            ['Daniel Anderson', 'daniel.anderson@email.com', '+1 (555) 999-0000'],
            ['Michelle Taylor', 'michelle.taylor@email.com', '+1 (555) 000-1111']
        ];
        
        $addresses = [
            '123 Main Street, Downtown',
            '456 Oak Avenue, Riverside',
            '789 Pine Road, Hillside',
            '321 Elm Street, Westside',
            '654 Maple Drive, Eastside',
            '987 Cedar Lane, Northside',
            '147 Birch Street, Southside',
            '258 Walnut Avenue, Central',
            '369 Cherry Road, Uptown',
            '741 Spruce Drive, Midtown'
        ];
        
        $service_types = ['facade_cleaning', 'roof_cleaning', 'complete_package', 'window_cleaning'];
        $statuses = ['new', 'reviewed', 'approved', 'converted', 'expired'];
        
        for ($i = 0; $i < 25; $i++) {
            $customer = $customers[array_rand($customers)];
            $address = $addresses[array_rand($addresses)];
            $service_type = $service_types[array_rand($service_types)];
            $status = $statuses[array_rand($statuses)];
            
            $square_meters = rand(50, 500);
            $linear_meters = rand(20, 100);
            $building_height = rand(1, 4);
            
            // Calculate pricing based on service type
            $base_price = match($service_type) {
                'facade_cleaning' => $square_meters * 2.5,
                'roof_cleaning' => $square_meters * 3.0,
                'complete_package' => $square_meters * 4.5,
                'window_cleaning' => $linear_meters * 8.0,
                default => $square_meters * 2.5
            };
            
            $adjustments = rand(-50, 100);
            $subtotal = $base_price + $adjustments;
            $tax_amount = $subtotal * 0.21; // 21% VAT
            $total_price = $subtotal + $tax_amount;
            
            $created_date = date('Y-m-d H:i:s', strtotime("-" . rand(1, 90) . " days"));
            $valid_until = date('Y-m-d', strtotime($created_date . " +30 days"));
            
            $quote_data = [
                'quote_number' => 'QT-' . date('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'customer_name' => $customer[0],
                'customer_email' => $customer[1],
                'customer_phone' => $customer[2],
                'property_address' => $address,
                'postal_code' => rand(10000, 99999),
                'service_type' => $service_type,
                'square_meters' => $square_meters,
                'linear_meters' => $linear_meters,
                'building_height' => $building_height,
                'property_type' => ['residential', 'commercial', 'industrial'][rand(0, 2)],
                'surface_material' => ['brick', 'concrete', 'wood', 'metal', 'glass'][rand(0, 4)],
                'roof_type' => ['tile', 'metal', 'shingle', 'flat'][rand(0, 3)],
                'last_cleaning_date' => date('Y-m-d', strtotime("-" . rand(30, 365) . " days")),
                'special_requirements' => rand(0, 1) ? 'High-pressure cleaning required for stubborn stains' : null,
                'base_price' => round($base_price, 2),
                'adjustments' => round($adjustments, 2),
                'subtotal' => round($subtotal, 2),
                'tax_amount' => round($tax_amount, 2),
                'total_price' => round($total_price, 2),
                'price_breakdown' => json_encode([
                    'base_rate' => round($base_price / ($service_type === 'window_cleaning' ? $linear_meters : $square_meters), 2),
                    'area_multiplier' => $service_type === 'window_cleaning' ? $linear_meters : $square_meters,
                    'difficulty_adjustment' => $adjustments,
                    'tax_rate' => 0.21
                ]),
                'status' => $status,
                'valid_until' => $valid_until,
                'user_ip' => '192.168.1.' . rand(1, 254),
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'marketing_consent' => rand(0, 1),
                'privacy_consent' => 1,
                'created_at' => $created_date,
                'updated_at' => rand(0, 1) ? date('Y-m-d H:i:s', strtotime($created_date . " +" . rand(1, 10) . " days")) : null
            ];
            
            $wpdb->insert($quotes_table, $quote_data);
        }
        
        echo "Generated 25 quotes\n";
    }
    
    /**
     * Generate dummy appointments
     */
    private static function generateAppointments(): void {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'pq_appointments';
        $current_time = current_time('mysql');
        
        // Get service and employee IDs
        $services = $wpdb->get_results("SELECT id, name, duration, price FROM {$wpdb->prefix}pq_services ORDER BY id");
        $employees = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}pq_employees WHERE status = 'active' ORDER BY id");
        $quotes = $wpdb->get_results("SELECT id FROM {$wpdb->prefix}pq_quotes ORDER BY id LIMIT 10");
        
        $customers = [
            ['Alice Cooper', 'alice.cooper@email.com', '+1 (555) 111-1111'],
            ['Bob Martin', 'bob.martin@email.com', '+1 (555) 222-2222'],
            ['Carol White', 'carol.white@email.com', '+1 (555) 333-3333'],
            ['David Lee', 'david.lee@email.com', '+1 (555) 444-4444'],
            ['Eva Green', 'eva.green@email.com', '+1 (555) 555-5555'],
            ['Frank Miller', 'frank.miller@email.com', '+1 (555) 666-6666'],
            ['Grace Hall', 'grace.hall@email.com', '+1 (555) 777-7777'],
            ['Henry Clark', 'henry.clark@email.com', '+1 (555) 888-8888'],
            ['Iris Young', 'iris.young@email.com', '+1 (555) 999-9999'],
            ['Jack Turner', 'jack.turner@email.com', '+1 (555) 000-0000']
        ];
        
        $statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'];
        $appointments_created = 0;
        
        // Generate appointments for January 2026 (weekdays only, with available slots)
        $start_date = strtotime('2026-01-02'); // First weekday of January 2026
        $end_date = strtotime('2026-01-31');
        
        $time_slots = [
            ['start' => '09:00:00', 'duration' => 120],
            ['start' => '11:30:00', 'duration' => 120],
            ['start' => '14:00:00', 'duration' => 120]
        ];
        
        $current_date = $start_date;
        while ($current_date <= $end_date) {
            $day_of_week = date('N', $current_date); // 1 (Monday) - 7 (Sunday)
            
            // Skip Sundays (7) and some Saturdays for realism
            if ($day_of_week == 7 || ($day_of_week == 6 && rand(0, 1))) {
                $current_date = strtotime('+1 day', $current_date);
                continue;
            }
            
            $service_date = date('Y-m-d', $current_date);
            
            // Create 1-2 appointments per day (leaving slots available for testing)
            $appointments_per_day = rand(1, 2);
            $used_slots = [];
            
            for ($i = 0; $i < $appointments_per_day; $i++) {
                // Select a time slot that hasn't been used today
                $available_slots = array_diff(array_keys($time_slots), $used_slots);
                if (empty($available_slots)) break;
                
                $slot_index = array_rand(array_flip($available_slots));
                $used_slots[] = $slot_index;
                
                $time_slot = $time_slots[$slot_index];
                $service = $services[array_rand($services)];
                $employee = $employees[array_rand($employees)];
                $customer = $customers[array_rand($customers)];
                
                $service_time_start = $time_slot['start'];
                $duration_minutes = $service->duration;
                $end_time = strtotime($service_time_start) + ($duration_minutes * 60);
                $service_time_end = date('H:i:s', $end_time);
                
                // Determine status based on date
                $days_from_now = round(($current_date - time()) / 86400);
                if ($days_from_now < -7) {
                    $status = ['completed', 'cancelled'][rand(0, 1)];
                } elseif ($days_from_now < 0) {
                    $status = 'completed';
                } elseif ($days_from_now <= 7) {
                    $status = ['confirmed', 'pending'][rand(0, 1)];
                } else {
                    $status = 'pending';
                }
                
                $price_variation = rand(-20, 50);
                $final_price = $service->price + $price_variation;
                
                $appointment_data = [
                    'service_id' => $service->id,
                    'employee_id' => $employee->id,
                    'quote_id' => rand(0, 1) && !empty($quotes) ? $quotes[array_rand($quotes)]->id : null,
                    'customer_name' => $customer[0],
                    'customer_email' => $customer[1],
                    'customer_phone' => $customer[2],
                    'service_date' => $service_date,
                    'service_time_start' => $service_time_start,
                    'service_time_end' => $service_time_end,
                    'duration' => $duration_minutes,
                    'price' => round($final_price, 2),
                    'status' => $status,
                    'notes' => rand(0, 1) ? 'Customer requested early morning appointment. Property has easy access.' : null,
                    'internal_notes' => rand(0, 1) ? 'Check equipment before arrival. Customer is repeat client.' : null,
                    'created_at' => date('Y-m-d H:i:s', strtotime("-" . rand(1, 30) . " days")),
                    'updated_at' => rand(0, 1) ? $current_time : null
                ];
                
                $wpdb->insert($appointments_table, $appointment_data);
                $appointments_created++;
            }
            
            $current_date = strtotime('+1 day', $current_date);
        }
        
        // Generate some additional historical appointments (past 2 months)
        for ($i = 0; $i < 15; $i++) {
            $service = $services[array_rand($services)];
            $employee = $employees[array_rand($employees)];
            $customer = $customers[array_rand($customers)];
            
            $days_offset = rand(-60, -1);
            $service_date = date('Y-m-d', strtotime("+$days_offset days"));
            
            $start_hour = rand(8, 16);
            $start_minute = [0, 15, 30, 45][rand(0, 3)];
            $service_time_start = sprintf('%02d:%02d:00', $start_hour, $start_minute);
            
            $duration_minutes = $service->duration;
            $end_time = strtotime($service_time_start) + ($duration_minutes * 60);
            $service_time_end = date('H:i:s', $end_time);
            
            $status = ['completed', 'cancelled', 'no_show'][rand(0, 2)];
            
            $price_variation = rand(-20, 50);
            $final_price = $service->price + $price_variation;
            
            $appointment_data = [
                'service_id' => $service->id,
                'employee_id' => $employee->id,
                'quote_id' => rand(0, 1) && !empty($quotes) ? $quotes[array_rand($quotes)]->id : null,
                'customer_name' => $customer[0],
                'customer_email' => $customer[1],
                'customer_phone' => $customer[2],
                'service_date' => $service_date,
                'service_time_start' => $service_time_start,
                'service_time_end' => $service_time_end,
                'duration' => $duration_minutes,
                'price' => round($final_price, 2),
                'status' => $status,
                'notes' => rand(0, 1) ? 'Customer requested early morning appointment.' : null,
                'internal_notes' => rand(0, 1) ? 'Check equipment before arrival.' : null,
                'created_at' => date('Y-m-d H:i:s', strtotime("+$days_offset days")),
                'updated_at' => rand(0, 1) ? $current_time : null
            ];
            
            $wpdb->insert($appointments_table, $appointment_data);
            $appointments_created++;
        }
        
        echo "Generated $appointments_created appointments (January 2026 + historical)\n";
    }
    
    /**
     * Generate dummy bookings
     */
    private static function generateBookings(): void {
        global $wpdb;
        
        $bookings_table = $wpdb->prefix . 'pq_bookings';
        $current_time = current_time('mysql');
        
        // Get some quotes to link bookings to
        $quotes = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}pq_quotes WHERE status IN ('approved', 'converted') LIMIT 15");
        
        $service_types = ['facade_cleaning', 'roof_cleaning', 'complete_package', 'window_cleaning'];
        $payment_statuses = ['pending', 'deposit_paid', 'paid', 'overdue'];
        $booking_statuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];
        
        $technicians = ['John Smith', 'Maria Garcia', 'David Johnson', 'Michael Brown'];
        
        for ($i = 0; $i < 20; $i++) {
            $quote = !empty($quotes) ? $quotes[array_rand($quotes)] : null;
            
            if ($quote) {
                $customer_name = $quote->customer_name;
                $customer_email = $quote->customer_email;
                $customer_phone = $quote->customer_phone;
                $service_type = $quote->service_type;
                $total_amount = $quote->total_price;
            } else {
                $customer_name = 'Direct Customer ' . ($i + 1);
                $customer_email = 'customer' . ($i + 1) . '@email.com';
                $customer_phone = '+1 (555) ' . rand(100, 999) . '-' . rand(1000, 9999);
                $service_type = $service_types[array_rand($service_types)];
                $total_amount = rand(100, 800);
            }
            
            $days_offset = rand(-30, 30);
            $service_date = date('Y-m-d', strtotime("+$days_offset days"));
            
            $start_hour = rand(8, 16);
            $service_time_start = sprintf('%02d:00:00', $start_hour);
            $service_time_end = sprintf('%02d:00:00', $start_hour + rand(2, 6));
            
            $deposit_amount = round($total_amount * 0.3, 2);
            $balance_due = $total_amount - $deposit_amount;
            
            $payment_status = $payment_statuses[array_rand($payment_statuses)];
            $booking_status = $booking_statuses[array_rand($booking_statuses)];
            
            $booking_data = [
                'booking_number' => 'BK-' . date('Y') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'quote_id' => $quote ? $quote->id : null,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone,
                'property_address' => '123 Service Street, City',
                'service_type' => $service_type,
                'service_date' => $service_date,
                'service_time_start' => $service_time_start,
                'service_time_end' => $service_time_end,
                'estimated_duration' => rand(120, 480),
                'service_details' => 'Professional cleaning service as per quote specifications.',
                'total_amount' => round($total_amount, 2),
                'deposit_amount' => $deposit_amount,
                'deposit_paid' => in_array($payment_status, ['deposit_paid', 'paid']) ? 1 : 0,
                'balance_due' => $payment_status === 'paid' ? 0 : $balance_due,
                'payment_status' => $payment_status,
                'booking_status' => $booking_status,
                'assigned_technician' => $technicians[array_rand($technicians)],
                'admin_notes' => rand(0, 1) ? 'Customer prefers morning appointments. Has parking available.' : null,
                'customer_notes' => rand(0, 1) ? 'Please call before arrival. Dog on property.' : null,
                'cancellation_reason' => $booking_status === 'cancelled' ? 'Customer rescheduled due to weather concerns' : null,
                'cancelled_at' => $booking_status === 'cancelled' ? date('Y-m-d H:i:s', strtotime("-" . rand(1, 10) . " days")) : null,
                'completed_at' => $booking_status === 'completed' ? date('Y-m-d H:i:s', strtotime("-" . rand(1, 5) . " days")) : null,
                'reminder_sent' => rand(0, 1),
                'created_at' => date('Y-m-d H:i:s', strtotime("-" . rand(1, 60) . " days")),
                'updated_at' => rand(0, 1) ? $current_time : null
            ];
            
            $wpdb->insert($bookings_table, $booking_data);
        }
        
        echo "Generated 20 bookings\n";
    }
    
    /**
     * Generate dummy email logs
     */
    private static function generateEmailLogs(): void {
        global $wpdb;
        
        $email_logs_table = $wpdb->prefix . 'pq_email_logs';
        
        $email_types = [
            'quote_confirmation',
            'appointment_confirmation', 
            'appointment_reminder',
            'appointment_cancelled',
            'appointment_rescheduled',
            'admin_notification'
        ];
        
        $statuses = ['sent', 'failed', 'pending'];
        $status_weights = [80, 15, 5]; // Most emails sent successfully
        
        $subjects = [
            'quote_confirmation' => 'Your Cleaning Quote #QT-{number}',
            'appointment_confirmation' => 'Appointment Confirmed - {service}',
            'appointment_reminder' => 'Reminder: Your appointment tomorrow',
            'appointment_cancelled' => 'Appointment Cancellation Notice',
            'appointment_rescheduled' => 'Appointment Rescheduled - New Date',
            'admin_notification' => 'New Appointment Booking - Action Required'
        ];
        
        $recipients = [
            'john.doe@email.com',
            'jane.smith@email.com', 
            'mike.johnson@email.com',
            'sarah.wilson@email.com',
            'admin@proclean.com',
            'manager@proclean.com'
        ];
        
        $error_messages = [
            'SMTP connection failed',
            'Invalid email address format',
            'Recipient mailbox full',
            'Message rejected by spam filter',
            'Temporary server error - retry later'
        ];
        
        for ($i = 0; $i < 50; $i++) {
            $email_type = $email_types[array_rand($email_types)];
            $status = $statuses[array_rand($statuses)];
            $recipient = $recipients[array_rand($recipients)];
            
            $sent_date = date('Y-m-d H:i:s', strtotime("-" . rand(1, 90) . " days"));
            
            $subject = str_replace(
                ['{number}', '{service}'],
                [rand(1000, 9999), 'Façade Cleaning Service'],
                $subjects[$email_type]
            );
            
            $email_data = [
                'reference_type' => in_array($email_type, ['quote_confirmation']) ? 'quote' : 'appointment',
                'reference_id' => rand(1, 30),
                'email_type' => $email_type,
                'recipient_email' => $recipient,
                'subject' => $subject,
                'sent_at' => $sent_date,
                'status' => $status,
                'error_message' => $status === 'failed' ? $error_messages[array_rand($error_messages)] : null,
                'opened_at' => ($status === 'sent' && rand(0, 100) < 60) ? date('Y-m-d H:i:s', strtotime($sent_date . " +" . rand(1, 48) . " hours")) : null,
                'clicked_at' => ($status === 'sent' && rand(0, 100) < 25) ? date('Y-m-d H:i:s', strtotime($sent_date . " +" . rand(2, 72) . " hours")) : null
            ];
            
            $wpdb->insert($email_logs_table, $email_data);
        }
        
        echo "Generated 50 email logs\n";
    }
    
    /**
     * Generate dummy settings
     */
    private static function generateSettings(): void {
        global $wpdb;
        
        $settings_table = $wpdb->prefix . 'pq_settings';
        $current_time = current_time('mysql');
        
        $settings = [
            [
                'setting_key' => 'business_name',
                'setting_value' => 'Pro Clean Services Ltd.',
                'setting_type' => 'string',
                'updated_at' => $current_time
            ],
            [
                'setting_key' => 'business_email',
                'setting_value' => 'info@procleanservices.com',
                'setting_type' => 'string',
                'updated_at' => $current_time
            ],
            [
                'setting_key' => 'business_phone',
                'setting_value' => '+1 (555) 123-CLEAN',
                'setting_type' => 'string',
                'updated_at' => $current_time
            ],
            [
                'setting_key' => 'default_tax_rate',
                'setting_value' => '0.21',
                'setting_type' => 'float',
                'updated_at' => $current_time
            ],
            [
                'setting_key' => 'quote_validity_days',
                'setting_value' => '30',
                'setting_type' => 'integer',
                'updated_at' => $current_time
            ],
            [
                'setting_key' => 'email_notifications_enabled',
                'setting_value' => '1',
                'setting_type' => 'boolean',
                'updated_at' => $current_time
            ],
            [
                'setting_key' => 'working_hours',
                'setting_value' => json_encode([
                    'monday' => ['start' => '08:00', 'end' => '18:00'],
                    'tuesday' => ['start' => '08:00', 'end' => '18:00'],
                    'wednesday' => ['start' => '08:00', 'end' => '18:00'],
                    'thursday' => ['start' => '08:00', 'end' => '18:00'],
                    'friday' => ['start' => '08:00', 'end' => '18:00'],
                    'saturday' => ['start' => '09:00', 'end' => '15:00'],
                    'sunday' => ['closed' => true]
                ]),
                'setting_type' => 'json',
                'updated_at' => $current_time
            ]
        ];
        
        foreach ($settings as $setting) {
            $wpdb->insert($settings_table, $setting);
        }
        
        echo "Generated " . count($settings) . " settings\n";
    }
    
    /**
     * Clear all dummy data
     */
    public static function clearAll(): void {
        global $wpdb;
        
        $tables = [
            'pq_employee_services',
            'pq_appointments', 
            'pq_email_logs',
            'pq_bookings',
            'pq_employees',
            'pq_services',
            'pq_quotes',
            'pq_settings'
        ];
        
        foreach ($tables as $table) {
            $full_table = $wpdb->prefix . $table;
            $wpdb->query("DELETE FROM $full_table");
            $wpdb->query("ALTER TABLE $full_table AUTO_INCREMENT = 1");
        }
        
        echo "Cleared all dummy data\n";
    }
    
    /**
     * Clear all data except services and service categories
     * Preserves service configuration while removing transactional data
     */
    public static function clearAllExceptServices(): void {
        global $wpdb;
        
        // Tables to clear (excluding services and service categories)
        $tables_to_clear = [
            'pq_employee_services',
            'pq_appointments', 
            'pq_email_logs',
            'pq_bookings',
            'pq_employees',
            'pq_quotes',
            'pq_settings'
        ];
        
        foreach ($tables_to_clear as $table) {
            $full_table = $wpdb->prefix . $table;
            $wpdb->query("DELETE FROM $full_table");
            $wpdb->query("ALTER TABLE $full_table AUTO_INCREMENT = 1");
        }
        
        // Note: pq_services and pq_service_categories are preserved
        // pq_service_meta is also cleared as it depends on specific service instances
        
        echo "Cleared all data except services and service categories\n";
    }
}