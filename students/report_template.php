<?php
/**
 * Enhanced realistic daily report template optimized for portrait PDF printing
 * Fixed overlapping text and proper image placement
 */
if (!function_exists('renderReportTemplate')) {
    function renderReportTemplate(array $student, string $entryDate, array $entries, string $themeColor): string {
        $studentName = htmlspecialchars($student['name'] ?? 'Monerah Cambia');
        $studentId = htmlspecialchars($student['student_id'] ?? 'TC-25-B-00566');
        $studentDept = htmlspecialchars($student['department'] ?? 'Bachelor of Science in Computer Science');
        $studentEmail = htmlspecialchars($student['email'] ?? 'monerah.cambia@example.com');
        $studentProgram = htmlspecialchars($student['program'] ?? $studentDept);
        $orgName = htmlspecialchars($student['organization'] ?? 'TechCorp Solutions Inc.');
        $orgAddress = htmlspecialchars($student['org_address'] ?? '123 Tech Park Avenue, Makati City');
        $supervisor = htmlspecialchars($student['supervisor'] ?? 'Mr. James Dela Cruz');
        $supervisorTitle = htmlspecialchars($student['supervisor_title'] ?? 'Senior Software Engineer');
        $trainingPeriod = htmlspecialchars($student['training_period'] ?? 'June 2026 - August 2026');
        $hoursRendered = htmlspecialchars($student['hours_rendered'] ?? '8');
        $totalHours = htmlspecialchars($student['total_hours'] ?? '240');
        $generatedOn = date('F j, Y g:i A');
        $formattedDate = date('F d, Y', strtotime($entryDate));
        $entryCount = count($entries);

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Narrative Report - <?= htmlspecialchars($formattedDate) ?></title>
            <style>
                /* ========== BASE RESET ========== */
                * { 
                    margin: 0; 
                    padding: 0; 
                    box-sizing: border-box; 
                }
                
                body { 
                    background: #e8ecf1; 
                    font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
                    padding: 20px;
                    color: #1a1a2e;
                    -webkit-font-smoothing: antialiased;
                }
                
                /* ========== MAIN CONTAINER ========== */
                .report-container {
                    max-width: 210mm;
                    margin: 0 auto;
                    background: #ffffff;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.08);
                    overflow: hidden;
                }
                
                /* ========== HEADER ========== */
                .report-header {
                    background: linear-gradient(135deg, <?= $themeColor ?> 0%, #1a1a2e 100%);
                    padding: 28px 35px 18px 35px;
                    color: #ffffff;
                    position: relative;
                    border-bottom: 5px solid #f7931e;
                }
                
                .header-top {
                    display: flex;
                    justify-content: space-between;
                    align-items: flex-start;
                    flex-wrap: wrap;
                    gap: 12px;
                }
                
                .header-brand {
                    display: flex;
                    align-items: center;
                    gap: 14px;
                }
                
                .header-brand .logo-placeholder {
                    width: 48px;
                    height: 48px;
                    background: rgba(255,255,255,0.12);
                    border-radius: 10px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 22px;
                    font-weight: 700;
                    color: #f7931e;
                    border: 2px solid rgba(255,255,255,0.15);
                    flex-shrink: 0;
                }
                
                .header-brand .brand-text .sub {
                    font-size: 10px;
                    letter-spacing: 2.5px;
                    text-transform: uppercase;
                    opacity: 0.7;
                    font-weight: 400;
                }
                
                .header-brand .brand-text h1 {
                    font-size: 26px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                    margin: 1px 0 0 0;
                    color: #ffffff;
                    line-height: 1.2;
                }
                
                .header-meta {
                    text-align: right;
                    font-size: 12px;
                    line-height: 1.8;
                    opacity: 0.9;
                }
                
                .header-meta span {
                    display: block;
                }
                
                .header-meta strong {
                    font-weight: 600;
                    color: #f7931e;
                }
                
                .header-badge {
                    display: inline-block;
                    background: #f7931e;
                    color: #1a1a2e;
                    padding: 3px 14px;
                    border-radius: 20px;
                    font-size: 10px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                    text-transform: uppercase;
                    margin-top: 4px;
                }
                
                /* ========== REPORT BODY ========== */
                .report-body {
                    padding: 25px 35px 20px 35px;
                }
                
                /* ========== INFO CARDS ========== */
                .info-cards {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 12px;
                    margin-bottom: 22px;
                }
                
                .info-card {
                    background: #f8f9fc;
                    border-radius: 10px;
                    padding: 12px 16px;
                    border: 1px solid #e9edf4;
                }
                
                .info-card .label {
                    font-size: 9px;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.8px;
                    color: #8a94a6;
                    margin-bottom: 3px;
                }
                
                .info-card .value {
                    font-size: 14px;
                    font-weight: 600;
                    color: #1a1a2e;
                    line-height: 1.3;
                }
                
                .info-card .value .highlight {
                    color: <?= $themeColor ?>;
                }
                
                /* ========== SECTION TITLE ========== */
                .section-title {
                    font-size: 17px;
                    font-weight: 700;
                    color: #1a1a2e;
                    margin: 25px 0 14px 0;
                    padding-bottom: 8px;
                    border-bottom: 2px solid #f0f2f5;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                
                .section-title .badge {
                    background: <?= $themeColor ?>;
                    color: #fff;
                    font-size: 10px;
                    padding: 2px 10px;
                    border-radius: 20px;
                    font-weight: 600;
                    letter-spacing: 0.3px;
                }
                
                .section-title .count-badge {
                    background: #f0f2f5;
                    color: #6b7a8f;
                    font-size: 11px;
                    padding: 2px 10px;
                    border-radius: 20px;
                    font-weight: 600;
                    margin-left: auto;
                }
                
                /* ========== ENTRY CARD ========== */
                .entry-card {
                    background: #ffffff;
                    border: 1px solid #e9edf4;
                    border-radius: 12px;
                    padding: 20px 22px;
                    margin-bottom: 16px;
                    page-break-inside: avoid;
                    overflow: hidden;
                }
                
                .entry-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 8px;
                    margin-bottom: 12px;
                    padding-bottom: 12px;
                    border-bottom: 1px solid #f0f2f5;
                }
                
                .entry-time {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex-wrap: wrap;
                }
                
                .entry-time .time-icon {
                    font-size: 18px;
                    line-height: 1;
                }
                
                .entry-time .time-text {
                    font-size: 15px;
                    font-weight: 600;
                    color: #1a1a2e;
                }
                
                .entry-time .time-date {
                    font-size: 12px;
                    color: #8a94a6;
                    font-weight: 400;
                }
                
                .entry-badge {
                    background: <?= $themeColor ?>;
                    color: #fff;
                    padding: 3px 12px;
                    border-radius: 20px;
                    font-size: 10px;
                    font-weight: 600;
                    letter-spacing: 0.3px;
                    white-space: nowrap;
                }
                
                /* ========== ENTRY CONTENT ========== */
                .entry-content {
                    line-height: 1.8;
                    color: #2d3748;
                    font-size: 14px;
                    margin-bottom: 10px;
                }
                
                .entry-content .note-label {
                    font-weight: 600;
                    display: block;
                    margin-bottom: 4px;
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    color: #8a94a6;
                }
                
                .entry-content .note-text {
                    white-space: pre-wrap;
                    line-height: 1.9;
                    word-wrap: break-word;
                }
                
                /* ========== GALLERY - IMAGES AT BOTTOM ========== */
                .gallery-section {
                    margin-top: 14px;
                    padding-top: 14px;
                    border-top: 1px solid #f0f2f5;
                    clear: both;
                }
                
                .gallery-section .gallery-label {
                    font-size: 11px;
                    font-weight: 600;
                    color: #8a94a6;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                    margin-bottom: 10px;
                    display: block;
                }
                
                .gallery {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                }
                
                .gallery-item {
                    width: 120px;
                    height: 120px;
                    border-radius: 10px;
                    overflow: hidden;
                    border: 1px solid #e9edf4;
                    background: #f8f9fc;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    flex-shrink: 0;
                }
                
                .gallery-item img {
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }
                
                .gallery-item .placeholder {
                    color: #b0b8c5;
                    font-size: 12px;
                    text-align: center;
                    padding: 10px;
                }
                
                .gallery-item .placeholder .icon {
                    font-size: 28px;
                    display: block;
                    margin-bottom: 4px;
                }
                
                /* ========== SIGNATURES ========== */
                .signature-section {
                    margin-top: 25px;
                    padding-top: 20px;
                    border-top: 2px solid #f0f2f5;
                    clear: both;
                }
                
                .signature-row {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 30px;
                    margin-top: 12px;
                }
                
                .signature-box {
                    flex: 1;
                    min-width: 180px;
                    text-align: center;
                }
                
                .signature-box .sig-line {
                    margin-top: 40px;
                    padding-top: 10px;
                    border-top: 2px dashed #cbd5e0;
                    font-weight: 600;
                    color: #4a5568;
                    font-size: 13px;
                }
                
                .signature-box .sig-details {
                    font-size: 12px;
                    color: #8a94a6;
                    margin-top: 4px;
                }
                
                .signature-box .sig-label {
                    font-size: 10px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    color: #b0b8c5;
                    font-weight: 600;
                }
                
                /* ========== STATS FOOTER ========== */
                .stats-footer {
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: space-between;
                    align-items: center;
                    padding: 14px 0 6px 0;
                    border-top: 1px solid #f0f2f5;
                    margin-top: 18px;
                    font-size: 12px;
                    color: #8a94a6;
                    clear: both;
                }
                
                .stats-footer .stat-item {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                
                .stats-footer .stat-item strong {
                    color: #1a1a2e;
                    font-weight: 600;
                }
                
                /* ========== FOOTER ========== */
                .report-footer {
                    background: #f8f9fc;
                    padding: 14px 35px;
                    text-align: center;
                    font-size: 11px;
                    color: #b0b8c5;
                    border-top: 1px solid #e9edf4;
                    letter-spacing: 0.3px;
                    line-height: 1.6;
                    clear: both;
                }
                
                .report-footer .org-name {
                    color: #6b7a8f;
                    font-weight: 500;
                }
                
                /* ========== NO ENTRIES ========== */
                .no-entries {
                    text-align: center;
                    padding: 35px 20px;
                    color: #8a94a6;
                }
                
                .no-entries .icon {
                    font-size: 40px;
                    display: block;
                    margin-bottom: 10px;
                }
                
                /* ============================================================ */
                /* ========== PRINT STYLES ========== */
                /* ============================================================ */
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 12mm 14mm 12mm 14mm;
                    }
                    
                    body {
                        background: #ffffff !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        font-size: 11pt;
                        line-height: 1.5;
                        color: #000000;
                    }
                    
                    .report-container {
                        max-width: 100% !important;
                        box-shadow: none !important;
                        border-radius: 0 !important;
                        margin: 0 !important;
                        background: #ffffff !important;
                    }
                    
                    .report-header {
                        padding: 20px 25px 14px 25px !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        border-bottom-width: 4px !important;
                    }
                    
                    .header-brand .logo-placeholder,
                    .header-badge,
                    .entry-badge,
                    .section-title .badge {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    
                    .report-body {
                        padding: 18px 25px 15px 25px !important;
                    }
                    
                    .info-card {
                        background: #f8f9fc !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        border-color: #e0e4ea !important;
                    }
                    
                    .entry-card {
                        border-color: #d0d4da !important;
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                        margin-bottom: 14px !important;
                        padding: 16px 18px !important;
                    }
                    
                    .gallery-item {
                        border-color: #d0d4da !important;
                        background: #fafafa !important;
                        width: 100px !important;
                        height: 100px !important;
                    }
                    
                    .gallery-item .placeholder {
                        color: #999 !important;
                    }
                    
                    .signature-box .sig-line {
                        border-top-color: #999 !important;
                        margin-top: 35px !important;
                    }
                    
                    .stats-footer {
                        border-top-color: #d0d4da !important;
                        font-size: 11px !important;
                    }
                    
                    .report-footer {
                        background: #f8f9fc !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        padding: 12px 25px !important;
                        font-size: 10px !important;
                        border-top-color: #d0d4da !important;
                    }
                    
                    .highlight {
                        color: <?= $themeColor ?> !important;
                    }
                    
                    .section-title {
                        page-break-after: avoid !important;
                        break-after: avoid !important;
                    }
                    
                    .entry-card:last-child {
                        page-break-after: avoid !important;
                        break-after: avoid !important;
                    }
                }
                
                /* ========== RESPONSIVE ========== */
                @media screen and (max-width: 768px) {
                    body { padding: 12px 8px; }
                    .report-body { padding: 16px; }
                    .report-header { padding: 18px 16px 14px 16px; }
                    .header-top { flex-direction: column; }
                    .header-meta { text-align: left; width: 100%; }
                    .info-cards { grid-template-columns: 1fr; }
                    .entry-card { padding: 14px; }
                    .gallery-item { width: 80px; height: 80px; }
                    .signature-row { flex-direction: column; gap: 16px; }
                    .stats-footer { flex-direction: column; gap: 6px; text-align: center; }
                    .entry-header { flex-direction: column; align-items: flex-start; }
                    .entry-badge { align-self: flex-start; }
                    .report-footer { padding: 10px 16px; }
                    .header-brand .brand-text h1 { font-size: 20px; }
                }
                
                @media screen and (max-width: 480px) {
                    .header-brand .logo-placeholder { width: 36px; height: 36px; font-size: 18px; }
                    .header-brand .brand-text h1 { font-size: 17px; }
                    .section-title { font-size: 15px; flex-wrap: wrap; }
                    .entry-time .time-text { font-size: 13px; }
                    .entry-time .time-date { font-size: 11px; }
                    .gallery-item { width: 65px; height: 65px; }
                }
            </style>
        </head>
        <body>
            <div class="report-container">
                
                <!-- ===== HEADER ===== -->
                <div class="report-header">
                    <div class="header-top">
                        <div class="header-brand">
                            <div class="logo-placeholder">📋</div>
                            <div class="brand-text">
                                <div class="sub">On-the-Job Training</div>
                                <h1>NARRATIVE REPORT</h1>
                            </div>
                        </div>
                        <div class="header-meta">
                            <span><strong>Date:</strong> <?= htmlspecialchars($formattedDate) ?></span>
                            <span><strong>Student ID:</strong> <?= $studentId ?></span>
                            <span><strong>Name:</strong> <?= $studentName ?></span>
                            <span class="header-badge">Daily Documentary</span>
                        </div>
                    </div>
                </div>
                
                <!-- ===== BODY ===== -->
                <div class="report-body">
                    
                    <!-- Information Cards -->
                    <div class="info-cards">
                        <div class="info-card">
                            <div class="label">Department / Program</div>
                            <div class="value"><?= $studentProgram ?></div>
                        </div>
                        <div class="info-card">
                            <div class="label">Report Type</div>
                            <div class="value">Daily Documentary Report</div>
                        </div>
                        <div class="info-card">
                            <div class="label">Organization</div>
                            <div class="value"><?= $orgName ?></div>
                        </div>
                        <div class="info-card">
                            <div class="label">Total Entries</div>
                            <div class="value"><span class="highlight"><?= $entryCount ?></span> entry(s)</div>
                        </div>
                    </div>
                    
                    <!-- Activity Log -->
                    <div class="section-title">
                        📋 Activity Log
                        <span class="badge">Daily Tasks</span>
                        <span class="count-badge"><?= $entryCount ?> entry</span>
                    </div>
                    
                    <?php if (empty($entries)) : ?>
                        <div class="no-entries">
                            <span class="icon">📭</span>
                            <p>No entries found for this date.</p>
                        </div>
                    <?php else : ?>
                        <?php foreach ($entries as $entry) : ?>
                            <?php 
                            $timeLabel = date('h:i A • F d, Y', strtotime($entry['created_at']));
                            $noteContent = !empty($entry['note']) ? $entry['note'] : 'No description provided.';
                            $images = getEntryImagePaths($entry);
                            ?>
                            <div class="entry-card">
                                <!-- Entry Header -->
                                <div class="entry-header">
                                    <div class="entry-time">
                                        <span class="time-icon">🕐</span>
                                        <span class="time-text"><?= date('h:i A', strtotime($entry['created_at'])) ?></span>
                                        <span class="time-date">• <?= date('F d, Y', strtotime($entry['created_at'])) ?></span>
                                    </div>
                                    <span class="entry-badge">Daily Report Entry</span>
                                </div>
                                
                                <!-- Entry Content (Text) -->
                                <div class="entry-content">
                                    <span class="note-label">📝 Description</span>
                                    <div class="note-text"><?= nl2br(htmlspecialchars($noteContent)) ?></div>
                                </div>
                                
                                <!-- Images at the Bottom -->
                                <?php if (!empty($images)) : ?>
                                    <div class="gallery-section">
                                        <span class="gallery-label">🖼️ Attached Images</span>
                                        <div class="gallery">
                                            <?php foreach ($images as $imagePath) : ?>
                                                <?php $dataUri = getImageDataUri($imagePath); ?>
                                                <?php if ($dataUri) : ?>
                                                    <div class="gallery-item">
                                                        <img src="<?= $dataUri ?>" alt="Activity documentation">
                                                    </div>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <div class="gallery-section">
                                        <span class="gallery-label">🖼️ Attached Images</span>
                                        <div class="gallery">
                                            <div class="gallery-item">
                                                <div class="placeholder">
                                                    <span class="icon">🖼️</span>
                                                    No images attached
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <!-- Signatures -->
                    <div class="signature-section">
                        <div class="section-title" style="margin-top:0;">
                            ✍️ Signatures
                            <span class="badge">Verification</span>
                        </div>
                        
                        <div class="signature-row">
                            <div class="signature-box">
                                <div class="sig-label">Student</div>
                                <div class="sig-line"><?= $studentName ?></div>
                                <div class="sig-details">Date: <?= htmlspecialchars($formattedDate) ?></div>
                            </div>
                            <div class="signature-box">
                                <div class="sig-label">Supervisor</div>
                                <div class="sig-line"><?= $supervisor ?></div>
                                <div class="sig-details"><?= $supervisorTitle ?> • <?= $orgName ?></div>
                                <div class="sig-details">Date: <?= htmlspecialchars($formattedDate) ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stats Footer -->
                    <div class="stats-footer">
                        <div class="stat-item">
                            📅 <strong>Training Period:</strong> <?= $trainingPeriod ?>
                        </div>
                        <div class="stat-item">
                            ⏱️ <strong>Hours Rendered:</strong> <?= $hoursRendered ?> hours
                        </div>
                        <div class="stat-item">
                            📊 <strong>Total Hours:</strong> <?= $totalHours ?> hours
                        </div>
                    </div>
                    
                </div>
                
                <!-- ===== FOOTER ===== -->
                <div class="report-footer">
                    This daily documentary report is submitted as part of the On-the-Job Training requirements.<br>
                    <span class="org-name"><?= $orgName ?></span> • <?= $orgAddress ?> • Generated on <?= htmlspecialchars($generatedOn) ?>
                </div>
                
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
}

/**
 * Helper function to get image data URI
 * 
 * @param string $path The file path to the image
 * @return string The base64 encoded data URI or empty string if file doesn't exist
 */
if (!function_exists('getImageDataUri')) {
    function getImageDataUri(string $path): string {
        if (empty($path) || !file_exists($path)) {
            return '';
        }
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        if ($data === false) {
            return '';
        }
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }
}

/**
 * Helper function to get entry image paths
 * 
 * @param array $entry The entry array containing image paths
 * @return array Array of image paths
 */
if (!function_exists('getEntryImagePaths')) {
    function getEntryImagePaths(array $entry): array {
        if (empty($entry['images']) || !is_array($entry['images'])) {
            return [];
        }
        return $entry['images'];
    }
}
?>