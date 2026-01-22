<?php
/**
 * Quote Calculator Template (Simplified)
 * 
 * This template can be overridden by copying it to:
 * yourtheme/pro-clean-quotation/quote-calculator.php
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

$form_id = 'pcq-calculator-' . uniqid();
$show_title = isset($atts['show_title']) && $atts['show_title'] === 'true';
$show_contact_form = isset($atts['show_contact_form']) && $atts['show_contact_form'] === 'true';
$title = isset($atts['title']) ? $atts['title'] : __('Quick Price Calculator', 'pro-clean-quotation');
?>

<div class="pcq-calculator-container">
    
    <?php if ($show_title): ?>
        <p class="pcq-calculator-title" style="font-size: 24px; font-weight: 700; margin-bottom: 12px;"><?php echo esc_html($title); ?></p>
        <p class="pcq-calculator-subtitle"><?php _e('Get an instant price estimate in seconds', 'pro-clean-quotation'); ?></p>
    <?php endif; ?>

    <form id="<?php echo esc_attr($form_id); ?>" class="pcq-calculator-form">
        
        <div class="pcq-calc-field">
            <label><?php _e('Service Type', 'pro-clean-quotation'); ?></label>
            <select name="service_type" required>
                <option value=""><?php _e('Select service...', 'pro-clean-quotation'); ?></option>
                <option value="facade"><?php _e('Façade Cleaning', 'pro-clean-quotation'); ?></option>
                <option value="roof"><?php _e('Roof Cleaning', 'pro-clean-quotation'); ?></option>
                <option value="both"><?php _e('Both Services', 'pro-clean-quotation'); ?></option>
            </select>
        </div>

        <div class="pcq-calc-field">
            <label><?php _e('Square Meters', 'pro-clean-quotation'); ?></label>
            <input type="number" name="square_meters" min="10" max="10000" step="1" placeholder="150" required>
        </div>

        <div class="pcq-calc-field">
            <label><?php _e('Building Floors', 'pro-clean-quotation'); ?></label>
            <select name="building_height" required>
                <?php for ($i = 1; $i <= 10; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="pcq-calc-field">
            <label><?php _e('Property Type', 'pro-clean-quotation'); ?></label>
            <select name="property_type" required>
                <option value="residential"><?php _e('Residential', 'pro-clean-quotation'); ?></option>
                <option value="commercial"><?php _e('Commercial', 'pro-clean-quotation'); ?></option>
                <option value="industrial"><?php _e('Industrial', 'pro-clean-quotation'); ?></option>
            </select>
        </div>

        <button type="button" id="<?php echo esc_attr($form_id); ?>_calculate" class="pcq-calc-btn">
            <?php _e('Calculate Price', 'pro-clean-quotation'); ?>
        </button>

        <div id="<?php echo esc_attr($form_id); ?>_result" class="pcq-calc-result" style="display: none;">
            <div class="pcq-calc-loading">
                <span class="pcq-spinner"></span>
                <span><?php _e('Calculating...', 'pro-clean-quotation'); ?></span>
            </div>
            <div class="pcq-calc-price" style="display: none;">
                <div class="pcq-calc-total">
                    <span class="pcq-calc-label"><?php _e('Estimated Price:', 'pro-clean-quotation'); ?></span>
                    <span class="pcq-calc-amount"></span>
                </div>
                <p class="pcq-calc-note">
                    <?php _e('This is an estimate. Contact us for a detailed quote.', 'pro-clean-quotation'); ?>
                </p>
                <?php if ($show_contact_form): ?>
                    <a href="<?php echo esc_url(home_url('/get-quote/')); ?>" class="pcq-calc-cta">
                        <?php _e('Get Detailed Quote', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php wp_nonce_field('pcq_nonce', 'pcq_nonce'); ?>
    </form>

</div>

<script>
jQuery(document).ready(function($) {
    var $form = $('#<?php echo esc_js($form_id); ?>');
    var $calcBtn = $('#<?php echo esc_js($form_id); ?>_calculate');
    var $result = $('#<?php echo esc_js($form_id); ?>_result');
    var $loading = $result.find('.pcq-calc-loading');
    var $price = $result.find('.pcq-calc-price');
    var $amount = $result.find('.pcq-calc-amount');

    $calcBtn.on('click', function() {
        if (!$form[0].checkValidity()) {
            $form[0].reportValidity();
            return;
        }

        var formData = $form.serializeArray();
        
        $result.show();
        $loading.show();
        $price.hide();

        $.ajax({
            url: pcq_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pcq_calculate_quote',
                nonce: pcq_ajax.nonce,
                ...Object.fromEntries(formData.map(item => [item.name, item.value]))
            },
            success: function(response) {
                if (response.success && response.data.total) {
                    $amount.text('€' + parseFloat(response.data.total).toFixed(2));
                    $loading.hide();
                    $price.show();
                } else {
                    alert(pcq_ajax.strings.error);
                    $result.hide();
                }
            },
            error: function() {
                alert(pcq_ajax.strings.error);
                $result.hide();
            }
        });
    });

    // Auto-calculate on change
    $form.on('change', 'select, input', function() {
        if ($result.is(':visible')) {
            $calcBtn.click();
        }
    });
});
</script>
