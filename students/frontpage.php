<?php
/**
 * Front Page (Cover Page) template for the Narrative Report.
 * 
 * @param array $student Student details (name, id, program, organization, supervisor, etc.)
 * @param string $themeColor The primary theme color (hex)
 * @param string $reportTitle Optional custom title (default: "NARRATIVE REPORT")
 * @return string HTML output
 */
if (!function_exists('renderFrontPageTemplate')) {
    function renderFrontPageTemplate(array $student, string $themeColor, string $reportTitle = 'NARRATIVE REPORT'): string {
        $studentName = htmlspecialchars($student['name'] ?? 'Monerah Cambia');
        $studentId = htmlspecialchars($student['student_id'] ?? 'TC-25-B-00566');
        $studentProgram = htmlspecialchars($student['program'] ?? 'Bachelor of Science in Computer Science');
        $orgName = htmlspecialchars($student['organization'] ?? 'TechCorp Solutions Inc.');
        $orgAddress = htmlspecialchars($student['org_address'] ?? '123 Tech Park Avenue, Makati City');
        $supervisor = htmlspecialchars($student['supervisor'] ?? 'Mr. James Dela Cruz');
        $supervisorTitle = htmlspecialchars($student['supervisor_title'] ?? 'Senior Software Engineer');
        $trainingPeriod = htmlspecialchars($student['training_period'] ?? 'June 2026 - August 2026');
        $logoUrl = htmlspecialchars($student['logo'] ?? '');
        $generatedOn = date('F j, Y');

        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Front Page - <?= $studentName ?></title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
            <style>
                /* ========== CSS VARIABLES ========== */
                :root {
                    --theme: <?= $themeColor ?>;
                    --theme-dark: #1a1a2e;
                    --gray-50: #f8f9fc;
                    --gray-100: #f0f2f5;
                    --gray-200: #e9edf4;
                    --gray-300: #d0d4da;
                    --gray-500: #8a94a6;
                    --gray-600: #6b7a8f;
                    --gray-700: #4a5568;
                    --gray-900: #1a1a2e;
                    --radius: 12px;
                    --shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
                }

                /* ========== BASE ========== */
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    background: var(--gray-50);
                    font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
                    padding: 24px;
                    color: var(--gray-900);
                    -webkit-font-smoothing: antialiased;
                }
                .print-btn {
                    display: inline-block;
                    background: var(--theme);
                    color: #fff;
                    border: none;
                    padding: 10px 24px;
                    border-radius: 30px;
                    font-size: 14px;
                    font-weight: 600;
                    cursor: pointer;
                    margin-bottom: 20px;
                    transition: background 0.2s;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                }
                .print-btn:hover { background: var(--theme-dark); }
                @media print { .print-btn { display: none !important; } }

                .report-container {
                    max-width: 210mm;
                    margin: 0 auto;
                    background: #ffffff;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.06);
                    border-radius: var(--radius);
                    overflow: hidden;
                }

                /* ========== HEADER ========== */
                .front-header {
                    background: linear-gradient(135deg, var(--theme) 0%, var(--theme-dark) 100%);
                    padding: 32px 40px 20px 40px;
                    color: #fff;
                    position: relative;
                    border-bottom: 4px solid var(--theme);
                }
                .front-header::after {
                    content: '';
                    position: absolute;
                    top: 0; left: 0; right: 0; bottom: 0;
                    background: repeating-linear-gradient(45deg, transparent, transparent 20px, rgba(255,255,255,0.02) 20px, rgba(255,255,255,0.02) 40px);
                    pointer-events: none;
                }
                .header-top {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    flex-wrap: wrap;
                    gap: 16px;
                    position: relative;
                    z-index: 1;
                }
                .header-brand {
                    display: flex;
                    align-items: center;
                    gap: 16px;
                }
                .header-brand .logo {
                    width: 56px;
                    height: 56px;
                    border-radius: var(--radius);
                    background: rgba(255,255,255,0.10);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 28px;
                    font-weight: 700;
                    color: #fff;
                    border: 2px solid rgba(255,255,255,0.15);
                    flex-shrink: 0;
                    overflow: hidden;
                }
                .header-brand .logo img { width: 100%; height: 100%; object-fit: contain; }
                .header-brand .brand-text .sub {
                    font-size: 11px;
                    letter-spacing: 3px;
                    text-transform: uppercase;
                    opacity: 0.7;
                    font-weight: 400;
                }
                .header-brand .brand-text h1 {
                    font-size: 28px;
                    font-weight: 700;
                    letter-spacing: -0.5px;
                    margin: 2px 0 0 0;
                    color: #fff;
                    line-height: 1.2;
                }
                .header-meta {
                    text-align: right;
                    font-size: 13px;
                    line-height: 1.7;
                    opacity: 0.95;
                }
                .header-meta span { display: block; }
                .header-meta strong {
                    font-weight: 600;
                    color: #fff;
                    background: rgba(255,255,255,0.15);
                    padding: 0 6px;
                    border-radius: 4px;
                }
                .header-badge {
                    display: inline-block;
                    background: #f7931e;
                    color: var(--theme-dark);
                    padding: 4px 16px;
                    border-radius: 30px;
                    font-size: 11px;
                    font-weight: 700;
                    letter-spacing: 0.5px;
                    text-transform: uppercase;
                    margin-top: 6px;
                }

                /* ========== FRONT BODY ========== */
                .front-body {
                    padding: 60px 40px 40px 40px;
                    text-align: center;
                }
                .front-body .main-title {
                    font-size: 42px;
                    font-weight: 700;
                    color: var(--theme-dark);
                    letter-spacing: -1px;
                    margin-bottom: 8px;
                }
                .front-body .sub-title {
                    font-size: 20px;
                    font-weight: 400;
                    color: var(--gray-500);
                    letter-spacing: 3px;
                    text-transform: uppercase;
                    margin-bottom: 40px;
                    border-bottom: 2px solid var(--gray-100);
                    padding-bottom: 20px;
                }
                .front-body .info-grid {
                    display: grid;
                    grid-template-columns: 1fr 1fr;
                    gap: 20px 40px;
                    max-width: 600px;
                    margin: 0 auto 50px auto;
                    text-align: left;
                }
                .front-body .info-grid .info-item {
                    display: flex;
                    flex-direction: column;
                }
                .front-body .info-grid .info-item .label {
                    font-size: 11px;
                    font-weight: 600;
                    text-transform: uppercase;
                    letter-spacing: 0.8px;
                    color: var(--gray-500);
                }
                .front-body .info-grid .info-item .value {
                    font-size: 18px;
                    font-weight: 600;
                    color: var(--gray-900);
                    margin-top: 2px;
                }

                /* ========== SIGNATURES (front) ========== */
                .front-signatures {
                    margin-top: 50px;
                    padding-top: 30px;
                    border-top: 2px solid var(--gray-100);
                    display: flex;
                    flex-wrap: wrap;
                    justify-content: center;
                    gap: 60px;
                }
                .front-signatures .sig-box {
                    min-width: 200px;
                    text-align: center;
                }
                .front-signatures .sig-box .sig-label {
                    font-size: 11px;
                    text-transform: uppercase;
                    letter-spacing: 1px;
                    color: var(--gray-500);
                    font-weight: 600;
                }
                .front-signatures .sig-box .sig-line {
                    margin-top: 44px;
                    padding-top: 8px;
                    border-top: 2px solid var(--gray-300);
                    font-weight: 600;
                    color: var(--gray-700);
                    font-size: 14px;
                }
                .front-signatures .sig-box .sig-details {
                    font-size: 13px;
                    color: var(--gray-500);
                    margin-top: 4px;
                }

                /* ========== FOOTER ========== */
                .report-footer {
                    background: var(--gray-50);
                    padding: 16px 40px;
                    text-align: center;
                    font-size: 12px;
                    color: var(--gray-500);
                    border-top: 1px solid var(--gray-200);
                    letter-spacing: 0.3px;
                    line-height: 1.8;
                    clear: both;
                }

                /* ============================================================ */
                /* ========== PRINT STYLES ========== */
                /* ============================================================ */
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 14mm 16mm 14mm 16mm;
                    }
                    body {
                        background: #fff !important;
                        padding: 0 !important;
                        margin: 0 !important;
                        font-size: 11pt;
                        color: #000;
                    }
                    .report-container {
                        max-width: 100% !important;
                        border-radius: 0 !important;
                        box-shadow: none !important;
                        margin: 0 !important;
                    }
                    .front-header {
                        padding: 20px 30px 16px 30px !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .front-header::after { display: none !important; }
                    .header-brand .logo,
                    .header-badge {
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .front-body {
                        padding: 40px 30px 30px 30px !important;
                    }
                    .front-body .main-title {
                        font-size: 36pt;
                    }
                    .front-body .sub-title {
                        font-size: 16pt;
                    }
                    .front-body .info-grid .info-item .value {
                        font-size: 16pt;
                    }
                    .front-signatures .sig-box .sig-line {
                        border-top-color: #999 !important;
                        margin-top: 38px !important;
                    }
                    .report-footer {
                        background: var(--gray-50) !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                        padding: 12px 30px !important;
                        font-size: 10pt !important;
                        border-top-color: var(--gray-200) !important;
                    }
                    .print-btn { display: none !important; }
                }

                /* ========== RESPONSIVE ========== */
                @media screen and (max-width: 640px) {
                    body { padding: 12px 8px; }
                    .front-body { padding: 30px 16px; }
                    .front-body .main-title { font-size: 28px; }
                    .front-body .sub-title { font-size: 16px; }
                    .front-body .info-grid {
                        grid-template-columns: 1fr;
                        gap: 14px;
                        max-width: 100%;
                    }
                    .front-signatures {
                        flex-direction: column;
                        gap: 30px;
                    }
                    .header-top { flex-direction: column; align-items: stretch; }
                    .header-meta { text-align: left; width: 100%; }
                }
            </style>
        </head>
        <body>
            <button class="print-btn" onclick="window.print()">🖨️ Print Front Page</button>

            <div class="report-container">
                <!-- HEADER -->
                <div class="front-header">
                    <div class="header-top">
                        <div class="header-brand">
                            <div class="logo">
                                <?php if ($logoUrl): ?>
                                    <img src="<?= $logoUrl ?>" alt="Organization Logo">
                                <?php else: ?>
                                    📋
                                <?php endif; ?>
                            </div>
                            <div class="brand-text">
                                <div class="sub">On-the-Job Training</div>
                                <h1>NARRATIVE REPORT</h1>
                            </div>
                        </div>
                        <div class="header-meta">
                            <span><strong>Student:</strong> <?= $studentName ?></span>
                            <span><strong>ID:</strong> <?= $studentId ?></span>
                            <span><strong>Program:</strong> <?= $studentProgram ?></span>
                            <span class="header-badge">Cover Page</span>
                        </div>
                    </div>
                </div>

                <!-- BODY -->
                <div class="front-body">
                    <div class="main-title"><?= $reportTitle ?></div>
                    <div class="sub-title">On-the-Job Training</div>

                    <div class="info-grid">
                        <div class="info-item">
                            <span class="label">Student Name</span>
                            <span class="value"><?= $studentName ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Student ID</span>
                            <span class="value"><?= $studentId ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Program</span>
                            <span class="value"><?= $studentProgram ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Organization</span>
                            <span class="value"><?= $orgName ?></span>
                        </div>
                        <div class="info-item" style="grid-column: span 2;">
                            <span class="label">Training Period</span>
                            <span class="value"><?= $trainingPeriod ?></span>
                        </div>
                        <div class="info-item" style="grid-column: span 2;">
                            <span class="label">Supervisor</span>
                            <span class="value"><?= $supervisor ?> (<?= $supervisorTitle ?>)</span>
                        </div>
                    </div>

                    <!-- Signatures -->
                    <div class="front-signatures">
                        <div class="sig-box">
                            <div class="sig-label">Student</div>
                            <div class="sig-line"><?= $studentName ?></div>
                            <div class="sig-details">Date: _________________</div>
                        </div>
                        <div class="sig-box">
                            <div class="sig-label">Supervisor</div>
                            <div class="sig-line"><?= $supervisor ?></div>
                            <div class="sig-details"><?= $supervisorTitle ?> • <?= $orgName ?></div>
                            <div class="sig-details">Date: _________________</div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="report-footer">
                    <span class="org-name"><?= $orgName ?></span> • <?= $orgAddress ?> • Generated on <?= $generatedOn ?>
                </div>
            </div>
        </body>
        </html>
        <?php
        $output = ob_get_clean();
        return $output ?: '';
    }
}
?>