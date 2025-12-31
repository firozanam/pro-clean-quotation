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

<div class="pcq-quote-success-container">
    
    <!-- Success Header -->
    <div class="pcq-success-header">
        <h2 class="pcq-success-title"><?php _e('Booking Confirmed Successfully!', 'pro-clean-quotation'); ?></h2>
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

<style>
.pcq-quote-success-container {
    max-width: 1140px;
    margin: 0 auto;
    padding: 0;
    background: #ffffff;
    border-radius: 4px;
    border: 1px solid #e8ebed;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.pcq-success-header {
    text-align: left;
    padding: 2rem 2rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
}

.pcq-success-title {
    color: #111827;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0 0 0.75rem 0;
    text-align: left;
}

.pcq-success-message {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0;
    line-height: 1.5;
    text-align: left;
}

.pcq-quote-details-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    padding: 1.5rem;
    margin: 2rem;
}

.pcq-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.pcq-detail-row:first-child {
    padding-top: 0;
}

.pcq-detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.pcq-detail-label {
    color: #111827;
    font-size: 0.9375rem;
    font-weight: 600;
}

.pcq-detail-value {
    color: #374151;
    font-size: 0.9375rem;
    font-weight: 600;
}

.pcq-price-row {
    background: transparent;
    margin: 0;
    padding: 0.75rem 0;
    border-top: none;
    border-bottom: none;
}

.pcq-price-row:last-child {
    border-bottom: none;
    padding-top: 0.875rem;
    padding-bottom: 0;
    border-top: 2px solid #60a5fa;
    margin-top: 0.5rem;
}

.pcq-price-highlight {
    color: #60a5fa;
    font-size: 1.125rem;
    font-weight: 600;
}

.pcq-quote-reference {
    margin: 0;
    padding: 1.5rem 2rem;
    border-top: 1px solid #f3f4f6;
}

.pcq-small-text {
    color: #6b7280;
    font-size: 0.8125rem;
    margin: 0;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .pcq-quote-success-container {
        padding: 0;
        margin: 1rem;
    }

    .pcq-success-header {
        padding: 1.5rem;
    }

    .pcq-quote-details-card {
        margin: 1.5rem;
    }

    .pcq-quote-reference {
        padding: 1.25rem 1.5rem;
    }

    .pcq-success-title {
        font-size: 1.25rem;
    }

    .pcq-detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}
</style>
