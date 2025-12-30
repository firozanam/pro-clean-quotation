/**
 * Booking Calendar JavaScript
 * 
 * Handles the booking calendar interface with time slot selection
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Booking Calendar Manager
     */
    const BookingCalendar = {
        
        /**
         * Initialize
         */
        init: function() {
            this.cacheDOM();
            this.bindEvents();
            this.initDatePicker();
        },

        /**
         * Cache DOM elements
         */
        cacheDOM: function() {
            this.$calendarContainer = $('#pcq-booking-calendar');
            this.$slotsContainer = $('.pcq-time-slots-container');
            this.$availableSlots = $('#pcq-available-slots');
            this.$bookingForm = $('#pcq-booking-form');
            this.$selectedDate = $('#selected_date');
            this.$selectedTimeStart = $('#selected_time_start');
            this.$selectedTimeEnd = $('#selected_time_end');
            this.$selectedDatetime = $('#pcq-selected-datetime');
            this.$changeDatetimeBtn = $('#pcq-change-datetime');
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            const self = this;
            
            // Change datetime button
            this.$changeDatetimeBtn.on('click', function(e) {
                e.preventDefault();
                self.resetSelection();
            });

            // Form submission
            this.$bookingForm.on('submit', function(e) {
                e.preventDefault();
                self.submitBooking();
            });
        },

        /**
         * Initialize date picker
         */
        initDatePicker: function() {
            if (!this.$calendarContainer.length) return;

            const self = this;
            const today = new Date();
            const minDate = new Date(today);
            minDate.setDate(today.getDate() + 1); // Minimum 1 day lead time

            const maxDate = new Date(today);
            maxDate.setDate(today.getDate() + 90); // Maximum 90 days

            // Simple date selection (using native date input as fallback)
            const dateInput = $('<input type="date" id="pcq-date-input" />')
                .attr('min', this.formatDate(minDate))
                .attr('max', this.formatDate(maxDate))
                .addClass('pcq-date-input');

            this.$calendarContainer.html(dateInput);

            dateInput.on('change', function() {
                const selectedDate = $(this).val();
                if (selectedDate) {
                    self.loadAvailableSlots(selectedDate);
                }
            });
        },

        /**
         * Load available time slots for selected date
         */
        loadAvailableSlots: function(date) {
            const self = this;
            
            // Show loading state
            this.$availableSlots.html('<div class="pcq-loading">Loading available slots...</div>');
            this.$slotsContainer.show();

            // Get service duration estimate
            const serviceDuration = this.estimateServiceDuration();
            const serviceType = this.getServiceType();

            $.ajax({
                url: pcq_ajax.ajax_url,
                type: 'GET',
                data: {
                    action: 'pcq_get_available_slots',
                    date: date,
                    service_duration: serviceDuration,
                    service_type: serviceType,
                    nonce: pcq_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        self.renderTimeSlots(response.data.available_slots, date);
                    } else {
                        self.$availableSlots.html('<div class="pcq-error">' + response.message + '</div>');
                    }
                },
                error: function() {
                    self.$availableSlots.html('<div class="pcq-error">Failed to load available slots. Please try again.</div>');
                }
            });
        },

        /**
         * Estimate service duration based on quote details
         */
        estimateServiceDuration: function() {
            const $container = $('.pcq-booking-form-container');
            const squareMeters = parseFloat($container.data('square-meters')) || 0;
            const serviceType = $container.data('service-type') || 'facade';
            
            // Duration estimation logic
            // Base: 50 sqm = 2 hours
            // Additional: 25 sqm = 1 hour
            let baseDuration = 2;
            
            if (squareMeters > 50) {
                const additionalSqm = squareMeters - 50;
                const additionalHours = Math.ceil(additionalSqm / 25);
                baseDuration += additionalHours;
            }

            // Service type multiplier
            if (serviceType === 'both') {
                baseDuration *= 1.5;
            } else if (serviceType === 'roof') {
                baseDuration *= 1.2;
            }

            // Round to nearest half-hour slots (2, 2.5, 3, 3.5, 4, etc.)
            // But return full hours for slot calculation
            const roundedDuration = Math.ceil(baseDuration);

            // Cap at 8 hours (full day)
            return Math.min(roundedDuration, 8);
        },

        /**
         * Get service type from quote
         */
        getServiceType: function() {
            return $('.pcq-booking-form-container').data('service-type') || 'facade';
        },

        /**
         * Render time slots
         */
        renderTimeSlots: function(slots, date) {
            const self = this;
            let html = '<div class="pcq-slots-grid">';

            if (!slots || slots.length === 0) {
                html += '<p class="pcq-no-slots">No available slots for this date. Please select another date.</p>';
            } else {
                slots.forEach(function(slot) {
                    const isAvailable = slot.available;
                    const slotClass = isAvailable ? 'pcq-slot-available' : 'pcq-slot-unavailable';
                    const timeRange = slot.start_time + ' - ' + slot.end_time;

                    html += '<button type="button" class="pcq-time-slot ' + slotClass + '" ';
                    
                    if (isAvailable) {
                        html += 'data-date="' + date + '" ';
                        html += 'data-start="' + slot.start_time + '" ';
                        html += 'data-end="' + slot.end_time + '"';
                    } else {
                        html += 'disabled title="' + (slot.reason || 'Not available') + '"';
                    }
                    
                    html += '>' + timeRange + '</button>';
                });
            }

            html += '</div>';
            this.$availableSlots.html(html);

            // Bind click events to available slots
            $('.pcq-slot-available').on('click', function() {
                self.selectTimeSlot($(this));
            });
        },

        /**
         * Select time slot
         */
        selectTimeSlot: function($slot) {
            const date = $slot.data('date');
            const startTime = $slot.data('start');
            const endTime = $slot.data('end');

            // Update hidden fields
            this.$selectedDate.val(date);
            this.$selectedTimeStart.val(startTime);
            this.$selectedTimeEnd.val(endTime);

            // Format datetime display
            const dateObj = new Date(date + 'T00:00:00');
            const formattedDate = dateObj.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });

            this.$selectedDatetime.html(
                '<strong>' + formattedDate + '</strong><br>' +
                'Time: <strong>' + startTime + ' - ' + endTime + '</strong>'
            );

            // Hide calendar and slots, show confirmation form
            this.$calendarContainer.parent().hide();
            this.$slotsContainer.hide();
            this.$bookingForm.show();

            // Scroll to form
            $('html, body').animate({
                scrollTop: this.$bookingForm.offset().top - 100
            }, 500);
        },

        /**
         * Reset selection
         */
        resetSelection: function() {
            this.$selectedDate.val('');
            this.$selectedTimeStart.val('');
            this.$selectedTimeEnd.val('');
            this.$selectedDatetime.html('');
            
            this.$bookingForm.hide();
            this.$calendarContainer.parent().show();
            this.$slotsContainer.show();
        },

        /**
         * Submit booking
         */
        submitBooking: function() {
            const self = this;
            const $submitBtn = this.$bookingForm.find('button[type="submit"]');
            const originalText = $submitBtn.text();

            // Disable button and show loading
            $submitBtn.prop('disabled', true).text('Processing...');

            $.ajax({
                url: pcq_ajax.ajax_url,
                type: 'POST',
                data: this.$bookingForm.serialize() + '&action=pcq_create_booking',
                success: function(response) {
                    if (response.success) {
                        // Redirect to confirmation page
                        const confirmUrl = self.buildConfirmationUrl(response.data);
                        window.location.href = confirmUrl;
                    } else {
                        alert('Error: ' + response.message);
                        $submitBtn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    alert('Failed to create booking. Please try again.');
                    $submitBtn.prop('disabled', false).text(originalText);
                }
            });
        },

        /**
         * Build confirmation page URL
         */
        buildConfirmationUrl: function(data) {
            const baseUrl = window.location.origin + '/booking-confirmation/';
            const params = new URLSearchParams({
                booking_id: data.booking_id || '',
                booking_number: data.booking_number || '',
                service_date: this.$selectedDate.val(),
                service_time: this.$selectedTimeStart.val() + ' - ' + this.$selectedTimeEnd.val(),
                total_amount: data.total_amount || 0,
                deposit_required: data.deposit_required || false,
                deposit_amount: data.deposit_amount || 0
            });

            return baseUrl + '?' + params.toString();
        },

        /**
         * Format date as YYYY-MM-DD
         */
        formatDate: function(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return year + '-' + month + '-' + day;
        }
    };

    // Initialize when DOM is ready
    $(document).ready(function() {
        if ($('.pcq-booking-form-container').length) {
            BookingCalendar.init();
        }
    });

})(jQuery);
