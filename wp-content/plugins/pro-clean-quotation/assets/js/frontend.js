/**
 * Pro Clean Quotation - Frontend JavaScript
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Quote Form Handler Class
     */
    class PCQQuoteForm {
        constructor(formElement) {
            this.form = $(formElement);
            this.isCalculating = false;
            this.isSubmitting = false;
            this.calculationTimeout = null;
            
            console.log('[DEBUG] PCQQuoteForm initialized for form:', this.form.attr('id'));
            
            this.init();
        }

        /**
         * Initialize form
         */
        init() {
            this.bindEvents();
            this.initConditionalFields();
            this.setupRealTimeCalculation();
            this.initScrollIndicator();
        }

        /**
         * Bind form events
         */
        bindEvents() {
            // Form submission
            this.form.on('submit', (e) => this.handleSubmit(e));
            
            // Real-time calculation triggers
            this.form.on('input change', 'input[name="square_meters"], input[name="linear_meters"], select[name="building_height"], select[name="property_type"], select[name="surface_material"], input[name="service_type"]', 
                () => this.triggerCalculation()
            );
            
            // Service type change for conditional fields
            this.form.on('change', 'input[name="service_type"]', () => this.handleServiceTypeChange());
            
            // Character counter for special requirements
            this.form.on('input', 'textarea[name="special_requirements"]', (e) => this.updateCharacterCount(e));
            
            // Phone number formatting
            this.form.on('input', 'input[name="customer_phone"]', (e) => this.formatPhoneNumber(e));
            
            // Postal code formatting
            this.form.on('input', 'input[name="postal_code"]', (e) => this.formatPostalCode(e));
        }

        /**
         * Initialize conditional fields
         */
        initConditionalFields() {
            this.handleServiceTypeChange();
        }

        /**
         * Setup real-time calculation
         */
        setupRealTimeCalculation() {
            console.log('[DEBUG] setupRealTimeCalculation called');
            
            // Initial calculation if form has values
            if (this.hasRequiredCalculationFields()) {
                console.log('[DEBUG] Form has required fields, triggering initial calculation');
                this.triggerCalculation();
            } else {
                console.log('[DEBUG] Form does not have required fields for initial calculation');
            }
        }

        /**
         * Initialize scroll indicator for service cards
         */
        initScrollIndicator() {
            const radioGroup = this.form.find('.pcq-radio-group.pcq-scrollable');
            const scrollIndicatorLeft = this.form.find('.pcq-scroll-indicator.pcq-scroll-left');
            const scrollIndicatorRight = this.form.find('.pcq-scroll-indicator.pcq-scroll-right');
            
            if (radioGroup.length === 0) {
                return;
            }

            // Force animation restart by toggling class
            const restartAnimation = (element) => {
                if (element.length === 0) return;
                
                const svg = element.find('svg');
                if (svg.length > 0) {
                    // Force reflow to restart animation
                    svg.css('animation', 'none');
                    void svg[0].offsetHeight; // Trigger reflow
                    svg.css('animation', '');
                }
            };

            // Update scroll indicators based on scroll position
            const updateScrollIndicators = () => {
                const element = radioGroup[0];
                const scrollLeft = element.scrollLeft;
                const scrollWidth = element.scrollWidth;
                const clientWidth = element.clientWidth;
                const maxScroll = scrollWidth - clientWidth;
                
                // Show/hide left indicator
                if (scrollIndicatorLeft.length > 0) {
                    if (scrollLeft <= 10) {
                        scrollIndicatorLeft.addClass('hidden');
                    } else {
                        const wasHidden = scrollIndicatorLeft.hasClass('hidden');
                        scrollIndicatorLeft.removeClass('hidden');
                        if (wasHidden) {
                            restartAnimation(scrollIndicatorLeft);
                        }
                    }
                }
                
                // Show/hide right indicator
                if (scrollIndicatorRight.length > 0) {
                    if (scrollLeft >= maxScroll - 10) {
                        scrollIndicatorRight.addClass('hidden');
                    } else {
                        const wasHidden = scrollIndicatorRight.hasClass('hidden');
                        scrollIndicatorRight.removeClass('hidden');
                        if (wasHidden) {
                            restartAnimation(scrollIndicatorRight);
                        }
                    }
                }
            };

            // Bind scroll event
            radioGroup.on('scroll', updateScrollIndicators);

            // Bind resize event
            $(window).on('resize', updateScrollIndicators);

            // Initial check with animation restart
            setTimeout(() => {
                updateScrollIndicators();
                restartAnimation(scrollIndicatorLeft);
                restartAnimation(scrollIndicatorRight);
            }, 100);

            // Hide indicators temporarily on user interaction
            radioGroup.on('mousedown touchstart', () => {
                scrollIndicatorLeft.addClass('hidden');
                scrollIndicatorRight.addClass('hidden');
                
                // Re-check after interaction
                setTimeout(updateScrollIndicators, 1000);
            });
        }

        /**
         * Handle form submission
         */
        async handleSubmit(e) {
            e.preventDefault();
            
            if (this.isSubmitting) {
                return;
            }

            // Clear previous messages
            this.clearMessages();
            
            // Validate form
            if (!this.validateForm()) {
                return;
            }

            this.isSubmitting = true;
            this.setSubmitButtonState(true);

            try {
                const formData = this.getFormData();
                const response = await this.submitQuote(formData);
                
                if (response.success) {
                    this.showSuccessMessage(response.message);
                    this.handleSuccessfulSubmission(response.data);
                } else {
                    this.showErrorMessage(response.message);
                    if (response.errors) {
                        this.showFieldErrors(response.errors);
                    }
                }
            } catch (error) {
                console.error('Quote submission error:', error);
                this.showErrorMessage(pcq_ajax.strings.error);
            } finally {
                this.isSubmitting = false;
                this.setSubmitButtonState(false);
            }
        }

        /**
         * Trigger price calculation with debouncing
         */
        triggerCalculation() {
            console.log('[DEBUG] triggerCalculation called');
            
            const hasRequired = this.hasRequiredCalculationFields();
            console.log('[DEBUG] hasRequiredCalculationFields:', hasRequired);
            
            if (!hasRequired) {
                console.log('[DEBUG] Missing required fields, hiding price display');
                this.hidePriceDisplay();
                return;
            }

            console.log('[DEBUG] Required fields present, scheduling calculation');

            // Clear existing timeout
            if (this.calculationTimeout) {
                clearTimeout(this.calculationTimeout);
            }

            // Set new timeout for debounced calculation
            this.calculationTimeout = setTimeout(() => {
                console.log('[DEBUG] Debounce timeout completed, calling calculatePrice');
                this.calculatePrice();
            }, 500);
        }

        /**
         * Calculate price
         */
        async calculatePrice() {
            console.log('[DEBUG] calculatePrice called, isCalculating:', this.isCalculating);
            
            if (this.isCalculating) {
                console.log('[DEBUG] Already calculating, skipping');
                return;
            }

            this.isCalculating = true;
            this.showCalculatingState();
            console.log('[DEBUG] Showing calculating state');

            try {
                const formData = this.getCalculationData();
                console.log('[DEBUG] Calculation data:', formData);
                
                console.log('[DEBUG] Sending AJAX request to:', pcq_ajax.ajax_url);
                const response = await this.requestCalculation(formData);
                console.log('[DEBUG] AJAX response:', response);
                
                if (response.success) {
                    console.log('[DEBUG] Calculation successful, displaying price');
                    this.displayPrice(response.data);
                } else {
                    console.warn('[DEBUG] Calculation failed:', response.message);
                    this.hidePriceDisplay();
                }
            } catch (error) {
                console.error('[DEBUG] Price calculation error:', error);
                this.hidePriceDisplay();
            } finally {
                this.isCalculating = false;
                this.hideCalculatingState();
                console.log('[DEBUG] Calculation complete, isCalculating:', this.isCalculating);
            }
        }

        /**
         * Handle service type change
         */
        handleServiceTypeChange() {
            const serviceType = this.form.find('input[name="service_type"]:checked').val();
            const roofTypeSection = this.form.find('.pcq-roof-type');
            
            // Legacy roof_type handling (backward compatibility)
            if (serviceType === 'roof' || serviceType === 'both') {
                roofTypeSection.show();
            } else {
                roofTypeSection.hide();
                this.form.find('select[name="roof_type"]').val('');
            }
            
            // Handle dynamic custom fields
            this.renderCustomFields(serviceType);
            
            this.triggerCalculation();
        }

        /**
         * Render custom fields for selected service
         */
        renderCustomFields(serviceId) {
            // Find the Surface Details form row
            const surfaceDetailsRow = this.form.find('.pcq-surface-details .pcq-form-row');
            
            // Remove any previously added custom fields
            surfaceDetailsRow.find('.pcq-custom-field-wrapper').remove();
            
            if (!serviceId || !window.pcqServicesData || !window.pcqServicesData[serviceId]) {
                return;
            }
            
            const customFields = window.pcqServicesData[serviceId];
            
            if (!customFields || customFields.length === 0) {
                return;
            }
            
            // Add each custom field as a form field in the row
            customFields.forEach(field => {
                const fieldHtml = this.generateCustomFieldHtml(field);
                surfaceDetailsRow.append(fieldHtml);
            });
            
            // Trigger validation setup for new fields
            this.setupCustomFieldValidation();
        }

        /**
         * Generate HTML for a custom field
         */
        generateCustomFieldHtml(field) {
            const fieldId = `custom_field_${field.id}`;
            const isRequired = field.required ? 'required' : '';
            const requiredMark = field.required ? '<span class="required">*</span>' : '';
            
            let optionsHtml = '';
            
            if (field.type === 'select') {
                optionsHtml = `<select name="custom_fields[${field.id}]" id="${fieldId}" class="pcq-calc-trigger" ${isRequired}>
                    <option value="">${pcq_ajax.strings.select_option || 'Select an option'}</option>`;
                
                field.options.forEach(option => {
                    const priceText = option.price_modifier && parseFloat(option.price_modifier) !== 0 
                        ? ` (+€${parseFloat(option.price_modifier).toFixed(2)})` 
                        : '';
                    optionsHtml += `<option value="${this.escapeHtml(option.value)}" data-price-modifier="${option.price_modifier || 0}">${this.escapeHtml(option.label)}${priceText}</option>`;
                });
                
                optionsHtml += `</select>`;
            } else if (field.type === 'radio') {
                optionsHtml = '<div class="pcq-radio-options">';
                
                field.options.forEach((option, index) => {
                    const optionId = `${fieldId}_${index}`;
                    const priceText = option.price_modifier && parseFloat(option.price_modifier) !== 0 
                        ? ` <span class="price-modifier">(+€${parseFloat(option.price_modifier).toFixed(2)})</span>` 
                        : '';
                    
                    optionsHtml += `
                        <label class="pcq-radio-label">
                            <input type="radio" 
                                   name="custom_fields[${field.id}]" 
                                   id="${optionId}"
                                   value="${this.escapeHtml(option.value)}" 
                                   data-price-modifier="${option.price_modifier || 0}"
                                   class="pcq-calc-trigger"
                                   ${isRequired}>
                            <span class="pcq-radio-text">${this.escapeHtml(option.label)}${priceText}</span>
                        </label>`;
                });
                
                optionsHtml += '</div>';
            }
            
            return `
                <div class="pcq-form-field pcq-custom-field-wrapper" data-field-id="${field.id}">
                    <label for="${fieldId}">${this.escapeHtml(field.label)} ${requiredMark}</label>
                    ${optionsHtml}
                </div>`;
        }

        /**
         * Setup validation for custom fields
         */
        setupCustomFieldValidation() {
            // Add change event listeners to custom fields for live calculation
            this.form.find('.pcq-custom-field-wrapper select, .pcq-custom-field-wrapper input[type="radio"]').off('change.customfields').on('change.customfields', () => {
                this.triggerCalculation();
            });
        }

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }

        /**
         * Update character count for textarea
         */
        updateCharacterCount(e) {
            const textarea = $(e.target);
            const maxLength = parseInt(textarea.attr('maxlength')) || 500;
            const currentLength = textarea.val().length;
            
            let counter = textarea.siblings('.character-count');
            if (counter.length === 0) {
                counter = $('<small class="character-count"></small>');
                textarea.after(counter);
            }
            
            counter.text(`${currentLength}/${maxLength} characters`);
            
            if (currentLength > maxLength * 0.9) {
                counter.addClass('warning');
            } else {
                counter.removeClass('warning');
            }
        }

        /**
         * Format phone number
         */
        formatPhoneNumber(e) {
            const input = $(e.target);
            let value = input.val();
            
            // Allow: digits, spaces, plus sign for Spanish format
            value = value.replace(/[^0-9+\s]/g, '');
            
            // Trim excessive spaces
            value = value.replace(/\s+/g, ' ');
            
            input.val(value);
        }

        /**
         * Format postal code
         */
        formatPostalCode(e) {
            const input = $(e.target);
            let value = input.val().replace(/[^0-9]/g, '');
            
            // Spanish postal code: 5 digits only
            if (value.length > 5) {
                value = value.substring(0, 5);
            }
            
            input.val(value);
        }

        /**
         * Validate form
         */
        validateForm() {
            let isValid = true;
            const errors = {};

            // Clear existing errors
            this.clearFieldErrors();

            // Required fields validation
            this.form.find('[required]').each((index, element) => {
                const field = $(element);
                const name = field.attr('name');
                const value = field.val().trim();

                if (!value) {
                    errors[name] = pcq_ajax.strings.required_field;
                    isValid = false;
                }
            });

            // Email validation
            const email = this.form.find('input[name="customer_email"]').val();
            if (email && !this.isValidEmail(email)) {
                errors.customer_email = pcq_ajax.strings.invalid_email;
                isValid = false;
            }

            // Phone validation
            const phone = this.form.find('input[name="customer_phone"]').val();
            if (phone && !this.isValidPhone(phone)) {
                errors.customer_phone = pcq_ajax.strings.invalid_phone;
                isValid = false;
            }

            // Postal code validation
            const postalCode = this.form.find('input[name="postal_code"]').val();
            if (postalCode && !this.isValidPostalCode(postalCode)) {
                errors.postal_code = 'Please enter a valid Spanish postal code (5 digits, e.g., 28001, 29600). Valid range: 01001-52999';
                isValid = false;
            }

            // Measurements validation
            const squareMeters = parseFloat(this.form.find('input[name="square_meters"]').val());
            if (squareMeters && (squareMeters < 10 || squareMeters > 10000)) {
                errors.square_meters = 'Square meters must be between 10 and 10,000';
                isValid = false;
            }

            const linearMeters = parseFloat(this.form.find('input[name="linear_meters"]').val());
            if (linearMeters && (linearMeters < 5 || linearMeters > 5000)) {
                errors.linear_meters = 'Linear meters must be between 5 and 5,000';
                isValid = false;
            }

            if (!isValid) {
                this.showFieldErrors(errors);
            }

            return isValid;
        }

        /**
         * Check if required calculation fields are filled
         */
        hasRequiredCalculationFields() {
            const serviceType = this.form.find('input[name="service_type"]:checked').val();
            const squareMeters = this.form.find('input[name="square_meters"]').val();
            
            console.log('[DEBUG] hasRequiredCalculationFields check:');
            console.log('  - serviceType:', serviceType);
            console.log('  - squareMeters:', squareMeters);
            console.log('  - squareMeters >= 10:', squareMeters && parseFloat(squareMeters) >= 10);
            
            return serviceType && squareMeters && parseFloat(squareMeters) >= 10;
        }

        /**
         * Get form data for submission
         */
        getFormData() {
            const data = {};
            
            this.form.find('input, select, textarea').each((index, element) => {
                const field = $(element);
                const name = field.attr('name');
                
                if (name) {
                    if (field.attr('type') === 'checkbox') {
                        data[name] = field.is(':checked') ? '1' : '';
                    } else if (field.attr('type') === 'radio') {
                        if (field.is(':checked')) {
                            data[name] = field.val();
                        }
                    } else {
                        data[name] = field.val();
                    }
                }
            });
            
            data.action = 'pcq_submit_quote';
            data.nonce = pcq_ajax.nonce;
            
            return data;
        }

        /**
         * Get calculation data
         */
        getCalculationData() {
            const data = {
                action: 'pcq_calculate_quote',
                nonce: pcq_ajax.nonce,
                service_type: this.form.find('input[name="service_type"]:checked').val(),
                square_meters: this.form.find('input[name="square_meters"]').val(),
                linear_meters: this.form.find('input[name="linear_meters"]').val(),
                building_height: this.form.find('select[name="building_height"]').val(),
                property_type: this.form.find('select[name="property_type"]').val(),
                surface_material: this.form.find('select[name="surface_material"]').val(),
                roof_type: this.form.find('select[name="roof_type"]').val()
            };
            
            // Include custom field data
            data.custom_fields = this.getCustomFieldData();
            
            return data;
        }

        /**
         * Get custom field data
         */
        getCustomFieldData() {
            const customFieldData = {};
            
            this.form.find('.pcq-custom-field-wrapper').each((index, element) => {
                const fieldElement = $(element);
                const fieldId = fieldElement.data('field-id');
                
                // Get value from select or radio
                const selectValue = fieldElement.find('select').val();
                const radioValue = fieldElement.find('input[type="radio"]:checked').val();
                
                const value = selectValue || radioValue || '';
                
                if (value) {
                    // Get price modifier from selected option
                    let priceModifier = 0;
                    if (selectValue) {
                        priceModifier = fieldElement.find('select option:selected').data('price-modifier') || 0;
                    } else if (radioValue) {
                        priceModifier = fieldElement.find('input[type="radio"]:checked').data('price-modifier') || 0;
                    }
                    
                    customFieldData[fieldId] = {
                        value: value,
                        price_modifier: parseFloat(priceModifier) || 0
                    };
                }
            });
            
            return customFieldData;
        }

        /**
         * Submit quote via AJAX
         */
        async submitQuote(data) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: pcq_ajax.ajax_url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    timeout: 30000,
                    success: resolve,
                    error: (xhr, status, error) => {
                        reject(new Error(`AJAX Error: ${status} - ${error}`));
                    }
                });
            });
        }

        /**
         * Request price calculation via AJAX
         */
        async requestCalculation(data) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: pcq_ajax.ajax_url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    timeout: 10000,
                    success: resolve,
                    error: (xhr, status, error) => {
                        reject(new Error(`Calculation Error: ${status} - ${error}`));
                    }
                });
            });
        }

        /**
         * Display calculated price
         */
        displayPrice(data) {
            console.log('[DEBUG] displayPrice called with data:', data);
            
            const priceDisplay = this.form.find('.pcq-price-display');
            console.log('[DEBUG] priceDisplay element found:', priceDisplay.length);
            
            const breakdown = priceDisplay.find('.pcq-price-breakdown');
            console.log('[DEBUG] breakdown element found:', breakdown.length);
            
            let html = '';
            
            if (data.breakdown) {
                Object.entries(data.breakdown).forEach(([key, item]) => {
                    const isTotal = key === 'total';
                    const cssClass = isTotal ? 'pcq-price-item pcq-price-total' : 'pcq-price-item';
                    
                    html += `
                        <div class="${cssClass}">
                            <span class="pcq-price-label">${item.label}</span>
                            <span class="pcq-price-amount">${data.currency}${this.formatPrice(item.amount)}</span>
                        </div>
                    `;
                });
            }
            
            html += `
                <div class="pcq-price-validity">
                    <small>Valid until: ${this.formatDate(data.valid_until)}</small>
                </div>
            `;
            
            console.log('[DEBUG] Generated HTML length:', html.length);
            breakdown.html(html);
            console.log('[DEBUG] Calling priceDisplay.show()');
            priceDisplay.show();
            console.log('[DEBUG] priceDisplay visibility:', priceDisplay.is(':visible'));
        }

        /**
         * Hide price display
         */
        hidePriceDisplay() {
            this.form.find('.pcq-price-display').hide();
        }

        /**
         * Show calculating state
         */
        showCalculatingState() {
            const priceDisplay = this.form.find('.pcq-price-display');
            const breakdown = priceDisplay.find('.pcq-price-breakdown');
            
            breakdown.html(`
                <div class="pcq-calculating">
                    <span>${pcq_ajax.strings.calculating}</span>
                </div>
            `);
            
            priceDisplay.show();
        }

        /**
         * Hide calculating state
         */
        hideCalculatingState() {
            // This is handled by displayPrice or hidePriceDisplay
        }

        /**
         * Set submit button state
         */
        setSubmitButtonState(loading) {
            const button = this.form.find('.pcq-submit-btn');
            const textSpan = button.find('.pcq-btn-text');
            const loadingSpan = button.find('.pcq-btn-loading');
            
            if (loading) {
                button.prop('disabled', true);
                textSpan.hide();
                loadingSpan.show();
            } else {
                button.prop('disabled', false);
                textSpan.show();
                loadingSpan.hide();
            }
        }

        /**
         * Show success message
         */
        showSuccessMessage(message) {
            this.showMessage(message, 'success');
        }

        /**
         * Show error message
         */
        showErrorMessage(message) {
            this.showMessage(message, 'error');
        }

        /**
         * Show message
         */
        showMessage(message, type) {
            const messagesContainer = this.form.find('.pcq-form-messages');
            const messageHtml = `<div class="pcq-message ${type}">${message}</div>`;
            
            messagesContainer.html(messageHtml);
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: messagesContainer.offset().top - 100
            }, 500);
        }

        /**
         * Clear messages
         */
        clearMessages() {
            this.form.find('.pcq-form-messages').empty();
        }

        /**
         * Show field errors
         */
        showFieldErrors(errors) {
            Object.entries(errors).forEach(([fieldName, errorMessage]) => {
                const field = this.form.find(`[name="${fieldName}"]`);
                const fieldContainer = field.closest('.pcq-form-field');
                
                fieldContainer.addClass('has-error');
                
                // Remove existing error message
                fieldContainer.find('.pcq-field-error').remove();
                
                // Add new error message
                const errorElement = $(`<span class="pcq-field-error">${errorMessage}</span>`);
                field.after(errorElement);
            });
        }

        /**
         * Clear field errors
         */
        clearFieldErrors() {
            this.form.find('.pcq-form-field').removeClass('has-error');
            this.form.find('.pcq-field-error').remove();
        }

        /**
         * Handle successful submission
         */
        handleSuccessfulSubmission(data) {
            // Hide the form
            this.form.hide();
            
            // Show success information
            const successHtml = `
                <div class="pcq-success-container">
                    <h3>Quote Generated Successfully!</h3>
                    <div class="pcq-quote-summary">
                        <p><strong>Quote Number:</strong> ${data.quote_number}</p>
                        <p><strong>Total Price:</strong> €${this.formatPrice(data.total_price)}</p>
                        <p><strong>Valid Until:</strong> ${this.formatDate(data.valid_until)}</p>
                    </div>
                    ${data.booking_url ? `<a href="${data.booking_url}" class="pcq-btn-primary pcq-btn-large">Book This Service</a>` : ''}
                    <p><small>A confirmation email has been sent to your email address.</small></p>
                </div>
            `;
            
            this.form.after(successHtml);
        }

        /**
         * Validation helpers
         */
        isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        isValidPhone(phone) {
            // Remove all spaces for validation
            const cleanPhone = phone.replace(/\s/g, '');
            
            // Accept Spanish phone formats:
            // +34612345678, 0034612345678, 612345678
            // Mobile: starts with 6, 7, 8, or 9 (after country code)
            // Also accept with spaces: +34 612 345 678, 612 345 678, etc.
            const phoneRegex = /^(\+34|0034)?[6-9][0-9]{8}$/;
            return phoneRegex.test(cleanPhone);
        }

        isValidPostalCode(postalCode) {
            // Spanish postal code format: 5 digits (01001-52999)
            // Province codes: 01-52, Locality: 000-999
            // Remove any spaces and validate
            const cleanPostal = postalCode.replace(/\s/g, '');
            const postalRegex = /^(0[1-9]|[1-4][0-9]|5[0-2])[0-9]{3}$/;
            return postalRegex.test(cleanPostal);
        }

        /**
         * Format price for display
         */
        formatPrice(amount) {
            return parseFloat(amount).toFixed(2);
        }

        /**
         * Format date for display
         */
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
    }

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        // Load services custom fields data from JSON
        const servicesDataElement = document.getElementById('pcq-services-custom-fields');
        if (servicesDataElement) {
            try {
                window.pcqServicesData = JSON.parse(servicesDataElement.textContent);
                console.log('Loaded services custom fields data:', window.pcqServicesData);
            } catch (e) {
                console.error('Failed to parse services custom fields data:', e);
                window.pcqServicesData = {};
            }
        } else {
            window.pcqServicesData = {};
        }
        
        // Initialize quote forms
        $('.pcq-quote-form').each(function() {
            new PCQQuoteForm(this);
        });
        
        // Add smooth scrolling for anchor links
        $('a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            const target = $(this.getAttribute('href'));
            if (target.length) {
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
    });

})(jQuery);