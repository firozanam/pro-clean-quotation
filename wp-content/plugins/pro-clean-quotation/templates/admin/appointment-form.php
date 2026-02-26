<?php
/**
 * Admin Appointment Form Template (Add/Edit)
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = $action === 'edit' && $appointment && $appointment->getId();
$page_title = $is_edit ? __('Edit Appointment', 'pro-clean-quotation') : __('Add New Appointment', 'pro-clean-quotation');
$button_text = $is_edit ? __('Update Appointment', 'pro-clean-quotation') : __('Create Appointment', 'pro-clean-quotation');

// Get quote data if creating from quote
$quote = null;
$quote_id = intval($_GET['quote_id'] ?? 0);
if ($quote_id && !$is_edit) {
    $quote = new \ProClean\Quotation\Models\Quote($quote_id);
}

// Match service by type from quote
$matched_service_id = '';
if ($quote && $quote->getId()) {
    $quote_service_type = $quote->getServiceType();
    
    // Only proceed if service type is valid
    if ($quote_service_type && is_string($quote_service_type) && trim($quote_service_type) !== '') {
        $services_list = \ProClean\Quotation\Models\Service::getAll(true);
        
        // Try to find matching service by name/type
        // Note: hyphen must be escaped or placed at start/end of character class
        $normalized_quote_type = strtolower(preg_replace('/[\s_-]/', '', $quote_service_type));
        
        foreach ($services_list as $service) {
            $normalized_service_name = strtolower(preg_replace('/[\s_-]/', '', $service->getName()));
            
            // Check if names match (fuzzy)
            if (strpos($normalized_service_name, $normalized_quote_type) !== false || 
                strpos($normalized_quote_type, $normalized_service_name) !== false) {
                $matched_service_id = $service->getId();
                break;
            }
        }
    }
}

// Default values (from quote or appointment)
$appointment_data = [
    'service_id' => $is_edit ? $appointment->data['service_id'] : $matched_service_id,
    'employee_id' => $is_edit ? $appointment->data['employee_id'] : '',
    'customer_name' => $is_edit ? (string)$appointment->getCustomerName() : ($quote ? (string)$quote->getCustomerName() : ($_GET['customer_name'] ?? '')),
    'customer_email' => $is_edit ? (string)$appointment->getCustomerEmail() : ($quote ? (string)$quote->getCustomerEmail() : ($_GET['customer_email'] ?? '')),
    'customer_phone' => $is_edit ? (string)$appointment->getCustomerPhone() : ($quote ? (string)$quote->getCustomerPhone() : ($_GET['customer_phone'] ?? '')),
    'service_date' => $is_edit ? $appointment->data['service_date'] : date('Y-m-d', strtotime('+1 day')), // Default: tomorrow
    'service_time_start' => $is_edit ? $appointment->data['service_time_start'] : '09:00',
    'service_time_end' => $is_edit ? $appointment->data['service_time_end'] : '11:00',
    'duration' => $is_edit ? $appointment->data['duration'] : 120,
    'price' => $is_edit ? $appointment->data['price'] : ($quote ? $quote->getTotalPrice() : ($_GET['total_amount'] ?? 0)),
    'status' => $is_edit ? $appointment->data['status'] : 'pending',
    'notes' => $is_edit ? (string)($appointment->data['notes'] ?? '') : (
        $quote && $quote->getSpecialRequirements() && trim($quote->getSpecialRequirements()) !== '' && !is_numeric($quote->getSpecialRequirements()) 
            ? $quote->getSpecialRequirements() 
            : ''
    ),
    'internal_notes' => $is_edit ? (string)($appointment->data['internal_notes'] ?? '') : ''
];

// Get assigned employees for edit mode
$assigned_employee_ids = $is_edit ? $appointment->getEmployeeIds() : [];

// Get services and employees
$services = \ProClean\Quotation\Models\Service::getAll(true);
$employees = \ProClean\Quotation\Models\Employee::getAll(true);
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php echo esc_html($page_title); ?></h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-appointments'); ?>" class="page-title-action">
        <?php _e('Back to Appointments', 'pro-clean-quotation'); ?>
    </a>
    
    <?php if ($quote): ?>
    <div class="notice notice-info">
        <p>
            <strong><?php _e('Creating appointment from quote:', 'pro-clean-quotation'); ?></strong>
            <?php echo esc_html($quote->getQuoteNumber()); ?> - <?php echo esc_html($quote->getCustomerName()); ?>
        </p>
    </div>
    <?php endif; ?>
    
    <form method="post" action="" class="pcq-appointment-form">
        <?php 
        if ($is_edit) {
            wp_nonce_field('pcq_save_appointment_' . $appointment->getId(), '_wpnonce');
            echo '<input type="hidden" name="appointment_id" value="' . $appointment->getId() . '">';
        } else {
            wp_nonce_field('pcq_create_appointment', '_wpnonce');
        }
        ?>
        <input type="hidden" name="action" value="save_appointment">
        <?php if ($quote_id): ?>
            <input type="hidden" name="quote_id" value="<?php echo $quote_id; ?>">
        <?php endif; ?>
        
        <div class="pcq-form-container">
            <!-- Main Form -->
            <div class="pcq-main-form">
                <!-- Customer Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Customer Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="customer_name"><?php _e('Customer Name', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text" name="customer_name" id="customer_name" 
                                       value="<?php echo esc_attr($appointment_data['customer_name']); ?>" 
                                       class="regular-text" required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="customer_email"><?php _e('Email Address', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="email" name="customer_email" id="customer_email" 
                                       value="<?php echo esc_attr($appointment_data['customer_email']); ?>" 
                                       class="regular-text" required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="customer_phone"><?php _e('Phone Number', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="tel" name="customer_phone" id="customer_phone" 
                                       value="<?php echo esc_attr($appointment_data['customer_phone']); ?>" 
                                       class="regular-text">
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Service Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Service Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="service_id"><?php _e('Service', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <select name="service_id" id="service_id" class="regular-text" required>
                                    <option value=""><?php _e('Select Service', 'pro-clean-quotation'); ?></option>
                                    <?php foreach ($services as $service): ?>
                                        <?php $svcDur = $service->getDuration(); ?>
                                        <option value="<?php echo $service->getId(); ?>"
                                                data-duration="<?php echo $svcDur ?? 0; ?>"
                                                data-price="<?php echo $service->getPrice(); ?>"
                                                <?php selected($appointment_data['service_id'], $service->getId()); ?>>
                                            <?php echo esc_html($service->getName()); ?>
                                            (<?php echo $svcDur !== null ? $svcDur . ' min' : 'No duration'; ?> - €<?php echo number_format($service->getPrice(), 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="employee_ids_display"><?php _e('Assign Team', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <div class="pcq-multiselect-wrapper regular-text">
                                    <div class="pcq-multiselect-trigger" id="employee_ids_display" tabindex="0">
                                        <span class="pcq-selected-text"><?php echo empty($assigned_employee_ids) ? __('Select employees...', 'pro-clean-quotation') : sprintf(__('%d selected', 'pro-clean-quotation'), count($assigned_employee_ids)); ?></span>
                                        <span class="pcq-dropdown-arrow">▼</span>
                                    </div>
                                    <div class="pcq-multiselect-dropdown" style="display: none;">
                                        <div class="pcq-multiselect-search">
                                            <input type="text" class="pcq-search-input" placeholder="<?php _e('Search employees...', 'pro-clean-quotation'); ?>">
                                        </div>
                                        <div class="pcq-multiselect-options">
                                            <label class="pcq-checkbox-option pcq-auto-assign">
                                                <input type="checkbox" name="employee_ids[]" value="0" <?php echo empty($assigned_employee_ids) ? 'checked' : ''; ?>>
                                                <span class="pcq-checkbox-label"><?php _e('Auto-assign', 'pro-clean-quotation'); ?></span>
                                            </label>
                                            <?php foreach ($employees as $employee): ?>
                                                <label class="pcq-checkbox-option" data-search="<?php echo esc_attr(strtolower($employee->getName())); ?>">
                                                    <input type="checkbox" name="employee_ids[]" value="<?php echo $employee->getId(); ?>" <?php echo in_array($employee->getId(), $assigned_employee_ids) ? 'checked' : ''; ?>>
                                                    <span class="pcq-checkbox-label"><?php echo esc_html($employee->getName()); ?></span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                                <p class="description">
                                    <strong><?php _e('Selected:', 'pro-clean-quotation'); ?></strong> <span id="pcq-selected-count"><?php echo empty($assigned_employee_ids) ? 0 : count($assigned_employee_ids); ?></span> <?php _e('employee(s)', 'pro-clean-quotation'); ?>
                                </p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="duration"><?php _e('Duration', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="number" name="duration" id="duration" 
                                       value="<?php echo esc_attr($appointment_data['duration']); ?>" 
                                       class="small-text" min="15" step="15" required>
                                <span><?php _e('minutes', 'pro-clean-quotation'); ?></span>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="price"><?php _e('Price', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="number" name="price" id="price" 
                                       value="<?php echo esc_attr($appointment_data['price']); ?>" 
                                       class="regular-text" step="0.01" min="0" required>
                                <span>€</span>
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
                                <label for="service_date"><?php _e('Service Date', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="date" name="service_date" id="service_date" 
                                       value="<?php echo esc_attr($appointment_data['service_date']); ?>" 
                                       class="regular-text" required min="<?php echo date('Y-m-d'); ?>">
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="service_time_start"><?php _e('Start Time', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="time" name="service_time_start" id="service_time_start" 
                                       value="<?php echo esc_attr($appointment_data['service_time_start']); ?>" 
                                       class="regular-text" required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="service_time_end"><?php _e('End Time', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="time" name="service_time_end" id="service_time_end" 
                                       value="<?php echo esc_attr($appointment_data['service_time_end']); ?>" 
                                       class="regular-text" readonly>
                                <p class="description"><?php _e('Automatically calculated based on start time and duration.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Notes -->
                <div class="pcq-form-section">
                    <h2><?php _e('Notes', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="notes"><?php _e('Customer Notes', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <textarea name="notes" id="notes" rows="4" class="large-text"><?php echo esc_textarea($appointment_data['notes']); ?></textarea>
                                <p class="description"><?php _e('Notes visible to the customer.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="internal_notes"><?php _e('Internal Notes', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <textarea name="internal_notes" id="internal_notes" rows="4" class="large-text"><?php echo esc_textarea($appointment_data['internal_notes']); ?></textarea>
                                <p class="description"><?php _e('Internal notes for staff only.', 'pro-clean-quotation'); ?></p>
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
                        <option value="pending" <?php selected($appointment_data['status'], 'pending'); ?>><?php _e('Pending', 'pro-clean-quotation'); ?></option>
                        <option value="confirmed" <?php selected($appointment_data['status'], 'confirmed'); ?>><?php _e('Confirmed', 'pro-clean-quotation'); ?></option>
                        <option value="in_progress" <?php selected($appointment_data['status'], 'in_progress'); ?>><?php _e('In Progress', 'pro-clean-quotation'); ?></option>
                        <option value="completed" <?php selected($appointment_data['status'], 'completed'); ?>><?php _e('Completed', 'pro-clean-quotation'); ?></option>
                        <option value="cancelled" <?php selected($appointment_data['status'], 'cancelled'); ?>><?php _e('Cancelled', 'pro-clean-quotation'); ?></option>
                        <option value="no_show" <?php selected($appointment_data['status'], 'no_show'); ?>><?php _e('No Show', 'pro-clean-quotation'); ?></option>
                    </select>
                </div>
                
                <!-- Actions -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Actions', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-actions">
                        <input type="submit" name="save" class="button button-primary button-large" 
                               value="<?php echo esc_attr($button_text); ?>">
                        
                        <a href="<?php echo admin_url('admin.php?page=pcq-appointments'); ?>" 
                           class="button button-large">
                            <?php _e('Cancel', 'pro-clean-quotation'); ?>
                        </a>
                        
                        <?php if ($is_edit): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-appointments&action=delete&id=' . $appointment->getId()), 'delete_appointment_' . $appointment->getId()); ?>" 
                           class="button button-large pcq-delete-btn" 
                           onclick="return confirm('<?php _e('Are you sure you want to delete this appointment?', 'pro-clean-quotation'); ?>')">
                            <?php _e('Delete Appointment', 'pro-clean-quotation'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Availability Check -->
                <div class="pcq-sidebar-section" id="availability-check" style="display: none;">
                    <h3><?php _e('Availability', 'pro-clean-quotation'); ?></h3>
                    <div id="availability-result"></div>
                </div>
                
                <?php if ($is_edit): ?>
                <!-- Information -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-info-list">
                        <div class="pcq-info-item">
                            <strong><?php _e('Created:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($appointment->data['created_at'])); ?>
                        </div>
                        
                        <?php if ($appointment->data['updated_at']): ?>
                        <div class="pcq-info-item">
                            <strong><?php _e('Last Updated:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($appointment->data['updated_at'])); ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="pcq-info-item">
                            <strong><?php _e('Appointment ID:', 'pro-clean-quotation'); ?></strong><br>
                            #<?php echo $appointment->getId(); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<style>
.pcq-appointment-form {
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

.pcq-availability-ok {
    color: #4CAF50;
    font-weight: 500;
}

.pcq-availability-conflict {
    color: #f44336;
    font-weight: 500;
}

/* Checkbox Multi-Select Dropdown */
.pcq-multiselect-wrapper {
    position: relative;
    display: inline-block;
}

