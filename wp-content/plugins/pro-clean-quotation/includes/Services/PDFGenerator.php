<?php

namespace ProClean\Quotation\Services;

use ProClean\Quotation\Models\Quote;
use ProClean\Quotation\Admin\Settings;
use Mpdf\Mpdf;
use Mpdf\MpdfException;

/**
 * PDF Generator Service
 * 
 * @package ProClean\Quotation\Services
 * @since 1.0.0
 */
class PDFGenerator {
    
    /**
     * PDF generator instance
     * 
     * @var PDFGenerator
     */
    private static $instance = null;
    
    /**
     * mPDF instance
     * 
     * @var Mpdf
     */
    private $mpdf;
    
    /**
     * Get instance
     * 
     * @return PDFGenerator
     */
    public static function getInstance(): PDFGenerator {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Load Composer autoloader
        $autoload = PCQ_PLUGIN_DIR . 'vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }
    }
    
    /**
     * Generate quote PDF
     *
     * @param Quote $quote Quote object
     * @return string|false Path to generated PDF file or false on failure
     */
    public function generateQuotePDF(Quote $quote) {
        try {
            // Check if mPDF class exists (vendor autoload may not have loaded it)
            if (!class_exists('Mpdf\Mpdf')) {
                // Try to load Composer autoloader
                $autoload = PCQ_PLUGIN_DIR . 'vendor/autoload.php';
                if (file_exists($autoload)) {
                    require_once $autoload;
                }
                
                // If still doesn't exist, return false
                if (!class_exists('Mpdf\Mpdf')) {
                    error_log('PCQ PDF: mPDF library not available. Check vendor installation.');
                    return false;
                }
            }
            
            // Initialize mPDF
            $this->mpdf = new Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left' => 15,
                'margin_right' => 15,
                'margin_top' => 30,
                'margin_bottom' => 20,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);
            
            // Set document properties
            $company_name = Settings::get('company_name', get_bloginfo('name'));
            $this->mpdf->SetTitle($company_name . ' - Quote #' . $quote->getQuoteNumber());
            $this->mpdf->SetAuthor($company_name);
            $this->mpdf->SetCreator('Pro Clean Quotation System');
            
            // Add watermark if configured
            if (Settings::get('pdf_watermark_enabled', true)) {
                $this->mpdf->SetWatermarkText('QUOTE', 0.1);
                $this->mpdf->showWatermarkText = true;
            }
            
            // Generate HTML content
            $html = $this->getQuotePDFHTML($quote);
            
            // Write HTML to PDF
            $this->mpdf->WriteHTML($html);
            
            // Generate filename
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/pro-clean-quotes/temp/';
            
            // Create directory if it doesn't exist
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }
            
            $filename = 'quote-' . $quote->getQuoteNumber() . '-' . time() . '.pdf';
            $filepath = $temp_dir . $filename;
            
