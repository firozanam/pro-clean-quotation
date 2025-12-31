<?php

namespace ProClean\Quotation\Email;

use ProClean\Quotation\Models\Quote;
use ProClean\Quotation\Admin\Settings;

/**
 * Email Manager Class
 * 
 * @package ProClean\Quotation\Email
 * @since 1.0.0
 */
class EmailManager {
    
    /**
     * Email manager instance
     * 
     * @var EmailManager
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return EmailManager
     */
    public static function getInstance(): EmailManager {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->initHooks();
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function initHooks(): void {
        add_filter('wp_mail_content_type', [$this, 'setHtmlContentType']);
        add_action('wp_mail_failed', [$this, 'logEmailError']);
    }
    
    /**
     * Get service type label
     * 
     * @param string $service_type Service type code
     * @return string Formatted service type label
     */
    private function getServiceTypeLabel(string $service_type): string {
        $labels = [
            // New format (from quote form)
            'facade' => __('Façade Cleaning', 'pro-clean-quotation'),
            'roof' => __('Roof Cleaning', 'pro-clean-quotation'),
            'both' => __('Both Services', 'pro-clean-quotation'),
            // Legacy format (backward compatibility)
            'facade_cleaning' => __('Façade Cleaning', 'pro-clean-quotation'),
            'roof_cleaning' => __('Roof Cleaning', 'pro-clean-quotation'),
            'complete_package' => __('Complete Package', 'pro-clean-quotation'),
            'window_cleaning' => __('Window Cleaning', 'pro-clean-quotation')
        ];
        
        return $labels[$service_type] ?? ucfirst(str_replace('_', ' ', $service_type));
    }
    
    /**
     * Send quote confirmation email to customer
     * 
     * @param Quote $quote Quote object
     * @return bool Success status
     */
    public function sendQuoteConfirmation(Quote $quote): bool {
        if (!Settings::get('email_notifications_enabled', true)) {
            return false;
        }
        
        $to = $quote->getCustomerEmail();
        $subject = sprintf(
            __('Your Cleaning Service Quote #%s - %s', 'pro-clean-quotation'),
            $quote->getQuoteNumber(),
            Settings::get('company_name', get_bloginfo('name'))
        );
        
        $template_data = [
            'quote' => $quote,
            'company_name' => Settings::get('company_name', get_bloginfo('name')),
            'company_email' => Settings::get('company_email', get_option('admin_email')),
            'company_phone' => Settings::get('company_phone', ''),
            'booking_url' => $this->generateBookingUrl($quote)
        ];
        
        $message = $this->renderTemplate('quote-confirmation', $template_data);
        $headers = $this->getEmailHeaders();
        
        // Generate PDF attachment
        $pdf_path = $this->generateQuotePDF($quote);
        $attachments = $pdf_path ? [$pdf_path] : [];
        
        $sent = wp_mail($to, $subject, $message, $headers, $attachments);
        
        // Clean up PDF file
        if ($pdf_path && file_exists($pdf_path)) {
            unlink($pdf_path);
        }
        
        // Log email
        $this->logEmail('quote', $quote->getId(), 'quote_confirmation', $to, $subject, $sent);
        
        return $sent;
    }
    
    /**
     * Send admin notification email
     * 
     * @param Quote $quote Quote object
     * @return bool Success status
     */
    public function sendAdminNotification(Quote $quote): bool {
        if (!Settings::get('email_notifications_enabled', true)) {
            return false;
        }
        
        $to = Settings::get('admin_notification_email', get_option('admin_email'));
        $subject = sprintf(
            __('New Quote Request #%s - %s', 'pro-clean-quotation'),
            $quote->getQuoteNumber(),
            $quote->getCustomerName()
        );
        
        $template_data = [
            'quote' => $quote,
            'admin_url' => admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote->getId())
        ];
        
        $message = $this->renderTemplate('admin-notification', $template_data);
        $headers = $this->getEmailHeaders();
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Log email
        $this->logEmail('quote', $quote->getId(), 'admin_notification', $to, $subject, $sent);
        
        return $sent;
    }
    
