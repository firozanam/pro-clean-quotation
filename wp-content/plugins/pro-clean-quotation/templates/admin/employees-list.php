<?php
/**
 * Admin Employees List Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Employees', 'pro-clean-quotation'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=pcq-employees&action=add'); ?>" class="page-title-action">
        <?php _e('Add New Employee', 'pro-clean-quotation'); ?>
    </a>
    
    <div class="pcq-table-container">
        <?php if (!empty($employees)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 60px;"><?php _e('ID', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Employee', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Contact Info', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Services', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Working Hours', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Status', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                        <?php 
                        $services = $employee->getServices();
                        $working_hours = $employee->getWorkingHours();
                        ?>
                        <tr>
                            <td><?php echo $employee->getId(); ?></td>
                            <td>
                                <div class="pcq-employee-info">
                                    <?php if ($employee->getAvatarUrl()): ?>
                                        <img src="<?php echo esc_url($employee->getAvatarUrl()); ?>" 
                                             alt="<?php echo esc_attr($employee->getName()); ?>" 
                                             class="pcq-employee-avatar">
                                    <?php else: ?>
                                        <div class="pcq-employee-avatar pcq-avatar-placeholder">
                                            <?php echo strtoupper(substr($employee->getName(), 0, 2)); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="pcq-employee-details">
                                        <strong><?php echo esc_html($employee->getName()); ?></strong>
                                        <?php if ($employee->getDescription()): ?>
                                            <div class="pcq-employee-description">
                                                <?php echo esc_html($employee->getDescription()); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if ($employee->getEmail()): ?>
                                    <div>
                                        <a href="mailto:<?php echo esc_attr($employee->getEmail()); ?>">
                                            <?php echo esc_html($employee->getEmail()); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($employee->getPhone()): ?>
                                    <div>
                                        <a href="tel:<?php echo esc_attr($employee->getPhone()); ?>">
                                            <?php echo esc_html($employee->getPhone()); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!$employee->getEmail() && !$employee->getPhone()): ?>
                                    <span class="pcq-no-contact"><?php _e('No contact info', 'pro-clean-quotation'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($services)): ?>
                                    <div class="pcq-services-list">
                                        <?php foreach ($services as $service): ?>
                                            <span class="pcq-service-tag" style="background-color: <?php echo esc_attr($service->getColor()); ?>">
                                                <?php echo esc_html($service->getName()); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="pcq-no-services"><?php _e('No services assigned', 'pro-clean-quotation'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($working_hours): ?>
                                    <div class="pcq-working-hours">
                                        <?php 
                                        $days_with_hours = array_filter($working_hours, function($day) {
                                            return $day['enabled'] ?? false;
                                        });
                                        
                                        if (!empty($days_with_hours)): ?>
                                            <div class="pcq-hours-summary">
                                                <?php 
                                                $first_day = reset($days_with_hours);
                                                echo $first_day['start'] . ' - ' . $first_day['end'];
                                                ?>
                                                <?php if (count($days_with_hours) > 1): ?>
                                                    <small>(<?php echo count($days_with_hours); ?> <?php _e('days', 'pro-clean-quotation'); ?>)</small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="pcq-no-hours"><?php _e('No working hours set', 'pro-clean-quotation'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="pcq-no-hours"><?php _e('No working hours set', 'pro-clean-quotation'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="pcq-status-badge pcq-status-<?php echo $employee->getStatus(); ?>">
                                    <?php echo ucfirst($employee->getStatus()); ?>
                                </span>
                            </td>
                            <td>
                                <div class="pcq-actions-dropdown">
                                    <button type="button" class="pcq-actions-toggle" aria-label="<?php _e('Actions', 'pro-clean-quotation'); ?>">
                                        <span class="pcq-dots">⋯</span>
                                    </button>
                                    <div class="pcq-actions-menu">
                                        <a href="<?php echo admin_url('admin.php?page=pcq-employees&action=edit&id=' . $employee->getId()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">✏️</span>
                                            <?php _e('Edit Employee', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <a href="<?php echo admin_url('admin.php?page=pcq-calendar&employee=' . $employee->getId()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">📅</span>
                                            <?php _e('View Schedule', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <?php if ($employee->getStatus() === 'active'): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-employees&action=deactivate&id=' . $employee->getId()), 'deactivate_employee_' . $employee->getId()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">⏸️</span>
                                            <?php _e('Deactivate', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php else: ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-employees&action=activate&id=' . $employee->getId()), 'activate_employee_' . $employee->getId()); ?>" 
                                           class="pcq-action-item pcq-action-approve">
                                            <span class="pcq-action-icon">▶️</span>
                                            <?php _e('Activate', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="pcq-action-divider"></div>
                                        
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-employees&action=delete&id=' . $employee->getId()), 'delete_employee_' . $employee->getId()); ?>" 
                                           class="pcq-action-item pcq-action-danger" 
                                           onclick="return confirm('<?php _e('Are you sure you want to delete this employee? This action cannot be undone.', 'pro-clean-quotation'); ?>')">
                                            <span class="pcq-action-icon">🗑️</span>
                                            <?php _e('Delete Employee', 'pro-clean-quotation'); ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
        <?php else: ?>
            <div class="pcq-no-results">
                <p><?php _e('No employees found.', 'pro-clean-quotation'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=pcq-employees&action=add'); ?>" class="button button-primary">
                    <?php _e('Add First Employee', 'pro-clean-quotation'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pcq-table-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow: hidden;
    margin-top: 20px;
}

.pcq-employee-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.pcq-employee-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.pcq-avatar-placeholder {
    background-color: #2196F3;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.pcq-employee-details {
    flex: 1;
}

.pcq-employee-description {
    font-size: 13px;
    color: #666;
    margin-top: 2px;
}

.pcq-services-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.pcq-service-tag {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 10px;
    color: #fff;
    font-size: 11px;
    font-weight: 500;
}

.pcq-working-hours {
    font-size: 13px;
}

.pcq-hours-summary {
    font-weight: 500;
}

.pcq-no-contact,
.pcq-no-services,
.pcq-no-hours {
    color: #999;
    font-style: italic;
    font-size: 13px;
}

.pcq-status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: capitalize;
}

.pcq-status-active { 
    background-color: #4caf50; 
    color: #fff; 
}

.pcq-status-inactive { 
    background-color: #9e9e9e; 
    color: #fff; 
}

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

.pcq-delete-btn {
    background-color: #f44336 !important;
    color: #fff !important;
    border-color: #f44336 !important;
}

.pcq-no-results {
    text-align: center;
    padding: 40px 20px;
}

@media (max-width: 768px) {
    .pcq-employee-info {
        flex-direction: column;
        align-items: flex-start;
        text-align: center;
    }
    
    .pcq-actions {
        flex-direction: column;
    }
    
    .pcq-services-list {
        justify-content: center;
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