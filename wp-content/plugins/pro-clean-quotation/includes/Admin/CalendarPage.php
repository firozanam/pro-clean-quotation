<?php

namespace ProClean\Quotation\Admin;

use ProClean\Quotation\Services\AppointmentManager;
use ProClean\Quotation\Models\Service;
use ProClean\Quotation\Models\Employee;

/**
 * Calendar Page Class
 * 
 * @package ProClean\Quotation\Admin
 * @since 1.0.0
 */
class CalendarPage {
    
    /**
     * Render calendar page
     */
    public function render(): void {
        // Ensure scripts are loaded for this page
        wp_enqueue_script('jquery');
        wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js', [], '6.1.10', true);
        
        $current_view = $_GET['view'] ?? 'month';
        $current_date = $_GET['date'] ?? date('Y-m-d');
        $employee_filter = $_GET['employee'] ?? '';
        
        $services = Service::getAll();
        $employees = Employee::getAll();
        
        ?>
        <div class="wrap pcq-calendar-page">
            <h1><?php _e('Appointment Calendar', 'pro-clean-quotation'); ?></h1>
            

            <!-- Calendar Controls -->
            <div class="pcq-calendar-controls">
                <div class="pcq-calendar-nav">
                    <button id="pcq-prev-period" class="button">&laquo; <?php _e('Previous', 'pro-clean-quotation'); ?></button>
                    <button id="pcq-today" class="button"><?php _e('Today', 'pro-clean-quotation'); ?></button>
                    <button id="pcq-next-period" class="button"><?php _e('Next', 'pro-clean-quotation'); ?> &raquo;</button>
                    <span id="pcq-current-period" class="pcq-period-display"></span>
                </div>
                
                <div class="pcq-calendar-views">
                    <select id="pcq-view-selector">
                        <option value="dayGridMonth" <?php selected($current_view, 'month'); ?>><?php _e('Calendar View', 'pro-clean-quotation'); ?></option>
                        <option value="listWeek" <?php selected($current_view, 'list'); ?>><?php _e('List View', 'pro-clean-quotation'); ?></option>
                    </select>
                </div>
                
                <div class="pcq-calendar-filters">
                    <select id="pcq-employee-filter">
                        <option value=""><?php _e('All Employees', 'pro-clean-quotation'); ?></option>
                        <?php foreach ($employees as $employee): ?>
                            <option value="<?php echo $employee->getId(); ?>" <?php selected($employee_filter, $employee->getId()); ?>>
                                <?php echo esc_html($employee->getName()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button id="pcq-add-appointment" class="button button-primary">
                        <?php _e('Add Appointment', 'pro-clean-quotation'); ?>
                    </button>
                    

                </div>
            </div>
            
            <!-- Calendar Container -->
            <div id="pcq-calendar" class="pcq-calendar-container">
                <div class="pcq-calendar-loading">
                    <p>Loading calendar...</p>
                </div>
            </div>
            
            <!-- Fallback Calendar (Simple Table View) -->
            <div id="pcq-calendar-fallback" class="pcq-calendar-fallback" style="display: none;">
                <h3>Calendar (Simple View)</h3>
                <p>The advanced calendar is loading. Here's a simple view of upcoming appointments:</p>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Customer</th>
                            <th>Service</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="pcq-appointments-table">
                        <tr>
                            <td colspan="5">Loading appointments...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Service Legend -->
            <div class="pcq-service-legend">
                <h3><?php _e('Services', 'pro-clean-quotation'); ?></h3>
                <div class="pcq-legend-items">
                    <?php foreach ($services as $service): ?>
                        <div class="pcq-legend-item">
                            <span class="pcq-legend-color" style="background-color: <?php echo esc_attr($service->getColor()); ?>"></span>
                            <span class="pcq-legend-label"><?php echo esc_html($service->getName()); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Appointment Modal -->
        <div id="pcq-appointment-modal" class="pcq-modal" style="display: none;">
            <div class="pcq-modal-content">
                <div class="pcq-modal-header">
                    <h2 id="pcq-modal-title"><?php _e('Appointment Details', 'pro-clean-quotation'); ?></h2>
                    <button class="pcq-modal-close">&times;</button>
                </div>
                <div class="pcq-modal-body">
                    <form id="pcq-appointment-form">
                        <input type="hidden" id="appointment-id" name="appointment_id">
                        
                        <div class="pcq-form-field">
                            <label for="appointment-quote"><?php _e('Select Quote (Optional)', 'pro-clean-quotation'); ?></label>
                            <select id="appointment-quote" name="quote_id">
                                <option value=""><?php _e('-- Select a quote to auto-fill --', 'pro-clean-quotation'); ?></option>
                            </select>
                            <p class="description"><?php _e('Select an existing quote to automatically fill customer and service information.', 'pro-clean-quotation'); ?></p>
                        </div>
                        
                        <div class="pcq-form-row">
                            <div class="pcq-form-field">
                                <label for="appointment-service"><?php _e('Service', 'pro-clean-quotation'); ?></label>
                                <select id="appointment-service" name="service_id" required>
                                    <option value=""><?php _e('Select Service', 'pro-clean-quotation'); ?></option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?php echo $service->getId(); ?>" data-duration="<?php echo $service->getDuration() ?? 0; ?>" data-color="<?php echo esc_attr($service->getColor()); ?>">
                                            <?php echo esc_html($service->getName()); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="pcq-form-field">
                                <label for="appointment-employee"><?php _e('Employee', 'pro-clean-quotation'); ?></label>
                                <select id="appointment-employee" name="employee_id">
                                    <option value=""><?php _e('Auto-assign', 'pro-clean-quotation'); ?></option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo $employee->getId(); ?>">
                                            <?php echo esc_html($employee->getName()); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="pcq-form-row">
                            <div class="pcq-form-field">
                                <label for="appointment-date"><?php _e('Date', 'pro-clean-quotation'); ?></label>
                                <input type="date" id="appointment-date" name="service_date" required>
                            </div>
                            
                            <div class="pcq-form-field">
                                <label for="appointment-time-start"><?php _e('Start Time', 'pro-clean-quotation'); ?></label>
                                <input type="time" id="appointment-time-start" name="service_time_start" required>
                            </div>
                            
                            <div class="pcq-form-field">
                                <label for="appointment-time-end"><?php _e('End Time', 'pro-clean-quotation'); ?></label>
                                <input type="time" id="appointment-time-end" name="service_time_end" required>
                            </div>
                        </div>
                        
                        <div class="pcq-form-section">
                            <h4><?php _e('Customer Information', 'pro-clean-quotation'); ?></h4>
                            
                            <div class="pcq-form-row">
                                <div class="pcq-form-field">
                                    <label for="customer-name"><?php _e('Customer Name', 'pro-clean-quotation'); ?></label>
                                    <input type="text" id="customer-name" name="customer_name" required>
                                </div>
                                
                                <div class="pcq-form-field">
                                    <label for="customer-email"><?php _e('Email', 'pro-clean-quotation'); ?></label>
                                    <input type="email" id="customer-email" name="customer_email" required>
                                </div>
                            </div>
                            
                            <div class="pcq-form-row">
                                <div class="pcq-form-field">
                                    <label for="customer-phone"><?php _e('Phone', 'pro-clean-quotation'); ?></label>
                                    <input type="tel" id="customer-phone" name="customer_phone">
                                </div>
                                
                                <div class="pcq-form-field">
                                    <label for="appointment-price"><?php _e('Price (€)', 'pro-clean-quotation'); ?></label>
                                    <input type="number" id="appointment-price" name="price" step="0.01" min="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="pcq-form-field">
                            <label for="appointment-notes"><?php _e('Notes', 'pro-clean-quotation'); ?></label>
                            <textarea id="appointment-notes" name="notes" rows="3"></textarea>
                        </div>
                        
                        <div class="pcq-form-field">
                            <label for="appointment-status"><?php _e('Status', 'pro-clean-quotation'); ?></label>
                            <select id="appointment-status" name="status">
                                <option value="pending"><?php _e('Pending', 'pro-clean-quotation'); ?></option>
                                <option value="confirmed"><?php _e('Confirmed', 'pro-clean-quotation'); ?></option>
                                <option value="in_progress"><?php _e('In Progress', 'pro-clean-quotation'); ?></option>
                                <option value="completed"><?php _e('Completed', 'pro-clean-quotation'); ?></option>
                                <option value="cancelled"><?php _e('Cancelled', 'pro-clean-quotation'); ?></option>
                                <option value="no_show"><?php _e('No Show', 'pro-clean-quotation'); ?></option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="pcq-modal-footer">
                    <button type="button" class="button" id="pcq-cancel-appointment"><?php _e('Cancel', 'pro-clean-quotation'); ?></button>
                    <button type="button" class="button button-secondary" id="pcq-delete-appointment" style="display: none;"><?php _e('Delete', 'pro-clean-quotation'); ?></button>
                    <button type="submit" class="button button-primary" form="pcq-appointment-form" id="pcq-save-appointment"><?php _e('Save Appointment', 'pro-clean-quotation'); ?></button>
                </div>
            </div>
        </div>
        
        <style>
        .pcq-calendar-page {
            margin: 20px 20px 20px 0;
        }
        
        .pcq-calendar-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
            padding: 15px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .pcq-calendar-nav {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .pcq-calendar-nav .button {
            padding: 8px 12px;
            font-size: 14px;
            height: auto;
            line-height: 1;
        }
        
        .pcq-calendar-filters .button {
            padding: 8px 12px;
            font-size: 14px;
            height: auto;
            line-height: 1;
        }
        
        .pcq-calendar-filters .button-primary {
            padding: 8px 12px;
            font-size: 14px;
            height: auto;
            line-height: 1;
        }
        
        .pcq-period-display {
            font-size: 18px;
            font-weight: 600;
            margin-left: 20px;
            color: #2c3e50;
        }
        
        .pcq-calendar-views select,
        .pcq-calendar-filters select {
            padding: 8px 32px 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            min-width: 150px;
            font-size: 14px;
            height: auto;
            line-height: 1.4;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background: #fff url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"><path fill="%23666" d="M6 9L1 4h10z"/></svg>') no-repeat right 10px center;
            background-size: 12px;
        }
        
        .pcq-calendar-views select:focus,
        .pcq-calendar-filters select:focus {
            outline: none;
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
        }
        
        .pcq-calendar-filters {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .pcq-calendar-container {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
            padding: 20px;
            min-height: 600px;
        }
        
        .pcq-service-legend {
            margin-top: 20px;
            padding: 15px;
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 8px;
        }
        
        .pcq-legend-items {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }
        
        .pcq-legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .pcq-legend-color {
            width: 16px;
            height: 16px;
            border-radius: 3px;
        }
        
        .pcq-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .pcq-modal-content {
            background: #fff;
            border-radius: 8px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .pcq-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }
        
        .pcq-modal-header h2 {
            margin: 0;
        }
        
        .pcq-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        
        .pcq-modal-body {
            padding: 20px;
        }
        
        .pcq-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 20px;
            border-top: 1px solid #ddd;
        }
        
        .pcq-form-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        
        .pcq-form-section h4 {
            margin: 0 0 15px 0;
            color: #2c3e50;
        }
        
        .pcq-form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .pcq-form-field {
            margin-bottom: 15px;
        }
        
        .pcq-form-field label {
            display: block;
            font-weight: 500;
            margin-bottom: 5px;
            color: #2c3e50;
        }
        
        .pcq-form-field input,
        .pcq-form-field select,
        .pcq-form-field textarea {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .pcq-form-field input:focus,
        .pcq-form-field select:focus,
        .pcq-form-field textarea:focus {
            outline: none;
            border-color: #2196F3;
            box-shadow: 0 0 0 2px rgba(33, 150, 243, 0.2);
        }
        
        @media (max-width: 768px) {
            .pcq-calendar-controls {
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .pcq-calendar-nav,
            .pcq-calendar-filters {
                justify-content: center;
            }
            
            .pcq-form-row {
                grid-template-columns: 1fr;
            }
        }
        </style>
        
        <script>
        // Ensure pcq_admin_ajax is available - always create it
        window.pcq_admin_ajax = {
            ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
            nonce: '<?php echo wp_create_nonce('pcq_admin_nonce'); ?>'
        };
        console.log('PCQ: AJAX object created:', window.pcq_admin_ajax);
        
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize calendar functionality
            console.log('DOM loaded, initializing calendar...');
            console.log('AJAX object available:', typeof pcq_admin_ajax !== 'undefined');
            console.log('jQuery available:', typeof jQuery !== 'undefined');
            console.log('FullCalendar available:', typeof FullCalendar !== 'undefined');
            
            // Test AJAX button (debug mode)
            jQuery('#pcq-test-ajax').on('click', function() {
                console.log('Testing AJAX...');
                testAjaxConnection();
            });
            
            // Always show the fallback calendar first
            showFallbackCalendar();
            
            // Try to load appointments data immediately
            loadAppointmentsData();
            
            // Try to initialize FullCalendar after a delay
            setTimeout(function() {
                if (typeof FullCalendar !== 'undefined') {
                    console.log('FullCalendar available, initializing advanced calendar...');
                    initializeFullCalendar();
                } else {
                    console.log('FullCalendar not available, using fallback calendar');
                }
            }, 2000);
            
            // Test AJAX connection
            function testAjaxConnection() {
                jQuery.ajax({
                    url: pcq_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pcq_get_calendar_events',
                        nonce: pcq_admin_ajax.nonce,
                        start: '2025-01-01',
                        end: '2025-12-31',
                        employee_id: ''
                    },
                    success: function(response) {
                        console.log('AJAX test success:', response);
                        alert('AJAX test successful! Found ' + (response.data ? response.data.length : 0) + ' appointments.');
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX test error:', error);
                        alert('AJAX test failed: ' + error);
                    }
                });
            }
            
            // Initialize FullCalendar
            function initializeFullCalendar() {
                try {
                    var calendarEl = document.getElementById('pcq-calendar');
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: {
                            left: 'prev,next today',
                            center: 'title',
                            right: 'dayGridMonth,timeGridWeek,timeGridDay'
                        },
                        height: 'auto',
                        events: function(fetchInfo, successCallback, failureCallback) {
                            loadCalendarEvents(fetchInfo, successCallback, failureCallback);
                        },
                        dateClick: function(info) {
                            alert('Date clicked: ' + info.dateStr + '. Appointment modal will be implemented.');
                        },
                        eventClick: function(info) {
                            alert('Appointment clicked: ' + info.event.title);
                        }
                    });
                    
                    calendar.render();
                    console.log('FullCalendar rendered successfully');
                    
                    // Hide fallback and show FullCalendar
                    document.getElementById('pcq-calendar-fallback').style.display = 'none';
                    calendarEl.style.display = 'block';
                    
                    // Handle view selector change
                    jQuery('#pcq-view-selector').on('change', function() {
                        var selectedView = jQuery(this).val();
                        console.log('Changing view to:', selectedView);
                        calendar.changeView(selectedView);
                    });
                    
                    // Handle navigation buttons
                    jQuery('#pcq-prev-period').on('click', function() {
                        calendar.prev();
                    });
                    
                    jQuery('#pcq-next-period').on('click', function() {
                        calendar.next();
                    });
                    
                    jQuery('#pcq-today').on('click', function() {
                        calendar.today();
                    });
                    
                } catch (error) {
                    console.error('FullCalendar initialization error:', error);
                }
            }
            
            // Function to load appointments data
            function loadAppointmentsData() {
                var currentYear = new Date().getFullYear();
                var startDate = currentYear + '-01-01';
                var endDate = (currentYear + 1) + '-12-31';
                
                jQuery.ajax({
                    url: pcq_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pcq_get_calendar_events',
                        nonce: pcq_admin_ajax.nonce,
                        start: startDate,
                        end: endDate,
                        employee_id: ''
                    },
                    success: function(response) {
                        console.log('Appointments data loaded:', response);
                        if (response.success) {
                            populateFallbackCalendar(response.data);
                        } else {
                            console.error('Failed to load appointments:', response.data);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error loading appointments:', error);
                        showFallbackCalendar();
                    }
                });
            }
            
            // Function to load calendar events for FullCalendar
            function loadCalendarEvents(fetchInfo, successCallback, failureCallback) {
                jQuery.ajax({
                    url: pcq_admin_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'pcq_get_calendar_events',
                        nonce: pcq_admin_ajax.nonce,
                        start: fetchInfo.startStr,
                        end: fetchInfo.endStr,
                        employee_id: jQuery('#pcq-employee-filter').val() || ''
                    },
                    success: function(response) {
                        if (response.success) {
                            successCallback(response.data || []);
                        } else {
                            failureCallback(response.data || 'Failed to load events');
                        }
                    },
                    error: function(xhr, status, error) {
                        failureCallback('AJAX error: ' + error);
                    }
                });
            }
            
            // Function to show fallback calendar
            function showFallbackCalendar() {
                document.getElementById('pcq-calendar').style.display = 'none';
                document.getElementById('pcq-calendar-fallback').style.display = 'block';
            }
            
            // Function to populate fallback calendar
            function populateFallbackCalendar(appointments) {
                var tableBody = document.getElementById('pcq-appointments-table');
                
                if (!appointments || appointments.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="5">No appointments found.</td></tr>';
                    return;
                }
                
                var html = '';
                appointments.forEach(function(appointment) {
                    var props = appointment.extendedProps || {};
                    var startDate = new Date(appointment.start);
                    
                    html += '<tr>';
                    html += '<td>' + startDate.toLocaleDateString() + '</td>';
                    html += '<td>' + startDate.toLocaleTimeString() + '</td>';
                    html += '<td>' + (props.customer_name || 'Unknown') + '</td>';
                    html += '<td>' + (props.service_name || appointment.title || 'Service') + '</td>';
                    html += '<td>' + (props.status || 'Unknown') + '</td>';
                    html += '</tr>';
                });
                
                tableBody.innerHTML = html;
            }
        });
        </script>
        <?php
    }
}