<?php
/**
 * Admin Quotes List Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Quotes', 'pro-clean-quotation'); ?></h1>
    
    <!-- Filters -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get" action="">
                <input type="hidden" name="page" value="pcq-quotes">
                
                <select name="status">
                    <option value=""><?php _e('All Statuses', 'pro-clean-quotation'); ?></option>
                    <option value="new" <?php selected($status_filter, 'new'); ?>><?php _e('New', 'pro-clean-quotation'); ?></option>
                    <option value="viewed" <?php selected($status_filter, 'viewed'); ?>><?php _e('Viewed', 'pro-clean-quotation'); ?></option>
                    <option value="booked" <?php selected($status_filter, 'booked'); ?>><?php _e('Booked', 'pro-clean-quotation'); ?></option>
                    <option value="expired" <?php selected($status_filter, 'expired'); ?>><?php _e('Expired', 'pro-clean-quotation'); ?></option>
                    <option value="declined" <?php selected($status_filter, 'declined'); ?>><?php _e('Declined', 'pro-clean-quotation'); ?></option>
                </select>
                
                <select name="service_type">
                    <option value=""><?php _e('All Services', 'pro-clean-quotation'); ?></option>
                    <option value="facade" <?php selected($service_filter, 'facade'); ?>><?php _e('Façade Cleaning', 'pro-clean-quotation'); ?></option>
                    <option value="roof" <?php selected($service_filter, 'roof'); ?>><?php _e('Roof Cleaning', 'pro-clean-quotation'); ?></option>
                    <option value="both" <?php selected($service_filter, 'both'); ?>><?php _e('Both Services', 'pro-clean-quotation'); ?></option>
                </select>
                
                <input type="submit" class="button" value="<?php _e('Filter', 'pro-clean-quotation'); ?>">
            </form>
        </div>
        
        <div class="tablenav-pages">
            <?php
            if ($quotes_data['pages'] > 1) {
                $pagination_args = [
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $quotes_data['pages'],
                    'current' => $quotes_data['current_page']
                ];
                echo paginate_links($pagination_args);
            }
            ?>
        </div>
    </div>
    
    <!-- Search -->
    <p class="search-box">
        <form method="get" action="">
            <input type="hidden" name="page" value="pcq-quotes">
            <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search quotes...', 'pro-clean-quotation'); ?>">
            <input type="submit" class="button" value="<?php _e('Search', 'pro-clean-quotation'); ?>">
        </form>
    </p>
    
    <!-- Quotes Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column"><?php _e('Quote #', 'pro-clean-quotation'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Customer', 'pro-clean-quotation'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Service', 'pro-clean-quotation'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Property', 'pro-clean-quotation'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Amount', 'pro-clean-quotation'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Status', 'pro-clean-quotation'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Date', 'pro-clean-quotation'); ?></th>
                <th scope="col" class="manage-column"><?php _e('Actions', 'pro-clean-quotation'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($quotes_data['quotes'])): ?>
                <tr>
                    <td colspan="8" class="no-items"><?php _e('No quotes found.', 'pro-clean-quotation'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($quotes_data['quotes'] as $quote): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote->getId()); ?>">
                                    <?php echo esc_html($quote->getQuoteNumber()); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <strong><?php echo esc_html($quote->getCustomerName()); ?></strong><br>
                            <a href="mailto:<?php echo esc_attr($quote->getCustomerEmail()); ?>">
                                <?php echo esc_html($quote->getCustomerEmail()); ?>
                            </a><br>
                            <a href="tel:<?php echo esc_attr($quote->getCustomerPhone()); ?>">
                                <?php echo esc_html($quote->getCustomerPhone()); ?>
                            </a>
                        </td>
                        <td>
                            <?php echo esc_html(ucfirst($quote->getServiceType())); ?><br>
                            <small><?php echo number_format($quote->getSquareMeters(), 1); ?> sqm</small>
                        </td>
                        <td>
                            <?php echo esc_html($quote->getPropertyType()); ?><br>
                            <small><?php echo esc_html($quote->getPostalCode()); ?></small>
                        </td>
                        <td>
                            <strong>€<?php echo number_format($quote->getTotalPrice(), 2); ?></strong>
                        </td>
                        <td>
                            <span class="pcq-status pcq-status-<?php echo esc_attr($quote->getStatus()); ?>">
                                <?php echo esc_html(ucfirst($quote->getStatus())); ?>
                            </span>
                            <?php if ($quote->isExpired()): ?>
                                <br><small class="pcq-expired"><?php _e('Expired', 'pro-clean-quotation'); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date('M j, Y', strtotime($quote->getCreatedAt())); ?><br>
                            <small><?php echo date('H:i', strtotime($quote->getCreatedAt())); ?></small>
                        </td>
                        <td>
                            <div class="pcq-actions-dropdown">
                                <button type="button" class="pcq-actions-toggle" aria-label="<?php _e('Actions', 'pro-clean-quotation'); ?>">
                                    <span class="pcq-dots">⋯</span>
                                </button>
                                <div class="pcq-actions-menu">
                                    <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $quote->getId()); ?>" 
                                       class="pcq-action-item">
                                        <span class="pcq-action-icon">👁</span>
                                        <?php _e('View Details', 'pro-clean-quotation'); ?>
                                    </a>
                                    
                                    <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=edit&id=' . $quote->getId()); ?>" 
                                       class="pcq-action-item">
                                        <span class="pcq-action-icon">✏️</span>
                                        <?php _e('Edit Quote', 'pro-clean-quotation'); ?>
                                    </a>
                                    
                                    <?php if (in_array($quote->getStatus(), ['new', 'reviewed'])): ?>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-quotes&action=approve&id=' . $quote->getId()), 'approve_quote_' . $quote->getId()); ?>" 
                                       class="pcq-action-item pcq-action-approve">
                                        <span class="pcq-action-icon">✅</span>
                                        <?php _e('Approve Quote', 'pro-clean-quotation'); ?>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if ($quote->canBeBooked()): ?>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-quotes&action=convert_to_booking&id=' . $quote->getId()), 'convert_quote_' . $quote->getId()); ?>" 
                                       class="pcq-action-item pcq-action-primary">
                                        <span class="pcq-action-icon">📅</span>
                                        <?php _e('Create Appointment', 'pro-clean-quotation'); ?>
                                    </a>
                                    <?php endif; ?>
                                    
                                    <div class="pcq-action-divider"></div>
                                    
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=pcq-quotes&action=delete&id=' . $quote->getId()), 'delete_quote_' . $quote->getId()); ?>" 
                                       class="pcq-action-item pcq-action-danger"
                                       onclick="return confirm('<?php _e('Are you sure you want to delete this quote?', 'pro-clean-quotation'); ?>')">
                                        <span class="pcq-action-icon">🗑️</span>
                                        <?php _e('Delete Quote', 'pro-clean-quotation'); ?>
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <!-- Bottom Pagination -->
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            if ($quotes_data['pages'] > 1) {
                echo paginate_links($pagination_args);
            }
            ?>
        </div>
    </div>
</div>

<style>
.pcq-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
}

.pcq-status-new {
    background: #e3f2fd;
    color: #1976d2;
}

.pcq-status-reviewed {
    background: #fff3e0;
    color: #f57c00;
}

.pcq-status-approved {
    background: #e8f5e8;
    color: #388e3c;
}

.pcq-status-converted {
    background: #f3e5f5;
    color: #7b1fa2;
}

.pcq-status-expired {
    background: #ffebee;
    color: #d32f2f;
}

.pcq-expired {
    color: #d32f2f;
    font-style: italic;
}

/* Actions Dropdown Styles */
.pcq-actions-dropdown {
    position: relative;
    display: inline-block;
}

