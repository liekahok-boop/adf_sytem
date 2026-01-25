<?php
/**
 * Report PDF Helper Functions
 * Generate elegant PDF reports with company info, logo, and professional styling
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Get company information from settings
 */
function getCompanyInfo() {
    $db = Database::getInstance();
    
    return [
        'name' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_name'")['setting_value'] ?? BUSINESS_NAME,
        'logo' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_logo'")['setting_value'] ?? null,
        'invoice_logo' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'invoice_logo'")['setting_value'] ?? null,
        'address' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_address'")['setting_value'] ?? '',
        'phone' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_phone'")['setting_value'] ?? '',
        'email' => $db->fetchOne("SELECT setting_value FROM settings WHERE setting_key = 'company_email'")['setting_value'] ?? '',
        'icon' => BUSINESS_ICON ?? '🏢',
        'color' => BUSINESS_COLOR ?? '#3b82f6'
    ];
}

/**
 * Generate Report Header HTML with Logo and Company Info
 */
function generateReportHeader($title, $subtitle = '', $dateRange = '') {
    $company = getCompanyInfo();
    
    // Determine logo to display
    $displayLogo = $company['invoice_logo'] ?? $company['logo'];
    $logoHtml = '';
    
    if ($displayLogo && file_exists($displayLogo)) {
        $logoHtml = '<img src="' . $displayLogo . '" alt="Logo" style="width: 80px; height: 80px; object-fit: contain;">';
    } else {
        $logoHtml = '<div style="width: 80px; height: 80px; font-size: 48px; display: flex; align-items: center; justify-content: center;">' . $company['icon'] . '</div>';
    }
    
    $html = '
    <div style="display: flex; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 3px solid ' . $company['color'] . ';">
        <div style="flex-shrink: 0;">
            ' . $logoHtml . '
        </div>
        <div style="flex: 1;">
            <div style="font-size: 28px; font-weight: 900; color: ' . $company['color'] . '; margin-bottom: 0.25rem; letter-spacing: -0.5px;">
                ' . htmlspecialchars($company['name']) . '
            </div>
            <div style="font-size: 11px; color: #666; line-height: 1.6;">
                ' . ($company['address'] ? htmlspecialchars($company['address']) . '<br>' : '') . '
                ' . ($company['phone'] ? 'Tel: ' . htmlspecialchars($company['phone']) . '<br>' : '') . '
                ' . ($company['email'] ? 'Email: ' . htmlspecialchars($company['email']) : '') . '
            </div>
        </div>
        <div style="text-align: right; min-width: 200px;">
            <div style="font-size: 12px; color: #666;">
                <div style="margin-bottom: 0.5rem;">
                    <strong>Laporan:</strong><br>
                    <span style="font-size: 18px; font-weight: 700; color: ' . $company['color'] . '; display: block;">
                        ' . htmlspecialchars($title) . '
                    </span>
                </div>
                ' . ($subtitle ? '<div style="color: #999; font-size: 11px; margin-bottom: 0.5rem;">' . htmlspecialchars($subtitle) . '</div>' : '') . '
                ' . ($dateRange ? '<div style="color: #666; font-weight: 600;">' . htmlspecialchars($dateRange) . '</div>' : '') . '
            </div>
        </div>
    </div>
    ';
    
    return $html;
}

/**
 * Generate Summary Card HTML for Print
 */
function generateSummaryCard($label, $value, $color = '#10b981', $icon = '') {
    return '
    <div style="background: linear-gradient(135deg, ' . $color . '10 0%, ' . $color . '05 100%); border: 2px solid ' . $color . '; border-radius: 8px; padding: 1.25rem; text-align: center; page-break-inside: avoid;">
        <div style="font-size: 11px; color: #666; text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
            ' . $icon . ' ' . htmlspecialchars($label) . '
        </div>
        <div style="font-size: 22px; font-weight: 900; color: ' . $color . '; line-height: 1.2;">
            ' . htmlspecialchars($value) . '
        </div>
    </div>
    ';
}

/**
 * Generate Report Footer with timestamp and page info
 */
