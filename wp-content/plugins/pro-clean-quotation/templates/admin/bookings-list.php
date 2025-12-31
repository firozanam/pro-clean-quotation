<?php
/**
 * Admin Bookings List Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Helper function to format status
function pcq_format_booking_status($status) {
    $statuses = [
        'pending' => __('Pending', 'pro-clean-quotation'),
        'confirmed' => __('Confirmed', 'pro-clean-quotation'),
        'completed' => __('Completed', 'pro-clean-quotation'),
        'cancelled' => __('Cancelled', 'pro-clean-quotation')
    ];
    return $statuses[$status] ?? ucfirst($status);
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Bookings', 'pro-clean-quotation'); ?></h1>
    
    <!-- Filters -->
    <div class="pcq-filters-container">
        <form method="get" action="" class="pcq-filters-form">
            <input type="hidden" name="page" value="pcq-bookings">
            
            <div class="pcq-filters-row">
                <select name="status" class="pcq-filter-select">
                    <option value=""><?php _e('All Statuses', 'pro-clean-quotation'); ?></option>
                    <option value="pending" <?php selected($status_filter, 'pending'); ?>><?php _e('Pending', 'pro-clean-quotation'); ?></option>
                    <option value="confirmed" <?php selected($status_filter, 'confirmed'); ?>><?php _e('Confirmed', 'pro-clean-quotation'); ?></option>
                    <option value="completed" <?php selected($status_filter, 'completed'); ?>><?php _e('Completed', 'pro-clean-quotation'); ?></option>
                    <option value="cancelled" <?php selected($status_filter, 'cancelled'); ?>><?php _e('Cancelled', 'pro-clean-quotation'); ?></option>
                </select>
                
                <button type="submit" class="button"><?php _e('Filter', 'pro-clean-quotation'); ?></button>
                
                <div class="pcq-search-wrapper">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search bookings...', 'pro-clean-quotation'); ?>" class="pcq-search-input">
                    <button type="submit" class="button"><?php _e('Search', 'pro-clean-quotation'); ?></button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Bookings Table -->
    <div class="pcq-table-wrapper">
        <table class="wp-list-table widefat fixed striped pcq-bookings-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column"><?php _e('Booking #', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Customer', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Service', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Service Date', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Amount', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Status', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Created', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="8" class="no-items"><?php _e('No bookings found.', 'pro-clean-quotation'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>
                                <strong>
                                    <a href="<?php echo admin_url('admin.php?page=pcq-bookings&action=view&id=' . $booking->id); ?>">
                                        <?php echo esc_html($booking->booking_number); ?>
                                    </a>
                                </strong>
                            </td>
                            <td class="pcq-customer-cell">
                                <div class="pcq-customer-name"><?php echo esc_html($booking->customer_name); ?></div>
                                <div class="pcq-customer-contact">
                                    <a href="mailto:<?php echo esc_attr($booking->customer_email); ?>" class="pcq-customer-email">
                                        <?php echo esc_html($booking->customer_email); ?>
                                    </a>
                                </div>
                                <?php if ($booking->customer_phone): ?>
                                <div class="pcq-customer-contact">
                                    <a href="tel:<?php echo esc_attr($booking->customer_phone); ?>">
                                        <?php echo esc_html($booking->customer_phone); ?>
                                    </a>
                                </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="pcq-service-type"><?php echo esc_html(ucfirst($booking->service_type)); ?></div>
                            </td>
                            <td>
                                <div><?php echo esc_html(date('M j, Y', strtotime($booking->service_date))); ?></div>
                                <small><?php echo esc_html(date('H:i', strtotime($booking->service_time_start))) . ' - ' . esc_html(date('H:i', strtotime($booking->service_time_end))); ?></small>
                            </td>
                            <td>
                                <strong>€<?php echo number_format($booking->total_amount, 2); ?></strong>
                            </td>
                            <td>
                                <span class="pcq-status pcq-status-<?php echo esc_attr($booking->booking_status); ?>">
                                    <?php echo esc_html(pcq_format_booking_status($booking->booking_status)); ?>
                                </span>
                            </td>
                            <td>
                                <div><?php echo date('M j, Y', strtotime($booking->created_at)); ?></div>
                                <small><?php echo date('H:i', strtotime($booking->created_at)); ?></small>
                            </td>
                            <td>
                                <div class="pcq-actions-dropdown">
                                    <button type="button" class="pcq-actions-toggle" aria-label="<?php _e('Actions', 'pro-clean-quotation'); ?>">
                                        <span class="pcq-dots">⋯</span>
                                    </button>
                                    <div class="pcq-actions-menu">
                                        <a href="<?php echo admin_url('admin.php?page=pcq-bookings&action=view&id=' . $booking->id); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">👁</span>
                                            <?php _e('View Details', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <?php if ($booking->quote_id): ?>
                                        <a href="<?php echo admin_url('admin.php?page=pcq-quotes&action=view&id=' . $booking->quote_id); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">📄</span>
                                            <?php _e('View Quote', 'pro-clean-quotation'); ?>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination or Footer -->
        <?php if ($total_pages > 1): ?>
            <div class="pcq-pagination">
                <?php echo paginate_links($pagination_args); ?>
            </div>
        <?php else: ?>
            <div class="pcq-table-footer">
                <span class="pcq-results-count">
                    <?php 
                    $total_bookings = count($bookings);
                    printf(
                        _n('%s booking', '%s bookings', $total_bookings, 'pro-clean-quotation'),
                        number_format_i18n($total_bookings)
                    );
                    ?>
                </span>
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

.pcq-filter-select {
    min-width: 150px;
    padding: 6px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    background: #fff;
}

.pcq-search-wrapper {
    display: flex;
    align-items: center;
    gap: 5px;
    margin-left: auto;
}

.pcq-search-input {
    padding: 6px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    min-width: 200px;
}

/* Table Styles */
.pcq-table-wrapper {
    margin-top: 20px;
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    overflow: hidden;
}

