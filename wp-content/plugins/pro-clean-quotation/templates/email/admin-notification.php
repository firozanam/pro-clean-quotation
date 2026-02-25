<?php
/**
 * Email Template: Admin Notification
 * 
 * Available variables:
 * @var \ProClean\Quotation\Models\Quote $quote
 * @var string $admin_url
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
    <title><?php echo esc_html__('New Quote Request', 'pro-clean-quotation'); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif; background-color: #f4f4f4;">
    
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f4;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #FF6B6B 0%, #EE5A6F 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 700;">
                                <?php echo esc_html__('🔔 New Quote Request', 'pro-clean-quotation'); ?>
                            </h1>
                            <p style="color: #ffffff; margin: 10px 0 0; font-size: 14px; opacity: 0.9;">
                                <?php echo esc_html__('Admin Notification', 'pro-clean-quotation'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            
                            <p style="color: #2c3e50; margin: 0 0 25px; font-size: 15px;">
                                <?php echo esc_html__('A new quote request has been submitted on your website.', 'pro-clean-quotation'); ?>
                            </p>
                            
                            <!-- Customer Information -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="color: #2c3e50; margin: 0 0 15px; font-size: 16px; font-weight: 600;">
                                            <?php echo esc_html__('Customer Information', 'pro-clean-quotation'); ?>
                                        </h3>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px; width: 40%;">
                                                    <?php echo esc_html__('Name:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
                                                    <?php echo esc_html($quote->getCustomerName()); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Email:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #2c3e50; font-size: 14px;">
                                                    <a href="mailto:<?php echo esc_attr($quote->getCustomerEmail()); ?>" style="color: #2196F3; text-decoration: none;">
                                                        <?php echo esc_html($quote->getCustomerEmail()); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Phone:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #2c3e50; font-size: 14px;">
                                                    <a href="tel:<?php echo esc_attr($quote->getCustomerPhone()); ?>" style="color: #2196F3; text-decoration: none;">
                                                        <?php echo esc_html($quote->getCustomerPhone()); ?>
                                                    </a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Address:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($quote->getPropertyAddress()); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Service Details -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #f8f9fa; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="color: #2c3e50; margin: 0 0 15px; font-size: 16px; font-weight: 600;">
                                            <?php echo esc_html__('Service Details', 'pro-clean-quotation'); ?>
                                        </h3>
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px; width: 40%;">
                                                    <?php echo esc_html__('Quote Number:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #2c3e50; font-size: 14px; font-weight: 600;">
                                                    #<?php echo esc_html($quote->getQuoteNumber()); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Service Type:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($quote->getServiceName()); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Property Size:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html(number_format($quote->getSquareMeters(), 1)); ?> m²
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Building Height:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #2c3e50; font-size: 14px;">
                                                    <?php echo esc_html($quote->getBuildingHeight()); ?> <?php echo esc_html__('floors', 'pro-clean-quotation'); ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 6px 0; color: #7f8c8d; font-size: 14px;">
                                                    <?php echo esc_html__('Total Price:', 'pro-clean-quotation'); ?>
                                                </td>
                                                <td style="padding: 6px 0; color: #4CAF50; font-size: 18px; font-weight: 700;">
                                                    €<?php echo esc_html(number_format($quote->getTotalPrice(), 2)); ?>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <?php if ($quote->getSpecialRequirements()): ?>
                            <!-- Special Requirements -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; margin-bottom: 25px;">
                                <tr>
                                    <td style="padding: 15px;">
                                        <h4 style="color: #856404; margin: 0 0 10px; font-size: 14px; font-weight: 600;">
                                            <?php echo esc_html__('Special Requirements:', 'pro-clean-quotation'); ?>
                                        </h4>
                                        <p style="color: #856404; margin: 0; font-size: 14px; line-height: 1.6;">
                                            <?php echo esc_html($quote->getSpecialRequirements()); ?>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <?php endif; ?>
                            
                            <!-- Action Button -->
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?php echo esc_url($admin_url); ?>" style="display: inline-block; background: #2196F3; color: #ffffff; text-decoration: none; padding: 14px 35px; border-radius: 50px; font-size: 15px; font-weight: 600; box-shadow: 0 4px 12px rgba(33, 150, 243, 0.3);">
                                    <?php echo esc_html__('View Full Quote', 'pro-clean-quotation'); ?>
                                </a>
                            </div>
                            
                            <p style="color: #7f8c8d; margin: 25px 0 0; font-size: 13px; text-align: center;">
                                <?php echo esc_html__('Please respond to this quote request as soon as possible.', 'pro-clean-quotation'); ?>
                            </p>
                            
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background: #f8f9fa; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e1e5e9;">
                            <p style="color: #95a5a6; margin: 0; font-size: 12px;">
                                <?php echo esc_html__('This is an automated notification from your quotation system.', 'pro-clean-quotation'); ?>
                            </p>
                        </td>
                    </tr>
                    
                </table>
                
            </td>
        </tr>
    </table>
    
</body>
</html>
