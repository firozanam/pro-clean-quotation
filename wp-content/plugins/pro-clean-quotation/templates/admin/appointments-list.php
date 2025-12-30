<?php
/**
 * Admin Appointments List Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

use ProClean\Quotation\Models\Service;
use ProClean\Quotation\Models\Employee;

$services = Service::getAll();
$employees = Employee::getAll();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Appointments', 'pro-clean-quotation'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=pcq-calendar'); ?>" class="page-title-action">
        <?php _e('Add New Appointment', 'pro-clean-quotation'); ?>
    </a>
    
    <!-- Filters -->
    <div class="pcq-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="pcq-appointments">
            
            <div class="pcq-filter-row">
                <select name="status">
                    <option value=""><?php _e('All Statuses', 'pro-clean-quotation'); ?></option>
                    <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'pro-clean-quotation'); ?></option>
                    <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>><?php _e('Confirmed', 'pro-clean-quotation'); ?></option>
                    <option value="in_progress" <?php selected($status_filter, 'in_progress'); ?>><?php _e('In Progress', 'pro-clean-quotation'); ?></option>
                    <option value="completed" <?php selected($status_filter, 'completed'); ?>><?php _e('Completed', 'pro-clean-quotation'); ?></option>
                    <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>><?php _e('Cancelled', 'pro-clean-quotation'); ?></option>
                    <option value="no_show" <?php selected($status_filter, 'no_show'); ?>><?php _e('No Show', 'pro-clean-quotation'); ?></option>
                </select>
                
                <select name="service_id">
                    <option value=""><?php _e('All Services', 'pro-clean-quotation'); ?></option>
                    <?php foreach ($services as $service): ?>
                        <option value="<?php echo $service->getId(); ?>" <?php selected($service_filter, $service->getId()); ?>>
                            <?php echo esc_html($service->getName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="employee_id">
                    <option value=""><?php _e('All Employees', 'pro-clean-quotation'); ?></option>
                    <?php foreach ($employees as $employee): ?>
                        <option value="<?php echo $employee->getId(); ?>" <?php selected($employee_filter, $employee->getId()); ?>>
                            <?php echo esc_html($employee->getName()); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="text" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search appointments...', 'pro-clean-quotation'); ?>">
                
                <button type="submit" class="button"><?php _e('Filter', 'pro-clean-quotation'); ?></button>
                
                <?php if (!empty($filters)): ?>
                    <a href="<?php echo admin_url('admin.php?page=pcq-appointments'); ?>" class="button">
                        <?php _e('Clear Filters', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Appointments Table -->
    <div class="pcq-table-container">
        <?php if (!empty($appointments_data['appointments'])): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Customer', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Service', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Employee', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Date & Time', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Duration', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Price', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Status', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments_data['appointments'] as $appointment): ?>
                        <?php 
                        $service = $appointment->getService();
                        $employee = $appointment->getEmployee();
                        $status_class = 'pcq-status-' . $appointment->getStatus();
                        ?>
                        <tr>
                            <td><?php echo $appointment->getId(); ?></td>
                            <td>
                                <strong><?php echo esc_html($appointment->getCustomerName()); ?></strong><br>
                                <small>
                                    <a href="mailto:<?php echo esc_attr($appointment->getCustomerEmail()); ?>">
                                        <?php echo esc_html($appointment->getCustomerEmail()); ?>
                                    </a>
                                    <?php if ($appointment->getCustomerPhone()): ?>
                                        <br><a href="tel:<?php echo esc_attr($appointment->getCustomerPhone()); ?>">
                                            <?php echo esc_html($appointment->getCustomerPhone()); ?>
                                        </a>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <?php if ($service): ?>
                                    <span class="pcq-service-badge" style="background-color: <?php echo esc_attr($service->getColor()); ?>">
                                        <?php echo esc_html($service->getName()); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="pcq-service-badge">
                                        <?php _e('Unknown Service', 'pro-clean-quotation'); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo $employee ? esc_html($employee->getName()) : __('Unassigned', 'pro-clean-quotation'); ?>
                            </td>
                            <td>
                                <strong><?php echo date('M j, Y', strtotime($appointment->getServiceDate())); ?></strong><br>
                                <small><?php echo $appointment->getServiceTimeStart() . ' - ' . $appointment->getServiceTimeEnd(); ?></small>
                            </td>
                            <td>
                                <?php echo $appointment->getDuration(); ?> <?php _e('min', 'pro-clean-quotation'); ?>
                            </td>
                            <td>
                                <strong>€<?php echo number_format($appointment->getPrice(), 2); ?></strong>
                            </td>
                            <td>
                                <span class="pcq-status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $appointment->getStatus())); ?>
                                </span>
                            </td>
                            <td>
                                <div class="pcq-actions-dropdown">
                                    <button type="button" class="pcq-actions-toggle" aria-label="<?php _e('Actions', 'pro-clean-quotation'); ?>">
                                        <span class="pcq-dots">⋯</span>
                                    </button>
                                    <div class="pcq-actions-menu">
                                        <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=view&id=' . $appointment->getId()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">👁</span>
                                            <?php _e('View Details', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <a href="<?php echo admin_url('admin.php?page=pcq-appointments&action=edit&id=' . $appointment->getId()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">✏️</span>
                                            <?php _e('Edit Appointment', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <?php if (in_array($appointment->getStatus(), ['pending'])): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-appointments&action=confirm&id=' . $appointment->getId()), 'confirm_appointment_' . $appointment->getId()); ?>" 
                                           class="pcq-action-item pcq-action-approve">
                                            <span class="pcq-action-icon">✅</span>
                                            <?php _e('Confirm Appointment', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($appointment->getStatus(), ['confirmed'])): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-appointments&action=start&id=' . $appointment->getId()), 'start_appointment_' . $appointment->getId()); ?>" 
                                           class="pcq-action-item pcq-action-primary">
                                            <span class="pcq-action-icon">▶️</span>
                                            <?php _e('Start Service', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if (in_array($appointment->getStatus(), ['in_progress'])): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-appointments&action=complete&id=' . $appointment->getId()), 'complete_appointment_' . $appointment->getId()); ?>" 
                                           class="pcq-action-item pcq-action-approve">
                                            <span class="pcq-action-icon">✅</span>
                                            <?php _e('Mark Complete', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="pcq-action-divider"></div>
                                        
                                        <?php if ($appointment->canBeCancelled()): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-appointments&action=cancel&id=' . $appointment->getId()), 'cancel_appointment_' . $appointment->getId()); ?>" 
                                           class="pcq-action-item pcq-action-danger"
                                           onclick="return confirm('<?php _e('Are you sure you want to cancel this appointment?', 'pro-clean-quotation'); ?>')">
                                            <span class="pcq-action-icon">❌</span>
                                            <?php _e('Cancel Appointment', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <!-- Pagination -->
            <?php if ($appointments_data['pages'] > 1): ?>
                <div class="pcq-pagination">
                    <?php
                    $pagination_args = [
                        'base' => add_query_arg('paged', '%#%'),
                        'format' => '',
                        'prev_text' => __('&laquo; Previous', 'pro-clean-quotation'),
                        'next_text' => __('Next &raquo;', 'pro-clean-quotation'),
                        'total' => $appointments_data['pages'],
                        'current' => $appointments_data['current_page']
                    ];
                    echo paginate_links($pagination_args);
                    ?>
                </div>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="pcq-no-results">
                <p><?php _e('No appointments found.', 'pro-clean-quotation'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=pcq-calendar'); ?>" class="button button-primary">
                    <?php _e('Create First Appointment', 'pro-clean-quotation'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pcq-filters {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 15px;
    margin: 20px 0;
}

.pcq-filter-row {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.pcq-filter-row select,
.pcq-filter-row input[type="text"] {
    padding: 8px 32px 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 150px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background: #fff url('data:image/svg+xml;charset=UTF-8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 12 12"><path fill="%23666" d="M6 9L1 4h10z"/></svg>') no-repeat right 10px center;
    background-size: 12px;
}

.pcq-filter-row input[type="text"] {
    background: #fff;
    padding: 8px 12px;
}

.pcq-filter-row select option {
    white-space: normal;
}

.pcq-table-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow: hidden;
    overflow-x: auto;
}

/* Table layout fixes */
.pcq-table-container table {
    table-layout: fixed;
    width: 100%;
}

