<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inspection Report - <?= esc($inspection['mtop_no']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            @page { size: portrait; margin: 1cm; }
        }
        body {
            background-color: #f0f0f0;
            font-family: 'Times New Roman', Times, serif;
        }
        .paper {
            background-color: white;
            width: 210mm;
            min-height: 297mm;
            padding: 20mm;
            margin: 20mm auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        @media print {
            body { background-color: white; }
            .paper { margin: 0; box-shadow: none; width: 100%; }
        }
        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 30px;
            position: relative;
        }
        .header-logo-left {
            position: absolute;
            left: 0;
            height: 80px;
            width: auto;
        }
        .header-logo-right {
            position: absolute;
            right: 0;
            height: 80px;
            width: auto;
        }
        .header-text {
            text-align: center;
            line-height: 1.2;
        }
        .header-text h5 { margin-bottom: 2px; font-weight: bold; }
        .header-text h4 { margin-bottom: 2px; font-weight: bold; }
        .report-title {
            text-align: center;
            text-decoration: underline;
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 40px;
        }
        .form-info {
            margin-bottom: 30px;
        }
        .info-row {
            display: flex;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
        }
        .info-label {
            font-weight: bold;
            white-space: nowrap;
            margin-right: 10px;
        }
        .info-value {
            flex-grow: 1;
        }
        .checklist {
            display: flex;
            flex-wrap: wrap;
            margin-bottom: 40px;
        }
        .check-item {
            width: 100%;
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            font-size: 1.05rem;
        }
        .check-item.half { width: 50%; }
        .check-item.third { width: 33.33%; }
        .box {
            width: 18px;
            height: 18px;
            border: 1.5px solid #000;
            margin-right: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 3px;
        }
        .box.checked::after {
            content: '✓';
            font-weight: bold;
            font-size: 16px;
        }
        .signature-section {
            margin-top: 60px;
        }
        .sig-line {
            border-bottom: 1px solid #000;
            width: 250px;
            display: inline-block;
        }
        .officer-block {
            text-align: center;
            margin-top: 80px;
            float: right;
            width: 300px;
        }
        .officer-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 1.1rem;
            text-transform: uppercase;
        }
        .officer-title {
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="no-print p-3 bg-dark text-white text-center">
        <button onclick="window.print()" class="btn btn-primary me-2"><i class="bi bi-printer"></i> Print Report</button>
        <button onclick="window.close()" class="btn btn-outline-light">Close</button>
    </div>

    <div class="paper">
        <div class="header-container">
            <img src="/img/pic 1.png" alt="Logo" class="header-logo-left">
            <div class="header-text">
                <p class="mb-0">Republic of the Philippines</p>
                <p class="mb-0">Province of Negros Occidental</p>
                <h4>CITY TRAFFIC MANAGEMENT OFFICE</h4>
                <p class="mb-0">City of Kabankalan</p>
            </div>
            <img src="/img/pic 1.png" alt="Logo" class="header-logo-right">
        </div>

        <div class="report-title">INSPECTION REPORT</div>

        <div class="form-info">
            <div class="d-flex mb-2">
                <span class="info-label">MTOP #:</span>
                <span class="info-value border-bottom border-dark"><?= esc($inspection['mtop_no']) ?></span>
            </div>
            <div class="d-flex mb-2">
                <span class="info-label">Name of Operator:</span>
                <span class="info-value border-bottom border-dark"><?= esc($inspection['operator_name']) ?></span>
            </div>
            <div class="row mb-2">
                <div class="col-6 d-flex">
                    <span class="info-label">Cellphone# Operator:</span>
                    <span class="info-value border-bottom border-dark"><?= esc($inspection['operator_cellphone']) ?></span>
                </div>
                <div class="col-6 d-flex">
                    <span class="info-label">Cellphone # Driver:</span>
                    <span class="info-value border-bottom border-dark"><?= esc($inspection['driver_cellphone']) ?></span>
                </div>
            </div>
            <div class="d-flex mb-2">
                <span class="info-label">Make /Type of MV:</span>
                <span class="info-value border-bottom border-dark"><?= esc($inspection['mv_make_type']) ?></span>
            </div>
        </div>

        <div class="checklist">
            <div class="check-item">
                <div class="box <?= $inspection['cert_attendance'] ? 'checked' : '' ?>"></div>
                <span>Cert. of Attendance of the Driver (CTRAMO Seminar).</span>
            </div>

            <div class="check-item half">
                <div class="box <?= $inspection['outside_route'] ? 'checked' : '' ?>"></div>
                <span>Outside route</span>
            </div>
            <div class="check-item half">
                <div class="box <?= $inspection['mtop_8x16_route'] ? 'checked' : '' ?>"></div>
                <span>MTOP # 8 x 16 Su-ay/Ilog route (Fixed)</span>
            </div>

            <div class="check-item half">
                <div class="box <?= $inspection['city_proper'] ? 'checked' : '' ?>"></div>
                <span>City Proper</span>
            </div>
            <div class="check-item half">
                <div class="box <?= $inspection['mtop_8x8_route'] ? 'checked' : '' ?>"></div>
                <span>MTOP # 8x8 City Proper/Outside Route (Fixed)</span>
            </div>

            <div class="check-item">
                <div class="box <?= $inspection['trash_can'] ? 'checked' : '' ?>"></div>
                <span>Trash Can</span>
            </div>

            <div class="check-item">
                <div class="box <?= $inspection['mtop_dashboard'] ? 'checked' : '' ?>"></div>
                <span>MTOP # dash board/Name of operator's</span>
            </div>

            <div class="check-item third">
                <div class="box <?= $inspection['signal_light_left'] ? 'checked' : '' ?>"></div>
                <span>Signal light/left</span>
            </div>
            <div class="check-item third">
                <div class="box <?= $inspection['signal_light_right'] ? 'checked' : '' ?>"></div>
                <span>Signal light/right</span>
            </div>
            <div class="check-item third">
                <div class="box <?= $inspection['head_light'] ? 'checked' : '' ?>"></div>
                <span>Head light</span>
            </div>

            <div class="check-item third">
                <div class="box <?= $inspection['stop_light'] ? 'checked' : '' ?>"></div>
                <span>stop light</span>
            </div>
            <div class="check-item third">
                <div class="box <?= $inspection['side_mirror'] ? 'checked' : '' ?>"></div>
                <span>Side Mirror left & right</span>
            </div>
            <div class="check-item third">
                <div class="box <?= $inspection['horn'] ? 'checked' : '' ?>"></div>
                <span>Horn</span>
            </div>

            <div class="check-item third">
                <div class="box <?= $inspection['standard_muffler'] ? 'checked' : '' ?>"></div>
                <span>Standard Muffler</span>
            </div>
            <div class="check-item third">
                <div class="box <?= $inspection['color_coding'] ? 'checked' : '' ?>"></div>
                <span>Color coding</span>
            </div>
            <div class="check-item third">
                <div class="box <?= $inspection['tarifa'] ? 'checked' : '' ?>"></div>
                <span>Tarifa</span>
            </div>
        </div>

        <div class="signature-section">
            <p>Inspected by: <span class="sig-line ps-2"><?= esc($inspection['inspected_by']) ?></span></p>
            <p>Date: <span class="sig-line ps-2"><?= date('F d, Y', strtotime($inspection['inspection_date'])) ?></span></p>
        </div>

        <div class="officer-block">
            <p class="officer-name">CHRISTINO P. BANDICO</p>
            <p class="officer-title">Traffic Operation Officer II</p>
        </div>
    </div>
</body>
</html>
