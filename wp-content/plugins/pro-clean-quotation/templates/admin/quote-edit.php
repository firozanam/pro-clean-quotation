<?php
/**
 * Admin Quote Edit Template
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
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Edit Quote', 'pro-clean-quotation'); ?></h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote->getId()); ?>" class="page-title-action">
        <?php _e('Back to Quote', 'pro-clean-quotation'); ?>
    </a>
    
    <form method="post" action="" class="pcq-quote-form">
        <?php wp_nonce_field('pcq_save_quote_' . $quote->getId(), '_wpnonce'); ?>
        <input type="hidden" name="quote_id" value="<?php echo $quote->getId(); ?>">
        <input type="hidden" name="action" value="save_quote">
        
        <div class="pcq-form-container">
            <!-- Main Form -->
            <div class="pcq-main-form">
                <!-- Customer Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Customer Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="customer_name"><?php _e('Customer Name', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" name="customer_name" id="customer_name" 
                                       value="<?php echo esc_attr($quote->getCustomerName()); ?>" 
                                       class="regular-text" required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="customer_email"><?php _e('Email Address', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="email" name="customer_email" id="customer_email" 
                                       value="<?php echo esc_attr($quote->getCustomerEmail()); ?>" 
                                       class="regular-text" required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="customer_phone"><?php _e('Phone Number', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="tel" name="customer_phone" id="customer_phone" 
                                       value="<?php echo esc_attr($quote->getCustomerPhone()); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="property_address"><?php _e('Property Address', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <textarea name="property_address" id="property_address" rows="3" class="large-text" required><?php echo esc_textarea($quote_data['property_address']); ?></textarea>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="postal_code"><?php _e('Postal Code', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="postal_code" id="postal_code" 
                                       value="<?php echo esc_attr($quote_data['postal_code']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Service Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Service Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="service_type"><?php _e('Service Type', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <select name="service_type" id="service_type" class="regular-text" required>
                                    <option value=""><?php _e('Select Service Type', 'pro-clean-quotation'); ?></option>
                                    <!-- New format (from quote form) -->
                                    <option value="facade" <?php selected($quote->getServiceType(), 'facade'); ?>><?php _e('Façade Cleaning', 'pro-clean-quotation'); ?></option>
                                    <option value="roof" <?php selected($quote->getServiceType(), 'roof'); ?>><?php _e('Roof Cleaning', 'pro-clean-quotation'); ?></option>
                                    <option value="both" <?php selected($quote->getServiceType(), 'both'); ?>><?php _e('Both Services', 'pro-clean-quotation'); ?></option>
                                    <!-- Legacy format (backward compatibility) -->
                                    <option value="facade_cleaning" <?php selected($quote->getServiceType(), 'facade_cleaning'); ?>><?php _e('Façade Cleaning (Legacy)', 'pro-clean-quotation'); ?></option>
                                    <option value="roof_cleaning" <?php selected($quote->getServiceType(), 'roof_cleaning'); ?>><?php _e('Roof Cleaning (Legacy)', 'pro-clean-quotation'); ?></option>
                                    <option value="complete_package" <?php selected($quote->getServiceType(), 'complete_package'); ?>><?php _e('Complete Package (Legacy)', 'pro-clean-quotation'); ?></option>
                                    <option value="window_cleaning" <?php selected($quote->getServiceType(), 'window_cleaning'); ?>><?php _e('Window Cleaning (Legacy)', 'pro-clean-quotation'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="square_meters"><?php _e('Square Meters', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="number" name="square_meters" id="square_meters" 
                                       value="<?php echo esc_attr($quote_data['square_meters']); ?>" 
                                       class="regular-text" step="0.1" min="0" required>
                                <span>m²</span>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="linear_meters"><?php _e('Linear Meters', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="linear_meters" id="linear_meters" 
                                       value="<?php echo esc_attr($quote_data['linear_meters']); ?>" 
                                       class="regular-text" step="0.1" min="0">
                                <span>m</span>
                                <p class="description"><?php _e('For window cleaning or linear measurements.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="building_height"><?php _e('Building Height', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="building_height" id="building_height" 
                                       value="<?php echo esc_attr($quote_data['building_height']); ?>" 
                                       class="small-text" min="1" max="20">
                                <span><?php _e('floors', 'pro-clean-quotation'); ?></span>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="property_type"><?php _e('Property Type', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <select name="property_type" id="property_type" class="regular-text">
                                    <option value=""><?php _e('Select Property Type', 'pro-clean-quotation'); ?></option>
                                    <option value="residential" <?php selected($quote_data['property_type'], 'residential'); ?>><?php _e('Residential', 'pro-clean-quotation'); ?></option>
                                    <option value="commercial" <?php selected($quote_data['property_type'], 'commercial'); ?>><?php _e('Commercial', 'pro-clean-quotation'); ?></option>
                                    <option value="industrial" <?php selected($quote_data['property_type'], 'industrial'); ?>><?php _e('Industrial', 'pro-clean-quotation'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="surface_material"><?php _e('Surface Material', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <select name="surface_material" id="surface_material" class="regular-text">
                                    <option value=""><?php _e('Select Surface Material', 'pro-clean-quotation'); ?></option>
                                    <option value="brick" <?php selected($quote_data['surface_material'], 'brick'); ?>><?php _e('Brick', 'pro-clean-quotation'); ?></option>
                                    <option value="concrete" <?php selected($quote_data['surface_material'], 'concrete'); ?>><?php _e('Concrete', 'pro-clean-quotation'); ?></option>
                                    <option value="wood" <?php selected($quote_data['surface_material'], 'wood'); ?>><?php _e('Wood', 'pro-clean-quotation'); ?></option>
                                    <option value="metal" <?php selected($quote_data['surface_material'], 'metal'); ?>><?php _e('Metal', 'pro-clean-quotation'); ?></option>
                                    <option value="glass" <?php selected($quote_data['surface_material'], 'glass'); ?>><?php _e('Glass', 'pro-clean-quotation'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="roof_type"><?php _e('Roof Type', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <select name="roof_type" id="roof_type" class="regular-text">
                                    <option value=""><?php _e('Select Roof Type', 'pro-clean-quotation'); ?></option>
                                    <option value="tile" <?php selected($quote_data['roof_type'], 'tile'); ?>><?php _e('Tile', 'pro-clean-quotation'); ?></option>
                                    <option value="metal" <?php selected($quote_data['roof_type'], 'metal'); ?>><?php _e('Metal', 'pro-clean-quotation'); ?></option>
                                    <option value="shingle" <?php selected($quote_data['roof_type'], 'shingle'); ?>><?php _e('Shingle', 'pro-clean-quotation'); ?></option>
                                    <option value="flat" <?php selected($quote_data['roof_type'], 'flat'); ?>><?php _e('Flat', 'pro-clean-quotation'); ?></option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="last_cleaning_date"><?php _e('Last Cleaning Date', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="date" name="last_cleaning_date" id="last_cleaning_date" 
                                       value="<?php echo esc_attr($quote_data['last_cleaning_date']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="special_requirements"><?php _e('Special Requirements', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <textarea name="special_requirements" id="special_requirements" rows="4" class="large-text"><?php echo esc_textarea($quote_data['special_requirements']); ?></textarea>
                                <p class="description"><?php _e('Any special requirements or notes for this cleaning job.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Pricing -->
                <div class="pcq-form-section">
                    <h2><?php _e('Pricing', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="base_price"><?php _e('Base Price', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="number" name="base_price" id="base_price" 
                                       value="<?php echo esc_attr($quote->getBasePrice()); ?>" 
                                       class="regular-text" step="0.01" min="0" required>
                                <span>€</span>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="adjustments"><?php _e('Adjustments', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="adjustments" id="adjustments" 
                                       value="<?php echo esc_attr($quote->getAdjustments()); ?>" 
                                       class="regular-text" step="0.01">
                                <span>€</span>
                                <p class="description"><?php _e('Additional charges or discounts (use negative values for discounts).', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="tax_amount"><?php _e('Tax Amount', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="tax_amount" id="tax_amount" 
                                       value="<?php echo esc_attr($quote->getTaxAmount()); ?>" 
                                       class="regular-text" step="0.01" min="0">
                                <span>€</span>
                                <p class="description"><?php _e('Tax amount (usually 21% VAT).', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Price Calculation Display -->
                    <div class="pcq-price-calculation">
                        <h4><?php _e('Price Calculation', 'pro-clean-quotation'); ?></h4>
                        <div class="pcq-calc-row">
                            <span><?php _e('Base Price:', 'pro-clean-quotation'); ?></span>
                            <span id="calc-base">€<?php echo number_format($quote->getBasePrice(), 2); ?></span>
                        </div>
                        <div class="pcq-calc-row">
                            <span><?php _e('Adjustments:', 'pro-clean-quotation'); ?></span>
                            <span id="calc-adjustments">€<?php echo number_format($quote->getAdjustments(), 2); ?></span>
                        </div>
                        <div class="pcq-calc-row">
                            <span><?php _e('Subtotal:', 'pro-clean-quotation'); ?></span>
                            <span id="calc-subtotal">€<?php echo number_format($quote->getSubtotal(), 2); ?></span>
                        </div>
                        <div class="pcq-calc-row">
                            <span><?php _e('Tax:', 'pro-clean-quotation'); ?></span>
                            <span id="calc-tax">€<?php echo number_format($quote->getTaxAmount(), 2); ?></span>
                        </div>
                        <div class="pcq-calc-row pcq-total">
                            <span><?php _e('Total:', 'pro-clean-quotation'); ?></span>
                            <span id="calc-total">€<?php echo number_format($quote->getTotalPrice(), 2); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="pcq-form-sidebar">
                <!-- Status -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Quote Status', 'pro-clean-quotation'); ?></h3>
                    
                    <select name="status" id="status" class="widefat">
                        <option value="new" <?php selected($quote->getStatus(), 'new'); ?>><?php _e('New', 'pro-clean-quotation'); ?></option>
                        <option value="reviewed" <?php selected($quote->getStatus(), 'reviewed'); ?>><?php _e('Reviewed', 'pro-clean-quotation'); ?></option>
                        <option value="approved" <?php selected($quote->getStatus(), 'approved'); ?>><?php _e('Approved', 'pro-clean-quotation'); ?></option>
                        <option value="converted" <?php selected($quote->getStatus(), 'converted'); ?>><?php _e('Converted', 'pro-clean-quotation'); ?></option>
                        <option value="expired" <?php selected($quote->getStatus(), 'expired'); ?>><?php _e('Expired', 'pro-clean-quotation'); ?></option>
                    </select>
                </div>
                
                <!-- Valid Until -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Valid Until', 'pro-clean-quotation'); ?></h3>
                    
                    <input type="date" name="valid_until" id="valid_until" 
                           value="<?php echo esc_attr($quote_data['valid_until']); ?>" 
                           class="widefat">
                </div>
                
                <!-- Actions -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Actions', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-actions">
                        <input type="submit" name="save" class="button button-primary button-large" 
                               value="<?php _e('Update Quote', 'pro-clean-quotation'); ?>">
                        
                        <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote->getId()); ?>" 
                           class="button button-large">
                            <?php _e('Cancel', 'pro-clean-quotation'); ?>
                        </a>
                    </div>
                </div>
                
                <!-- Information -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-info-list">
                        <div class="pcq-info-item">
                            <strong><?php _e('Quote Number:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo esc_html($quote->getQuoteNumber()); ?>
                        </div>
                        
                        <div class="pcq-info-item">
                            <strong><?php _e('Created:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($quote->getCreatedAt())); ?>
                        </div>
                        
                        <?php if ($quote->getUpdatedAt()): ?>
                        <div class="pcq-info-item">
                            <strong><?php _e('Last Updated:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($quote->getUpdatedAt())); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.pcq-quote-form {
    margin-top: 20px;
}

.pcq-form-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.pcq-form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-form-section h2 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 8px;
}

.pcq-sidebar-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.pcq-sidebar-section h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pcq-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pcq-price-calculation {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-top: 20px;
}

.pcq-price-calculation h4 {
    margin: 0 0 15px 0;
    color: #2c3e50;
}

.pcq-calc-row {
    display: flex;
    justify-content: space-between;
    padding: 5px 0;
    border-bottom: 1px solid #e0e0e0;
}

.pcq-calc-row:last-child {
    border-bottom: none;
}

.pcq-total {
    border-top: 2px solid #27ae60;
    margin-top: 10px;
    padding-top: 10px;
    font-weight: bold;
    font-size: 16px;
    color: #27ae60;
}

.pcq-info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.pcq-info-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 13px;
}

.required {
    color: #d63638;
}

@media (max-width: 768px) {
    .pcq-form-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Auto-calculate pricing
    function updateCalculation() {
        var basePrice = parseFloat($('#base_price').val()) || 0;
        var adjustments = parseFloat($('#adjustments').val()) || 0;
        var taxAmount = parseFloat($('#tax_amount').val()) || 0;
        
        var subtotal = basePrice + adjustments;
        var total = subtotal + taxAmount;
        
        $('#calc-base').text('€' + basePrice.toFixed(2));
        $('#calc-adjustments').text('€' + adjustments.toFixed(2));
        $('#calc-subtotal').text('€' + subtotal.toFixed(2));
        $('#calc-tax').text('€' + taxAmount.toFixed(2));
        $('#calc-total').text('€' + total.toFixed(2));
    }
    
    // Update calculation when prices change
    $('#base_price, #adjustments, #tax_amount').on('input', updateCalculation);
    
    // Auto-calculate tax when subtotal changes
    $('#base_price, #adjustments').on('input', function() {
        var basePrice = parseFloat($('#base_price').val()) || 0;
        var adjustments = parseFloat($('#adjustments').val()) || 0;
        var subtotal = basePrice + adjustments;
        var taxAmount = subtotal * 0.21; // 21% VAT
        
        $('#tax_amount').val(taxAmount.toFixed(2));
        updateCalculation();
    });
    
    // Form validation
    $('form.pcq-quote-form').on('submit', function(e) {
        var isValid = true;
        
        // Check required fields
        $(this).find('[required]').each(function() {
            var field = $(this);
            var value = field.val().trim();
            
            if (!value) {
                field.addClass('error');
                isValid = false;
            } else {
                field.removeClass('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('<?php _e('Please fill in all required fields.', 'pro-clean-quotation'); ?>');
        }
    });
});
</script>