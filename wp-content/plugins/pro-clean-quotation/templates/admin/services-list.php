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
    
    <div class="pcq-table-container">
        <?php if (!empty($services)): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 60px;"><?php _e('ID', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Service Name', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Duration', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Price', 'pro-clean-quotation'); ?></th>
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
                            <td>
                                <div class="pcq-service-info">
                                    <div class="pcq-service-name">
                                        <span class="pcq-color-indicator" style="background-color: <?php echo esc_attr($service->getColor()); ?>"></span>
                                        <strong><?php echo esc_html($service->getName()); ?></strong>
                                    </div>
                                    <?php if ($service->getDescription()): ?>
                                        <div class="pcq-service-description">
                                            <?php echo esc_html($service->getDescription()); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php echo $service->getDuration(); ?> <?php _e('min', 'pro-clean-quotation'); ?>
                            </td>
                            <td>
                                <strong>€<?php echo number_format($service->getPrice(), 2); ?></strong>
                            </td>
                            <td>
                                <?php echo $service->getCapacity(); ?> 
                                <?php echo $service->getCapacity() == 1 ? __('person', 'pro-clean-quotation') : __('people', 'pro-clean-quotation'); ?>
                            </td>
                            <td>
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
.pcq-table-container {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow: hidden;
    margin-top: 20px;
}

.pcq-service-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.pcq-service-name {
    display: flex;
    align-items: center;
    gap: 8px;
}

.pcq-color-indicator {
    width: 16px;
    height: 16px;
    border-radius: 3px;
    display: inline-block;
}

.pcq-service-description {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
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
    .pcq-actions {
        flex-direction: column;
    }
    
    .pcq-service-name {
        flex-wrap: wrap;
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