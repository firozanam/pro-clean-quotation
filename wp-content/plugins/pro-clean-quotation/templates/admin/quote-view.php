<?php
/**
 * Admin Quote View Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * @var \ProClean\Quotation\Models\Quote $quote Quote object passed from controller
 */

// Get quote data
$quote_data = $quote->toArray();
$price_breakdown = json_decode($quote_data['price_breakdown'] ?? '{}', true);
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Quote Details', 'pro-clean-quotation'); ?></h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-quotes'); ?>" class="page-title-action">
        <?php _e('Back to Quotes', 'pro-clean-quotation'); ?>
    </a>
    
    <div class="pcq-quote-view">
        <!-- Quote Header -->
        <div class="pcq-quote-header">
            <div class="pcq-quote-meta">
                <div class="pcq-quote-number">
                    <strong><?php _e('Quote #', 'pro-clean-quotation'); ?><?php echo esc_html($quote->getQuoteNumber()); ?></strong>
                </div>
                <div class="pcq-quote-status">
                    <span class="pcq-status pcq-status-<?php echo esc_attr($quote->getStatus()); ?>">
                        <?php 
                        $status_labels = [
                            'new' => __('New', 'pro-clean-quotation'),
                            'reviewed' => __('Reviewed', 'pro-clean-quotation'),
                            'approved' => __('Approved', 'pro-clean-quotation'),
                            'converted' => __('Converted', 'pro-clean-quotation'),
                            'expired' => __('Expired', 'pro-clean-quotation')
                        ];
                        echo $status_labels[$quote->getStatus()] ?? ucfirst($quote->getStatus());
                        ?>
                    </span>
                </div>
                <div class="pcq-quote-total">
                    <span class="pcq-total-amount">€<?php echo number_format($quote->getTotalPrice(), 2); ?></span>
                </div>
            </div>
            
            <div class="pcq-quote-actions">
                <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=edit&id=' . $quote->getId()); ?>" 
                   class="button button-primary">
                    <?php _e('Edit Quote', 'pro-clean-quotation'); ?>
                </a>
                
                <?php if (in_array($quote->getStatus(), ['new', 'reviewed'])): ?>
                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-quotes&action=approve&id=' . $quote->getId()), 'approve_quote_' . $quote->getId()); ?>" 
                       class="button button-secondary">
                        <?php _e('Approve Quote', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
                
                <?php if (in_array($quote->getStatus(), ['approved'])): ?>
                    <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=add&quote_id=' . $quote->getId()); ?>" 
                       class="button button-secondary">
                        <?php _e('Create Appointment', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
                
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-quotes&action=delete&id=' . $quote->getId()), 'delete_quote_' . $quote->getId()); ?>" 
                   class="button pcq-delete-btn" 
                   onclick="return confirm('<?php _e('Are you sure you want to delete this quote?', 'pro-clean-quotation'); ?>')">
                    <?php _e('Delete', 'pro-clean-quotation'); ?>
                </a>
            </div>
        </div>
        
        <!-- Quote Details -->
        <div class="pcq-quote-details">
            <div class="pcq-details-grid">
                <!-- Customer Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Customer Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Name:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo esc_html($quote->getCustomerName()); ?>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Email:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <a href="mailto:<?php echo esc_attr($quote->getCustomerEmail()); ?>">
                                <?php echo esc_html($quote->getCustomerEmail()); ?>
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($quote->getCustomerPhone()): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Phone:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <a href="tel:<?php echo esc_attr($quote->getCustomerPhone()); ?>">
                                <?php echo esc_html($quote->getCustomerPhone()); ?>
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Property Address:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo esc_html($quote_data['property_address']); ?>
                            <?php if ($quote_data['postal_code']): ?>
                                <br><small><?php echo esc_html($quote_data['postal_code']); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Service Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Service Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Service Type:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <span class="pcq-service-badge pcq-service-<?php echo esc_attr($quote->getServiceType()); ?>">
                                <?php 
                                // Support both old and new service type formats
                                $service_type = $quote->getServiceType();
                                $service_labels = [
                                    // New format (from quote form)
                                    'facade' => __('Façade Cleaning', 'pro-clean-quotation'),
                                    'roof' => __('Roof Cleaning', 'pro-clean-quotation'),
                                    'both' => __('Both Services', 'pro-clean-quotation'),
                                    // Legacy format (backward compatibility)
                                    'facade_cleaning' => __('Façade Cleaning', 'pro-clean-quotation'),
                                    'roof_cleaning' => __('Roof Cleaning', 'pro-clean-quotation'),
                                    'complete_package' => __('Complete Package', 'pro-clean-quotation'),
                                    'window_cleaning' => __('Window Cleaning', 'pro-clean-quotation')
                                ];
                                echo $service_labels[$service_type] ?? ucfirst(str_replace('_', ' ', $service_type));
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Area:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo number_format($quote_data['square_meters'], 1); ?> m²
                            <?php if ($quote_data['linear_meters']): ?>
                                <br><small><?php echo number_format($quote_data['linear_meters'], 1); ?> <?php _e('linear meters', 'pro-clean-quotation'); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($quote_data['building_height']): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Building Height:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo $quote_data['building_height']; ?> <?php _e('floors', 'pro-clean-quotation'); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote_data['property_type']): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Property Type:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo ucfirst($quote_data['property_type']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote_data['surface_material']): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Surface Material:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo ucfirst($quote_data['surface_material']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Quote Information -->
                <div class="pcq-detail-section">
                    <h3><?php _e('Quote Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Created:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($quote->getCreatedAt())); ?>
                            <small class="pcq-relative-time">
                                (<?php echo human_time_diff(strtotime($quote->getCreatedAt())); ?> ago)
                            </small>
                        </div>
                    </div>
                    
                    <div class="pcq-detail-row">
                        <label><?php _e('Valid Until:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php 
                            $valid_until = $quote_data['valid_until'];
                            $is_expired = strtotime($valid_until) < time();
                            ?>
                            <span class="<?php echo $is_expired ? 'pcq-expired' : 'pcq-valid'; ?>">
                                <?php echo date('F j, Y', strtotime($valid_until)); ?>
                                <?php if ($is_expired): ?>
                                    <small>(<?php _e('Expired', 'pro-clean-quotation'); ?>)</small>
                                <?php else: ?>
                                    <small>(<?php echo human_time_diff(time(), strtotime($valid_until)); ?> remaining)</small>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                    
                    <?php if ($quote->getUpdatedAt()): ?>
                    <div class="pcq-detail-row">
                        <label><?php _e('Last Updated:', 'pro-clean-quotation'); ?></label>
                        <div class="pcq-detail-value">
                            <?php echo date('F j, Y \a\t g:i A', strtotime($quote->getUpdatedAt())); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Pricing Breakdown -->
            <div class="pcq-pricing-section">
                <h3><?php _e('Pricing Breakdown', 'pro-clean-quotation'); ?></h3>
                
                <div class="pcq-pricing-table">
                    <div class="pcq-pricing-row">
                        <span class="pcq-pricing-label"><?php _e('Base Price:', 'pro-clean-quotation'); ?></span>
                        <span class="pcq-pricing-value">€<?php echo number_format($quote->getBasePrice(), 2); ?></span>
                    </div>
                    
                    <?php if ($quote->getAdjustments() != 0): ?>
                    <div class="pcq-pricing-row">
                        <span class="pcq-pricing-label">
                            <?php echo $quote->getAdjustments() > 0 ? __('Additional Charges:', 'pro-clean-quotation') : __('Discount:', 'pro-clean-quotation'); ?>
                        </span>
                        <span class="pcq-pricing-value <?php echo $quote->getAdjustments() > 0 ? 'pcq-positive' : 'pcq-negative'; ?>">
                            <?php echo $quote->getAdjustments() > 0 ? '+' : ''; ?>€<?php echo number_format($quote->getAdjustments(), 2); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="pcq-pricing-row pcq-subtotal">
                        <span class="pcq-pricing-label"><?php _e('Subtotal:', 'pro-clean-quotation'); ?></span>
                        <span class="pcq-pricing-value">€<?php echo number_format($quote->getSubtotal(), 2); ?></span>
                    </div>
                    
                    <?php if ($quote->getTaxAmount() > 0): ?>
                    <div class="pcq-pricing-row">
                        <span class="pcq-pricing-label"><?php _e('Tax (21%):', 'pro-clean-quotation'); ?></span>
                        <span class="pcq-pricing-value">€<?php echo number_format($quote->getTaxAmount(), 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="pcq-pricing-row pcq-total">
                        <span class="pcq-pricing-label"><?php _e('Total Price:', 'pro-clean-quotation'); ?></span>
                        <span class="pcq-pricing-value">€<?php echo number_format($quote->getTotalPrice(), 2); ?></span>
                    </div>
                </div>
                
                <?php if (!empty($price_breakdown)): ?>
                <div class="pcq-calculation-details">
                    <h4><?php _e('Calculation Details', 'pro-clean-quotation'); ?></h4>
                    <div class="pcq-calculation-grid">
                        <?php if (isset($price_breakdown['base_rate'])): ?>
                        <div class="pcq-calc-item">
                            <label><?php _e('Base Rate:', 'pro-clean-quotation'); ?></label>
                            <span>€<?php echo number_format($price_breakdown['base_rate'], 2); ?> per <?php echo $quote->getServiceType() === 'window_cleaning' ? 'm' : 'm²'; ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($price_breakdown['area_multiplier'])): ?>
                        <div class="pcq-calc-item">
                            <label><?php _e('Area:', 'pro-clean-quotation'); ?></label>
                            <span><?php echo number_format($price_breakdown['area_multiplier'], 1); ?> <?php echo $quote->getServiceType() === 'window_cleaning' ? 'm' : 'm²'; ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (isset($price_breakdown['difficulty_adjustment'])): ?>
                        <div class="pcq-calc-item">
                            <label><?php _e('Difficulty Adjustment:', 'pro-clean-quotation'); ?></label>
                            <span>€<?php echo number_format($price_breakdown['difficulty_adjustment'], 2); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Special Requirements -->
            <?php if ($quote_data['special_requirements']): ?>
            <div class="pcq-requirements-section">
                <h3><?php _e('Special Requirements', 'pro-clean-quotation'); ?></h3>
                <div class="pcq-requirements-content">
                    <?php echo nl2br(esc_html($quote_data['special_requirements'])); ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Additional Information -->
            <?php if ($quote_data['last_cleaning_date'] || $quote_data['roof_type']): ?>
            <div class="pcq-additional-section">
                <h3><?php _e('Additional Information', 'pro-clean-quotation'); ?></h3>
                
                <div class="pcq-additional-grid">
                    <?php if ($quote_data['last_cleaning_date']): ?>
                    <div class="pcq-additional-item">
                        <label><?php _e('Last Cleaning:', 'pro-clean-quotation'); ?></label>
                        <span><?php echo date('F j, Y', strtotime($quote_data['last_cleaning_date'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($quote_data['roof_type']): ?>
                    <div class="pcq-additional-item">
                        <label><?php _e('Roof Type:', 'pro-clean-quotation'); ?></label>
                        <span><?php echo ucfirst($quote_data['roof_type']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Consent Information -->
            <div class="pcq-consent-section">
                <h3><?php _e('Consent & Privacy', 'pro-clean-quotation'); ?></h3>
                
                <div class="pcq-consent-grid">
                    <div class="pcq-consent-item">
                        <label><?php _e('Marketing Consent:', 'pro-clean-quotation'); ?></label>
                        <span class="pcq-consent-<?php echo $quote_data['marketing_consent'] ? 'yes' : 'no'; ?>">
                            <?php echo $quote_data['marketing_consent'] ? __('Yes', 'pro-clean-quotation') : __('No', 'pro-clean-quotation'); ?>
                        </span>
                    </div>
                    
                    <div class="pcq-consent-item">
                        <label><?php _e('Privacy Consent:', 'pro-clean-quotation'); ?></label>
                        <span class="pcq-consent-<?php echo $quote_data['privacy_consent'] ? 'yes' : 'no'; ?>">
                            <?php echo $quote_data['privacy_consent'] ? __('Yes', 'pro-clean-quotation') : __('No', 'pro-clean-quotation'); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pcq-quote-view {
    margin-top: 20px;
}

.pcq-quote-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-quote-meta {
    display: flex;
    gap: 20px;
    align-items: center;
}

.pcq-quote-number {
    font-size: 18px;
    color: #2c3e50;
}

.pcq-total-amount {
    font-size: 24px;
    font-weight: bold;
    color: #27ae60;
}

.pcq-quote-actions {
    display: flex;
    gap: 10px;
}

.pcq-delete-btn {
    background-color: #f44336 !important;
    color: #fff !important;
    border-color: #f44336 !important;
}

.pcq-quote-details {
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
    min-width: 120px;
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

.pcq-status {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.pcq-status-new { background-color: #e3f2fd; color: #1976d2; }
.pcq-status-reviewed { background-color: #fff3e0; color: #f57c00; }
.pcq-status-approved { background-color: #e8f5e8; color: #388e3c; }
.pcq-status-converted { background-color: #f3e5f5; color: #7b1fa2; }
.pcq-status-expired { background-color: #ffebee; color: #d32f2f; }

.pcq-service-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    color: #fff;
    background-color: #2196F3;
}

.pcq-expired { color: #d32f2f; }
.pcq-valid { color: #388e3c; }

.pcq-pricing-section {
    border-top: 1px solid #e0e0e0;
    padding-top: 20px;
    margin-top: 20px;
}

.pcq-pricing-section h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    border-bottom: 2px solid #27ae60;
    padding-bottom: 8px;
}

.pcq-pricing-table {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-pricing-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e0e0e0;
}

.pcq-pricing-row:last-child {
    border-bottom: none;
}

.pcq-pricing-label {
    font-weight: 500;
    color: #555;
}

.pcq-pricing-value {
    font-weight: 500;
    color: #2c3e50;
}

.pcq-positive { color: #d32f2f; }
.pcq-negative { color: #388e3c; }

.pcq-subtotal {
    border-top: 1px solid #ccc;
    margin-top: 10px;
    padding-top: 15px;
}

.pcq-total {
    border-top: 2px solid #27ae60;
    margin-top: 10px;
    padding-top: 15px;
    font-size: 18px;
}

.pcq-total .pcq-pricing-value {
    color: #27ae60;
    font-size: 20px;
}

.pcq-calculation-details h4 {
    margin: 0 0 15px 0;
    color: #555;
    font-size: 14px;
}

.pcq-calculation-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.pcq-calc-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 12px;
    background: #fff;
    border-radius: 4px;
    border: 1px solid #e0e0e0;
}

.pcq-calc-item label {
    font-weight: 500;
    color: #666;
}

.pcq-requirements-section,
.pcq-additional-section,
.pcq-consent-section {
    border-top: 1px solid #e0e0e0;
    padding-top: 20px;
    margin-top: 20px;
}

.pcq-requirements-section h3,
.pcq-additional-section h3,
.pcq-consent-section h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    border-bottom: 2px solid #9C27B0;
    padding-bottom: 8px;
}

.pcq-requirements-content {
    background: #f8f9fa;
    border-radius: 4px;
    padding: 15px;
    border-left: 4px solid #9C27B0;
}

.pcq-additional-grid,
.pcq-consent-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.pcq-additional-item,
.pcq-consent-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 15px;
    background: #f8f9fa;
    border-radius: 4px;
    border: 1px solid #e0e0e0;
}

.pcq-additional-item label,
.pcq-consent-item label {
    font-weight: 500;
    color: #666;
}

.pcq-consent-yes { color: #388e3c; font-weight: 500; }
.pcq-consent-no { color: #d32f2f; font-weight: 500; }

@media (max-width: 768px) {
    .pcq-quote-header {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }
    
    .pcq-quote-meta {
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
    
    .pcq-calculation-grid,
    .pcq-additional-grid,
    .pcq-consent-grid {
        grid-template-columns: 1fr;
    }
}
</style>