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
    <hr class="wp-header-end">
    
    <?php if (empty($categories)): ?>
        <div class="notice notice-info inline">
            <p><?php _e('No service categories found. Create your first category to organize your services.', 'pro-clean-quotation'); ?></p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th style="width: 50px;"><?php _e('Color', 'pro-clean-quotation'); ?></th>
                    <th><?php _e('Name', 'pro-clean-quotation'); ?></th>
                    <th><?php _e('Description', 'pro-clean-quotation'); ?></th>
                    <th style="width: 100px;"><?php _e('Order', 'pro-clean-quotation'); ?></th>
                    <th style="width: 100px;"><?php _e('Status', 'pro-clean-quotation'); ?></th>
                    <th style="width: 150px;"><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $category): ?>
                    <tr>
                        <td>
                            <span style="display: inline-block; width: 30px; height: 30px; background-color: <?php echo esc_attr($category->color); ?>; border-radius: 4px; border: 1px solid #ddd;"></span>
                        </td>
                        <td>
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=pcq-service-categories&action=edit&id=' . $category->id); ?>">
                                    <?php echo esc_html($category->name); ?>
                                </a>
                            </strong>
                        </td>
                        <td><?php echo esc_html($category->description); ?></td>
                        <td><?php echo esc_html($category->sort_order); ?></td>
                        <td>
                            <?php if ($category->status === 'active'): ?>
                                <span class="status-badge status-active"><?php _e('Active', 'pro-clean-quotation'); ?></span>
                            <?php else: ?>
                                <span class="status-badge status-inactive"><?php _e('Inactive', 'pro-clean-quotation'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=pcq-service-categories&action=edit&id=' . $category->id); ?>" class="button button-small">
                                <?php _e('Edit', 'pro-clean-quotation'); ?>
                            </a>
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-service-categories&action=delete&id=' . $category->id), 'delete_category_' . $category->id); ?>" 
                               class="button button-small button-link-delete" 
                               onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this category?', 'pro-clean-quotation'); ?>');">
                                <?php _e('Delete', 'pro-clean-quotation'); ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<style>
.status-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}
.status-active {
    background: #d4edda;
    color: #155724;
}
.status-inactive {
    background: #f8d7da;
    color: #721c24;
}
</style>
