<?php
/**
 * Admin Services List Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Services', 'pro-clean-quotation'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=pcq-services&action=add'); ?>" class="page-title-action">
        <?php _e('Add New Service', 'pro-clean-quotation'); ?>
    </a>
    
    <!-- Search -->
    <div class="pcq-filters-container">
        <form method="get" action="" class="pcq-filters-form">
            <input type="hidden" name="page" value="pcq-services">
            
            <div class="pcq-filters-row">
                <input type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="<?php _e('Search services...', 'pro-clean-quotation'); ?>" class="pcq-search-input">
                <button type="submit" class="button"><?php _e('Filter', 'pro-clean-quotation'); ?></button>
                
                <?php if (isset($_GET['s']) && !empty($_GET['s'])): ?>
                    <a href="<?php echo admin_url('admin.php?page=pcq-services'); ?>" class="button">
                        <?php _e('Clear', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="pcq-table-wrapper">
        <?php if (!empty($services)): ?>
            <table class="wp-list-table widefat fixed striped pcq-services-table">
                <thead>
                    <tr>
                        <th><?php _e('ID', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Service Name', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Duration', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Call-out Fee', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Rate/sqm', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Rate/linear m', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Capacity', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Buffer Time', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Status', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($services as $service): ?>
                        <tr>
                            <td><?php echo $service->getId(); ?></td>
                            <td class="pcq-service-name-cell">
                                <span class="pcq-color-indicator" style="background-color: <?php echo esc_attr($service->getColor()); ?>"></span>
                                <span class="pcq-service-name-text"><?php echo esc_html($service->getName()); ?></span>
                            </td>
                            <td>
                                <?php $duration = $service->getDuration(); echo $duration !== null ? $duration . ' ' . __('min', 'pro-clean-quotation') : '—'; ?>
                            </td>
                            <td>
                                €<?php echo number_format($service->getBaseRate(), 2); ?>
                            </td>
                            <td>
                                €<?php echo number_format($service->getRatePerSqm(), 2); ?>/m²
                            </td>
                            <td>
                                €<?php echo number_format($service->getRatePerLinearMeter(), 2); ?>/m
                            </td>
                            <td>
                                <?php echo $service->getCapacity(); ?> 
                                <?php echo $service->getCapacity() == 1 ? __('person', 'pro-clean-quotation') : __('people', 'pro-clean-quotation'); ?>
                            </td>
                            <td class="pcq-buffer-cell">
                                <?php 
                                $buffer_before = $service->getBufferTimeBefore();
                                $buffer_after = $service->getBufferTimeAfter();
                                if ($buffer_before || $buffer_after) {
                                    echo sprintf(__('%d min before, %d min after', 'pro-clean-quotation'), $buffer_before, $buffer_after);
                                } else {
                                    echo __('None', 'pro-clean-quotation');
                                }
                                ?>
                            </td>
                            <td>
                                <span class="pcq-status-badge pcq-status-<?php echo $service->getStatus(); ?>">
                                    <?php echo ucfirst($service->getStatus()); ?>
                                </span>
                            </td>
                            <td>
                                <div class="pcq-actions-dropdown">
                                    <button type="button" class="pcq-actions-toggle" aria-label="<?php _e('Actions', 'pro-clean-quotation'); ?>">
                                        <span class="pcq-dots">⋯</span>
                                    </button>
                                    <div class="pcq-actions-menu">
                                        <a href="<?php echo admin_url('admin.php?page=pcq-services&action=edit&id=' . $service->getId()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">✏️</span>
                                            <?php _e('Edit Service', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <?php if ($service->getStatus() === 'active'): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-services&action=deactivate&id=' . $service->getId()), 'deactivate_service_' . $service->getId()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">⏸️</span>
                                            <?php _e('Deactivate', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php else: ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-services&action=activate&id=' . $service->getId()), 'activate_service_' . $service->getId()); ?>" 
                                           class="pcq-action-item pcq-action-approve">
                                            <span class="pcq-action-icon">▶️</span>
                                            <?php _e('Activate', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <div class="pcq-action-divider"></div>
                                        
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-services&action=delete&id=' . $service->getId()), 'delete_service_' . $service->getId()); ?>" 
                                           class="pcq-action-item pcq-action-danger" 
                                           onclick="return confirm('<?php _e('Are you sure you want to delete this service? This action cannot be undone.', 'pro-clean-quotation'); ?>')">
                                            <span class="pcq-action-icon">🗑️</span>
                                            <?php _e('Delete Service', 'pro-clean-quotation'); ?>
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
                <p><?php _e('No services found.', 'pro-clean-quotation'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=pcq-services&action=add'); ?>" class="button button-primary">
                    <?php _e('Create First Service', 'pro-clean-quotation'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Filters Container */
