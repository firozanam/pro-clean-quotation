<?php
/**
 * Booking Confirmation Page Template
 * 
 * Displayed after a successful booking creation
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
$deposit_required = isset($_GET['deposit_required']) && $_GET['deposit_required'] === 'true';
$deposit_amount = isset($_GET['deposit_amount']) ? floatval($_GET['deposit_amount']) : 0;
?>

<div class="pcq-booking-confirmation-container">
    
    <!-- Success Icon -->
    <div class="pcq-success-icon">
        <svg width="100" height="100" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="#4CAF50" stroke-width="2"/>
            <path d="M8 12L11 15L16 9" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    <!-- Confirmation Message -->
    <h2 class="pcq-confirmation-title"><?php _e('Booking Confirmed!', 'pro-clean-quotation'); ?></h2>
    <p class="pcq-confirmation-message">
        <?php _e('Your service booking has been successfully confirmed. A confirmation email has been sent to your email address.', 'pro-clean-quotation'); ?>
    </p>

    <!-- Booking Details Card -->
    <div class="pcq-booking-details-card">
        <h3 class="pcq-card-title"><?php _e('Booking Details', 'pro-clean-quotation'); ?></h3>
        
        <div class="pcq-detail-row">
            <span class="pcq-detail-label"><?php _e('Booking Reference:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value"><strong><?php echo esc_html($booking_number); ?></strong></span>
        </div>
        
        <div class="pcq-detail-row pcq-highlight-row">
            <span class="pcq-detail-label">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 5px;">
                    <rect x="3" y="4" width="18" height="18" rx="2" stroke="#2196F3" stroke-width="2"/>
                    <line x1="3" y1="9" x2="21" y2="9" stroke="#2196F3" stroke-width="2"/>
                </svg>
                <?php _e('Service Date:', 'pro-clean-quotation'); ?>
            </span>
            <span class="pcq-detail-value"><strong><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($service_date))); ?></strong></span>
        </div>
        
        <div class="pcq-detail-row pcq-highlight-row">
            <span class="pcq-detail-label">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 5px;">
                    <circle cx="12" cy="12" r="9" stroke="#2196F3" stroke-width="2"/>
                    <path d="M12 6v6l4 2" stroke="#2196F3" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <?php _e('Service Time:', 'pro-clean-quotation'); ?>
            </span>
            <span class="pcq-detail-value"><strong><?php echo esc_html($service_time); ?></strong></span>
        </div>
        
        <div class="pcq-detail-row pcq-price-row">
            <span class="pcq-detail-label"><?php _e('Total Amount:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value pcq-price-highlight">€<?php echo number_format($total_amount, 2); ?></span>
        </div>
        
        <?php if ($deposit_required): ?>
        <div class="pcq-detail-row pcq-deposit-row">
            <span class="pcq-detail-label"><?php _e('Deposit Required (20%):', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value">€<?php echo number_format($deposit_amount, 2); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Important Notice -->
    <div class="pcq-notice-box pcq-info-notice">
        <h4><?php _e('Important Information', 'pro-clean-quotation'); ?></h4>
        <ul class="pcq-notice-list">
            <li><?php _e('You will receive a reminder 24 hours before your scheduled service.', 'pro-clean-quotation'); ?></li>
            <li><?php _e('Please ensure access to the property is available at the scheduled time.', 'pro-clean-quotation'); ?></li>
            <li><?php _e('If you need to reschedule, please contact us at least 48 hours in advance.', 'pro-clean-quotation'); ?></li>
            <?php if ($deposit_required): ?>
            <li><?php _e('Payment instructions have been sent to your email address.', 'pro-clean-quotation'); ?></li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Action Buttons -->
    <div class="pcq-confirmation-actions">
        <button type="button" class="pcq-btn-primary" onclick="window.print();">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                <path d="M6 9V2h12v7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2"/>
                <rect x="4" y="9" width="16" height="5" stroke="currentColor" stroke-width="2"/>
            </svg>
            <?php _e('Print Confirmation', 'pro-clean-quotation'); ?>
        </button>
        
        <a href="<?php echo esc_url(home_url('/')); ?>" class="pcq-btn-secondary">
            <?php _e('Back to Home', 'pro-clean-quotation'); ?>
        </a>
    </div>

    <!-- Contact Information -->
    <div class="pcq-contact-info">
        <h4><?php _e('Questions? Contact Us', 'pro-clean-quotation'); ?></h4>
        <div class="pcq-contact-grid">
            <div class="pcq-contact-item">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" stroke="#2196F3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <strong><?php _e('Email', 'pro-clean-quotation'); ?></strong>
                    <a href="mailto:<?php echo esc_attr(get_option('pcq_company_email', 'info@proclean.nl')); ?>">
                        <?php echo esc_html(get_option('pcq_company_email', 'info@proclean.nl')); ?>
                    </a>
                </div>
            </div>
            
            <div class="pcq-contact-item">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" stroke="#2196F3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div>
                    <strong><?php _e('Phone', 'pro-clean-quotation'); ?></strong>
                    <a href="tel:<?php echo esc_attr(str_replace(' ', '', get_option('pcq_company_phone', '+31201234567'))); ?>">
                        <?php echo esc_html(get_option('pcq_company_phone', '+31 20 123 4567')); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Reference -->
    <div class="pcq-booking-reference">
        <p class="pcq-small-text">
            <?php _e('Please save your booking reference number:', 'pro-clean-quotation'); ?> 
            <strong class="pcq-reference-code"><?php echo esc_html($booking_number); ?></strong>
        </p>
    </div>

</div>

<style>
.pcq-booking-confirmation-container {
    max-width: 750px;
    margin: 40px auto;
    padding: 40px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    text-align: center;
}

.pcq-success-icon {
    margin-bottom: 25px;
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.pcq-confirmation-title {
    color: #4CAF50;
    font-size: 32px;
    margin-bottom: 15px;
    font-weight: 700;
}

.pcq-confirmation-message {
    color: #666;
    font-size: 17px;
    line-height: 1.6;
    margin-bottom: 35px;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.pcq-booking-details-card {
    background: #f8f9fa;
    border: 2px solid #4CAF50;
    border-radius: 8px;
    padding: 25px;
    margin: 30px 0;
    text-align: left;
}

.pcq-card-title {
    color: #333;
    font-size: 20px;
    margin-bottom: 20px;
    text-align: center;
    padding-bottom: 15px;
    border-bottom: 2px solid #e0e0e0;
}

.pcq-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 14px 0;
    border-bottom: 1px solid #e0e0e0;
}

.pcq-detail-row:last-child {
    border-bottom: none;
}

.pcq-highlight-row {
    background: #fff;
    margin: 0 -25px;
    padding: 14px 25px;
}

.pcq-detail-label {
    color: #666;
    font-size: 15px;
    display: flex;
    align-items: center;
}

.pcq-detail-value {
    color: #333;
    font-size: 16px;
}

.pcq-price-row {
    background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
    margin: 15px -25px 0;
    padding: 18px 25px;
    border-radius: 0 0 6px 6px;
    border: none;
}

.pcq-price-row .pcq-detail-label,
.pcq-price-row .pcq-detail-value {
    color: #fff;
}

.pcq-price-highlight {
    font-size: 26px;
    font-weight: bold;
}

.pcq-deposit-row {
    background: #fff3cd;
    margin: 10px -25px 0;
    padding: 14px 25px;
    border-left: 4px solid #ffc107;
}

.pcq-notice-box {
    border-radius: 6px;
    padding: 20px;
    margin: 30px 0;
    text-align: left;
}

.pcq-info-notice {
    background: #e3f2fd;
    border-left: 4px solid #2196F3;
}

.pcq-notice-box h4 {
    color: #1976D2;
    margin-top: 0;
    margin-bottom: 15px;
    font-size: 18px;
}

.pcq-notice-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.pcq-notice-list li {
    padding: 8px 0 8px 30px;
    position: relative;
    color: #555;
    line-height: 1.6;
}

.pcq-notice-list li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #2196F3;
    font-weight: bold;
    font-size: 18px;
}

.pcq-confirmation-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin: 35px 0;
}

.pcq-btn-primary,
.pcq-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 14px 28px;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
}

.pcq-btn-primary {
    background: #2196F3;
    color: #fff;
}

.pcq-btn-primary:hover {
    background: #1976D2;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);
}

.pcq-btn-secondary {
    background: #fff;
    color: #2196F3;
    border: 2px solid #2196F3;
}

.pcq-btn-secondary:hover {
    background: #f0f7ff;
}

.pcq-contact-info {
    background: #f8f9fa;
    border-radius: 6px;
    padding: 25px;
    margin: 30px 0;
}

.pcq-contact-info h4 {
    color: #333;
    margin-top: 0;
    margin-bottom: 20px;
    text-align: center;
}

.pcq-contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.pcq-contact-item {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.pcq-contact-item svg {
    flex-shrink: 0;
    margin-top: 3px;
}

.pcq-contact-item div {
    flex: 1;
}

.pcq-contact-item strong {
    display: block;
    color: #333;
    margin-bottom: 5px;
    font-size: 14px;
}

.pcq-contact-item a {
    color: #2196F3;
    text-decoration: none;
    font-size: 15px;
}

.pcq-contact-item a:hover {
    text-decoration: underline;
}

.pcq-booking-reference {
    margin-top: 35px;
    padding-top: 25px;
    border-top: 2px solid #e0e0e0;
}

.pcq-small-text {
    color: #999;
    font-size: 14px;
    margin: 0;
}

.pcq-reference-code {
    color: #2196F3;
    font-family: monospace;
    font-size: 16px;
    padding: 3px 8px;
    background: #e3f2fd;
    border-radius: 3px;
}

@media (max-width: 768px) {
    .pcq-booking-confirmation-container {
        padding: 30px 20px;
        margin: 20px;
    }

    .pcq-confirmation-title {
        font-size: 26px;
    }

    .pcq-confirmation-actions {
        flex-direction: column;
    }

    .pcq-btn-primary,
    .pcq-btn-secondary {
        width: 100%;
    }

    .pcq-detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }

    .pcq-highlight-row {
        margin: 0;
        padding: 14px;
    }

    .pcq-price-row {
        margin: 15px 0 0;
        padding: 18px 20px;
    }

    .pcq-deposit-row {
        margin: 10px 0 0;
        padding: 14px 20px;
    }

    .pcq-booking-details-card {
        padding: 20px 15px;
    }
}

@media print {
    .pcq-confirmation-actions {
        display: none !important;
    }
    
    .pcq-booking-confirmation-container {
        box-shadow: none;
        padding: 20px;
    }
}
</style>
