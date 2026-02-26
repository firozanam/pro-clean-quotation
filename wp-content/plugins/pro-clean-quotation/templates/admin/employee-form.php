<?php
/**
 * Admin Employee Form Template (Add/Edit)
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = $action === 'edit' && $employee && $employee->getId();
$page_title = $is_edit ? __('Edit Employee', 'pro-clean-quotation') : __('Add New Employee', 'pro-clean-quotation');
$button_text = $is_edit ? __('Update Employee', 'pro-clean-quotation') : __('Create Employee', 'pro-clean-quotation');

// Default values
$employee_data = [
    'name' => $is_edit ? $employee->getName() : '',
    'email' => $is_edit ? $employee->getEmail() : '',
    'phone' => $is_edit ? $employee->getPhone() : '',
    'description' => $is_edit ? $employee->getDescription() : '',
    'status' => $is_edit ? $employee->getStatus() : 'active'
];

// Default working hours
$default_working_hours = [
    'monday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
    'tuesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
    'wednesday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
    'thursday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
    'friday' => ['start' => '08:00', 'end' => '18:00', 'enabled' => true],
    'saturday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => true],
    'sunday' => ['start' => '09:00', 'end' => '15:00', 'enabled' => false]
];

// Get working hours safely
$working_hours = $default_working_hours;
if ($is_edit && $employee->getId()) {
    try {
        $employee_working_hours = $employee->getWorkingHours();
        if (is_array($employee_working_hours) && !empty($employee_working_hours)) {
            $working_hours = array_merge($default_working_hours, $employee_working_hours);
        }
    } catch (Exception $e) {
        // Use default working hours if there's an error
        error_log('Employee working hours error: ' . $e->getMessage());
    }
}

// Get all services for assignment
$services = \ProClean\Quotation\Models\Service::getAll(false);
$assigned_services = [];
if ($is_edit && $employee->getId()) {
    global $wpdb;
    $employee_services_table = $wpdb->prefix . 'pq_employee_services';
    $assigned_service_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT service_id FROM $employee_services_table WHERE employee_id = %d",
        $employee->getId()
    ));
    $assigned_services = array_map('intval', $assigned_service_ids);
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-employees'); ?>" class="page-title-action">
        <?php _e('Back to Employees', 'pro-clean-quotation'); ?>
    </a>
    
    <form method="post" action="" class="pcq-employee-form">
        <?php 
        if ($is_edit) {
            wp_nonce_field('pcq_save_employee_' . $employee->getId(), '_wpnonce');
            echo '<input type="hidden" name="employee_id" value="' . $employee->getId() . '">';
        } else {
            wp_nonce_field('pcq_create_employee', '_wpnonce');
        }
        ?>
        <input type="hidden" name="action" value="save_employee">
        
        <div class="pcq-form-container">
            <!-- Main Form -->
            <div class="pcq-main-form">
                <!-- Basic Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Basic Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="name"><?php _e('Full Name', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" name="name" id="name" 
                                       value="<?php echo esc_attr($employee_data['name']); ?>" 
                                       class="regular-text" required>
                                <p class="description"><?php _e('Enter the employee\'s full name.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="email"><?php _e('Email Address', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="email" name="email" id="email" 
                                       value="<?php echo esc_attr($employee_data['email']); ?>" 
                                       class="regular-text">
                                <p class="description"><?php _e('Employee\'s email address for notifications.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="phone"><?php _e('Phone Number', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="tel" name="phone" id="phone" 
                                       value="<?php echo esc_attr($employee_data['phone']); ?>" 
                                       class="regular-text">
                                <p class="description"><?php _e('Employee\'s contact phone number.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="description"><?php _e('Description', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <textarea name="description" id="description" rows="4" class="large-text"><?php echo esc_textarea($employee_data['description']); ?></textarea>
                                <p class="description"><?php _e('Brief description of the employee\'s role and expertise.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Working Hours -->
                <div class="pcq-form-section">
                    <h2><?php _e('Working Hours', 'pro-clean-quotation'); ?></h2>
                    
                    <div class="pcq-working-hours">
                        <?php 
                        $days = [
                            'monday' => __('Monday', 'pro-clean-quotation'),
                            'tuesday' => __('Tuesday', 'pro-clean-quotation'),
                            'wednesday' => __('Wednesday', 'pro-clean-quotation'),
                            'thursday' => __('Thursday', 'pro-clean-quotation'),
                            'friday' => __('Friday', 'pro-clean-quotation'),
                            'saturday' => __('Saturday', 'pro-clean-quotation'),
                            'sunday' => __('Sunday', 'pro-clean-quotation')
                        ];
                        
                        foreach ($days as $day => $label): 
                            $day_data = $working_hours[$day] ?? $default_working_hours[$day];
                        ?>
                        <div class="pcq-working-day">
                            <div class="pcq-day-header">
                                <label>
                                    <input type="checkbox" name="working_hours[<?php echo $day; ?>][enabled]" 
                                           value="1" <?php checked($day_data['enabled']); ?>>
                                    <strong><?php echo esc_html($label); ?></strong>
                                </label>
                            </div>
                            <div class="pcq-day-times">
                                <input type="time" name="working_hours[<?php echo $day; ?>][start]" 
                                       value="<?php echo esc_attr($day_data['start']); ?>" 
                                       class="pcq-time-input">
                                <span><?php _e('to', 'pro-clean-quotation'); ?></span>
                                <input type="time" name="working_hours[<?php echo $day; ?>][end]" 
                                       value="<?php echo esc_attr($day_data['end']); ?>" 
                                       class="pcq-time-input">
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Service Assignment -->
                <div class="pcq-form-section">
                    <h2><?php _e('Service Assignment', 'pro-clean-quotation'); ?></h2>
                    
                    <div class="pcq-services-assignment">
                        <?php if (!empty($services)): ?>
                            <p class="description"><?php _e('Select which services this employee can perform:', 'pro-clean-quotation'); ?></p>
                            
                            <div class="pcq-services-grid">
                                <?php foreach ($services as $service): ?>
                                <label class="pcq-service-checkbox">
                                    <input type="checkbox" name="assigned_services[]" 
                                           value="<?php echo $service->getId(); ?>"
                                           <?php checked(in_array($service->getId(), $assigned_services)); ?>>
                                    <span class="pcq-service-badge" style="background-color: <?php echo esc_attr($service->getColor()); ?>">
                                        <?php echo esc_html($service->getName()); ?>
                                    </span>
                                    <small class="pcq-service-details">
                                        <?php $dur = $service->getDuration(); echo $dur !== null ? $dur . ' min • ' : ''; ?>€<?php echo number_format($service->getPrice(), 2); ?>
                                    </small>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p><?php _e('No services available. Please create services first.', 'pro-clean-quotation'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Sidebar -->
            <div class="pcq-form-sidebar">
                <!-- Status -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Status', 'pro-clean-quotation'); ?></h3>
                    
                    <select name="status" id="status" class="widefat">
                        <option value="active" <?php selected($employee_data['status'], 'active'); ?>><?php _e('Active', 'pro-clean-quotation'); ?></option>
                        <option value="inactive" <?php selected($employee_data['status'], 'inactive'); ?>><?php _e('Inactive', 'pro-clean-quotation'); ?></option>
                    </select>
                    
                    <p class="description"><?php _e('Inactive employees are not available for new appointments.', 'pro-clean-quotation'); ?></p>
                </div>
                
                <!-- Actions -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Actions', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-actions">
                        <input type="submit" name="save" class="button button-primary button-large" 
                               value="<?php echo esc_attr($button_text); ?>">
                        
                        <a href="<?php echo admin_url('admin.php?page=pcq-employees'); ?>" 
                           class="button button-large">
                            <?php _e('Cancel', 'pro-clean-quotation'); ?>
                        </a>
                        
                        <?php if ($is_edit): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-employees&action=delete&id=' . $employee->getId()), 'delete_employee_' . $employee->getId()); ?>" 
                           class="button button-large pcq-delete-btn" 
                           onclick="return confirm('<?php _e('Are you sure you want to delete this employee? This action cannot be undone.', 'pro-clean-quotation'); ?>')">
                            <?php _e('Delete Employee', 'pro-clean-quotation'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if ($is_edit): ?>
                <!-- Information -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-info-list">
                        <div class="pcq-info-item">
                            <strong><?php _e('Created:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($employee->getCreatedAt())); ?>
                        </div>
                        
                        <?php if ($employee->getUpdatedAt()): ?>
                        <div class="pcq-info-item">
                            <strong><?php _e('Last Updated:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($employee->getUpdatedAt())); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="pcq-info-item">
                            <strong><?php _e('Employee ID:', 'pro-clean-quotation'); ?></strong><br>
                            #<?php echo $employee->getId(); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<style>
.pcq-employee-form {
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

.pcq-working-hours {
    display: grid;
    gap: 15px;
}

.pcq-working-day {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
}

.pcq-day-header {
    min-width: 120px;
}

.pcq-day-times {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pcq-time-input {
    width: 80px;
}

.pcq-services-assignment {
    margin-top: 10px;
}

.pcq-services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.pcq-service-checkbox {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.pcq-service-checkbox:hover {
    border-color: #3498db;
    background-color: #f8f9fa;
}

.pcq-service-checkbox input[type="checkbox"] {
    margin: 0;
}

.pcq-service-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 12px;
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    margin-top: 5px;
}

.pcq-service-details {
    color: #666;
    font-size: 12px;
    margin-top: 5px;
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
    
    .pcq-working-day {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .pcq-services-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Toggle working hours inputs based on checkbox
    $('.pcq-working-day input[type="checkbox"]').on('change', function() {
        var timeInputs = $(this).closest('.pcq-working-day').find('.pcq-time-input');
        if ($(this).is(':checked')) {
            timeInputs.prop('disabled', false);
        } else {
            timeInputs.prop('disabled', true);
        }
    }).trigger('change');
    
    // Form validation
    $('form.pcq-employee-form').on('submit', function(e) {
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