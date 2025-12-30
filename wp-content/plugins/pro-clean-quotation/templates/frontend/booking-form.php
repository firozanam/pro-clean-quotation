<?php
/**
 * Booking Form Template
 * 
 * This template can be overridden by copying it to:
 * yourtheme/pro-clean-quotation/booking-form.php
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

use ProClean\Quotation\Models\Quote;

$quote_id = isset($_GET['quote_id']) ? intval($_GET['quote_id']) : 0;
$token = isset($_GET['token']) ? sanitize_text_field($_GET['token']) : '';
$quote = null;

if ($quote_id && $token) {
    $quote = new Quote($quote_id);
    // Verify token (you should implement token verification in Quote model)
}

$show_title = isset($atts['show_title']) && $atts['show_title'] === 'true';
$title = isset($atts['title']) ? $atts['title'] : __('Book Your Service', 'pro-clean-quotation');
?>

<div class="pcq-booking-form-container">
    
    <?php if ($show_title): ?>
        <h3 class="pcq-booking-title"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <?php if ($quote && $quote->getId()): ?>
        <!-- Quote Summary -->
        <div class="pcq-quote-summary">
            <h4><?php _e('Your Quote Summary', 'pro-clean-quotation'); ?></h4>
            <table class="pcq-summary-table">
                <tr>
                    <td><?php _e('Quote Number:', 'pro-clean-quotation'); ?></td>
                    <td><strong>#<?php echo esc_html($quote->getQuoteNumber()); ?></strong></td>
                </tr>
                <tr>
                    <td><?php _e('Service:', 'pro-clean-quotation'); ?></td>
                    <td><?php echo esc_html(ucfirst($quote->getServiceType())); ?></td>
                </tr>
                <tr>
                    <td><?php _e('Property Size:', 'pro-clean-quotation'); ?></td>
                    <td><?php echo number_format($quote->getSquareMeters(), 1); ?> sqm</td>
                </tr>
                <tr>
                    <td><?php _e('Total Amount:', 'pro-clean-quotation'); ?></td>
                    <td class="pcq-price-highlight">€<?php echo number_format($quote->getTotalPrice(), 2); ?></td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <!-- Booking Calendar Integration -->
    <div class="pcq-booking-calendar-wrapper">
        <h4><?php _e('Select Date & Time', 'pro-clean-quotation'); ?></h4>
        
        <?php
        // Check if MotoPress Appointment is active
        if (function_exists('mpa_get_plugin')) {
            ?>
            <div class="pcq-motopress-booking">
                <?php
                // Display MotoPress booking form
                if ($quote && $quote->getId()) {
                    // Pre-fill service based on quote
                    $service_id = $this->getMotoPresServiceId($quote->getServiceType());
                    if ($service_id) {
                        echo do_shortcode('[mpa_booking service_id="' . $service_id . '"]');
                    } else {
                        echo do_shortcode('[mpa_booking]');
                    }
                } else {
                    echo do_shortcode('[mpa_booking]');
                }
                ?>
            </div>
            <?php
        } else {
            // Fallback: Custom booking calendar
            ?>
            <div class="pcq-custom-booking">
                <div class="pcq-calendar-container">
                    <div id="pcq-booking-calendar"></div>
                </div>
                
                <div class="pcq-time-slots-container" style="display: none;">
                    <h5><?php _e('Available Time Slots', 'pro-clean-quotation'); ?></h5>
                    <div id="pcq-available-slots"></div>
                </div>
                
                <form id="pcq-booking-form" style="display: none;">
                    <input type="hidden" name="quote_id" value="<?php echo esc_attr($quote_id); ?>">
                    <input type="hidden" name="selected_date" id="selected_date">
                    <input type="hidden" name="selected_time_start" id="selected_time_start">
                    <input type="hidden" name="selected_time_end" id="selected_time_end">
                    
                    <div class="pcq-booking-confirmation">
                        <h5><?php _e('Confirm Your Booking', 'pro-clean-quotation'); ?></h5>
                        <p id="pcq-selected-datetime"></p>
                        
                        <div class="pcq-form-field">
                            <label for="booking_notes"><?php _e('Additional Notes (Optional)', 'pro-clean-quotation'); ?></label>
                            <textarea id="booking_notes" name="booking_notes" rows="3"></textarea>
                        </div>
                        
                        <div class="pcq-form-actions">
                            <button type="button" class="pcq-btn-secondary" id="pcq-change-datetime">
                                <?php _e('Change Date/Time', 'pro-clean-quotation'); ?>
                            </button>
                            <button type="submit" class="pcq-btn-primary">
                                <?php _e('Confirm Booking', 'pro-clean-quotation'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <?php wp_nonce_field('pcq_create_booking', 'pcq_booking_nonce'); ?>
                </form>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Custom booking calendar logic (simplified - needs full implementation)
                var $calendar = $('#pcq-booking-calendar');
                var $slotsContainer = $('.pcq-time-slots-container');
                var $bookingForm = $('#pcq-booking-form');
                
                // Initialize calendar (you would use a library like FullCalendar)
                console.log('Booking calendar would be initialized here');
                
                // Note: This is a placeholder. Full implementation would include:
                // - Calendar widget initialization
                // - AJAX calls to get available slots
                // - Time slot selection handling
                // - Booking submission
            });
            </script>
            <?php
        }
        ?>
    </div>

    <?php if (!$quote || !$quote->getId()): ?>
        <div class="pcq-no-quote-notice">
            <p><?php _e('To book a service, please first request a quote using our quote form.', 'pro-clean-quotation'); ?></p>
            <a href="<?php echo esc_url(home_url('/get-quote/')); ?>" class="pcq-btn-primary">
                <?php _e('Get a Quote', 'pro-clean-quotation'); ?>
            </a>
        </div>
    <?php endif; ?>

</div>