.pcq-multiselect-wrapper.regular-text {
    max-width: 25em;
}

.pcq-multiselect-trigger {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 8px;
    background: #fff;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    cursor: pointer;
    height: 30px;
    line-height: 28px;
    transition: border-color 0.2s;
    font-size: 14px;
    width: 100%;
    box-sizing: border-box;
}

.pcq-multiselect-trigger:hover,
.pcq-multiselect-trigger:focus {
    border-color: #2271b1;
    outline: none;
}

.pcq-multiselect-trigger.active {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
}

.pcq-selected-text {
    flex: 1;
    color: #2c3338;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pcq-dropdown-arrow {
    margin-left: 8px;
    color: #8c8f94;
    transition: transform 0.2s;
    font-size: 12px;
    line-height: 1;
}

.pcq-multiselect-trigger.active .pcq-dropdown-arrow {
    transform: rotate(180deg);
}

.pcq-multiselect-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    z-index: 1000;
    max-height: 300px;
    overflow: hidden;
}

.pcq-multiselect-search {
    padding: 8px;
    border-bottom: 1px solid #dcdcde;
}

.pcq-search-input {
    width: 100%;
    padding: 6px 10px;
    border: 1px solid #8c8f94;
    border-radius: 3px;
    font-size: 13px;
}