    /**
     * Send booking confirmation email
     * 
     * @param array $booking_data Booking data
     * @return bool Success status
     */
    public function sendBookingConfirmation(array $booking_data): bool {
        if (!Settings::get('email_notifications_enabled', true)) {
            return false;
        }
        
        $customer_sent = false;
        $admin_sent = false;
        
        // Send customer confirmation email
        $to = $booking_data['customer_email'];
        $subject = sprintf(
            __('Booking Confirmed #%s - %s', 'pro-clean-quotation'),
            $booking_data['booking_number'],
            Settings::get('company_name', get_bloginfo('name'))
        );
        
        $template_data = [
            'booking' => $booking_data,
            'company_name' => Settings::get('company_name', get_bloginfo('name')),
            'company_email' => Settings::get('company_email', get_option('admin_email')),
            'company_phone' => Settings::get('company_phone', '')
        ];
        
        $message = $this->renderTemplate('booking-confirmation', $template_data);
        $headers = $this->getEmailHeaders();
        
        $customer_sent = wp_mail($to, $subject, $message, $headers);
        
        // Log customer email
        $this->logEmail('booking', $booking_data['id'] ?? 0, 'booking_confirmation', $to, $subject, $customer_sent);
        
        // Send admin notification email
        $admin_email = Settings::get('admin_notification_email', get_option('admin_email'));
        $admin_subject = sprintf(
            __('New Booking #%s - %s', 'pro-clean-quotation'),
            $booking_data['booking_number'],
            $booking_data['customer_name']
        );
        
        $admin_template_data = [
            'booking' => $booking_data,
            'admin_url' => admin_url('admin.php?page=pcq-bookings&action=view&id=' . ($booking_data['id'] ?? 0))
        ];
        
        $admin_message = $this->renderTemplate('booking-admin-notification', $admin_template_data);
        $admin_sent = wp_mail($admin_email, $admin_subject, $admin_message, $headers);
        
        // Log admin email
        $this->logEmail('booking', $booking_data['id'] ?? 0, 'booking_admin_notification', $admin_email, $admin_subject, $admin_sent);
        
        // Return true if at least customer email was sent
        return $customer_sent;
    }
    
    /**
     * Send reminder email
     * 
     * @param array $booking_data Booking data
     * @return bool Success status
     */
    public function sendReminder(array $booking_data): bool {
        if (!Settings::get('email_notifications_enabled', true)) {
            return false;
        }
        
        $to = $booking_data['customer_email'];
        $subject = sprintf(
            __('Reminder: Your Cleaning Service Tomorrow - %s', 'pro-clean-quotation'),
            Settings::get('company_name', get_bloginfo('name'))
        );
        
        $template_data = [
            'booking' => $booking_data,
            'company_name' => Settings::get('company_name', get_bloginfo('name')),
            'company_phone' => Settings::get('company_phone', '')
        ];
        
        $message = $this->renderTemplate('booking-reminder', $template_data);
        $headers = $this->getEmailHeaders();
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Log email
        $this->logEmail('booking', $booking_data['id'] ?? 0, 'booking_reminder', $to, $subject, $sent);
        
        return $sent;
    }
    
    /**
     * Send appointment confirmation email
     * 
     * @param \ProClean\Quotation\Models\Appointment $appointment Appointment object
     * @return bool Success status
     */
    public function sendAppointmentConfirmation(\ProClean\Quotation\Models\Appointment $appointment): bool {
        if (!Settings::get('email_notifications_enabled', true)) {
            return false;
        }
        
        $service = $appointment->getService();
        $employee = $appointment->getEmployee();
        
        $to = $appointment->getCustomerEmail();
        $subject = sprintf(
            __('Appointment Confirmed - %s on %s', 'pro-clean-quotation'),
            $service ? $service->getName() : 'Service',
            date('M j, Y', strtotime($appointment->getServiceDate()))
        );
        
        $template_data = [
            'appointment' => $appointment,
            'service' => $service,
            'employee' => $employee,
            'company_name' => Settings::get('company_name', get_bloginfo('name')),
            'company_email' => Settings::get('company_email', get_option('admin_email')),
            'company_phone' => Settings::get('company_phone', '')
        ];
        
        $message = $this->renderTemplate('appointment-confirmation', $template_data);
        $headers = $this->getEmailHeaders();
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Log email
        $this->logEmail('appointment', $appointment->getId(), 'appointment_confirmation', $to, $subject, $sent);
        
        return $sent;
    }
    
    /**
     * Send appointment rescheduled email
     * 
     * @param \ProClean\Quotation\Models\Appointment $appointment Appointment object
     * @return bool Success status
     */
    public function sendAppointmentRescheduled(\ProClean\Quotation\Models\Appointment $appointment): bool {
        if (!Settings::get('email_notifications_enabled', true)) {
            return false;
        }
        
        $service = $appointment->getService();
        
        $to = $appointment->getCustomerEmail();
        $subject = sprintf(
            __('Appointment Rescheduled - %s', 'pro-clean-quotation'),
            $service ? $service->getName() : 'Service'
        );
        
        $template_data = [
            'appointment' => $appointment,
            'service' => $service,
            'company_name' => Settings::get('company_name', get_bloginfo('name')),
            'company_phone' => Settings::get('company_phone', '')
        ];
        
        $message = $this->renderTemplate('appointment-rescheduled', $template_data);
        $headers = $this->getEmailHeaders();
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Log email
        $this->logEmail('appointment', $appointment->getId(), 'appointment_rescheduled', $to, $subject, $sent);
        
        return $sent;
    }
    
