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
    <div class="pcq-filters-container">
        <form method="get" action="" class="pcq-filters-form">
            <input type="hidden" name="page" value="pcq-quotes">
            
            <div class="pcq-filters-row">
                <select name="status" class="pcq-filter-select">
                    <option value=""><?php _e('All Statuses', 'pro-clean-quotation'); ?></option>
                    <option value="new" <?php selected($status_filter, 'new'); ?>><?php _e('New', 'pro-clean-quotation'); ?></option>
                    <option value="viewed" <?php selected($status_filter, 'viewed'); ?>><?php _e('Viewed', 'pro-clean-quotation'); ?></option>
                    <option value="booked" <?php selected($status_filter, 'booked'); ?>><?php _e('Booked', 'pro-clean-quotation'); ?></option>
                    <option value="expired" <?php selected($status_filter, 'expired'); ?>><?php _e('Expired', 'pro-clean-quotation'); ?></option>
                    <option value="declined" <?php selected($status_filter, 'declined'); ?>><?php _e('Declined', 'pro-clean-quotation'); ?></option>
                </select>
                
                <select name="service_type" class="pcq-filter-select">
                    <option value=""><?php _e('All Services', 'pro-clean-quotation'); ?></option>
                    <option value="facade" <?php selected($service_filter, 'facade'); ?>><?php _e('Façade Cleaning', 'pro-clean-quotation'); ?></option>
                    <option value="roof" <?php selected($service_filter, 'roof'); ?>><?php _e('Roof Cleaning', 'pro-clean-quotation'); ?></option>
                    <option value="both" <?php selected($service_filter, 'both'); ?>><?php _e('Both Services', 'pro-clean-quotation'); ?></option>
                </select>
                
                <button type="submit" class="button"><?php _e('Filter', 'pro-clean-quotation'); ?></button>
                
                <div class="pcq-search-wrapper">
                    <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="<?php _e('Search quotes...', 'pro-clean-quotation'); ?>" class="pcq-search-input">
                    <button type="submit" class="button"><?php _e('Search', 'pro-clean-quotation'); ?></button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Quotes Table -->
    <div class="pcq-table-wrapper">
        <table class="wp-list-table widefat fixed striped pcq-quotes-table">
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
                            <td class="pcq-customer-cell">
                                <div class="pcq-customer-name"><?php echo esc_html($quote->getCustomerName()); ?></div>
                                <div class="pcq-customer-contact">
                                    <a href="mailto:<?php echo esc_attr($quote->getCustomerEmail()); ?>" class="pcq-customer-email">
                                        <?php echo esc_html($quote->getCustomerEmail()); ?>
                                    </a>
                                </div>
                                <div class="pcq-customer-contact">
                                    <a href="tel:<?php echo esc_attr($quote->getCustomerPhone()); ?>">
                                        <?php echo esc_html($quote->getCustomerPhone()); ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <div class="pcq-service-type"><?php echo esc_html($quote->getServiceName()); ?></div>
                                <small><?php echo number_format($quote->getSquareMeters(), 1); ?> sqm</small>
                            </td>
                            <td>
                                <div><?php echo esc_html($quote->getPropertyType()); ?></div>
                                <small><?php echo esc_html($quote->getPostalCode()); ?></small>
                            </td>
                            <td>
                                <strong>€<?php echo number_format($quote->getTotalPrice(), 2); ?></strong>
                            </td>
                            <td>
                                <span class="pcq-status pcq-status-<?php echo esc_attr($quote->getStatus()); ?>">
                                    <?php echo esc_html(ucfirst($quote->getStatus())); ?>
                                </span>
                            </td>
                            <td>
                                <div><?php echo date('M j, Y', strtotime($quote->getCreatedAt())); ?></div>
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
    
    <!-- Pagination -->
    <?php if ($quotes_data['pages'] > 1): ?>
        <div class="pcq-pagination">
            <?php
            $pagination_args = [
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo; Previous', 'pro-clean-quotation'),
                'next_text' => __('Next &raquo;', 'pro-clean-quotation'),
                'total' => $quotes_data['pages'],
                'current' => $quotes_data['current_page']
            ];
            echo paginate_links($pagination_args);
            ?>
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
    overflow-x: auto;
    overflow-y: visible;
}

/* Table Layout */
.pcq-quotes-table {
    table-layout: fixed;
    width: 100%;
    margin: 0 !important;
}

/* Column widths */
.pcq-quotes-table thead th:nth-child(1),
.pcq-quotes-table tbody td:nth-child(1) {
    width: 110px;
}

.pcq-quotes-table thead th:nth-child(2),
.pcq-quotes-table tbody td:nth-child(2) {
    width: 220px;
}

.pcq-quotes-table thead th:nth-child(3),
.pcq-quotes-table tbody td:nth-child(3) {
    width: 130px;
}

.pcq-quotes-table thead th:nth-child(4),
.pcq-quotes-table tbody td:nth-child(4) {
    width: 120px;
}

.pcq-quotes-table thead th:nth-child(5),
.pcq-quotes-table tbody td:nth-child(5) {
    width: 100px;
}

.pcq-quotes-table thead th:nth-child(6),
.pcq-quotes-table tbody td:nth-child(6) {
    width: 100px;
}

.pcq-quotes-table thead th:nth-child(7),
.pcq-quotes-table tbody td:nth-child(7) {
    width: 110px;
}

.pcq-quotes-table thead th:nth-child(8),
.pcq-quotes-table tbody td:nth-child(8) {
    width: 60px;
}

/* Table headers */
.pcq-quotes-table thead th {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Customer cell styling */
.pcq-customer-cell {
    padding: 8px 10px !important;
}

.pcq-customer-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 4px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pcq-customer-contact {
    font-size: 13px;
    line-height: 1.4;
    margin-bottom: 2px;
}

.pcq-customer-email {
    color: #2271b1;
    text-decoration: none;
    word-break: break-all;
    display: inline-block;
    max-width: 100%;
}

.pcq-customer-email:hover {
    text-decoration: underline;
    color: #135e96;
}

.pcq-service-type {
    font-weight: 500;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
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

/* Responsive */
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