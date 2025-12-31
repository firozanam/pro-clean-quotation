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
    
    // Verify token for security
    if ($quote->getId() && $quote->getToken() === $token) {
        // Token is valid, proceed
    } else {
        // Invalid token or quote not found
        $quote = null;
    }
}

$show_title = isset($atts['show_title']) && $atts['show_title'] === 'true';
$title = isset($atts['title']) ? $atts['title'] : __('Book Your Service', 'pro-clean-quotation');
?>

<div class="pcq-booking-form-container" 
     data-square-meters="<?php echo $quote ? esc_attr($quote->getSquareMeters()) : '0'; ?>" 
     data-service-type="<?php echo $quote ? esc_attr($quote->getServiceType()) : 'facade'; ?>">
    
    <?php if ($show_title): ?>
        <h3 class="pcq-booking-title"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>
    
    <?php if ($quote_id && $token && !$quote): ?>
        <!-- Invalid Token/Quote Error -->
        <div class="pcq-error-notice" style="padding: 20px; background: #f8d7da; border-left: 4px solid #dc3545; margin: 20px 0; border-radius: 4px;">
            <h4 style="color: #721c24; margin: 0 0 10px 0;"><?php _e('Invalid Booking Link', 'pro-clean-quotation'); ?></h4>
            <p style="margin: 0;"><?php _e('The booking link you used is invalid or has expired. Please request a new quote or contact us for assistance.', 'pro-clean-quotation'); ?></p>
            <p style="margin: 15px 0 0 0;">
                <a href="<?php echo esc_url(home_url('/get-quote/')); ?>" class="button" style="display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 4px;">
                    <?php _e('Request New Quote', 'pro-clean-quotation'); ?>
                </a>
            </p>
        </div>
    <?php endif; ?>
    
    <?php if ($quote && $quote->getId()): ?>
        <!-- Quote Summary -->
        <div class="pcq-quote-summary" style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin-bottom: 30px;">
            <h4 style="margin: 0 0 15px 0; color: #333; font-size: 18px;"><?php _e('Your Quote Summary', 'pro-clean-quotation'); ?></h4>
            <table class="pcq-summary-table" style="width: 100%; border-collapse: collapse;">
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 12px 8px; color: #666; width: 40%;"><?php _e('Quote Number:', 'pro-clean-quotation'); ?></td>
                    <td style="padding: 12px 8px; text-align: right; font-weight: 600;"><strong>#<?php echo esc_html($quote->getQuoteNumber()); ?></strong></td>
                </tr>
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 12px 8px; color: #666;"><?php _e('Service:', 'pro-clean-quotation'); ?></td>
                    <td style="padding: 12px 8px; text-align: right;"><?php echo esc_html(ucfirst($quote->getServiceType())); ?></td>
                </tr>
                <tr style="border-bottom: 1px solid #dee2e6;">
                    <td style="padding: 12px 8px; color: #666;"><?php _e('Property Size:', 'pro-clean-quotation'); ?></td>
                    <td style="padding: 12px 8px; text-align: right;"><?php echo number_format($quote->getSquareMeters(), 1); ?> sqm</td>
                </tr>
                <tr>
                    <td style="padding: 12px 8px; color: #666;"><?php _e('Total Amount:', 'pro-clean-quotation'); ?></td>
                    <td style="padding: 12px 8px; text-align: right; color: #007bff; font-size: 20px; font-weight: 700;">€<?php echo number_format($quote->getTotalPrice(), 2); ?></td>
                </tr>
            </table>
        </div>

    <!-- Booking Calendar Integration -->
    <div class="pcq-booking-calendar-wrapper" style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h4 style="margin: 0 0 20px 0; color: #333; font-size: 18px;"><?php _e('Select Date & Time', 'pro-clean-quotation'); ?></h4>
        
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
                <div class="pcq-calendar-container" style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;"><?php _e('Select Service Date:', 'pro-clean-quotation'); ?></label>
                    <div id="pcq-booking-calendar"></div>
                </div>
                
                <div class="pcq-time-slots-container" style="display: none;">
                    <h5><?php _e('Available Time Slots', 'pro-clean-quotation'); ?></h5>
                    <div id="pcq-available-slots"></div>
                </div>
                
                <form id="pcq-booking-form" style="display: none;">
                    <input type="hidden" name="quote_id" value="<?php echo esc_attr($quote_id); ?>">
                    <input type="hidden" name="quote_token" value="<?php echo esc_attr($token); ?>">
                    <input type="hidden" name="service_date" id="selected_date">
                    <input type="hidden" name="service_time_start" id="selected_time_start">
                    <input type="hidden" name="service_time_end" id="selected_time_end">
                    
                    <div class="pcq-booking-confirmation">
                        <h5><?php _e('Confirm Your Booking', 'pro-clean-quotation'); ?></h5>
                        <p id="pcq-selected-datetime"></p>
                        
                        <div class="pcq-form-field">
                            <label for="booking_notes"><?php _e('Additional Notes (Optional)', 'pro-clean-quotation'); ?></label>
                            <textarea id="booking_notes" name="customer_notes" rows="3"></textarea>
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
                console.log('PCQ: Booking calendar initializing');
                
                // Check if BookingCalendar is available
                if (typeof BookingCalendar !== 'undefined' && BookingCalendar.init) {
                    BookingCalendar.init();
                } else {
                    console.log('PCQ: BookingCalendar not loaded, using simple fallback');
                    
                    // Simple fallback message
                    var $availableSlots = $('#pcq-available-slots');
                    var $slotsContainer = $('.pcq-time-slots-container');
                    
                    $availableSlots.html('<div class="pcq-info"><p><strong>Please select a date above to view available time slots.</strong></p><p>Our team is available Monday through Saturday, 9:00 AM to 6:00 PM.</p></div>');
                    $slotsContainer.show();
                }
            });
            </script>
            <?php
        }
        ?>
    </div>
    <?php endif; ?>

    <?php if (!$quote || !$quote->getId()): ?>
        <div class="pcq-no-quote-notice">
            <p><?php _e('To book a service, please first request a quote using our quote form.', 'pro-clean-quotation'); ?></p>
            <a href="<?php echo esc_url(home_url('/get-quote/')); ?>" class="pcq-btn-primary">
                <?php _e('Get a Quote', 'pro-clean-quotation'); ?>
            </a>
        </div>
    <?php endif; ?>

</div>
