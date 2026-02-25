<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Admin\Settings;

/**
 * Pricing Engine Service
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class PricingEngine {
    
    /**
     * Pricing engine instance
     * 
     * @var PricingEngine
     */
    private static $instance = null;
    
    /**
     * Cached pricing rules
     * 
     * @var array
     */
    private $pricing_cache = null;
    
    /**
     * Get instance
     * 
     * @return PricingEngine
     */
    public static function getInstance(): PricingEngine {
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
     * Get pricing rules with caching
     * 
     * @return array Pricing rules
     */
    public function getPricingRules(): array {
        if ($this->pricing_cache === null) {
            $this->pricing_cache = get_transient('pcq_pricing_cache');
            
            if ($this->pricing_cache === false) {
                $this->pricing_cache = $this->loadPricingRules();
                set_transient('pcq_pricing_cache', $this->pricing_cache, 12 * HOUR_IN_SECONDS);
            }
        }
        
        return $this->pricing_cache;
    }
    
    /**
     * Clear pricing cache
     */
    public function clearCache(): void {
        delete_transient('pcq_pricing_cache');
        $this->pricing_cache = null;
    }
    
    /**
     * Calculate dynamic pricing based on demand
     * 
     * @param string $date Service date
     * @param string $service_type Service type
     * @return float Demand multiplier (1.0 = normal, >1.0 = higher demand)
     */
    public function getDemandMultiplier(string $date, string $service_type): float {
        // Get bookings for the week
        $week_start = date('Y-m-d', strtotime('monday', strtotime($date)));
        $week_end = date('Y-m-d', strtotime('sunday', strtotime($date)));
        
        $bookings_count = $this->getBookingsCountForPeriod($week_start, $week_end, $service_type);
        $max_weekly_capacity = Settings::get('max_daily_bookings', 3) * 6; // 6 working days
        
        $demand_ratio = $bookings_count / $max_weekly_capacity;
        
        if ($demand_ratio >= 0.8) {
            return 1.2; // 20% increase for high demand
        } elseif ($demand_ratio >= 0.6) {
            return 1.1; // 10% increase for medium demand
        }
        
        return 1.0; // Normal pricing
    }
    
    /**
     * Get seasonal pricing multiplier
     * 
     * @param string $date Service date
     * @return float Seasonal multiplier
     */
    public function getSeasonalMultiplier(string $date): float {
        $month = (int) date('n', strtotime($date));
        
        // Peak season (spring/summer cleaning)
        if (in_array($month, [3, 4, 5, 6, 7, 8])) {
            return 1.1; // 10% increase
        }
        
        // Off-season discount (winter)
        if (in_array($month, [12, 1, 2])) {
            return 0.9; // 10% discount
        }
        
        return 1.0; // Normal pricing
    }
    
    /**
     * Calculate bulk discount
     * 
     * @param float $square_meters Property size
     * @param string $service_type Service type
     * @return float Discount multiplier (1.0 = no discount, <1.0 = discount)
     */
    public function getBulkDiscountMultiplier(float $square_meters, string $service_type): float {
        $bulk_tiers = [
            500 => 0.95,  // 5% discount for 500+ sqm
            1000 => 0.90, // 10% discount for 1000+ sqm
            2000 => 0.85, // 15% discount for 2000+ sqm
            5000 => 0.80  // 20% discount for 5000+ sqm
        ];
        
        foreach (array_reverse($bulk_tiers, true) as $threshold => $multiplier) {
            if ($square_meters >= $threshold) {
                return $multiplier;
            }
        }
        
        return 1.0; // No discount
    }
    
    /**
     * Get repeat customer discount
     * 
     * @param string $customer_email Customer email
     * @return float Discount multiplier
     */
    public function getRepeatCustomerDiscount(string $customer_email): float {
        $previous_bookings = $this->getCustomerBookingsCount($customer_email);
        
        if ($previous_bookings >= 5) {
            return 0.9; // 10% discount for 5+ bookings
        } elseif ($previous_bookings >= 2) {
            return 0.95; // 5% discount for 2+ bookings
        }
        
        return 1.0; // No discount
    }
    
    /**
     * Calculate urgency surcharge
     * 
     * @param string $service_date Requested service date
     * @return float Surcharge multiplier
     */
    public function getUrgencySurcharge(string $service_date): float {
        $days_until_service = (strtotime($service_date) - time()) / (24 * 3600);
        
        if ($days_until_service <= 1) {
            return 1.5; // 50% surcharge for same/next day
        } elseif ($days_until_service <= 3) {
            return 1.25; // 25% surcharge for 2-3 days
        } elseif ($days_until_service <= 7) {
            return 1.1; // 10% surcharge for within a week
        }
        
        return 1.0; // No surcharge
    }
    
    /**
     * Apply promotional codes
     * 
     * @param string $promo_code Promotional code
     * @param float $total_amount Total amount
     * @return array Discount result
     */
    public function applyPromoCode(string $promo_code, float $total_amount): array {
        $promo_codes = $this->getActivePromoCodes();
        
        if (!isset($promo_codes[$promo_code])) {
            return [
                'valid' => false,
                'message' => __('Invalid promotional code.', 'pro-clean-quotation'),
                'discount' => 0
            ];
        }
        
        $promo = $promo_codes[$promo_code];
        
        // Check expiry
        if (isset($promo['expires']) && strtotime($promo['expires']) < time()) {
            return [
                'valid' => false,
                'message' => __('Promotional code has expired.', 'pro-clean-quotation'),
                'discount' => 0
            ];
        }
        
        // Check minimum amount
        if (isset($promo['min_amount']) && $total_amount < $promo['min_amount']) {
            return [
                'valid' => false,
                'message' => sprintf(__('Minimum order amount of €%.2f required.', 'pro-clean-quotation'), $promo['min_amount']),
                'discount' => 0
            ];
        }
        
        // Calculate discount
        $discount = 0;
        if ($promo['type'] === 'percentage') {
            $discount = $total_amount * ($promo['value'] / 100);
            if (isset($promo['max_discount'])) {
                $discount = min($discount, $promo['max_discount']);
            }
        } elseif ($promo['type'] === 'fixed') {
            $discount = min($promo['value'], $total_amount);
        }
        
        return [
            'valid' => true,
            'message' => sprintf(__('Promotional code applied: %s', 'pro-clean-quotation'), $promo['description']),
            'discount' => $discount,
            'code' => $promo_code
        ];
    }
    
    /**
     * Calculate comprehensive pricing with new formula
     *
     * Formula:
     *   Subtotal = base_rate + (area_sqm × rate_per_sqm)
     *   VAT Amount = Subtotal × vat_rate
     *   Total Estimate = Subtotal + VAT Amount
     *
     * @param array $params Pricing parameters
     * @return array Detailed pricing breakdown
     */
    public function calculateComprehensivePricing(array $params): array {
        $base_pricing = Settings::getPricingSettings();
        
        // Get service-specific pricing or fall back to global settings
        $service_pricing = $this->getServicePricing($params['service_type'] ?? null, $base_pricing);
        
        // Extract pricing values
        $base_rate = $service_pricing['base_rate'];        // Fixed call-out fee
        $rate_per_sqm = $service_pricing['rate_per_sqm'];  // Price per square meter
        $area_sqm = max(0, floatval($params['square_meters'] ?? 0));  // Area input
        
        // Validate minimum area
        if ($area_sqm <= 0) {
            return [
                'error' => true,
                'message' => __('Area must be greater than 0', 'pro-clean-quotation'),
                'base_calculation' => null,
                'tax_amount' => 0,
                'final_total' => 0
            ];
        }
        
        // === NEW PRICING FORMULA ===
        // 1. Calculate the variable size cost
        $size_cost = $area_sqm * $rate_per_sqm;
        
        // 2. Calculate Subtotal (base_rate + size_cost)
        $subtotal = $base_rate + $size_cost;
        
        // Apply custom field price modifiers
        $custom_field_adjustments = $this->applyCustomFieldModifiers($params);
        $subtotal += $custom_field_adjustments;
        
        // Apply complexity adjustments (property type, material, height)
        $complexity_adjustments = $this->calculateComplexityAdjustments($params, $base_pricing);
        $subtotal += $complexity_adjustments;
        
        // Apply minimum charge
        $subtotal = max($subtotal, $base_pricing['minimum_quote_value']);
        
        // Dynamic pricing adjustments
        $multipliers = [
            'demand' => $this->getDemandMultiplier($params['service_date'] ?? date('Y-m-d'), $params['service_type']),
            'seasonal' => $this->getSeasonalMultiplier($params['service_date'] ?? date('Y-m-d')),
            'bulk_discount' => $this->getBulkDiscountMultiplier($params['square_meters'], $params['service_type']),
            'repeat_customer' => isset($params['customer_email']) ? $this->getRepeatCustomerDiscount($params['customer_email']) : 1.0,
            'urgency' => isset($params['service_date']) ? $this->getUrgencySurcharge($params['service_date']) : 1.0
        ];
        
        // Apply multipliers
        $adjusted_subtotal = $subtotal;
        $adjustments_breakdown = [];
        
        foreach ($multipliers as $type => $multiplier) {
            if ($multiplier != 1.0) {
                $adjustment = $adjusted_subtotal * ($multiplier - 1.0);
                $adjusted_subtotal += $adjustment;
                
                $adjustments_breakdown[$type] = [
                    'multiplier' => $multiplier,
                    'adjustment' => $adjustment,
                    'label' => $this->getMultiplierLabel($type, $multiplier)
                ];
            }
        }
        
        // Apply promotional code if provided
        $promo_discount = 0;
        $promo_result = null;
        if (!empty($params['promo_code'])) {
            $promo_result = $this->applyPromoCode($params['promo_code'], $adjusted_subtotal);
            if ($promo_result['valid']) {
                $promo_discount = $promo_result['discount'];
                $adjusted_subtotal -= $promo_discount;
            }
        }
        
        // 3. Calculate VAT (tax)
        $tax_rate = $base_pricing['tax_rate'] ?? 21;  // Default 21%
        $tax_amount = $this->calculateTax($adjusted_subtotal, $base_pricing);
        
        // 4. Calculate Total
        $total = $adjusted_subtotal + $tax_amount;
        
        // Build breakdown for display
        $breakdown = [
            [
                'label' => sprintf(__('Service/Call-out Fee', 'pro-clean-quotation')),
                'amount' => round($base_rate, 2)
            ],
            [
                'label' => sprintf(__('Cleaning Service (%d sqm @ €%s/sqm)', 'pro-clean-quotation'),
                    $area_sqm, number_format($rate_per_sqm, 2)),
                'amount' => round($size_cost, 2)
            ]
        ];
        
        // Add custom field adjustments to breakdown
        if ($custom_field_adjustments != 0) {
            $breakdown[] = [
                'label' => __('Custom Options', 'pro-clean-quotation'),
                'amount' => round($custom_field_adjustments, 2)
            ];
        }
        
        // Add complexity adjustments to breakdown
        if ($complexity_adjustments != 0) {
            $breakdown[] = [
                'label' => __('Complexity Adjustments', 'pro-clean-quotation'),
                'amount' => round($complexity_adjustments, 2)
            ];
        }
        
        // Add subtotal
        $breakdown[] = [
            'label' => __('Subtotal', 'pro-clean-quotation'),
            'amount' => round($subtotal, 2)
        ];
        
        // Add dynamic adjustments
        foreach ($adjustments_breakdown as $type => $adj) {
            $breakdown[] = [
                'label' => $adj['label'],
                'amount' => round($adj['adjustment'], 2)
            ];
        }
        
        // Add promo discount
        if ($promo_discount > 0) {
            $breakdown[] = [
                'label' => __('Promotional Discount', 'pro-clean-quotation'),
                'amount' => -round($promo_discount, 2)
            ];
        }
        
        // Add VAT with dynamic percentage
        if ($tax_amount > 0) {
            $breakdown[] = [
                'label' => sprintf(__('VAT (%d%%)', 'pro-clean-quotation'), $tax_rate),
                'amount' => round($tax_amount, 2)
            ];
        }
        
        // Add total
        $breakdown[] = [
            'label' => __('Total Estimate', 'pro-clean-quotation'),
            'amount' => round($total, 2)
        ];
        
        return [
            'base_calculation' => [
                'base_rate' => round($base_rate, 2),
                'rate_per_sqm' => round($rate_per_sqm, 2),
                'area_sqm' => $area_sqm,
                'size_cost' => round($size_cost, 2),
                'complexity_adjustments' => round($complexity_adjustments, 2),
                'custom_field_adjustments' => round($custom_field_adjustments, 2),
                'subtotal' => round($subtotal, 2)
            ],
            'breakdown' => $breakdown,
            'dynamic_adjustments' => $adjustments_breakdown,
            'promotional_discount' => round($promo_discount, 2),
            'promo_result' => $promo_result,
            'tax_rate' => $tax_rate,
            'tax_amount' => round($tax_amount, 2),
            'adjusted_subtotal' => round($adjusted_subtotal, 2),
            'final_total' => round($total, 2),
            'total' => round($total, 2),
            'total_price' => round($total, 2),
            'savings' => round(max(0, $subtotal - $adjusted_subtotal + $promo_discount), 2),
            'multipliers_applied' => array_keys(array_filter($multipliers, fn($m) => $m != 1.0))
        ];
    }
    
    /**
     * Get service-specific pricing values
     *
     * @param int|null $service_id Service ID
     * @param array $base_pricing Base pricing settings
     * @return array Pricing values (base_rate, rate_per_sqm)
     */
    private function getServicePricing($service_id, array $base_pricing): array {
        // Default values from settings
        $default = [
            'base_rate' => 20.00,
            'rate_per_sqm' => 20.00
        ];
        
        if (empty($service_id)) {
            return $default;
        }
        
        // Try to load service-specific pricing
        global $wpdb;
        $services_table = $wpdb->prefix . 'pq_services';
        
        $service = $wpdb->get_row($wpdb->prepare(
            "SELECT base_rate, rate_per_sqm FROM $services_table WHERE id = %d",
            $service_id
        ), ARRAY_A);
        
        if ($service) {
            return [
                'base_rate' => floatval($service['base_rate'] ?? $default['base_rate']),
                'rate_per_sqm' => floatval($service['rate_per_sqm'] ?? $default['rate_per_sqm'])
            ];
        }
        
        return $default;
    }

    /**
     * Apply custom field price modifiers
     * 
     * @param array $params Pricing parameters
     * @return float Total custom field adjustments
     */
    private function applyCustomFieldModifiers(array $params): float {
        $total_adjustment = 0;
        
        // Check if custom_fields parameter exists and is array
        if (empty($params['custom_fields']) || !is_array($params['custom_fields'])) {
            return $total_adjustment;
        }
        
        // Load service to get custom field configurations
        $service_id = $params['service_type'] ?? null;
        if (empty($service_id)) {
            return $total_adjustment;
        }
        
        // Get service custom fields configuration
        global $wpdb;
        $service_meta_table = $wpdb->prefix . 'pq_service_meta';
        
        $custom_fields_json = $wpdb->get_var($wpdb->prepare(
            "SELECT meta_value FROM $service_meta_table WHERE service_id = %d AND meta_key = %s",
            $service_id,
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
            
            // Check if this field was submitted
            if (empty($params['custom_fields'][$field_id])) {
                continue;
            }
            
            $selected_value = is_array($params['custom_fields'][$field_id]) 
                ? ($params['custom_fields'][$field_id]['value'] ?? '') 
                : $params['custom_fields'][$field_id];
            
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
     * Load pricing rules from settings
     * 
     * @return array Pricing rules
     */
    private function loadPricingRules(): array {
        return Settings::getPricingSettings();
    }
    
    /**
     * Get bookings count for period
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param string $service_type Service type
     * @return int Bookings count
     */
    private function getBookingsCountForPeriod(string $start_date, string $end_date, string $service_type): int {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE service_date BETWEEN %s AND %s 
             AND service_type = %s 
             AND booking_status NOT IN ('cancelled')",
            $start_date,
            $end_date,
            $service_type
        ));
    }
    
    /**
     * Get customer bookings count
     * 
     * @param string $customer_email Customer email
     * @return int Bookings count
     */
    private function getCustomerBookingsCount(string $customer_email): int {
        global $wpdb;
        
        $table = $wpdb->prefix . 'pq_bookings';
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table 
             WHERE customer_email = %s 
             AND booking_status = 'completed'",
            $customer_email
        ));
    }
    
    /**
     * Get active promotional codes
     * 
     * @return array Promotional codes
     */
    private function getActivePromoCodes(): array {
        // This would typically be stored in database or settings
        // For now, return some example codes
        return [
            'WELCOME10' => [
                'type' => 'percentage',
                'value' => 10,
                'description' => '10% off for new customers',
                'min_amount' => 100,
                'expires' => '2025-12-31'
            ],
            'SPRING25' => [
                'type' => 'fixed',
                'value' => 25,
                'description' => '€25 off spring cleaning',
                'min_amount' => 200,
                'expires' => '2025-06-30'
            ],
            'BULK50' => [
                'type' => 'percentage',
                'value' => 15,
                'max_discount' => 50,
                'description' => '15% off large properties (max €50)',
                'min_amount' => 500
            ]
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
     * @param array $params Parameters
     * @param array $pricing Pricing settings
     * @return float Size cost
     */
    private function calculateSizeCost(array $params, array $pricing): float {
        $square_meters = $params['square_meters'] ?? 0;
        $linear_meters = $params['linear_meters'] ?? 0;
        $service_type = $params['service_type'];
        
        // Try to get service-specific rates if service_type is a numeric ID
        if (is_numeric($service_type)) {
            $service = new \ProClean\Quotation\Models\Service(intval($service_type));
            if ($service->getId()) {
                $sqm_rate = $service->getRatePerSqm();
                $linear_rate = $service->getRatePerLinearMeter();
                
                $sqm_cost = $square_meters * $sqm_rate;
                $linear_cost = $linear_meters * $linear_rate;
                
                return $sqm_cost + $linear_cost;
            }
        }
        
        // Fallback to global pricing settings for legacy string-based service types
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
     * Calculate complexity adjustments
     * 
     * @param array $params Parameters
     * @param array $pricing Pricing settings
     * @return float Adjustments
     */
    private function calculateComplexityAdjustments(array $params, array $pricing): float {
        $base_amount = $pricing['facade_base_rate'];
        $adjustments = 0;
        
        // Property type multiplier
        $property_type = $params['property_type'] ?? 'residential';
        $property_multiplier = match($property_type) {
            'commercial' => $pricing['commercial_multiplier'],
            'industrial' => $pricing['industrial_multiplier'],
            default => $pricing['residential_multiplier']
        };
        
        // Surface material multiplier
        $surface_material = $params['surface_material'] ?? 'brick';
        $material_multiplier = match($surface_material) {
            'stone' => $pricing['stone_multiplier'],
            'glass' => $pricing['glass_multiplier'],
            'metal' => $pricing['metal_multiplier'],
            'concrete' => $pricing['concrete_multiplier'],
            'composite' => $pricing['composite_multiplier'],
            default => $pricing['brick_multiplier']
        };
        
        // Height adjustment
        $building_height = $params['building_height'] ?? 1;
        $height_adjustment = ($building_height - 1) * $pricing['height_multiplier_per_floor'];
        
        // Calculate total adjustment
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
            return 0;
        }
        
        return $subtotal * ($pricing['tax_rate'] / 100);
    }
    
    /**
     * Get multiplier label for display
     * 
     * @param string $type Multiplier type
     * @param float $multiplier Multiplier value
     * @return string Label
     */
    private function getMultiplierLabel(string $type, float $multiplier): string {
        $percentage = ($multiplier - 1.0) * 100;
        $sign = $percentage > 0 ? '+' : '';
        
        switch ($type) {
            case 'demand':
                return sprintf(__('High Demand Surcharge (%s%.1f%%)', 'pro-clean-quotation'), $sign, $percentage);
            case 'seasonal':
                return $percentage > 0 
                    ? sprintf(__('Peak Season Surcharge (%s%.1f%%)', 'pro-clean-quotation'), $sign, $percentage)
                    : sprintf(__('Off-Season Discount (%s%.1f%%)', 'pro-clean-quotation'), $sign, $percentage);
            case 'bulk_discount':
                return sprintf(__('Bulk Discount (%s%.1f%%)', 'pro-clean-quotation'), $sign, $percentage);
            case 'repeat_customer':
                return sprintf(__('Repeat Customer Discount (%s%.1f%%)', 'pro-clean-quotation'), $sign, $percentage);
            case 'urgency':
                return sprintf(__('Urgency Surcharge (%s%.1f%%)', 'pro-clean-quotation'), $sign, $percentage);
            default:
                return sprintf(__('Adjustment (%s%.1f%%)', 'pro-clean-quotation'), $sign, $percentage);
        }
    }
}