.pcq-bookings-table {
    border: none;
    margin: 0;
}

.pcq-customer-cell {
    font-size: 13px;
}

.pcq-customer-name {
    font-weight: 600;
    color: #2271b1;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pcq-customer-contact {
    color: #646970;
    font-size: 12px;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pcq-customer-email {
    color: #646970;
    text-decoration: none;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pcq-customer-email:hover {
    color: #2271b1;
    text-decoration: underline;
}

.pcq-service-type {
    font-weight: 500;
    color: #1d2327;
}

/* Status Badges */
.pcq-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.pcq-status-pending {
    background: #fcf3cf;
    color: #b7791f;
}

.pcq-status-confirmed {
    background: #d4edda;
    color: #155724;
}

.pcq-status-completed {
    background: #d1ecf1;
    color: #0c5460;
}

.pcq-status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

/* Actions Dropdown - Using Global Styles from admin.css */
/* Local overrides only if needed */

/* Pagination */
.pcq-pagination {
    padding: 15px 20px;
    text-align: center;
    border-top: 1px solid #ccd0d4;
}

.pcq-pagination .page-numbers {
    display: inline-block;
    padding: 6px 12px;
    margin: 0 2px;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    text-decoration: none;
    color: #2271b1;
    background: #fff;
    transition: all 0.2s;
}

.pcq-pagination .page-numbers:hover {
    background: #f0f0f1;
    border-color: #8c8f94;
}

.pcq-pagination .page-numbers.current {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

/* Table Footer */
.pcq-table-footer {
    padding: 12px 20px;
    border-top: 1px solid #ccd0d4;
    background: #f9fafb;
    text-align: center;
}

.pcq-results-count {
    font-size: 13px;
    color: #646970;
    font-weight: 500;
}

/* Responsive */
@media screen and (max-width: 782px) {
    .pcq-filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pcq-filter-select,
    .pcq-search-input {
        width: 100%;
    }
    
    .pcq-search-wrapper {
        margin-left: 0;
    }
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