.pcq-actions-toggle {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
    font-size: 16px;
    line-height: 1;
    color: #666;
}

.pcq-actions-toggle:hover {
    background-color: #f0f0f0;
    color: #333;
}

.pcq-actions-toggle:focus {
    outline: 2px solid #2271b1;
    outline-offset: 1px;
}

.pcq-dots {
    display: block;
    font-weight: bold;
    transform: rotate(90deg);
}

.pcq-actions-menu {
    position: absolute;
    top: 100%;
    right: 0;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 6px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 180px;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    padding: 6px 0;
}

.pcq-actions-dropdown.active .pcq-actions-menu {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.pcq-action-item {
    display: flex;
    align-items: center;
    padding: 8px 16px;
    color: #333;
    text-decoration: none;
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
    margin-right: 8px;
    font-size: 14px;
    width: 16px;
    text-align: center;
}

.pcq-action-primary {
    color: #1976d2;
    font-weight: 500;
}

.pcq-action-primary:hover {
    background-color: #e7f3ff;
    color: #0073aa;
}

.pcq-action-approve {
    color: #388e3c;
    font-weight: 500;
}

.pcq-action-approve:hover {
    background-color: #f0f6ff;
    color: #00a32a;
}

.pcq-action-danger {
    color: #d32f2f;
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

/* Responsive adjustments */
@media (max-width: 768px) {
    .pcq-actions-menu {
        right: auto;
        left: 0;
        min-width: 160px;
    }
}

.pcq-status-viewed {
    background: #fff3e0;
    color: #f57c00;
}

.pcq-status-booked {
    background: #e8f5e8;
    color: #2e7d32;
}

.pcq-status-expired {
    background: #ffebee;
    color: #c62828;
}

.pcq-status-declined {
    background: #fafafa;
    color: #616161;
}

.pcq-expired {
    color: #c62828;
    font-style: italic;
}

.search-box {
    float: right;
    margin: 0 0 10px 0;
}

.search-box input[type="search"] {
    width: 200px;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Handle dropdown toggle
    $(document).on('click', '.pcq-actions-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var $dropdown = $(this).closest('.pcq-actions-dropdown');
        var isActive = $dropdown.hasClass('active');
        
        // Close all other dropdowns
        $('.pcq-actions-dropdown').removeClass('active');
        
        // Toggle current dropdown
        if (!isActive) {
            $dropdown.addClass('active');
        }
    });
    
    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.pcq-actions-dropdown').length) {
            $('.pcq-actions-dropdown').removeClass('active');
        }
    });
    
    // Close dropdown when pressing Escape
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            $('.pcq-actions-dropdown').removeClass('active');
        }
    });
    
    // Handle keyboard navigation in dropdown
    $(document).on('keydown', '.pcq-actions-dropdown.active', function(e) {
        var $items = $(this).find('.pcq-action-item');
        var $focused = $items.filter(':focus');
        var currentIndex = $items.index($focused);
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                var nextIndex = currentIndex < $items.length - 1 ? currentIndex + 1 : 0;
                $items.eq(nextIndex).focus();
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                var prevIndex = currentIndex > 0 ? currentIndex - 1 : $items.length - 1;
                $items.eq(prevIndex).focus();
                break;
                
            case 'Enter':
            case ' ':
                if ($focused.length) {
                    e.preventDefault();
                    $focused[0].click();
                }
                break;
        }
    });
    
    // Focus first item when dropdown opens
    $(document).on('click', '.pcq-actions-toggle', function() {
        var $dropdown = $(this).closest('.pcq-actions-dropdown');
        if ($dropdown.hasClass('active')) {
            setTimeout(function() {
                $dropdown.find('.pcq-action-item').first().focus();
            }, 100);
        }
    });
});
</script>