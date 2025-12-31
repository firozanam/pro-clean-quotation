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
    
    <!-- Search -->
    <div class="pcq-filters">
        <form method="get" action="">
            <input type="hidden" name="page" value="pcq-employees">
            
            <div class="pcq-filter-row">
                <input type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="<?php _e('Search employees...', 'pro-clean-quotation'); ?>" class="pcq-search-input">
                <button type="submit" class="button"><?php _e('Filter', 'pro-clean-quotation'); ?></button>
                
                <?php if (isset($_GET['s']) && !empty($_GET['s'])): ?>
                    <a href="<?php echo admin_url('admin.php?page=pcq-employees'); ?>" class="button">
                        <?php _e('Clear', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="pcq-table-wrapper">
        <?php if (!empty($employees)): ?>
            <table class="wp-list-table widefat fixed striped pcq-employees-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'pro-clean-quotation'); ?></th>
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
                            <td class="pcq-employee-cell">
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
                                        <div class="pcq-employee-name"><?php echo esc_html($employee->getName()); ?></div>
                                        <?php if ($employee->getDescription()): ?>
                                            <div class="pcq-employee-description">
                                                <?php echo esc_html($employee->getDescription()); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="pcq-contact-cell">
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
                            <td class="pcq-services-cell">
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
                            <td class="pcq-hours-cell">
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
        </div>
            
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
/* Filters */
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

.pcq-filter-row .pcq-search-input {
    flex: 1;
    min-width: 250px;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.pcq-filter-row .pcq-search-input:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
}

/* Table Wrapper */
.pcq-table-wrapper {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow: hidden;
    overflow-x: auto;
}

/* Table Layout */
.pcq-employees-table {
    table-layout: fixed;
    width: 100%;
    margin: 0 !important;
}

/* Column widths */
.pcq-employees-table thead th:nth-child(1),
.pcq-employees-table tbody td:nth-child(1) {
    width: 50px;
}

.pcq-employees-table thead th:nth-child(2),
.pcq-employees-table tbody td:nth-child(2) {
    width: 280px;
}

.pcq-employees-table thead th:nth-child(3),
.pcq-employees-table tbody td:nth-child(3) {
    width: 180px;
}

.pcq-employees-table thead th:nth-child(4),
.pcq-employees-table tbody td:nth-child(4) {
    width: auto;
}

.pcq-employees-table thead th:nth-child(5),
.pcq-employees-table tbody td:nth-child(5) {
    width: 140px;
}

.pcq-employees-table thead th:nth-child(6),
.pcq-employees-table tbody td:nth-child(6) {
    width: 90px;
}

.pcq-employees-table thead th:nth-child(7),
.pcq-employees-table tbody td:nth-child(7) {
    width: 60px;
}

/* Table headers */
.pcq-employees-table thead th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 12px 10px;
}

/* Employee cell */
.pcq-employee-cell {
    padding: 8px 10px !important;
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
    flex-shrink: 0;
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
    min-width: 0;
}

.pcq-employee-name {
    font-weight: 600;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pcq-employee-description {
    font-size: 13px;
    color: #666;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Contact cell */
.pcq-contact-cell {
    font-size: 13px;
}

.pcq-contact-cell a {
    color: #2271b1;
    text-decoration: none;
    word-break: break-all;
}

.pcq-contact-cell a:hover {
    text-decoration: underline;
}

/* Services cell */
.pcq-services-cell {
    padding: 8px 10px !important;
}

.pcq-services-list {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    max-height: 52px;
    overflow-y: auto;
    padding-right: 5px;
}

.pcq-services-list::-webkit-scrollbar {
    width: 6px;
}

.pcq-services-list::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.pcq-services-list::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 3px;
}

.pcq-services-list::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.pcq-service-tag {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 10px;
    color: #fff;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
}

/* Hours cell */
.pcq-hours-cell {
    font-size: 13px;
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
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: capitalize;
    white-space: nowrap;
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

/* Responsive */
@media (max-width: 768px) {
    .pcq-filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pcq-filter-row .pcq-search-input {
        width: 100%;
        min-width: auto;
    }
    
    .pcq-employee-info {
        flex-direction: column;
        align-items: flex-start;
        text-align: center;
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