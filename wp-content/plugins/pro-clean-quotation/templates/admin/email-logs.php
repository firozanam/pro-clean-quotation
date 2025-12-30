<?php
/**
 * Admin Email Logs Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Email Logs', 'pro-clean-quotation'); ?></h1>
    
    <div class="pcq-email-logs-container">
        <!-- Filters -->
        <div class="pcq-filters">
            <form method="get" action="">
                <input type="hidden" name="page" value="pcq-email-logs">
                
                <div class="pcq-filter-row">
                    <select name="email_type" id="email_type">
                        <option value=""><?php _e('All Email Types', 'pro-clean-quotation'); ?></option>
                        <option value="quote_confirmation" <?php selected($_GET['email_type'] ?? '', 'quote_confirmation'); ?>><?php _e('Quote Confirmation', 'pro-clean-quotation'); ?></option>
                        <option value="appointment_confirmation" <?php selected($_GET['email_type'] ?? '', 'appointment_confirmation'); ?>><?php _e('Appointment Confirmation', 'pro-clean-quotation'); ?></option>
                        <option value="appointment_reminder" <?php selected($_GET['email_type'] ?? '', 'appointment_reminder'); ?>><?php _e('Appointment Reminder', 'pro-clean-quotation'); ?></option>
                        <option value="appointment_cancelled" <?php selected($_GET['email_type'] ?? '', 'appointment_cancelled'); ?>><?php _e('Appointment Cancelled', 'pro-clean-quotation'); ?></option>
                        <option value="appointment_rescheduled" <?php selected($_GET['email_type'] ?? '', 'appointment_rescheduled'); ?>><?php _e('Appointment Rescheduled', 'pro-clean-quotation'); ?></option>
                    </select>
                    
                    <select name="status" id="status">
                        <option value=""><?php _e('All Statuses', 'pro-clean-quotation'); ?></option>
                        <option value="sent" <?php selected($_GET['status'] ?? '', 'sent'); ?>><?php _e('Sent', 'pro-clean-quotation'); ?></option>
                        <option value="failed" <?php selected($_GET['status'] ?? '', 'failed'); ?>><?php _e('Failed', 'pro-clean-quotation'); ?></option>
                        <option value="pending" <?php selected($_GET['status'] ?? '', 'pending'); ?>><?php _e('Pending', 'pro-clean-quotation'); ?></option>
                    </select>
                    
                    <input type="text" name="search" placeholder="<?php _e('Search by email or subject...', 'pro-clean-quotation'); ?>" 
                           value="<?php echo esc_attr($_GET['search'] ?? ''); ?>" class="regular-text">
                    
                    <input type="submit" class="button" value="<?php _e('Filter', 'pro-clean-quotation'); ?>">
                    
                    <?php if (!empty($_GET['email_type']) || !empty($_GET['status']) || !empty($_GET['search'])): ?>
                        <a href="<?php echo admin_url('admin.php?page=pcq-email-logs'); ?>" class="button">
                            <?php _e('Clear Filters', 'pro-clean-quotation'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <!-- Email Logs Table -->
        <div class="pcq-table-container">
            <?php if (!empty($logs)): ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 60px;"><?php _e('ID', 'pro-clean-quotation'); ?></th>
                            <th style="width: 120px;"><?php _e('Date/Time', 'pro-clean-quotation'); ?></th>
                            <th style="width: 150px;"><?php _e('Type', 'pro-clean-quotation'); ?></th>
                            <th style="width: 200px;"><?php _e('Recipient', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Subject', 'pro-clean-quotation'); ?></th>
                            <th style="width: 100px;"><?php _e('Reference', 'pro-clean-quotation'); ?></th>
                            <th style="width: 80px;"><?php _e('Status', 'pro-clean-quotation'); ?></th>
                            <th style="width: 100px;"><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo $log->id; ?></td>
                                <td>
                                    <div class="pcq-datetime">
                                        <div class="pcq-date"><?php echo date('M j, Y', strtotime($log->sent_at)); ?></div>
                                        <div class="pcq-time"><?php echo date('g:i A', strtotime($log->sent_at)); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <span class="pcq-email-type pcq-type-<?php echo esc_attr($log->email_type); ?>">
                                        <?php 
                                        $type_labels = [
                                            'quote_confirmation' => __('Quote Confirmation', 'pro-clean-quotation'),
                                            'appointment_confirmation' => __('Appointment Confirmation', 'pro-clean-quotation'),
                                            'appointment_reminder' => __('Reminder', 'pro-clean-quotation'),
                                            'appointment_cancelled' => __('Cancellation', 'pro-clean-quotation'),
                                            'appointment_rescheduled' => __('Rescheduled', 'pro-clean-quotation'),
                                            'admin_notification' => __('Admin Notification', 'pro-clean-quotation')
                                        ];
                                        echo $type_labels[$log->email_type] ?? ucfirst(str_replace('_', ' ', $log->email_type));
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="pcq-recipient">
                                        <?php echo esc_html($log->recipient_email); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="pcq-subject">
                                        <?php echo esc_html($log->subject); ?>
                                        <?php if ($log->error_message): ?>
                                            <div class="pcq-error-preview">
                                                <small class="pcq-error-text">
                                                    <?php echo esc_html(substr($log->error_message, 0, 100)); ?>
                                                    <?php if (strlen($log->error_message) > 100): ?>...<?php endif; ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($log->reference_type && $log->reference_id): ?>
                                        <div class="pcq-reference">
                                            <span class="pcq-ref-type"><?php echo ucfirst($log->reference_type); ?></span>
                                            <span class="pcq-ref-id">#<?php echo $log->reference_id; ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="pcq-no-reference">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="pcq-status pcq-status-<?php echo esc_attr($log->status); ?>">
                                        <?php 
                                        $status_labels = [
                                            'sent' => __('Sent', 'pro-clean-quotation'),
                                            'failed' => __('Failed', 'pro-clean-quotation'),
                                            'pending' => __('Pending', 'pro-clean-quotation')
                                        ];
                                        echo $status_labels[$log->status] ?? ucfirst($log->status);
                                        ?>
                                    </span>
                                    
                                    <?php if ($log->opened_at): ?>
                                        <div class="pcq-engagement">
                                            <span class="pcq-opened" title="<?php _e('Email opened', 'pro-clean-quotation'); ?>">👁</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($log->clicked_at): ?>
                                        <div class="pcq-engagement">
                                            <span class="pcq-clicked" title="<?php _e('Link clicked', 'pro-clean-quotation'); ?>">🔗</span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="pcq-actions-dropdown">
                                        <button type="button" class="pcq-actions-toggle" aria-label="<?php _e('Actions', 'pro-clean-quotation'); ?>">
                                            <span class="pcq-dots">⋯</span>
                                        </button>
                                        <div class="pcq-actions-menu">
                                            <a href="<?php echo admin_url('admin.php?page=pcq-email-logs&action=view&id=' . $log->id); ?>" 
                                               class="pcq-action-item">
                                                <span class="pcq-action-icon">👁</span>
                                                <?php _e('View Details', 'pro-clean-quotation'); ?>
                                            </a>
                                            
                                            <?php if ($log->status === 'failed'): ?>
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-email-logs&action=resend&id=' . $log->id), 'resend_email_' . $log->id); ?>" 
                                               class="pcq-action-item pcq-action-approve">
                                                <span class="pcq-action-icon">🔄</span>
                                                <?php _e('Resend Email', 'pro-clean-quotation'); ?>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <div class="pcq-action-divider"></div>
                                            
                                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-email-logs&action=delete&id=' . $log->id), 'delete_email_log_' . $log->id); ?>" 
                                               class="pcq-action-item pcq-action-danger" 
                                               onclick="return confirm('<?php _e('Are you sure you want to delete this email log?', 'pro-clean-quotation'); ?>')">
                                                <span class="pcq-action-icon">🗑️</span>
                                                <?php _e('Delete Log', 'pro-clean-quotation'); ?>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="pcq-pagination">
                        <?php
                        $pagination_args = [
                            'base' => add_query_arg('paged', '%#%'),
                            'format' => '',
                            'prev_text' => __('&laquo; Previous', 'pro-clean-quotation'),
                            'next_text' => __('Next &raquo;', 'pro-clean-quotation'),
                            'total' => $total_pages,
                            'current' => $page
                        ];
                        echo paginate_links($pagination_args);
                        ?>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="pcq-no-data">
                    <div class="pcq-no-data-icon">📧</div>
                    <h3><?php _e('No Email Logs Found', 'pro-clean-quotation'); ?></h3>
                    <p><?php _e('No emails have been sent yet, or they match your current filters.', 'pro-clean-quotation'); ?></p>
                    
                    <?php if (!empty($_GET['email_type']) || !empty($_GET['status']) || !empty($_GET['search'])): ?>
                        <a href="<?php echo admin_url('admin.php?page=pcq-email-logs'); ?>" class="button button-primary">
                            <?php _e('Clear Filters', 'pro-clean-quotation'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Summary Stats -->
        <div class="pcq-email-stats">
            <div class="pcq-stats-grid">
                <div class="pcq-stat-item">
                    <div class="pcq-stat-number"><?php echo number_format($total); ?></div>
                    <div class="pcq-stat-label"><?php _e('Total Emails', 'pro-clean-quotation'); ?></div>
                </div>
                
                <?php
                // Get stats
                global $wpdb;
                $table = $wpdb->prefix . 'pq_email_logs';
                
                $sent_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'sent'");
                $failed_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'failed'");
                $opened_count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE opened_at IS NOT NULL");
                ?>
                
                <div class="pcq-stat-item pcq-stat-success">
                    <div class="pcq-stat-number"><?php echo number_format($sent_count); ?></div>
                    <div class="pcq-stat-label"><?php _e('Sent Successfully', 'pro-clean-quotation'); ?></div>
                </div>
                
                <div class="pcq-stat-item pcq-stat-error">
                    <div class="pcq-stat-number"><?php echo number_format($failed_count); ?></div>
                    <div class="pcq-stat-label"><?php _e('Failed', 'pro-clean-quotation'); ?></div>
                </div>
                
                <div class="pcq-stat-item pcq-stat-info">
                    <div class="pcq-stat-number"><?php echo number_format($opened_count); ?></div>
                    <div class="pcq-stat-label"><?php _e('Opened', 'pro-clean-quotation'); ?></div>
                </div>
                
                <?php if ($sent_count > 0): ?>
                <div class="pcq-stat-item pcq-stat-rate">
                    <div class="pcq-stat-number"><?php echo round(($opened_count / $sent_count) * 100, 1); ?>%</div>
                    <div class="pcq-stat-label"><?php _e('Open Rate', 'pro-clean-quotation'); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.pcq-email-logs-container {
    margin-top: 20px;
}

.pcq-filters {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-filter-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.pcq-filter-row select,
.pcq-filter-row input[type="text"] {
    margin: 0;
}

.pcq-table-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 20px;
}

.pcq-datetime {
    font-size: 13px;
}

.pcq-date {
    font-weight: 500;
}

.pcq-time {
    color: #666;
    font-size: 12px;
}

.pcq-email-type {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    color: #fff;
}

.pcq-type-quote_confirmation { background-color: #2196F3; }
.pcq-type-appointment_confirmation { background-color: #4CAF50; }
.pcq-type-appointment_reminder { background-color: #FF9800; }
.pcq-type-appointment_cancelled { background-color: #f44336; }
.pcq-type-appointment_rescheduled { background-color: #9C27B0; }
.pcq-type-admin_notification { background-color: #607D8B; }

.pcq-recipient {
    font-size: 13px;
    word-break: break-word;
}

.pcq-subject {
    font-size: 13px;
}

.pcq-error-preview {
    margin-top: 5px;
}

.pcq-error-text {
    color: #d63638;
    font-style: italic;
}

.pcq-reference {
    font-size: 12px;
}

.pcq-ref-type {
    color: #666;
    text-transform: capitalize;
}

.pcq-ref-id {
    font-weight: 500;
    color: #2271b1;
}

.pcq-no-reference {
    color: #999;
}

.pcq-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.pcq-status-sent {
    background-color: #d4edda;
    color: #155724;
}

.pcq-status-failed {
    background-color: #f8d7da;
    color: #721c24;
}

.pcq-status-pending {
    background-color: #fff3cd;
    color: #856404;
}

.pcq-engagement {
    margin-top: 2px;
    font-size: 12px;
}

.pcq-opened,
.pcq-clicked {
    cursor: help;
}

/* Actions Dropdown Styles */
.pcq-actions-dropdown {
    position: relative;
    display: inline-block;
}

