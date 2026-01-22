<?php

namespace ProClean\Quotation\Database;

use ProClean\Quotation\Models\Service;

/**
 * Custom Fields Migration
 * 
 * Migrates existing roof_type data to the new custom fields system
 * 
 * @package ProClean\Quotation\Database
 * @since 1.0.0
 */
class CustomFieldsMigration {
    
    /**
     * Run migration
     * 
     * @return array Migration result with stats
     */
    public static function migrate(): array {
        global $wpdb;
        
        $stats = [
            'services_migrated' => 0,
            'quotes_migrated' => 0,
            'appointments_migrated' => 0,
            'errors' => []
        ];
        
        try {
            // Step 1: Create "Roof Type" custom field for roof cleaning services
            $stats['services_migrated'] = self::migrateRoofCleaningService();
            
            // Step 2: Migrate existing quote data
            $stats['quotes_migrated'] = self::migrateQuotes();
            
            // Step 3: Migrate existing appointment data
            $stats['appointments_migrated'] = self::migrateAppointments();
            
        } catch (\Exception $e) {
            $stats['errors'][] = $e->getMessage();
        }
        
        return $stats;
    }
    
    /**
     * Add Roof Type custom field to roof cleaning services
     * 
     * @return int Number of services updated
     */
    private static function migrateRoofCleaningService(): int {
        global $wpdb;
        
        $services_table = $wpdb->prefix . 'pq_services';
        $migrated = 0;
        
        // Find services that have "roof" in their name or ID
        $roof_services = $wpdb->get_results(
            "SELECT id FROM $services_table 
             WHERE name LIKE '%roof%' 
             OR name LIKE '%Roof%' 
             OR id IN (SELECT DISTINCT service_type FROM {$wpdb->prefix}pq_quotes WHERE roof_type IS NOT NULL AND roof_type != '')",
            ARRAY_A
        );
        
        foreach ($roof_services as $service_data) {
            $service = new Service($service_data['id']);
            
            // Check if custom fields already exist
            $existing_fields = $service->getCustomFields();
            $has_roof_type = false;
            
            foreach ($existing_fields as $field) {
                if (($field['id'] ?? '') === 'roof_type') {
                    $has_roof_type = true;
                    break;
                }
            }
            
            // Add Roof Type field if it doesn't exist
            if (!$has_roof_type) {
                $custom_fields = [
                    [
                        'id' => 'roof_type',
                        'label' => 'Roof Type',
                        'type' => 'select',
                        'required' => false,
                        'options' => [
                            [
                                'value' => 'flat',
                                'label' => 'Flat Roof',
                                'price_modifier' => 0,
                                'price_modifier_type' => 'fixed'
                            ],
                            [
                                'value' => 'pitched',
                                'label' => 'Pitched Roof',
                                'price_modifier' => 50,
                                'price_modifier_type' => 'fixed'
                            ],
                            [
                                'value' => 'complex',
                                'label' => 'Complex Roof',
                                'price_modifier' => 100,
                                'price_modifier_type' => 'fixed'
                            ]
                        ]
                    ]
                ];
                
                $service->setCustomFields($custom_fields);
                $migrated++;
            }
        }
        
        return $migrated;
    }
    
    /**
     * Migrate roof_type data in existing quotes
     * 
     * @return int Number of quotes updated
     */
    private static function migrateQuotes(): int {
        global $wpdb;
        
        $quotes_table = $wpdb->prefix . 'pq_quotes';
        $migrated = 0;
        
        // Find quotes with roof_type data
        $quotes = $wpdb->get_results(
            "SELECT id, roof_type FROM $quotes_table 
             WHERE roof_type IS NOT NULL AND roof_type != '' 
             AND (custom_field_data IS NULL OR custom_field_data = '')",
            ARRAY_A
        );
        
        foreach ($quotes as $quote_data) {
            $custom_field_data = [
                'roof_type' => $quote_data['roof_type']
            ];
            
            $result = $wpdb->update(
                $quotes_table,
                ['custom_field_data' => json_encode($custom_field_data)],
                ['id' => $quote_data['id']],
                ['%s'],
                ['%d']
            );
            
            if ($result !== false) {
                $migrated++;
            }
        }
        
        return $migrated;
    }
    
    /**
     * Migrate roof_type data in existing appointments
     * 
     * @return int Number of appointments updated
     */
    private static function migrateAppointments(): int {
        global $wpdb;
        
        $appointments_table = $wpdb->prefix . 'pq_appointments';
        $migrated = 0;
        
        // Check if appointments table has roof_type column
        $columns = $wpdb->get_col("DESCRIBE $appointments_table", 0);
        
        if (!in_array('roof_type', $columns)) {
            return 0; // Column doesn't exist, skip migration
        }
        
        // Find appointments with roof_type data
        $appointments = $wpdb->get_results(
            "SELECT id, roof_type FROM $appointments_table 
             WHERE roof_type IS NOT NULL AND roof_type != '' 
             AND (custom_field_data IS NULL OR custom_field_data = '')",
            ARRAY_A
        );
        
        foreach ($appointments as $appointment_data) {
            $custom_field_data = [
                'roof_type' => $appointment_data['roof_type']
            ];
            
            $result = $wpdb->update(
                $appointments_table,
                ['custom_field_data' => json_encode($custom_field_data)],
                ['id' => $appointment_data['id']],
                ['%s'],
                ['%d']
            );
            
            if ($result !== false) {
                $migrated++;
            }
        }
        
        return $migrated;
    }
    
    /**
     * Rollback migration (restore roof_type from custom_field_data)
     * 
     * @return array Rollback result with stats
     */
    public static function rollback(): array {
        global $wpdb;
        
        $stats = [
            'quotes_restored' => 0,
            'appointments_restored' => 0,
            'errors' => []
        ];
        
        try {
            // Restore quotes
            $quotes_table = $wpdb->prefix . 'pq_quotes';
            $quotes = $wpdb->get_results(
                "SELECT id, custom_field_data FROM $quotes_table 
                 WHERE custom_field_data IS NOT NULL AND custom_field_data != ''",
                ARRAY_A
            );
            
            foreach ($quotes as $quote_data) {
                $custom_fields = json_decode($quote_data['custom_field_data'], true);
                
                if (isset($custom_fields['roof_type'])) {
                    $wpdb->update(
                        $quotes_table,
                        ['roof_type' => $custom_fields['roof_type']],
                        ['id' => $quote_data['id']],
                        ['%s'],
                        ['%d']
                    );
                    
                    $stats['quotes_restored']++;
                }
            }
            
            // Restore appointments
            $appointments_table = $wpdb->prefix . 'pq_appointments';
            $appointments = $wpdb->get_results(
                "SELECT id, custom_field_data FROM $appointments_table 
                 WHERE custom_field_data IS NOT NULL AND custom_field_data != ''",
                ARRAY_A
            );
            
            foreach ($appointments as $appointment_data) {
                $custom_fields = json_decode($appointment_data['custom_field_data'], true);
                
                if (isset($custom_fields['roof_type'])) {
                    $wpdb->update(
                        $appointments_table,
                        ['roof_type' => $custom_fields['roof_type']],
                        ['id' => $appointment_data['id']],
                        ['%s'],
                        ['%d']
                    );
                    
                    $stats['appointments_restored']++;
                }
            }
            
        } catch (\Exception $e) {
            $stats['errors'][] = $e->getMessage();
        }
        
        return $stats;
    }
}