            // Output PDF to file
            $this->mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);
            
            return $filepath;
            
        } catch (MpdfException $e) {
            error_log('PCQ PDF Generation Error (MpdfException): ' . $e->getMessage());
            return false;
        } catch (\Exception $e) {
            error_log('PCQ PDF Generation Error (Exception): ' . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            error_log('PCQ PDF Generation Error (Throwable): ' . $e->getMessage());
            error_log('PCQ PDF: Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Get quote PDF HTML content
     * 
     * @param Quote $quote Quote object
     * @return string HTML content
     */
    private function getQuotePDFHTML(Quote $quote): string {
        $company_name = Settings::get('company_name', get_bloginfo('name'));
        $company_email = Settings::get('company_email', get_option('admin_email'));
        $company_phone = Settings::get('company_phone', '');
        $company_address = Settings::get('company_address', '');
        $company_logo = Settings::get('company_logo', '');
        
        $breakdown = $quote->getPriceBreakdown();
        
        // Start HTML
        $html = '<!DOCTYPE html>';
        $html .= '<html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<style>';
        $html .= $this->getPDFStyles();
        $html .= '</style>';
        $html .= '</head><body>';
        
        // Header with logo
        if ($company_logo && filter_var($company_logo, FILTER_VALIDATE_URL)) {
            $html .= '<div class="header">';
            $html .= '<img src="' . esc_url($company_logo) . '" alt="' . esc_attr($company_name) . '" class="logo">';
            $html .= '</div>';
        } else {
            $html .= '<div class="header">';
            $html .= '<h1 class="company-name">' . esc_html($company_name) . '</h1>';
            $html .= '</div>';
        }
        
        // Company contact info
        $html .= '<div class="company-info">';
        if ($company_address) {
            $html .= '<p>' . nl2br(esc_html($company_address)) . '</p>';
        }
        if ($company_phone) {
            $html .= '<p>Tel: ' . esc_html($company_phone) . '</p>';
        }
        if ($company_email) {
            $html .= '<p>Email: ' . esc_html($company_email) . '</p>';
        }
        $html .= '</div>';
        
        // Quote title
        $html .= '<div class="quote-header">';
        $html .= '<h2>QUOTATION</h2>';
        $html .= '<div class="quote-meta">';
        $html .= '<table>';
        $html .= '<tr><td><strong>Quote Number:</strong></td><td>#' . esc_html($quote->getQuoteNumber()) . '</td></tr>';
        $html .= '<tr><td><strong>Date:</strong></td><td>' . date('F j, Y', strtotime($quote->getCreatedAt())) . '</td></tr>';
        $html .= '<tr><td><strong>Valid Until:</strong></td><td>' . date('F j, Y', strtotime($quote->getValidUntil())) . '</td></tr>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '</div>';
        
        // Customer information
        $html .= '<div class="customer-section">';
        $html .= '<h3>Customer Information</h3>';
        $html .= '<table>';
        $html .= '<tr><td><strong>Name:</strong></td><td>' . esc_html($quote->getCustomerName()) . '</td></tr>';
        $html .= '<tr><td><strong>Email:</strong></td><td>' . esc_html($quote->getCustomerEmail()) . '</td></tr>';
        $html .= '<tr><td><strong>Phone:</strong></td><td>' . esc_html($quote->getCustomerPhone()) . '</td></tr>';
        $html .= '<tr><td><strong>Address:</strong></td><td>' . esc_html($quote->getPropertyAddress()) . '</td></tr>';
        $html .= '<tr><td><strong>Postal Code:</strong></td><td>' . esc_html($quote->getPostalCode()) . '</td></tr>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Service details
        $html .= '<div class="service-section">';
        $html .= '<h3>Service Details</h3>';
        $html .= '<table>';
        $html .= '<tr><td><strong>Service Type:</strong></td><td>' . esc_html(ucfirst($quote->getServiceType())) . ' Cleaning</td></tr>';
        $html .= '<tr><td><strong>Property Size:</strong></td><td>' . number_format($quote->getSquareMeters(), 1) . ' sqm</td></tr>';
        if ($quote->getLinearMeters() > 0) {
            $html .= '<tr><td><strong>Linear Meters:</strong></td><td>' . number_format($quote->getLinearMeters(), 1) . ' m</td></tr>';
        }
        $html .= '<tr><td><strong>Property Type:</strong></td><td>' . esc_html(ucfirst($quote->getPropertyType())) . '</td></tr>';
        $html .= '<tr><td><strong>Building Height:</strong></td><td>' . $quote->getBuildingHeight() . ' floors</td></tr>';
        $html .= '<tr><td><strong>Surface Material:</strong></td><td>' . esc_html(ucfirst($quote->getSurfaceMaterial())) . '</td></tr>';
        if ($quote->getRoofType()) {
            $html .= '<tr><td><strong>Roof Type:</strong></td><td>' . esc_html(ucfirst($quote->getRoofType())) . '</td></tr>';
        }
        $html .= '</table>';
        $html .= '</div>';
        
        // Special requirements
        if ($quote->getSpecialRequirements()) {
            $html .= '<div class="notes-section">';
            $html .= '<h3>Special Requirements</h3>';
            $html .= '<p>' . nl2br(esc_html($quote->getSpecialRequirements())) . '</p>';
            $html .= '</div>';
        }
        
        // Price breakdown
        $html .= '<div class="pricing-section">';
        $html .= '<h3>Price Breakdown</h3>';
        $html .= '<table class="price-table">';
        $html .= '<thead>';
        $html .= '<tr><th align="left">Description</th><th align="right">Amount</th></tr>';
        $html .= '</thead>';
        $html .= '<tbody>';
        
        foreach ($breakdown as $key => $item) {
            $row_class = $key === 'total' ? ' class="total-row"' : '';
            $html .= '<tr' . $row_class . '>';
            $html .= '<td>' . esc_html($item['label']) . '</td>';
            $html .= '<td align="right">€' . number_format($item['amount'], 2) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        
        // Terms and conditions
        $html .= '<div class="terms-section">';
        $html .= '<h3>Terms & Conditions</h3>';
        $html .= '<ol>';
        $html .= '<li>This quotation is valid for 30 days from the date of issue.</li>';
        $html .= '<li>This is an estimated quote based on the information provided. Final pricing may vary after on-site assessment.</li>';
        $html .= '<li>Payment terms: 50% deposit required upon booking confirmation, balance due upon completion.</li>';
        $html .= '<li>Cancellation policy: Free cancellation up to 48 hours before scheduled service. Cancellations within 48 hours forfeit the deposit.</li>';
        $html .= '<li>All prices are in EUR and include applicable VAT.</li>';
        $html .= '<li>Services are performed during business hours (Monday-Friday 8:00-18:00, Saturday 9:00-15:00).</li>';
        $html .= '<li>Customer must ensure access to the property and water source.</li>';
        $html .= '<li>Weather conditions may affect scheduling. We will reschedule at no additional cost if necessary.</li>';
        $html .= '</ol>';
        $html .= '</div>';
        
        // Footer
        $html .= '<div class="footer">';
        $html .= '<p><strong>Ready to book?</strong></p>';
        $html .= '<p>Contact us at ' . esc_html($company_phone) . ' or ' . esc_html($company_email) . '</p>';
        $html .= '<p class="thank-you">Thank you for choosing ' . esc_html($company_name) . '!</p>';
        $html .= '</div>';
        
        // Document footer with page numbers
        $html .= '<div class="page-footer">';
        $html .= '<p>Quote #' . esc_html($quote->getQuoteNumber()) . ' | ' . esc_html($company_name) . ' | Page {PAGENO} of {nbpg}</p>';
        $html .= '</div>';
        
        $html .= '</body></html>';
        
        return $html;
    }
    
    /**
     * Get PDF styles
     * 
     * @return string CSS styles
     */
    private function getPDFStyles(): string {
        return '
            @page {
                margin-top: 30mm;
                margin-bottom: 20mm;
            }
            
            body {
                font-family: "DejaVu Sans", sans-serif;
                font-size: 10pt;
                color: #333;
                line-height: 1.6;
            }
            
            .header {
                text-align: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 3px solid #2196F3;
            }
            
            .logo {
                max-width: 200px;
                height: auto;
            }
            
            .company-name {
                font-size: 24pt;
                color: #2196F3;
                margin: 0;
            }
            
            .company-info {
                text-align: center;
                font-size: 9pt;
                color: #666;
                margin-bottom: 20px;
            }
            
            .company-info p {
                margin: 2px 0;
            }
            
            .quote-header {
                margin: 30px 0;
                padding: 15px;
                background-color: #f5f5f5;
                border-left: 4px solid #2196F3;
            }
            
            .quote-header h2 {
                margin: 0 0 10px 0;
                font-size: 18pt;
                color: #2196F3;
            }
            
            .quote-meta table {
                width: 100%;
                font-size: 9pt;
            }
            
            .quote-meta td {
                padding: 3px 0;
            }
            
            .customer-section,
            .service-section,
            .notes-section,
            .pricing-section,
            .terms-section {
                margin: 20px 0;
            }
            
            h3 {
                font-size: 12pt;
                color: #2196F3;
                border-bottom: 1px solid #ddd;
                padding-bottom: 5px;
                margin-bottom: 10px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
            }
            
            table td {
                padding: 5px 10px;
                vertical-align: top;
            }
            
            table td:first-child {
                width: 40%;
            }
            
            .price-table {
                margin-top: 10px;
                border: 1px solid #ddd;
            }
            
            .price-table thead {
                background-color: #2196F3;
                color: white;
            }
            
            .price-table th {
                padding: 8px 10px;
                font-weight: bold;
            }
            
            .price-table tbody tr {
                border-bottom: 1px solid #eee;
            }
            
            .price-table tbody td {
                padding: 6px 10px;
            }
            
            .price-table .total-row {
                background-color: #f5f5f5;
                font-weight: bold;
                font-size: 11pt;
                border-top: 2px solid #2196F3;
            }
            
            .terms-section ol {
                font-size: 8pt;
                color: #666;
                padding-left: 20px;
            }
            
            .terms-section li {
                margin-bottom: 5px;
            }
            
            .footer {
                margin-top: 40px;
                padding-top: 20px;
                border-top: 2px solid #2196F3;
                text-align: center;
            }
            
            .thank-you {
                font-style: italic;
                color: #2196F3;
                font-size: 11pt;
            }
            
            .page-footer {
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                text-align: center;
                font-size: 8pt;
                color: #999;
            }
        ';
    }
    
    /**
     * Clean up temporary PDF files older than 24 hours
     */
    public static function cleanupTempFiles(): void {
        $upload_dir = wp_upload_dir();
        $temp_dir = $upload_dir['basedir'] . '/pro-clean-quotes/temp/';
        
        if (!file_exists($temp_dir)) {
            return;
        }
        
        $files = glob($temp_dir . '*.pdf');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file) >= 24 * 3600)) {
                @unlink($file);
            }
        }
    }
}
