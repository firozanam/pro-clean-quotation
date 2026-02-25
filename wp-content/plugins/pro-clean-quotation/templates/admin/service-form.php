<?php
/**
 * Admin Service Form Template (Add/Edit)
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = $action === 'edit' && $service && $service->getId();
$page_title = $is_edit ? __('Edit Service', 'pro-clean-quotation') : __('Add New Service', 'pro-clean-quotation');
$button_text = $is_edit ? __('Update Service', 'pro-clean-quotation') : __('Create Service', 'pro-clean-quotation');

// Get available categories
global $wpdb;
$categories_table = $wpdb->prefix . 'pq_service_categories';
$categories = $wpdb->get_results("SELECT * FROM $categories_table WHERE status = 'active' ORDER BY sort_order ASC, name ASC");

// Default values
$service_data = [
    'name' => $is_edit ? $service->getName() : '',
    'description' => $is_edit ? $service->getDescription() : '',
    'category_id' => $is_edit ? $service->getCategoryId() : 0,
    'duration' => $is_edit ? $service->getDuration() : 60,
    'base_rate' => $is_edit ? $service->getBaseRate() : 20.00,
    'rate_per_sqm' => $is_edit ? $service->getRatePerSqm() : 20.00,
    'rate_per_linear_meter' => $is_edit ? $service->getRatePerLinearMeter() : 5.00,
    'capacity' => $is_edit ? $service->getCapacity() : 1,
    'buffer_time_before' => $is_edit ? $service->getBufferTimeBefore() : 15,
    'buffer_time_after' => $is_edit ? $service->getBufferTimeAfter() : 15,
    'color' => $is_edit ? $service->getColor() : '#2196F3',
    'status' => $is_edit ? $service->getStatus() : 'active',
    'sort_order' => $is_edit ? $service->getSortOrder() : 0,
    'min_advance_time' => $is_edit ? $service->getMinAdvanceTime() : 0,
    'max_advance_time' => $is_edit ? $service->getMaxAdvanceTime() : 0
];

// Get custom fields
$custom_fields = $is_edit ? $service->getCustomFields() : [];
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-services'); ?>" class="page-title-action">
        <?php _e('Back to Services', 'pro-clean-quotation'); ?>
    </a>
    
    <form method="post" action="" class="pcq-service-form">
        <?php 
        if ($is_edit) {
            wp_nonce_field('pcq_save_service_' . $service->getId(), '_wpnonce');
            echo '<input type="hidden" name="service_id" value="' . $service->getId() . '">';
        } else {
            wp_nonce_field('pcq_create_service', '_wpnonce');
        }
        ?>
        <input type="hidden" name="action" value="save_service">
        
        <div class="pcq-form-container">
            <!-- Main Form -->
            <div class="pcq-main-form">
                <!-- Basic Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Basic Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="name"><?php _e('Service Name', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" name="name" id="name" 
                                       value="<?php echo esc_attr($service_data['name']); ?>" 
                                       class="regular-text" required>
                                <p class="description"><?php _e('Enter the name of the service (e.g., "Façade Cleaning").', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="description"><?php _e('Description', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <textarea name="description" id="description" rows="4" class="large-text"><?php echo esc_textarea($service_data['description']); ?></textarea>
                                <p class="description"><?php _e('Brief description of what this service includes.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="category_id"><?php _e('Category', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <select name="category_id" id="category_id" class="regular-text">
                                    <option value="0"><?php _e('-- No Category --', 'pro-clean-quotation'); ?></option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo esc_attr($category->id); ?>" 
                                                <?php selected($service_data['category_id'], $category->id); ?>
                                                data-color="<?php echo esc_attr($category->color); ?>">
                                            <?php echo esc_html($category->name); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php _e('Organize this service under a category.', 'pro-clean-quotation'); ?>
                                    <?php if (empty($categories)): ?>
                                        <a href="<?php echo admin_url('admin.php?page=pcq-service-categories&action=add'); ?>"><?php _e('Create a category first', 'pro-clean-quotation'); ?></a>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="color"><?php _e('Color', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="color" id="color" 
                                       value="<?php echo esc_attr($service_data['color']); ?>" 
                                       class="pcq-color-picker">
                                <p class="description"><?php _e('Color used to display this service in the calendar and other views.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Scheduling -->
                <div class="pcq-form-section">
                    <h2><?php _e('Scheduling', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="duration"><?php _e('Duration', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="number" name="duration" id="duration" 
                                       value="<?php echo esc_attr($service_data['duration']); ?>" 
                                       class="small-text" min="15" step="15" required>
                                <span><?php _e('minutes', 'pro-clean-quotation'); ?></span>
                                <p class="description"><?php _e('Default duration for this service in minutes.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="capacity"><?php _e('Capacity', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="capacity" id="capacity" 
                                       value="<?php echo esc_attr($service_data['capacity']); ?>" 
                                       class="small-text" min="1" max="10">
                                <span><?php _e('people', 'pro-clean-quotation'); ?></span>
                                <p class="description"><?php _e('Number of people/employees required for this service.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="buffer_time_before"><?php _e('Buffer Time Before', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="buffer_time_before" id="buffer_time_before" 
                                       value="<?php echo esc_attr($service_data['buffer_time_before']); ?>" 
                                       class="small-text" min="0" step="5">
                                <span><?php _e('minutes', 'pro-clean-quotation'); ?></span>
                                <p class="description"><?php _e('Time needed before the service starts (setup, travel, etc.).', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="buffer_time_after"><?php _e('Buffer Time After', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="buffer_time_after" id="buffer_time_after" 
                                       value="<?php echo esc_attr($service_data['buffer_time_after']); ?>" 
                                       class="small-text" min="0" step="5">
                                <span><?php _e('minutes', 'pro-clean-quotation'); ?></span>
                                <p class="description"><?php _e('Time needed after the service ends (cleanup, travel, etc.).', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Pricing -->
                <div class="pcq-form-section">
                    <h2><?php _e('Pricing', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="base_rate"><?php _e('Service/Call-out Fee', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="number" name="base_rate" id="base_rate"
                                       value="<?php echo esc_attr($service_data['base_rate']); ?>"
                                       class="regular-text" step="0.01" min="0" required>
                                <span>€</span>
                                <p class="description"><?php _e('Minimum cost to show up at the job site, regardless of property size (e.g., €20.00). This is the fixed call-out fee.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="rate_per_sqm"><?php _e('Rate per Square Meter', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="number" name="rate_per_sqm" id="rate_per_sqm"
                                       value="<?php echo esc_attr($service_data['rate_per_sqm']); ?>"
                                       class="regular-text" step="0.01" min="0" required>
                                <span>€/m²</span>
                                <p class="description"><?php _e('Price per square meter for the work (e.g., €20.00/m²). This is multiplied by the property area.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="rate_per_linear_meter"><?php _e('Rate per Linear Meter', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="rate_per_linear_meter" id="rate_per_linear_meter"
                                       value="<?php echo esc_attr($service_data['rate_per_linear_meter']); ?>"
                                       class="regular-text" step="0.01" min="0">
                                <span>€/m</span>
                                <p class="description"><?php _e('Price per linear meter for perimeter/edge work (e.g., €5.00/m). This is multiplied by the property perimeter length.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                    
                    <div class="pcq-pricing-preview" style="margin-top: 15px; padding: 15px; background: #f7f7f7; border-left: 4px solid #2196F3;">
                        <h4 style="margin: 0 0 10px 0;"><?php _e('Pricing Formula Preview', 'pro-clean-quotation'); ?></h4>
                        <p class="description" style="margin: 0;">
                            <strong><?php _e('Formula:', 'pro-clean-quotation'); ?></strong><br>
                            <code style="background: #e0e0e0; padding: 2px 6px; border-radius: 3px;">
                                <?php _e('Subtotal', 'pro-clean-quotation'); ?> = <?php _e('Service Fee', 'pro-clean-quotation'); ?> + (<?php _e('Area', 'pro-clean-quotation'); ?> × <?php _e('Rate/m²', 'pro-clean-quotation'); ?>) + (<?php _e('Perimeter', 'pro-clean-quotation'); ?> × <?php _e('Rate/m', 'pro-clean-quotation'); ?>)
                            </code><br><br>
                            <strong><?php _e('Example (100 m², 40 m perimeter):', 'pro-clean-quotation'); ?></strong><br>
                            <span id="pcq-pricing-example">
                                <?php
                                $example_base = $service_data['base_rate'];
                                $example_rate_sqm = $service_data['rate_per_sqm'];
                                $example_rate_linear = $service_data['rate_per_linear_meter'];
                                $example_area = 100;
                                $example_perimeter = 40;
                                $example_subtotal = $example_base + ($example_area * $example_rate_sqm) + ($example_perimeter * $example_rate_linear);
                                printf(
                                    '€%s + (100 m² × €%s/m²) + (40 m × €%s/m) = €%s',
                                    number_format($example_base, 2),
                                    number_format($example_rate_sqm, 2),
                                    number_format($example_rate_linear, 2),
                                    number_format($example_subtotal, 2)
                                );
                                ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <!-- Custom Fields -->
                <div class="pcq-form-section">
                    <h2><?php _e('Custom Fields', 'pro-clean-quotation'); ?></h2>
                    <p class="description">
                        <?php _e('Define service-specific fields that customers need to fill when requesting this service. Each field can have price modifiers.', 'pro-clean-quotation'); ?>
                    </p>
                    
                    <div id="pcq-custom-fields-container">
                        <?php if (!empty($custom_fields)): ?>
                            <?php foreach ($custom_fields as $index => $field): ?>
                                <div class="pcq-custom-field-item" data-index="<?php echo $index; ?>">
                                    <div class="pcq-custom-field-header">
                                        <h4><?php echo esc_html($field['label'] ?? __('Custom Field', 'pro-clean-quotation')); ?></h4>
                                        <button type="button" class="button pcq-remove-field"><?php _e('Remove', 'pro-clean-quotation'); ?></button>
                                    </div>
                                    
                                    <input type="hidden" name="custom_fields[<?php echo $index; ?>][id]" value="<?php echo esc_attr($field['id'] ?? ''); ?>">
                                    
                                    <table class="form-table">
                                        <tr>
                                            <th><label><?php _e('Field Label', 'pro-clean-quotation'); ?> <span class="required">*</span></label></th>
                                            <td>
                                                <input type="text" name="custom_fields[<?php echo $index; ?>][label]" 
                                                       value="<?php echo esc_attr($field['label'] ?? ''); ?>" 
                                                       class="regular-text pcq-field-label" required>
                                                <p class="description"><?php _e('Label shown to customers (e.g., "Roof Type")', 'pro-clean-quotation'); ?></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label><?php _e('Field Type', 'pro-clean-quotation'); ?></label></th>
                                            <td>
                                                <select name="custom_fields[<?php echo $index; ?>][type]" class="regular-text">
                                                    <option value="select" <?php selected($field['type'] ?? 'select', 'select'); ?>><?php _e('Dropdown (Select)', 'pro-clean-quotation'); ?></option>
                                                    <option value="radio" <?php selected($field['type'] ?? '', 'radio'); ?>><?php _e('Radio Buttons', 'pro-clean-quotation'); ?></option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label><?php _e('Required', 'pro-clean-quotation'); ?></label></th>
                                            <td>
                                                <label>
                                                    <input type="checkbox" name="custom_fields[<?php echo $index; ?>][required]" 
                                                           value="1" <?php checked($field['required'] ?? true, true); ?>>
                                                    <?php _e('This field is required', 'pro-clean-quotation'); ?>
                                                </label>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><label><?php _e('Options', 'pro-clean-quotation'); ?></label></th>
                                            <td>
                                                <div class="pcq-field-options">
                                                    <?php if (!empty($field['options'])): ?>
                                                        <?php foreach ($field['options'] as $opt_index => $option): ?>
                                                            <div class="pcq-option-row">
                                                                <input type="text" name="custom_fields[<?php echo $index; ?>][options][<?php echo $opt_index; ?>][value]" 
                                                                       value="<?php echo esc_attr($option['value'] ?? ''); ?>" 
                                                                       placeholder="<?php _e('Value (e.g., flat)', 'pro-clean-quotation'); ?>" class="small-text">
                                                                <input type="text" name="custom_fields[<?php echo $index; ?>][options][<?php echo $opt_index; ?>][label]" 
                                                                       value="<?php echo esc_attr($option['label'] ?? ''); ?>" 
                                                                       placeholder="<?php _e('Label (e.g., Flat Roof)', 'pro-clean-quotation'); ?>" class="regular-text">
                                                                <input type="number" name="custom_fields[<?php echo $index; ?>][options][<?php echo $opt_index; ?>][price_modifier]" 
                                                                       value="<?php echo esc_attr($option['price_modifier'] ?? 0); ?>" 
                                                                       placeholder="0" class="small-text" step="0.01">
                                                                <span class="description">€</span>
                                                                <button type="button" class="button pcq-remove-option">×</button>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <button type="button" class="button pcq-add-option" data-field-index="<?php echo $index; ?>">
                                                    <?php _e('+ Add Option', 'pro-clean-quotation'); ?>
                                                </button>
                                                <p class="description"><?php _e('Define available options with price modifiers (positive or negative amounts in euros)', 'pro-clean-quotation'); ?></p>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" id="pcq-add-custom-field" class="button button-secondary">
                        <?php _e('+ Add Custom Field', 'pro-clean-quotation'); ?>
                    </button>
                </div>
                
                <!-- Advanced Settings -->
                <div class="pcq-form-section">
                    <h2><?php _e('Advanced Settings', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="min_advance_time"><?php _e('Minimum Advance Time', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="min_advance_time" id="min_advance_time" 
                                       value="<?php echo esc_attr($service_data['min_advance_time']); ?>" 
                                       class="small-text" min="0">
                                <span><?php _e('hours', 'pro-clean-quotation'); ?></span>
                                <p class="description"><?php _e('Minimum time required between booking and service (0 = no restriction).', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="max_advance_time"><?php _e('Maximum Advance Time', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="max_advance_time" id="max_advance_time" 
                                       value="<?php echo esc_attr($service_data['max_advance_time']); ?>" 
                                       class="small-text" min="0">
                                <span><?php _e('days', 'pro-clean-quotation'); ?></span>
                                <p class="description"><?php _e('Maximum time in advance that this service can be booked (0 = no restriction).', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="sort_order"><?php _e('Sort Order', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="sort_order" id="sort_order" 
                                       value="<?php echo esc_attr($service_data['sort_order']); ?>" 
                                       class="small-text">
                                <p class="description"><?php _e('Order in which this service appears in lists (lower numbers appear first).', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="pcq-form-sidebar">
                <!-- Status -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Status', 'pro-clean-quotation'); ?></h3>
                    
                    <select name="status" id="status" class="widefat">
                        <option value="active" <?php selected($service_data['status'], 'active'); ?>><?php _e('Active', 'pro-clean-quotation'); ?></option>
                        <option value="inactive" <?php selected($service_data['status'], 'inactive'); ?>><?php _e('Inactive', 'pro-clean-quotation'); ?></option>
                    </select>
                    
                    <p class="description"><?php _e('Inactive services are not available for new bookings.', 'pro-clean-quotation'); ?></p>
                </div>
                
                <!-- Actions -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Actions', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-actions">
                        <input type="submit" name="save" class="button button-primary button-large" 
                               value="<?php echo esc_attr($button_text); ?>">
                        
                        <a href="<?php echo admin_url('admin.php?page=pcq-services'); ?>" 
                           class="button button-large">
                            <?php _e('Cancel', 'pro-clean-quotation'); ?>
                        </a>
                        
                        <?php if ($is_edit): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-services&action=delete&id=' . $service->getId()), 'delete_service_' . $service->getId()); ?>" 
                           class="button button-large pcq-delete-btn" 
                           onclick="return confirm('<?php _e('Are you sure you want to delete this service? This action cannot be undone.', 'pro-clean-quotation'); ?>')">
                            <?php _e('Delete Service', 'pro-clean-quotation'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Preview -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Preview', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-service-preview">
                        <div class="pcq-preview-badge">
                            <span class="pcq-service-badge" id="preview-badge" style="background-color: <?php echo esc_attr($service_data['color']); ?>">
                                <span id="preview-name"><?php echo esc_html($service_data['name'] ?: 'Service Name'); ?></span>
                            </span>
                        </div>
                        
                        <div class="pcq-preview-details">
                            <div><strong><?php _e('Duration:', 'pro-clean-quotation'); ?></strong> <span id="preview-duration"><?php echo $service_data['duration']; ?></span> min</div>
                            <div><strong><?php _e('Call-out Fee:', 'pro-clean-quotation'); ?></strong> €<span id="preview-base-rate"><?php echo number_format($service_data['base_rate'], 2); ?></span></div>
                            <div><strong><?php _e('Rate/sqm:', 'pro-clean-quotation'); ?></strong> €<span id="preview-rate-per-sqm"><?php echo number_format($service_data['rate_per_sqm'], 2); ?></span></div>
                            <div><strong><?php _e('Rate/linear m:', 'pro-clean-quotation'); ?></strong> €<span id="preview-rate-linear"><?php echo number_format($service_data['rate_per_linear_meter'], 2); ?></span></div>
                            <div><strong><?php _e('Capacity:', 'pro-clean-quotation'); ?></strong> <span id="preview-capacity"><?php echo $service_data['capacity']; ?></span> people</div>
                        </div>
                    </div>
                </div>
                
                <?php if ($is_edit): ?>
                <!-- Information -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-info-list">
                        <div class="pcq-info-item">
                            <strong><?php _e('Created:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($service->getCreatedAt())); ?>
                        </div>
                        
                        <?php if ($service->getUpdatedAt()): ?>
                        <div class="pcq-info-item">
                            <strong><?php _e('Last Updated:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($service->getUpdatedAt())); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="pcq-info-item">
                            <strong><?php _e('Service ID:', 'pro-clean-quotation'); ?></strong><br>
                            #<?php echo $service->getId(); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<style>
.pcq-service-form {
    margin-top: 20px;
}

.pcq-form-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}

.pcq-form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
}

.pcq-form-section h2 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    border-bottom: 2px solid #3498db;
    padding-bottom: 8px;
}

.pcq-sidebar-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.pcq-sidebar-section h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.pcq-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.pcq-delete-btn {
    background-color: #f44336 !important;
    color: #fff !important;
    border-color: #f44336 !important;
}

.pcq-service-preview {
    text-align: center;
}

.pcq-preview-badge {
    margin-bottom: 15px;
}

.pcq-service-badge {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 12px;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
}

.pcq-preview-details {
    text-align: left;
    font-size: 13px;
    line-height: 1.6;
}

.pcq-preview-details div {
    margin-bottom: 5px;
}

.pcq-info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.pcq-info-item {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    font-size: 13px;
}

.required {
    color: #d63638;
}

.pcq-custom-field-item {
    background: #f8f9fa;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 15px;
}

.pcq-custom-field-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.pcq-custom-field-header h4 {
    margin: 0;
    color: #2c3e50;
}

.pcq-field-options {
    margin-bottom: 10px;
}

.pcq-option-row {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
    padding: 10px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.pcq-option-row input[type="text"]:nth-of-type(1) {
    flex: 0 0 150px;
}

.pcq-option-row input[type="text"]:nth-of-type(2) {
    flex: 1;
}

.pcq-option-row input[type="number"] {
    flex: 0 0 100px;
}

.pcq-remove-option {
    flex: 0 0 auto;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
}

.pcq-remove-option:hover {
    background: #c82333;
}

.pcq-remove-field {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.pcq-remove-field:hover {
    background: #c82333;
    border-color: #c82333;
}

@media (max-width: 768px) {
    .pcq-form-container {
        grid-template-columns: 1fr;
    }
    
    .pcq-option-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pcq-option-row input {
        width: 100% !important;
        flex: 1 1 auto !important;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Initialize color picker
    if ($.fn.wpColorPicker) {
        $('.pcq-color-picker').wpColorPicker({
            change: function(event, ui) {
                updatePreview();
            }
        });
    }
    
    // Update preview when fields change
    $('#name, #duration, #base_rate, #rate_per_sqm, #rate_per_linear_meter, #capacity, #color').on('input change', function() {
        updatePreview();
    });
    
    function updatePreview() {
        var name = $('#name').val() || 'Service Name';
        var duration = $('#duration').val() || '60';
        var baseRate = parseFloat($('#base_rate').val()) || 0;
        var ratePerSqm = parseFloat($('#rate_per_sqm').val()) || 0;
        var ratePerLinear = parseFloat($('#rate_per_linear_meter').val()) || 0;
        var capacity = $('#capacity').val() || '1';
        var color = $('#color').val() || '#2196F3';
        
        $('#preview-name').text(name);
        $('#preview-duration').text(duration);
        $('#preview-base-rate').text(baseRate.toFixed(2));
        $('#preview-rate-per-sqm').text(ratePerSqm.toFixed(2));
        $('#preview-rate-linear').text(ratePerLinear.toFixed(2));
        $('#preview-capacity').text(capacity);
        $('#preview-badge').css('background-color', color);
    }
    
    // Form validation
    $('form.pcq-service-form').on('submit', function(e) {
        var isValid = true;
        
        // Check required fields
        $(this).find('[required]').each(function() {
            var field = $(this);
            var value = field.val().trim();
            
            if (!value) {
                field.addClass('error');
                isValid = false;
            } else {
                field.removeClass('error');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('<?php _e('Please fill in all required fields.', 'pro-clean-quotation'); ?>');
        }
    });
    
    // ===== Custom Fields Management =====
    
    var fieldIndex = <?php echo !empty($custom_fields) ? count($custom_fields) : 0; ?>;
    
    // Add new custom field
    $('#pcq-add-custom-field').on('click', function() {
        var fieldHtml = `
            <div class="pcq-custom-field-item" data-index="${fieldIndex}">
                <div class="pcq-custom-field-header">
                    <h4><?php _e('New Custom Field', 'pro-clean-quotation'); ?></h4>
                    <button type="button" class="button pcq-remove-field"><?php _e('Remove', 'pro-clean-quotation'); ?></button>
                </div>
                
                <input type="hidden" name="custom_fields[${fieldIndex}][id]" value="">
                
                <table class="form-table">
                    <tr>
                        <th><label><?php _e('Field Label', 'pro-clean-quotation'); ?> <span class="required">*</span></label></th>
                        <td>
                            <input type="text" name="custom_fields[${fieldIndex}][label]" 
                                   value="" class="regular-text pcq-field-label" required>
                            <p class="description"><?php _e('Label shown to customers (e.g., "Roof Type")', 'pro-clean-quotation'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Field Type', 'pro-clean-quotation'); ?></label></th>
                        <td>
                            <select name="custom_fields[${fieldIndex}][type]" class="regular-text">
                                <option value="select"><?php _e('Dropdown (Select)', 'pro-clean-quotation'); ?></option>
                                <option value="radio"><?php _e('Radio Buttons', 'pro-clean-quotation'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Required', 'pro-clean-quotation'); ?></label></th>
                        <td>
                            <label>
                                <input type="checkbox" name="custom_fields[${fieldIndex}][required]" value="1" checked>
                                <?php _e('This field is required', 'pro-clean-quotation'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th><label><?php _e('Options', 'pro-clean-quotation'); ?></label></th>
                        <td>
                            <div class="pcq-field-options"></div>
                            <button type="button" class="button pcq-add-option" data-field-index="${fieldIndex}">
                                <?php _e('+ Add Option', 'pro-clean-quotation'); ?>
                            </button>
                            <p class="description"><?php _e('Define available options with price modifiers (positive or negative amounts in euros)', 'pro-clean-quotation'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
        `;
        
        $('#pcq-custom-fields-container').append(fieldHtml);
        fieldIndex++;
    });
    
    // Remove custom field
    $(document).on('click', '.pcq-remove-field', function() {
        if (confirm('<?php _e('Are you sure you want to remove this custom field?', 'pro-clean-quotation'); ?>')) {
            $(this).closest('.pcq-custom-field-item').remove();
        }
    });
    
    // Add option to custom field
    $(document).on('click', '.pcq-add-option', function() {
        var fieldIndex = $(this).data('field-index');
        var optionsContainer = $(this).siblings('.pcq-field-options');
        var optionIndex = optionsContainer.find('.pcq-option-row').length;
        
        var optionHtml = `
            <div class="pcq-option-row">
                <input type="text" name="custom_fields[${fieldIndex}][options][${optionIndex}][value]" 
                       value="" placeholder="<?php _e('Value (e.g., flat)', 'pro-clean-quotation'); ?>" class="small-text">
                <input type="text" name="custom_fields[${fieldIndex}][options][${optionIndex}][label]" 
                       value="" placeholder="<?php _e('Label (e.g., Flat Roof)', 'pro-clean-quotation'); ?>" class="regular-text">
                <input type="number" name="custom_fields[${fieldIndex}][options][${optionIndex}][price_modifier]" 
                       value="0" placeholder="0" class="small-text" step="0.01">
                <span class="description">€</span>
                <button type="button" class="button pcq-remove-option">×</button>
            </div>
        `;
        
        optionsContainer.append(optionHtml);
    });
    
    // Remove option
    $(document).on('click', '.pcq-remove-option', function() {
        $(this).closest('.pcq-option-row').remove();
    });
    
    // Update field header label when field label changes
    $(document).on('input', '.pcq-field-label', function() {
        var label = $(this).val() || '<?php _e('Custom Field', 'pro-clean-quotation'); ?>';
        $(this).closest('.pcq-custom-field-item').find('.pcq-custom-field-header h4').text(label);
    });
});
</script>