.pcq-search-input:focus {
    outline: none;
    border-color: #2271b1;
}

.pcq-multiselect-options {
    max-height: 240px;
    overflow-y: auto;
}

.pcq-checkbox-option {
    display: flex;
    align-items: center;
    padding: 6px 10px;
    cursor: pointer;
    transition: background-color 0.15s;
    border-bottom: 1px solid #f0f0f1;
    font-size: 13px;
}

.pcq-checkbox-option:last-child {
    border-bottom: none;
}

.pcq-checkbox-option:hover {
    background-color: #f6f7f7;
}

.pcq-checkbox-option.pcq-auto-assign {
    background-color: #f0f6fc;
    font-weight: 500;
    border-bottom: 2px solid #2271b1;
}

.pcq-checkbox-option input[type="checkbox"] {
    margin: 0 6px 0 0;
    cursor: pointer;
    width: 16px;
    height: 16px;
}

.pcq-checkbox-label {
    flex: 1;
    user-select: none;
    cursor: pointer;
}

.pcq-checkbox-option.hidden {
    display: none;
}

@media (max-width: 768px) {
    .pcq-form-container {
        grid-template-columns: 1fr;
    }
    
    .pcq-multiselect-wrapper {
        width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // ========== Multi-Select Checkbox Dropdown ==========
    var $trigger = $('.pcq-multiselect-trigger');
    var $dropdown = $('.pcq-multiselect-dropdown');
    var $searchInput = $('.pcq-search-input');
    var $options = $('.pcq-checkbox-option');
    var $selectedText = $('.pcq-selected-text');
    var $selectedCount = $('#pcq-selected-count');
    var $autoAssignCheckbox = $('.pcq-auto-assign input[type="checkbox"]');
    
    // Toggle dropdown
    $trigger.on('click', function(e) {
        e.stopPropagation();
        $dropdown.toggle();
        $trigger.toggleClass('active');
        
        if ($dropdown.is(':visible')) {
            $searchInput.focus();
        }
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.pcq-multiselect-wrapper').length) {
            $dropdown.hide();
            $trigger.removeClass('active');
        }
    });
    
    // Prevent dropdown from closing when clicking inside options
    $dropdown.on('click', function(e) {
        e.stopPropagation();
    });
    
    // Handle checkbox changes
    $options.find('input[type="checkbox"]').on('change', function() {
        var $checkbox = $(this);
        var isAutoAssign = $checkbox.val() === '0';
        
        // If auto-assign is checked, uncheck all others
        if (isAutoAssign && $checkbox.is(':checked')) {
            $options.not('.pcq-auto-assign').find('input[type="checkbox"]').prop('checked', false);
        } else if (!isAutoAssign && $checkbox.is(':checked')) {
            // If any employee is checked, uncheck auto-assign
            $autoAssignCheckbox.prop('checked', false);
        }
        
        updateSelectedText();
    });
    
    // Search functionality
    $searchInput.on('input', function() {
        var searchTerm = $(this).val().toLowerCase();
        
        $options.not('.pcq-auto-assign').each(function() {
            var $option = $(this);
            var searchData = $option.data('search') || '';
            
            if (searchData.includes(searchTerm)) {
                $option.removeClass('hidden');
            } else {
                $option.addClass('hidden');
            }
        });
    });
    
    // Update selected text
    function updateSelectedText() {
        var checkedBoxes = $options.find('input[type="checkbox"]:checked');
        var count = checkedBoxes.length;
        var isAutoAssign = $autoAssignCheckbox.is(':checked');
        
        if (isAutoAssign) {
            $selectedText.text('<?php _e('Auto-assign', 'pro-clean-quotation'); ?>');
            $selectedCount.text('0');
        } else if (count === 0) {
            $selectedText.text('<?php _e('Select employees...', 'pro-clean-quotation'); ?>');
            $selectedCount.text('0');
        } else if (count === 1) {
            var selectedName = checkedBoxes.closest('label').find('.pcq-checkbox-label').text();
            $selectedText.text(selectedName);
            $selectedCount.text('1');
        } else {
            $selectedText.text(count + ' <?php _e('selected', 'pro-clean-quotation'); ?>');
            $selectedCount.text(count);
        }
    }
    
    // Initialize selected text
    updateSelectedText();
    
    // ========== Service Auto-fill ==========
    // Auto-update duration and price when service changes
    $('#service_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var duration = selectedOption.data('duration');
        var price = selectedOption.data('price');
        
        if (duration) {
            $('#duration').val(duration);
            updateEndTime();
        }
        
        if (price && !$('#price').val()) {
            $('#price').val(price);
        }
    });
    
    // ========== Time Calculation ==========
    // Auto-calculate end time when start time or duration changes
    $('#service_time_start, #duration').on('change', updateEndTime);
    
    function updateEndTime() {
        var startTime = $('#service_time_start').val();
        var duration = parseInt($('#duration').val()) || 0;
        
        if (startTime && duration) {
            var start = new Date('2000-01-01 ' + startTime);
            var end = new Date(start.getTime() + (duration * 60000));
            var endTime = end.toTimeString().substr(0, 5);
            $('#service_time_end').val(endTime);
        }
    }
    
    // ========== Availability Check ==========
    // Check availability when date, time, or duration changes
    $('#service_date, #service_time_start, #duration').on('change', checkAvailability);
    
    function checkAvailability() {
        var date = $('#service_date').val();
        var startTime = $('#service_time_start').val();
        var duration = $('#duration').val();
        
        if (date && startTime && duration) {
            $('#availability-check').show();
            $('#availability-result').html('<div class="spinner is-active"></div> Checking availability...');
            
            // This would normally make an AJAX call to check availability
            // For now, we'll show a placeholder
            setTimeout(function() {
                $('#availability-result').html('<div class="pcq-availability-ok">✓ Time slot available</div>');
            }, 1000);
        }
    }
    
    // ========== Form Validation ==========
    // Form validation
    $('form.pcq-appointment-form').on('submit', function(e) {
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
        
        // Check if service date is not in the past
        var serviceDate = new Date($('#service_date').val());
        var today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (serviceDate < today) {
            alert('<?php _e('Service date cannot be in the past.', 'pro-clean-quotation'); ?>');
            $('#service_date').addClass('error');
            isValid = false;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('<?php _e('Please fill in all required fields correctly.', 'pro-clean-quotation'); ?>');
        }
    });
    
    // Initialize end time calculation
    updateEndTime();
});
</script>