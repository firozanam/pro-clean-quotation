<?php
/**
 * Email Template: Appointment Confirmation
 * 
 * Available variables:
 * @var \ProClean\Quotation\Models\Appointment $appointment
 * @var object $service
 * @var object $employee
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
    <title><?php echo esc_html__('Appointment Confirmed', 'pro-clean-quotation'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #f4f4f4;">
    
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <div style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="3" y="4" width="18" height="18" rx="2" stroke="#ffffff" stroke-width="2.5"/>
                                    <line x1="3" y1="9" x2="21" y2="9" stroke="#ffffff" stroke-width="2.5"/>
                                    <path d="M9 17l2 2 4-4" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </div>
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">
                                <?php echo esc_html__('Appointment Confirmed', 'pro-clean-quotation'); ?>
                            </h1>
                            <p style="color: #ffffff; margin: 15px 0 0; font-size: 16px; opacity: 0.95;">
                                <?php echo esc_html($company_name); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            
                            <p style="color: #2c3e50; margin: 0 0 30px; font-size: 16px; text-align: center; line-height: 1.6;">
                                <?php echo esc_html__('Your appointment has been successfully scheduled via MotoPress Appointment system.', 'pro-clean-quotation'); ?>
                            </p>
                            
                            <!-- Appointment Date/Time Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 30px; text-align: center;">
                                        <p style="color: rgba(255,255,255,0.9); margin: 0 0 12px; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                                            <?php echo esc_html__('Scheduled For', 'pro-clean-quotation'); ?>
                                        </p>
                                        <h2 style="color: #ffffff; margin: 0 0 8px; font-size: 32px; font-weight: 700;">
                                            <?php echo esc_html(date_i18n('F j, Y', strtotime($appointment->getServiceDate()))); ?>
                                        </h2>
                                        <p style="color: #ffffff; margin: 0; font-size: 20px; font-weight: 600;">
                                            <?php echo esc_html(date('g:i A', strtotime($appointment->getServiceDate() . ' ' . $appointment->getServiceTimeStart()))); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Service Details -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <h3 style="color: #2c3e50; margin: 0 0 20px; font-size: 18px; font-weight: 600;">
                                            <?php echo esc_html__('Service Information', 'pro-clean-quotation'); ?>
                                        </h3>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <?php if ($service): ?>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Service:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
                                                    <?php echo esc_html($service->getName()); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Duration:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($service->getDuration()); ?> <?php echo esc_html__('minutes', 'pro-clean-quotation'); ?>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            
                                            <?php if ($employee): ?>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Assigned To:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
                                                    <?php echo esc_html($employee->getName()); ?>
                                                </td>
                                            </tr>
                                            <?php endif; ?>
                                            
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Status:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0;">
                                                    <span style="display: inline-block; background: #4CAF50; color: #ffffff; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                                        <?php echo esc_html(ucfirst($appointment->getStatus())); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Customer Details -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <h3 style="color: #2c3e50; margin: 0 0 20px; font-size: 18px; font-weight: 600;">
                                            <?php echo esc_html__('Your Information', 'pro-clean-quotation'); ?>
                                        </h3>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Name:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($appointment->getCustomerName()); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Email:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($appointment->getCustomerEmail()); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Phone:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($appointment->getCustomerPhone()); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Important Notes -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #e8f5e9; border-left: 4px solid #4CAF50; border-radius: 4px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h4 style="color: #2e7d32; margin: 0 0 15px; font-size: 16px; font-weight: 600;">
                                            <?php echo esc_html__('Please Note', 'pro-clean-quotation'); ?>
                                        </h4>
                                        <ul style="margin: 0; padding-left: 20px; color: #2e7d32; font-size: 14px; line-height: 1.8;">
                                            <li><?php echo esc_html__('You will receive a reminder 24 hours before your appointment.', 'pro-clean-quotation'); ?></li>
                                            <li><?php echo esc_html__('To reschedule or cancel, please contact us at least 24 hours in advance.', 'pro-clean-quotation'); ?></li>
                                            <li><?php echo esc_html__('Please be available at the scheduled time.', 'pro-clean-quotation'); ?></li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Add to Calendar Button -->
                            <div style="text-align: center; margin: 30px 0;">
                                <p style="color: #7f8c8d; margin: 0; font-size: 14px;">
                                    <?php echo esc_html__('Add this appointment to your calendar to stay reminded.', 'pro-clean-quotation'); ?>
                                </p>
                            </div>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e1e5e9;">
                            <p style="color: #2c3e50; margin: 0 0 15px; font-size: 16px; font-weight: 600;">
                                <?php echo esc_html__('Questions or Changes?', 'pro-clean-quotation'); ?>
                            </p>
                            <p style="color: #7f8c8d; margin: 0 0 10px; font-size: 14px;">
                                <strong><?php echo esc_html__('Email:', 'pro-clean-quotation'); ?></strong>
                                <a href="mailto:<?php echo esc_attr($company_email); ?>" style="color: #667eea; text-decoration: none;">
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
