/**
 * Live Price Calculator
 * Real-time quote calculation with debouncing
 * 
 * @package ProClean\Quotation
 * @version 1.0.0
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
         * Initialize the calculator
         */
        init: function() {
            this.bindEvents();
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
            
            // Initial check for roof type field
            self.toggleRoofTypeField();
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
                        self.showError(response.data.message || pcq_ajax.strings.error);
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
            
            // Validate numeric values
            const sqm = parseFloat(data.square_meters);
            if (isNaN(sqm) || sqm < 10 || sqm > 10000) {
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
         * Display price breakdown
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
                        const isTotal = item.label.toLowerCase().includes('total');
                        const itemClass = isTotal ? 'pcq-price-total' : 'pcq-price-item';
                        
                        html += '<div class="' + itemClass + '">';
                        html += '<span class="pcq-price-label">' + self.escapeHtml(item.label) + '</span>';
                        html += '<span class="pcq-price-amount">€' + self.formatPrice(item.amount) + '</span>';
                        html += '</div>';
                    }
                });
            }
            
            html += '</div>';
            
            // Add total price highlight (if different from breakdown total)
            if (data.total || data.total_price) {
                const totalAmount = data.total || data.total_price;
                html += '<div class="pcq-price-final">';
                html += '<span class="pcq-price-final-label">' + pcq_ajax.strings.total_price + '</span>';
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
         * Format price
         */
        formatPrice: function(amount) {
            const num = parseFloat(amount);
            if (isNaN(num)) return '0.00';
            return num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        },
        
        /**
         * Escape HTML
         */
        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        }
    };
    
    // Store reference in global scope for access
    window.LivePriceCalculator = LivePriceCalculator;
    
    // Initialize on document ready
    $(document).ready(function() {
        // Check if we're on a page with quote form
        if ($('.pcq-quote-form').length > 0) {
            LivePriceCalculator.init();
        }
    });

})(jQuery);
