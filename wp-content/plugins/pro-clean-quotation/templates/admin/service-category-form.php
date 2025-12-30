<?php
/**
 * Service Category Form Template
 * 
 * @package ProClean\Quotation
 */

if (!defined('ABSPATH')) {
    exit;
}

$is_edit = $category !== null;
$page_title = $is_edit ? __('Edit Service Category', 'pro-clean-quotation') : __('Add New Service Category', 'pro-clean-quotation');
$nonce_action = $is_edit ? 'pcq_save_category_' . $category->id : 'pcq_create_category';
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>
    
    <form method="post" action="<?php echo admin_url('admin.php?page=pcq-service-categories'); ?>">
        <?php wp_nonce_field($nonce_action, '_wpnonce'); ?>
        <input type="hidden" name="action" value="save_category">
        <input type="hidden" name="category_id" value="<?php echo $is_edit ? esc_attr($category->id) : '0'; ?>">
        
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="name"><?php _e('Category Name', 'pro-clean-quotation'); ?> <span class="required">*</span></label>
                </th>
                <td>
                    <input type="text" id="name" name="name" class="regular-text" 
                           value="<?php echo $is_edit ? esc_attr($category->name) : ''; ?>" required>
                    <p class="description"><?php _e('Enter the category name (e.g., "Exterior Cleaning", "Interior Services")', 'pro-clean-quotation'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="description"><?php _e('Description', 'pro-clean-quotation'); ?></label>
                </th>
                <td>
                    <textarea id="description" name="description" class="large-text" rows="4"><?php echo $is_edit ? esc_textarea($category->description) : ''; ?></textarea>
                    <p class="description"><?php _e('Optional description for this category', 'pro-clean-quotation'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="color"><?php _e('Color', 'pro-clean-quotation'); ?></label>
                </th>
                <td>
                    <input type="color" id="color" name="color" value="<?php echo $is_edit ? esc_attr($category->color) : '#2196F3'; ?>">
                    <p class="description"><?php _e('Choose a color for this category (used in calendars and displays)', 'pro-clean-quotation'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="sort_order"><?php _e('Sort Order', 'pro-clean-quotation'); ?></label>
                </th>
                <td>
                    <input type="number" id="sort_order" name="sort_order" class="small-text" 
                           value="<?php echo $is_edit ? esc_attr($category->sort_order) : '0'; ?>" min="0">
                    <p class="description"><?php _e('Lower numbers appear first', 'pro-clean-quotation'); ?></p>
                </td>
            </tr>
            
            <tr>
                <th scope="row">
                    <label for="status"><?php _e('Status', 'pro-clean-quotation'); ?></label>
                </th>
                <td>
                    <select id="status" name="status">
                        <option value="active" <?php echo ($is_edit && $category->status === 'active') ? 'selected' : ''; ?>>
                            <?php _e('Active', 'pro-clean-quotation'); ?>
                        </option>
                        <option value="inactive" <?php echo ($is_edit && $category->status === 'inactive') ? 'selected' : ''; ?>>
                            <?php _e('Inactive', 'pro-clean-quotation'); ?>
                        </option>
                    </select>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary">
                <?php echo $is_edit ? __('Update Category', 'pro-clean-quotation') : __('Create Category', 'pro-clean-quotation'); ?>
            </button>
            <a href="<?php echo admin_url('admin.php?page=pcq-service-categories'); ?>" class="button">
                <?php _e('Cancel', 'pro-clean-quotation'); ?>
            </a>
        </p>
    </form>
</div>

<style>
.required {
    color: #d63638;
}
</style>
