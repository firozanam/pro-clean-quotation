<?php
/**
 * Admin Customers List Template
 * 
 * @package ProClean\Quotation
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Customers', 'pro-clean-quotation'); ?></h1>
    
    <!-- Filters -->
    <div class="pcq-filters-container">
        <form method="get" action="" class="pcq-filters-form">
            <input type="hidden" name="page" value="pcq-customers">
            
            <div class="pcq-filters-row">
                <select name="status" class="pcq-filter-select">
                    <option value=""><?php _e('All Status', 'pro-clean-quotation'); ?></option>
                    <option value="active" <?php selected($status_filter, 'active'); ?>><?php _e('Active', 'pro-clean-quotation'); ?></option>
                    <option value="inactive" <?php selected($status_filter, 'inactive'); ?>><?php _e('Inactive', 'pro-clean-quotation'); ?></option>
                </select>
                
                <button type="submit" class="button"><?php _e('Filter', 'pro-clean-quotation'); ?></button>
                
                <div class="pcq-search-wrapper">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search customers...', 'pro-clean-quotation'); ?>" class="pcq-search-input">
                    <button type="submit" class="button"><?php _e('Search', 'pro-clean-quotation'); ?></button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Customers Table -->
    <div class="pcq-table-wrapper">
        <table class="wp-list-table widefat fixed striped pcq-customers-table">
            <thead>
                <tr>
                    <th scope="col" class="manage-column"><?php _e('Customer Name', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Email', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Phone Number', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Address', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Quotes', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Appointments', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Total Spent', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Last Activity', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Status', 'pro-clean-quotation'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Actions', 'pro-clean-quotation'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers_data['customers'])): ?>
                    <tr>
                        <td colspan="10" class="no-items"><?php _e('No customers found.', 'pro-clean-quotation'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers_data['customers'] as $customer): ?>
                        <tr>
                            <td>
                                <strong class="pcq-customer-name">
                                    <?php echo esc_html($customer->getName()); ?>
                                </strong>
                            </td>
                            <td class="pcq-email-cell">
                                <a href="mailto:<?php echo esc_attr($customer->getEmail()); ?>" class="pcq-customer-email">
                                    <?php echo esc_html($customer->getEmail()); ?>
                                </a>
                            </td>
                            <td class="pcq-phone-cell">
                                <?php if ($customer->getPhone()): ?>
                                    <a href="tel:<?php echo esc_attr($customer->getPhone()); ?>">
                                        <?php echo esc_html($customer->getPhone()); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="pcq-no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="pcq-address-cell">
                                <?php if ($customer->getAddress()): ?>
                                    <div class="pcq-address-text">
                                        <?php echo esc_html($customer->getAddress()); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="pcq-no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="pcq-count-cell">
                                <span class="pcq-count-badge pcq-count-quotes">
                                    <?php echo $customer->getTotalQuotes(); ?>
                                </span>
                            </td>
                            <td class="pcq-count-cell">
                                <span class="pcq-count-badge pcq-count-appointments">
                                    <?php echo $customer->getTotalAppointments(); ?>
                                </span>
                            </td>
                            <td class="pcq-price-cell">
                                <strong>€<?php echo number_format($customer->getTotalSpent(), 2); ?></strong>
                            </td>
                            <td class="pcq-date-cell">
                                <?php if ($customer->getLastActivity()): ?>
                                    <div><?php echo date('M j, Y', strtotime($customer->getLastActivity())); ?></div>
                                    <small><?php echo date('H:i', strtotime($customer->getLastActivity())); ?></small>
                                <?php else: ?>
                                    <span class="pcq-no-data">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="pcq-status pcq-status-<?php echo esc_attr($customer->getStatus()); ?>">
                                    <?php echo esc_html(ucfirst($customer->getStatus())); ?>
                                </span>
                            </td>
                            <td>
                                <div class="pcq-actions-dropdown">
                                    <button type="button" class="pcq-actions-toggle" aria-label="<?php _e('Actions', 'pro-clean-quotation'); ?>">
                                        <span class="pcq-dots">⋯</span>
                                    </button>
                                    <div class="pcq-actions-menu">
                                        <a href="<?php echo admin_url('admin.php?page=pcq-quotes&s=' . urlencode($customer->getEmail())); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">📋</span>
                                            <?php _e('View Quotes', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <a href="<?php echo admin_url('admin.php?page=pcq-appointments&s=' . urlencode($customer->getEmail())); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">📅</span>
                                            <?php _e('View Appointments', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <div class="pcq-action-divider"></div>
                                        
                                        <a href="mailto:<?php echo esc_attr($customer->getEmail()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">✉️</span>
                                            <?php _e('Send Email', 'pro-clean-quotation'); ?>
                                        </a>
                                        
                                        <?php if ($customer->getPhone()): ?>
                                        <a href="tel:<?php echo esc_attr($customer->getPhone()); ?>" 
                                           class="pcq-action-item">
                                            <span class="pcq-action-icon">📞</span>
                                            <?php _e('Call Customer', 'pro-clean-quotation'); ?>
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
        
        <!-- Pagination or Footer Spacer -->
        <?php if ($customers_data['pages'] > 1): ?>
            <div class="pcq-pagination">
                <?php
                $pagination_args = [
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo; Previous', 'pro-clean-quotation'),
                    'next_text' => __('Next &raquo;', 'pro-clean-quotation'),
                    'total' => $customers_data['pages'],
                    'current' => $customers_data['current_page']
                ];
                echo paginate_links($pagination_args);
                ?>
            </div>
        <?php else: ?>
            <div class="pcq-table-footer">
                <span class="pcq-results-count">
                    <?php 
                    printf(
                        _n('%s customer found', '%s customers found', $customers_data['total'], 'pro-clean-quotation'),
                        number_format_i18n($customers_data['total'])
                    ); 
                    ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.wrap {
    margin-bottom: 0 !important;
}

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
    font-size: 14px;
}

.pcq-filter-select:focus {
    outline: none;
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
}

.pcq-search-wrapper {
    display: flex;
    gap: 5px;
    margin-left: auto;
}

.pcq-search-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
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
    margin-bottom: 0;
}

/* Table Layout */
.pcq-customers-table {
    table-layout: fixed;
    width: 100%;
    margin: 0 !important;
    border-radius: 0;
}