.pcq-filters-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 15px;
    margin: 20px 0;
}

.pcq-filters-form {
    margin: 0;
}

.pcq-filters-row {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.pcq-search-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    flex: 1;
    min-width: 250px;
    font-size: 14px;
}

.pcq-search-input:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
}

/* Table Wrapper */
.pcq-table-wrapper {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow-x: auto;
    overflow-y: visible;
}

/* Table Layout */
.pcq-services-table {
    table-layout: fixed;
    width: 100%;
    margin: 0 !important;
}

/* Column widths */
.pcq-services-table thead th:nth-child(1),
.pcq-services-table tbody td:nth-child(1) {
    width: 50px;
}

.pcq-services-table thead th:nth-child(2),
.pcq-services-table tbody td:nth-child(2) {
    width: 250px;
}

.pcq-services-table thead th:nth-child(3),
.pcq-services-table tbody td:nth-child(3) {
    width: 90px;
}

.pcq-services-table thead th:nth-child(4),
.pcq-services-table tbody td:nth-child(4) {
    width: 100px;
}

.pcq-services-table thead th:nth-child(5),
.pcq-services-table tbody td:nth-child(5) {
    width: 100px;
}

.pcq-services-table thead th:nth-child(6),
.pcq-services-table tbody td:nth-child(6) {
    width: 180px;
}

.pcq-services-table thead th:nth-child(7),
.pcq-services-table tbody td:nth-child(7) {
    width: 90px;
}

.pcq-services-table thead th:nth-child(8),
.pcq-services-table tbody td:nth-child(8) {
    width: 60px;
}

/* Table headers */
.pcq-services-table thead th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 12px 10px;
}

/* Service Name Cell */
.pcq-service-name-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px !important;
}

.pcq-service-name-text {
    font-weight: 600;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.pcq-color-indicator {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    display: inline-block;
    flex-shrink: 0;
}

/* Buffer Cell */
.pcq-buffer-cell {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-size: 13px;
}

.pcq-no-results {
    text-align: center;
    padding: 40px 20px;
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
    z-index: 10000;
    display: none;
    padding: 4px 0;
    margin-top: 4px;
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
    white-space: nowrap;
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

/* Pagination */
.pcq-pagination {
    padding: 15px 20px;
    text-align: center;
    border-top: 1px solid #ccd0d4;
    background: #fff;
    margin-top: -1px;
}

.pcq-pagination .page-numbers {
    display: inline-block;
    padding: 6px 12px;
    margin: 0 2px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: #fff;
    color: #2271b1;
    text-decoration: none;
    font-size: 13px;
    transition: all 0.2s ease;
}

.pcq-pagination .page-numbers:hover {
    background: #f6f7f7;
    border-color: #2271b1;
}

.pcq-pagination .page-numbers.current {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
    font-weight: 600;
}

.pcq-pagination .page-numbers.dots {
    border: none;
    background: transparent;
    color: #666;
    cursor: default;
}

.pcq-pagination .page-numbers.dots:hover {
    background: transparent;
    border: none;
}

.pcq-pagination .prev,
.pcq-pagination .next {
    font-weight: 500;
}

/* Responsive */
@media (max-width: 768px) {
    .pcq-filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pcq-search-input {
        width: 100%;
        min-width: auto;
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