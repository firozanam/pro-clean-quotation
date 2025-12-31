<?php

namespace ProClean\Quotation\Admin;

/**
 * Admin Menu Class
 * 
 * @package ProClean\Quotation\Admin
 * @since 1.0.0
 */
class AdminMenu {
    
    /**
     * Admin menu instance
     * 
     * @var AdminMenu
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return AdminMenu
     */
    public static function getInstance(): AdminMenu {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_menu', [$this, 'addMenuPages']);
        add_action('admin_init', [$this, 'handleActions']);
    }
    
    /**
     * Add admin menu pages
     */
    public function addMenuPages(): void {
        // Main menu page
        add_menu_page(
            __('Pro Clean Quotation', 'pro-clean-quotation'),
            __('Quotations', 'pro-clean-quotation'),
            'manage_options',
            'pro-clean-quotation',
            [$this, 'renderDashboard'],
            'dashicons-calculator',
            30
        );
        
        // Dashboard (same as main page)
        add_submenu_page(
            'pro-clean-quotation',
            __('Dashboard', 'pro-clean-quotation'),
            __('Dashboard', 'pro-clean-quotation'),
            'manage_options',
            'pro-clean-quotation',
            [$this, 'renderDashboard']
        );
        
        // Quotes management
        add_submenu_page(
            'pro-clean-quotation',
            __('Quotes', 'pro-clean-quotation'),
            __('Quotes', 'pro-clean-quotation'),
            'manage_options',
            'pcq-quotes',
            [$this, 'renderQuotes']
        );
        
        // Appointments management
        add_submenu_page(
            'pro-clean-quotation',
            __('Appointments', 'pro-clean-quotation'),
            __('Appointments', 'pro-clean-quotation'),
            'manage_options',
            'pcq-appointments',
            [$this, 'renderAppointments']
        );
        
        // Calendar view
        add_submenu_page(
            'pro-clean-quotation',
            __('Calendar', 'pro-clean-quotation'),
            __('Calendar', 'pro-clean-quotation'),
            'manage_options',
            'pcq-calendar',
            [$this, 'renderCalendar']
        );
        
        // Customers management
        add_submenu_page(
            'pro-clean-quotation',
            __('Customers', 'pro-clean-quotation'),
            __('Customers', 'pro-clean-quotation'),
            'manage_options',
            'pcq-customers',
            [$this, 'renderCustomers']
        );
        
        // Services management
        add_submenu_page(
            'pro-clean-quotation',
            __('Services', 'pro-clean-quotation'),
            __('Services', 'pro-clean-quotation'),
            'manage_options',
            'pcq-services',
            [$this, 'renderServices']
        );
        
        // Service Categories
        add_submenu_page(
            'pro-clean-quotation',
            __('Service Categories', 'pro-clean-quotation'),
            __('Service Categories', 'pro-clean-quotation'),
            'manage_options',
            'pcq-service-categories',
            [$this, 'renderServiceCategories']
        );
        
        // Employees management
        add_submenu_page(
            'pro-clean-quotation',
            __('Employees', 'pro-clean-quotation'),
            __('Employees', 'pro-clean-quotation'),
            'manage_options',
            'pcq-employees',
            [$this, 'renderEmployees']
        );
        
        // Settings
        add_submenu_page(
            'pro-clean-quotation',
            __('Settings', 'pro-clean-quotation'),
            __('Settings', 'pro-clean-quotation'),
            'manage_options',
            'pcq-settings',
            [$this, 'renderSettings']
        );
        
        // Email logs
        add_submenu_page(
            'pro-clean-quotation',
            __('Email Logs', 'pro-clean-quotation'),
            __('Email Logs', 'pro-clean-quotation'),
            'manage_options',
            'pcq-email-logs',
            [$this, 'renderEmailLogs']
        );
        
        // Shortcodes Reference
        add_submenu_page(
            'pro-clean-quotation',
            __('Shortcodes', 'pro-clean-quotation'),
            __('Shortcodes', 'pro-clean-quotation'),
            'manage_options',
            'pcq-shortcodes',
            [$this, 'renderShortcodes']
        );
        
        // Dummy Data (only in development)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            add_submenu_page(
                'pro-clean-quotation',
                __('Dummy Data', 'pro-clean-quotation'),
                __('Dummy Data', 'pro-clean-quotation'),
                'manage_options',
                'pcq-dummy-data',
                [$this, 'renderDummyData']
            );
        }
    }
    
    /**
     * Handle admin actions
     */
    public function handleActions(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $action = $_GET['action'] ?? '';
        $page = $_GET['page'] ?? '';
        
        // Handle quote actions
        if ($page === 'pcq-quotes' && $action && isset($_GET['id'])) {
            $quote_id = intval($_GET['id']);
            
            switch ($action) {
                case 'delete':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'delete_quote_' . $quote_id)) {
                        $this->deleteQuote($quote_id);
                    }
                    break;
                    
                case 'convert_to_booking':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'convert_quote_' . $quote_id)) {
                        $this->convertQuoteToBooking($quote_id);
                    }
                    break;
            }
        }
        
        // Handle appointment form submission
        if ($page === 'pcq-appointments' && $_POST && isset($_POST['action']) && $_POST['action'] === 'save_appointment') {
            $appointment_id = intval($_POST['appointment_id'] ?? 0);
            $nonce_action = $appointment_id ? 'pcq_save_appointment_' . $appointment_id : 'pcq_create_appointment';
            
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', $nonce_action)) {
                if ($appointment_id) {
                    $this->saveAppointment($appointment_id, $_POST);
                } else {
                    $this->createAppointment($_POST);
                }
            }
        }
        
        // Handle appointment actions
        if ($page === 'pcq-appointments' && $action && isset($_GET['id'])) {
            $appointment_id = intval($_GET['id']);
            
            switch ($action) {
                case 'confirm':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'confirm_appointment_' . $appointment_id)) {
                        $this->confirmAppointment($appointment_id);
                    }
                    break;
                    
                case 'start':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'start_appointment_' . $appointment_id)) {
                        $this->startAppointment($appointment_id);
                    }
                    break;
                    
                case 'complete':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'complete_appointment_' . $appointment_id)) {
                        $this->completeAppointment($appointment_id);
                    }
                    break;
                    
                case 'cancel':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'cancel_appointment_' . $appointment_id)) {
                        $this->cancelAppointment($appointment_id);
                    }
                    break;
            }
        }
        
        // Handle appointment form submission
        if ($page === 'pcq-appointments' && $_POST && isset($_POST['action']) && $_POST['action'] === 'save_appointment') {
            $appointment_id = intval($_POST['appointment_id'] ?? 0);
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', 'pcq_save_appointment_' . $appointment_id)) {
                $this->saveAppointment($appointment_id, $_POST);
            }
        }
        
        // Handle quote form submission
        if ($page === 'pcq-quotes' && $_POST && isset($_POST['action']) && $_POST['action'] === 'save_quote') {
            $quote_id = intval($_POST['quote_id'] ?? 0);
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', 'pcq_save_quote_' . $quote_id)) {
                $this->saveQuote($quote_id, $_POST);
            }
        }
        
        // Handle quote actions
        if ($page === 'pcq-quotes' && $action && isset($_GET['id'])) {
            $quote_id = intval($_GET['id']);
            
            switch ($action) {
                case 'delete':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'delete_quote_' . $quote_id)) {
                        $this->deleteQuote($quote_id);
                    }
                    break;
                    
                case 'approve':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'approve_quote_' . $quote_id)) {
                        $this->approveQuote($quote_id);
                    }
                    break;
            }
        }
        
        // Handle dummy data actions
        if ($page === 'pcq-dummy-data' && $action) {
            switch ($action) {
                case 'generate':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'generate_dummy_data')) {
                        $this->generateDummyData();
                    }
                    break;
                    
                case 'clear':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'clear_dummy_data')) {
                        $this->clearDummyData();
                    }
                    break;
                    
                case 'recreate':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'recreate_database')) {
                        $this->recreateDatabase();
                    }
                    break;
            }
        }
        
        // Handle email log actions
        if ($page === 'pcq-email-logs' && $action && isset($_GET['id'])) {
            $log_id = intval($_GET['id']);
            
            switch ($action) {
                case 'view':
                    // Don't render here - let renderEmailLogs() handle it
                    // Just validate the log exists
                    global $wpdb;
                    $table = $wpdb->prefix . 'pq_email_logs';
                    $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $log_id));
                    if (!$log) {
                        wp_die(__('Email log not found.', 'pro-clean-quotation'));
                    }
                    break;
                    
                case 'resend':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'resend_email_' . $log_id)) {
                        $this->resendEmail($log_id);
                    }
                    break;
                    
                case 'delete':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'delete_email_log_' . $log_id)) {
                        $this->deleteEmailLog($log_id);
                    }
                    break;
            }
        }
        
        // Handle employee form submission
        if ($page === 'pcq-employees' && $_POST && isset($_POST['action']) && $_POST['action'] === 'save_employee') {
            $employee_id = intval($_POST['employee_id'] ?? 0);
            $nonce_action = $employee_id ? 'pcq_save_employee_' . $employee_id : 'pcq_create_employee';
            
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', $nonce_action)) {
                $this->saveEmployee($employee_id, $_POST);
            }
        }
        
        // Handle employee actions
        if ($page === 'pcq-employees' && $action && isset($_GET['id'])) {
            $employee_id = intval($_GET['id']);
            
            switch ($action) {
                case 'delete':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'delete_employee_' . $employee_id)) {
                        $this->deleteEmployee($employee_id);
                    }
                    break;
            }
        }
        
        // Handle service form submission
        if ($page === 'pcq-services' && $_POST && isset($_POST['action']) && $_POST['action'] === 'save_service') {
            $service_id = intval($_POST['service_id'] ?? 0);
            $nonce_action = $service_id ? 'pcq_save_service_' . $service_id : 'pcq_create_service';
            
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', $nonce_action)) {
                $this->saveService($service_id, $_POST);
            }
        }
        
        // Handle service actions
        if ($page === 'pcq-services' && $action && isset($_GET['id'])) {
            $service_id = intval($_GET['id']);
            
            switch ($action) {
                case 'activate':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'activate_service_' . $service_id)) {
                        $this->activateService($service_id);
                    }
                    break;
                case 'deactivate':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'deactivate_service_' . $service_id)) {
                        $this->deactivateService($service_id);
                    }
                    break;
                case 'delete':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'delete_service_' . $service_id)) {
                        $this->deleteService($service_id);
                    }
                    break;
            }
        }
        
        // Handle service category form submission
        if ($page === 'pcq-service-categories' && $_POST && isset($_POST['action']) && $_POST['action'] === 'save_category') {
            $category_id = intval($_POST['category_id'] ?? 0);
            $nonce_action = $category_id ? 'pcq_save_category_' . $category_id : 'pcq_create_category';
            
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', $nonce_action)) {
                $this->saveServiceCategory($category_id, $_POST);
            }
        }
        
        // Handle service category actions
        if ($page === 'pcq-service-categories' && $action && isset($_GET['id'])) {
            $category_id = intval($_GET['id']);
            
            switch ($action) {
                case 'delete':
                    if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'delete_category_' . $category_id)) {
                        $this->deleteServiceCategory($category_id);
                    }
                    break;
            }
        }
        
        // Handle settings save
        if ($page === 'pcq-settings' && $action === 'save' && $_POST) {
            if (wp_verify_nonce($_POST['_wpnonce'] ?? '', 'pcq_save_settings')) {
                $this->saveSettings($_POST);
            }
        }
    }
    
    /**
     * Render dashboard page
     */
    public function renderDashboard(): void {
        $dashboard = Dashboard::getInstance();
        $dashboard->render();
    }
    
    /**
     * Render quotes page
     */
    public function renderQuotes(): void {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'view':
                $this->renderQuoteView();
                break;
            case 'edit':
                $this->renderQuoteEdit();
                break;
            default:
                $this->renderQuotesList();
                break;
        }
    }
    
    /**
     * Render appointment form
     */
    private function renderAppointmentForm(): void {
        $action = $_GET['action'];
        $appointment_id = intval($_GET['id'] ?? 0);
        $appointment = null;
        
        if ($appointment_id) {
            $appointment = new \ProClean\Quotation\Models\Appointment($appointment_id);
            
            if (!$appointment->getId()) {
                wp_die(__('Appointment not found.', 'pro-clean-quotation'));
            }
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/appointment-form.php';
    }
    
    /**
     * Render appointments page
     */
    public function renderAppointments(): void {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'add':
            case 'edit':
                $this->renderAppointmentForm();
                break;
            case 'view':
                $this->renderAppointmentView();
                break;
            default:
                $this->renderAppointmentsList();
                break;
        }
    }
    
    /**
     * Render calendar page
     */
    public function renderCalendar(): void {
        $calendar_page = new CalendarPage();
        $calendar_page->render();
    }
    
    /**
     * Render customers page
     */
    public function renderCustomers(): void {
        // Get filters
        $status_filter = $_GET['status'] ?? '';
        $search = $_GET['s'] ?? '';
        $page = max(1, intval($_GET['paged'] ?? 1));
        
        $filters = array_filter([
            'status' => $status_filter,
            'search' => $search
        ]);
        
        $customers_data = \ProClean\Quotation\Models\Customer::getAll($page, 20, $filters);
        
        include PCQ_PLUGIN_DIR . 'templates/admin/customers-list.php';
    }
    
    /**
     * Render services page
     */
    public function renderServices(): void {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'add':
            case 'edit':
                $this->renderServiceForm();
                break;
            default:
                $this->renderServicesList();
                break;
        }
    }
    
    /**
     * Render employees page
     */
    public function renderEmployees(): void {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'add':
            case 'edit':
                $this->renderEmployeeForm();
                break;
            default:
                $this->renderEmployeesList();
                break;
        }
    }
    
    /**
     * Render appointments list
     */
    private function renderAppointmentsList(): void {
        // Get filters
        $status_filter = $_GET['status'] ?? '';
        $employee_filter = $_GET['employee_id'] ?? '';
        $service_filter = $_GET['service_id'] ?? '';
        $search = $_GET['s'] ?? '';
        $page = max(1, intval($_GET['paged'] ?? 1));
        
        $filters = array_filter([
            'status' => $status_filter,
            'employee_id' => $employee_filter,
            'service_id' => $service_filter,
            'search' => $search
        ]);
        
        $appointments_data = \ProClean\Quotation\Models\Appointment::getAll($filters, $page, 20);
        
        include PCQ_PLUGIN_DIR . 'templates/admin/appointments-list.php';
    }
    
    /**
     * Render appointment view
     */
    private function renderAppointmentView(): void {
        $appointment_id = intval($_GET['id'] ?? 0);
        $appointment = new \ProClean\Quotation\Models\Appointment($appointment_id);
        
        if (!$appointment->getId()) {
            wp_die(__('Appointment not found.', 'pro-clean-quotation'));
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/appointment-view.php';
    }
    
    /**
     * Render appointment edit
     */
    private function renderAppointmentEdit(): void {
        $appointment_id = intval($_GET['id'] ?? 0);
        $appointment = new \ProClean\Quotation\Models\Appointment($appointment_id);
        
        if (!$appointment->getId()) {
            wp_die(__('Appointment not found.', 'pro-clean-quotation'));
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/appointment-edit.php';
    }
    
    /**
     * Render services list
     */
    private function renderServicesList(): void {
        global $wpdb;
        
        // Get search parameter
        $search = $_GET['s'] ?? '';
        
        $table = $wpdb->prefix . 'pq_services';
        
        if (!empty($search)) {
            // Search services by name or description
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $services_data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $table WHERE name LIKE %s OR description LIKE %s ORDER BY sort_order ASC, name ASC",
                    $search_term,
                    $search_term
                ),
                ARRAY_A
            );
            
            $services = [];
            foreach ($services_data as $service_data) {
                $services[] = new \ProClean\Quotation\Models\Service($service_data);
            }
        } else {
            // Get all services
            $services = \ProClean\Quotation\Models\Service::getAll(false);
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/services-list.php';
    }
    
    /**
     * Render service form
     */
    private function renderServiceForm(): void {
        $action = $_GET['action'];
        $service_id = intval($_GET['id'] ?? 0);
        $service = $service_id ? new \ProClean\Quotation\Models\Service($service_id) : null;
        
        include PCQ_PLUGIN_DIR . 'templates/admin/service-form.php';
    }
    
    /**
     * Render employees list
     */
    private function renderEmployeesList(): void {
        global $wpdb;
        
        // Get search parameter
        $search = $_GET['s'] ?? '';
        
        if (!empty($search)) {
            // Search employees by name, email, or description
            $employees_table = $wpdb->prefix . 'pq_employees';
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            
            $employees_data = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $employees_table WHERE name LIKE %s OR email LIKE %s OR description LIKE %s ORDER BY name ASC",
                    $search_term,
                    $search_term,
                    $search_term
                ),
                ARRAY_A
            );
            
            $employees = [];
            foreach ($employees_data as $employee_data) {
                $employees[] = new \ProClean\Quotation\Models\Employee($employee_data);
            }
        } else {
            // Get all employees
            $employees = \ProClean\Quotation\Models\Employee::getAll(false);
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/employees-list.php';
    }
    
    /**
     * Render employee form
     */
    private function renderEmployeeForm(): void {
        $action = $_GET['action'];
        $employee_id = intval($_GET['id'] ?? 0);
        $employee = null;
        
        if ($employee_id) {
            $employee = new \ProClean\Quotation\Models\Employee($employee_id);
            
            if (!$employee->getId()) {
                wp_die(__('Employee not found.', 'pro-clean-quotation'));
            }
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/employee-form.php';
    }
    
    /**
     * Render settings page
     */
    public function renderSettings(): void {
        $settings_page = new SettingsPage();
        $settings_page->render();
    }
    
    /**
     * Render dummy data page
     */
    public function renderDummyData(): void {
        include PCQ_PLUGIN_DIR . 'templates/admin/dummy-data.php';
    }
    
    /**
     * Save quote
     */
    private function saveQuote(int $quote_id, array $data): void {
        try {
            $quote = new \ProClean\Quotation\Models\Quote($quote_id);
            
            if (!$quote->getId()) {
                throw new Exception(__('Quote not found.', 'pro-clean-quotation'));
            }
            
            // Sanitize and validate data
            $quote_data = [
                'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
                'customer_email' => sanitize_email($data['customer_email'] ?? ''),
                'customer_phone' => sanitize_text_field($data['customer_phone'] ?? ''),
                'property_address' => sanitize_textarea_field($data['property_address'] ?? ''),
                'postal_code' => sanitize_text_field($data['postal_code'] ?? ''),
                'service_type' => sanitize_text_field($data['service_type'] ?? ''),
                'square_meters' => floatval($data['square_meters'] ?? 0),
                'linear_meters' => floatval($data['linear_meters'] ?? 0),
                'building_height' => intval($data['building_height'] ?? 0),
                'property_type' => sanitize_text_field($data['property_type'] ?? ''),
                'surface_material' => sanitize_text_field($data['surface_material'] ?? ''),
                'roof_type' => sanitize_text_field($data['roof_type'] ?? ''),
                'last_cleaning_date' => sanitize_text_field($data['last_cleaning_date'] ?? ''),
                'special_requirements' => sanitize_textarea_field($data['special_requirements'] ?? ''),
                'base_price' => floatval($data['base_price'] ?? 0),
                'adjustments' => floatval($data['adjustments'] ?? 0),
                'tax_amount' => floatval($data['tax_amount'] ?? 0),
                'status' => sanitize_text_field($data['status'] ?? 'new'),
                'valid_until' => sanitize_text_field($data['valid_until'] ?? '')
            ];
            
            // Calculate subtotal and total
            $quote_data['subtotal'] = $quote_data['base_price'] + $quote_data['adjustments'];
            $quote_data['total_price'] = $quote_data['subtotal'] + $quote_data['tax_amount'];
            
            // Validate required fields
            if (empty($quote_data['customer_name']) || 
                empty($quote_data['customer_email']) || 
                empty($quote_data['property_address']) ||
                empty($quote_data['service_type']) ||
                $quote_data['square_meters'] <= 0 ||
                $quote_data['base_price'] <= 0) {
                throw new Exception(__('Please fill in all required fields with valid values.', 'pro-clean-quotation'));
            }
            
            // Update quote data
            foreach ($quote_data as $key => $value) {
                $quote->data[$key] = $value;
            }
            
            if ($quote->save()) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Quote updated successfully.', 'pro-clean-quotation') . '</p></div>';
                });
                
                wp_redirect(admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote_id));
                exit;
            } else {
                throw new Exception(__('Failed to save quote.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }
    
    /**
     * Approve quote
     */
    private function approveQuote(int $quote_id): void {
        try {
            $quote = new \ProClean\Quotation\Models\Quote($quote_id);
            
            if (!$quote->getId()) {
                throw new Exception(__('Quote not found.', 'pro-clean-quotation'));
            }
            
            $quote->data['status'] = 'approved';
            
            if ($quote->save()) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Quote approved successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new Exception(__('Failed to approve quote.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote_id));
        exit;
    }

    /**
     * Generate dummy data
     */
    private function generateDummyData(): void {
        try {
            \ProClean\Quotation\Database\DummyDataGenerator::generateAll();
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Dummy data generated successfully!', 'pro-clean-quotation') . '</p></div>';
            });
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Error generating dummy data: ', 'pro-clean-quotation') . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-dummy-data'));
        exit;
    }
    
    /**
     * Clear dummy data
     */
    private function clearDummyData(): void {
        try {
            \ProClean\Quotation\Database\DummyDataGenerator::clearAll();
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Dummy data cleared successfully!', 'pro-clean-quotation') . '</p></div>';
            });
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Error clearing dummy data: ', 'pro-clean-quotation') . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-dummy-data'));
        exit;
    }

    /**
     * Recreate database with dummy data
     */
    private function recreateDatabase(): void {
        try {
            \ProClean\Quotation\Database\Installer::forceRecreate();
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Database recreated with dummy data successfully!', 'pro-clean-quotation') . '</p></div>';
            });
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Error recreating database: ', 'pro-clean-quotation') . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-dummy-data'));
        exit;
    }

    /**
     * Render email logs page
     */
    public function renderEmailLogs(): void {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'view':
                $log_id = intval($_GET['id'] ?? 0);
                if ($log_id) {
                    $this->renderEmailLogView($log_id);
                } else {
                    $this->renderEmailLogsList();
                }
                break;
            default:
                $this->renderEmailLogsList();
                break;
        }
    }
    
    /**
     * Render quotes list
     */
    private function renderQuotesList(): void {
        // Get filters
        $status_filter = $_GET['status'] ?? '';
        $service_filter = $_GET['service_type'] ?? '';
        $search = $_GET['s'] ?? '';
        $page = max(1, intval($_GET['paged'] ?? 1));
        
        $filters = array_filter([
            'status' => $status_filter,
            'service_type' => $service_filter,
            'search' => $search
        ]);
        
        $quotes_data = \ProClean\Quotation\Models\Quote::getAll($page, 20, $filters);
        
        // Prepare pagination arguments
        $pagination_args = [
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => __('&laquo; Previous', 'pro-clean-quotation'),
            'next_text' => __('Next &raquo;', 'pro-clean-quotation'),
            'total' => $quotes_data['pages'],
            'current' => $quotes_data['current_page']
        ];
        
        include PCQ_PLUGIN_DIR . 'templates/admin/quotes-list.php';
    }
    
    /**
     * Render quote view
     */
    private function renderQuoteView(): void {
        $quote_id = intval($_GET['id'] ?? 0);
        $quote = new \ProClean\Quotation\Models\Quote($quote_id);
        
        if (!$quote->getId()) {
            wp_die(__('Quote not found.', 'pro-clean-quotation'));
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/quote-view.php';
    }
    
    /**
     * Render quote edit
     */
    private function renderQuoteEdit(): void {
        $quote_id = intval($_GET['id'] ?? 0);
        $quote = new \ProClean\Quotation\Models\Quote($quote_id);
        
        if (!$quote->getId()) {
            wp_die(__('Quote not found.', 'pro-clean-quotation'));
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/quote-edit.php';
    }
    
    /**
     * Render email logs list
     */
    private function renderEmailLogsList(): void {
        global $wpdb;
        
        $page = max(1, intval($_GET['paged'] ?? 1));
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $table = $wpdb->prefix . 'pq_email_logs';
        
        // Build WHERE clause for filters
        $where_conditions = [];
        $where_values = [];
        
        if (!empty($_GET['email_type'])) {
            $where_conditions[] = "email_type = %s";
            $where_values[] = sanitize_text_field($_GET['email_type']);
        }
        
        if (!empty($_GET['status'])) {
            $where_conditions[] = "status = %s";
            $where_values[] = sanitize_text_field($_GET['status']);
        }
        
        if (!empty($_GET['search'])) {
            $search = '%' . $wpdb->esc_like(sanitize_text_field($_GET['search'])) . '%';
            $where_conditions[] = "(recipient_email LIKE %s OR subject LIKE %s)";
            $where_values[] = $search;
            $where_values[] = $search;
        }
        
        $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
        
        // Get total count
        $count_query = "SELECT COUNT(*) FROM $table $where_clause";
        if (!empty($where_values)) {
            $total = $wpdb->get_var($wpdb->prepare($count_query, $where_values));
        } else {
            $total = $wpdb->get_var($count_query);
        }
        
        // Get logs
        $logs_query = "SELECT * FROM $table $where_clause ORDER BY sent_at DESC LIMIT %d OFFSET %d";
        $query_values = array_merge($where_values, [$per_page, $offset]);
        
        if (!empty($where_values)) {
            $logs = $wpdb->get_results($wpdb->prepare($logs_query, $query_values));
        } else {
            $logs = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM $table ORDER BY sent_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            ));
        }
        
        $total_pages = ceil($total / $per_page);
        
        include PCQ_PLUGIN_DIR . 'templates/admin/email-logs.php';
    }
    
    /**
     * Delete quote
     */
    private function deleteQuote(int $quote_id): void {
        $quote = new \ProClean\Quotation\Models\Quote($quote_id);
        
        if ($quote->getId() && $quote->delete()) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success is-dismissible"><p>' . __('Quote deleted successfully.', 'pro-clean-quotation') . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to delete quote.', 'pro-clean-quotation') . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-quotes'));
        exit;
    }
    
    /**
     * Convert quote to booking
     */
    private function convertQuoteToBooking(int $quote_id): void {
        $quote = new \ProClean\Quotation\Models\Quote($quote_id);
        
        if (!$quote->getId()) {
            wp_die(__('Quote not found.', 'pro-clean-quotation'));
        }
        
        // Redirect to our own appointment creation with quote data
        $redirect_url = admin_url('admin.php?page=pcq-appointments&action=add');
        $redirect_url = add_query_arg([
            'quote_id' => $quote_id,
            'customer_name' => $quote->getCustomerName(),
            'customer_email' => $quote->getCustomerEmail(),
            'customer_phone' => $quote->getCustomerPhone(),
            'service_type' => $quote->getServiceType(),
            'total_amount' => $quote->getTotalPrice()
        ], $redirect_url);
        
        wp_redirect($redirect_url);
        exit;
    }
    
    /**
     * Save settings
     */
    private function saveSettings(array $data): void {
        $settings_page = new SettingsPage();
        $settings_page->save($data);
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'pro-clean-quotation') . '</p></div>';
        });
    }
    
    /**
     * Cancel appointment
     */
    private function cancelAppointment(int $appointment_id): void {
        $appointment_manager = \ProClean\Quotation\Services\AppointmentManager::getInstance();
        $result = $appointment_manager->cancelAppointment($appointment_id, 'Cancelled by admin');
        
        if ($result['success']) {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-appointments'));
        exit;
    }
    
    /**
     * Confirm appointment
     */
    private function confirmAppointment(int $appointment_id): void {
        try {
            $appointment = new \ProClean\Quotation\Models\Appointment($appointment_id);
            
            if (!$appointment->getId()) {
                throw new \Exception(__('Appointment not found.', 'pro-clean-quotation'));
            }
            
            // Update status to confirmed
            $appointment->data['status'] = 'confirmed';
            $appointment->data['updated_at'] = current_time('mysql');
            
            if ($appointment->save()) {
                // Send confirmation email
                $email_manager = \ProClean\Quotation\Email\EmailManager::getInstance();
                $email_manager->sendAppointmentConfirmation($appointment);
                
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Appointment confirmed successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new \Exception(__('Failed to confirm appointment.', 'pro-clean-quotation'));
            }
        } catch (\Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-appointments'));
        exit;
    }
    
    /**
     * Start appointment
     */
    private function startAppointment(int $appointment_id): void {
        try {
            $appointment = new \ProClean\Quotation\Models\Appointment($appointment_id);
            
            if (!$appointment->getId()) {
                throw new \Exception(__('Appointment not found.', 'pro-clean-quotation'));
            }
            
            // Update status to in_progress
            $appointment->data['status'] = 'in_progress';
            $appointment->data['updated_at'] = current_time('mysql');
            
            if ($appointment->save()) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Appointment started successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new \Exception(__('Failed to start appointment.', 'pro-clean-quotation'));
            }
        } catch (\Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-appointments'));
        exit;
    }
    
    /**
     * Complete appointment
     */
    private function completeAppointment(int $appointment_id): void {
        try {
            $appointment = new \ProClean\Quotation\Models\Appointment($appointment_id);
            
            if (!$appointment->getId()) {
                throw new \Exception(__('Appointment not found.', 'pro-clean-quotation'));
            }
            
            // Update status to completed
            $appointment->data['status'] = 'completed';
            $appointment->data['updated_at'] = current_time('mysql');
            
            if ($appointment->save()) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Appointment marked as complete.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new \Exception(__('Failed to complete appointment.', 'pro-clean-quotation'));
            }
        } catch (\Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-appointments'));
        exit;
    }
    
    /**
     * Save employee
     */
    private function saveEmployee(int $employee_id, array $data): void {
        global $wpdb;
        
        try {
            if ($employee_id) {
                // Update existing employee
                $employee = new \ProClean\Quotation\Models\Employee($employee_id);
                
                if (!$employee->getId()) {
                    throw new Exception(__('Employee not found.', 'pro-clean-quotation'));
                }
            } else {
                // Create new employee
                $employee = new \ProClean\Quotation\Models\Employee();
            }
            
            // Sanitize and validate data
            $employee_data = [
                'name' => sanitize_text_field($data['name'] ?? ''),
                'email' => sanitize_email($data['email'] ?? ''),
                'phone' => sanitize_text_field($data['phone'] ?? ''),
                'description' => sanitize_textarea_field($data['description'] ?? ''),
                'status' => in_array($data['status'] ?? 'active', ['active', 'inactive']) ? $data['status'] : 'active',
                'working_hours' => json_encode($data['working_hours'] ?? [])
            ];
            
            // Validate required fields
            if (empty($employee_data['name'])) {
                throw new Exception(__('Employee name is required.', 'pro-clean-quotation'));
            }
            
            // Update employee data
            foreach ($employee_data as $key => $value) {
                $employee->data[$key] = $value;
            }
            
            if ($employee->save()) {
                // Handle service assignments
                $assigned_services = array_map('intval', $data['assigned_services'] ?? []);
                $employee_services_table = $wpdb->prefix . 'pq_employee_services';
                
                // Remove existing assignments
                $wpdb->delete($employee_services_table, ['employee_id' => $employee->getId()], ['%d']);
                
                // Add new assignments
                foreach ($assigned_services as $service_id) {
                    $wpdb->insert($employee_services_table, [
                        'employee_id' => $employee->getId(),
                        'service_id' => $service_id,
                        'created_at' => current_time('mysql')
                    ]);
                }
                
                $message = $employee_id ? 
                    __('Employee updated successfully.', 'pro-clean-quotation') : 
                    __('Employee created successfully.', 'pro-clean-quotation');
                
                add_action('admin_notices', function() use ($message) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                });
                
                wp_redirect(admin_url('admin.php?page=pcq-employees&action=edit&id=' . $employee->getId()));
                exit;
            } else {
                throw new Exception(__('Failed to save employee.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }
    
    /**
     * Delete employee
     */
    private function deleteEmployee(int $employee_id): void {
        global $wpdb;
        
        try {
            $employee = new \ProClean\Quotation\Models\Employee($employee_id);
            
            if (!$employee->getId()) {
                throw new Exception(__('Employee not found.', 'pro-clean-quotation'));
            }
            
            // Check if employee has appointments
            $appointments_table = $wpdb->prefix . 'pq_appointments';
            $appointment_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $appointments_table WHERE employee_id = %d",
                $employee_id
            ));
            
            if ($appointment_count > 0) {
                throw new Exception(__('Cannot delete employee with existing appointments. Please reassign appointments first.', 'pro-clean-quotation'));
            }
            
            // Delete employee
            $employees_table = $wpdb->prefix . 'pq_employees';
            $result = $wpdb->delete($employees_table, ['id' => $employee_id], ['%d']);
            
            if ($result) {
                // Also delete employee-service relationships
                $employee_services_table = $wpdb->prefix . 'pq_employee_services';
                $wpdb->delete($employee_services_table, ['employee_id' => $employee_id], ['%d']);
                
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Employee deleted successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new Exception(__('Failed to delete employee.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-employees'));
        exit;
    }

    /**
     * Save service
     */
    private function saveService(int $service_id, array $data): void {
        try {
            if ($service_id) {
                // Update existing service
                $service = new \ProClean\Quotation\Models\Service($service_id);
                
                if (!$service->getId()) {
                    throw new Exception(__('Service not found.', 'pro-clean-quotation'));
                }
            } else {
                // Create new service
                $service = new \ProClean\Quotation\Models\Service();
            }
            
            // Sanitize and validate data
            $service_data = [
                'name' => sanitize_text_field($data['name'] ?? ''),
                'description' => sanitize_textarea_field($data['description'] ?? ''),
                'category_id' => intval($data['category_id'] ?? 0) ?: null,
                'duration' => max(15, intval($data['duration'] ?? 60)),
                'price' => max(0, floatval($data['price'] ?? 0)),
                'capacity' => max(1, intval($data['capacity'] ?? 1)),
                'buffer_time_before' => max(0, intval($data['buffer_time_before'] ?? 0)),
                'buffer_time_after' => max(0, intval($data['buffer_time_after'] ?? 0)),
                'color' => sanitize_hex_color($data['color'] ?? '#2196F3') ?: '#2196F3',
                'status' => in_array($data['status'] ?? 'active', ['active', 'inactive']) ? $data['status'] : 'active',
                'sort_order' => intval($data['sort_order'] ?? 0),
                'min_advance_time' => max(0, intval($data['min_advance_time'] ?? 0)),
                'max_advance_time' => max(0, intval($data['max_advance_time'] ?? 0))
            ];
            
            // Validate required fields
            if (empty($service_data['name'])) {
                throw new Exception(__('Service name is required.', 'pro-clean-quotation'));
            }
            
            // Update service data
            foreach ($service_data as $key => $value) {
                $service->data[$key] = $value;
            }
            
            if ($service->save()) {
                $message = $service_id ? 
                    __('Service updated successfully.', 'pro-clean-quotation') : 
                    __('Service created successfully.', 'pro-clean-quotation');
                
                add_action('admin_notices', function() use ($message) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                });
                
                wp_redirect(admin_url('admin.php?page=pcq-services&action=edit&id=' . $service->getId()));
                exit;
            } else {
                throw new Exception(__('Failed to save service.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }
    
    /**
     * Delete service
     */
    private function deleteService(int $service_id): void {
        global $wpdb;
        
        try {
            $service = new \ProClean\Quotation\Models\Service($service_id);
            
            if (!$service->getId()) {
                throw new Exception(__('Service not found.', 'pro-clean-quotation'));
            }
            
            // Check if service has appointments
            $appointments_table = $wpdb->prefix . 'pq_appointments';
            $appointment_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $appointments_table WHERE service_id = %d",
                $service_id
            ));
            
            if ($appointment_count > 0) {
                throw new Exception(__('Cannot delete service with existing appointments. Please cancel or reassign appointments first.', 'pro-clean-quotation'));
            }
            
            // Delete service
            $services_table = $wpdb->prefix . 'pq_services';
            $result = $wpdb->delete($services_table, ['id' => $service_id], ['%d']);
            
            if ($result) {
                // Also delete employee-service relationships
                $employee_services_table = $wpdb->prefix . 'pq_employee_services';
                $wpdb->delete($employee_services_table, ['service_id' => $service_id], ['%d']);
                
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Service deleted successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new Exception(__('Failed to delete service.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-services'));
        exit;
    }
    
    /**
     * Activate service
     */
    private function activateService(int $service_id): void {
        global $wpdb;
        
        try {
            $service = new \ProClean\Quotation\Models\Service($service_id);
            
            if (!$service->getId()) {
                throw new Exception(__('Service not found.', 'pro-clean-quotation'));
            }
            
            // Update service status to active
            $services_table = $wpdb->prefix . 'pq_services';
            $result = $wpdb->update(
                $services_table,
                ['status' => 'active', 'updated_at' => current_time('mysql')],
                ['id' => $service_id],
                ['%s', '%s'],
                ['%d']
            );
            
            if ($result !== false) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Service activated successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new Exception(__('Failed to activate service.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-services'));
        exit;
    }
    
    /**
     * Deactivate service
     */
    private function deactivateService(int $service_id): void {
        global $wpdb;
        
        try {
            $service = new \ProClean\Quotation\Models\Service($service_id);
            
            if (!$service->getId()) {
                throw new Exception(__('Service not found.', 'pro-clean-quotation'));
            }
            
            // Update service status to inactive
            $services_table = $wpdb->prefix . 'pq_services';
            $result = $wpdb->update(
                $services_table,
                ['status' => 'inactive', 'updated_at' => current_time('mysql')],
                ['id' => $service_id],
                ['%s', '%s'],
                ['%d']
            );
            
            if ($result !== false) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Service deactivated successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new Exception(__('Failed to deactivate service.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-services'));
        exit;
    }

    /**
     * Create appointment
     */
    private function createAppointment(array $data): void {
        try {
            // Sanitize and validate data
            $appointment_data = [
                'service_id' => intval($data['service_id'] ?? 0),
                'employee_id' => intval($data['employee_id'] ?? 0) ?: null,
                'quote_id' => intval($data['quote_id'] ?? 0) ?: null,
                'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
                'customer_email' => sanitize_email($data['customer_email'] ?? ''),
                'customer_phone' => sanitize_text_field($data['customer_phone'] ?? ''),
                'service_date' => sanitize_text_field($data['service_date'] ?? ''),
                'service_time_start' => sanitize_text_field($data['service_time_start'] ?? ''),
                'service_time_end' => sanitize_text_field($data['service_time_end'] ?? ''),
                'duration' => intval($data['duration'] ?? 0),
                'price' => floatval($data['price'] ?? 0),
                'status' => sanitize_text_field($data['status'] ?? 'pending'),
                'notes' => sanitize_textarea_field($data['notes'] ?? ''),
                'internal_notes' => sanitize_textarea_field($data['internal_notes'] ?? ''),
                'created_at' => current_time('mysql')
            ];
            
            // Get employee IDs (multiple)
            $employee_ids = isset($data['employee_ids']) && is_array($data['employee_ids']) 
                ? array_map('intval', $data['employee_ids']) 
                : [];
            
            // Remove auto-assign option (0) if present
            $employee_ids = array_filter($employee_ids, fn($id) => $id > 0);
            
            // Set primary employee_id for backward compatibility
            if (!empty($employee_ids)) {
                $appointment_data['employee_id'] = $employee_ids[0];
            }
            
            // Validate required fields
            if (empty($appointment_data['service_id']) || 
                empty($appointment_data['customer_name']) || 
                empty($appointment_data['customer_email']) ||
                empty($appointment_data['service_date']) ||
                empty($appointment_data['service_time_start']) ||
                empty($appointment_data['service_time_end']) ||
                $appointment_data['duration'] <= 0 ||
                $appointment_data['price'] <= 0) {
                throw new Exception(__('Please fill in all required fields with valid values.', 'pro-clean-quotation'));
            }
            
            // Create appointment
            $appointment = \ProClean\Quotation\Models\Appointment::create($appointment_data);
            
            if ($appointment) {
                // Assign team members
                if (!empty($employee_ids)) {
                    $appointment->setEmployees($employee_ids);
                }
                
                // Update quote status if created from quote
                if ($appointment_data['quote_id']) {
                    $quote = new \ProClean\Quotation\Models\Quote($appointment_data['quote_id']);
                    if ($quote->getId()) {
                        $quote->data['status'] = 'converted';
                        $quote->save();
                    }
                }
                
                $team_size = count($employee_ids);
                $message = $team_size > 0 
                    ? sprintf(__('Appointment created successfully with team of %d member(s).', 'pro-clean-quotation'), $team_size)
                    : __('Appointment created successfully.', 'pro-clean-quotation');
                
                add_action('admin_notices', function() use ($message) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                });
                
                wp_redirect(admin_url('admin.php?page=pcq-appointments&action=view&id=' . $appointment->getId()));
                exit;
            } else {
                throw new Exception(__('Failed to create appointment.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }

    /**
     * Save appointment
     */
    private function saveAppointment(int $appointment_id, array $data): void {
        try {
            $appointment = new \ProClean\Quotation\Models\Appointment($appointment_id);
            
            if (!$appointment->getId()) {
                throw new Exception(__('Appointment not found.', 'pro-clean-quotation'));
            }
            
            // Sanitize and validate data
            $appointment_data = [
                'service_id' => intval($data['service_id'] ?? 0),
                'employee_id' => intval($data['employee_id'] ?? 0) ?: null,
                'customer_name' => sanitize_text_field($data['customer_name'] ?? ''),
                'customer_email' => sanitize_email($data['customer_email'] ?? ''),
                'customer_phone' => sanitize_text_field($data['customer_phone'] ?? ''),
                'service_date' => sanitize_text_field($data['service_date'] ?? ''),
                'service_time_start' => sanitize_text_field($data['service_time_start'] ?? ''),
                'service_time_end' => sanitize_text_field($data['service_time_end'] ?? ''),
                'duration' => intval($data['duration'] ?? 0),
                'price' => floatval($data['price'] ?? 0),
                'status' => sanitize_text_field($data['status'] ?? 'pending'),
                'notes' => sanitize_textarea_field($data['notes'] ?? ''),
                'internal_notes' => sanitize_textarea_field($data['internal_notes'] ?? '')
            ];
            
            // Get employee IDs (multiple)
            $employee_ids = isset($data['employee_ids']) && is_array($data['employee_ids']) 
                ? array_map('intval', $data['employee_ids']) 
                : [];
            
            // Remove auto-assign option (0) if present
            $employee_ids = array_filter($employee_ids, fn($id) => $id > 0);
            
            // Set primary employee_id for backward compatibility
            if (!empty($employee_ids)) {
                $appointment_data['employee_id'] = $employee_ids[0];
            }
            
            // Validate required fields
            if (empty($appointment_data['service_id']) || 
                empty($appointment_data['customer_name']) || 
                empty($appointment_data['customer_email']) ||
                empty($appointment_data['service_date']) ||
                empty($appointment_data['service_time_start']) ||
                empty($appointment_data['service_time_end'])) {
                throw new Exception(__('Please fill in all required fields.', 'pro-clean-quotation'));
            }
            
            // Update appointment data
            foreach ($appointment_data as $key => $value) {
                $appointment->data[$key] = $value;
            }
            
            if ($appointment->save()) {
                // Update team members
                if (!empty($employee_ids)) {
                    $appointment->setEmployees($employee_ids);
                }
                
                $team_size = count($employee_ids);
                $message = $team_size > 0 
                    ? sprintf(__('Appointment updated successfully with team of %d member(s).', 'pro-clean-quotation'), $team_size)
                    : __('Appointment updated successfully.', 'pro-clean-quotation');
                
                add_action('admin_notices', function() use ($message) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                });
                
                wp_redirect(admin_url('admin.php?page=pcq-appointments&action=view&id=' . $appointment_id));
                exit;
            } else {
                throw new Exception(__('Failed to save appointment.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }
    
    /**
     * Render email log view
     */
    private function renderEmailLogView(int $log_id): void {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_email_logs';
        $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $log_id));
        
        if (!$log) {
            wp_die(__('Email log not found.', 'pro-clean-quotation'));
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/email-log-view.php';
    }
    
    /**
     * Resend email
     */
    private function resendEmail(int $log_id): void {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'pq_email_logs';
            $log = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $log_id));
            
            if (!$log) {
                throw new Exception(__('Email log not found.', 'pro-clean-quotation'));
            }
            
            if ($log->status !== 'failed') {
                throw new Exception(__('Only failed emails can be resent.', 'pro-clean-quotation'));
            }
            
            // Get the original reference data
            $reference_data = null;
            if ($log->reference_type && $log->reference_id) {
                switch ($log->reference_type) {
                    case 'quote':
                        $reference_data = new \ProClean\Quotation\Models\Quote($log->reference_id);
                        break;
                    case 'appointment':
                        $reference_data = new \ProClean\Quotation\Models\Appointment($log->reference_id);
                        break;
                }
            }
            
            // Use EmailManager to resend
            $email_manager = \ProClean\Quotation\Email\EmailManager::getInstance();
            
            // This is a simplified resend - in a real implementation, you'd need to 
            // reconstruct the original email data and template
            $result = wp_mail(
                $log->recipient_email,
                $log->subject,
                'This is a resent email. Original content may not be available.',
                ['Content-Type: text/html; charset=UTF-8']
            );
            
            if ($result) {
                // Update log status
                $wpdb->update(
                    $table,
                    ['status' => 'sent', 'sent_at' => current_time('mysql'), 'error_message' => null],
                    ['id' => $log_id],
                    ['%s', '%s', '%s'],
                    ['%d']
                );
                
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Email resent successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new Exception(__('Failed to resend email.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-email-logs'));
        exit;
    }
    
    /**
     * Delete email log
     */
    private function deleteEmailLog(int $log_id): void {
        global $wpdb;
        
        try {
            $table = $wpdb->prefix . 'pq_email_logs';
            $result = $wpdb->delete($table, ['id' => $log_id], ['%d']);
            
            if ($result) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Email log deleted successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new Exception(__('Failed to delete email log.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-email-logs'));
        exit;
    }
    
    /**
     * Render service categories page
     */
    public function renderServiceCategories(): void {
        $action = $_GET['action'] ?? 'list';
        
        switch ($action) {
            case 'add':
            case 'edit':
                $this->renderServiceCategoryForm();
                break;
            default:
                $this->renderServiceCategoriesList();
                break;
        }
    }
    
    /**
     * Render service categories list
     */
    private function renderServiceCategoriesList(): void {
        global $wpdb;
        
        // Get search parameter
        $search = $_GET['s'] ?? '';
        
        $categories_table = $wpdb->prefix . 'pq_service_categories';
        
        if (!empty($search)) {
            // Search categories by name or description
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $categories = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM $categories_table WHERE name LIKE %s OR description LIKE %s ORDER BY sort_order ASC, name ASC",
                    $search_term,
                    $search_term
                )
            );
        } else {
            // Get all categories
            $categories = $wpdb->get_results("SELECT * FROM $categories_table ORDER BY sort_order ASC, name ASC");
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/service-categories-list.php';
    }
    
    /**
     * Render service category form
     */
    private function renderServiceCategoryForm(): void {
        $action = $_GET['action'];
        $category_id = intval($_GET['id'] ?? 0);
        $category = null;
        
        if ($category_id) {
            global $wpdb;
            $categories_table = $wpdb->prefix . 'pq_service_categories';
            $category = $wpdb->get_row($wpdb->prepare("SELECT * FROM $categories_table WHERE id = %d", $category_id));
            
            if (!$category) {
                wp_die(__('Service category not found.', 'pro-clean-quotation'));
            }
        }
        
        include PCQ_PLUGIN_DIR . 'templates/admin/service-category-form.php';
    }
    
    /**
     * Save service category
     */
    private function saveServiceCategory(int $category_id, array $data): void {
        global $wpdb;
        
        try {
            $categories_table = $wpdb->prefix . 'pq_service_categories';
            
            // Sanitize and validate data
            $category_data = [
                'name' => sanitize_text_field($data['name'] ?? ''),
                'description' => sanitize_textarea_field($data['description'] ?? ''),
                'color' => sanitize_hex_color($data['color'] ?? '#2196F3') ?: '#2196F3',
                'sort_order' => intval($data['sort_order'] ?? 0),
                'status' => in_array($data['status'] ?? 'active', ['active', 'inactive']) ? $data['status'] : 'active',
                'updated_at' => current_time('mysql')
            ];
            
            // Validate required fields
            if (empty($category_data['name'])) {
                throw new Exception(__('Category name is required.', 'pro-clean-quotation'));
            }
            
            if ($category_id) {
                // Update existing category
                $result = $wpdb->update($categories_table, $category_data, ['id' => $category_id], ['%s', '%s', '%s', '%d', '%s', '%s'], ['%d']);
                $message = __('Service category updated successfully.', 'pro-clean-quotation');
                $redirect_id = $category_id;
            } else {
                // Create new category
                $category_data['created_at'] = current_time('mysql');
                $result = $wpdb->insert($categories_table, $category_data, ['%s', '%s', '%s', '%d', '%s', '%s', '%s']);
                $message = __('Service category created successfully.', 'pro-clean-quotation');
                $redirect_id = $wpdb->insert_id;
            }
            
            if ($result !== false) {
                add_action('admin_notices', function() use ($message) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($message) . '</p></div>';
                });
                
                wp_redirect(admin_url('admin.php?page=pcq-service-categories&action=edit&id=' . $redirect_id));
                exit;
            } else {
                throw new Exception(__('Failed to save service category.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
    }
    
    /**
     * Delete service category
     */
    private function deleteServiceCategory(int $category_id): void {
        global $wpdb;
        
        try {
            $categories_table = $wpdb->prefix . 'pq_service_categories';
            $services_table = $wpdb->prefix . 'pq_services';
            
            // Check if category has services
            $service_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $services_table WHERE category_id = %d",
                $category_id
            ));
            
            if ($service_count > 0) {
                throw new Exception(sprintf(
                    __('Cannot delete category with %d service(s). Please reassign or delete services first.', 'pro-clean-quotation'),
                    $service_count
                ));
            }
            
            // Delete category
            $result = $wpdb->delete($categories_table, ['id' => $category_id], ['%d']);
            
            if ($result) {
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Service category deleted successfully.', 'pro-clean-quotation') . '</p></div>';
                });
            } else {
                throw new Exception(__('Failed to delete service category.', 'pro-clean-quotation'));
            }
            
        } catch (Exception $e) {
            add_action('admin_notices', function() use ($e) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($e->getMessage()) . '</p></div>';
            });
        }
        
        wp_redirect(admin_url('admin.php?page=pcq-service-categories'));
        exit;
    }
    
    /**
     * Render shortcodes reference page
     */
    public function renderShortcodes(): void {
        include PCQ_PLUGIN_DIR . 'templates/admin/shortcodes-reference.php';
    }
}