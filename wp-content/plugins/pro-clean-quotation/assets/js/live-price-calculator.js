/**
 * Live Price Calculator
 * Real-time quote calculation with debouncing
 *
 * @package ProClean\Quotation
 * @version 2.1.0
 *
 * Pricing Formula:
 *   Subtotal = base_rate + (area_sqm × rate_per_sqm) + (linear_meters × rate_per_linear_meter)
 *   VAT Amount = Subtotal × vat_rate
 *   Total Estimate = Subtotal + VAT Amount
 */

(function($) {
    'use strict';

    const LivePriceCalculator = {
        
        /**
         * Debounce timer
         */
        debounceTimer: null,
        
        /**
         * Debounce delay in milliseconds
         */
        debounceDelay: 500,
        
        /**
         * Current calculation request
         */
        currentRequest: null,
        
        /**
         * Default pricing values (can be overridden from server)
         */
        defaults: {
            base_rate: 20.00,
            rate_per_sqm: 20.00,
            rate_per_linear_meter: 5.00,
            vat_rate: 21
        },
        
        /**
         * Initialize the calculator
         */
        init: function() {
            this.bindEvents();
            this.loadPricingDefaults();
        },
        
        /**
         * Load pricing defaults from data attributes or server
         */
        loadPricingDefaults: function() {
            // Try to get pricing from data attributes
            const $form = $('.pcq-quote-form');
            if ($form.length) {
                this.defaults.base_rate = parseFloat($form.data('base-rate')) || 20.00;
                this.defaults.rate_per_sqm = parseFloat($form.data('rate-per-sqm')) || 20.00;
                this.defaults.rate_per_linear_meter = parseFloat($form.data('rate-per-linear-meter')) || 5.00;
                this.defaults.vat_rate = parseFloat($form.data('vat-rate')) || 21;
            }
        },
        
        /**
         * Bind form field events
         */
        bindEvents: function() {
            const self = this;
            
            // Bind to all calculation trigger fields
            $('.pcq-calc-trigger').on('input change', function() {
                self.debouncedCalculate();
            });
            
            // Service type radio buttons
            $('input[name="service_type"]').on('change', function() {
                self.toggleRoofTypeField();
                self.debouncedCalculate();
            });
            
            // Service selection dropdown (if using service selector)
            $('select[name="service_id"]').on('change', function() {
                self.updatePricingFromService($(this).val());
                self.debouncedCalculate();
            });
            
            // Initial check for roof type field
            self.toggleRoofTypeField();
        },
        
        /**
         * Update pricing defaults based on selected service
         */
        updatePricingFromService: function(serviceId) {
            if (!serviceId) return;
            
            const self = this;
            
            // Fetch service-specific pricing
            $.ajax({
                url: pcq_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pcq_get_service_pricing',
                    nonce: pcq_ajax.nonce,
                    service_id: serviceId
                },
                success: function(response) {
                    if (response.success && response.data) {
                        self.defaults.base_rate = parseFloat(response.data.base_rate) || 20.00;
                        self.defaults.rate_per_sqm = parseFloat(response.data.rate_per_sqm) || 20.00;
                        self.defaults.rate_per_linear_meter = parseFloat(response.data.rate_per_linear_meter) || 5.00;
                    }
                }
            });
        },
        
        /**
         * Toggle roof type field visibility
         */
        toggleRoofTypeField: function() {
            const serviceType = $('input[name="service_type"]:checked').val();
            const $roofTypeField = $('.pcq-roof-type-field');
            
            if (serviceType === 'roof' || serviceType === 'both') {
                $roofTypeField.slideDown(300);
            } else {
                $roofTypeField.slideUp(300);
                $roofTypeField.find('select').val('');
            }
        },
        
        /**
         * Debounced calculation
         */
        debouncedCalculate: function() {
            const self = this;
            
            // Clear existing timer
            if (this.debounceTimer) {
                clearTimeout(this.debounceTimer);
            }
            
            // Cancel any ongoing request
            if (this.currentRequest) {
                this.currentRequest.abort();
                this.currentRequest = null;
            }
            
            // Set new timer
            this.debounceTimer = setTimeout(function() {
                self.calculate();
            }, this.debounceDelay);
        },
        
        /**
         * Client-side quote calculation (for instant feedback)
         * Uses the formula: Subtotal = base_rate + (area_sqm × rate_per_sqm) + (linear_meters × rate_per_linear_meter)
         */
        calculateClientSide: function(formData) {
            const area_sqm = parseFloat(formData.square_meters) || 0;
            const linear_meters = parseFloat(formData.linear_meters) || 0;
            
            // Validate minimum area
            if (area_sqm <= 0) {
                return null;
            }
            
            const base_rate = this.defaults.base_rate;
            const rate_per_sqm = this.defaults.rate_per_sqm;
            const rate_per_linear_meter = this.defaults.rate_per_linear_meter;
            const vat_rate = this.defaults.vat_rate / 100;  // Convert percentage to decimal
            
            // Calculate using the new formula
            const sizeCost = area_sqm * rate_per_sqm;
            const linearCost = linear_meters * rate_per_linear_meter;
            const subtotal = base_rate + sizeCost + linearCost;
            const vatAmount = subtotal * vat_rate;
            const totalEstimate = subtotal + vatAmount;
            
            return {
                base_rate: base_rate.toFixed(2),
                size_cost: sizeCost.toFixed(2),
                linear_cost: linearCost.toFixed(2),
                subtotal: subtotal.toFixed(2),
                vat: vatAmount.toFixed(2),
                total: totalEstimate.toFixed(2),
                vat_rate: this.defaults.vat_rate,
                area_sqm: area_sqm,
                linear_meters: linear_meters,
                rate_per_sqm: rate_per_sqm,
                rate_per_linear_meter: rate_per_linear_meter
            };
        },
        
        /**
         * Calculate quote
         */
        calculate: function() {
            const self = this;
            
            // Get form data
            const formData = this.getFormData();
            
            // Validate required fields
            if (!this.validateFormData(formData)) {
                this.hidePriceDisplay();
                return;
            }
            
            // Show loading state
            this.showLoading();
            
            // Make AJAX request
            this.currentRequest = $.ajax({
                url: pcq_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'pcq_calculate_quote',
                    nonce: pcq_ajax.nonce,
                    ...formData
                },
                success: function(response) {
                    self.currentRequest = null;
                    
                    if (response.success) {
                        self.displayPrice(response.data);
                    } else {
                        // Handle both response formats: {message: ...} and {data: {message: ...}}
                        var errorMsg = response.message || (response.data && response.data.message) || pcq_ajax.strings.error;
                        self.showError(errorMsg);
                    }
                },
                error: function(xhr, status) {
                    self.currentRequest = null;
                    
                    if (status !== 'abort') {
                        self.showError(pcq_ajax.strings.error);
                    }
                }
            });
        },
        
        /**
         * Get form data
         */
        getFormData: function() {
            return {
                service_type: $('input[name="service_type"]:checked').val() || '',
                service_id: $('select[name="service_id"]').val() || '',
                square_meters: $('input[name="square_meters"]').val() || '',
                linear_meters: $('input[name="linear_meters"]').val() || '',
                building_height: $('select[name="building_height"]').val() || '',
                property_type: $('select[name="property_type"]').val() || '',
                surface_material: $('select[name="surface_material"]').val() || '',
                roof_type: $('select[name="roof_type"]').val() || ''
            };
        },
        
        /**
         * Validate form data
         */
        validateFormData: function(data) {
            // Check required fields
            if (!data.service_type || !data.square_meters || !data.building_height || 
                !data.property_type || !data.surface_material) {
                return false;
            }
            
            // Validate numeric values - minimum area must be > 0
            const sqm = parseFloat(data.square_meters);
            if (isNaN(sqm) || sqm <= 0) {
                this.showError(pcq_ajax.strings.invalid_area || 'Area must be greater than 0');
                return false;
            }
            
            // Maximum validation (reasonable upper limit)
            if (sqm > 100000) {
                this.showError(pcq_ajax.strings.area_too_large || 'Area exceeds maximum limit');
                return false;
            }
            
            return true;
        },
        
        /**
         * Show loading state
         */
        showLoading: function() {
            const $display = $('.pcq-price-display');
            const $loading = $display.find('.pcq-price-loading');
            const $result = $display.find('.pcq-price-result');
            
            $display.slideDown(400);
            $loading.show();
            $result.hide();
        },
        
        /**
         * Display price breakdown with new labels
         */
        displayPrice: function(data) {
            const self = this;
            const $display = $('.pcq-price-display');
            const $loading = $display.find('.pcq-price-loading');
            const $result = $display.find('.pcq-price-result');
            
            // Build price breakdown HTML
            let html = '<div class="pcq-price-items">';
            
            // Add each line item from breakdown object
            if (data.breakdown) {
                // Convert breakdown object to array if needed
                const breakdownArray = Array.isArray(data.breakdown) 
                    ? data.breakdown 
                    : Object.values(data.breakdown);
                
                breakdownArray.forEach(function(item) {
                    if (item && item.label && typeof item.amount !== 'undefined') {
                        const labelLower = item.label.toLowerCase();
                        const isTotal = labelLower.includes('total') && !labelLower.includes('vat');
                        const isVat = labelLower.includes('vat');
                        const isSubtotal = labelLower.includes('subtotal');
                        
                        let itemClass = 'pcq-price-item';
                        if (isTotal) itemClass = 'pcq-price-total';
                        else if (isVat) itemClass = 'pcq-price-vat';
                        else if (isSubtotal) itemClass = 'pcq-price-subtotal';
                        
                        // Format negative amounts (discounts)
                        const amount = parseFloat(item.amount);
                        const amountStr = amount < 0 ? '-€' + self.formatPrice(Math.abs(amount)) : '€' + self.formatPrice(amount);
                        
                        html += '<div class="' + itemClass + '">';
                        html += '<span class="pcq-price-label">' + self.escapeHtml(item.label) + '</span>';
                        html += '<span class="pcq-price-amount">' + amountStr + '</span>';
                        html += '</div>';
                    }
                });
            }
            
            html += '</div>';
            
            // Add total price highlight (if different from breakdown total)
            if (data.total || data.total_price || data.final_total) {
                const totalAmount = data.total || data.total_price || data.final_total;
                html += '<div class="pcq-price-final">';
                html += '<span class="pcq-price-final-label">' + (pcq_ajax.strings.total_estimate || 'Total Estimate') + '</span>';
                html += '<span class="pcq-price-final-amount">€' + self.formatPrice(totalAmount) + '</span>';
                html += '</div>';
            }
            
            // Update display
            $result.html(html);
            $loading.hide();
            $result.fadeIn(400);
        },
        
        /**
         * Show error message
         */
        showError: function(message) {
            const $display = $('.pcq-price-display');
            const $loading = $display.find('.pcq-price-loading');
            const $result = $display.find('.pcq-price-result');
            
            const html = '<div class="pcq-price-error">' +
                        '<span class="pcq-error-icon">⚠</span>' +
                        '<span class="pcq-error-message">' + this.escapeHtml(message) + '</span>' +
                        '</div>';
            
            $result.html(html);
            $loading.hide();
            $result.fadeIn(400);
        },
        
        /**
         * Hide price display
         */
        hidePriceDisplay: function() {
            $('.pcq-price-display').slideUp(300);
        },
        
        /**
         * Format price with proper rounding (2 decimal places)
         */
        formatPrice: function(amount) {
            const num = parseFloat(amount);
            if (isNaN(num)) return '0.00';
            // Always use toFixed(2) to prevent long decimals
            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&',
                '<': '<',
                '>': '>',
                '"': '"',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    // Store reference in global scope for access
    window.LivePriceCalculator = LivePriceCalculator;
    
    /**
     * Standalone calculateQuote function for external use
     * Implements the formula: Subtotal = base_rate + (area_sqm × rate_per_sqm) + (linear_meters × rate_per_linear_meter)
     *
     * @param {number} area - Area in square meters
     * @param {number} linearMeters - Perimeter/edge length in linear meters (optional)
     * @param {object} options - Optional pricing overrides
     * @returns {object} Quote breakdown
     */
    window.calculateQuote = function(area, linearMeters, options) {
        // Handle optional parameters - if linearMeters is an object, it's actually the options
        if (typeof linearMeters === 'object' && linearMeters !== null) {
            options = linearMeters;
            linearMeters = 0;
        }
        
        const BASE_RATE = options?.base_rate || 20.00;              // Fixed Call-out fee
        const RATE_PER_SQM = options?.rate_per_sqm || 20.00;        // Price per square meter
        const RATE_PER_LINEAR_M = options?.rate_per_linear_meter || 5.00; // Price per linear meter
        const VAT_PERCENT = options?.vat_rate || 21;                // VAT percentage
        const linear_m = parseFloat(linearMeters) || 0;             // Linear meters with default
        
        // Validate minimum area
        if (!area || area <= 0) {
            return {
                error: true,
                message: 'Area must be greater than 0'
            };
        }
        
        // 1. Calculate the variable size cost
        const sizeCost = area * RATE_PER_SQM;
        
        // 2. Calculate the linear meter cost
        const linearCost = linear_m * RATE_PER_LINEAR_M;
        
        // 3. Calculate Subtotal
        const subtotal = BASE_RATE + sizeCost + linearCost;
        
        // 4. Calculate Taxes
        const vatAmount = subtotal * (VAT_PERCENT / 100);
        
        // 5. Final Total
        const totalEstimate = subtotal + vatAmount;
        
        return {
            baseRate: BASE_RATE.toFixed(2),
            ratePerSqm: RATE_PER_SQM.toFixed(2),
            ratePerLinearMeter: RATE_PER_LINEAR_M.toFixed(2),
            sizeCost: sizeCost.toFixed(2),
            linearCost: linearCost.toFixed(2),
            subtotal: subtotal.toFixed(2),
            vatRate: VAT_PERCENT,
            vat: vatAmount.toFixed(2),
            total: totalEstimate.toFixed(2)
        };
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        // Check if we're on a page with quote form
        if ($('.pcq-quote-form').length > 0) {
            LivePriceCalculator.init();
        }
    });

})(jQuery);