/* Column widths to prevent overlapping */
.pcq-table-container table thead th:nth-child(1),
.pcq-table-container table tbody td:nth-child(1) {
    width: 40px;
}

.pcq-table-container table thead th:nth-child(2),
.pcq-table-container table tbody td:nth-child(2) {
    width: 180px;
}

.pcq-table-container table thead th:nth-child(3),
.pcq-table-container table tbody td:nth-child(3) {
    width: 160px;
    max-width: 160px;
}

.pcq-table-container table thead th:nth-child(4),
.pcq-table-container table tbody td:nth-child(4) {
    width: 140px;
}

.pcq-table-container table thead th:nth-child(5),
.pcq-table-container table tbody td:nth-child(5) {
    width: 140px;
}

.pcq-table-container table thead th:nth-child(6),
.pcq-table-container table tbody td:nth-child(6) {
    width: 80px;
}

.pcq-table-container table thead th:nth-child(7),
.pcq-table-container table tbody td:nth-child(7) {
    width: 90px;
}

.pcq-table-container table thead th:nth-child(8),
.pcq-table-container table tbody td:nth-child(8) {
    width: 100px;
}

.pcq-table-container table thead th:nth-child(9),
.pcq-table-container table tbody td:nth-child(9) {
    width: 60px;
}

/* Ensure table headers don't wrap */
.pcq-table-container table thead th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Ensure table cells don't wrap unnecessarily */
.pcq-table-container table tbody td {
    white-space: normal;
}

