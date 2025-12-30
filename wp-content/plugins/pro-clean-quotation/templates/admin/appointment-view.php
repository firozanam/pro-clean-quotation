<?php
/**
 * Admin Appointment View Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$service = $appointment->getService();
$employee = $appointment->getEmployee();
$quote = $appointment->getQuote();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Appointment Details', 'pro-clean-quotation'); ?>
        <span class="pcq-appointment-id">#<?php echo $appointment->getId(); ?></span>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=edit&id=' . $appointment->getId()); ?>" class="page-title-action">
        <?php _e('Edit Appointment', 'pro-clean-quotation'); ?>
    </a>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-appointments'); ?>" class="page-title-action">
        <?php _e('Back to Appointments', 'pro-clean-quotation'); ?>
    </a>
    
    <div class="pcq-appointment-view">
        <!-- Status and Quick Actions -->
        <div class="pcq-status-section">
            <div class="pcq-status-display">
                <span class="pcq-status-badge pcq-status-<?php echo $appointment->getStatus(); ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $appointment->getStatus())); ?>
                </span>
                
                <?php if ($appointment->canBeCancelled()): ?>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-appointments&action=cancel&id=' . $appointment->getId()), 'cancel_appointment_' . $appointment->getId()); ?>" 
                       class="button pcq-cancel-btn" 
                       onclick="return confirm('<?php _e('Are you sure you want to cancel this appointment?', 'pro-clean-quotation'); ?>')">
                        <?php _e('Cancel Appointment', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
                
                <?php if ($appointment->canBeRescheduled()): ?>
                    <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=reschedule&id=' . $appointment->getId()); ?>" 
                       class="button">
                        <?php _e('Reschedule', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Main Content Grid -->
        <div class="pcq-content-grid">
            <!-- Left Column -->
            <div class="pcq-main-content">
                <!-- Service Information -->
                <div class="pcq-info-card">
                    <h2><?php _e('Service Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="pcq-info-table">
                        <tr>
                            <th><?php _e('Service', 'pro-clean-quotation'); ?></th>
                            <td>
                                <?php if ($service): ?>
                                    <span class="pcq-service-badge" style="background-color: <?php echo esc_attr($service->getColor()); ?>">
                                        <?php echo esc_html($service->getName()); ?>
                                    </span>
                                    <?php if ($service->getDescription()): ?>
                                        <br><small><?php echo esc_html($service->getDescription()); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="pcq-missing"><?php _e('Service not found', 'pro-clean-quotation'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><?php _e('Date & Time', 'pro-clean-quotation'); ?></th>
                            <td>
                                <strong><?php echo date('l, F j, Y', strtotime($appointment->getServiceDate())); ?></strong><br>
                                <span class="pcq-time"><?php echo $appointment->getServiceTimeStart() . ' - ' . $appointment->getServiceTimeEnd(); ?></span>
                                <small>(<?php echo $appointment->getDuration(); ?> <?php _e('minutes', 'pro-clean-quotation'); ?>)</small>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><?php _e('Assigned Employee', 'pro-clean-quotation'); ?></th>
                            <td>
                                <?php if ($employee): ?>
                                    <div class="pcq-employee-info">
                                        <?php if ($employee->getAvatarUrl()): ?>
                                            <img src="<?php echo esc_url($employee->getAvatarUrl()); ?>" 
                                                 alt="<?php echo esc_attr($employee->getName()); ?>" 
                                                 class="pcq-employee-avatar-small">
                                        <?php endif; ?>
                                        
                                        <div>
                                            <strong><?php echo esc_html($employee->getName()); ?></strong>
                                            <?php if ($employee->getEmail()): ?>
                                                <br><a href="mailto:<?php echo esc_attr($employee->getEmail()); ?>">
                                                    <?php echo esc_html($employee->getEmail()); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($employee->getPhone()): ?>
                                                <br><a href="tel:<?php echo esc_attr($employee->getPhone()); ?>">
                                                    <?php echo esc_html($employee->getPhone()); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="pcq-unassigned"><?php _e('Unassigned', 'pro-clean-quotation'); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><?php _e('Price', 'pro-clean-quotation'); ?></th>
                            <td>
                                <span class="pcq-price">€<?php echo number_format($appointment->getPrice(), 2); ?></span>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Customer Information -->
                <div class="pcq-info-card">
                    <h2><?php _e('Customer Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="pcq-info-table">
                        <tr>
                            <th><?php _e('Name', 'pro-clean-quotation'); ?></th>
                            <td><strong><?php echo esc_html($appointment->getCustomerName()); ?></strong></td>
                        </tr>
                        
                        <tr>
                            <th><?php _e('Email', 'pro-clean-quotation'); ?></th>
                            <td>
                                <a href="mailto:<?php echo esc_attr($appointment->getCustomerEmail()); ?>">
                                    <?php echo esc_html($appointment->getCustomerEmail()); ?>
                                </a>
                            </td>
                        </tr>
                        
                        <?php if ($appointment->getCustomerPhone()): ?>
                        <tr>
                            <th><?php _e('Phone', 'pro-clean-quotation'); ?></th>
                            <td>
                                <a href="tel:<?php echo esc_attr($appointment->getCustomerPhone()); ?>">
                                    <?php echo esc_html($appointment->getCustomerPhone()); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                
                <!-- Notes -->
                <?php if ($appointment->getNotes() || $appointment->getInternalNotes()): ?>
                <div class="pcq-info-card">
                    <h2><?php _e('Notes', 'pro-clean-quotation'); ?></h2>
                    
                    <?php if ($appointment->getNotes()): ?>
                        <div class="pcq-notes-section">
                            <h4><?php _e('Customer Notes', 'pro-clean-quotation'); ?></h4>
                            <div class="pcq-notes-content">
                                <?php echo nl2br(esc_html($appointment->getNotes())); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($appointment->getInternalNotes()): ?>
                        <div class="pcq-notes-section">
                            <h4><?php _e('Internal Notes', 'pro-clean-quotation'); ?></h4>
                            <div class="pcq-notes-content pcq-internal-notes">
                                <?php echo nl2br(esc_html($appointment->getInternalNotes())); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Sidebar -->
            <div class="pcq-sidebar">
                <!-- Quick Stats -->
                <div class="pcq-info-card">
                    <h3><?php _e('Quick Info', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-quick-stats">
                        <div class="pcq-stat">
                            <span class="pcq-stat-label"><?php _e('Created', 'pro-clean-quotation'); ?></span>
                            <span class="pcq-stat-value"><?php echo date('M j, Y', strtotime($appointment->getCreatedAt())); ?></span>
                        </div>
                        
                        <?php if ($appointment->getUpdatedAt()): ?>
                        <div class="pcq-stat">
                            <span class="pcq-stat-label"><?php _e('Last Updated', 'pro-clean-quotation'); ?></span>
                            <span class="pcq-stat-value"><?php echo date('M j, Y', strtotime($appointment->getUpdatedAt())); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <div class="pcq-stat">
                            <span class="pcq-stat-label"><?php _e('Time Until', 'pro-clean-quotation'); ?></span>
                            <span class="pcq-stat-value">
                                <?php 
                                $appointment_time = strtotime($appointment->getServiceDate() . ' ' . $appointment->getServiceTimeStart());
                                $now = time();
                                $diff = $appointment_time - $now;
                                
                                if ($diff > 0) {
                                    $days = floor($diff / (24 * 60 * 60));
                                    $hours = floor(($diff % (24 * 60 * 60)) / (60 * 60));
                                    
                                    if ($days > 0) {
                                        echo $days . ' ' . _n('day', 'days', $days, 'pro-clean-quotation');
                                    } elseif ($hours > 0) {
                                        echo $hours . ' ' . _n('hour', 'hours', $hours, 'pro-clean-quotation');
                                    } else {
                                        echo __('Less than 1 hour', 'pro-clean-quotation');
                                    }
                                } else {
                                    echo __('Past appointment', 'pro-clean-quotation');
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Related Quote -->
                <?php if ($quote): ?>
                <div class="pcq-info-card">
                    <h3><?php _e('Related Quote', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-quote-info">
                        <p>
                            <strong><?php _e('Quote #', 'pro-clean-quotation'); ?><?php echo $quote->getQuoteNumber(); ?></strong><br>
                            <small><?php echo date('M j, Y', strtotime($quote->getCreatedAt())); ?></small>
                        </p>
                        
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote->getId()); ?>" 
                               class="button button-small">
                                <?php _e('View Quote', 'pro-clean-quotation'); ?>
                            </a>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Actions -->
                <div class="pcq-info-card">
                    <h3><?php _e('Actions', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-actions-list">
                        <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=edit&id=' . $appointment->getId()); ?>" 
                           class="button button-primary button-large">
                            <?php _e('Edit Appointment', 'pro-clean-quotation'); ?>
                        </a>
                        
                        <a href="mailto:<?php echo esc_attr($appointment->getCustomerEmail()); ?>?subject=<?php echo urlencode('Regarding your appointment on ' . date('M j, Y', strtotime($appointment->getServiceDate()))); ?>" 
                           class="button button-large">
                            <?php _e('Email Customer', 'pro-clean-quotation'); ?>
                        </a>
                        
                        <?php if ($appointment->getCustomerPhone()): ?>
                        <a href="tel:<?php echo esc_attr($appointment->getCustomerPhone()); ?>" 
                           class="button button-large">
                            <?php _e('Call Customer', 'pro-clean-quotation'); ?>
                        </a>
                        <?php endif; ?>
                        
                        <a href="<?php echo admin_url('admin.php?page=pcq-calendar&date=' . $appointment->getServiceDate()); ?>" 
                           class="button button-large">
                            <?php _e('View in Calendar', 'pro-clean-quotation'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pcq-appointment-view {
    margin-top: 20px;
}

.pcq-appointment-id {
    color: #666;
    font-weight: normal;
    font-size: 0.8em;
}

.pcq-status-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-status-display {
    display: flex;
    align-items: center;
    gap: 15px;
}

.pcq-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.pcq-info-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-info-card h2,
.pcq-info-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 8px;
}

.pcq-info-table {
    width: 100%;
    border-collapse: collapse;
}

.pcq-info-table th {
    text-align: left;
    padding: 12px 15px 12px 0;
    font-weight: 600;
    color: #2c3e50;
    width: 150px;
    vertical-align: top;
}

.pcq-info-table td {
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.pcq-service-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    color: #fff;
    font-size: 13px;
    font-weight: 500;
}

.pcq-status-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 500;
    text-transform: capitalize;
}

.pcq-status-pending { background-color: #ff9800; color: #fff; }
.pcq-status-confirmed { background-color: #4caf50; color: #fff; }
.pcq-status-in_progress { background-color: #2196f3; color: #fff; }
.pcq-status-completed { background-color: #8bc34a; color: #fff; }
.pcq-status-cancelled { background-color: #f44336; color: #fff; }
.pcq-status-no_show { background-color: #9e9e9e; color: #fff; }

.pcq-employee-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pcq-employee-avatar-small {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.pcq-price {
    font-size: 18px;
    font-weight: 600;
    color: #27ae60;
}

.pcq-time {
    font-family: monospace;
    background: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
}

.pcq-missing,
.pcq-unassigned {
    color: #999;
    font-style: italic;
}

.pcq-notes-section {
    margin-bottom: 15px;
}

.pcq-notes-section h4 {
    margin: 0 0 8px 0;
    color: #34495e;
    font-size: 14px;
}

.pcq-notes-content {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 4px;
    border-left: 4px solid #3498db;
}

.pcq-internal-notes {
    border-left-color: #e74c3c;
}

.pcq-quick-stats {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.pcq-stat {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.pcq-stat-label {
    font-weight: 500;
    color: #666;
}

.pcq-stat-value {
    font-weight: 600;
    color: #2c3e50;
}

.pcq-quote-info p {
    margin: 0 0 10px 0;
}

.pcq-actions-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pcq-cancel-btn {
    background-color: #f44336 !important;
    color: #fff !important;
    border-color: #f44336 !important;
}

@media (max-width: 768px) {
    .pcq-content-grid {
        grid-template-columns: 1fr;
    }
    
    .pcq-status-display {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .pcq-info-table th {
        width: auto;
        display: block;
        padding-bottom: 5px;
    }
    
    .pcq-info-table td {
        display: block;
        padding-top: 0;
        padding-bottom: 15px;
    }
}
</style>