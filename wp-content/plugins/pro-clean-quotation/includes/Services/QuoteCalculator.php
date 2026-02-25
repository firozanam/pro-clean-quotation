<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Admin\Settings;

/**
 * Quote Calculator Service
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class QuoteCalculator {
    
    /**
     * Calculator instance
     * 
     * @var QuoteCalculator
     */
    private static $instance = null;
    
    /**
     * Get instance
     * 
     * @return QuoteCalculator
     */
    public static function getInstance(): QuoteCalculator {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Private constructor for singleton
    }
    
    /**
     * Calculate quote based on form data
     * 
     * @param array $data Form data
     * @return array Calculation result
     */
    public function calculateQuote(array $data): array {
        try {
            // Validate input data
            $validation = $this->validateCalculationData($data);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message'],
                    'errors' => $validation['errors']
                ];
            }
            
            // Extract and sanitize data
            $service_type = sanitize_text_field($data['service_type'] ?? '');
            $square_meters = floatval($data['square_meters'] ?? 0);
            $linear_meters = floatval($data['linear_meters'] ?? 0);
            $building_height = intval($data['building_height'] ?? 1);
            $property_type = sanitize_text_field($data['property_type'] ?? 'residential');
            $surface_material = sanitize_text_field($data['surface_material'] ?? 'brick');
            $roof_type = sanitize_text_field($data['roof_type'] ?? '');
            
            // Validate minimum area (must be > 0)
            if ($square_meters <= 0) {
                return [
                    'success' => false,
                    'message' => __('Area must be greater than 0.', 'pro-clean-quotation'),
                    'errors' => ['square_meters' => __('Area must be greater than 0.', 'pro-clean-quotation')]
                ];
            }
            
            // Get pricing settings
            $pricing = Settings::getPricingSettings();
            
            // Get service-specific pricing (base_rate, rate_per_sqm, rate_per_linear_meter)
            $service_pricing = $this->getServicePricing($service_type);
            $base_rate = $service_pricing['base_rate'];
            $rate_per_sqm = $service_pricing['rate_per_sqm'];
            $rate_per_linear_meter = $service_pricing['rate_per_linear_meter'];
            
            // === NEW PRICING FORMULA ===
            // 1. Calculate the variable size cost (area × rate_per_sqm)
            $sqm_cost = $square_meters * $rate_per_sqm;
            
            // 1b. Calculate linear meter cost (linear_meters × rate_per_linear_meter)
            $linear_cost = $linear_meters * $rate_per_linear_meter;
            
            // Total size cost combines both
            $size_cost = $sqm_cost + $linear_cost;
            
            // 2. Calculate subtotal (base_rate + size_cost)
            $subtotal = $base_rate + $size_cost;
            
            // Calculate adjustments (for complexity, property type, etc.)
            $adjustments = $this->calculateAdjustments(
                $property_type,
                $surface_material,
                $building_height,
                $roof_type,
                $pricing
            );
            $subtotal += $adjustments;
            
            // Apply custom field modifiers
            $custom_field_adjustments = $this->applyCustomFieldModifiers($service_type, $data['custom_fields'] ?? []);
            $subtotal += $custom_field_adjustments;
            
            // Apply minimum charge
            $subtotal = max($subtotal, $pricing['minimum_quote_value']);
            
            // 3. Calculate VAT (tax)
            $tax_rate = $pricing['tax_rate'];
            $tax_amount = $this->calculateTax($subtotal, $pricing);
            
            // 4. Calculate Total
            $total = $subtotal + $tax_amount;
            
            // Create breakdown with new labels
            $breakdown = $this->createPriceBreakdown([
                'base_rate' => $base_rate,
                'rate_per_sqm' => $rate_per_sqm,
                'rate_per_linear_meter' => $rate_per_linear_meter,
                'sqm_cost' => $sqm_cost,
                'linear_cost' => $linear_cost,
                'size_cost' => $size_cost,
                'adjustments' => $adjustments,
                'custom_field_adjustments' => $custom_field_adjustments,
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
                'tax_rate' => $tax_rate,
                'total' => $total,
                'service_type' => $service_type,
                'square_meters' => $square_meters,
                'linear_meters' => $linear_meters,
                'property_type' => $property_type,
                'surface_material' => $surface_material,
                'building_height' => $building_height,
                'pricing' => $pricing
            ]);
            
            return [
                'success' => true,
                'data' => [
                    'base_rate' => round($base_rate, 2),
                    'rate_per_sqm' => round($rate_per_sqm, 2),
                    'rate_per_linear_meter' => round($rate_per_linear_meter, 2),
                    'sqm_cost' => round($sqm_cost, 2),
                    'linear_cost' => round($linear_cost, 2),
                    'size_cost' => round($size_cost, 2),
                    'adjustments' => round($adjustments, 2),
                    'subtotal' => round($subtotal, 2),
                    'tax_rate' => $tax_rate,
                    'tax_amount' => round($tax_amount, 2),
                    'total' => round($total, 2),
                    'total_price' => round($total, 2),
                    'breakdown' => $breakdown,
                    'valid_until' => date('Y-m-d', strtotime('+' . Settings::get('quote_validity_days', 30) . ' days')),
                    'currency' => '€'
                ]
            ];
            
        } catch (\Exception $e) {
            error_log('PCQ Quote calculation error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => __('An error occurred while calculating the quote. Please try again.', 'pro-clean-quotation')
            ];
        }
    }
    
    /**
     * Validate calculation data
     * 
     * @param array $data Input data
     * @return array Validation result
     */
    private function validateCalculationData(array $data): array {
        $errors = [];
        
        // Service type validation - validate against database
        if (empty($data['service_type'])) {
            $errors['service_type'] = __('Service type is required.', 'pro-clean-quotation');
        } else {
            $service_id = intval($data['service_type']);
            $service = new \ProClean\Quotation\Models\Service($service_id);
            
            if (!$service->getId() || !$service->isActive()) {
                $errors['service_type'] = __('Invalid service type.', 'pro-clean-quotation');
            }
        }
        
        // Square meters validation
        $square_meters = floatval($data['square_meters'] ?? 0);
        if ($square_meters < 10 || $square_meters > 10000) {
            $errors['square_meters'] = __('Square meters must be between 10 and 10,000.', 'pro-clean-quotation');
        }
        
        // Linear meters validation (optional)
        if (!empty($data['linear_meters'])) {
            $linear_meters = floatval($data['linear_meters']);
            if ($linear_meters < 5 || $linear_meters > 5000) {
                $errors['linear_meters'] = __('Linear meters must be between 5 and 5,000.', 'pro-clean-quotation');
            }
        }
        
        // Building height validation
        $building_height = intval($data['building_height'] ?? 1);
        if ($building_height < 1 || $building_height > 20) {
            $errors['building_height'] = __('Building height must be between 1 and 20 floors.', 'pro-clean-quotation');
        }
        
        // Property type validation
        if (!empty($data['property_type']) && !in_array($data['property_type'], ['residential', 'commercial', 'industrial'])) {
            $errors['property_type'] = __('Invalid property type.', 'pro-clean-quotation');
        }
        
        // Surface material validation
        $valid_materials = ['brick', 'stone', 'glass', 'metal', 'concrete', 'composite'];
        if (!empty($data['surface_material']) && !in_array($data['surface_material'], $valid_materials)) {
            $errors['surface_material'] = __('Invalid surface material.', 'pro-clean-quotation');
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'message' => empty($errors) ? '' : __('Please correct the following errors:', 'pro-clean-quotation')
        ];
    }
    
    /**
     * Calculate base price
     * 
     * @param string $service_type Service ID
     * @param array $pricing Pricing settings
     * @return float Base price
     */
    private function calculateBasePrice(string $service_type, array $pricing): float {
        // Service type is now a service ID, get the actual service
        $service = new \ProClean\Quotation\Models\Service(intval($service_type));
        
        if (!$service->getId()) {
            return 0;
        }
        
        // Get base price from service record using getPrice() method
        $base_price = $service->getPrice();
        
        // If no base price set, return 0
        return floatval($base_price);
    }
    
    /**
     * Calculate size-based cost
     * 
     * @param string $service_type Service type
     * @param float $square_meters Square meters
     * @param float $linear_meters Linear meters
     * @param array $pricing Pricing settings
     * @return float Size cost
     */
    private function calculateSizeCost(string $service_type, float $square_meters, float $linear_meters, array $pricing): float {
        // Service type is now a service ID, get the service
        $service = new \ProClean\Quotation\Models\Service(intval($service_type));
        
        if (!$service->getId()) {
            return 0;
        }
        
        // Use service-specific rates from the database
        $sqm_rate = $service->getRatePerSqm();
        $linear_rate = $service->getRatePerLinearMeter();
        
        // Calculate costs
        $sqm_cost = $square_meters * $sqm_rate;
        $linear_cost = $linear_meters * $linear_rate;
        
        return $sqm_cost + $linear_cost;
    }
    
    /**
     * Calculate adjustments
     * 
     * @param string $property_type Property type
     * @param string $surface_material Surface material
     * @param int $building_height Building height
     * @param string $roof_type Roof type
     * @param array $pricing Pricing settings
     * @return float Adjustments
     */
    private function calculateAdjustments(string $property_type, string $surface_material, int $building_height, string $roof_type, array $pricing): float {
        $base_amount = $pricing['facade_base_rate']; // Use as base for percentage calculations
        $adjustments = 0;
        
        // Property type multiplier
        $property_multiplier = match($property_type) {
            'commercial' => $pricing['commercial_multiplier'],
            'industrial' => $pricing['industrial_multiplier'],
            default => $pricing['residential_multiplier']
        };
        
        // Surface material multiplier
        $material_multiplier = match($surface_material) {
            'stone' => $pricing['stone_multiplier'],
            'glass' => $pricing['glass_multiplier'],
            'metal' => $pricing['metal_multiplier'],
            'concrete' => $pricing['concrete_multiplier'],
            'composite' => $pricing['composite_multiplier'],
            default => $pricing['brick_multiplier']
        };
        
        // Height adjustment (per floor above ground level)
        $height_adjustment = ($building_height - 1) * $pricing['height_multiplier_per_floor'];
        
        // Calculate total adjustment percentage
        $total_multiplier = ($property_multiplier - 1) + ($material_multiplier - 1) + $height_adjustment;
        
        return $base_amount * $total_multiplier;
    }
    
    /**
     * Calculate tax
     * 
     * @param float $subtotal Subtotal amount
     * @param array $pricing Pricing settings
     * @return float Tax amount
     */
    private function calculateTax(float $subtotal, array $pricing): float {
        if ($pricing['tax_inclusive']) {
            return 0; // Tax already included in prices
        }
        
        return $subtotal * ($pricing['tax_rate'] / 100);
    }
    
    /**
     * Apply custom field price modifiers
     * 
     * @param string $service_type Service ID
     * @param array $custom_fields Custom field data from frontend
     * @return float Total custom field adjustments
     */
    private function applyCustomFieldModifiers(string $service_type, array $custom_fields): float {
        $total_adjustment = 0;
        
        // Check if custom_fields parameter exists and is array
        if (empty($custom_fields) || !is_array($custom_fields)) {
            return $total_adjustment;
        }
        
        // Get service custom fields configuration
        global $wpdb;
        $service_meta_table = $wpdb->prefix . 'pq_service_meta';
        
        $custom_fields_json = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $service_meta_table WHERE service_id = %d AND meta_key = %s",
            intval($service_type),
            'custom_fields'
        ));
        
        if (empty($custom_fields_json)) {
            return $total_adjustment;
        }
        
        $custom_fields_config = json_decode($custom_fields_json, true);
        if (!is_array($custom_fields_config)) {
            return $total_adjustment;
        }
        
        // Process each custom field
        foreach ($custom_fields_config as $field_config) {
            $field_id = $field_config['id'] ?? '';
            
            if (empty($custom_fields[$field_id])) {
                continue;
            }
            
            // Get selected value
            $field_data = $custom_fields[$field_id];
            $selected_value = is_array($field_data) 
                ? ($field_data['value'] ?? '') 
                : $field_data;
            
            // Find the selected option's price modifier
            if (!empty($field_config['options']) && is_array($field_config['options'])) {
                foreach ($field_config['options'] as $option) {
                    if ($option['value'] === $selected_value) {
                        $price_modifier = floatval($option['price_modifier'] ?? 0);
                        if ($price_modifier != 0) {
                            $total_adjustment += $price_modifier;
                        }
                        break;
                    }
                }
            }
        }
        
        return $total_adjustment;
    }
    
    /**
     * Get service-specific pricing values
     *
     * @param string $service_id Service ID
     * @return array Pricing values (base_rate, rate_per_sqm, rate_per_linear_meter)
     */
    private function getServicePricing($service_id): array {
        $defaults = [
            'base_rate' => 20.00,
            'rate_per_sqm' => 20.00,
            'rate_per_linear_meter' => 5.00
        ];
        
        $service = new \ProClean\Quotation\Models\Service(intval($service_id));
        
        if (!$service->getId()) {
            return $defaults;
        }
        
        return [
            'base_rate' => $service->getBaseRate() ?: $defaults['base_rate'],
            'rate_per_sqm' => $service->getRatePerSqm() ?: $defaults['rate_per_sqm'],
            'rate_per_linear_meter' => $service->getRatePerLinearMeter() ?: $defaults['rate_per_linear_meter']
        ];
    }
    
    /**
     * Create price breakdown with new labels
     *
     * @param array $data Calculation data
     * @return array Price breakdown
     */
    private function createPriceBreakdown(array $data): array {
        // Get service name from service ID
        $service = new \ProClean\Quotation\Models\Service(intval($data['service_type']));
        $service_name = $service->getId() ? $service->getName() : 'Service';
        
        $breakdown = [];
        
        // 1. Service/Call-out Fee (was "Base Rate")
        $breakdown[] = [
            'label' => __('Service/Call-out Fee', 'pro-clean-quotation'),
            'amount' => round($data['base_rate'], 2)
        ];
        
        // 2. Size-based cost (sqm) with explicit rate display
        $breakdown[] = [
            'label' => sprintf(
                __('%s (%d sqm @ €%s/sqm)', 'pro-clean-quotation'),
                $service_name,
                $data['square_meters'],
                number_format($data['rate_per_sqm'], 2)
            ),
            'amount' => round($data['sqm_cost'], 2)
        ];
        
        // 3. Linear meter cost with explicit rate display (if applicable)
        if (!empty($data['linear_meters']) && $data['linear_meters'] > 0) {
            $breakdown[] = [
                'label' => sprintf(
                    __('Perimeter/Edge Work (%d m @ €%s/m)', 'pro-clean-quotation'),
                    $data['linear_meters'],
                    number_format($data['rate_per_linear_meter'], 2)
                ),
                'amount' => round($data['linear_cost'], 2)
            ];
        }
        
        // 4. Property & Complexity Adjustments
        if (!empty($data['adjustments']) && $data['adjustments'] != 0) {
            $breakdown[] = [
                'label' => __('Property & Complexity Adjustments', 'pro-clean-quotation'),
                'amount' => round($data['adjustments'], 2)
            ];
        }
        
        // 5. Custom field adjustments if they exist
        if (!empty($data['custom_field_adjustments']) && $data['custom_field_adjustments'] != 0) {
            $breakdown[] = [
                'label' => __('Service Options', 'pro-clean-quotation'),
                'amount' => round($data['custom_field_adjustments'], 2)
            ];
        }
        
        // 6. Subtotal
        $breakdown[] = [
            'label' => __('Subtotal', 'pro-clean-quotation'),
            'amount' => round($data['subtotal'], 2)
        ];
        
        // 7. VAT with dynamic percentage
        $breakdown[] = [
            'label' => sprintf(__('VAT (%d%%)', 'pro-clean-quotation'), $data['tax_rate']),
            'amount' => round($data['tax_amount'], 2)
        ];
        
        // 8. Total Estimate
        $breakdown[] = [
            'label' => __('Total Estimate', 'pro-clean-quotation'),
            'amount' => round($data['total'], 2)
        ];
        
        return $breakdown;
    }
}