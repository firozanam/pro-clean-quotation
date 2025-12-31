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
    
    <!-- Success Header -->
    <div class="pcq-success-header">
        <h2 class="pcq-success-title"><?php _e('Quote Generated Successfully!', 'pro-clean-quotation'); ?></h2>
        <p class="pcq-success-message">
            <?php _e('A confirmation email has been sent to your email address.', 'pro-clean-quotation'); ?>
        </p>
    </div>

    <!-- Quote Details Card -->
    <div class="pcq-quote-details-card">
        <div class="pcq-detail-row">
            <span class="pcq-detail-label"><?php _e('Quote Number:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value"><?php echo esc_html($quote_number); ?></span>
        </div>
        
        <?php if ($valid_until): ?>
        <div class="pcq-detail-row">
            <span class="pcq-detail-label"><?php _e('Valid Until:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value"><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($valid_until))); ?></span>
        </div>
        <?php endif; ?>
        
        <div class="pcq-detail-row pcq-price-row">
            <span class="pcq-detail-label"><?php _e('Total Price:', 'pro-clean-quotation'); ?></span>
            <span class="pcq-detail-value pcq-price-highlight">€<?php echo number_format($total_price, 2); ?></span>
        </div>
    </div>

    <!-- CTA Buttons -->
    <div class="pcq-success-actions">
        <?php if ($booking_url): ?>
        <a href="<?php echo esc_url($booking_url); ?>" class="pcq-btn-primary pcq-btn-large">
            <?php _e('Book This Service', 'pro-clean-quotation'); ?>
        </a>
        <?php endif; ?>
    </div>

    <!-- Quote Reference -->
    <div class="pcq-quote-reference">
        <p class="pcq-small-text">
            <?php _e('This is an estimated quote. Final pricing may vary after on-site assessment. Quote valid for 30 days.', 'pro-clean-quotation'); ?>
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

.pcq-success-icon {
    margin-bottom: 0;
    display: none;
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

.pcq-next-steps {
    display: none;
}

.pcq-steps-list {
    display: none;
}

.pcq-success-actions {
    display: flex;
    gap: 0;
    justify-content: flex-start;
    flex-wrap: nowrap;
    margin: 0;
    padding: 2rem;
    border-top: 1px solid #f3f4f6;
}

.pcq-btn-primary,
.pcq-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.75rem 2.5rem;
    border-radius: 4px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
    border: none;
    margin-right: 0.75rem;
    box-sizing: border-box;
}

.pcq-btn-primary:last-child,
.pcq-btn-secondary:last-child {
    margin-right: 0;
}

.pcq-btn-large {
    padding: 0.75rem 2.5rem;
    font-size: 1rem;
}

.pcq-btn-primary {
    background: #5a8f3a !important;
    background-color: #5a8f3a !important;
    color: #ffffff !important;
    box-shadow: 0 2px 4px rgba(90, 143, 58, 0.2) !important;
}

.pcq-btn-primary:hover {
    background: #4a7a2e !important;
    background-color: #4a7a2e !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(90, 143, 58, 0.3) !important;
}

.pcq-btn-primary svg {
    display: none;
}

.pcq-btn-secondary {
    background: #ffffff;
    color: #374151;
    border: 1px solid #d1d5db;
}

.pcq-btn-secondary:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.pcq-btn-secondary svg {
    display: none;
}

.pcq-additional-info {
    display: none;
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

    .pcq-success-actions {
        padding: 1.5rem;
    }

    .pcq-quote-reference {
        padding: 1.25rem 1.5rem;
    }

    .pcq-success-title {
        font-size: 1.25rem;
    }

    .pcq-success-actions {
        flex-direction: column;
        gap: 0.75rem;
    }

    .pcq-btn-primary,
    .pcq-btn-secondary {
        width: 100%;
        margin-right: 0;
    }

    .pcq-detail-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.25rem;
    }
}

@media print {
    .pcq-success-actions,
    .pcq-btn-secondary {
        display: none !important;
    }
}
</style>