/* Enable horizontal scroll only for table */
.pcq-table-wrapper {
    overflow-x: auto;
}

/* Column widths */
.pcq-customers-table thead th:nth-child(1),
.pcq-customers-table tbody td:nth-child(1) {
    width: 140px;
}

.pcq-customers-table thead th:nth-child(2),
.pcq-customers-table tbody td:nth-child(2) {
    width: 180px;
}

.pcq-customers-table thead th:nth-child(3),
.pcq-customers-table tbody td:nth-child(3) {
    width: 130px;
}

.pcq-customers-table thead th:nth-child(4),
.pcq-customers-table tbody td:nth-child(4) {
    width: 200px;
}

.pcq-customers-table thead th:nth-child(5),
.pcq-customers-table tbody td:nth-child(5) {
    width: 70px;
}

.pcq-customers-table thead th:nth-child(6),
.pcq-customers-table tbody td:nth-child(6) {
    width: 100px;
}

.pcq-customers-table thead th:nth-child(7),
.pcq-customers-table tbody td:nth-child(7) {
    width: 100px;
}

.pcq-customers-table thead th:nth-child(8),
.pcq-customers-table tbody td:nth-child(8) {
    width: 120px;
}

.pcq-customers-table thead th:nth-child(9),
.pcq-customers-table tbody td:nth-child(9) {
    width: 80px;
}

.pcq-customers-table thead th:nth-child(10),
.pcq-customers-table tbody td:nth-child(10) {
    width: 60px;
}

/* Table headers */
.pcq-customers-table thead th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    padding: 12px 10px;
}

/* Cell styling */
.pcq-customer-name {
    font-weight: 600;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: block;
}

.pcq-email-cell {
    padding: 8px 10px !important;
}

.pcq-customer-email {
    color: #2271b1;
    text-decoration: none;
    word-break: break-word;
    display: block;
    font-size: 13px;
}

.pcq-customer-email:hover {
    text-decoration: underline;
    color: #135e96;
}

.pcq-phone-cell a {
    color: #2271b1;
    text-decoration: none;
    white-space: nowrap;
}

.pcq-phone-cell a:hover {
    text-decoration: underline;
    color: #135e96;
}

.pcq-address-cell {
    padding: 8px 10px !important;
}

.pcq-address-text {
    font-size: 13px;
    line-height: 1.4;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    max-height: 2.8em;
}

.pcq-count-cell {
    text-align: center;
}

.pcq-count-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    min-width: 32px;
    text-align: center;
}

.pcq-count-quotes {
    background: #e3f2fd;
    color: #1976d2;
}

.pcq-count-appointments {
    background: #f3e5f5;
    color: #7b1fa2;
}

.pcq-price-cell {
    text-align: right;
    white-space: nowrap;
}

.pcq-date-cell {
    white-space: nowrap;
}

.pcq-date-cell small {
    display: block;
    color: #666;
    font-size: 12px;
}

.pcq-no-data {
    color: #999;
    font-style: italic;
}

/* Status badges */
.pcq-status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
    letter-spacing: 0.3px;
}

.pcq-status-active {
    background: #e8f5e8;
    color: #388e3c;
}

.pcq-status-inactive {
    background: #fafafa;
    color: #616161;
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
    z-index: 10000;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    padding: 6px 0;
    margin-top: 4px;
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
    margin-right: 8px;
    font-size: 14px;
    width: 16px;
    text-align: center;
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

/* Table Footer (when no pagination) */
.pcq-table-footer {
    padding: 12px 20px;
    text-align: center;
    border-top: 1px solid #ccd0d4;
    background: #f9f9f9;
    margin-top: -1px;
}

.pcq-results-count {
    color: #666;
    font-size: 13px;
    font-weight: 500;
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

/* Responsive adjustments */
@media (max-width: 1200px) {
    .pcq-search-wrapper {
        margin-left: 0;
        width: 100%;
    }
    
    .pcq-search-input {
        flex: 1;
    }
}

@media (max-width: 768px) {
    .pcq-filters-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .pcq-filter-select,
    .pcq-search-input {
        width: 100%;
        min-width: auto;
    }
    
    .pcq-search-wrapper {
        width: 100%;
    }
    
    .pcq-actions-menu {
        right: auto;
        left: 0;
        min-width: 160px;
    }
}

.search-box {
    display: none;
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
