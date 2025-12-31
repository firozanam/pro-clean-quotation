<?php
/**
 * Shortcodes Reference Template
 * 
 * @package ProClean\Quotation
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap pcq-shortcodes-page">
    <h1><?php _e('Shortcodes Reference', 'pro-clean-quotation'); ?></h1>
    <p class="description"><?php _e('Copy and paste these shortcodes into your pages or posts to display the quotation forms.', 'pro-clean-quotation'); ?></p>
    
    <div class="pcq-shortcode-cards">
        
        <!-- Quote Form Shortcode -->
        <div class="pcq-shortcode-card">
            <div class="shortcode-header">
                <h2><?php _e('Quote Request Form', 'pro-clean-quotation'); ?></h2>
                <span class="shortcode-badge">Main Form</span>
            </div>
            
            <div class="shortcode-content">
                <p class="shortcode-description">
                    <?php _e('Display the complete quote request form with price calculation.', 'pro-clean-quotation'); ?>
                </p>
                
                <div class="shortcode-box">
                    <code>[pcq_quote_form]</code>
                    <button class="button button-small copy-shortcode" data-shortcode="[pcq_quote_form]">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                    </button>
                </div>
                
                <h4><?php _e('Available Parameters:', 'pro-clean-quotation'); ?></h4>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Parameter', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Default', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Description', 'pro-clean-quotation'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>title</code></td>
                            <td>"Get Your Free Quote"</td>
                            <td><?php _e('Form title text', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_title</code></td>
                            <td>true</td>
                            <td><?php _e('Show/hide form title (true/false)', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <tr>
                            <td><code>style</code></td>
                            <td>default</td>
                            <td><?php _e('Form style (default, compact, modern)', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <tr>
                            <td><code>columns</code></td>
                            <td>2</td>
                            <td><?php _e('Number of columns (1 or 2)', 'pro-clean-quotation'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <h4><?php _e('Examples:', 'pro-clean-quotation'); ?></h4>
                <div class="shortcode-examples">
                    <div class="shortcode-box">
                        <code>[pcq_quote_form title="Request a Quote" columns="1"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[pcq_quote_form title="Request a Quote" columns="1"]'>
                            <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                        </button>
                    </div>
                    <div class="shortcode-box">
                        <code>[pcq_quote_form show_title="false" style="compact"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[pcq_quote_form show_title="false" style="compact"]'>
                            <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Form Shortcode -->
        <div class="pcq-shortcode-card">
            <div class="shortcode-header">
                <h2><?php _e('Direct Booking Form', 'pro-clean-quotation'); ?></h2>
                <span class="shortcode-badge booking">Booking</span>
            </div>
            
            <div class="shortcode-content">
                <p class="shortcode-description">
                    <?php _e('Display the booking form for customers to schedule a service directly.', 'pro-clean-quotation'); ?>
                </p>
                
                <div class="shortcode-box">
                    <code>[pcq_booking_form]</code>
                    <button class="button button-small copy-shortcode" data-shortcode="[pcq_booking_form]">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                    </button>
                </div>
                
                <h4><?php _e('Available Parameters:', 'pro-clean-quotation'); ?></h4>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Parameter', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Default', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Description', 'pro-clean-quotation'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>quote_id</code></td>
                            <td>-</td>
                            <td><?php _e('Pre-fill form with quote data', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <tr>
                            <td><code>title</code></td>
                            <td>"Book Your Service"</td>
                            <td><?php _e('Form title text', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_title</code></td>
                            <td>true</td>
                            <td><?php _e('Show/hide form title (true/false)', 'pro-clean-quotation'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <h4><?php _e('Examples:', 'pro-clean-quotation'); ?></h4>
                <div class="shortcode-examples">
                    <div class="shortcode-box">
                        <code>[pcq_booking_form title="Schedule Your Cleaning"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[pcq_booking_form title="Schedule Your Cleaning"]'>
                            <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Price Calculator Shortcode -->
        <div class="pcq-shortcode-card">
            <div class="shortcode-header">
                <h2><?php _e('Quick Price Calculator', 'pro-clean-quotation'); ?></h2>
                <span class="shortcode-badge calculator">Calculator</span>
            </div>
            
            <div class="shortcode-content">
                <p class="shortcode-description">
                    <?php _e('Display a simple price calculator without contact form (for informational purposes).', 'pro-clean-quotation'); ?>
                </p>
                
                <div class="shortcode-box">
                    <code>[pcq_quote_calculator]</code>
                    <button class="button button-small copy-shortcode" data-shortcode="[pcq_quote_calculator]">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                    </button>
                </div>
                
                <h4><?php _e('Available Parameters:', 'pro-clean-quotation'); ?></h4>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Parameter', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Default', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Description', 'pro-clean-quotation'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>title</code></td>
                            <td>"Quick Price Calculator"</td>
                            <td><?php _e('Calculator title text', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_title</code></td>
                            <td>true</td>
                            <td><?php _e('Show/hide calculator title (true/false)', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_contact_form</code></td>
                            <td>false</td>
                            <td><?php _e('Include contact form after calculation (true/false)', 'pro-clean-quotation'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <h4><?php _e('Examples:', 'pro-clean-quotation'); ?></h4>
                <div class="shortcode-examples">
                    <div class="shortcode-box">
                        <code>[pcq_quote_calculator show_contact_form="true"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[pcq_quote_calculator show_contact_form="true"]'>
                            <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                        </button>
                    </div>
                    <div class="shortcode-box">
                        <code>[pcq_quote_calculator title="Estimate Your Price" show_title="true"]</code>
                        <button class="button button-small copy-shortcode" data-shortcode='[pcq_quote_calculator title="Estimate Your Price" show_title="true"]'>
                            <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Booking Confirmation Shortcode -->
        <div class="pcq-shortcode-card">
            <div class="shortcode-header">
                <h2><?php _e('Booking Confirmation Page', 'pro-clean-quotation'); ?></h2>
                <span class="shortcode-badge" style="background: #7c3aed;">Confirmation</span>
            </div>
            
            <div class="shortcode-content">
                <p class="shortcode-description">
                    <?php _e('Display booking confirmation details after successful booking. Typically used on a dedicated confirmation page.', 'pro-clean-quotation'); ?>
                </p>
                
                <div class="shortcode-box">
                    <code>[pcq_booking_confirmation]</code>
                    <button class="button button-small copy-shortcode" data-shortcode="[pcq_booking_confirmation]">
                        <span class="dashicons dashicons-clipboard"></span> <?php _e('Copy', 'pro-clean-quotation'); ?>
                    </button>
                </div>
                
                <h4><?php _e('Available Parameters:', 'pro-clean-quotation'); ?></h4>
                <table class="widefat">
                    <thead>
                        <tr>
                            <th><?php _e('Parameter', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Default', 'pro-clean-quotation'); ?></th>
                            <th><?php _e('Description', 'pro-clean-quotation'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>title</code></td>
                            <td>"Booking Confirmed"</td>
                            <td><?php _e('Confirmation page title', 'pro-clean-quotation'); ?></td>
                        </tr>
                        <tr>
                            <td><code>show_title</code></td>
                            <td>true</td>
                            <td><?php _e('Show/hide confirmation title (true/false)', 'pro-clean-quotation'); ?></td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="notice notice-info inline" style="margin: 15px 0;">
                    <p><strong><?php _e('Note:', 'pro-clean-quotation'); ?></strong> <?php _e('This shortcode displays booking details passed via URL parameters. It should be placed on a dedicated page where customers are redirected after completing a booking.', 'pro-clean-quotation'); ?></p>
                </div>
                
                <h4><?php _e('Setup Instructions:', 'pro-clean-quotation'); ?></h4>
                <ol style="padding-left: 20px; line-height: 1.8;">
                    <li><?php _e('Create a new page called "Booking Confirmation"', 'pro-clean-quotation'); ?></li>
                    <li><?php _e('Add the shortcode <code>[pcq_booking_confirmation]</code> to the page content', 'pro-clean-quotation'); ?></li>
                    <li><?php _e('Publish the page - the system will automatically detect and use it', 'pro-clean-quotation'); ?></li>
                </ol>
            </div>
        </div>
        
    </div>
    
    <!-- Quick Tips Section -->
    <div class="pcq-tips-section">
        <h2><?php _e('💡 Quick Tips', 'pro-clean-quotation'); ?></h2>
        <ul>
            <li><?php _e('<strong>One-Click Copy:</strong> Click the "Copy" button next to any shortcode to copy it to your clipboard.', 'pro-clean-quotation'); ?></li>
            <li><?php _e('<strong>Page Integration:</strong> Add shortcodes to any page or post using the WordPress editor.', 'pro-clean-quotation'); ?></li>
            <li><?php _e('<strong>Widget Support:</strong> Use shortcodes in text widgets to display forms in sidebars or footer areas.', 'pro-clean-quotation'); ?></li>
            <li><?php _e('<strong>Theme Customization:</strong> Forms inherit your theme styles automatically but can be customized via CSS.', 'pro-clean-quotation'); ?></li>
            <li><?php _e('<strong>Multiple Forms:</strong> You can use different shortcodes on different pages for various purposes.', 'pro-clean-quotation'); ?></li>
        </ul>
    </div>
</div>

<style>
.pcq-shortcodes-page {
    max-width: 1200px;
}

.pcq-shortcode-cards {
    display: grid;
    gap: 20px;
    margin: 30px 0;
}

.pcq-shortcode-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.shortcode-header {
    background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
    color: #fff;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.shortcode-header h2 {
    margin: 0;
    color: #fff;
    font-size: 18px;
    font-weight: 600;
}

.shortcode-badge {
    background: rgba(255,255,255,0.2);
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.shortcode-badge.booking {
    background: #d63638;
}

.shortcode-badge.calculator {
    background: #00a32a;
}

.shortcode-content {
    padding: 25px;
}

.shortcode-description {
    font-size: 14px;
    color: #646970;
    margin-bottom: 20px;
}

.shortcode-box {
    background: #f6f7f7;
    border: 2px solid #dcdcde;
    border-radius: 6px;
    padding: 12px 15px;
    margin: 15px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    font-family: 'Courier New', monospace;
}

.shortcode-box code {
    font-size: 14px;
    color: #d63638;
    background: transparent;
    padding: 0;
    flex: 1;
    line-height: 1.5;
}

.copy-shortcode {
    margin: 0;
    padding: 6px 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    height: 32px;
    font-size: 13px;
    font-weight: 500;
    border-radius: 4px;
    border: 1px solid #2271b1;
    background: #2271b1;
    color: #fff;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.copy-shortcode:hover {
    background: #135e96;
    border-color: #135e96;
    color: #fff;
}

.copy-shortcode:focus {
    box-shadow: 0 0 0 1px #fff, 0 0 0 3px #2271b1;
    outline: none;
}

.copy-shortcode .dashicons {
    font-size: 16px;
    width: 16px;
    height: 16px;
    line-height: 16px;
    vertical-align: middle;
    margin-top: -2px;
}

.copy-shortcode.copied {
    background: #00a32a;
    border-color: #00a32a;
    color: #fff;
}

.shortcode-content h4 {
    margin-top: 25px;
    margin-bottom: 10px;
    font-size: 15px;
    font-weight: 600;
}

.shortcode-content table {
    margin: 15px 0;
}

.shortcode-content table code {
    background: #f0f0f1;
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 12px;
}

.shortcode-examples {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pcq-tips-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 25px;
    margin-top: 30px;
}

.pcq-tips-section h2 {
    margin-top: 0;
    color: #1d2327;
}

.pcq-tips-section ul {
    margin: 15px 0;
    padding-left: 25px;
}

.pcq-tips-section li {
    margin: 10px 0;
    line-height: 1.6;
}
</style>

<script>
jQuery(document).ready(function($) {
    $('.copy-shortcode').on('click', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var shortcode = button.data('shortcode');
        
        // Create temporary textarea to copy text
        var temp = $('<textarea>');
        $('body').append(temp);
        temp.val(shortcode).select();
        document.execCommand('copy');
        temp.remove();
        
        // Visual feedback
        var originalHtml = button.html();
        button.addClass('copied')
              .html('<span class="dashicons dashicons-yes"></span> <?php _e('Copied!', 'pro-clean-quotation'); ?>');
        
        setTimeout(function() {
            button.removeClass('copied').html(originalHtml);
        }, 2000);
    });
});
</script>
