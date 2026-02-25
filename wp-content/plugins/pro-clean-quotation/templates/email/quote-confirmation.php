<?php
/**
 * Email Template: Quote Confirmation
 * 
 * Available variables:
 * @var \ProClean\Quotation\Models\Quote $quote
 * @var string $company_name
 * @var string $company_email
 * @var string $company_phone
 * @var string $booking_url
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

$breakdown = $quote->getPriceBreakdown();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html__('Quote Confirmation', 'pro-clean-quotation'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #f4f4f4;">
    
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <!-- Main Container -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%); padding: 40px 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: 700;">
                                <?php echo esc_html($company_name); ?>
                            </h1>
                            <p style="color: #ffffff; margin: 10px 0 0; font-size: 16px; opacity: 0.9;">
                                <?php echo esc_html__('Professional Cleaning Services', 'pro-clean-quotation'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            
                            <!-- Success Message -->
                            <div style="text-align: center; margin-bottom: 30px;">
                                <div style="width: 60px; height: 60px; background: #4CAF50; border-radius: 50%; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 13L9 17L19 7" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <h2 style="color: #2c3e50; margin: 0 0 10px; font-size: 24px;">
                                    <?php echo esc_html__('Quote Generated Successfully!', 'pro-clean-quotation'); ?>
                                </h2>
                                <p style="color: #7f8c8d; margin: 0; font-size: 15px;">
                                    <?php echo esc_html__('Thank you for requesting a quote. Your cleaning service quote is ready.', 'pro-clean-quotation'); ?>
                                </p>
                            </div>
                            
                            <!-- Quote Details Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <h3 style="color: #2c3e50; margin: 0 0 20px; font-size: 18px; border-bottom: 2px solid #e1e5e9; padding-bottom: 10px;">
                                            <?php echo esc_html__('Quote Details', 'pro-clean-quotation'); ?>
                                        </h3>
                                        
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Quote Number:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
                                                    #<?php echo esc_html($quote->getQuoteNumber()); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Service Type:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($quote->getServiceName()); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Property Size:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html(number_format($quote->getSquareMeters(), 1)); ?> m²
                                                </td>
                                            </tr>
                                            <?php 
                                            // Display custom fields if available
                                            $custom_fields = $quote->getFormattedCustomFields();
                                            foreach ($custom_fields as $field): 
                                            ?>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html($field['label']); ?>:
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($field['display']); ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Valid Until:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($quote->getValidUntil()))); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Price Breakdown -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 25px;">
                                        <h3 style="color: #2c3e50; margin: 0 0 20px; font-size: 18px;">
                                            <?php echo esc_html__('Price Breakdown', 'pro-clean-quotation'); ?>
                                        </h3>
                                        
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <?php foreach ($breakdown as $item): ?>
                                            <tr>
                                                <td style="padding: 8px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html($item['label']); ?>
                                                </td>
                                                <td align="right" style="padding: 8px 0; color: #2c3e50; font-size: 14px;">
                                                    €<?php echo esc_html(number_format($item['amount'], 2)); ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <tr>
                                                <td colspan="2" style="padding-top: 15px; border-top: 2px solid #2196F3;"></td>
                                            </tr>
                                            <tr style="background: #2196F3;">
                                                <td style="padding: 15px 10px; color: #ffffff; font-size: 18px; font-weight: 700; border-radius: 4px 0 0 4px;">
                                                    <?php echo esc_html__('Total Amount:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td align="right" style="padding: 15px 10px; color: #ffffff; font-size: 22px; font-weight: 700; border-radius: 0 4px 4px 0;">
                                                    €<?php echo esc_html(number_format($quote->getTotalPrice(), 2)); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?php echo esc_url($booking_url); ?>" style="display: inline-block; background: #4CAF50; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 50px; font-size: 16px; font-weight: 600; box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);">
                                    <?php echo esc_html__('Book This Service', 'pro-clean-quotation'); ?>
                                </a>
                            </div>
                            
                            <!-- Additional Info -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #e3f2fd; border-left: 4px solid #2196F3; border-radius: 4px; margin: 30px 0;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h4 style="color: #1976D2; margin: 0 0 15px; font-size: 16px;">
                                            <?php echo esc_html__('Important Information', 'pro-clean-quotation'); ?>
                                        </h4>
                                        <ul style="margin: 0; padding-left: 20px; color: #555; font-size: 14px; line-height: 1.8;">
                                            <li><?php echo esc_html__('This quote is valid for 30 days from the date issued.', 'pro-clean-quotation'); ?></li>
                                            <li><?php echo esc_html__('Actual price may vary based on on-site assessment.', 'pro-clean-quotation'); ?></li>
                                            <li><?php echo esc_html__('A detailed PDF quote is attached to this email.', 'pro-clean-quotation'); ?></li>
                                        </ul>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8f9fa; padding: 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e1e5e9;">
                            <p style="color: #2c3e50; margin: 0 0 15px; font-size: 16px; font-weight: 600;">
                                <?php echo esc_html__('Need Help?', 'pro-clean-quotation'); ?>
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
