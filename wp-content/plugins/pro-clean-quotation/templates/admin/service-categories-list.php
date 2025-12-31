<?php
/**
 * Service Categories List Template
 * 
 * @package ProClean\Quotation
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Service Categories', 'pro-clean-quotation'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=pcq-service-categories&action=add'); ?>" class="page-title-action">
        <?php _e('Add New', 'pro-clean-quotation'); ?>
    </a>
    
    <!-- Search -->
    <div class="pcq-filters-container">
        <form method="get" action="" class="pcq-filters-form">
            <input type="hidden" name="page" value="pcq-service-categories">
            
            <div class="pcq-filters-row">
                <input type="search" name="s" value="<?php echo isset($_GET['s']) ? esc_attr($_GET['s']) : ''; ?>" placeholder="<?php _e('Search categories...', 'pro-clean-quotation'); ?>" class="pcq-search-input">
                <button type="submit" class="button"><?php _e('Filter', 'pro-clean-quotation'); ?></button>
                
                <?php if (isset($_GET['s']) && !empty($_GET['s'])): ?>
                    <a href="<?php echo admin_url('admin.php?page=pcq-service-categories'); ?>" class="button">
                        <?php _e('Clear', 'pro-clean-quotation'); ?>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="pcq-table-wrapper">
        <?php if (empty($categories)): ?>
            <div class="pcq-no-results">
                <p><?php _e('No service categories found.', 'pro-clean-quotation'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=pcq-service-categories&action=add'); ?>" class="button button-primary">
                    <?php _e('Create First Category', 'pro-clean-quotation'); ?>
                </a>
            </div>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped pcq-categories-table">
                <thead>
                    <tr>
                        <th><?php _e('Color', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Name', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Description', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Order', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Status', 'pro-clean-quotation'); ?></th>
                        <th><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td class="pcq-color-cell">
                                <span class="pcq-color-box" style="background-color: <?php echo esc_attr($category->color); ?>;"></span>
                            </td>
                            <td class="pcq-name-cell">
                                <strong>
                                    <a href="<?php echo admin_url('admin.php?page=pcq-service-categories&action=edit&id=' . $category->id); ?>">
                                        <?php echo esc_html($category->name); ?>
                                    </a>
                                </strong>
                            </td>
                            <td class="pcq-description-cell">
                                <span class="pcq-description-text"><?php echo esc_html($category->description); ?></span>
                            </td>
                            <td><?php echo esc_html($category->sort_order); ?></td>
                            <td>
                                <span class="pcq-status-badge pcq-status-<?php echo esc_attr($category->status); ?>">
                                    <?php echo ucfirst($category->status); ?>
                                </span>
                            </td>
                            <td>
                                <div class="pcq-actions-dropdown">
                                    <button type="button" class="pcq-actions-toggle" aria-label="<?php _e('Actions', 'pro-clean-quotation'); ?>">
                                        <span class="pcq-dots">⋯</span>
                                    </button>
                                    <div class="pcq-actions-menu">
                                        <a href="<?php echo admin_url('admin.php?page=pcq-service-categories&action=edit&id=' . $category->id); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">✏️</span>
                                            <?php _e('Edit Category', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <div class="pcq-action-divider"></div>
                                        
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-service-categories&action=delete&id=' . $category->id), 'delete_category_' . $category->id); ?>" 
                                           class="pcq-action-item pcq-action-danger" 
                                           onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this category?', 'pro-clean-quotation'); ?>')">
                                            <span class="pcq-action-icon">🗑️</span>
                                            <?php _e('Delete Category', 'pro-clean-quotation'); ?>
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
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
    overflow: hidden;
    overflow-x: auto;
}

/* Table Layout */
.pcq-categories-table {
    table-layout: fixed;
    width: 100%;
    margin: 0 !important;
}

/* Column widths */
.pcq-categories-table thead th:nth-child(1),
.pcq-categories-table tbody td:nth-child(1) {
    width: 60px;
}

.pcq-categories-table thead th:nth-child(2),
.pcq-categories-table tbody td:nth-child(2) {
    width: 200px;
}

.pcq-categories-table thead th:nth-child(3),
.pcq-categories-table tbody td:nth-child(3) {
    width: auto;
}

.pcq-categories-table thead th:nth-child(4),
.pcq-categories-table tbody td:nth-child(4) {
    width: 80px;
}

.pcq-categories-table thead th:nth-child(5),
.pcq-categories-table tbody td:nth-child(5) {
    width: 100px;
}

.pcq-categories-table thead th:nth-child(6),
.pcq-categories-table tbody td:nth-child(6) {
    width: 60px;
}

/* Table headers */
.pcq-categories-table thead th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 12px 10px;
}

/* Color cell */
.pcq-color-cell {
    text-align: center;
}

.pcq-color-box {
    display: inline-block;
    width: 30px;
    height: 30px;
    border-radius: 4px;
    border: 1px solid #ddd;
}

/* Name cell */
.pcq-name-cell {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pcq-name-cell strong {
    font-weight: 600;
    color: #2c3e50;
}

/* Description cell */
.pcq-description-cell {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pcq-description-text {
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: #666;
    font-size: 13px;
}

/* Status Badge */
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

/* No Results */
.pcq-no-results {
    text-align: center;
    padding: 40px 20px;
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

.pcq-action-danger:hover {
    background-color: #fcf0f1;
    color: #d63638;
}

.pcq-action-divider {
    height: 1px;
    background-color: #e0e0e0;
    margin: 4px 0;
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
