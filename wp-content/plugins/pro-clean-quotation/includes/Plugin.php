<?php

namespace ProClean\Quotation;

/**
 * Main Plugin Class
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */
class Plugin {
    
    /**
     * Plugin instance
     * 
     * @var Plugin
     */
    private static $instance = null;
    
    /**
     * Get plugin instance
     * 
     * @return Plugin
     */
    public static function getInstance(): Plugin {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }
    
    /**
     * Initialize plugin
     */
    private function init(): void {
        // Check and update database if needed
        $this->maybeUpdateDatabase();
        
        // Initialize components
        $this->initHooks();
        $this->initComponents();
    }
    
    /**
     * Check and update database if needed
     */
    private function maybeUpdateDatabase(): void {
        // Only run for admin users to avoid performance impact
        if (!is_admin()) {
            return;
        }
        
        // Check if database needs update
        if (Database\Installer::needsUpdate()) {
            Database\Installer::maybeUpdate();
        }
    }
    
    /**
     * Initialize WordPress hooks
     */
    private function initHooks(): void {
        add_action('init', [$this, 'loadTextDomain']);
        add_action('template_redirect', [$this, 'handleBookingRedirect']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminScripts']);
        add_action('wp_head', [$this, 'addAjaxUrlToFrontend'], 1);
        add_action('rest_api_init', [$this, 'initRestAPI']);
        add_action('wp_ajax_pcq_calculate_quote', [$this, 'handleAjaxCalculateQuote']);
        add_action('wp_ajax_nopriv_pcq_calculate_quote', [$this, 'handleAjaxCalculateQuote']);
        add_action('wp_ajax_pcq_submit_quote', [$this, 'handleAjaxSubmitQuote']);
        add_action('wp_ajax_nopriv_pcq_submit_quote', [$this, 'handleAjaxSubmitQuote']);
        add_action('wp_ajax_pcq_get_available_slots', [$this, 'handleAjaxGetAvailableSlots']);
        add_action('wp_ajax_nopriv_pcq_get_available_slots', [$this, 'handleAjaxGetAvailableSlots']);
        add_action('wp_ajax_pcq_create_booking', [$this, 'handleAjaxCreateBooking']);
        add_action('wp_ajax_nopriv_pcq_create_booking', [$this, 'handleAjaxCreateBooking']);
        add_action('wp_ajax_pcq_get_calendar_events', [$this, 'handleAjaxGetCalendarEvents']);
        add_action('wp_ajax_pcq_get_appointment', [$this, 'handleAjaxGetAppointment']);
        add_action('wp_ajax_pcq_save_appointment', [$this, 'handleAjaxSaveAppointment']);
        add_action('wp_ajax_pcq_delete_appointment', [$this, 'handleAjaxDeleteAppointment']);
        add_action('wp_ajax_pcq_test_smtp', [$this, 'handleAjaxTestSMTP']);
        
        // Database health checks
        add_action('admin_notices', [Admin\DatabaseFixer::class, 'showMissingTablesNotice']);
        add_action('admin_init', [Admin\DatabaseFixer::class, 'handleFixAction']);
        
        // Cron jobs
        add_action('pcq_cleanup_temp_pdfs', [$this, 'cleanupTempPDFs']);
    }
    
    /**
     * Initialize plugin components
     */
    private function initComponents(): void {
        // Initialize admin components
        if (is_admin()) {
            Admin\AdminMenu::getInstance();
            Admin\Settings::getInstance();
            Admin\Dashboard::getInstance();
        }
        
        // Initialize frontend components
        Frontend\ShortcodeManager::getInstance();
        Frontend\FormHandler::getInstance();
        
        // Initialize API
        API\QuoteController::getInstance();
        API\BookingController::getInstance();
        
        // Initialize email system
        Email\EmailManager::getInstance();
        Email\SMTPConfig::getInstance();
        
        // Initialize services
        Services\QuoteCalculator::getInstance();
        Services\BookingManager::getInstance();
        Services\PricingEngine::getInstance();
        Services\AppointmentManager::getInstance();
        Services\ReminderManager::getInstance();
        Services\ValidationService::getInstance();
        Services\AvailabilityService::getInstance();
        Services\WebhookManager::getInstance();
        
        // Initialize i18n/multilingual support
        I18n\LanguageManager::getInstance();
        I18n\WPMLCompat::getInstance();
        I18n\PolylangCompat::getInstance();
    }
    
    /**
     * Load text domain
     */
    public function loadTextDomain(): void {
        load_plugin_textdomain(
            'pro-clean-quotation',
            false,
            dirname(PCQ_PLUGIN_BASENAME) . '/languages'
        );
    }
    
    /**
     * Handle booking page redirect for old URLs
     * Redirects /book-service/ to the proper booking page
     * 
     * NOTE: This is disabled because the booking page itself uses the /book-service/ slug.
     * Old emails should work directly now that the page exists.
     */
    public function handleBookingRedirect(): void {
        // Redirect functionality disabled to prevent loops
        // The booking page with slug 'book-service' handles all requests directly
        return;
    }
    
    /**
     * Add ajaxurl to frontend for Elementor and other plugins
     */
    public function addAjaxUrlToFrontend(): void {
        // Skip if in admin, Elementor editor, or ajaxurl already defined
        if (is_admin() || (isset($_GET['elementor-preview']) || isset($_GET['action']) && $_GET['action'] === 'elementor')) {
            return;
        }
        
        // Only add if not already defined by another plugin
        if (!wp_script_is('pcq-ajaxurl-inline', 'done')) {
            ?>
            <script type="text/javascript" id="pcq-ajaxurl-inline">
                if (typeof ajaxurl === 'undefined') {
                    var ajaxurl = <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>;
                }
            </script>
            <?php
        }
    }
    
    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueueScripts(): void {
        wp_enqueue_style(
            'pcq-frontend-style',
            PCQ_PLUGIN_URL . 'assets/css/frontend.css',
            [],
            PCQ_VERSION
        );
        
        wp_enqueue_script(
            'pcq-frontend-script',
            PCQ_PLUGIN_URL . 'assets/js/frontend.js',
            ['jquery'],
            PCQ_VERSION,
            true
        );
        
        // Enqueue live price calculator on pages with quote form
        if (is_page() || is_front_page() || is_home()) {
            wp_enqueue_script(
                'pcq-live-price-calculator',
                PCQ_PLUGIN_URL . 'assets/js/live-price-calculator.js',
                ['jquery'],
                PCQ_VERSION,
                true
            );
        }
        
        // Enqueue booking calendar script on booking pages
        if (is_page() || has_shortcode(get_post()->post_content ?? '', 'pcq_booking_form')) {
            wp_enqueue_script(
                'pcq-booking-calendar',
                PCQ_PLUGIN_URL . 'assets/js/booking-calendar.js',
                ['jquery'],
                PCQ_VERSION,
                true
            );
        }
        
        // Localize script for AJAX
        wp_localize_script('pcq-frontend-script', 'pcq_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pcq_nonce'),
            'strings' => [
                'calculating' => __('Calculating...', 'pro-clean-quotation'),
                'error' => __('An error occurred. Please try again.', 'pro-clean-quotation'),
                'required_field' => __('This field is required.', 'pro-clean-quotation'),
                'invalid_email' => __('Please enter a valid email address.', 'pro-clean-quotation'),
                'invalid_phone' => __('Please enter a valid phone number.', 'pro-clean-quotation'),
                'total_price' => __('Total Price', 'pro-clean-quotation'),
            ]
        ]);
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueueAdminScripts(): void {
        $screen = get_current_screen();
        
        // Debug: Log the screen ID
        error_log('PCQ: Current screen ID: ' . $screen->id);
        
        // Check if we're on any of our plugin pages
        $is_plugin_page = (
            strpos($screen->id, 'pro-clean-quotation') !== false || 
            strpos($screen->id, 'pcq-') !== false ||
            strpos($screen->id, 'quotations') !== false ||
            $screen->id === 'toplevel_page_pro-clean-quotation' ||
            strpos($screen->id, '_page_pcq-') !== false
        );
        
        if ($is_plugin_page) {
            error_log('PCQ: Loading admin scripts for screen: ' . $screen->id);
            
            wp_enqueue_style(
                'pcq-admin-style',
                PCQ_PLUGIN_URL . 'assets/css/admin.css',
                [],
                PCQ_VERSION
            );
            
            $script_deps = ['jquery', 'wp-color-picker'];
            
            // Enqueue FullCalendar for calendar page
            $is_calendar_page = (
                strpos($screen->id, 'pcq-calendar') !== false || 
                strpos($screen->id, 'calendar') !== false ||
                strpos($screen->id, '_page_pcq-calendar') !== false
            );
            
            if ($is_calendar_page) {
                error_log('PCQ: Loading FullCalendar for calendar page');
                
                wp_enqueue_script(
                    'fullcalendar',
                    'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js',
                    [],
                    '6.1.10',
                    true
                );
                $script_deps[] = 'fullcalendar';
            }
            
            wp_enqueue_script(
                'pcq-admin-script',
                PCQ_PLUGIN_URL . 'assets/js/admin.js',
                $script_deps,
                PCQ_VERSION,
                true
            );
            
            wp_localize_script('pcq-admin-script', 'pcq_admin_ajax', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pcq_admin_nonce'),
            ]);
            
            error_log('PCQ: Admin scripts enqueued successfully');
        } else {
            error_log('PCQ: Not loading admin scripts for screen: ' . $screen->id);
        }
    }
    
    /**
     * Initialize REST API
     */
    public function initRestAPI(): void {
        API\QuoteController::getInstance()->registerRoutes();
        API\BookingController::getInstance()->registerRoutes();
    }
    
    /**
     * Handle AJAX quote calculation
     */
    public function handleAjaxCalculateQuote(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_nonce')) {
            wp_die(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        $calculator = Services\QuoteCalculator::getInstance();
        $result = $calculator->calculateQuote($_POST);
        
        // Add language information to response
        $lang_manager = I18n\LanguageManager::getInstance();
        $result = apply_filters('pcq_ajax_response', $result, 'calculate_quote');
        
        wp_send_json($result);
    }
    
    /**
     * Handle AJAX quote submission
     */
    public function handleAjaxSubmitQuote(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_nonce')) {
            wp_die(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        $form_handler = Frontend\FormHandler::getInstance();
        $result = $form_handler->submitQuote($_POST);
        
        // Add language information to response
        $result = apply_filters('pcq_ajax_response', $result, 'submit_quote');
        
        wp_send_json($result);
    }
    
    /**
     * Handle AJAX get calendar events
     */
    public function handleAjaxGetCalendarEvents(): void {
        // Log the request for debugging
        error_log('PCQ: Calendar events AJAX request received');
        
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_admin_nonce') || !current_user_can('manage_options')) {
            error_log('PCQ: Calendar events security check failed');
            wp_send_json_error(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        $start_date = sanitize_text_field($_POST['start'] ?? '');
        $end_date = sanitize_text_field($_POST['end'] ?? '');
        $employee_id = intval($_POST['employee_id'] ?? 0);
        
        error_log('PCQ: Calendar events request - Start: ' . $start_date . ', End: ' . $end_date . ', Employee: ' . $employee_id);
        
        try {
            $appointment_manager = Services\AppointmentManager::getInstance();
            $events = $appointment_manager->getCalendarEvents($start_date, $end_date, $employee_id ?: null);
            
            error_log('PCQ: Calendar events found: ' . count($events));
            
            wp_send_json_success($events);
        } catch (\Exception $e) {
            error_log('PCQ: Calendar events error: ' . $e->getMessage());
            wp_send_json_error('Failed to load calendar events: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle AJAX get appointment
     */
    public function handleAjaxGetAppointment(): void {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_admin_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        $appointment_id = intval($_POST['appointment_id'] ?? 0);
        
        if (!$appointment_id) {
            wp_send_json_error(__('Invalid appointment ID.', 'pro-clean-quotation'));
        }
        
        $appointment = new Models\Appointment($appointment_id);
        
        if (!$appointment->getId()) {
            wp_send_json_error(__('Appointment not found.', 'pro-clean-quotation'));
        }
        
        wp_send_json_success($appointment->toArray());
    }
    
    /**
     * Handle AJAX save appointment
     */
    public function handleAjaxSaveAppointment(): void {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_admin_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        $appointment_id = intval($_POST['appointment_id'] ?? 0);
        
        // Sanitize form data
        $appointment_data = [
            'service_id' => intval($_POST['service_id'] ?? 0),
            'employee_id' => intval($_POST['employee_id'] ?? 0) ?: null,
            'customer_name' => sanitize_text_field($_POST['customer_name'] ?? ''),
            'customer_email' => sanitize_email($_POST['customer_email'] ?? ''),
            'customer_phone' => sanitize_text_field($_POST['customer_phone'] ?? ''),
            'service_date' => sanitize_text_field($_POST['service_date'] ?? ''),
            'service_time_start' => sanitize_text_field($_POST['service_time_start'] ?? ''),
            'service_time_end' => sanitize_text_field($_POST['service_time_end'] ?? ''),
            'price' => floatval($_POST['price'] ?? 0),
            'status' => sanitize_text_field($_POST['status'] ?? 'pending'),
            'notes' => sanitize_textarea_field($_POST['notes'] ?? '')
        ];
        
        // Calculate duration
        if ($appointment_data['service_time_start'] && $appointment_data['service_time_end']) {
            $start = strtotime($appointment_data['service_time_start']);
            $end = strtotime($appointment_data['service_time_end']);
            $appointment_data['duration'] = ($end - $start) / 60;
        }
        
        if ($appointment_id) {
            // Update existing appointment
            $appointment = new Models\Appointment($appointment_id);
            
            if (!$appointment->getId()) {
                wp_send_json_error(__('Appointment not found.', 'pro-clean-quotation'));
            }
            
            // Update appointment data
            foreach ($appointment_data as $key => $value) {
                $appointment->data[$key] = $value;
            }
            
            if ($appointment->save()) {
                wp_send_json_success(__('Appointment updated successfully.', 'pro-clean-quotation'));
            } else {
                wp_send_json_error(__('Failed to update appointment.', 'pro-clean-quotation'));
            }
        } else {
            // Create new appointment
            $appointment = Models\Appointment::create($appointment_data);
            
            if ($appointment) {
                wp_send_json_success(__('Appointment created successfully.', 'pro-clean-quotation'));
            } else {
                wp_send_json_error(__('Failed to create appointment.', 'pro-clean-quotation'));
            }
        }
    }
    
    /**
     * Handle AJAX delete appointment
     */
    public function handleAjaxDeleteAppointment(): void {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_admin_nonce') || !current_user_can('manage_options')) {
            wp_die(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        $appointment_id = intval($_POST['appointment_id'] ?? 0);
        
        if (!$appointment_id) {
            wp_send_json_error(__('Invalid appointment ID.', 'pro-clean-quotation'));
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'pq_appointments';
        
        $result = $wpdb->delete($table, ['id' => $appointment_id], ['%d']);
        
        if ($result) {
            wp_send_json_success(__('Appointment deleted successfully.', 'pro-clean-quotation'));
        } else {
            wp_send_json_error(__('Failed to delete appointment.', 'pro-clean-quotation'));
        }
    }
    
    /**
     * Cleanup temporary PDF files (cron job)
     */
    public function cleanupTempPDFs(): void {
        Services\PDFGenerator::cleanupTempFiles();
    }
    
    /**
     * Handle AJAX get available slots
     */
    public function handleAjaxGetAvailableSlots(): void {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'] ?? '', 'pcq_nonce')) {
            wp_send_json_error(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        $date = sanitize_text_field($_GET['date'] ?? '');
        $service_duration = intval($_GET['service_duration'] ?? 2);
        $service_type = sanitize_text_field($_GET['service_type'] ?? 'facade');
        
        if (empty($date)) {
            wp_send_json_error(__('Date parameter is required.', 'pro-clean-quotation'));
        }
        
        $booking_manager = Services\BookingManager::getInstance();
        $slots = $booking_manager->getAvailableSlots($date, $service_duration, $service_type);
        
        wp_send_json_success([
            'available_slots' => $slots,
            'date' => $date,
            'service_duration' => $service_duration
        ]);
    }
    
    /**
     * Handle AJAX create booking
     */
    public function handleAjaxCreateBooking(): void {
        // Verify nonce
        if (!wp_verify_nonce($_POST['pcq_booking_nonce'] ?? '', 'pcq_create_booking')) {
            wp_send_json_error(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        $booking_manager = Services\BookingManager::getInstance();
        $result = $booking_manager->createBookingFromQuote($_POST);
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    /**
     * Handle AJAX test SMTP connection
     */
    public function handleAjaxTestSMTP(): void {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_test_smtp') || !current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Security check failed.', 'pro-clean-quotation')]);
        }
        
        $smtp_config = Email\SMTPConfig::getInstance();
        $result = $smtp_config->testConnection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
}