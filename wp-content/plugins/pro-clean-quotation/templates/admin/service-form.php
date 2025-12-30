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
    'price' => $is_edit ? $service->getPrice() : 0,
    'capacity' => $is_edit ? $service->getCapacity() : 1,
    'buffer_time_before' => $is_edit ? $service->getBufferTimeBefore() : 15,
    'buffer_time_after' => $is_edit ? $service->getBufferTimeAfter() : 15,
    'color' => $is_edit ? $service->getColor() : '#2196F3',
    'status' => $is_edit ? $service->getStatus() : 'active',
    'sort_order' => $is_edit ? $service->getSortOrder() : 0,
    'min_advance_time' => $is_edit ? $service->getMinAdvanceTime() : 0,
    'max_advance_time' => $is_edit ? $service->getMaxAdvanceTime() : 0
];
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
                                <label for="price"><?php _e('Base Price', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="price" id="price" 
                                       value="<?php echo esc_attr($service_data['price']); ?>" 
                                       class="regular-text" step="0.01" min="0">
                                <span>€</span>
                                <p class="description"><?php _e('Base price for this service. Can be overridden per appointment.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
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
                            <div><strong><?php _e('Price:', 'pro-clean-quotation'); ?></strong> €<span id="preview-price"><?php echo number_format($service_data['price'], 2); ?></span></div>
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

@media (max-width: 768px) {
    .pcq-form-container {
        grid-template-columns: 1fr;
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
    $('#name, #duration, #price, #capacity, #color').on('input change', function() {
        updatePreview();
    });
    
    function updatePreview() {
        var name = $('#name').val() || 'Service Name';
        var duration = $('#duration').val() || '60';
        var price = parseFloat($('#price').val()) || 0;
        var capacity = $('#capacity').val() || '1';
        var color = $('#color').val() || '#2196F3';
        
        $('#preview-name').text(name);
        $('#preview-duration').text(duration);
        $('#preview-price').text(price.toFixed(2));
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
});
</script>