<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Inspection Report - <?= esc($inspection['mtop_no']) ?><?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    @media print {
        .no-print, .sidebar, .main-content > .desktop-header { display: none !important; }
        body { background: #fff !important; padding: 0 !important; margin: 0 !important; }
        .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        .paper { margin: 0 auto !important; box-shadow: none !important; width: 100% !important; padding: 12mm 15mm !important; }
        @page { size: portrait; margin: 0.8cm; }
    }

    body {
        font-family: 'Times New Roman', Times, serif;
    }

    .paper {
        background-color: white;
        width: 210mm;
        min-height: 297mm;
        padding: 18mm 20mm;
        margin: 20px auto;
        box-shadow: 0 0 18px rgba(0,0,0,0.12);
        position: relative;
        font-family: 'Times New Roman', Times, serif;
        color: #000;
        box-sizing: border-box;
    }

    .header-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 22px;
        position: relative;
        min-height: 85px;
    }

    .header-logo-left {
        position: absolute;
        left: 10px;
        top: 0;
        height: 75px;
        width: auto;
    }

    .header-logo-right {
        position: absolute;
        right: 10px;
        top: 0;
        height: 85px;
        width: auto;
        object-fit: contain;
    }

    .header-text {
        text-align: center;
        line-height: 1.25;
        font-family: 'Times New Roman', Times, serif;
    }

    .header-text p {
        margin-bottom: 1px;
        font-size: 0.95rem;
    }

    .header-text h5 {
        margin: 2px 0;
        font-weight: bold;
        font-size: 1.08rem;
        letter-spacing: 0.3px;
        font-family: 'Times New Roman', Times, serif;
    }

    .report-title {
        text-align: center;
        text-decoration: underline;
        font-weight: bold;
        font-size: 1.2rem;
        margin-top: 10px;
        margin-bottom: 25px;
        letter-spacing: 0.8px;
        font-family: 'Times New Roman', Times, serif;
    }

    .form-info {
        margin-bottom: 20px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 0.98rem;
    }

    .info-row {
        display: flex;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .info-label {
        font-weight: bold;
        white-space: nowrap;
        font-family: 'Times New Roman', Times, serif;
    }

    .info-underline {
        flex-grow: 1;
        border-bottom: 1px solid #000000;
        min-height: 18px;
        padding-left: 6px;
        padding-right: 6px;
        font-family: 'Times New Roman', Times, serif;
    }

    .checklist-section {
        margin-bottom: 30px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 0.96rem;
    }

    .check-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .check-cell {
        display: flex;
        align-items: center;
        padding-right: 10px;
    }

    .check-cell.full { width: 100%; }
    .check-cell.half { width: 50%; }
    .check-cell.third { width: 33.333%; }

    .box {
        width: 17px;
        height: 17px;
        border: 1.5px solid #000;
        margin-right: 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .box.checked::after {
        content: '✓';
        font-weight: bold;
        font-size: 15px;
        line-height: 1;
        color: #000;
    }

    .signature-section {
        margin-top: 35px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 0.98rem;
    }

    .sig-row {
        display: flex;
        align-items: baseline;
        margin-bottom: 8px;
    }

    .sig-line {
        border-bottom: 1px solid #000;
        width: 230px;
        display: inline-block;
        padding-left: 8px;
        min-height: 18px;
    }

    .officer-block {
        text-align: center;
        margin-top: 45px;
        float: right;
        min-width: 260px;
        font-family: 'Times New Roman', Times, serif;
    }

    .officer-name {
        font-weight: bold;
        font-size: 1.05rem;
        text-transform: uppercase;
        margin-bottom: 2px;
        letter-spacing: 0.5px;
        font-family: 'Times New Roman', Times, serif;
    }

    .officer-title {
        font-style: italic;
        font-size: 0.92rem;
        margin-bottom: 0;
        font-family: 'Times New Roman', Times, serif;
    }
</style>

<div class="container-fluid py-3 no-print">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <a href="<?= base_url('inspections') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Inspections
        </a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('inspections/edit/' . $inspection['id']) ?>" class="btn btn-outline-primary btn-sm shadow-sm">
                <i class="bi bi-pencil me-1"></i> Edit Report
            </a>
            <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm px-3">
                <i class="bi bi-printer me-1"></i> Print Report
            </button>
        </div>
    </div>
</div>

<div class="paper">
    <!-- Header -->
    <div class="header-container">
        <img src="<?= base_url('img/pic-1.png') ?>" alt="City of Kabankalan Seal" class="header-logo-left">
        <div class="header-text">
            <p class="mb-0">Republic of the Philippines</p>
            <p class="mb-0">Province of Negros Occidental</p>
            <h5 class="mb-0">CITY TRAFFIC MANAGEMENT OFFICE</h5>
            <p class="mb-0">City of Kabankalan</p>
        </div>
        <img src="<?= base_url('img/ctramo-logo.png') ?>" alt="CTRAMO Logo" class="header-logo-right">
    </div>

    <!-- Title -->
    <div class="report-title">INSPECTION REPORT</div>

    <!-- Form Info Section (exact paper layout) -->
    <div class="form-info">
        <!-- Line 1: MTOP # and Route -->
        <div class="info-row">
            <span class="info-label me-2">MTOP #:</span>
            <span class="info-underline me-4" style="max-width: 220px;"><?= esc($inspection['mtop_no']) ?></span>
            <span class="info-label me-2">Route:</span>
            <span class="info-underline"><?= esc($inspection['route'] ?? '') ?></span>
        </div>

        <!-- Line 2: Name of Operator and Cellphone # DRIVER -->
        <div class="info-row">
            <span class="info-label me-2">Name of Operator:</span>
            <span class="info-underline me-4"><?= esc($inspection['operator_name']) ?></span>
            <span class="info-label me-2">Cellphone # DRIVER:</span>
            <span class="info-underline" style="max-width: 220px;"><?= esc($inspection['driver_cellphone']) ?></span>
        </div>

        <!-- Line 3: Cellphone# Operator and Prof. License# -->
        <div class="info-row">
            <span class="info-label me-2">Cellphone# Operator:</span>
            <span class="info-underline me-4" style="max-width: 260px;"><?= esc($inspection['operator_cellphone']) ?></span>
            <span class="info-label me-2">Prof. License#:</span>
            <span class="info-underline"><?= esc($inspection['driver_license_no'] ?? '') ?></span>
        </div>

        <!-- Line 4: Name of DRIVER -->
        <div class="info-row">
            <span class="info-label me-2">Name of DRIVER:</span>
            <span class="info-underline"><?= esc($inspection['driver_name'] ?? '') ?></span>
        </div>

        <!-- Line 5: Make/Type and Color -->
        <div class="info-row">
            <span class="info-label me-2">Make/Type:</span>
            <span class="info-underline me-4"><?= esc($inspection['mv_make_type']) ?></span>
            <span class="info-label me-2">Color:</span>
            <span class="info-underline" style="max-width: 250px;"><?= esc($inspection['body_color'] ?? '') ?></span>
        </div>
    </div>

    <!-- Checklist Section (exact paper layout & wording) -->
    <div class="checklist-section">
        <!-- 1: Full width -->
        <div class="check-row">
            <div class="check-cell full">
                <div class="box <?= !empty($inspection['cert_attendance']) ? 'checked' : '' ?>"></div>
                <span>Cert. of Attendance of the driver (CTRAMO Seminar)</span>
            </div>
        </div>

        <!-- 2: Two columns (Outside route & MTOP # 8 x 16 Suay/Ilog route) -->
        <div class="check-row">
            <div class="check-cell half">
                <div class="box <?= !empty($inspection['outside_route']) ? 'checked' : '' ?>"></div>
                <span>Outside route</span>
            </div>
            <div class="check-cell half">
                <div class="box <?= !empty($inspection['mtop_8x16_route']) ? 'checked' : '' ?>"></div>
                <span>MTOP # 8 x 16 Suay/Ilog route (Fixed)</span>
            </div>
        </div>

        <!-- 3: Left column (Outside route 8X8 (Fixed)) -->
        <div class="check-row">
            <div class="check-cell half">
                <div class="box <?= !empty($inspection['outside_route_8x8']) ? 'checked' : '' ?>"></div>
                <span>Outside route 8X8 (Fixed)</span>
            </div>
            <div class="check-cell half"></div>
        </div>

        <!-- 4: Two columns (City Proper & MTOP # 8 x 8 City Proper) -->
        <div class="check-row">
            <div class="check-cell half">
                <div class="box <?= !empty($inspection['city_proper']) ? 'checked' : '' ?>"></div>
                <span>City Proper</span>
            </div>
            <div class="check-cell half">
                <div class="box <?= !empty($inspection['mtop_8x8_route']) ? 'checked' : '' ?>"></div>
                <span>MTOP # 8 x 8 City Proper (Fixed)</span>
            </div>
        </div>

        <!-- 5: Full width (Trash Can) -->
        <div class="check-row">
            <div class="check-cell full">
                <div class="box <?= !empty($inspection['trash_can']) ? 'checked' : '' ?>"></div>
                <span>Trash Can</span>
            </div>
        </div>

        <!-- 6: Full width (MTOP # dash board/Name of operator's) -->
        <div class="check-row">
            <div class="check-cell full">
                <div class="box <?= !empty($inspection['mtop_dashboard']) ? 'checked' : '' ?>"></div>
                <span>MTOP # dash board/Name of operator's</span>
            </div>
        </div>

        <!-- 7: Three columns (Signal lights & Head light) -->
        <div class="check-row">
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['signal_light_left']) ? 'checked' : '' ?>"></div>
                <span>Signal light/left</span>
            </div>
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['signal_light_right']) ? 'checked' : '' ?>"></div>
                <span>Signal light/right</span>
            </div>
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['head_light']) ? 'checked' : '' ?>"></div>
                <span>Head light</span>
            </div>
        </div>

        <!-- 8: Three columns (Stop light, Side Mirror, Horn) -->
        <div class="check-row">
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['stop_light']) ? 'checked' : '' ?>"></div>
                <span>Stop light</span>
            </div>
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['side_mirror']) ? 'checked' : '' ?>"></div>
                <span>Side Mirror left &amp; right</span>
            </div>
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['horn']) ? 'checked' : '' ?>"></div>
                <span>Horn</span>
            </div>
        </div>

        <!-- 9: Three columns (Standard Muffler, Color coding, Tarifa) -->
        <div class="check-row">
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['standard_muffler']) ? 'checked' : '' ?>"></div>
                <span>Standard Muffler</span>
            </div>
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['color_coding']) ? 'checked' : '' ?>"></div>
                <span>Color coding</span>
            </div>
            <div class="check-cell third">
                <div class="box <?= !empty($inspection['tarifa']) ? 'checked' : '' ?>"></div>
                <span>Tarifa</span>
            </div>
        </div>
    </div>

    <!-- Signatures Section -->
    <div class="signature-section">
        <div class="sig-row">
            <span class="info-label me-2">Inspected by:</span>
            <span class="sig-line"><?= esc($inspection['inspected_by']) ?></span>
        </div>
        <div class="sig-row">
            <span class="info-label me-2">Date:</span>
            <span class="sig-line ps-2"><?= !empty($inspection['inspection_date']) ? date('F d, Y', strtotime($inspection['inspection_date'])) : '' ?></span>
        </div>
    </div>

    <!-- Officer Signatory (matching official paper) -->
    <div class="officer-block">
        <p class="officer-name">LLACH T. ANZURES</p>
        <p class="officer-title">Administrative Aide VI</p>
    </div>

    <div style="clear: both;"></div>
</div>
<?= $this->endSection() ?>
