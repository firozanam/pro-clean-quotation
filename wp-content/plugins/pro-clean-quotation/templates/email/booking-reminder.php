<?php
/**
 * Email Template: Booking Reminder
 * 
 * Available variables:
 * @var array $booking
 * @var string $company_name
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
    <title><?php echo esc_html__('Reminder: Service Tomorrow', 'pro-clean-quotation'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #f4f4f4;">
    
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%); padding: 35px 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <div style="width: 70px; height: 70px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center;">
                                <svg width="35" height="35" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="12" cy="12" r="9" stroke="#ffffff" stroke-width="2.5"/>
                                    <path d="M12 6v6l4 2" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 26px; font-weight: 700;">
                                <?php echo esc_html__('Reminder: Service Tomorrow', 'pro-clean-quotation'); ?>
                            </h1>
                            <p style="color: #ffffff; margin: 12px 0 0; font-size: 15px; opacity: 0.95;">
                                <?php echo esc_html($company_name); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 35px 30px;">
                            
                            <p style="color: #2c3e50; margin: 0 0 25px; font-size: 16px; line-height: 1.6;">
                                <?php echo esc_html__('This is a friendly reminder that your cleaning service is scheduled for tomorrow.', 'pro-clean-quotation'); ?>
                            </p>
                            
                            <!-- Booking Reference Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #FFF3E0; border-left: 4px solid #FF9800; border-radius: 4px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <p style="color: #E65100; margin: 0 0 5px; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; font-weight: 600;">
                                            <?php echo esc_html__('Booking Reference', 'pro-clean-quotation'); ?>
                                        </p>
                                        <p style="color: #E65100; margin: 0; font-size: 24px; font-weight: 700; letter-spacing: 1px;">
                                            <?php echo esc_html($booking['booking_number']); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Service Details -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <h3 style="color: #2c3e50; margin: 0 0 18px; font-size: 17px; font-weight: 600;">
                                            <?php echo esc_html__('Appointment Details', 'pro-clean-quotation'); ?>
                                        </h3>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                                        <rect x="3" y="4" width="18" height="18" rx="2" stroke="#FF9800" stroke-width="2"/>
                                                        <line x1="3" y1="9" x2="21" y2="9" stroke="#FF9800" stroke-width="2"/>
                                                    </svg>
                                                    <?php echo esc_html__('Date:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 16px; font-weight: 700;">
                                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($booking['service_date']))); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" style="vertical-align: middle; margin-right: 8px;">
                                                        <circle cx="12" cy="12" r="9" stroke="#FF9800" stroke-width="2"/>
                                                        <path d="M12 6v6l4 2" stroke="#FF9800" stroke-width="2" stroke-linecap="round"/>
                                                    </svg>
                                                    <?php echo esc_html__('Time:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 16px; font-weight: 700;">
                                                    <?php echo esc_html($booking['service_time_start'] . ' - ' . $booking['service_time_end']); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Service:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html(ucfirst(str_replace('_', ' ', $booking['service_type']))); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Location:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($booking['property_address']); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Preparation Checklist -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #E3F2FD; border-left: 4px solid #2196F3; border-radius: 4px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h4 style="color: #1565C0; margin: 0 0 12px; font-size: 15px; font-weight: 600;">
                                            <?php echo esc_html__('Please Prepare:', 'pro-clean-quotation'); ?>
                                        </h4>
                                        <ul style="margin: 0; padding-left: 20px; color: #1565C0; font-size: 14px; line-height: 1.8;">
                                            <li><?php echo esc_html__('Ensure someone is available to provide access', 'pro-clean-quotation'); ?></li>
                                            <li><?php echo esc_html__('Clear the work area if needed', 'pro-clean-quotation'); ?></li>
                                            <li><?php echo esc_html__('Secure any fragile items', 'pro-clean-quotation'); ?></li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="color: #7f8c8d; margin: 25px 0 0; font-size: 14px; text-align: center; line-height: 1.6;">
                                <?php echo esc_html__('Looking forward to serving you tomorrow!', 'pro-clean-quotation'); ?>
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8f9fa; padding: 25px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e1e5e9;">
                            <p style="color: #2c3e50; margin: 0 0 12px; font-size: 15px; font-weight: 600;">
                                <?php echo esc_html__('Need to Reschedule?', 'pro-clean-quotation'); ?>
                            </p>
                            <?php if ($company_phone): ?>
                            <p style="color: #7f8c8d; margin: 0 0 15px; font-size: 14px;">
                                <strong><?php echo esc_html__('Call us:', 'pro-clean-quotation'); ?></strong>
                                <a href="tel:<?php echo esc_attr($company_phone); ?>" style="color: #FF9800; text-decoration: none; font-weight: 600;">
                                    <?php echo esc_html($company_phone); ?>
                                </a>
                            </p>
                            <?php endif; ?>
                            
                            <p style="color: #95a5a6; margin: 15px 0 0; font-size: 12px;">
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
