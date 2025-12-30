<?php
/**
 * Quote Success Page Template
 * 
 * Displayed after a successful quote submission with booking CTA
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

$quote_id = isset($_GET['quote_id']) ? intval($_GET['quote_id']) : 0;
$quote_number = isset($_GET['quote_number']) ? sanitize_text_field($_GET['quote_number']) : '';
$total_price = isset($_GET['total']) ? floatval($_GET['total']) : 0;
$valid_until = isset($_GET['valid_until']) ? sanitize_text_field($_GET['valid_until']) : '';
$booking_url = isset($_GET['booking_url']) ? esc_url_raw($_GET['booking_url']) : '';
?>

<div class="pcq-quote-success-container">
    
    <!-- Success Icon -->
    <div class="pcq-success-icon">
        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <circle cx="12" cy="12" r="10" stroke="#4CAF50" stroke-width="2"/>
            <path d="M8 12L11 15L16 9" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>

    <!-- Success Message -->
    <h2 class="pcq-success-title"><?php _e('Quote Generated Successfully!', 'pro-clean-quotation'); ?></h2>
    <p class="pcq-success-message">
        <?php _e('Your quote has been generated and sent to your email address.', 'pro-clean-quotation'); ?>
    </p>

    <!-- Quote Details Card -->
    <div class="pcq-quote-details-card">
        <div class="pcq-detail-row">
            <span class="pcq-detail-label"><?php _e('Quote Number:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value"><strong><?php echo esc_html($quote_number); ?></strong></span>
        </div>
        
        <div class="pcq-detail-row pcq-price-row">
            <span class="pcq-detail-label"><?php _e('Total Amount:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value pcq-price-highlight">€<?php echo number_format($total_price, 2); ?></span>
        </div>
        
        <?php if ($valid_until): ?>
        <div class="pcq-detail-row">
            <span class="pcq-detail-label"><?php _e('Valid Until:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($valid_until))); ?></span>
        </div>
        <?php endif; ?>
    </div>

    <!-- What's Next Section -->
    <div class="pcq-next-steps">
        <h3><?php _e('What is Next?', 'pro-clean-quotation'); ?></h3>
        <ol class="pcq-steps-list">
            <li>
                <strong><?php _e('Check Your Email', 'pro-clean-quotation'); ?></strong>
                <p><?php _e('We have sent a detailed quote to your email address with a PDF attachment.', 'pro-clean-quotation'); ?></p>
            </li>
            <li>
                <strong><?php _e('Review the Quote', 'pro-clean-quotation'); ?></strong>
                <p><?php _e('Take your time to review the service details and pricing breakdown.', 'pro-clean-quotation'); ?></p>
            </li>
            <li>
                <strong><?php _e('Book Your Service', 'pro-clean-quotation'); ?></strong>
                <p><?php _e('When you are ready, click the button below to select a date and time.', 'pro-clean-quotation'); ?></p>
            </li>
        </ol>
    </div>

    <!-- CTA Buttons -->
    <div class="pcq-success-actions">
        <?php if ($booking_url): ?>
        <a href="<?php echo esc_url($booking_url); ?>" class="pcq-btn-primary pcq-btn-large">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                <line x1="3" y1="9" x2="21" y2="9" stroke="currentColor" stroke-width="2"/>
                <line x1="9" y1="1" x2="9" y2="4" stroke="currentColor" stroke-width="2"/>
                <line x1="15" y1="1" x2="15" y2="4" stroke="currentColor" stroke-width="2"/>
            </svg>
            <?php _e('Book This Service Now', 'pro-clean-quotation'); ?>
        </a>
        <?php endif; ?>
        
        <button type="button" class="pcq-btn-secondary pcq-btn-large" onclick="window.print();">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="margin-right: 8px;">
                <path d="M6 9V2h12v7" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <rect x="6" y="14" width="12" height="8" stroke="currentColor" stroke-width="2"/>
                <rect x="4" y="9" width="16" height="5" stroke="currentColor" stroke-width="2"/>
            </svg>
            <?php _e('Print Quote', 'pro-clean-quotation'); ?>
        </button>
    </div>

    <!-- Additional Info -->
    <div class="pcq-additional-info">
        <h4><?php _e('Need Help?', 'pro-clean-quotation'); ?></h4>
        <p>
            <?php _e('If you have any questions about your quote, please do not hesitate to contact us.', 'pro-clean-quotation'); ?>
        </p>
        <p>
            <strong><?php _e('Phone:', 'pro-clean-quotation'); ?></strong> 
            <?php echo esc_html(get_option('pcq_company_phone', '+31 20 123 4567')); ?>
        </p>
        <p>
            <strong><?php _e('Email:', 'pro-clean-quotation'); ?></strong> 
            <a href="mailto:<?php echo esc_attr(get_option('pcq_company_email', 'info@proclean.nl')); ?>">
                <?php echo esc_html(get_option('pcq_company_email', 'info@proclean.nl')); ?>
            </a>
        </p>
    </div>

    <!-- Quote Reference -->
    <div class="pcq-quote-reference">
        <p class="pcq-small-text">
            <?php _e('Please reference quote number', 'pro-clean-quotation'); ?> 
            <strong><?php echo esc_html($quote_number); ?></strong> 
            <?php _e('in all correspondence.', 'pro-clean-quotation'); ?>
        </p>
    </div>

</div>

<style>
.pcq-quote-success-container {
    max-width: 700px;
    margin: 40px auto;
    padding: 40px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
}

.pcq-success-icon {
    margin-bottom: 20px;
}

.pcq-success-title {
    color: #333;
    font-size: 28px;
    margin-bottom: 10px;
}

.pcq-success-message {
    color: #666;
    font-size: 16px;
    margin-bottom: 30px;
}

.pcq-quote-details-card {
    background: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    padding: 20px;
    margin: 30px 0;
    text-align: left;
}

.pcq-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid #e0e0e0;
}

.pcq-detail-row:last-child {
    border-bottom: none;
}

.pcq-detail-label {
    color: #666;
    font-size: 14px;
}

.pcq-detail-value {
    color: #333;
    font-size: 16px;
}

.pcq-price-row {
    background: #fff;
    margin: 0 -20px;
    padding: 15px 20px;
    border-top: 2px solid #2196F3;
    border-bottom: 2px solid #2196F3;
}

.pcq-price-highlight {
    color: #2196F3;
    font-size: 24px;
    font-weight: bold;
}

.pcq-next-steps {
    margin: 40px 0;
    text-align: left;
}

.pcq-next-steps h3 {
    color: #333;
    font-size: 20px;
    margin-bottom: 20px;
}

.pcq-steps-list {
    list-style: none;
    counter-reset: step-counter;
    padding: 0;
}

.pcq-steps-list li {
    counter-increment: step-counter;
    position: relative;
    padding-left: 50px;
    margin-bottom: 25px;
}

.pcq-steps-list li::before {
    content: counter(step-counter);
    position: absolute;
    left: 0;
    top: 0;
    width: 35px;
    height: 35px;
    background: #2196F3;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
}

.pcq-steps-list li strong {
    display: block;
    color: #333;
    margin-bottom: 5px;
    font-size: 16px;
}

.pcq-steps-list li p {
    color: #666;
    font-size: 14px;
    margin: 0;
    line-height: 1.6;
}

.pcq-success-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin: 40px 0;
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

.pcq-btn-large {
    padding: 16px 32px;
    font-size: 18px;
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

.pcq-additional-info {
    background: #f8f9fa;
    border-left: 4px solid #2196F3;
    padding: 20px;
    margin: 40px 0;
    text-align: left;
}

.pcq-additional-info h4 {
    color: #333;
    margin-top: 0;
    margin-bottom: 15px;
}

.pcq-additional-info p {
    color: #666;
    margin-bottom: 10px;
    line-height: 1.6;
}

.pcq-additional-info a {
    color: #2196F3;
    text-decoration: none;
}

.pcq-additional-info a:hover {
    text-decoration: underline;
}

.pcq-quote-reference {
    margin-top: 40px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.pcq-small-text {
    color: #999;
    font-size: 13px;
    margin: 0;
}

@media (max-width: 768px) {
    .pcq-quote-success-container {
        padding: 30px 20px;
        margin: 20px;
    }

    .pcq-success-title {
        font-size: 24px;
    }

    .pcq-success-actions {
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
}

@media print {
    .pcq-success-actions,
    .pcq-btn-secondary {
        display: none !important;
    }
}
</style>
