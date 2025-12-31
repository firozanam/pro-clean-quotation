<?php
/**
 * Quote Form Template
 * 
 * This template can be overridden by copying it to:
 * yourtheme/pro-clean-quotation/quote-form.php
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

// Get settings
use ProClean\Quotation\Admin\Settings;

$form_id = 'pcq-quote-form-' . uniqid();
$show_title = isset($atts['show_title']) && $atts['show_title'] === 'true';
$columns = isset($atts['columns']) ? intval($atts['columns']) : 2;
$column_class = $columns === 1 ? 'pcq-single-column' : 'pcq-two-columns';
$style = isset($atts['style']) ? $atts['style'] : 'default';
$title = isset($atts['title']) ? $atts['title'] : __('Get Your Free Quote', 'pro-clean-quotation');
?>

<div class="pcq-quote-form-container pcq-style-<?php echo esc_attr($style); ?>">
    
    <?php if ($show_title): ?>
        <h3 class="pcq-form-title"><?php echo esc_html($title); ?></h3>
        <p class="pcq-form-subtitle"><?php _e('Fill out the form below to receive an instant price estimate for your cleaning service.', 'pro-clean-quotation'); ?></p>
    <?php endif; ?>

    <form id="<?php echo esc_attr($form_id); ?>" class="pcq-quote-form <?php echo esc_attr($column_class); ?>" method="post">
        
        <!-- Service Selection -->
        <div class="pcq-form-section pcq-service-selection">
            <h4><?php _e('Service Type', 'pro-clean-quotation'); ?> <span class="required">*</span></h4>
            <div class="pcq-radio-group">
                <label class="pcq-radio-label">
                    <input type="radio" name="service_type" value="facade" required>
                    <span class="pcq-radio-text">
                        <strong><?php _e('Façade Cleaning', 'pro-clean-quotation'); ?></strong>
                        <small><?php _e('Professional exterior wall cleaning', 'pro-clean-quotation'); ?></small>
                    </span>
                </label>
                <label class="pcq-radio-label">
                    <input type="radio" name="service_type" value="roof" required>
                    <span class="pcq-radio-text">
                        <strong><?php _e('Roof Cleaning', 'pro-clean-quotation'); ?></strong>
                        <small><?php _e('Safe and thorough roof cleaning', 'pro-clean-quotation'); ?></small>
                    </span>
                </label>
                <label class="pcq-radio-label">
                    <input type="radio" name="service_type" value="both" required>
                    <span class="pcq-radio-text">
                        <strong><?php _e('Both Services', 'pro-clean-quotation'); ?></strong>
                        <small><?php _e('Complete exterior cleaning package', 'pro-clean-quotation'); ?></small>
                    </span>
                </label>
            </div>
        </div>

        <!-- Property Measurements -->
        <div class="pcq-form-section pcq-measurements">
            <h4><?php _e('Property Measurements', 'pro-clean-quotation'); ?></h4>
            <div class="pcq-form-row">
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_square_meters">
                        <?php _e('Square Meters', 'pro-clean-quotation'); ?> <span class="required">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="<?php echo esc_attr($form_id); ?>_square_meters" 
                        name="square_meters" 
                        min="10" 
                        max="10000" 
                        step="0.1" 
                        placeholder="150" 
                        required
                        class="pcq-calc-trigger"
                    >
                    <small class="pcq-field-help"><?php _e('Total area to be cleaned (10-10,000 sqm)', 'pro-clean-quotation'); ?></small>
                </div>
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_linear_meters">
                        <?php _e('Linear Meters', 'pro-clean-quotation'); ?>
                    </label>
                    <input 
                        type="number" 
                        id="<?php echo esc_attr($form_id); ?>_linear_meters" 
                        name="linear_meters" 
                        min="0" 
                        max="5000" 
                        step="0.1" 
                        placeholder="45"
                        class="pcq-calc-trigger"
                    >
                    <small class="pcq-field-help"><?php _e('Perimeter or edge length (optional)', 'pro-clean-quotation'); ?></small>
                </div>
            </div>
            <div class="pcq-form-row">
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_building_height">
                        <?php _e('Building Height (Floors)', 'pro-clean-quotation'); ?> <span class="required">*</span>
                    </label>
                    <select id="<?php echo esc_attr($form_id); ?>_building_height" name="building_height" required class="pcq-calc-trigger">
                        <?php for ($i = 1; $i <= 20; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected($i, 1); ?>>
                                <?php echo $i; ?> <?php echo $i === 1 ? __('floor', 'pro-clean-quotation') : __('floors', 'pro-clean-quotation'); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <small class="pcq-field-help"><?php _e('Number of floors in the building', 'pro-clean-quotation'); ?></small>
                </div>
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_property_type">
                        <?php _e('Property Type', 'pro-clean-quotation'); ?> <span class="required">*</span>
                    </label>
                    <select id="<?php echo esc_attr($form_id); ?>_property_type" name="property_type" required class="pcq-calc-trigger">
                        <option value="residential"><?php _e('Residential', 'pro-clean-quotation'); ?></option>
                        <option value="commercial"><?php _e('Commercial', 'pro-clean-quotation'); ?></option>
                        <option value="industrial"><?php _e('Industrial', 'pro-clean-quotation'); ?></option>
                    </select>
                    <small class="pcq-field-help"><?php _e('Type of property', 'pro-clean-quotation'); ?></small>
                </div>
            </div>
        </div>

        <!-- Surface Details -->
        <div class="pcq-form-section pcq-surface-details">
            <h4><?php _e('Surface Details', 'pro-clean-quotation'); ?></h4>
            <div class="pcq-form-row">
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_surface_material">
                        <?php _e('Surface Material', 'pro-clean-quotation'); ?> <span class="required">*</span>
                    </label>
                    <select id="<?php echo esc_attr($form_id); ?>_surface_material" name="surface_material" required class="pcq-calc-trigger">
                        <option value="brick"><?php _e('Brick', 'pro-clean-quotation'); ?></option>
                        <option value="stone"><?php _e('Stone', 'pro-clean-quotation'); ?></option>
                        <option value="glass"><?php _e('Glass', 'pro-clean-quotation'); ?></option>
                        <option value="metal"><?php _e('Metal', 'pro-clean-quotation'); ?></option>
                        <option value="concrete"><?php _e('Concrete', 'pro-clean-quotation'); ?></option>
                        <option value="composite"><?php _e('Composite', 'pro-clean-quotation'); ?></option>
                    </select>
                </div>
                <div class="pcq-form-field pcq-roof-type-field" style="display: none;">
                    <label for="<?php echo esc_attr($form_id); ?>_roof_type">
                        <?php _e('Roof Type', 'pro-clean-quotation'); ?>
                    </label>
                    <select id="<?php echo esc_attr($form_id); ?>_roof_type" name="roof_type" class="pcq-calc-trigger">
                        <option value=""><?php _e('Select roof type', 'pro-clean-quotation'); ?></option>
                        <option value="flat"><?php _e('Flat Roof', 'pro-clean-quotation'); ?></option>
                        <option value="pitched"><?php _e('Pitched Roof', 'pro-clean-quotation'); ?></option>
                        <option value="complex"><?php _e('Complex Roof', 'pro-clean-quotation'); ?></option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Live Price Display -->
        <div class="pcq-price-display" id="<?php echo esc_attr($form_id); ?>_price_display" style="display: none;">
            <h4><?php _e('Estimated Quote', 'pro-clean-quotation'); ?></h4>
            <div class="pcq-price-breakdown">
                <div class="pcq-price-loading">
                    <span class="pcq-spinner"></span>
                    <span><?php _e('Calculating your quote...', 'pro-clean-quotation'); ?></span>
                </div>
                <div class="pcq-price-result" style="display: none;"></div>
            </div>
            <p class="pcq-price-disclaimer">
                <small><?php _e('This is an estimated quote. Final pricing may vary after on-site assessment. Quote valid for 30 days.', 'pro-clean-quotation'); ?></small>
            </p>
        </div>

        <!-- Contact Information -->
        <div class="pcq-form-section pcq-contact-info">
            <h4><?php _e('Contact Information', 'pro-clean-quotation'); ?></h4>
            <div class="pcq-form-row">
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_customer_name">
                        <?php _e('Full Name', 'pro-clean-quotation'); ?> <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="<?php echo esc_attr($form_id); ?>_customer_name" 
                        name="customer_name" 
                        required
                        placeholder="<?php esc_attr_e('John Doe', 'pro-clean-quotation'); ?>"
                    >
                </div>
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_customer_email">
                        <?php _e('Email Address', 'pro-clean-quotation'); ?> <span class="required">*</span>
                    </label>
                    <input 
                        type="email" 
                        id="<?php echo esc_attr($form_id); ?>_customer_email" 
                        name="customer_email" 
                        required
                        placeholder="<?php esc_attr_e('john@example.com', 'pro-clean-quotation'); ?>"
                    >
                </div>
            </div>
            <div class="pcq-form-row">
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_customer_phone">
                        <?php _e('Phone Number', 'pro-clean-quotation'); ?> <span class="required">*</span>
                    </label>
                    <input 
                        type="tel" 
                        id="<?php echo esc_attr($form_id); ?>_customer_phone" 
                        name="customer_phone" 
                        required
                        pattern="^((\+34|0034)[6-9][0-9]{8}|[6-9][0-9]{8})$"
                        placeholder="<?php esc_attr_e('+34 612 345 678', 'pro-clean-quotation'); ?>"
                        title="<?php esc_attr_e('Enter a valid Spanish phone number (e.g., +34 612 345 678 or 612345678)', 'pro-clean-quotation'); ?>"
                    >
                    <small class="pcq-field-help"><?php _e('Format: +34 612 345 678 or 612345678', 'pro-clean-quotation'); ?></small>
                </div>
                <div class="pcq-form-field">
                    <label for="<?php echo esc_attr($form_id); ?>_postal_code">
                        <?php _e('Postal Code', 'pro-clean-quotation'); ?> <span class="required">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="<?php echo esc_attr($form_id); ?>_postal_code" 
                        name="postal_code" 
                        required
                        pattern="^(0[1-9]|[1-4][0-9]|5[0-2])[0-9]{3}$"
                        placeholder="<?php esc_attr_e('29600', 'pro-clean-quotation'); ?>"
                        maxlength="5"
                        title="<?php esc_attr_e('Enter a valid Spanish postal code (5 digits, e.g., 29600)', 'pro-clean-quotation'); ?>"
                    >
                    <small class="pcq-field-help"><?php _e('Format: 29600 (5-digit Spanish postal code)', 'pro-clean-quotation'); ?></small>
                </div>
            </div>
            <div class="pcq-form-field">
                <label for="<?php echo esc_attr($form_id); ?>_property_address">
                    <?php _e('Property Address', 'pro-clean-quotation'); ?> <span class="required">*</span>
                </label>
                <textarea 
                    id="<?php echo esc_attr($form_id); ?>_property_address" 
                    name="property_address" 
                    rows="2" 
                    required
                    placeholder="<?php esc_attr_e('Street name, house number, city', 'pro-clean-quotation'); ?>"
                ></textarea>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="pcq-form-section pcq-additional-info">
            <h4><?php _e('Additional Information', 'pro-clean-quotation'); ?></h4>
            <div class="pcq-form-field">
                <label for="<?php echo esc_attr($form_id); ?>_special_requirements">
                    <?php _e('Special Requirements or Notes', 'pro-clean-quotation'); ?>
                </label>
                <textarea 
                    id="<?php echo esc_attr($form_id); ?>_special_requirements" 
                    name="special_requirements" 
                    rows="3" 
                    maxlength="500"
                    placeholder="<?php esc_attr_e('Any special requirements, access limitations, or notes...', 'pro-clean-quotation'); ?>"
                ></textarea>
                <small class="pcq-field-help"><?php _e('Maximum 500 characters', 'pro-clean-quotation'); ?></small>
            </div>
            <div class="pcq-form-field">
                <label for="<?php echo esc_attr($form_id); ?>_last_cleaning_date">
                    <?php _e('Last Cleaning Date', 'pro-clean-quotation'); ?>
                </label>
                <input 
                    type="date" 
                    id="<?php echo esc_attr($form_id); ?>_last_cleaning_date" 
                    name="last_cleaning_date"
                    max="<?php echo date('Y-m-d'); ?>"
                >
                <small class="pcq-field-help"><?php _e('When was the property last cleaned? (Optional)', 'pro-clean-quotation'); ?></small>
            </div>
        </div>

        <!-- Legal Consents -->
        <div class="pcq-form-section pcq-legal">
            <div class="pcq-checkbox-field">
                <label>
                    <input type="checkbox" name="privacy_consent" required>
                    <span class="pcq-checkbox-text">
                        <?php 
                        printf(
                            __('I agree to the %s and consent to the processing of my personal data.', 'pro-clean-quotation'),
                            '<a href="' . esc_url(get_privacy_policy_url()) . '" target="_blank">' . __('Privacy Policy', 'pro-clean-quotation') . '</a>'
                        ); 
                        ?>
                        <span class="required">*</span>
                    </span>
                </label>
            </div>
            <div class="pcq-checkbox-field">
                <label>
                    <input type="checkbox" name="marketing_consent">
                    <span class="pcq-checkbox-text">
                        <?php _e('I would like to receive marketing communications and special offers.', 'pro-clean-quotation'); ?>
                    </span>
                </label>
            </div>
        </div>

        <!-- Form Messages -->
        <div class="pcq-form-messages"></div>

        <!-- Submit Button -->
        <div class="pcq-form-actions">
            <button type="submit" class="pcq-submit-btn">
                <span class="pcq-btn-text"><?php _e('Get My Free Quote', 'pro-clean-quotation'); ?></span>
                <span class="pcq-btn-loading" style="display: none;">
                    <span class="pcq-spinner"></span>
                    <?php _e('Processing...', 'pro-clean-quotation'); ?>
                </span>
            </button>
        </div>

        <!-- Security -->
        <?php wp_nonce_field('pcq_nonce', 'pcq_nonce'); ?>
        <input type="hidden" name="action" value="pcq_submit_quote">
        
    </form>

</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('#<?php echo esc_js($form_id); ?>');
    var $serviceType = $form.find('input[name="service_type"]');
    var $roofTypeField = $form.find('.pcq-roof-type-field');
    var $priceDisplay = $('#<?php echo esc_js($form_id); ?>_price_display');
    var $priceLoading = $priceDisplay.find('.pcq-price-loading');
    var $priceResult = $priceDisplay.find('.pcq-price-result');
    var calcTimeout = null;

    // Show/hide roof type field based on service selection
    $serviceType.on('change', function() {
        var serviceType = $('input[name="service_type"]:checked').val();
        if (serviceType === 'roof' || serviceType === 'both') {
            $roofTypeField.slideDown();
        } else {
            $roofTypeField.slideUp();
        }
        triggerCalculation();
    });

    // Trigger price calculation on field changes
    $form.on('change keyup', '.pcq-calc-trigger', function() {
        triggerCalculation();
    });

    function triggerCalculation() {
        clearTimeout(calcTimeout);
        calcTimeout = setTimeout(calculatePrice, 500); // 500ms debounce
    }

    function calculatePrice() {
        var formData = $form.serializeArray();
        var hasRequiredData = $('input[name="service_type"]:checked').val() && 
                              $('input[name="square_meters"]').val();

        if (!hasRequiredData) {
            return;
        }

        $priceDisplay.show();
        $priceLoading.show();
        $priceResult.hide();

        $.ajax({
            url: pcq_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pcq_calculate_quote',
                nonce: pcq_ajax.nonce,
                ...Object.fromEntries(formData.map(item => [item.name, item.value]))
            },
            success: function(response) {
                if (response.success) {
                    displayPriceBreakdown(response.data);
                } else {
                    $priceResult.html('<p class="pcq-error">' + (response.data || pcq_ajax.strings.error) + '</p>');
                }
                $priceLoading.hide();
                $priceResult.show();
            },
            error: function() {
                $priceResult.html('<p class="pcq-error">' + pcq_ajax.strings.error + '</p>');
                $priceLoading.hide();
                $priceResult.show();
            }
        });
    }

    function displayPriceBreakdown(data) {
        var html = '<table class="pcq-price-table">';
        if (data.breakdown) {
            $.each(data.breakdown, function(key, item) {
                var rowClass = key === 'total' ? 'pcq-price-total' : '';
                html += '<tr class="' + rowClass + '">';
                html += '<td>' + item.label + '</td>';
                html += '<td class="pcq-price-amount">€' + parseFloat(item.amount).toFixed(2) + '</td>';
                html += '</tr>';
            });
        }
        html += '</table>';
        $priceResult.html(html);
    }

    // Form submission
    $form.on('submit', function(e) {
        e.preventDefault();
        
        var $submitBtn = $form.find('.pcq-submit-btn');
        var $btnText = $submitBtn.find('.pcq-btn-text');
        var $btnLoading = $submitBtn.find('.pcq-btn-loading');
        var $messages = $form.find('.pcq-form-messages');

        $submitBtn.prop('disabled', true);
        $btnText.hide();
        $btnLoading.show();
        $messages.empty();

        $.ajax({
            url: pcq_ajax.ajax_url,
            type: 'POST',
            data: $form.serialize(),
            success: function(response) {
                if (response.success) {
                    $messages.html('<div class="pcq-message pcq-success">' + 
                                 '<strong><?php esc_html_e('Success!', 'pro-clean-quotation'); ?></strong> ' + 
                                 response.data.message + '</div>');
                    $form[0].reset();
                    $priceDisplay.hide();
                    
                    // Scroll to message
                    $('html, body').animate({
                        scrollTop: $messages.offset().top - 100
                    }, 500);
                } else {
                    $messages.html('<div class="pcq-message pcq-error">' + 
                                 '<strong><?php esc_html_e('Error!', 'pro-clean-quotation'); ?></strong> ' + 
                                 (response.data || pcq_ajax.strings.error) + '</div>');
                }
            },
            error: function() {
                $messages.html('<div class="pcq-message pcq-error">' + 
                             '<strong><?php esc_html_e('Error!', 'pro-clean-quotation'); ?></strong> ' + 
                             pcq_ajax.strings.error + '</div>');
            },
            complete: function() {
                $submitBtn.prop('disabled', false);
                $btnText.show();
                $btnLoading.hide();
            }
        });
    });
});
</script>
