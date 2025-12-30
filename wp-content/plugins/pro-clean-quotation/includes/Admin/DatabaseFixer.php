<?php

namespace ProClean\Quotation\Admin;

/**
 * Database Fixer Class
 * 
 * Handles runtime database integrity checks and fixes
 * 
 * @package ProClean\Quotation\Admin
 * @since 1.0.0
 */
class DatabaseFixer {
    
    /**
     * Check if critical tables exist
     * 
     * @return array Array of missing tables
     */
    public static function getMissingTables(): array {
        global $wpdb;
        
        $required_tables = [
            'pq_quotes',
            'pq_bookings',
            'pq_email_logs',
            'pq_settings',
            'pq_services',
            'pq_employees',
            'pq_employee_services',
            'pq_appointments',
            'pq_appointment_employees',
            'pq_service_categories',
            'pq_webhook_logs',
            'pq_availability_overrides'
        ];
        
        $missing = [];
        
        foreach ($required_tables as $table_name) {
            $full_table_name = $wpdb->prefix . $table_name;
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = %s AND table_name = %s",
                    DB_NAME,
                    $full_table_name
                )
            );
            
            if (!$exists) {
                $missing[] = $full_table_name;
            }
        }
        
        return $missing;
    }
    
    /**
     * Check if database is healthy
     * 
     * @return bool True if all tables exist
     */
    public static function isDatabaseHealthy(): bool {
        return empty(self::getMissingTables());
    }
    
    /**
     * Fix missing tables
     * 
     * @return array Result with success status and message
     */
    public static function fixMissingTables(): array {
        try {
            $missing_before = self::getMissingTables();
            
            if (empty($missing_before)) {
                return [
                    'success' => true,
                    'message' => __('All database tables are present. No fix needed.', 'pro-clean-quotation'),
                    'tables_created' => []
                ];
            }
            
            // Run table creation
            \ProClean\Quotation\Database\Installer::createTables();
            
            // Run migration
            \ProClean\Quotation\Database\Installer::migrateEmployeeAssignments();
            
            $missing_after = self::getMissingTables();
            $created = array_diff($missing_before, $missing_after);
            
            if (empty($missing_after)) {
                return [
                    'success' => true,
                    'message' => sprintf(
                        __('Successfully created %d missing table(s).', 'pro-clean-quotation'),
                        count($created)
                    ),
                    'tables_created' => $created
                ];
            } else {
                return [
                    'success' => false,
                    'message' => sprintf(
                        __('Fixed %d table(s), but %d table(s) still missing. Please check database permissions.', 'pro-clean-quotation'),
                        count($created),
                        count($missing_after)
                    ),
                    'tables_created' => $created,
                    'still_missing' => $missing_after
                ];
            }
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('Error fixing database: ', 'pro-clean-quotation') . $e->getMessage()
            ];
        }
    }
    
    /**
     * Display admin notice if tables are missing
     */
    public static function showMissingTablesNotice(): void {
        $missing = self::getMissingTables();
        
        if (empty($missing)) {
            return;
        }
        
        $fix_url = admin_url('admin.php?page=pcq-settings&action=fix-database&nonce=' . wp_create_nonce('pcq-fix-database'));
        
        ?>
        <div class="notice notice-error">
            <p>
                <strong><?php _e('Pro Clean Quotation System - Database Issue Detected', 'pro-clean-quotation'); ?></strong>
            </p>
            <p>
                <?php 
                printf(
                    __('%d database table(s) are missing. This will cause errors when accessing appointments and other features.', 'pro-clean-quotation'),
                    count($missing)
                );
                ?>
            </p>
            <p>
                <strong><?php _e('Missing tables:', 'pro-clean-quotation'); ?></strong>
                <code><?php echo implode('</code>, <code>', $missing); ?></code>
            </p>
            <p>
                <a href="<?php echo esc_url($fix_url); ?>" class="button button-primary">
                    <?php _e('Fix Database Now', 'pro-clean-quotation'); ?>
                </a>
            </p>
        </div>
        <?php
    }
    
    /**
     * Handle database fix action
     */
    public static function handleFixAction(): void {
        if (!isset($_GET['action']) || $_GET['action'] !== 'fix-database') {
            return;
        }
        
        if (!isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'pcq-fix-database')) {
            wp_die(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions.', 'pro-clean-quotation'));
        }
        
        $result = self::fixMissingTables();
        
        if ($result['success']) {
            add_action('admin_notices', function() use ($result) {
                ?>
                <div class="notice notice-success is-dismissible">
                    <p><?php echo esc_html($result['message']); ?></p>
                    <?php if (!empty($result['tables_created'])): ?>
                        <p>
                            <strong><?php _e('Created tables:', 'pro-clean-quotation'); ?></strong>
                            <code><?php echo implode('</code>, <code>', $result['tables_created']); ?></code>
                        </p>
                    <?php endif; ?>
                </div>
                <?php
            });
        } else {
            add_action('admin_notices', function() use ($result) {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php echo esc_html($result['message']); ?></p>
                    <?php if (!empty($result['still_missing'])): ?>
                        <p>
                            <strong><?php _e('Still missing:', 'pro-clean-quotation'); ?></strong>
                            <code><?php echo implode('</code>, <code>', $result['still_missing']); ?></code>
                        </p>
                    <?php endif; ?>
                </div>
                <?php
            });
        }
        
        // Redirect to remove the action parameter
        wp_redirect(admin_url('admin.php?page=pcq-settings'));
        exit;
    }
}
