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
        padding: 18mm 20mm;
        margin: 0 auto;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.12);
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        position: relative;
        font-family: 'Times New Roman', Times, serif;
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
        margin-bottom: 10px;
    }

    .info-label {
        font-weight: bold;
        white-space: nowrap;
        font-family: 'Times New Roman', Times, serif;
    }

    .paper-input {
        flex-grow: 1;
        min-width: 0;
        border: none;
        border-bottom: 1px solid #000000;
        border-radius: 0;
        padding: 2px 6px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 0.98rem;
        background: transparent;
        color: #000000;
        outline: none;
        transition: border-color 0.2s, background-color 0.2s;
    }

    .paper-input:focus {
        border-bottom: 2px solid #2563eb;
        background-color: rgba(37, 99, 235, 0.04);
    }

    .checklist-section {
        margin-bottom: 30px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 0.96rem;
    }

    .check-row {
        display: flex;
        align-items: flex-start;
        margin-bottom: 9px;
    }

    .check-cell {
        display: flex;
        align-items: center;
        padding-right: 10px;
        cursor: pointer;
        user-select: none;
        border-radius: 4px;
        padding: 3px 6px;
        transition: background-color 0.15s;
    }

    .check-cell:hover {
        background-color: rgba(37, 99, 235, 0.05);
    }

    .check-cell.full { width: 100%; }
    .check-cell.half { width: 50%; }
    .check-cell.third { width: 33.333%; }

    /* Custom Document Checkbox */
    .doc-checkbox {
        position: relative;
        width: 17px;
        height: 17px;
        margin-right: 10px;
        flex-shrink: 0;
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
        width: 17px;
        height: 17px;
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
        font-size: 15px;
        line-height: 1;
        color: #000;
    }

    .check-cell:hover .doc-checkbox .box {
        border-color: #2563eb;
    }

    .signature-section {
        margin-top: 35px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 0.98rem;
    }

    .sig-row {
        display: flex;
        align-items: baseline;
        margin-bottom: 10px;
    }

    .sig-line-input {
        border: none;
        border-bottom: 1px solid #000;
        border-radius: 0;
        width: 250px;
        padding: 2px 8px;
        font-family: 'Times New Roman', Times, serif;
        font-size: 0.98rem;
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

        .info-row {
            flex-direction: column;
            align-items: stretch;
        }

        .info-underline, .paper-input {
            width: 100% !important;
            max-width: 100% !important;
            margin-bottom: 6px;
        }

        .check-row {
            flex-direction: column;
        }

        .check-cell.half, .check-cell.third {
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
            <a href="<?= base_url('inspections') ?>" class="btn btn-light btn-sm px-3 border shadow-sm">Cancel</a>
            <button type="submit" form="inspectionForm" class="btn btn-primary btn-sm px-4 shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Save Inspection Report
            </button>
        </div>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger mx-auto mb-3 shadow-sm" style="max-width: 210mm;">
            <ul class="mb-0 ps-3">
                <?php foreach (session()->getFlashdata('errors') as $err): ?>
                    <li><?= esc($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= base_url('inspections/store') ?>" method="POST" id="inspectionForm">
        <?= csrf_field() ?>

        <div class="paper-card">
            <!-- Header matching official format -->
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

            <!-- Form Info Section matching paper format -->
            <div class="form-info">
                <!-- Line 1: MTOP # and Route -->
                <div class="info-row">
                    <span class="info-label me-2">MTOP #:</span>
                    <input type="text" name="mtop_no" class="paper-input me-4" style="max-width: 220px;" required value="<?= old('mtop_no') ?>" placeholder="e.g. 1234" autocomplete="off">
                    <span class="info-label me-2">Route:</span>
                    <input type="text" name="route" class="paper-input" value="<?= old('route') ?>" placeholder="e.g. Suay/Ilog or City Proper" autocomplete="off">
                </div>

                <!-- Line 2: Name of Operator and Cellphone # DRIVER -->
                <div class="info-row">
                    <span class="info-label me-2">Name of Operator:</span>
                    <input type="text" name="operator_name" class="paper-input me-4" required value="<?= old('operator_name') ?>" placeholder="Enter Operator Name" autocomplete="off">
                    <span class="info-label me-2">Cellphone # DRIVER:</span>
                    <input type="text" name="driver_cellphone" class="paper-input" style="max-width: 220px;" value="<?= old('driver_cellphone') ?>" placeholder="09123456789" autocomplete="off">
                </div>

                <!-- Line 3: Cellphone# Operator and Prof. License# -->
                <div class="info-row">
                    <span class="info-label me-2">Cellphone# Operator:</span>
                    <input type="text" name="operator_cellphone" class="paper-input me-4" style="max-width: 260px;" value="<?= old('operator_cellphone') ?>" placeholder="09123456789" autocomplete="off">
                    <span class="info-label me-2">Prof. License#:</span>
                    <input type="text" name="driver_license_no" class="paper-input" value="<?= old('driver_license_no') ?>" placeholder="License Number" autocomplete="off">
                </div>

                <!-- Line 4: Name of DRIVER -->
                <div class="info-row">
                    <span class="info-label me-2">Name of DRIVER:</span>
                    <input type="text" name="driver_name" class="paper-input" value="<?= old('driver_name') ?>" placeholder="Enter Driver Name" autocomplete="off">
                </div>

                <!-- Line 5: Make/Type and Color -->
                <div class="info-row">
                    <span class="info-label me-2">Make/Type:</span>
                    <input type="text" name="mv_make_type" class="paper-input me-4" value="<?= old('mv_make_type') ?>" placeholder="e.g. Honda TMX / Tricycle" autocomplete="off">
                    <span class="info-label me-2">Color:</span>
                    <input type="text" name="body_color" class="paper-input" style="max-width: 250px;" value="<?= old('body_color') ?>" placeholder="e.g. Blue" autocomplete="off">
                </div>
            </div>

            <!-- Checklist Section matching paper layout & wording -->
            <div class="checklist-section">
                <!-- 1: Full width -->
                <div class="check-row">
                    <label class="check-cell full" for="cert_attendance">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="cert_attendance" id="cert_attendance" <?= old('cert_attendance') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Cert. of Attendance of the driver (CTRAMO Seminar)</span>
                    </label>
                </div>

                <!-- 2: Two columns -->
                <div class="check-row">
                    <label class="check-cell half" for="outside_route">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="outside_route" id="outside_route" <?= old('outside_route') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Outside route</span>
                    </label>
                    <label class="check-cell half" for="mtop_8x16_route">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="mtop_8x16_route" id="mtop_8x16_route" <?= old('mtop_8x16_route') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>MTOP # 8 x 16 Suay/Ilog route (Fixed)</span>
                    </label>
                </div>

                <!-- 3: Left column -->
                <div class="check-row">
                    <label class="check-cell half" for="outside_route_8x8">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="outside_route_8x8" id="outside_route_8x8" <?= old('outside_route_8x8') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Outside route 8X8 (Fixed)</span>
                    </label>
                    <div class="check-cell half"></div>
                </div>

                <!-- 4: Two columns -->
                <div class="check-row">
                    <label class="check-cell half" for="city_proper">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="city_proper" id="city_proper" <?= old('city_proper') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>City Proper</span>
                    </label>
                    <label class="check-cell half" for="mtop_8x8_route">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="mtop_8x8_route" id="mtop_8x8_route" <?= old('mtop_8x8_route') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>MTOP # 8 x 8 City Proper (Fixed)</span>
                    </label>
                </div>

                <!-- 5: Full width -->
                <div class="check-row">
                    <label class="check-cell full" for="trash_can">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="trash_can" id="trash_can" <?= old('trash_can') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Trash Can</span>
                    </label>
                </div>

                <!-- 6: Full width -->
                <div class="check-row">
                    <label class="check-cell full" for="mtop_dashboard">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="mtop_dashboard" id="mtop_dashboard" <?= old('mtop_dashboard') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>MTOP # dash board/Name of operator's</span>
                    </label>
                </div>

                <!-- 7: Three columns -->
                <div class="check-row">
                    <label class="check-cell third" for="signal_light_left">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="signal_light_left" id="signal_light_left" <?= old('signal_light_left') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Signal light/left</span>
                    </label>
                    <label class="check-cell third" for="signal_light_right">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="signal_light_right" id="signal_light_right" <?= old('signal_light_right') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Signal light/right</span>
                    </label>
                    <label class="check-cell third" for="head_light">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="head_light" id="head_light" <?= old('head_light') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Head light</span>
                    </label>
                </div>

                <!-- 8: Three columns -->
                <div class="check-row">
                    <label class="check-cell third" for="stop_light">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="stop_light" id="stop_light" <?= old('stop_light') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Stop light</span>
                    </label>
                    <label class="check-cell third" for="side_mirror">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="side_mirror" id="side_mirror" <?= old('side_mirror') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Side Mirror left &amp; right</span>
                    </label>
                    <label class="check-cell third" for="horn">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="horn" id="horn" <?= old('horn') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Horn</span>
                    </label>
                </div>

                <!-- 9: Three columns -->
                <div class="check-row">
                    <label class="check-cell third" for="standard_muffler">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="standard_muffler" id="standard_muffler" <?= old('standard_muffler') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Standard Muffler</span>
                    </label>
                    <label class="check-cell third" for="color_coding">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="color_coding" id="color_coding" <?= old('color_coding') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Color coding</span>
                    </label>
                    <label class="check-cell third" for="tarifa">
                        <div class="doc-checkbox">
                            <input type="checkbox" name="tarifa" id="tarifa" <?= old('tarifa') ? 'checked' : '' ?>>
                            <div class="box"></div>
                        </div>
                        <span>Tarifa</span>
                    </label>
                </div>
            </div>

            <!-- Signatures Section -->
            <div class="signature-section">
                <?php 
                    $inspectorVal = old('inspected_by') ?? session()->get('name') ?? session()->get('username') ?? '';
                ?>
                <div class="sig-row">
                    <span class="info-label me-2">Inspected by:</span>
                    <input type="text" name="inspected_by" class="sig-line-input" required value="<?= esc($inspectorVal) ?>" placeholder="Officer Name" autocomplete="off">
                </div>
                <div class="sig-row">
                    <span class="info-label me-2">Date:</span>
                    <input type="date" name="inspection_date" class="sig-line-input" style="width: 220px;" required value="<?= old('inspection_date') ?? date('Y-m-d') ?>">
                </div>
            </div>

            <!-- Officer Signatory Block (matching paper document) -->
            <div class="officer-block">
                <p class="officer-name">LLACH T. ANZURES</p>
                <p class="officer-title">Administrative Aide VI</p>
            </div>

            <div style="clear: both;"></div>
        </div>

        <div class="toolbar-container px-2 mt-3 justify-content-end">
            <a href="<?= base_url('inspections') ?>" class="btn btn-light btn-sm px-3 me-2 border shadow-sm">Cancel</a>
            <button type="submit" class="btn btn-primary btn-sm px-4 shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Save Inspection Report
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
