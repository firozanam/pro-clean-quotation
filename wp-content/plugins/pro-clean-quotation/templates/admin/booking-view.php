<?php
/**
 * Admin Booking View Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Helper function to format status
function pcq_format_booking_status_label($status) {
    $statuses = [
        'pending' => __('Pending', 'pro-clean-quotation'),
        'confirmed' => __('Confirmed', 'pro-clean-quotation'),
        'completed' => __('Completed', 'pro-clean-quotation'),
        'cancelled' => __('Cancelled', 'pro-clean-quotation')
    ];
    return $statuses[$status] ?? ucfirst($status);
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Booking Details', 'pro-clean-quotation'); ?></h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-bookings'); ?>" class="page-title-action">
        <?php _e('Back to Bookings', 'pro-clean-quotation'); ?>
    </a>
    
    <div class="pcq-booking-view">
        <!-- Booking Header -->
        <div class="pcq-booking-header">
            <div class="pcq-booking-meta">
                <div class="pcq-booking-number">
                    <strong><?php _e('Booking #', 'pro-clean-quotation'); ?><?php echo esc_html($booking->booking_number); ?></strong>
                </div>
                <div class="pcq-booking-status">
                    <span class="pcq-status pcq-status-<?php echo esc_attr($booking->booking_status); ?>">
                        <?php echo esc_html(pcq_format_booking_status_label($booking->booking_status)); ?>
                    </span>
                </div>
                <div class="pcq-booking-total">
                    <span class="pcq-total-amount">€<?php echo number_format($booking->total_amount, 2); ?></span>
                </div>
            </div>
            
            <div class="pcq-booking-actions">
                <?php if ($booking->quote_id && $quote): ?>
                    <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $booking->quote_id); ?>" 
                       class="button button-secondary">
                        <?php _e('View Quote', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo admin_url('admin.php?page=pcq-bookings'); ?>" 
                   class="button">
                    <?php _e('Back to List', 'pro-clean-quotation'); ?>
                </a>
            </div>
        </div>
        
        <!-- Booking Details -->
        <div class="pcq-booking-details">
            <div class="pcq-details-grid">
                <!-- Customer Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Customer Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Name:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo esc_html($booking->customer_name); ?>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Email:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <a href="mailto:<?php echo esc_attr($booking->customer_email); ?>">
                                <?php echo esc_html($booking->customer_email); ?>
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($booking->customer_phone): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Phone:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <a href="tel:<?php echo esc_attr($booking->customer_phone); ?>">
                                <?php echo esc_html($booking->customer_phone); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Property Address:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo nl2br(esc_html($booking->property_address)); ?>
                        </div>
                    </div>
                </div>
                
                <!-- Service Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Service Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Service Type:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php 
                            // Format service type with robust handling
                            $service_type = $booking->service_type;
                            
                            // Handle numeric or NULL values
                            if (empty($service_type) || is_numeric($service_type)) {
                                $service_type = 'unknown';
                            }
                            
                            $service_labels = [
                                'facade' => __('Façade Cleaning', 'pro-clean-quotation'),
                                'roof' => __('Roof Cleaning', 'pro-clean-quotation'),
                                'both' => __('Complete Package', 'pro-clean-quotation'),
                                'facade_cleaning' => __('Façade Cleaning', 'pro-clean-quotation'),
                                'roof_cleaning' => __('Roof Cleaning', 'pro-clean-quotation'),
                                'complete_package' => __('Complete Package', 'pro-clean-quotation'),
                                'window_cleaning' => __('Window Cleaning', 'pro-clean-quotation'),
                                'unknown' => __('Not Specified', 'pro-clean-quotation')
                            ];
                            
                            $display_name = $service_labels[$service_type] ?? ucfirst(str_replace('_', ' ', $service_type));
                            ?>
                            <span class="pcq-service-badge">
                                <?php echo esc_html($display_name); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Service Date:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <strong><?php echo esc_html(date('l, F j, Y', strtotime($booking->service_date))); ?></strong>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Service Time:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo esc_html(date('g:i A', strtotime($booking->service_time_start))); ?>
                            - 
                            <?php echo esc_html(date('g:i A', strtotime($booking->service_time_end))); ?>
                            <?php if ($booking->estimated_duration): ?>
                                <br><small><?php echo esc_html($booking->estimated_duration); ?> <?php _e('hours', 'pro-clean-quotation'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($booking->assigned_technician): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Assigned Technician:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo esc_html($booking->assigned_technician); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($booking->service_details): ?>
                <?php 
                // Try to decode JSON if it's JSON format
                $details = $booking->service_details;
                $decoded = json_decode($details, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)): ?>
                <!-- Service Details -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Service Details', 'pro-clean-quotation'); ?></h3>
                    
                    <table class="pcq-service-details-table">
                        <?php if (isset($decoded['square_meters'])): ?>
                        <tr>
                            <td class="pcq-detail-label"><?php _e('Area:', 'pro-clean-quotation'); ?></td>
                            <td class="pcq-detail-data"><?php echo number_format($decoded['square_meters'], 1); ?> m²</td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($decoded['linear_meters'])): ?>
                        <tr>
                            <td class="pcq-detail-label"><?php _e('Linear Meters:', 'pro-clean-quotation'); ?></td>
                            <td class="pcq-detail-data"><?php echo number_format($decoded['linear_meters'], 1); ?> m</td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($decoded['building_height'])): ?>
                        <tr>
                            <td class="pcq-detail-label"><?php _e('Building Height:', 'pro-clean-quotation'); ?></td>
                            <td class="pcq-detail-data"><?php echo $decoded['building_height']; ?> <?php _e('floors', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($decoded['property_type'])): ?>
                        <tr>
                            <td class="pcq-detail-label"><?php _e('Property Type:', 'pro-clean-quotation'); ?></td>
                            <td class="pcq-detail-data"><?php echo ucfirst($decoded['property_type']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($decoded['surface_material'])): ?>
                        <tr>
                            <td class="pcq-detail-label"><?php _e('Surface Material:', 'pro-clean-quotation'); ?></td>
                            <td class="pcq-detail-data"><?php echo ucfirst($decoded['surface_material']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($decoded['roof_type'])): ?>
                        <tr>
                            <td class="pcq-detail-label"><?php _e('Roof Type:', 'pro-clean-quotation'); ?></td>
                            <td class="pcq-detail-data"><?php echo ucfirst($decoded['roof_type']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($decoded['payment_method'])): ?>
                        <tr>
                            <td class="pcq-detail-label"><?php _e('Payment Method:', 'pro-clean-quotation'); ?></td>
                            <td class="pcq-detail-data"><?php echo ucfirst($decoded['payment_method']); ?></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if (isset($decoded['appointment_id'])): ?>
                        <tr>
                            <td class="pcq-detail-label"><?php _e('Appointment ID:', 'pro-clean-quotation'); ?></td>
                            <td class="pcq-detail-data">
                                <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=view&id=' . $decoded['appointment_id']); ?>" class="pcq-appointment-link">
                                    #<?php echo $decoded['appointment_id']; ?>
                                </a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </table>
                </div>
                <?php endif; ?>
                <?php endif; ?>
                
                <!-- Payment Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Payment Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Total Amount:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <strong style="font-size: 18px;">€<?php echo number_format($booking->total_amount, 2); ?></strong>
                        </div>
                    </div>
                    
                    <?php if ($booking->deposit_amount > 0): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Deposit Amount:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            €<?php echo number_format($booking->deposit_amount, 2); ?>
                            <?php if ($booking->deposit_paid): ?>
                                <span class="pcq-status pcq-status-confirmed" style="margin-left: 10px;">
                                    <?php _e('Paid', 'pro-clean-quotation'); ?>
                                </span>
                            <?php else: ?>
                                <span class="pcq-status pcq-status-pending" style="margin-left: 10px;">
                                    <?php _e('Unpaid', 'pro-clean-quotation'); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Balance Due:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            €<?php echo number_format($booking->balance_due ?? ($booking->total_amount - ($booking->deposit_paid ? $booking->deposit_amount : 0)), 2); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Payment Status:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <span class="pcq-status pcq-status-<?php echo esc_attr($booking->payment_status); ?>">
                                <?php echo esc_html(ucfirst($booking->payment_status)); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Booking Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Booking Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Created:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($booking->created_at)); ?>
                            <small class="pcq-relative-time">
                                (<?php echo human_time_diff(strtotime($booking->created_at)); ?> ago)
                            </small>
                        </div>
                    </div>
                    
                    <?php if ($booking->updated_at): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Last Updated:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($booking->updated_at)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($booking->completed_at): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Completed:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($booking->completed_at)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($booking->cancelled_at): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Cancelled:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($booking->cancelled_at)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($booking->reminder_sent): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Reminder Sent:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php if ($booking->reminder_sent_at): ?>
                                <?php echo date('F j, Y \a\t g:i A', strtotime($booking->reminder_sent_at)); ?>
                            <?php else: ?>
                                <?php _e('Yes', 'pro-clean-quotation'); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Notes -->
                <?php if ($booking->customer_notes || $booking->admin_notes || $booking->cancellation_reason): ?>
                <div class="pcq-detail-section pcq-full-width">
                    <h3><?php _e('Notes', 'pro-clean-quotation'); ?></h3>
                    
                    <?php if ($booking->customer_notes): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Customer Notes:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo nl2br(esc_html($booking->customer_notes)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($booking->admin_notes): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Admin Notes:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo nl2br(esc_html($booking->admin_notes)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($booking->cancellation_reason): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Cancellation Reason:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo nl2br(esc_html($booking->cancellation_reason)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.pcq-booking-view {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    margin-top: 20px;
    overflow: hidden;
}

.pcq-booking-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
}

.pcq-booking-meta {
    display: flex;
    align-items: center;
    gap: 20px;
}

.pcq-booking-number strong {
    font-size: 18px;
    color: #1d2327;
}

.pcq-booking-total {
    font-size: 24px;
    font-weight: 700;
    color: #10b981;
}

.pcq-booking-actions {
    display: flex;
    gap: 10px;
}

.pcq-booking-details {
    padding: 25px;
}

.pcq-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.pcq-detail-section {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 20px;
}

.pcq-detail-section.pcq-full-width {
    grid-column: 1 / -1;
}

.pcq-detail-section h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
    border-bottom: 2px solid #2271b1;
    padding-bottom: 8px;
}

.pcq-detail-row {
    display: flex;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e5e7eb;
}

.pcq-detail-row:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.pcq-detail-row label {
    flex: 0 0 40%;
    font-weight: 600;
    color: #646970;
    font-size: 13px;
}

.pcq-detail-value {
    flex: 1;
    color: #1d2327;
    font-size: 13px;
}

.pcq-detail-value a {
    color: #2271b1;
    text-decoration: none;
}

.pcq-detail-value a:hover {
    text-decoration: underline;
}

.pcq-relative-time {
    color: #646970;
    font-style: italic;
}

/* Status Badges */
.pcq-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pcq-status-pending {
    background: #fcf3cf;
    color: #b7791f;
}