/* Specific cells that should never wrap */
.pcq-table-container table tbody td:nth-child(1),
.pcq-table-container table tbody td:nth-child(5),
.pcq-table-container table tbody td:nth-child(6),
.pcq-table-container table tbody td:nth-child(7),
.pcq-table-container table tbody td:nth-child(8) {
    white-space: nowrap;
}

/* Customer and Employee columns can wrap if needed */
.pcq-table-container table tbody td:nth-child(2) strong,
.pcq-table-container table tbody td:nth-child(4) {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
}

.pcq-service-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    color: #fff;
    font-size: 12px;
    font-weight: 500;
    background-color: #2196F3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 145px;
    vertical-align: middle;
}

.pcq-status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
    white-space: nowrap;
}

.pcq-status-pending { background-color: #ff9800; color: #fff; }
.pcq-status-confirmed { background-color: #4caf50; color: #fff; }
.pcq-status-in_progress { background-color: #2196f3; color: #fff; }
.pcq-status-completed { background-color: #8bc34a; color: #fff; }
.pcq-status-cancelled { background-color: #f44336; color: #fff; }
.pcq-status-no_show { background-color: #9e9e9e; color: #fff; }

/* Actions Dropdown Styles */
.pcq-actions-dropdown {
    position: relative;
    display: inline-block;
}

.pcq-actions-toggle {
    background: #f6f7f7;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 6px 10px;
    cursor: pointer;
    font-size: 16px;
    line-height: 1;
    transition: all 0.2s ease;
}

.pcq-actions-toggle:hover {
    background: #e8e9ea;
    border-color: #999;
}

.pcq-actions-toggle:focus {
    outline: 2px solid #2271b1;
    outline-offset: 1px;
}

.pcq-dots {
    display: inline-block;
    font-weight: bold;
    color: #666;
}

.pcq-actions-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    min-width: 180px;
    z-index: 1000;
    display: none;
    padding: 4px 0;
}

.pcq-actions-dropdown.active .pcq-actions-menu {
    display: block;
}

.pcq-action-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    text-decoration: none;
    color: #2c3e50;
    font-size: 13px;
    transition: background-color 0.2s ease;
    border: none;
    background: none;
    text-align: left;
    cursor: pointer;
}

.pcq-action-item:hover {
    background-color: #f0f0f1;
    color: #2c3e50;
}

.pcq-action-item:focus {
    background-color: #f0f0f1;
    color: #2c3e50;
    outline: none;
    box-shadow: inset 0 0 0 1px #2271b1;
}

.pcq-action-icon {
    font-size: 14px;
    width: 16px;
    text-align: center;
}

.pcq-action-primary:hover {
    background-color: #e7f3ff;
    color: #0073aa;
}

.pcq-action-approve:hover {
    background-color: #f0f6ff;
    color: #00a32a;
}

.pcq-action-danger:hover {
    background-color: #fcf0f1;
    color: #d63638;
}

.pcq-action-divider {
    height: 1px;
    background-color: #e0e0e0;
    margin: 4px 0;
}

.pcq-actions {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.pcq-cancel-btn {
    background-color: #f44336 !important;
    color: #fff !important;
    border-color: #f44336 !important;
}

.pcq-no-results {
    text-align: center;
    padding: 40px 20px;
}

.pcq-pagination {
    padding: 15px;
    text-align: center;
    border-top: 1px solid #ddd;
}

@media (max-width: 768px) {
    .pcq-filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pcq-actions {
        flex-direction: column;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle dropdown toggles
    const dropdownToggles = document.querySelectorAll('.pcq-actions-toggle');
    
    dropdownToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const dropdown = this.closest('.pcq-actions-dropdown');
            const isActive = dropdown.classList.contains('active');
            
            // Close all other dropdowns
            document.querySelectorAll('.pcq-actions-dropdown.active').forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('active');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('active', !isActive);
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.pcq-actions-dropdown')) {
            document.querySelectorAll('.pcq-actions-dropdown.active').forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    });
    
    // Handle keyboard navigation
    document.addEventListener('keydown', function(e) {
        const activeDropdown = document.querySelector('.pcq-actions-dropdown.active');
        if (!activeDropdown) return;
        
        const menuItems = activeDropdown.querySelectorAll('.pcq-action-item');
        const currentFocus = document.activeElement;
        const currentIndex = Array.from(menuItems).indexOf(currentFocus);
        
        switch(e.key) {
            case 'Escape':
                activeDropdown.classList.remove('active');
                activeDropdown.querySelector('.pcq-actions-toggle').focus();
                break;
            case 'ArrowDown':
                e.preventDefault();
                const nextIndex = currentIndex < menuItems.length - 1 ? currentIndex + 1 : 0;
                menuItems[nextIndex].focus();
                break;
            case 'ArrowUp':
                e.preventDefault();
                const prevIndex = currentIndex > 0 ? currentIndex - 1 : menuItems.length - 1;
                menuItems[prevIndex].focus();
                break;
        }
    });
});
</script>