function generateReportFooter() {
    $company = getCompanyInfo();
    
    return '
    <div style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #ddd; text-align: center; font-size: 10px; color: #999;">
        <div style="margin-bottom: 0.5rem;">
            <strong>' . htmlspecialchars($company['name']) . '</strong>
        </div>
        <div>Dicetak pada: ' . date('d M Y H:i:s') . '</div>
        <div style="margin-top: 0.5rem; font-style: italic;">Laporan ini adalah dokumen resmi dari ' . htmlspecialchars($company['name']) . '</div>
    </div>
    ';
}

/**
 * Generate Summary Table HTML
 */
function generateSummaryTable($data) {
    $html = '
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; font-size: 12px;">
        <thead>
            <tr style="background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); font-weight: 700;">
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #d1d5db;">Periode</th>
                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #d1d5db;">Pemasukan</th>
                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #d1d5db;">Pengeluaran</th>
                <th style="padding: 12px; text-align: right; border-bottom: 2px solid #d1d5db;">Net Balance</th>
                <th style="padding: 12px; text-align: center; border-bottom: 2px solid #d1d5db;">Transaksi</th>
            </tr>
        </thead>
        <tbody>
    ';
    
    $rowCount = 0;
    foreach ($data as $row) {
        $bgColor = ($rowCount % 2 === 0) ? '#f9fafb' : '#ffffff';
        $netColor = ($row['net_balance'] >= 0) ? '#10b981' : '#ef4444';
        
        $html .= '
            <tr style="background: ' . $bgColor . ';">
                <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb;">' . htmlspecialchars($row['period']) . '</td>
                <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #10b981; font-weight: 600;">
                    Rp ' . number_format($row['income'], 0, ',', '.') . '
                </td>
                <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: right; color: #ef4444; font-weight: 600;">
                    Rp ' . number_format($row['expense'], 0, ',', '.') . '
                </td>
                <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: right; color: ' . $netColor . '; font-weight: 700;">
                    Rp ' . number_format($row['net_balance'], 0, ',', '.') . '
                </td>
                <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; text-align: center; color: #666;">
                    ' . $row['transaction_count'] . '
                </td>
            </tr>
        ';
        $rowCount++;
    }
    
    $html .= '
        </tbody>
    </table>
    ';
    
    return $html;
}

/**
 * Generate Signature Section for printed reports
 */
function generateSignatureSection() {
    $company = getCompanyInfo();
    
    return '
    <div style="margin-top: 3rem; page-break-inside: avoid;">
        <table style="width: 100%; font-size: 11px;">
            <tr>
                <td style="width: 33%; text-align: center;">
                    <div style="border-top: 1px solid #000; padding-top: 1rem; min-height: 60px;">
                        <div style="margin-top: 0.5rem;">Pembuat Laporan</div>
                    </div>
                </td>
                <td style="width: 34%;"></td>
                <td style="width: 33%; text-align: center;">
                    <div style="border-top: 1px solid #000; padding-top: 1rem; min-height: 60px;">
                        <div style="margin-top: 0.5rem;">Persetujuan</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    ';
}

/**
 * Get Print CSS for reports
 */
function getReportPrintCSS() {
    return '
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            html, body {
                width: 210mm;
                height: 297mm;
                margin: 0;
                padding: 0;
            }
            
            body {
                font-family: "Segoe UI", "Trebuchet MS", sans-serif;
                font-size: 11px;
                color: #333;
                line-height: 1.5;
            }
            
            .report-container {
                width: 100%;
                margin: 0 auto;
                padding: 20mm;
                background: white;
            }
            
            .sidebar, .top-bar, .btn, form, .filter-section, .no-print {
                display: none !important;
            }
            
            .main-content {
                margin-left: 0;
                padding: 0;
            }
            
            h1, h2, h3 {
                page-break-after: avoid;
            }
            
            table {
                page-break-inside: avoid;
            }
            
            tr {
                page-break-inside: avoid;
            }
            
            .page-break {
                page-break-after: always;
            }
            
            .card {
                page-break-inside: avoid;
                border: none;
                background: white;
                box-shadow: none;
            }
            
            a {
                color: #000;
                text-decoration: none;
            }
            
            img {
                max-width: 100%;
                height: auto;
            }
        }
    </style>
    ';
}
?>
