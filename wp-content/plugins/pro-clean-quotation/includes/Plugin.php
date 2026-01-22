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
        add_action('wp_ajax_pcq_get_quotes_for_appointment', [$this, 'handleAjaxGetQuotesForAppointment']);
        add_action('wp_ajax_pcq_test_smtp', [$this, 'handleAjaxTestSMTP']);
        
        // Database health checks
        add_action('admin_notices', [Admin\DatabaseFixer::class, 'showMissingTablesNotice']);
        add_action('admin_init', [Admin\DatabaseFixer::class, 'handleFixAction']);
        
        // Missing pages notice and handler
        add_action('admin_notices', [$this, 'showMissingPagesNotice']);
        add_action('wp_ajax_pcq_create_confirmation_page', [$this, 'handleAjaxCreateConfirmationPage']);
        
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
            'confirmation_url' => $this->getBookingConfirmationUrl(),
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
        try {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_nonce')) {
                error_log('PCQ: Calculate quote nonce verification failed');
                wp_die(__('Security check failed.', 'pro-clean-quotation'));
            }
            
            error_log('PCQ: Calculate quote request data: ' . print_r($_POST, true));
            
            $calculator = Services\QuoteCalculator::getInstance();
            $result = $calculator->calculateQuote($_POST);
            
            error_log('PCQ: Calculate quote result: ' . print_r($result, true));
            
            // Add language information to response
            $lang_manager = I18n\LanguageManager::getInstance();
            $result = apply_filters('pcq_ajax_response', $result, 'calculate_quote');
            
            wp_send_json($result);
        } catch (\Throwable $e) {
            error_log('PCQ: Fatal error in handleAjaxCalculateQuote: ' . $e->getMessage());
            error_log('PCQ: Stack trace: ' . $e->getTraceAsString());
            
            wp_send_json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage()
            ]);
        }
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
     * Handle AJAX get quotes for appointment
     */
    public function handleAjaxGetQuotesForAppointment(): void {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_admin_nonce') || !current_user_can('manage_options')) {
            wp_send_json_error(__('Security check failed.', 'pro-clean-quotation'));
        }
        
        // Get quotes that can be booked (not expired, cancelled, or rejected)
        global $wpdb;
        $table = $wpdb->prefix . 'pq_quotes';
        
        $quotes = $wpdb->get_results(
            "SELECT id, quote_number, customer_name, customer_email, customer_phone, 
                    property_address, service_type, total_price, special_requirements, 
                    created_at, status
             FROM {$table}
             WHERE status IN ('new', 'sent', 'accepted')
               AND (valid_until IS NULL OR valid_until > NOW())
             ORDER BY created_at DESC
             LIMIT 100",
            ARRAY_A
        );
        
        // Format quotes for dropdown
        $formatted_quotes = [];
        foreach ($quotes as $quote) {
            $formatted_quotes[] = [
                'id' => $quote['id'],
                'quote_number' => $quote['quote_number'],
                'customer_name' => $quote['customer_name'],
                'customer_email' => $quote['customer_email'],
                'customer_phone' => $quote['customer_phone'],
                'property_address' => $quote['property_address'],
                'service_type' => $quote['service_type'],
                'total_price' => $quote['total_price'],
                'special_requirements' => $quote['special_requirements'],
                'created_at' => $quote['created_at'],
                'status' => $quote['status'],
                'display_text' => sprintf(
                    '#%s - %s (%s) - €%s',
                    $quote['quote_number'],
                    $quote['customer_name'],
                    $quote['service_type'],
                    number_format($quote['total_price'], 2)
                )
            ];
        }
        
        wp_send_json_success($formatted_quotes);
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
        try {
            // Verify nonce
            if (!wp_verify_nonce($_POST['pcq_booking_nonce'] ?? '', 'pcq_create_booking')) {
                error_log('PCQ Booking Error: Nonce verification failed');
                error_log('PCQ Booking Data: ' . print_r($_POST, true));
                wp_send_json_error(__('Security check failed.', 'pro-clean-quotation'));
            }
            
            // Validate required fields
            $required_fields = ['quote_id', 'quote_token', 'service_date', 'service_time_start', 'service_time_end'];
            $missing_fields = [];
            
            foreach ($required_fields as $field) {
                if (empty($_POST[$field])) {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                error_log('PCQ Booking Error: Missing required fields: ' . implode(', ', $missing_fields));
                error_log('PCQ Booking Data: ' . print_r($_POST, true));
                wp_send_json_error(
                    sprintf(
                        __('Missing required fields: %s', 'pro-clean-quotation'),
                        implode(', ', $missing_fields)
                    )
                );
            }
            
            $booking_manager = Services\BookingManager::getInstance();
            $result = $booking_manager->createBookingFromQuote($_POST);
            
            if ($result['success']) {
                wp_send_json_success($result['data']);
            } else {
                error_log('PCQ Booking Error: ' . ($result['message'] ?? 'Unknown error'));
                wp_send_json_error($result['message'] ?? __('Booking creation failed.', 'pro-clean-quotation'));
            }
        } catch (\Exception $e) {
            error_log('PCQ Booking Exception: ' . $e->getMessage());
            error_log('PCQ Booking Trace: ' . $e->getTraceAsString());
            wp_send_json_error(__('An unexpected error occurred. Please try again.', 'pro-clean-quotation'));
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
    
    /**
     * Get booking confirmation page URL
     * 
     * @return string Confirmation page URL
     */
    private function getBookingConfirmationUrl(): string {
        // Try to find a page with the booking confirmation shortcode
        $pages = get_posts([
            'post_type' => 'page',
            'post_status' => 'publish',
            's' => '[pcq_booking_confirmation]',
            'posts_per_page' => 1
        ]);
        
        if (!empty($pages)) {
            return get_permalink($pages[0]->ID);
        }
        
        // Fallback: return home URL (the shortcode will handle the display)
        return home_url('/');
    }
    
    /**
     * Show admin notice if confirmation page is missing
     */
    public function showMissingPagesNotice(): void {
        // Only show to admins
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Check if confirmation page exists
        $confirmation_page = get_page_by_path('booking-confirmation');
        
        if (!$confirmation_page) {
            ?>
            <div class="notice notice-warning is-dismissible" id="pcq-missing-confirmation-page">
                <p>
                    <strong><?php _e('Pro Clean Quotation:', 'pro-clean-quotation'); ?></strong> 
                    <?php _e('The booking confirmation page is missing. Customers will see a 404 error after booking.', 'pro-clean-quotation'); ?>
                </p>
                <p>
                    <button type="button" class="button button-primary" id="pcq-create-confirmation-page-btn">
                        <?php _e('Create Confirmation Page Now', 'pro-clean-quotation'); ?>
                    </button>
                    <span class="spinner" style="float: none; margin: 0 10px;"></span>
                    <span id="pcq-create-page-message"></span>
                </p>
            </div>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#pcq-create-confirmation-page-btn').on('click', function() {
                    var $btn = $(this);
                    var $spinner = $btn.next('.spinner');
                    var $message = $('#pcq-create-page-message');
                    
                    $btn.prop('disabled', true);
                    $spinner.addClass('is-active');
                    $message.html('');
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'pcq_create_confirmation_page',
                            nonce: '<?php echo wp_create_nonce('pcq_create_confirmation_page'); ?>'
                        },
                        success: function(response) {
                            $spinner.removeClass('is-active');
                            if (response.success) {
                                $message.html('<span style="color: #46b450;">✓ ' + response.data.message + '</span>');
                                setTimeout(function() {
                                    $('#pcq-missing-confirmation-page').fadeOut();
                                }, 3000);
                            } else {
                                $message.html('<span style="color: #dc3232;">✗ ' + response.data.message + '</span>');
                                $btn.prop('disabled', false);
                            }
                        },
                        error: function() {
                            $spinner.removeClass('is-active');
                            $message.html('<span style="color: #dc3232;">✗ An error occurred. Please try again.</span>');
                            $btn.prop('disabled', false);
                        }
                    });
                });
            });
            </script>
            <?php
        }
    }
    
    /**
     * Handle AJAX request to create confirmation page
     */
    public function handleAjaxCreateConfirmationPage(): void {
        // Verify nonce and permissions
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pcq_create_confirmation_page') || !current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Security check failed.', 'pro-clean-quotation')]);
        }
        
        // Check if page already exists
        $existing_page = get_page_by_path('booking-confirmation');
        
        if ($existing_page) {
            update_option('pcq_confirmation_page_id', $existing_page->ID);
            wp_send_json_success([
                'message' => __('Confirmation page already exists and has been configured.', 'pro-clean-quotation'),
                'page_url' => get_permalink($existing_page->ID)
            ]);
        }
        
        // Create new confirmation page
        $confirmation_page = [
            'post_title'    => __('Booking Confirmation', 'pro-clean-quotation'),
            'post_content'  => '[pcq_booking_confirmation]',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'post_name'     => 'booking-confirmation',
            'post_author'   => get_current_user_id(),
            'comment_status' => 'closed',
            'ping_status'   => 'closed'
        ];
        
        $page_id = wp_insert_post($confirmation_page);
        
        if ($page_id && !is_wp_error($page_id)) {
            update_option('pcq_confirmation_page_id', $page_id);
            flush_rewrite_rules();
            
            wp_send_json_success([
                'message' => __('Confirmation page created successfully!', 'pro-clean-quotation'),
                'page_url' => get_permalink($page_id),
                'page_id' => $page_id
            ]);
        } else {
            $error_message = is_wp_error($page_id) ? $page_id->get_error_message() : __('Failed to create page.', 'pro-clean-quotation');
            wp_send_json_error(['message' => $error_message]);
        }
    }
}