.pcq-status-confirmed {
    background: #d4edda;
    color: #155724;
}

.pcq-status-completed {
    background: #d1ecf1;
    color: #0c5460;
}

.pcq-status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.pcq-service-badge {
    display: inline-block;
    padding: 5px 12px;
    background: #e0e7ff;
    color: #3730a3;
    border-radius: 6px;
    font-weight: 600;
    font-size: 13px;
}

.pcq-service-details-table {
    width: 100%;
    border-collapse: collapse;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    font-size: 13px;
}

.pcq-service-details-table tr {
    border-bottom: 1px solid #f3f4f6;
}

.pcq-service-details-table tr:last-child {
    border-bottom: none;
}

.pcq-service-details-table tr:nth-child(even) {
    background: #f9fafb;
}

.pcq-service-details-table td {
    padding: 8px 12px;
    line-height: 1.5;
}

.pcq-detail-label {
    font-weight: 600;
    color: #374151;
    width: 40%;
    text-align: left;
}

.pcq-detail-data {
    color: #111827;
    text-align: left;
}

.pcq-appointment-link {
    color: #2271b1;
    text-decoration: none;
    font-weight: 600;
}

.pcq-appointment-link:hover {
    text-decoration: underline;
    color: #135e96;
}

@media screen and (max-width: 782px) {
    .pcq-booking-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .pcq-booking-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .pcq-booking-actions {
        width: 100%;
        flex-direction: column;
    }
    
    .pcq-booking-actions .button {
        width: 100%;
    }
    
    .pcq-details-grid {
        grid-template-columns: 1fr;
    }
    
    .pcq-detail-row {
        flex-direction: column;
        gap: 5px;
    }
    
    .pcq-detail-row label {
        flex: none;
    }
}
</style>
