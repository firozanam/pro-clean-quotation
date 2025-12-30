<?php
/**
 * Email Template: Booking Confirmation
 * 
 * Available variables:
 * @var array $booking
 * @var string $company_name
 * @var string $company_email
 * @var string $company_phone
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
 */

defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Booking Confirmed', 'pro-clean-quotation'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #f4f4f4;">
    
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 13L9 17L19 7" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">
                                <?php echo esc_html__('Booking Confirmed!', 'pro-clean-quotation'); ?>
                            </h1>
                            <p style="color: #ffffff; margin: 15px 0 0; font-size: 16px; opacity: 0.95;">
                                <?php echo esc_html($company_name); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            
                            <p style="color: #2c3e50; margin: 0 0 30px; font-size: 16px; text-align: center;">
                                <?php echo esc_html__('Thank you! Your cleaning service booking has been confirmed.', 'pro-clean-quotation'); ?>
                            </p>
                            
                            <!-- Booking Reference -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 25px; text-align: center;">
                                        <p style="color: rgba(255,255,255,0.9); margin: 0 0 10px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                                            <?php echo esc_html__('Booking Reference', 'pro-clean-quotation'); ?>
                                        </p>
                                        <h2 style="color: #ffffff; margin: 0; font-size: 32px; font-weight: 700; letter-spacing: 2px;">
                                            <?php echo esc_html($booking['booking_number']); ?>
                                        </h2>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Service Details -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <h3 style="color: #2c3e50; margin: 0 0 20px; font-size: 18px; font-weight: 600;">
                                            <?php echo esc_html__('Service Details', 'pro-clean-quotation'); ?>
                                        </h3>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 10px 0; color: #7f8c8d; font-size: 14px;">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                                        <rect x="3" y="4" width="18" height="18" rx="2" stroke="#2196F3" stroke-width="2"/>
                                                        <line x1="3" y1="9" x2="21" y2="9" stroke="#2196F3" stroke-width="2"/>
                                                    </svg>
                                                    <?php echo esc_html__('Service Date:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 10px 0; color: #2c3e50; font-size: 16px; font-weight: 700;">
                                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($booking['service_date']))); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0; color: #7f8c8d; font-size: 14px;">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                                        <circle cx="12" cy="12" r="9" stroke="#2196F3" stroke-width="2"/>
                                                        <path d="M12 6v6l4 2" stroke="#2196F3" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                    <?php echo esc_html__('Service Time:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 10px 0; color: #2c3e50; font-size: 16px; font-weight: 700;">
                                                    <?php echo esc_html($booking['service_time_start'] . ' - ' . $booking['service_time_end']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Service Type:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 10px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $booking['service_type']))); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 10px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Property Address:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 10px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($booking['property_address']); ?>
                                                </td>
                                            </tr>
                                            <tr style="border-top: 2px solid #e1e5e9;">
                                                <td style="padding: 15px 0 0; color: #7f8c8d; font-size: 15px; font-weight: 600;">
                                                    <?php echo esc_html__('Total Amount:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 15px 0 0; color: #4CAF50; font-size: 24px; font-weight: 700;">
                                                    €<?php echo esc_html(number_format($booking['total_amount'], 2)); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Important Information -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #e8f5e9; border-left: 4px solid #4CAF50; border-radius: 4px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h4 style="color: #2e7d32; margin: 0 0 15px; font-size: 16px; font-weight: 600;">
                                            <?php echo esc_html__('Important Information', 'pro-clean-quotation'); ?>
                                        </h4>
                                        <ul style="margin: 0; padding-left: 20px; color: #2e7d32; font-size: 14px; line-height: 1.8;">
                                            <li><?php echo esc_html__('You will receive a reminder 24 hours before your scheduled service.', 'pro-clean-quotation'); ?></li>
                                            <li><?php echo esc_html__('Please ensure someone is available to provide access to the property.', 'pro-clean-quotation'); ?></li>
                                            <li><?php echo esc_html__('To reschedule, please contact us at least 48 hours in advance.', 'pro-clean-quotation'); ?></li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #7f8c8d; margin: 30px 0 0; font-size: 14px; text-align: center; line-height: 1.6;">
                                <?php echo esc_html__('Please save your booking reference number for future correspondence.', 'pro-clean-quotation'); ?>
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e1e5e9;">
                            <p style="color: #2c3e50; margin: 0 0 15px; font-size: 16px; font-weight: 600;">
                                <?php echo esc_html__('Need to Make Changes?', 'pro-clean-quotation'); ?>
                            </p>
                            <p style="color: #7f8c8d; margin: 0 0 10px; font-size: 14px;">
                                <strong><?php echo esc_html__('Email:', 'pro-clean-quotation'); ?></strong>
                                <a href="mailto:<?php echo esc_attr($company_email); ?>" style="color: #2196F3; text-decoration: none;">
                                    <?php echo esc_html($company_email); ?>
                                </a>
                            </p>
                            <?php if ($company_phone): ?>
                            <p style="color: #7f8c8d; margin: 0 0 20px; font-size: 14px;">
                                <strong><?php echo esc_html__('Phone:', 'pro-clean-quotation'); ?></strong>
                                <?php echo esc_html($company_phone); ?>
                            </p>
                            <?php endif; ?>
                            
                            <p style="color: #95a5a6; margin: 20px 0 0; font-size: 12px;">
                                © <?php echo date('Y'); ?> <?php echo esc_html($company_name); ?>. <?php echo esc_html__('All rights reserved.', 'pro-clean-quotation'); ?>
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>