.pcq-actions-toggle {
    background: #f6f7f7;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    transition: all 0.2s ease;
}

.pcq-actions-toggle:hover {
    background: #e8e9ea;
    border-color: #999;
}

.pcq-actions-toggle:focus {
    outline: 2px solid #2271b1;
    outline-offset: 1px;
}

.pcq-dots {
    display: inline-block;
    font-weight: bold;
    color: #666;
}

.pcq-actions-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    min-width: 180px;
    z-index: 1000;
    display: none;
    padding: 4px 0;
}

.pcq-actions-dropdown.active .pcq-actions-menu {
    display: block;
}

.pcq-action-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    text-decoration: none;
    color: #2c3e50;
    font-size: 13px;
    transition: background-color 0.2s ease;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
}

.pcq-action-item:hover {
    background-color: #f0f0f1;
    color: #2c3e50;
}

.pcq-action-item:focus {
    background-color: #f0f0f1;
    color: #2c3e50;
    outline: none;
    box-shadow: inset 0 0 0 1px #2271b1;
}

.pcq-action-icon {
    font-size: 14px;
    width: 16px;
    text-align: center;
}

.pcq-action-approve:hover {
    background-color: #f0f6ff;
    color: #00a32a;
}

.pcq-action-danger:hover {
    background-color: #fcf0f1;
    color: #d63638;
}

