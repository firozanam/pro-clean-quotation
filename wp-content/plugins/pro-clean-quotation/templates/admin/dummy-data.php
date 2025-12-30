<?php
/**
 * Admin Dummy Data Management Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current data counts
global $wpdb;

$tables = [
    'quotes' => $wpdb->prefix . 'pq_quotes',
    'appointments' => $wpdb->prefix . 'pq_appointments', 
    'bookings' => $wpdb->prefix . 'pq_bookings',
    'services' => $wpdb->prefix . 'pq_services',
    'employees' => $wpdb->prefix . 'pq_employees',
    'email_logs' => $wpdb->prefix . 'pq_email_logs',
    'settings' => $wpdb->prefix . 'pq_settings'
];

$counts = [];
foreach ($tables as $name => $table) {
    $counts[$name] = $wpdb->get_var("SELECT COUNT(*) FROM $table");
}

$total_records = array_sum($counts);
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Dummy Data Management', 'pro-clean-quotation'); ?></h1>
    
    <div class="pcq-dummy-data-container">
        <!-- Warning Notice -->
        <div class="notice notice-warning">
            <p>
                <strong><?php _e('Development Tool Only', 'pro-clean-quotation'); ?></strong><br>
                <?php _e('This page is only available when WP_DEBUG is enabled. Use this tool to generate test data for development and testing purposes.', 'pro-clean-quotation'); ?>
            </p>
        </div>
        
        <!-- Current Data Status -->
        <div class="pcq-data-status">
            <h2><?php _e('Current Database Status', 'pro-clean-quotation'); ?></h2>
            
            <div class="pcq-stats-grid">
                <?php foreach ($counts as $table => $count): ?>
                <div class="pcq-stat-card">
                    <div class="pcq-stat-number"><?php echo number_format($count); ?></div>
                    <div class="pcq-stat-label"><?php echo ucfirst(str_replace('_', ' ', $table)); ?></div>
                </div>
                <?php endforeach; ?>
                
                <div class="pcq-stat-card pcq-stat-total">
                    <div class="pcq-stat-number"><?php echo number_format($total_records); ?></div>
                    <div class="pcq-stat-label"><?php _e('Total Records', 'pro-clean-quotation'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="pcq-actions-section">
            <h2><?php _e('Actions', 'pro-clean-quotation'); ?></h2>
            
            <div class="pcq-action-cards">
                <!-- Generate Dummy Data -->
                <div class="pcq-action-card">
                    <div class="pcq-action-icon">📊</div>
                    <h3><?php _e('Generate Dummy Data', 'pro-clean-quotation'); ?></h3>
                    <p><?php _e('Create comprehensive test data including quotes, appointments, bookings, services, employees, and email logs.', 'pro-clean-quotation'); ?></p>
                    
                    <div class="pcq-data-preview">
                        <h4><?php _e('Will Generate:', 'pro-clean-quotation'); ?></h4>
                        <ul>
                            <li>7 Services (Façade, Roof, Windows, etc.)</li>
                            <li>6 Employees with different specializations</li>
                            <li>25 Customer Quotes with various statuses</li>
                            <li>30 Appointments (past, present, future)</li>
                            <li>20 Bookings with payment tracking</li>
                            <li>50 Email Logs with engagement data</li>
                            <li>Business Settings and Configuration</li>
                        </ul>
                    </div>
                    
                    <?php if ($total_records < 10): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-dummy-data&action=generate'), 'generate_dummy_data'); ?>" 
                           class="button button-primary button-large">
                            <?php _e('Generate Dummy Data', 'pro-clean-quotation'); ?>
                        </a>
                    <?php else: ?>
                        <p class="pcq-warning-text">
                            <?php _e('Dummy data appears to already exist. Clear existing data first if you want to regenerate.', 'pro-clean-quotation'); ?>
                        </p>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-dummy-data&action=generate'), 'generate_dummy_data'); ?>" 
                           class="button button-secondary"
                           onclick="return confirm('<?php _e('This will add more dummy data to existing records. Continue?', 'pro-clean-quotation'); ?>')">
                            <?php _e('Add More Data', 'pro-clean-quotation'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <!-- Force Recreate Database -->
                <div class="pcq-action-card">
                    <div class="pcq-action-icon">🔄</div>
                    <h3><?php _e('Force Recreate Database', 'pro-clean-quotation'); ?></h3>
                    <p><?php _e('Drop all tables and recreate them with fresh dummy data. This will completely reset the plugin database.', 'pro-clean-quotation'); ?></p>
                    
                    <div class="pcq-warning-box">
                        <strong><?php _e('⚠️ Warning:', 'pro-clean-quotation'); ?></strong>
                        <?php _e('This will delete ALL existing data and recreate tables with dummy data.', 'pro-clean-quotation'); ?>
                    </div>
                    
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-dummy-data&action=recreate'), 'recreate_database'); ?>" 
                       class="button button-large pcq-danger-btn"
                       onclick="return confirm('<?php _e('This will DELETE ALL DATA and recreate with dummy data. Are you sure?', 'pro-clean-quotation'); ?>')">
                        <?php _e('Recreate Database', 'pro-clean-quotation'); ?>
                    </a>
                </div>
                
                <!-- Clear Data -->
                <div class="pcq-action-card pcq-danger-card">
                    <div class="pcq-action-icon">🗑️</div>
                    <h3><?php _e('Clear All Data', 'pro-clean-quotation'); ?></h3>
                    <p><?php _e('Remove all quotes, appointments, bookings, services, employees, email logs, and settings from the database.', 'pro-clean-quotation'); ?></p>
                    
                    <div class="pcq-warning-box">
                        <strong><?php _e('⚠️ Warning:', 'pro-clean-quotation'); ?></strong>
                        <?php _e('This action cannot be undone. All data will be permanently deleted.', 'pro-clean-quotation'); ?>
                    </div>
                    
                    <?php if ($total_records > 0): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-dummy-data&action=clear'), 'clear_dummy_data'); ?>" 
                           class="button button-large pcq-danger-btn"
                           onclick="return confirm('<?php _e('Are you sure you want to delete ALL data? This cannot be undone!', 'pro-clean-quotation'); ?>')">
                            <?php _e('Clear All Data', 'pro-clean-quotation'); ?>
                        </a>
                    <?php else: ?>
                        <button class="button button-large" disabled>
                            <?php _e('No Data to Clear', 'pro-clean-quotation'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Data Details -->
        <div class="pcq-data-details">
            <h2><?php _e('Generated Data Details', 'pro-clean-quotation'); ?></h2>
            
            <div class="pcq-details-grid">
                <div class="pcq-detail-section">
                    <h3><?php _e('Services', 'pro-clean-quotation'); ?></h3>
                    <ul>
                        <li>Façade Cleaning (Basic & Premium)</li>
                        <li>Roof Cleaning (Standard & Deep Clean)</li>
                        <li>Complete Cleaning Package</li>
                        <li>Window Cleaning - Residential</li>
                        <li>Emergency Cleaning Service</li>
                    </ul>
                </div>
                
                <div class="pcq-detail-section">
                    <h3><?php _e('Employees', 'pro-clean-quotation'); ?></h3>
                    <ul>
                        <li>John Smith - Façade Specialist</li>
                        <li>Maria Garcia - Roof Expert</li>
                        <li>David Johnson - Window & Emergency</li>
                        <li>Sarah Wilson - Residential Specialist</li>
                        <li>Michael Brown - All Services</li>
                        <li>Lisa Anderson - Premium Services</li>
                    </ul>
                </div>
                
                <div class="pcq-detail-section">
                    <h3><?php _e('Sample Data Includes', 'pro-clean-quotation'); ?></h3>
                    <ul>
                        <li>Realistic customer information</li>
                        <li>Various appointment statuses</li>
                        <li>Payment tracking data</li>
                        <li>Email engagement metrics</li>
                        <li>Service pricing variations</li>
                        <li>Employee scheduling conflicts</li>
                    </ul>
                </div>
                
                <div class="pcq-detail-section">
                    <h3><?php _e('Testing Scenarios', 'pro-clean-quotation'); ?></h3>
                    <ul>
                        <li>Past, current, and future appointments</li>
                        <li>Different quote statuses and conversions</li>
                        <li>Failed and successful email deliveries</li>
                        <li>Various payment states</li>
                        <li>Employee availability conflicts</li>
                        <li>Service capacity management</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pcq-dummy-data-container {
    margin-top: 20px;
}

.pcq-data-status {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.pcq-stat-card {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}

.pcq-stat-total {
    background: #e3f2fd;
    border-color: #2196F3;
}

.pcq-stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 5px;
}

.pcq-stat-label {
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pcq-actions-section {
    margin-bottom: 30px;
}

.pcq-action-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.pcq-action-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 25px;
    text-align: center;
}

.pcq-danger-card {
    border-color: #f44336;
    background: #fef5f5;
}

.pcq-action-icon {
    font-size: 48px;
    margin-bottom: 15px;
}

.pcq-action-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
}

.pcq-action-card p {
    color: #666;
    margin-bottom: 20px;
    line-height: 1.6;
}

.pcq-data-preview {
    text-align: left;
    background: #f8f9fa;
    border-radius: 4px;
    padding: 15px;
    margin: 15px 0;
}

.pcq-data-preview h4 {
    margin: 0 0 10px 0;
    color: #2c3e50;
    font-size: 14px;
}

.pcq-data-preview ul {
    margin: 0;
    padding-left: 20px;
}

.pcq-data-preview li {
    font-size: 13px;
    color: #555;
    margin-bottom: 3px;
}

.pcq-warning-box {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 10px;
    margin: 15px 0;
    font-size: 13px;
    color: #856404;
}

.pcq-warning-text {
    color: #856404;
    font-style: italic;
    margin-bottom: 15px;
}

.pcq-danger-btn {
    background-color: #f44336 !important;
    color: #fff !important;
    border-color: #f44336 !important;
}

.pcq-danger-btn:hover {
    background-color: #d32f2f !important;
    border-color: #d32f2f !important;
}

.pcq-data-details {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
}

.pcq-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.pcq-detail-section h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 5px;
}

.pcq-detail-section ul {
    margin: 0;
    padding-left: 20px;
}

.pcq-detail-section li {
    margin-bottom: 5px;
    color: #555;
    font-size: 14px;
}

@media (max-width: 768px) {
    .pcq-action-cards {
        grid-template-columns: 1fr;
    }
    
    .pcq-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .pcq-details-grid {
        grid-template-columns: 1fr;
    }
}
</style>