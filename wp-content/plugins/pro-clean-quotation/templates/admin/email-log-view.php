<?php
/**
 * Admin Email Log View Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Email Log Details', 'pro-clean-quotation'); ?></h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-email-logs'); ?>" class="page-title-action">
        <?php _e('Back to Email Logs', 'pro-clean-quotation'); ?>
    </a>
    
    <div class="pcq-email-log-view">
        <!-- Header Info -->
        <div class="pcq-log-header">
            <div class="pcq-log-meta">
                <div class="pcq-log-id">
                    <strong><?php _e('Log ID:', 'pro-clean-quotation'); ?></strong> #<?php echo $log->id; ?>
                </div>
                <div class="pcq-log-status">
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
                </div>
            </div>
            
            <div class="pcq-log-actions">
                <?php if ($log->status === 'failed'): ?>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-email-logs&action=resend&id=' . $log->id), 'resend_email_' . $log->id); ?>" 
                       class="button button-primary">
                        <?php _e('Resend Email', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-email-logs&action=delete&id=' . $log->id), 'delete_email_log_' . $log->id); ?>" 
                   class="button pcq-delete-btn" 
                   onclick="return confirm('<?php _e('Are you sure you want to delete this email log?', 'pro-clean-quotation'); ?>')">
                    <?php _e('Delete Log', 'pro-clean-quotation'); ?>
                </a>
            </div>
        </div>
        
        <!-- Email Details -->
        <div class="pcq-log-details">
            <div class="pcq-details-grid">
                <!-- Basic Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Email Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Email Type:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <span class="pcq-email-type pcq-type-<?php echo esc_attr($log->email_type); ?>">
                                <?php 
                                $type_labels = [
                                    'quote_confirmation' => __('Quote Confirmation', 'pro-clean-quotation'),
                                    'appointment_confirmation' => __('Appointment Confirmation', 'pro-clean-quotation'),
                                    'appointment_reminder' => __('Appointment Reminder', 'pro-clean-quotation'),
                                    'appointment_cancelled' => __('Appointment Cancelled', 'pro-clean-quotation'),
                                    'appointment_rescheduled' => __('Appointment Rescheduled', 'pro-clean-quotation'),
                                    'admin_notification' => __('Admin Notification', 'pro-clean-quotation')
                                ];
                                echo $type_labels[$log->email_type] ?? ucfirst(str_replace('_', ' ', $log->email_type));
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Recipient:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <a href="mailto:<?php echo esc_attr($log->recipient_email); ?>">
                                <?php echo esc_html($log->recipient_email); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Subject:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo esc_html($log->subject); ?>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Sent At:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($log->sent_at)); ?>
                            <small class="pcq-relative-time">
                                (<?php echo human_time_diff(strtotime($log->sent_at)); ?> ago)
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Reference Information -->
                <?php if ($log->reference_type && $log->reference_id): ?>
                <div class="pcq-detail-section">
                    <h3><?php _e('Reference', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Type:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo ucfirst($log->reference_type); ?>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('ID:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <a href="<?php echo admin_url('admin.php?page=pcq-' . $log->reference_type . 's&action=view&id=' . $log->reference_id); ?>">
                                #<?php echo $log->reference_id; ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Engagement Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Engagement', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Opened:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php if ($log->opened_at): ?>
                                <span class="pcq-engagement-yes">
                                    ✓ <?php echo date('F j, Y \a\t g:i A', strtotime($log->opened_at)); ?>
                                </span>
                            <?php else: ?>
                                <span class="pcq-engagement-no">
                                    ✗ <?php _e('Not opened', 'pro-clean-quotation'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Clicked:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php if ($log->clicked_at): ?>
                                <span class="pcq-engagement-yes">
                                    ✓ <?php echo date('F j, Y \a\t g:i A', strtotime($log->clicked_at)); ?>
                                </span>
                            <?php else: ?>
                                <span class="pcq-engagement-no">
                                    ✗ <?php _e('No clicks', 'pro-clean-quotation'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Error Information -->
            <?php if ($log->error_message): ?>
            <div class="pcq-error-section">
                <h3><?php _e('Error Details', 'pro-clean-quotation'); ?></h3>
                <div class="pcq-error-message">
                    <pre><?php echo esc_html($log->error_message); ?></pre>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.pcq-email-log-view {
    margin-top: 20px;
}

.pcq-log-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-log-meta {
    display: flex;
    gap: 20px;
    align-items: center;
}

.pcq-log-id {
    font-size: 16px;
    color: #2c3e50;
}

.pcq-log-actions {
    display: flex;
    gap: 10px;
}

.pcq-delete-btn {
    background-color: #f44336 !important;
    color: #fff !important;
    border-color: #f44336 !important;
}

.pcq-log-details {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
}

.pcq-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-bottom: 30px;
}

.pcq-detail-section h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 8px;
}

.pcq-detail-row {
    display: flex;
    margin-bottom: 15px;
    align-items: flex-start;
}

.pcq-detail-row label {
    min-width: 100px;
    font-weight: 500;
    color: #555;
    margin-right: 15px;
}

.pcq-detail-value {
    flex: 1;
}

.pcq-relative-time {
    color: #666;
    font-style: italic;
}

.pcq-engagement-yes {
    color: #4CAF50;
    font-weight: 500;
}

.pcq-engagement-no {
    color: #999;
}

.pcq-error-section {
    border-top: 1px solid #e0e0e0;
    padding-top: 20px;
}

.pcq-error-section h3 {
    margin: 0 0 15px 0;
    color: #d63638;
    border-bottom: 2px solid #d63638;
    padding-bottom: 8px;
}

.pcq-error-message {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    padding: 15px;
}

.pcq-error-message pre {
    margin: 0;
    white-space: pre-wrap;
    word-wrap: break-word;
    font-family: Consolas, Monaco, monospace;
    font-size: 13px;
    color: #d63638;
}

.pcq-status {
    display: inline-block;
    padding: 6px 12px;
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

@media (max-width: 768px) {
    .pcq-log-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .pcq-log-meta {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .pcq-details-grid {
        grid-template-columns: 1fr;
    }
    
    .pcq-detail-row {
        flex-direction: column;
        gap: 5px;
    }
    
    .pcq-detail-row label {
        min-width: auto;
        margin-right: 0;
    }
}
</style>