.pcq-action-divider {
    height: 1px;
    background-color: #e0e0e0;
    margin: 4px 0;
}

.pcq-actions {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.pcq-resend-btn {
    background-color: #FF9800 !important;
    color: #fff !important;
    border-color: #FF9800 !important;
}

.pcq-delete-btn {
    background-color: #f44336 !important;
    color: #fff !important;
    border-color: #f44336 !important;
}

.pcq-no-data {
    text-align: center;
    padding: 60px 20px;
    color: #666;
}

.pcq-no-data-icon {
    font-size: 48px;
    margin-bottom: 20px;
}

.pcq-no-data h3 {
    margin: 0 0 10px 0;
    color: #444;
}

.pcq-pagination {
    padding: 20px;
    text-align: center;
    border-top: 1px solid #e0e0e0;
}

.pcq-email-stats {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
}

.pcq-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.pcq-stat-item {
    text-align: center;
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
}

.pcq-stat-success { border-left: 4px solid #4CAF50; }
.pcq-stat-error { border-left: 4px solid #f44336; }
.pcq-stat-info { border-left: 4px solid #2196F3; }
.pcq-stat-rate { border-left: 4px solid #9C27B0; }

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

@media (max-width: 768px) {
    .pcq-filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pcq-actions {
        flex-direction: column;
    }
    
    .pcq-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggles
    const dropdownToggles = document.querySelectorAll('.pcq-actions-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.closest('.pcq-actions-dropdown');
            const isActive = dropdown.classList.contains('active');
            
            // Close all other dropdowns
            document.querySelectorAll('.pcq-actions-dropdown.active').forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('active', !isActive);
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.pcq-actions-dropdown')) {
            document.querySelectorAll('.pcq-actions-dropdown.active').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
    
    // Handle keyboard navigation
    document.addEventListener('keydown', function(e) {
        const activeDropdown = document.querySelector('.pcq-actions-dropdown.active');
        if (!activeDropdown) return;
        
        const menuItems = activeDropdown.querySelectorAll('.pcq-action-item');
        const currentFocus = document.activeElement;
        const currentIndex = Array.from(menuItems).indexOf(currentFocus);
        
        switch(e.key) {
            case 'Escape':
                activeDropdown.classList.remove('active');
                activeDropdown.querySelector('.pcq-actions-toggle').focus();
                break;
            case 'ArrowDown':
                e.preventDefault();
                const nextIndex = currentIndex < menuItems.length - 1 ? currentIndex + 1 : 0;
                menuItems[nextIndex].focus();
                break;
            case 'ArrowUp':
                e.preventDefault();
                const prevIndex = currentIndex > 0 ? currentIndex - 1 : menuItems.length - 1;
                menuItems[prevIndex].focus();
                break;
        }
    });
});
</script>