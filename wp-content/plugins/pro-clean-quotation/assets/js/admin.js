/**
 * Pro Clean Quotation - Admin JavaScript
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize admin functionality
        initSettingsTabs();
        initColorPickers();
        initFormValidation();
        initCalendar();
        initAppointmentModal();
        
        // Failsafe: Ensure settings page elements are visible
        ensureSettingsPageVisibility();
    });

    /**
     * Ensure settings page elements are always visible
     */
    function ensureSettingsPageVisibility() {
        // Only run on settings page
        if (!jQuery('.pcq-settings-page').length) {
            return;
        }
        
        // Force visibility of key elements
        jQuery('.pcq-settings-page.wrap').css({
            'display': 'block',
            'visibility': 'visible'
        });
        
        jQuery('.pcq-settings-page.wrap > h1').css({
            'display': 'block',
            'visibility': 'visible'
        });
        
        jQuery('.pcq-nav-tab-wrapper').css({
            'display': 'flex',
            'visibility': 'visible'
        });
        
        // Log for debugging
        console.log('PCQ: Settings page visibility ensured');
    }

    /**
     * Initialize settings tabs
     */
    function initSettingsTabs() {
        // Hide all tab content initially
        jQuery('.pcq-tab-content').hide();
        
        jQuery('.pcq-nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const targetTab = jQuery(this).attr('href').substring(1);
            
            // Update active tab
            jQuery('.pcq-nav-tab').removeClass('pcq-nav-tab-active');
            jQuery(this).addClass('pcq-nav-tab-active');
            
            // Show/hide tab content
            jQuery('.pcq-tab-content').hide();
            jQuery('#' + targetTab).fadeIn(200);
            
            // Update URL hash without jumping
            if (history.pushState) {
                history.pushState(null, null, '#' + targetTab);
            } else {
                window.location.hash = targetTab;
            }
        });
        
        // Show active tab on page load
        const hash = window.location.hash.substring(1);
        if (hash && jQuery('#' + hash).length) {
            jQuery('.pcq-nav-tab[href="#' + hash + '"]').addClass('pcq-nav-tab-active');
            jQuery('#' + hash).show();
        } else {
            jQuery('.pcq-nav-tab').first().addClass('pcq-nav-tab-active');
            jQuery('.pcq-tab-content').first().show();
        }
    }

    /**
     * Initialize color pickers
     */
    function initColorPickers() {
        if (jQuery.fn.wpColorPicker) {
            jQuery('.pcq-color-picker').wpColorPicker();
        }
    }

    /**
     * Initialize form validation
     */
    function initFormValidation() {
        jQuery('form.pcq-settings-form').on('submit', function(e) {
            let isValid = true;
            
            // Validate required fields
            jQuery(this).find('[required]').each(function() {
                const field = jQuery(this);
                const value = field.val().trim();
                
                if (!value) {
                    field.addClass('error');
                    isValid = false;
                } else {
                    field.removeClass('error');
                }
            });
            
            // Validate email fields
            jQuery(this).find('input[type="email"]').each(function() {
                const field = jQuery(this);
                const value = field.val().trim();
                
                if (value && !isValidEmail(value)) {
                    field.addClass('error');
                    isValid = false;
                } else {
                    field.removeClass('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please correct the highlighted fields.');
            }
        });
    }

    /**
     * Validate email address
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Initialize calendar functionality
     */
    function initCalendar() {
        if (jQuery('#pcq-calendar').length === 0) {
            return;
        }

        // Wait for FullCalendar to be available
        if (typeof FullCalendar === 'undefined') {
            console.log('FullCalendar not loaded, retrying...');
            setTimeout(initCalendar, 500);
            return;
        }

        console.log('Initializing FullCalendar...');

        // Calendar configuration
        const calendarConfig = {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: '',
                center: 'title',
                right: ''
            },
            height: 'auto',
            events: function(fetchInfo, successCallback, failureCallback) {
                console.log('Fetching calendar events...');
                
                // Fetch events via AJAX
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
                        console.log('Calendar events response:', response);
                        if (response.success) {
                            successCallback(response.data || []);
                        } else {
                            console.error('Calendar events error:', response.data);
                            failureCallback(response.data || 'Failed to load events');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                        failureCallback('Failed to load events: ' + error);
                    }
                });
            },
            eventClick: function(info) {
                console.log('Event clicked:', info.event);
                if (info.event.extendedProps && info.event.extendedProps.appointment_id) {
                    openAppointmentModal(info.event.extendedProps.appointment_id);
                }
            },
            dateClick: function(info) {
                console.log('Date clicked:', info.dateStr);
                openAppointmentModal(null, info.dateStr);
            },
            eventDidMount: function(info) {
                // Add tooltip with appointment details
                const appointment = info.event.extendedProps;
                if (appointment) {
                    const tooltip = `
                        ${appointment.customer_name || 'Unknown Customer'}
                        ${appointment.service_name || 'Service'}
                        ${appointment.employee_name || 'Unassigned'}
                        €${appointment.price || '0'}
                    `;
                    
                    jQuery(info.el).attr('title', tooltip.replace(/\s+/g, ' ').trim());
                }
            },
            loading: function(isLoading) {
                console.log('Calendar loading:', isLoading);
                if (isLoading) {
                    jQuery('#pcq-calendar').addClass('loading');
                } else {
                    jQuery('#pcq-calendar').removeClass('loading');
                }
            }
        };

        try {
            // Initialize FullCalendar
            const calendar = new FullCalendar.Calendar(document.getElementById('pcq-calendar'), calendarConfig);
            calendar.render();
            
            console.log('Calendar rendered successfully');

            // Calendar controls
            jQuery('#pcq-prev-period').on('click', function() {
                calendar.prev();
                updatePeriodDisplay(calendar);
            });

            jQuery('#pcq-next-period').on('click', function() {
                calendar.next();
                updatePeriodDisplay(calendar);
            });

            jQuery('#pcq-today').on('click', function() {
                calendar.today();
                updatePeriodDisplay(calendar);
            });

            jQuery('#pcq-view-selector').on('change', function() {
                calendar.changeView(jQuery(this).val());
                updatePeriodDisplay(calendar);
            });

            jQuery('#pcq-employee-filter').on('change', function() {
                console.log('Employee filter changed:', jQuery(this).val());
                calendar.refetchEvents();
            });

            // Update period display
            function updatePeriodDisplay(calendar) {
                const view = calendar.view;
                const title = view.title;
                jQuery('#pcq-current-period').text(title);
            }

            // Initial period display
            updatePeriodDisplay(calendar);

            // Store calendar instance globally
            window.pcqCalendar = calendar;
            
        } catch (error) {
            console.error('Calendar initialization error:', error);
            jQuery('#pcq-calendar').html('<div class="error"><p>Failed to initialize calendar: ' + error.message + '</p></div>');
        }
    }

    /**
     * Initialize appointment modal
     */
    function initAppointmentModal() {
        // Open modal button
        jQuery('#pcq-add-appointment').on('click', function() {
            openAppointmentModal();
        });

        // Close modal
        jQuery('.pcq-modal-close, #pcq-cancel-appointment').on('click', function() {
            closeAppointmentModal();
        });

        // Close modal on outside click
        jQuery('#pcq-appointment-modal').on('click', function(e) {
            if (e.target === this) {
                closeAppointmentModal();
            }
        });
        
        // Quote selection change - auto-fill form
        jQuery('#appointment-quote').on('change', function() {
            const quoteId = jQuery(this).val();
            if (quoteId) {
                autoFillFromQuote(quoteId);
            }
        });

        // Service selection change
        jQuery('#appointment-service').on('change', function() {
            const selectedOption = jQuery(this).find('option:selected');
            const duration = selectedOption.data('duration') || 60;
            
            // Auto-calculate end time based on duration
            const startTime = jQuery('#appointment-time-start').val();
            if (startTime) {
                const endTime = calculateEndTime(startTime, duration);
                jQuery('#appointment-time-end').val(endTime);
            }
        });

        // Start time change
        jQuery('#appointment-time-start').on('change', function() {
            const selectedService = jQuery('#appointment-service').find('option:selected');
            const duration = selectedService.data('duration') || 60;
            const startTime = jQuery(this).val();
            
            if (startTime) {
                const endTime = calculateEndTime(startTime, duration);
                jQuery('#appointment-time-end').val(endTime);
            }
        });

        // Form submission
        jQuery('#pcq-appointment-form').on('submit', function(e) {
            e.preventDefault();
            saveAppointment();
        });

        // Delete appointment
        jQuery('#pcq-delete-appointment').on('click', function() {
            if (confirm('Are you sure you want to delete this appointment?')) {
                deleteAppointment();
            }
        });
    }

    /**
     * Open appointment modal
     */
    function openAppointmentModal(appointmentId = null, selectedDate = null) {
        if (appointmentId) {
            // Load existing appointment
            loadAppointment(appointmentId);
            jQuery('#pcq-modal-title').text('Edit Appointment');
            jQuery('#pcq-delete-appointment').show();
        } else {
            // New appointment
            resetAppointmentForm();
            jQuery('#pcq-modal-title').text('Add New Appointment');
            jQuery('#pcq-delete-appointment').hide();
            
            // Load quotes for new appointments
            loadQuotesForAppointment();
            
            if (selectedDate) {
                jQuery('#appointment-date').val(selectedDate);
            }
        }
        
        jQuery('#pcq-appointment-modal').show();
    }

    /**
     * Close appointment modal
     */
    function closeAppointmentModal() {
        jQuery('#pcq-appointment-modal').hide();
        resetAppointmentForm();
    }

    /**
     * Reset appointment form
     */
    function resetAppointmentForm() {
        jQuery('#pcq-appointment-form')[0].reset();
        jQuery('#appointment-id').val('');
        jQuery('#appointment-quote').val(''); // Reset quote selector
        jQuery('.pcq-form-field').removeClass('error');
    }
    
    /**
     * Load quotes for appointment dropdown
     */
    function loadQuotesForAppointment() {
        jQuery.ajax({
            url: pcq_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pcq_get_quotes_for_appointment',
                nonce: pcq_admin_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    const quotes = response.data;
                    const $quoteSelect = jQuery('#appointment-quote');
                    
                    // Clear existing options except the first one
                    $quoteSelect.find('option:not(:first)').remove();
                    
                    if (quotes.length === 0) {
                        // Optionally update the placeholder text
                        $quoteSelect.find('option:first').text('-- No quotes available --');
                        return;
                    }
                    
                    // Add quote options
                    quotes.forEach(function(quote) {
                        $quoteSelect.append(
                            jQuery('<option></option>')
                                .val(quote.id)
                                .text(quote.display_text)
                                .data('quote', quote)
                        );
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading quotes:', error);
            }
        });
    }
    
    /**
     * Auto-fill form from selected quote
     */
    function autoFillFromQuote(quoteId) {
        const $selectedOption = jQuery('#appointment-quote option:selected');
        const quote = $selectedOption.data('quote');
        
        if (!quote) {
            return;
        }
        
        // Fill customer information
        jQuery('#customer-name').val(quote.customer_name || '');
        jQuery('#customer-email').val(quote.customer_email || '');
        jQuery('#customer-phone').val(quote.customer_phone || '');
        
        // Fill price
        jQuery('#appointment-price').val(quote.total_price || '');
        
        // Set default date to tomorrow if not already set
        const $dateField = jQuery('#appointment-date');
        if (!$dateField.val()) {
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            const tomorrowStr = tomorrow.toISOString().split('T')[0];
            $dateField.val(tomorrowStr);
        }
        
        // Set default start time to 9:00 AM if not already set
        const $startTime = jQuery('#appointment-time-start');
        if (!$startTime.val()) {
            $startTime.val('09:00');
        }
        
        // Fill notes with special requirements
        const existingNotes = jQuery('#appointment-notes').val();
        if (quote.special_requirements) {
            const newNotes = existingNotes ? 
                existingNotes + '\n\n' + quote.special_requirements : 
                quote.special_requirements;
            jQuery('#appointment-notes').val(newNotes);
        }
        
        // Try to match service by service_type (this will auto-calculate end time)
        matchServiceByType(quote.service_type);
        
        // If service was matched, trigger time calculation
        // Otherwise set default 2-hour duration
        const $endTime = jQuery('#appointment-time-end');
        if (!$endTime.val() && $startTime.val()) {
            const endTime = calculateEndTime($startTime.val(), 120); // Default 2 hours
            $endTime.val(endTime);
        }
        
        // Show success feedback
        showNotice('Quote information loaded successfully! Please verify date and time.', 'success');
    }
    
    /**
     * Match and select service by type from quote
     */
    function matchServiceByType(serviceType) {
        if (!serviceType) return;
        
        const $serviceSelect = jQuery('#appointment-service');
        const normalizedType = serviceType.toLowerCase().replace(/[\s-_]/g, '');
        
        // Try to find matching service option
        $serviceSelect.find('option').each(function() {
            const optionText = jQuery(this).text().toLowerCase().replace(/[\s-_]/g, '');
            
            // Check if service name contains the type or type contains service name
            if (optionText.includes(normalizedType) || normalizedType.includes(optionText)) {
                $serviceSelect.val(jQuery(this).val()).trigger('change');
                return false; // break loop
            }
        });
    }

    /**
     * Load appointment data
     */
    function loadAppointment(appointmentId) {
        jQuery.ajax({
            url: pcq_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pcq_get_appointment',
                nonce: pcq_admin_ajax.nonce,
                appointment_id: appointmentId
            },
            success: function(response) {
                if (response.success) {
                    const appointment = response.data;
                    
                    // Populate form fields
                    jQuery('#appointment-id').val(appointment.id);
                    jQuery('#appointment-service').val(appointment.service_id);
                    jQuery('#appointment-employee').val(appointment.employee_id);
                    jQuery('#appointment-date').val(appointment.service_date);
                    jQuery('#appointment-time-start').val(appointment.service_time_start);
                    jQuery('#appointment-time-end').val(appointment.service_time_end);
                    jQuery('#customer-name').val(appointment.customer_name);
                    jQuery('#customer-email').val(appointment.customer_email);
                    jQuery('#customer-phone').val(appointment.customer_phone);
                    jQuery('#appointment-price').val(appointment.price);
                    jQuery('#appointment-notes').val(appointment.notes);
                    jQuery('#appointment-status').val(appointment.status);
                } else {
                    alert('Failed to load appointment: ' + response.data);
                }
            },
            error: function() {
                alert('Failed to load appointment');
            }
        });
    }

    /**
     * Save appointment
     */
    function saveAppointment() {
        const formData = jQuery('#pcq-appointment-form').serialize();
        
        jQuery.ajax({
            url: pcq_admin_ajax.ajax_url,
            type: 'POST',
            data: formData + '&action=pcq_save_appointment&nonce=' + pcq_admin_ajax.nonce,
            success: function(response) {
                if (response.success) {
                    closeAppointmentModal();
                    
                    // Refresh calendar
                    if (window.pcqCalendar) {
                        window.pcqCalendar.refetchEvents();
                    }
                    
                    // Show success message
                    showNotice('Appointment saved successfully!', 'success');
                } else {
                    alert('Failed to save appointment: ' + response.data);
                }
            },
            error: function() {
                alert('Failed to save appointment');
            }
        });
    }

    /**
     * Delete appointment
     */
    function deleteAppointment() {
        const appointmentId = jQuery('#appointment-id').val();
        
        if (!appointmentId) {
            return;
        }
        
        jQuery.ajax({
            url: pcq_admin_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'pcq_delete_appointment',
                nonce: pcq_admin_ajax.nonce,
                appointment_id: appointmentId
            },
            success: function(response) {
                if (response.success) {
                    closeAppointmentModal();
                    
                    // Refresh calendar
                    if (window.pcqCalendar) {
                        window.pcqCalendar.refetchEvents();
                    }
                    
                    // Show success message
                    showNotice('Appointment deleted successfully!', 'success');
                } else {
                    alert('Failed to delete appointment: ' + response.data);
                }
            },
            error: function() {
                alert('Failed to delete appointment');
            }
        });
    }

    /**
     * Calculate end time based on start time and duration
     */
    function calculateEndTime(startTime, durationMinutes) {
        const [hours, minutes] = startTime.split(':').map(Number);
        const startDate = new Date();
        startDate.setHours(hours, minutes, 0, 0);
        
        const endDate = new Date(startDate.getTime() + (durationMinutes * 60000));
        
        const endHours = String(endDate.getHours()).padStart(2, '0');
        const endMinutes = String(endDate.getMinutes()).padStart(2, '0');
        
        return `${endHours}:${endMinutes}`;
    }

    /**
     * Show admin notice
     */
    function showNotice(message, type = 'info') {
        const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
        const notice = jQuery(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);
        
        jQuery('.wrap h1').after(notice);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            notice.fadeOut();
        }, 5000);
        
        // Handle dismiss button
        notice.find('.notice-dismiss').on('click', function() {
            notice.fadeOut();
        });
    }

})(jQuery);