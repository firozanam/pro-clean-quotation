<?php
/**
 * Booking Confirmation Template
 * 
 * Displayed after successful booking creation
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$booking_number = isset($_GET['booking_number']) ? sanitize_text_field($_GET['booking_number']) : '';
$service_date = isset($_GET['service_date']) ? sanitize_text_field($_GET['service_date']) : '';
$service_time = isset($_GET['service_time']) ? sanitize_text_field($_GET['service_time']) : '';
$total_amount = isset($_GET['total_amount']) ? floatval($_GET['total_amount']) : 0;
?>

<div class="pcq-confirmation-wrapper">
<div class="pcq-quote-success-container">
    
    <!-- Success Header -->
    <div class="pcq-success-header">
        <p class="pcq-success-title" style="font-size: 28px; font-weight: 700; margin-bottom: 12px;"><?php _e('Booking Confirmed Successfully!', 'pro-clean-quotation'); ?></p>
        <p class="pcq-success-message">
            <?php _e('Your service has been scheduled. A confirmation email has been sent to your email address.', 'pro-clean-quotation'); ?>
        </p>
    </div>

    <!-- Booking Details Card -->
    <div class="pcq-quote-details-card">
        <div class="pcq-detail-row">
            <span class="pcq-detail-label"><?php _e('Booking Number:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value"><?php echo esc_html($booking_number); ?></span>
        </div>
        
        <?php if ($service_date): ?>
        <div class="pcq-detail-row">
            <span class="pcq-detail-label"><?php _e('Service Date:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($service_date))); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($service_time): ?>
        <div class="pcq-detail-row">
            <span class="pcq-detail-label"><?php _e('Time:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value"><?php echo esc_html($service_time); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if ($total_amount > 0): ?>
        <div class="pcq-detail-row pcq-price-row">
            <span class="pcq-detail-label"><?php _e('Total Amount:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value pcq-price-highlight">€<?php echo number_format($total_amount, 2); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Quote Reference -->
    <div class="pcq-quote-reference">
        <p class="pcq-small-text">
            <?php _e('We will contact you 24 hours before your scheduled service to confirm details. If you need to make changes, please contact us as soon as possible.', 'pro-clean-quotation'); ?>
        </p>
    </div>

</div>
</div>