    /**
     * Send appointment cancelled email
     * 
     * @param \ProClean\Quotation\Models\Appointment $appointment Appointment object
     * @param string $reason Cancellation reason
     * @return bool Success status
     */
    public function sendAppointmentCancelled(\ProClean\Quotation\Models\Appointment $appointment, string $reason = ''): bool {
        if (!Settings::get('email_notifications_enabled', true)) {
            return false;
        }
        
        $service = $appointment->getService();
        
        $to = $appointment->getCustomerEmail();
        $subject = sprintf(
            __('Appointment Cancelled - %s', 'pro-clean-quotation'),
            $service ? $service->getName() : 'Service'
        );
        
        $template_data = [
            'appointment' => $appointment,
            'service' => $service,
            'reason' => $reason,
            'company_name' => Settings::get('company_name', get_bloginfo('name')),
            'company_phone' => Settings::get('company_phone', '')
        ];
        
        $message = $this->renderTemplate('appointment-cancelled', $template_data);
        $headers = $this->getEmailHeaders();
        
        $sent = wp_mail($to, $subject, $message, $headers);
        
        // Log email
        $this->logEmail('appointment', $appointment->getId(), 'appointment_cancelled', $to, $subject, $sent);
        
        return $sent;
    }
    
    /**
     * Render email template
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return string Rendered HTML
     */
    private function renderTemplate(string $template, array $data): string {
        $template_path = $this->getTemplatePath($template . '.php');
        
        if (!file_exists($template_path)) {
            return $this->getDefaultTemplate($template, $data);
        }
        
        ob_start();
        extract($data);
        include $template_path;
        return ob_get_clean();
    }
    
    /**
     * Get email template path
     * 
     * @param string $template Template filename
     * @return string Template path
     */
    private function getTemplatePath(string $template): string {
        // Check theme override first
        $theme_template = get_stylesheet_directory() . '/pro-clean-quotation/email/' . $template;
        if (file_exists($theme_template)) {
            return $theme_template;
        }
        
        // Check parent theme
        $parent_template = get_template_directory() . '/pro-clean-quotation/email/' . $template;
        if (file_exists($parent_template)) {
            return $parent_template;
        }
        
        // Use plugin template
        return PCQ_PLUGIN_DIR . 'templates/email/' . $template;
    }
    
    /**
     * Get default email template
     * 
     * @param string $template Template name
     * @param array $data Template data
     * @return string HTML content
     */
    private function getDefaultTemplate(string $template, array $data): string {
        switch ($template) {
            case 'quote-confirmation':
                return $this->getQuoteConfirmationTemplate($data);
            case 'admin-notification':
                return $this->getAdminNotificationTemplate($data);
            case 'booking-confirmation':
                return $this->getBookingConfirmationTemplate($data);
            case 'booking-admin-notification':
                return $this->getBookingAdminNotificationTemplate($data);
            case 'booking-reminder':
                return $this->getBookingReminderTemplate($data);
            case 'appointment-confirmation':
                return $this->getAppointmentConfirmationTemplate($data);
            case 'appointment-rescheduled':
                return $this->getAppointmentRescheduledTemplate($data);
            case 'appointment-cancelled':
                return $this->getAppointmentCancelledTemplate($data);
            default:
                return '<p>Email template not found.</p>';
        }
    }
    
