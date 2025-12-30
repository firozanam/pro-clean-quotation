<?php

namespace ProClean\Quotation\Admin;

use ProClean\Quotation\Models\Quote;

/**
 * Admin Dashboard Class
 * 
 * @package ProClean\Quotation\Admin
 * @since 1.0.0
 */
class Dashboard {
    
    /**
     * Dashboard instance
     * 
     * @var Dashboard
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return Dashboard
     */
    public static function getInstance(): Dashboard {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor for singleton
    }
    
    /**
     * Render dashboard
     */
    public function render(): void {
        $stats = $this->getStats();
        $recent_quotes = $this->getRecentQuotes();
        $upcoming_bookings = $this->getUpcomingBookings();
        
        ?>
        <div class="wrap">
            <h1><?php _e('Pro Clean Quotation Dashboard', 'pro-clean-quotation'); ?></h1>
            
            <!-- Stats Cards -->
            <div class="pcq-dashboard-stats">
                <div class="pcq-stat-card">
                    <div class="pcq-stat-icon">
                        <span class="dashicons dashicons-calculator"></span>
                    </div>
                    <div class="pcq-stat-content">
                        <h3><?php echo number_format($stats['today_quotes']); ?></h3>
                        <p><?php _e('Quotes Today', 'pro-clean-quotation'); ?></p>
                    </div>
                </div>
                
                <div class="pcq-stat-card">
                    <div class="pcq-stat-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="pcq-stat-content">
                        <h3><?php echo number_format($stats['pending_quotes']); ?></h3>
                        <p><?php _e('Pending Quotes', 'pro-clean-quotation'); ?></p>
                    </div>
                </div>
                
                <div class="pcq-stat-card">
                    <div class="pcq-stat-icon">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                    <div class="pcq-stat-content">
                        <h3>€<?php echo number_format($stats['week_revenue'], 2); ?></h3>
                        <p><?php _e('This Week Revenue', 'pro-clean-quotation'); ?></p>
                    </div>
                </div>
                
                <div class="pcq-stat-card">
                    <div class="pcq-stat-icon">
                        <span class="dashicons dashicons-yes-alt"></span>
                    </div>
                    <div class="pcq-stat-content">
                        <h3><?php echo number_format($stats['conversion_rate'], 1); ?>%</h3>
                        <p><?php _e('Conversion Rate', 'pro-clean-quotation'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="pcq-dashboard-content">
                <!-- Recent Quotes -->
                <div class="pcq-dashboard-section">
                    <div class="pcq-section-header">
                        <h2><?php _e('Recent Quotes', 'pro-clean-quotation'); ?></h2>
                        <a href="<?php echo admin_url('admin.php?page=pcq-quotes'); ?>" class="button">
                            <?php _e('View All', 'pro-clean-quotation'); ?>
                        </a>
                    </div>
                    
                    <div class="pcq-table-container">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Quote #', 'pro-clean-quotation'); ?></th>
                                    <th><?php _e('Customer', 'pro-clean-quotation'); ?></th>
                                    <th><?php _e('Service', 'pro-clean-quotation'); ?></th>
                                    <th><?php _e('Amount', 'pro-clean-quotation'); ?></th>
                                    <th><?php _e('Status', 'pro-clean-quotation'); ?></th>
                                    <th><?php _e('Date', 'pro-clean-quotation'); ?></th>
                                    <th><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_quotes)): ?>
                                    <tr>
                                        <td colspan="7" class="pcq-no-data">
                                            <?php _e('No quotes found.', 'pro-clean-quotation'); ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_quotes as $quote): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo esc_html($quote->getQuoteNumber()); ?></strong>
                                            </td>
                                            <td>
                                                <?php echo esc_html($quote->getCustomerName()); ?><br>
                                                <small><?php echo esc_html($quote->getCustomerEmail()); ?></small>
                                            </td>
                                            <td>
                                                <?php echo esc_html(ucfirst($quote->getServiceType())); ?><br>
                                                <small><?php echo number_format($quote->getSquareMeters(), 1); ?> sqm</small>
                                            </td>
                                            <td>
                                                <strong>€<?php echo number_format($quote->getTotalPrice(), 2); ?></strong>
                                            </td>
                                            <td>
                                                <span class="pcq-status pcq-status-<?php echo esc_attr($quote->getStatus()); ?>">
                                                    <?php echo esc_html(ucfirst($quote->getStatus())); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('M j, Y', strtotime($quote->getCreatedAt())); ?><br>
                                                <small><?php echo date('H:i', strtotime($quote->getCreatedAt())); ?></small>
                                            </td>
                                            <td>
                                                <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote->getId()); ?>" 
                                                   class="button button-small">
                                                    <?php _e('View', 'pro-clean-quotation'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="pcq-dashboard-section">
                    <div class="pcq-section-header">
                        <h2><?php _e('Quick Actions', 'pro-clean-quotation'); ?></h2>
                    </div>
                    
                    <div class="pcq-quick-actions">
                        <div class="pcq-action-card">
                            <h3><?php _e('Pricing Settings', 'pro-clean-quotation'); ?></h3>
                            <p><?php _e('Update service rates and pricing rules', 'pro-clean-quotation'); ?></p>
                            <a href="<?php echo admin_url('admin.php?page=pcq-settings&tab=pricing'); ?>" class="button button-primary">
                                <?php _e('Manage Pricing', 'pro-clean-quotation'); ?>
                            </a>
                        </div>
                        
                        <div class="pcq-action-card">
                            <h3><?php _e('Email Templates', 'pro-clean-quotation'); ?></h3>
                            <p><?php _e('Customize email notifications and templates', 'pro-clean-quotation'); ?></p>
                            <a href="<?php echo admin_url('admin.php?page=pcq-settings&tab=email'); ?>" class="button button-primary">
                                <?php _e('Edit Templates', 'pro-clean-quotation'); ?>
                            </a>
                        </div>
                        
                        <div class="pcq-action-card">
                            <h3><?php _e('Form Settings', 'pro-clean-quotation'); ?></h3>
                            <p><?php _e('Configure quote form fields and validation', 'pro-clean-quotation'); ?></p>
                            <a href="<?php echo admin_url('admin.php?page=pcq-settings&tab=form'); ?>" class="button button-primary">
                                <?php _e('Form Settings', 'pro-clean-quotation'); ?>
                            </a>
                        </div>
                        
                        <div class="pcq-action-card">
                            <h3><?php _e('Integration', 'pro-clean-quotation'); ?></h3>
                            <p><?php _e('Manage MotoPress and WooCommerce integration', 'pro-clean-quotation'); ?></p>
                            <a href="<?php echo admin_url('admin.php?page=pcq-settings&tab=integration'); ?>" class="button button-primary">
                                <?php _e('Integration Settings', 'pro-clean-quotation'); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="pcq-dashboard-section">
                    <div class="pcq-section-header">
                        <h2><?php _e('System Status', 'pro-clean-quotation'); ?></h2>
                    </div>
                    
                    <div class="pcq-system-status">
                        <?php $this->renderSystemStatus(); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <style>
        .pcq-dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .pcq-stat-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .pcq-stat-icon {
            width: 50px;
            height: 50px;
            background: #2271b1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        
        .pcq-stat-content h3 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #1d2327;
        }
        
        .pcq-stat-content p {
            margin: 5px 0 0 0;
            color: #646970;
            font-size: 14px;
        }
        
        .pcq-dashboard-content {
            display: grid;
            gap: 30px;
        }
        
        .pcq-dashboard-section {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .pcq-section-header {
            padding: 20px;
            border-bottom: 1px solid #ccd0d4;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f6f7f7;
        }
        
        .pcq-section-header h2 {
            margin: 0;
            font-size: 18px;
        }
        
        .pcq-table-container {
            overflow-x: auto;
        }
        
        .pcq-no-data {
            text-align: center;
            padding: 40px;
            color: #646970;
        }
        
        .pcq-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .pcq-status-new {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .pcq-status-viewed {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .pcq-status-booked {
            background: #e8f5e8;
            color: #2e7d32;
        }
        
        .pcq-status-expired {
            background: #ffebee;
            color: #c62828;
        }
        
        .pcq-quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .pcq-action-card {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            text-align: center;
        }
        
        .pcq-action-card h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        
        .pcq-action-card p {
            margin: 0 0 15px 0;
            color: #646970;
            font-size: 14px;
        }
        
        .pcq-system-status {
            padding: 20px;
        }
        
        .pcq-status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f1;
        }
        
        .pcq-status-item:last-child {
            border-bottom: none;
        }
        
        .pcq-status-good {
            color: #00a32a;
        }
        
        .pcq-status-warning {
            color: #dba617;
        }
        
        .pcq-status-error {
            color: #d63638;
        }
        </style>
        <?php
    }
    
    /**
     * Get dashboard statistics
     * 
     * @return array Statistics data
     */
    private function getStats(): array {
        global $wpdb;
        
        $quotes_table = $wpdb->prefix . 'pq_quotes';
        
        // Today's quotes
        $today_quotes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $quotes_table WHERE DATE(created_at) = %s",
            current_time('Y-m-d')
        ));
        
        // Pending quotes (last 30 days)
        $pending_quotes = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $quotes_table WHERE status = 'new' AND created_at >= %s",
            date('Y-m-d H:i:s', strtotime('-30 days'))
        ));
        
        // This week's revenue (estimated from quotes)
        $week_start = date('Y-m-d', strtotime('monday this week'));
        $week_revenue = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(total_price) FROM $quotes_table WHERE DATE(created_at) >= %s",
            $week_start
        ));
        
        // Conversion rate (quotes to bookings - simplified calculation)
        $total_quotes = $wpdb->get_var("SELECT COUNT(*) FROM $quotes_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $booked_quotes = $wpdb->get_var("SELECT COUNT(*) FROM $quotes_table WHERE status = 'booked' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
        $conversion_rate = $total_quotes > 0 ? ($booked_quotes / $total_quotes) * 100 : 0;
        
        return [
            'today_quotes' => (int) $today_quotes,
            'pending_quotes' => (int) $pending_quotes,
            'week_revenue' => (float) $week_revenue ?: 0,
            'conversion_rate' => (float) $conversion_rate
        ];
    }
    
    /**
     * Get recent quotes
     * 
     * @return array Recent quotes
     */
    private function getRecentQuotes(): array {
        $quotes_data = Quote::getAll(1, 5);
        return $quotes_data['quotes'];
    }
    
    /**
     * Get upcoming bookings
     * 
     * @return array Upcoming bookings
     */
    private function getUpcomingBookings(): array {
        // This would integrate with MotoPress Appointment
        // For now, return empty array
        return [];
    }
    
    /**
     * Render system status
     */
    private function renderSystemStatus(): void {
        $status_items = [
            [
                'label' => __('Plugin Version', 'pro-clean-quotation'),
                'value' => PCQ_VERSION,
                'status' => 'good'
            ],
            [
                'label' => __('WordPress Version', 'pro-clean-quotation'),
                'value' => get_bloginfo('version'),
                'status' => version_compare(get_bloginfo('version'), '6.0', '>=') ? 'good' : 'warning'
            ],
            [
                'label' => __('PHP Version', 'pro-clean-quotation'),
                'value' => PHP_VERSION,
                'status' => version_compare(PHP_VERSION, '8.0', '>=') ? 'good' : 'warning'
            ],
            [
                'label' => __('MotoPress Appointment', 'pro-clean-quotation'),
                'value' => is_plugin_active('motopress-appointment-lite/motopress-appointment.php') ? __('Active', 'pro-clean-quotation') : __('Inactive', 'pro-clean-quotation'),
                'status' => is_plugin_active('motopress-appointment-lite/motopress-appointment.php') ? 'good' : 'error'
            ],
            [
                'label' => __('WooCommerce (Optional)', 'pro-clean-quotation'),
                'value' => is_plugin_active('woocommerce/woocommerce.php') ? __('Active - Online Payments Available', 'pro-clean-quotation') : __('Not Active - Cash/Bank Transfer Only', 'pro-clean-quotation'),
                'status' => is_plugin_active('woocommerce/woocommerce.php') ? 'good' : 'warning'
            ],
            [
                'label' => __('Email Notifications', 'pro-clean-quotation'),
                'value' => Settings::get('email_notifications_enabled', true) ? __('Enabled', 'pro-clean-quotation') : __('Disabled', 'pro-clean-quotation'),
                'status' => Settings::get('email_notifications_enabled', true) ? 'good' : 'warning'
            ]
        ];
        
        foreach ($status_items as $item) {
            echo '<div class="pcq-status-item">';
            echo '<span>' . esc_html($item['label']) . '</span>';
            echo '<span class="pcq-status-' . esc_attr($item['status']) . '">' . esc_html($item['value']) . '</span>';
            echo '</div>';
        }
    }
}