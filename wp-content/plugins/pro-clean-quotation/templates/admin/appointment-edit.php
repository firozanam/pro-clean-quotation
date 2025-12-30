<?php
/**
 * Admin Appointment Edit Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use ProClean\Quotation\Models\Service;
use ProClean\Quotation\Models\Employee;

$services = Service::getAll(false);
$employees = Employee::getAll(false);
$service = $appointment->getService();
$employee = $appointment->getEmployee();
$assigned_employee_ids = $appointment->getEmployeeIds();
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php _e('Edit Appointment', 'pro-clean-quotation'); ?>
        <span class="pcq-appointment-id">#<?php echo $appointment->getId(); ?></span>
    </h1>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=view&id=' . $appointment->getId()); ?>" class="page-title-action">
        <?php _e('View Appointment', 'pro-clean-quotation'); ?>
    </a>
    
    <a href="<?php echo admin_url('admin.php?page=pcq-appointments'); ?>" class="page-title-action">
        <?php _e('Back to Appointments', 'pro-clean-quotation'); ?>
    </a>
    
    <form method="post" action="" class="pcq-appointment-form">
        <?php wp_nonce_field('pcq_save_appointment_' . $appointment->getId()); ?>
        <input type="hidden" name="action" value="save_appointment">
        <input type="hidden" name="appointment_id" value="<?php echo $appointment->getId(); ?>">
        
        <div class="pcq-form-container">
            <!-- Main Form -->
            <div class="pcq-main-form">
                <!-- Service Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Service Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="service_id"><?php _e('Service', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <select name="service_id" id="service_id" class="regular-text" required>
                                    <option value=""><?php _e('Select Service', 'pro-clean-quotation'); ?></option>
                                    <?php foreach ($services as $svc): ?>
                                        <option value="<?php echo $svc->getId(); ?>" 
                                                <?php selected($appointment->getServiceId(), $svc->getId()); ?>
                                                data-duration="<?php echo $svc->getDuration(); ?>"
                                                data-price="<?php echo $svc->getPrice(); ?>">
                                            <?php echo esc_html($svc->getName()); ?>
                                            (<?php echo $svc->getDuration(); ?> min - €<?php echo number_format($svc->getPrice(), 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description"><?php _e('Select the service for this appointment.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="employee_ids"><?php _e('Assigned Team', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <select name="employee_ids[]" id="employee_ids" class="pcq-employee-multiselect" multiple size="6" style="min-height: 150px;">
                                    <option value="0" <?php echo empty($assigned_employee_ids) ? 'selected' : ''; ?>><?php _e('Auto-assign', 'pro-clean-quotation'); ?></option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo $emp->getId(); ?>" 
                                                <?php echo in_array($emp->getId(), $assigned_employee_ids) ? 'selected' : ''; ?>>
                                            <?php echo esc_html($emp->getName()); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    <?php _e('Hold Ctrl (Cmd on Mac) to select multiple team members. Select "Auto-assign" for automatic assignment.', 'pro-clean-quotation'); ?>
                                    <br>
                                    <strong><?php _e('Team Size:', 'pro-clean-quotation'); ?></strong> <span id="pcq-selected-count">0</span> <?php _e('member(s)', 'pro-clean-quotation'); ?>
                                </p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Schedule Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Schedule', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="service_date"><?php _e('Date', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="date" name="service_date" id="service_date" 
                                       value="<?php echo esc_attr($appointment->getServiceDate()); ?>" 
                                       class="regular-text" required>
                                <p class="description"><?php _e('Select the appointment date.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="service_time_start"><?php _e('Start Time', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="time" name="service_time_start" id="service_time_start" 
                                       value="<?php echo esc_attr($appointment->getServiceTimeStart()); ?>" 
                                       class="regular-text" required>
                                <p class="description"><?php _e('Appointment start time.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="service_time_end"><?php _e('End Time', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="time" name="service_time_end" id="service_time_end" 
                                       value="<?php echo esc_attr($appointment->getServiceTimeEnd()); ?>" 
                                       class="regular-text" required>
                                <p class="description"><?php _e('Appointment end time.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="duration"><?php _e('Duration', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="duration" id="duration" 
                                       value="<?php echo esc_attr($appointment->getDuration()); ?>" 
                                       class="small-text" min="15" step="15" readonly>
                                <span><?php _e('minutes', 'pro-clean-quotation'); ?></span>
                                <p class="description"><?php _e('Duration is calculated automatically based on start and end times.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                
                <!-- Customer Information -->
                <div class="pcq-form-section">
                    <h2><?php _e('Customer Information', 'pro-clean-quotation'); ?></h2>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="customer_name"><?php _e('Customer Name', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="text" name="customer_name" id="customer_name" 
                                       value="<?php echo esc_attr($appointment->getCustomerName()); ?>" 
                                       class="regular-text" required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="customer_email"><?php _e('Email', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="email" name="customer_email" id="customer_email" 
                                       value="<?php echo esc_attr($appointment->getCustomerEmail()); ?>" 
                                       class="regular-text" required>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="customer_phone"><?php _e('Phone', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="tel" name="customer_phone" id="customer_phone" 
                                       value="<?php echo esc_attr($appointment->getCustomerPhone()); ?>" 
                                       class="regular-text">
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
                                <label for="price"><?php _e('Price', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <input type="number" name="price" id="price" 
                                       value="<?php echo esc_attr($appointment->getPrice()); ?>" 
                                       class="regular-text" step="0.01" min="0" required>
                                <span>€</span>
                                <p class="description"><?php _e('Total price for this appointment.', 'pro-clean-quotation'); ?></p>
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
                                <textarea name="notes" id="notes" rows="4" class="large-text"><?php echo esc_textarea($appointment->getNotes()); ?></textarea>
                                <p class="description"><?php _e('Notes visible to the customer.', 'pro-clean-quotation'); ?></p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th scope="row">
                                <label for="internal_notes"><?php _e('Internal Notes', 'pro-clean-quotation'); ?></label>
                            </th>
                            <td>
                                <textarea name="internal_notes" id="internal_notes" rows="4" class="large-text"><?php echo esc_textarea($appointment->getInternalNotes()); ?></textarea>
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
                        <option value="pending" <?php selected($appointment->getStatus(), 'pending'); ?>><?php _e('Pending', 'pro-clean-quotation'); ?></option>
                        <option value="confirmed" <?php selected($appointment->getStatus(), 'confirmed'); ?>><?php _e('Confirmed', 'pro-clean-quotation'); ?></option>
                        <option value="in_progress" <?php selected($appointment->getStatus(), 'in_progress'); ?>><?php _e('In Progress', 'pro-clean-quotation'); ?></option>
                        <option value="completed" <?php selected($appointment->getStatus(), 'completed'); ?>><?php _e('Completed', 'pro-clean-quotation'); ?></option>
                        <option value="cancelled" <?php selected($appointment->getStatus(), 'cancelled'); ?>><?php _e('Cancelled', 'pro-clean-quotation'); ?></option>
                        <option value="no_show" <?php selected($appointment->getStatus(), 'no_show'); ?>><?php _e('No Show', 'pro-clean-quotation'); ?></option>
                    </select>
                </div>
                
                <!-- Actions -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Actions', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-actions">
                        <input type="submit" name="save" class="button button-primary button-large" 
                               value="<?php _e('Update Appointment', 'pro-clean-quotation'); ?>">
                        
                        <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=view&id=' . $appointment->getId()); ?>" 
                           class="button button-large">
                            <?php _e('Cancel', 'pro-clean-quotation'); ?>
                        </a>
                        
                        <?php if ($appointment->canBeCancelled()): ?>
                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-appointments&action=cancel&id=' . $appointment->getId()), 'cancel_appointment_' . $appointment->getId()); ?>" 
                           class="button button-large pcq-cancel-btn" 
                           onclick="return confirm('<?php _e('Are you sure you want to cancel this appointment?', 'pro-clean-quotation'); ?>')">
                            <?php _e('Cancel Appointment', 'pro-clean-quotation'); ?>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Info -->
                <div class="pcq-sidebar-section">
                    <h3><?php _e('Information', 'pro-clean-quotation'); ?></h3>
                    
                    <div class="pcq-info-list">
                        <div class="pcq-info-item">
                            <strong><?php _e('Created:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($appointment->getCreatedAt())); ?>
                        </div>
                        
                        <?php if ($appointment->getUpdatedAt()): ?>
                        <div class="pcq-info-item">
                            <strong><?php _e('Last Updated:', 'pro-clean-quotation'); ?></strong><br>
                            <?php echo date('M j, Y \a\t g:i A', strtotime($appointment->getUpdatedAt())); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($appointment->getQuoteId()): ?>
                        <div class="pcq-info-item">
                            <strong><?php _e('Related Quote:', 'pro-clean-quotation'); ?></strong><br>
                            <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $appointment->getQuoteId()); ?>">
                                <?php _e('View Quote', 'pro-clean-quotation'); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.pcq-appointment-form {
    margin-top: 20px;
}

.pcq-appointment-id {
    color: #666;
    font-weight: normal;
    font-size: 0.8em;
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

.pcq-cancel-btn {
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

@media (max-width: 768px) {
    .pcq-form-container {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Auto-calculate duration when times change
    function calculateDuration() {
        var startTime = $('#service_time_start').val();
        var endTime = $('#service_time_end').val();
        
        if (startTime && endTime) {
            var start = new Date('2000-01-01 ' + startTime);
            var end = new Date('2000-01-01 ' + endTime);
            
            if (end > start) {
                var duration = (end - start) / (1000 * 60); // Convert to minutes
                $('#duration').val(duration);
            }
        }
    }
    
    $('#service_time_start, #service_time_end').on('change', calculateDuration);
    
    // Auto-fill end time when service is selected
    $('#service_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var duration = selectedOption.data('duration');
        var price = selectedOption.data('price');
        
        if (duration) {
            var startTime = $('#service_time_start').val();
            if (startTime) {
                var start = new Date('2000-01-01 ' + startTime);
                var end = new Date(start.getTime() + (duration * 60000));
                var endTime = end.toTimeString().substr(0, 5);
                $('#service_time_end').val(endTime);
                $('#duration').val(duration);
            }
        }
        
        if (price && !$('#price').val()) {
            $('#price').val(price);
        }
    });
    
    // Auto-calculate end time when start time changes
    $('#service_time_start').on('change', function() {
        var selectedService = $('#service_id').find('option:selected');
        var duration = selectedService.data('duration');
        
        if (duration) {
            var startTime = $(this).val();
            if (startTime) {
                var start = new Date('2000-01-01 ' + startTime);
                var end = new Date(start.getTime() + (duration * 60000));
                var endTime = end.toTimeString().substr(0, 5);
                $('#service_time_end').val(endTime);
                $('#duration').val(duration);
            }
        }
    });
});
</script>