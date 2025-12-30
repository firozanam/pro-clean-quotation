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
            
            // Get pricing settings
            $pricing = Settings::getPricingSettings();
            
            // Calculate base price
            $base_price = $this->calculateBasePrice($service_type, $pricing);
            
            // Calculate size-based cost
            $size_cost = $this->calculateSizeCost(
                $service_type,
                $square_meters,
                $linear_meters,
                $pricing
            );
            
            // Calculate adjustments
            $adjustments = $this->calculateAdjustments(
                $property_type,
                $surface_material,
                $building_height,
                $roof_type,
                $pricing
            );
            
            // Calculate subtotal
            $subtotal = $base_price + $size_cost + $adjustments;
            
            // Apply minimum charge
            $subtotal = max($subtotal, $pricing['minimum_quote_value']);
            
            // Calculate tax
            $tax_amount = $this->calculateTax($subtotal, $pricing);
            
            // Calculate total
            $total = $subtotal + $tax_amount;
            
            // Create breakdown
            $breakdown = $this->createPriceBreakdown([
                'base_price' => $base_price,
                'size_cost' => $size_cost,
                'adjustments' => $adjustments,
                'subtotal' => $subtotal,
                'tax_amount' => $tax_amount,
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
                    'base_price' => round($base_price, 2),
                    'size_cost' => round($size_cost, 2),
                    'adjustments' => round($adjustments, 2),
                    'subtotal' => round($subtotal, 2),
                    'tax_amount' => round($tax_amount, 2),
                    'total' => round($total, 2),
                    'breakdown' => $breakdown,
                    'valid_until' => date('Y-m-d', strtotime('+' . Settings::get('quote_validity_days', 30) . ' days')),
                    'currency' => '€',
                    'tax_rate' => $pricing['tax_rate']
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
        
        // Service type validation
        if (empty($data['service_type'])) {
            $errors['service_type'] = __('Service type is required.', 'pro-clean-quotation');
        } elseif (!in_array($data['service_type'], ['facade', 'roof', 'both'])) {
            $errors['service_type'] = __('Invalid service type.', 'pro-clean-quotation');
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
     * @param string $service_type Service type
     * @param array $pricing Pricing settings
     * @return float Base price
     */
    private function calculateBasePrice(string $service_type, array $pricing): float {
        switch ($service_type) {
            case 'facade':
                return $pricing['facade_base_rate'];
            case 'roof':
                return $pricing['roof_base_rate'];
            case 'both':
                return $pricing['facade_base_rate'] + $pricing['roof_base_rate'];
            default:
                return 0;
        }
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
        $sqm_cost = 0;
        $linear_cost = 0;
        
        switch ($service_type) {
            case 'facade':
                $sqm_cost = $square_meters * $pricing['facade_per_sqm'];
                $linear_cost = $linear_meters * $pricing['facade_per_linear_meter'];
                break;
            case 'roof':
                $sqm_cost = $square_meters * $pricing['roof_per_sqm'];
                $linear_cost = $linear_meters * $pricing['roof_per_linear_meter'];
                break;
            case 'both':
                $sqm_cost = $square_meters * ($pricing['facade_per_sqm'] + $pricing['roof_per_sqm']);
                $linear_cost = $linear_meters * ($pricing['facade_per_linear_meter'] + $pricing['roof_per_linear_meter']);
                break;
        }
        
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
     * Create price breakdown
     * 
     * @param array $data Calculation data
     * @return array Price breakdown
     */
    private function createPriceBreakdown(array $data): array {
        return [
            'base_service' => [
                'label' => sprintf(__('%s Base Rate', 'pro-clean-quotation'), ucfirst($data['service_type'])),
                'amount' => $data['base_price']
            ],
            'size_calculation' => [
                'label' => sprintf(__('Size (%s sqm)', 'pro-clean-quotation'), number_format($data['square_meters'], 1)),
                'amount' => $data['size_cost'],
                'details' => [
                    'square_meters' => $data['square_meters'],
                    'linear_meters' => $data['linear_meters']
                ]
            ],
            'adjustments' => [
                'label' => __('Property & Complexity Adjustments', 'pro-clean-quotation'),
                'amount' => $data['adjustments'],
                'details' => [
                    'property_type' => $data['property_type'],
                    'surface_material' => $data['surface_material'],
                    'building_height' => $data['building_height']
                ]
            ],
            'subtotal' => [
                'label' => __('Subtotal', 'pro-clean-quotation'),
                'amount' => $data['subtotal']
            ],
            'tax' => [
                'label' => sprintf(__('VAT (%s%%)', 'pro-clean-quotation'), $data['pricing']['tax_rate']),
                'amount' => $data['tax_amount']
            ],
            'total' => [
                'label' => __('Total Estimate', 'pro-clean-quotation'),
                'amount' => $data['total']
            ]
        ];
    }
}