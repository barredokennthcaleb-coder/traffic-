<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>New Inspection - Traffic System<?= $this->endSection() ?>

<?= $this->section('content') ?>
<style>
    .paper-wrapper {
        background-color: var(--surface-bg, #f1f5f9);
        padding: 20px 0 60px 0;
    }

    .paper-card {
        background-color: #ffffff;
        color: #000000;
        max-width: 210mm;
        min-height: 297mm;
        padding: 20mm;
        margin: 0 auto;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        position: relative;
        font-family: 'Times New Roman', Times, serif;
    }

    .header-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 25px;
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
        line-height: 1.25;
    }

    .header-text h4 {
        margin: 2px 0;
        font-weight: bold;
        font-family: 'Times New Roman', Times, serif;
    }

    .report-title {
        text-align: center;
        text-decoration: underline;
        font-weight: bold;
        font-size: 1.25rem;
        margin-bottom: 35px;
        font-family: 'Times New Roman', Times, serif;
        letter-spacing: 0.5px;
    }

    .form-info {
        margin-bottom: 25px;
        font-family: 'Times New Roman', Times, serif;
    }

    .info-label {
        font-weight: bold;
        white-space: nowrap;
        margin-right: 10px;
        font-size: 1.05rem;
        font-family: 'Times New Roman', Times, serif;
    }

    .paper-input {
        flex-grow: 1;
        min-width: 0;
        border: none;
        border-bottom: 1.5px solid #000000;
        border-radius: 0;
        padding: 2px 6px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 1.05rem;
        background: transparent;
        color: #000000;
        outline: none;
        transition: border-color 0.2s, background-color 0.2s;
    }

    .paper-input:focus {
        border-bottom: 2px solid #2563eb;
        background-color: rgba(37, 99, 235, 0.04);
    }

    .checklist {
        display: flex;
        flex-wrap: wrap;
        margin-bottom: 35px;
    }

    .check-item {
        width: 100%;
        display: flex;
        align-items: flex-start;
        margin-bottom: 11px;
        font-size: 1.05rem;
        font-family: 'Times New Roman', Times, serif;
        cursor: pointer;
        user-select: none;
        padding: 2px 4px;
        border-radius: 4px;
        transition: background-color 0.15s;
    }

    .check-item:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }

    .check-item.half { width: 50%; }
    .check-item.third { width: 33.333%; }

    /* Custom Document Checkbox */
    .doc-checkbox {
        position: relative;
        width: 18px;
        height: 18px;
        margin-right: 12px;
        margin-top: 3px;
        flex-shrink: 0;
        cursor: pointer;
    }

    .doc-checkbox input[type="checkbox"] {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        z-index: 2;
        margin: 0;
    }

    .doc-checkbox .box {
        position: absolute;
        top: 0;
        left: 0;
        width: 18px;
        height: 18px;
        border: 1.5px solid #000;
        background-color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.15s;
        pointer-events: none;
    }

    .doc-checkbox input[type="checkbox"]:checked ~ .box::after {
        content: '✓';
        font-weight: bold;
        font-size: 16px;
        line-height: 1;
        color: #000;
    }

    .check-item:hover .doc-checkbox .box {
        border-color: #2563eb;
    }

    .doc-checkbox input[type="checkbox"]:focus ~ .box {
        outline: 2px solid #2563eb;
        outline-offset: 1px;
    }

    .signature-section {
        margin-top: 60px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 1.05rem;
    }

    .sig-field {
        display: flex;
        align-items: center;
        margin-bottom: 12px;
    }

    .sig-line-input {
        border: none;
        border-bottom: 1.5px solid #000;
        border-radius: 0;
        width: 250px;
        padding: 2px 8px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 1.05rem;
        background: transparent;
        color: #000;
        outline: none;
    }

    .sig-line-input:focus {
        border-bottom: 2px solid #2563eb;
        background-color: rgba(37, 99, 235, 0.04);
    }

    .officer-block {
        text-align: center;
        margin-top: 80px;
        float: right;
        width: 300px;
        font-family: 'Times New Roman', Times, serif;
    }

    .officer-name {
        font-weight: bold;
        text-decoration: underline;
        font-size: 1.1rem;
        text-transform: uppercase;
        margin-bottom: 2px;
        font-family: 'Times New Roman', Times, serif;
    }

    .officer-title {
        font-style: italic;
        margin-bottom: 0;
        font-family: 'Times New Roman', Times, serif;
    }

    .toolbar-container {
        max-width: 210mm;
        margin: 0 auto 15px auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    @media (max-width: 768px) {
        .paper-card {
            width: 100%;
            padding: 15px 12px;
            min-height: auto;
        }

        .header-logo-left, .header-logo-right {
            position: static;
            height: 50px;
            margin: 0 8px;
        }

        .header-container {
            flex-direction: row;
            justify-content: space-around;
        }

        .header-text h4 {
            font-size: 1rem;
        }

        .check-item.half, .check-item.third {
            width: 100%;
        }

        .officer-block {
            float: none;
            margin-top: 35px;
            width: 100%;
            text-align: center;
        }

        .sig-line-input {
            width: 100%;
        }
    }
</style>

<div class="paper-wrapper">
    <div class="toolbar-container px-2">
        <a href="<?= base_url('inspections') ?>" class="btn btn-outline-secondary btn-sm shadow-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Inspections
        </a>
        <div class="d-flex gap-2">
            <a href="<?= base_url('inspections') ?>" class="btn btn-light btn-sm px-3">Cancel</a>
            <button type="submit" form="inspectionForm" class="btn btn-primary btn-sm px-4 shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Save Inspection Report
            </button>
        </div>
    </div>

    <form action="<?= base_url('inspections/store') ?>" method="POST" id="inspectionForm">
        <?= csrf_field() ?>

        <div class="paper-card">
            <!-- Header matching print format -->
            <div class="header-container">
                <img src="<?= base_url('img/pic-1.png') ?>" alt="Logo" class="header-logo-left">
                <div class="header-text">
                    <p class="mb-0">Republic of the Philippines</p>
                    <p class="mb-0">Province of Negros Occidental</p>
                    <h4>CITY TRAFFIC MANAGEMENT OFFICE</h4>
                    <p class="mb-0">City of Kabankalan</p>
                </div>
                <img src="<?= base_url('img/pic-1.png') ?>" alt="Logo" class="header-logo-right">
            </div>

            <!-- Report Title -->
            <div class="report-title">INSPECTION REPORT</div>

            <!-- Form Info matching print format -->
            <div class="form-info">
                <div class="d-flex align-items-baseline mb-2">
                    <span class="info-label">MTOP #:</span>
                    <input type="text" name="mtop_no" class="paper-input" required value="<?= old('mtop_no') ?>" placeholder="Enter MTOP Number" autocomplete="off">
                </div>
                <div class="d-flex align-items-baseline mb-2">
                    <span class="info-label">Name of Operator:</span>
                    <input type="text" name="operator_name" class="paper-input" required value="<?= old('operator_name') ?>" placeholder="Enter Operator Name" autocomplete="off">
                </div>
                <div class="row mb-2">
                    <div class="col-6 d-flex align-items-baseline">
                        <span class="info-label">Cellphone# Operator:</span>
                        <input type="text" name="operator_cellphone" class="paper-input" value="<?= old('operator_cellphone') ?>" placeholder="09123456789" autocomplete="off">
                    </div>
                    <div class="col-6 d-flex align-items-baseline">
                        <span class="info-label">Cellphone # Driver:</span>
                        <input type="text" name="driver_cellphone" class="paper-input" value="<?= old('driver_cellphone') ?>" placeholder="09123456789" autocomplete="off">
                    </div>
                </div>
                <div class="d-flex align-items-baseline mb-2">
                    <span class="info-label">Make /Type of MV:</span>
                    <input type="text" name="mv_make_type" class="paper-input" value="<?= old('mv_make_type') ?>" placeholder="e.g. Honda TMX / Tricycle" autocomplete="off">
                </div>
            </div>

            <!-- Checklist matching print format layout & order -->
            <div class="checklist">
                <!-- Row 1: Full width -->
                <label class="check-item" for="cert_attendance">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="cert_attendance" id="cert_attendance" <?= old('cert_attendance') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Cert. of Attendance of the Driver (CTRAMO Seminar).</span>
                </label>

                <!-- Row 2: Half width -->
                <label class="check-item half" for="outside_route">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="outside_route" id="outside_route" <?= old('outside_route') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Outside route</span>
                </label>
                <label class="check-item half" for="mtop_8x16_route">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="mtop_8x16_route" id="mtop_8x16_route" <?= old('mtop_8x16_route') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>MTOP # 8 x 16 Su-ay/Ilog route (Fixed)</span>
                </label>

                <!-- Row 3: Half width -->
                <label class="check-item half" for="city_proper">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="city_proper" id="city_proper" <?= old('city_proper') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>City Proper</span>
                </label>
                <label class="check-item half" for="mtop_8x8_route">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="mtop_8x8_route" id="mtop_8x8_route" <?= old('mtop_8x8_route') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>MTOP # 8x8 City Proper/Outside Route (Fixed)</span>
                </label>

                <!-- Row 4: Full width -->
                <label class="check-item" for="trash_can">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="trash_can" id="trash_can" <?= old('trash_can') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Trash Can</span>
                </label>

                <!-- Row 5: Full width -->
                <label class="check-item" for="mtop_dashboard">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="mtop_dashboard" id="mtop_dashboard" <?= old('mtop_dashboard') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>MTOP # dash board/Name of operator's</span>
                </label>

                <!-- Row 6: Third width -->
                <label class="check-item third" for="signal_light_left">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="signal_light_left" id="signal_light_left" <?= old('signal_light_left') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Signal light/left</span>
                </label>
                <label class="check-item third" for="signal_light_right">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="signal_light_right" id="signal_light_right" <?= old('signal_light_right') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Signal light/right</span>
                </label>
                <label class="check-item third" for="head_light">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="head_light" id="head_light" <?= old('head_light') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Head light</span>
                </label>

                <!-- Row 7: Third width -->
                <label class="check-item third" for="stop_light">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="stop_light" id="stop_light" <?= old('stop_light') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>stop light</span>
                </label>
                <label class="check-item third" for="side_mirror">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="side_mirror" id="side_mirror" <?= old('side_mirror') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Side Mirror left &amp; right</span>
                </label>
                <label class="check-item third" for="horn">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="horn" id="horn" <?= old('horn') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Horn</span>
                </label>

                <!-- Row 8: Third width -->
                <label class="check-item third" for="standard_muffler">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="standard_muffler" id="standard_muffler" <?= old('standard_muffler') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Standard Muffler</span>
                </label>
                <label class="check-item third" for="color_coding">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="color_coding" id="color_coding" <?= old('color_coding') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Color coding</span>
                </label>
                <label class="check-item third" for="tarifa">
                    <div class="doc-checkbox">
                        <input type="checkbox" name="tarifa" id="tarifa" <?= old('tarifa') ? 'checked' : '' ?>>
                        <div class="box"></div>
                    </div>
                    <span>Tarifa</span>
                </label>
            </div>

            <!-- Signatures Section matching print format -->
            <div class="signature-section">
                <?php 
                    $inspectorVal = old('inspected_by') ?? session()->get('name') ?? session()->get('username') ?? '';
                ?>
                <div class="sig-field">
                    <span class="info-label">Inspected by:</span>
                    <input type="text" name="inspected_by" class="sig-line-input" required value="<?= esc($inspectorVal) ?>" placeholder="Officer Name" autocomplete="off">
                </div>
                <div class="sig-field">
                    <span class="info-label">Date:</span>
                    <input type="date" name="inspection_date" class="sig-line-input" required value="<?= old('inspection_date') ?? date('Y-m-d') ?>">
                </div>
            </div>

            <!-- Officer Block matching print format -->
            <div class="officer-block">
                <p class="officer-name">CHRISTINO P. BANDICO</p>
                <p class="officer-title">Traffic Operation Officer II</p>
            </div>

            <div class="clearfix"></div>
        </div>

        <div class="toolbar-container px-2 mt-3 justify-content-end">
            <a href="<?= base_url('inspections') ?>" class="btn btn-light btn-sm px-3 me-2">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Save Inspection Report
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