    /**
     * Get quote confirmation email template
     * 
     * @param array $data Template data
     * @return string HTML content
     */
    private function getQuoteConfirmationTemplate(array $data): string {
        $quote = $data['quote'];
        $breakdown = $quote->getPriceBreakdown();
        
        $html = $this->getEmailHeader($data['company_name']);
        
        $html .= '<h2 style="color: #2c3e50; margin-bottom: 20px;">Your Cleaning Service Quote</h2>';
        
        $html .= '<p>Dear ' . esc_html($quote->getCustomerName()) . ',</p>';
        $html .= '<p>Thank you for requesting a quote from ' . esc_html($data['company_name']) . '.</p>';
        
        // Service details
        $html .= '<h3 style="color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 5px;">Service Details</h3>';
        $html .= '<table style="width: 100%; margin-bottom: 20px;">';
        $html .= '<tr><td><strong>Service Type:</strong></td><td>' . esc_html($this->getServiceTypeLabel($quote->getServiceType())) . '</td></tr>';
        $html .= '<tr><td><strong>Property Size:</strong></td><td>' . number_format($quote->getSquareMeters(), 1) . ' sqm</td></tr>';
        if ($quote->getLinearMeters() > 0) {
            $html .= '<tr><td><strong>Linear Meters:</strong></td><td>' . number_format($quote->getLinearMeters(), 1) . ' m</td></tr>';
        }
        $html .= '<tr><td><strong>Property Type:</strong></td><td>' . esc_html(ucfirst($quote->getPropertyType())) . '</td></tr>';
        $html .= '<tr><td><strong>Building Height:</strong></td><td>' . $quote->getBuildingHeight() . ' floors</td></tr>';
        $html .= '</table>';
        
        // Price breakdown
        $html .= '<h3 style="color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 5px;">Estimated Quote</h3>';
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        
        foreach ($breakdown as $key => $item) {
            $style = $key === 'total' ? 'font-weight: bold; border-top: 2px solid #3498db; padding-top: 10px;' : '';
            $html .= '<tr style="' . $style . '">';
            $html .= '<td style="padding: 8px 0;">' . esc_html($item['label']) . '</td>';
            $html .= '<td style="text-align: right; padding: 8px 0;">€' . number_format($item['amount'], 2) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        $html .= '<p style="background: #f8f9fa; padding: 15px; border-left: 4px solid #3498db; margin: 20px 0;">';
        $html .= '<strong>Quote Valid Until:</strong> ' . date('F j, Y', strtotime($quote->getValidUntil())) . '<br>';
        $html .= '<em>This is an estimated quote based on the information provided. Final pricing may vary after on-site assessment.</em>';
        $html .= '</p>';
        
        // Next steps
        $html .= '<h3 style="color: #34495e; border-bottom: 2px solid #3498db; padding-bottom: 5px;">Next Steps</h3>';
        $html .= '<p>Ready to book? <a href="' . esc_url($data['booking_url']) . '" style="color: #3498db; text-decoration: none; font-weight: bold;">Click here to schedule your service</a></p>';
        
        $html .= '<p>If you have any questions, please don\'t hesitate to contact us:</p>';
        $html .= '<ul>';
        $html .= '<li>Email: <a href="mailto:' . esc_attr($data['company_email']) . '">' . esc_html($data['company_email']) . '</a></li>';
        if ($data['company_phone']) {
            $html .= '<li>Phone: <a href="tel:' . esc_attr($data['company_phone']) . '">' . esc_html($data['company_phone']) . '</a></li>';
        }
        $html .= '</ul>';
        
        $html .= '<p>Best regards,<br>' . esc_html($data['company_name']) . '</p>';
        
        $html .= $this->getEmailFooter();
        
        return $html;
    }
    
    /**
     * Get admin notification email template
     * 
     * @param array $data Template data
     * @return string HTML content
     */
    private function getAdminNotificationTemplate(array $data): string {
        $quote = $data['quote'];
        
        $html = $this->getEmailHeader('Admin Notification');
        
        $html .= '<h2 style="color: #e74c3c;">New Quote Request</h2>';
        
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Quote Number:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($quote->getQuoteNumber()) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Customer:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($quote->getCustomerName()) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Email:</strong></td><td style="padding: 8px; border: 1px solid #ddd;"><a href="mailto:' . esc_attr($quote->getCustomerEmail()) . '">' . esc_html($quote->getCustomerEmail()) . '</a></td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Phone:</strong></td><td style="padding: 8px; border: 1px solid #ddd;"><a href="tel:' . esc_attr($quote->getCustomerPhone()) . '">' . esc_html($quote->getCustomerPhone()) . '</a></td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Service:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($this->getServiceTypeLabel($quote->getServiceType())) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Property Size:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . number_format($quote->getSquareMeters(), 1) . ' sqm</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Address:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($quote->getPropertyAddress()) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Estimated Value:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">€' . number_format($quote->getTotalPrice(), 2) . '</td></tr>';
        $html .= '</table>';
        
        if ($quote->getSpecialRequirements()) {
            $html .= '<h3>Special Requirements:</h3>';
            $html .= '<p style="background: #f8f9fa; padding: 15px; border-left: 4px solid #f39c12;">' . esc_html($quote->getSpecialRequirements()) . '</p>';
        }
        
        $html .= '<p><a href="' . esc_url($data['admin_url']) . '" style="background: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">View Quote in Admin</a></p>';
        
        $html .= $this->getEmailFooter();
        
        return $html;
    }
    
    /**
     * Get booking confirmation email template
     * 
     * @param array $data Template data
     * @return string HTML content
     */
    private function getBookingConfirmationTemplate(array $data): string {
        $booking = $data['booking'];
        
        $html = $this->getEmailHeader($data['company_name']);
        
        $html .= '<h2 style="color: #27ae60;">Booking Confirmed!</h2>';
        
        $html .= '<p>Dear ' . esc_html($booking['customer_name']) . ',</p>';
        $html .= '<p>Your cleaning service has been successfully scheduled!</p>';
        
        // Booking details table
        $html .= '<h3 style="color: #34495e; border-bottom: 2px solid #27ae60; padding-bottom: 5px;">Booking Details</h3>';
        $html .= '<table style="width: 100%; margin-bottom: 20px;">';
        $html .= '<tr><td><strong>Booking Reference:</strong></td><td>#' . esc_html($booking['booking_number']) . '</td></tr>';
        $html .= '<tr><td><strong>Service:</strong></td><td>' . esc_html($this->getServiceTypeLabel($booking['service_type'])) . '</td></tr>';
        $html .= '<tr><td><strong>Date:</strong></td><td>' . date('l, F j, Y', strtotime($booking['service_date'])) . '</td></tr>';
        $html .= '<tr><td><strong>Time:</strong></td><td>' . date('H:i', strtotime($booking['service_time_start'])) . ' - ' . date('H:i', strtotime($booking['service_time_end'])) . '</td></tr>';
        $html .= '<tr><td><strong>Address:</strong></td><td>' . esc_html($booking['property_address']) . '</td></tr>';
        $html .= '</table>';
        
        $html .= '<p style="background: #d4edda; padding: 15px; border-left: 4px solid #27ae60; margin: 20px 0;">';
        $html .= '<strong>What to expect:</strong><br>';
        $html .= '• Our team will arrive within the scheduled time window<br>';
        $html .= '• Please ensure access to the property<br>';
        $html .= '• Water source access required<br>';
        $html .= '• Free cancellation up to 48 hours before appointment';
        $html .= '</p>';
        
        $html .= '<p>Need to reschedule or have questions? Contact us at <a href="tel:' . esc_attr($data['company_phone']) . '">' . esc_html($data['company_phone']) . '</a></p>';
        
        $html .= '<p>See you soon!<br>' . esc_html($data['company_name']) . '</p>';
        
        $html .= $this->getEmailFooter();
        
        return $html;
    }
    
    /**
     * Get booking admin notification email template
     * 
     * @param array $data Template data
     * @return string HTML content
     */
    private function getBookingAdminNotificationTemplate(array $data): string {
        $booking = $data['booking'];
        
        $html = $this->getEmailHeader('Admin Notification');
        
        $html .= '<h2 style="color: #27ae60;">New Booking Received!</h2>';
        
        $html .= '<p>A new cleaning service has been booked. Details below:</p>';
        
        // Booking details table
        $html .= '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Booking Number:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($booking['booking_number']) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Customer:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($booking['customer_name']) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Email:</strong></td><td style="padding: 8px; border: 1px solid #ddd;"><a href="mailto:' . esc_attr($booking['customer_email']) . '">' . esc_html($booking['customer_email']) . '</a></td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Phone:</strong></td><td style="padding: 8px; border: 1px solid #ddd;"><a href="tel:' . esc_attr($booking['customer_phone']) . '">' . esc_html($booking['customer_phone']) . '</a></td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Service:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($this->getServiceTypeLabel($booking['service_type'])) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Date:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . date('l, F j, Y', strtotime($booking['service_date'])) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Time:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . date('H:i', strtotime($booking['service_time_start'])) . ' - ' . date('H:i', strtotime($booking['service_time_end'])) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Duration:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . ($booking['estimated_duration'] ?? 'N/A') . ' hours</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Address:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($booking['property_address']) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Postal Code:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($booking['postal_code']) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>City:</strong></td><td style="padding: 8px; border: 1px solid #ddd;">' . esc_html($booking['city']) . '</td></tr>';
        $html .= '<tr><td style="padding: 8px; border: 1px solid #ddd;"><strong>Total Amount:</strong></td><td style="padding: 8px; border: 1px solid #ddd;"><strong>€' . number_format($booking['total_amount'], 2) . '</strong></td></tr>';
        $html .= '</table>';
        
        // Customer notes if available
        if (!empty($booking['customer_notes'])) {
            $html .= '<h3>Customer Notes:</h3>';
            $html .= '<p style="background: #f8f9fa; padding: 15px; border-left: 4px solid #27ae60;">' . esc_html($booking['customer_notes']) . '</p>';
        }
        
        // Special requirements if available
        if (!empty($booking['special_requirements'])) {
            $html .= '<h3>Special Requirements:</h3>';
            $html .= '<p style="background: #fff3cd; padding: 15px; border-left: 4px solid #f39c12;">' . esc_html($booking['special_requirements']) . '</p>';
        }
        
        $html .= '<p style="background: #d4edda; padding: 15px; border-left: 4px solid #27ae60; margin: 20px 0;">';
        $html .= '<strong>Action Required:</strong><br>';
        $html .= '• Review booking details<br>';
        $html .= '• Assign team members<br>';
        $html .= '• Prepare equipment<br>';
        $html .= '• Contact customer if needed';
        $html .= '</p>';
        
        $html .= '<p><a href="' . esc_url($data['admin_url']) . '" style="background: #27ae60; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">View Booking in Admin</a></p>';
        
        $html .= $this->getEmailFooter();
        
        return $html;
    }
    
    /**
     * Get booking reminder email template
     * 
     * @param array $data Template data
     * @return string HTML content
     */
    private function getBookingReminderTemplate(array $data): string {
        $booking = $data['booking'];
        
        $html = $this->getEmailHeader($data['company_name']);
        
        $html .= '<h2 style="color: #f39c12;">Reminder: Your Cleaning Service Tomorrow</h2>';
        
        $html .= '<p>Dear ' . esc_html($booking['customer_name']) . ',</p>';
        $html .= '<p>This is a friendly reminder that your cleaning service is scheduled for tomorrow.</p>';
        
        $html .= '<div style="background: #fff3cd; padding: 20px; border-left: 4px solid #f39c12; margin: 20px 0;">';
        $html .= '<h3 style="margin-top: 0;">Tomorrow\'s Appointment</h3>';
        $html .= '<p><strong>Date:</strong> ' . date('l, F j, Y', strtotime($booking['service_date'])) . '</p>';
        $html .= '<p><strong>Time:</strong> ' . date('H:i', strtotime($booking['service_time_start'])) . ' - ' . date('H:i', strtotime($booking['service_time_end'])) . '</p>';
        $html .= '<p><strong>Service:</strong> ' . esc_html(ucfirst($booking['service_type'])) . '</p>';
        $html .= '</div>';
        
        $html .= '<p>Please ensure:</p>';
        $html .= '<ul>';
        $html .= '<li>Access to the property is available</li>';
        $html .= '<li>Water source is accessible</li>';
        $html .= '<li>Any vehicles are moved from the work area</li>';
        $html .= '</ul>';
        
        $html .= '<p>If you need to reschedule or have any questions, please call us immediately at <a href="tel:' . esc_attr($data['company_phone']) . '">' . esc_html($data['company_phone']) . '</a></p>';
        
        $html .= '<p>Thank you!<br>' . esc_html($data['company_name']) . '</p>';
        
        $html .= $this->getEmailFooter();
        
        return $html;
    }
    
    /**
     * Get appointment confirmation email template
     * 
     * @param array $data Template data
     * @return string HTML content
     */
    private function getAppointmentConfirmationTemplate(array $data): string {
        $appointment = $data['appointment'];
        $service = $data['service'];
        $employee = $data['employee'];
        
        $html = $this->getEmailHeader($data['company_name']);
        
        $html .= '<h2 style="color: #27ae60;">Appointment Confirmed!</h2>';
        
        $html .= '<p>Dear ' . esc_html($appointment->getCustomerName()) . ',</p>';
        $html .= '<p>Your appointment has been successfully scheduled!</p>';
        
        // Appointment details
        $html .= '<h3 style="color: #34495e; border-bottom: 2px solid #27ae60; padding-bottom: 5px;">Appointment Details</h3>';
        $html .= '<table style="width: 100%; margin-bottom: 20px;">';
        $html .= '<tr><td><strong>Service:</strong></td><td>' . ($service ? esc_html($service->getName()) : 'Service') . '</td></tr>';
        $html .= '<tr><td><strong>Date:</strong></td><td>' . date('l, F j, Y', strtotime($appointment->getServiceDate())) . '</td></tr>';
        $html .= '<tr><td><strong>Time:</strong></td><td>' . $appointment->getServiceTimeStart() . ' - ' . $appointment->getServiceTimeEnd() . '</td></tr>';
        $html .= '<tr><td><strong>Duration:</strong></td><td>' . $appointment->getDuration() . ' minutes</td></tr>';
        if ($employee) {
            $html .= '<tr><td><strong>Assigned to:</strong></td><td>' . esc_html($employee->getName()) . '</td></tr>';
        }
        $html .= '<tr><td><strong>Price:</strong></td><td>€' . number_format($appointment->getPrice(), 2) . '</td></tr>';
        $html .= '</table>';
        
        if ($appointment->getNotes()) {
            $html .= '<h3>Notes:</h3>';
            $html .= '<p style="background: #f8f9fa; padding: 15px; border-left: 4px solid #27ae60;">' . esc_html($appointment->getNotes()) . '</p>';
        }
        
        $html .= '<p style="background: #d4edda; padding: 15px; border-left: 4px solid #27ae60; margin: 20px 0;">';
        $html .= '<strong>What to expect:</strong><br>';
        $html .= '• Our team will arrive within the scheduled time window<br>';
        $html .= '• Please ensure access to the property<br>';
        $html .= '• Water source access required<br>';
        $html .= '• Free cancellation up to 48 hours before appointment';
        $html .= '</p>';
        
        $html .= '<p>Need to reschedule or have questions? Contact us at <a href="tel:' . esc_attr($data['company_phone']) . '">' . esc_html($data['company_phone']) . '</a></p>';
        
        $html .= '<p>See you soon!<br>' . esc_html($data['company_name']) . '</p>';
        
        $html .= $this->getEmailFooter();
        
        return $html;
    }
    
    /**
     * Get appointment rescheduled email template
     * 
     * @param array $data Template data
     * @return string HTML content
     */
    private function getAppointmentRescheduledTemplate(array $data): string {
        $appointment = $data['appointment'];
        $service = $data['service'];
        
        $html = $this->getEmailHeader($data['company_name']);
        
        $html .= '<h2 style="color: #f39c12;">Appointment Rescheduled</h2>';
        
        $html .= '<p>Dear ' . esc_html($appointment->getCustomerName()) . ',</p>';
        $html .= '<p>Your appointment has been successfully rescheduled to a new date and time.</p>';
        
        // New appointment details
        $html .= '<h3 style="color: #34495e; border-bottom: 2px solid #f39c12; padding-bottom: 5px;">New Appointment Details</h3>';
        $html .= '<table style="width: 100%; margin-bottom: 20px;">';
        $html .= '<tr><td><strong>Service:</strong></td><td>' . ($service ? esc_html($service->getName()) : 'Service') . '</td></tr>';
        $html .= '<tr><td><strong>New Date:</strong></td><td>' . date('l, F j, Y', strtotime($appointment->getServiceDate())) . '</td></tr>';
        $html .= '<tr><td><strong>New Time:</strong></td><td>' . $appointment->getServiceTimeStart() . ' - ' . $appointment->getServiceTimeEnd() . '</td></tr>';
        $html .= '<tr><td><strong>Duration:</strong></td><td>' . $appointment->getDuration() . ' minutes</td></tr>';
        $html .= '</table>';
        
        $html .= '<p style="background: #fff3cd; padding: 15px; border-left: 4px solid #f39c12; margin: 20px 0;">';
        $html .= '<strong>Please note:</strong> Make sure to update your calendar with the new appointment time.';
        $html .= '</p>';
        
        $html .= '<p>If you have any questions about this change, please contact us at <a href="tel:' . esc_attr($data['company_phone']) . '">' . esc_html($data['company_phone']) . '</a></p>';
        
        $html .= '<p>Thank you for your understanding!<br>' . esc_html($data['company_name']) . '</p>';
        
        $html .= $this->getEmailFooter();
        
        return $html;
    }
    
    /**
     * Get appointment cancelled email template
     * 
     * @param array $data Template data
     * @return string HTML content
     */
    private function getAppointmentCancelledTemplate(array $data): string {
        $appointment = $data['appointment'];
        $service = $data['service'];
        $reason = $data['reason'];
        
        $html = $this->getEmailHeader($data['company_name']);
        
        $html .= '<h2 style="color: #e74c3c;">Appointment Cancelled</h2>';
        
        $html .= '<p>Dear ' . esc_html($appointment->getCustomerName()) . ',</p>';
        $html .= '<p>We regret to inform you that your appointment has been cancelled.</p>';
        
        // Cancelled appointment details
        $html .= '<h3 style="color: #34495e; border-bottom: 2px solid #e74c3c; padding-bottom: 5px;">Cancelled Appointment</h3>';
        $html .= '<table style="width: 100%; margin-bottom: 20px;">';
        $html .= '<tr><td><strong>Service:</strong></td><td>' . ($service ? esc_html($service->getName()) : 'Service') . '</td></tr>';
        $html .= '<tr><td><strong>Date:</strong></td><td>' . date('l, F j, Y', strtotime($appointment->getServiceDate())) . '</td></tr>';
        $html .= '<tr><td><strong>Time:</strong></td><td>' . $appointment->getServiceTimeStart() . ' - ' . $appointment->getServiceTimeEnd() . '</td></tr>';
        $html .= '</table>';
        
        if ($reason) {
            $html .= '<h3>Reason for Cancellation:</h3>';
            $html .= '<p style="background: #f8d7da; padding: 15px; border-left: 4px solid #e74c3c;">' . esc_html($reason) . '</p>';
        }
        
        $html .= '<p style="background: #f8d7da; padding: 15px; border-left: 4px solid #e74c3c; margin: 20px 0;">';
        $html .= '<strong>We apologize for any inconvenience this may cause.</strong><br>';
        $html .= 'If you would like to reschedule, please contact us and we\'ll be happy to find a new time that works for you.';
        $html .= '</p>';
        
        $html .= '<p>To reschedule or if you have any questions, please contact us at <a href="tel:' . esc_attr($data['company_phone']) . '">' . esc_html($data['company_phone']) . '</a></p>';
        
        $html .= '<p>Thank you for your understanding.<br>' . esc_html($data['company_name']) . '</p>';
        
        $html .= $this->getEmailFooter();
        
        return $html;
    }
    
    /**
     * Get email headers
     * 
     * @return array Email headers
     */
    private function getEmailHeaders(): array {
        $from_name = Settings::get('email_from_name', get_bloginfo('name'));
        $from_email = Settings::get('email_from_address', get_option('admin_email'));
        
        return [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_email
        ];
    }
    
    /**
     * Get email header HTML
     * 
     * @param string $title Email title
     * @return string HTML header
     */
    private function getEmailHeader(string $title): string {
        $company_logo = Settings::get('company_logo', '');
        
        $html = '<!DOCTYPE html>';
        $html .= '<html><head><meta charset="UTF-8"><title>' . esc_html($title) . '</title></head>';
        $html .= '<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">';
        
        if ($company_logo) {
            $html .= '<div style="text-align: center; margin-bottom: 30px;">';
            $html .= '<img src="' . esc_url($company_logo) . '" alt="Company Logo" style="max-width: 200px; height: auto;">';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * Get email footer HTML
     * 
     * @return string HTML footer
     */
    private function getEmailFooter(): string {
        $company_name = Settings::get('company_name', get_bloginfo('name'));
        $company_address = Settings::get('company_address', '');
        
        $html = '<hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">';
        $html .= '<div style="font-size: 12px; color: #666; text-align: center;">';
        $html .= '<p>' . esc_html($company_name);
        
        if ($company_address) {
            $html .= '<br>' . esc_html($company_address);
        }
        
        $html .= '</p>';
        $html .= '<p>This email was sent automatically. Please do not reply to this email.</p>';
        $html .= '</div>';
        $html .= '</body></html>';
        
        return $html;
    }
    
    /**
     * Generate booking URL
     * 
     * @param Quote $quote Quote object
     * @return string Booking URL
     */
    private function generateBookingUrl(Quote $quote): string {
        $booking_page = get_option('pcq_booking_page_id');
        
        if ($booking_page) {
            return add_query_arg([
                'quote_id' => $quote->getId(),
                'token' => $quote->getToken()
            ], get_permalink($booking_page));
        }
        
        return home_url('/book-service/?quote_id=' . $quote->getId() . '&token=' . $quote->getToken());
    }
    
    /**
     * Generate quote PDF
     * 
     * @param Quote $quote Quote object
     * @return string|false PDF file path or false on failure
     */
    private function generateQuotePDF(Quote $quote) {
        // Check if PDF generation is enabled
        if (!Settings::get('pdf_generation_enabled', true)) {
            return false;
        }
        
        // Use PDFGenerator service
        $pdf_generator = \ProClean\Quotation\Services\PDFGenerator::getInstance();
        return $pdf_generator->generateQuotePDF($quote);
    }
    
    /**
     * Log email to database
     * 
     * @param string $reference_type Reference type
     * @param int $reference_id Reference ID
     * @param string $email_type Email type
     * @param string $recipient Recipient email
     * @param string $subject Email subject
     * @param bool $sent Success status
     */
    private function logEmail(string $reference_type, int $reference_id, string $email_type, string $recipient, string $subject, bool $sent): void {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_email_logs';
        
        $wpdb->insert($table, [
            'reference_type' => $reference_type,
            'reference_id' => $reference_id,
            'email_type' => $email_type,
            'recipient_email' => $recipient,
            'subject' => $subject,
            'sent_at' => current_time('mysql'),
            'status' => $sent ? 'sent' : 'failed'
        ]);
    }
    
    /**
     * Set HTML content type for emails
     * 
     * @return string Content type
     */
    public function setHtmlContentType(): string {
        return 'text/html';
    }
    
    /**
     * Log email errors
     * 
     * @param \WP_Error $wp_error WordPress error object
     */
    public function logEmailError(\WP_Error $wp_error): void {
        error_log('PCQ Email Error: ' . $wp_error->get_error_message());
    }
}