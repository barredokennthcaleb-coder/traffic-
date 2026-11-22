<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Record Penalty Payment<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="pay-page">

    <!-- PAGE HEADER -->
    <div class="pay-header">
        <div class="pay-header-left">
            <div class="pay-header-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div>
                <h1 class="pay-title">Record Payment</h1>
                <p class="pay-subtitle">Issue official receipt for traffic violation</p>
            </div>
        </div>
        <a href="<?= base_url('penalties') ?>" class="pay-btn-back">
            <i class="bi bi-arrow-left me-1"></i> Back to Pending
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10">
            <form action="<?= base_url('penalties/store') ?>" method="POST" id="paymentForm">
                <?= csrf_field() ?>
                <input type="hidden" name="violation_id" value="<?= $violation['id'] ?>">
                <input type="hidden" name="amount_paid" value="<?= number_format((float) $violation['penalty_amount'], 2, '.', '') ?>">

                <div class="receipt-wrapper">
                    <div class="receipt-container">
                        <!-- Header -->
                        <div class="receipt-header">
                            <img src="<?= base_url('img/pic-1.png') ?>" alt="Logo">
                            <div class="header-text">
                                <h2>OFFICIAL RECEIPT</h2>
                                <p>Republic of the Philippines</p>
                                <h3>OFFICE OF THE TREASURER</h3>
                                <p>City of Kabankalan</p>
                            </div>
                            <img src="<?= base_url('img/pic-1.png') ?>" alt="Logo">
                        </div>

                        <!-- Form Info -->
                        <div class="row-flex">
                            <div class="col-flex border-right-black" style="width: 50%;">
                                <div style="font-size: 15px;">Accountable Form No. 51</div>
                                <div style="font-size: 15px;">Revised January, 1992</div>
                            </div>
                            <div class="col-flex" style="width: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; font-size: 20px;">
                                ORIGINAL
                            </div>
                        </div>

                        <!-- Date & No -->
                        <div class="row-flex">
                            <div class="col-flex border-right-black" style="width: 50%; display: flex; align-items: flex-end;">
                                <div style="width: 60px;">DATE</div>
                                <input type="text" class="receipt-input" style="flex: 1; text-align: center; font-size: 18px;" value="<?= date('M d, Y') ?>">
                            </div>
                            <div class="col-flex" style="width: 50%; display: flex; align-items: center; justify-content: center;">
                                <span style="font-size: 20px; margin-right: 15px;">NO.</span>
                                <input
                                    type="text"
                                    name="receipt_number"
                                    class="orcr-auto-number"
                                    value="<?= esc(old('receipt_number', $next_orcr_number)) ?>"
                                    inputmode="numeric"
                                    pattern="[0-9]{1,18}"
                                    maxlength="18"
                                    required
                                    aria-label="Official Receipt (OR) number"
                                    title="Enter the Official Receipt (OR) number. The next number is prefilled."
                                >
                            </div>
                        </div>

                        <!-- Payor -->
                        <div class="row-flex">
                            <div class="col-flex border-right-black" style="width: 75%; display: flex; align-items: flex-end;">
                                <div style="width: 70px;">PAYOR</div>
                                <input type="text" class="receipt-input" style="flex: 1; padding-left: 10px; font-size: 18px; text-transform: uppercase;" value="<?= esc($violation['driver_name']) ?>">
                            </div>
                            <div class="col-flex" style="width: 25%; text-align: center; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>FUND</div>
                                <input type="text" class="receipt-input" style="text-align: center; margin-top: 5px;">
                            </div>
                        </div>

                        <!-- Table -->
                        <table class="table-container">
                            <thead>
                                <tr>
                                    <th class="col-nature">NATURE OF COLLECTION</th>
                                    <th class="col-account">ACCOUNT<br>CODE</th>
                                    <th class="col-amount">AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    // One receipt line per violation, each with its own fine.
                                    $receiptLines  = $infractions ?? [];
                                    $blankRows     = max(0, 7 - count($receiptLines));
                                ?>
                                <?php if ($receiptLines === []): ?>
                                <tr>
                                    <td class="col-nature"><input type="text" class="receipt-input" value="<?= esc($violation['violation_type']) ?> - Ticket #<?= esc($violation['ticket_id']) ?>"></td>
                                    <td class="col-account"><input type="text" class="receipt-input" style="text-align: center;"></td>
                                    <td class="col-amount">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <span class="me-1">&#8369;</span>
                                            <input type="number" step="0.01" name="amount_paid" class="receipt-input text-end amount-row" style="width: 100px; padding: 0;" required value="<?= $violation['penalty_amount'] ?>">
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                    <?php foreach ($receiptLines as $index => $line): ?>
                                    <tr>
                                        <td class="col-nature">
                                            <input type="text" class="receipt-input"
                                                   value="<?= esc($line['violation_type']) ?><?= $index === 0 ? ' - Ticket #' . esc($line['ticket_id'] ?? $violation['ticket_id']) : '' ?>">
                                        </td>
                                        <td class="col-account"><input type="text" class="receipt-input" style="text-align: center;"></td>
                                        <td class="col-amount">
                                            <div class="d-flex align-items-center justify-content-end">
                                                <span class="me-1">&#8369;</span>
                                                <input type="text" class="receipt-input text-end amount-row" style="width: 100px; padding: 0;" value="<?= number_format((float) $line['penalty_amount'], 2, '.', '') ?>">
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php for($i=0; $i<$blankRows; $i++): ?>
                                <tr>
                                    <td class="col-nature"><input type="text" class="receipt-input"></td>
                                    <td class="col-account"><input type="text" class="receipt-input" style="text-align: center;"></td>
                                    <td class="col-amount">
                                        <div class="d-flex align-items-center justify-content-end">
                                            <span class="me-1">&#8369;</span>
                                            <input type="text" class="receipt-input text-end amount-row" style="width: 100px; padding: 0;">
                                        </div>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                                <tr class="total-row">
                                    <td class="col-nature" style="letter-spacing: 5px; text-align: center;">T O T A L</td>
                                    <td class="col-account"></td>
                                    <td class="col-amount" style="position: relative;">
                                        <span style="position: absolute; left: 12px; font-weight: normal;">&#8369;</span>
                                        <span id="totalDisplay"><?= number_format($violation['penalty_amount'], 2) ?></span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <?php
                            $amountWords = '';
                            try {
                                if(class_exists('NumberFormatter')) {
                                    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
                                    $amountWords = ucwords($f->format($violation['penalty_amount'])) . ' Pesos';
                                }
                            } catch (\Exception $e) {}
                        ?>
                        <!-- Amount in Words -->
                        <div class="amount-in-words">
                            <div style="font-size: 14px; margin-bottom: 5px;">AMOUNT IN WORDS</div>
                            <input type="text" class="receipt-input" id="amountWordsInput" style="min-height: 25px; padding-left: 10px; text-transform: uppercase; font-size: 16px;" value="<?= $amountWords ?>">
                        </div>

                        <!-- Bank Details Section -->
                        <div class="payment-type-bank">
                            <div class="payment-checkboxes">
                                <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                                    <input type="radio" name="payment_method" value="Cash" class="payment-radio" required checked> Cash
                                </label>
                                <label style="display: block; margin-bottom: 8px; cursor: pointer;">
                                    <input type="radio" name="payment_method" value="Check" class="payment-radio"> Check
                                </label>
                                <label style="display: block; cursor: pointer;">
                                    <input type="radio" name="payment_method" value="Money Order" class="payment-radio"> Money Order
                                </label>
                            </div>
                            <div class="bank-details">
                                <div class="bank-header">
                                    <div>DRAWEE<br>BANK</div>
                                    <div>NUMBER</div>
                                    <div>DATE</div>
                                </div>
                                <div class="bank-inputs">
                                    <div><input type="text" class="receipt-input bank-field"></div>
                                    <div><input type="text" class="receipt-input bank-field"></div>
                                    <div><input type="text" class="receipt-input bank-field"></div>
                                </div>
                                <div class="bank-inputs" style="border-top: 1px solid #000;">
                                    <div><input type="text" class="receipt-input bank-field"></div>
                                    <div><input type="text" class="receipt-input bank-field"></div>
                                    <div><input type="text" class="receipt-input bank-field"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks Form Field -->
                        <div class="form-remarks">
                            <label for="remarks" style="font-size: 14px; font-weight: bold; margin-bottom: 5px; display: block;">REMARKS / ADDITIONAL NOTES:</label>
                            <textarea name="remarks" id="remarks" class="receipt-input" rows="2" placeholder="Optional notes..."></textarea>
                        </div>

                        <!-- Signature -->
                        <?php
                            $collectorName = trim((string) (session()->get('full_name') ?? ''));
                            if ($collectorName === '') {
                                $collectorName = trim((string) (session()->get('username') ?? 'Admin'));
                            }
                        ?>
                        <div class="signature-section">
                            <div style="font-size: 16px;">Received the amount stated above</div>
                            <div style="margin-top: 30px; padding-left: 50%;">
                                <div style="display:flex; align-items: flex-end;">
                                    <div style="margin-right: 15px; font-size: 18px;">By:</div>
                                    <div style="flex:1; text-align: center;">
                                        <div class="collecting-sig-box">
                                            <img id="collectingSigPreview" src="" alt="Collecting officer e-signature">
                                            <span id="collectingSigPlaceholder">E-signature not yet applied</span>
                                        </div>
                                        <input type="text" name="collecting_officer_name" id="collecting_officer_name" class="receipt-input" style="height: 30px; text-align: center; font-size: 18px;" value="<?= esc(old('collecting_officer_name', $collectorName)) ?>" required>
                                        <div style="text-align: center; font-size: 14px; margin-top: 5px;">COLLECTING OFFICER</div>
                                        <div class="collecting-sig-actions">
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-0" id="openCollectingSigBtn">
                                                <i class="bi bi-pen-fill me-1"></i><span id="collectingSigBtnLabel">Sign Here</span>
                                            </button>
                                            <button type="button" class="btn btn-link text-danger btn-sm p-0 ms-1" id="clearCollectingSigBtn" style="display:none;" title="Remove signature">
                                                <i class="bi bi-x-circle-fill fs-6"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="collecting_officer_signature" id="collecting_officer_signature" value="">
                        </div>
                        
                        <!-- Footer Note -->
                        <div style="padding: 15px; font-size: 14px; border-top: 2px solid #000; text-align: center;">
                            NOTE: Write the number and date of this receipt on the back of check or money order received.
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?= base_url('penalties') ?>" class="pay-btn-cancel">
                            <i class="bi bi-x-lg me-1"></i> Cancel
                        </a>
                        <button type="submit" class="pay-btn-submit" id="submitBtn">
                            <i class="bi bi-check-circle me-1"></i> Confirm &amp; Record Payment
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Collecting Officer E-Signature Modal (draw or upload) -->
<div class="modal fade" id="collectingSignatureModal" tabindex="-1" aria-labelledby="collectingSignatureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="collectingSignatureModalLabel">
                    <i class="bi bi-pen-fill text-primary me-2"></i>Collecting Officer E-Signature
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-justified mb-3" id="collectingSigTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold" id="collectingDrawTab" data-bs-toggle="tab" data-bs-target="#collectingDrawPanel" type="button" role="tab">
                            <i class="bi bi-fingerprint me-1"></i> Draw Signature
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold" id="collectingUploadTab" data-bs-toggle="tab" data-bs-target="#collectingUploadPanel" type="button" role="tab">
                            <i class="bi bi-upload me-1"></i> Upload Image
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="collectingDrawPanel" role="tabpanel">
                        <p class="text-muted small mb-2">Use your mouse, touch pen, or finger to draw your signature inside the pad:</p>
                        <div class="border rounded-3 p-2 bg-light text-center">
                            <canvas id="collectingSigCanvas" width="420" height="170" style="touch-action: none; cursor: crosshair; background:#fff; border:1px dashed #cbd5e1; border-radius:8px; width:100%; max-width:420px; height:170px;"></canvas>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearCollectingPadBtn">
                                <i class="bi bi-eraser-fill me-1"></i> Clear Pad
                            </button>
                            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>Smooth Digital Ink</span>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="collectingUploadPanel" role="tabpanel">
                        <p class="text-muted small mb-2">Select a transparent PNG, JPG, or WebP image of your signature:</p>
                        <input type="file" id="collectingSigFile" class="form-control" accept="image/png, image/jpeg, image/webp">
                        <div class="text-center mt-3 p-3 border rounded-3 bg-light" id="collectingUploadPreviewBox" style="display:none;">
                            <img id="collectingUploadPreview" src="" alt="Signature preview" style="max-height:100px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary fw-bold px-4" id="applyCollectingSigBtn">
                    <i class="bi bi-check-circle-fill me-1"></i> Apply Signature
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@import url('<?= base_url('vendor/inter/inter.css') ?>');

/* ── PAGE WRAPPER ───────────────────────────────────────────── */
.pay-page {
    font-family: 'Inter', system-ui, sans-serif;
    padding: 1.5rem 1.75rem 2rem;
    background: #f8fafc;
    min-height: 100vh;
}

/* ── PAGE HEADER ────────────────────────────────────────────── */
.pay-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.75rem;
}
.pay-header-left {
    display: flex;
    align-items: center;
    gap: .85rem;
}
.pay-header-icon {
    width: 46px; height: 46px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    color: #fff;
    font-size: 1.25rem;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    flex-shrink: 0;
}
.pay-title {
    font-size: 1.3rem;
    font-weight: 800;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.02em;
}
.pay-subtitle {
    font-size: 0.8rem;
    color: #64748b;
    margin: 0;
    font-weight: 500;
}
.pay-btn-back {
    display: inline-flex; align-items: center;
    background: #0f172a;
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 0.55rem 1.1rem;
    font-size: 0.87rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.2);
}
.pay-btn-back:hover {
    background: #1e293b;
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
}

/* ── RECEIPT WRAPPER ────────────────────────────────────────── */
.receipt-wrapper {
    font-family: 'Times New Roman', Times, serif;
    width: 100%;
    max-width: 750px;
    margin: 0 auto;
    background: #fff;
    color: #000;
    padding: 24px;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(15, 23, 42, 0.08);
    border: 1px solid #e9eef5;
}
.receipt-container {
    border: 2px solid #000;
    padding: 0;
    background: #fff;
    position: relative;
}

/* ── RECEIPT HEADER ─────────────────────────────────────────── */
.receipt-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    border-bottom: 2px solid #000;
}
.receipt-header img {
    width: 80px;
    height: 80px;
    object-fit: contain;
}
.header-text {
    text-align: center;
    flex-grow: 1;
}
.header-text h2 {
    font-size: 26px;
    font-weight: bold;
    margin: 0;
    letter-spacing: 1px;
    color: #000;
}
.header-text h3 {
    font-size: 20px;
    font-weight: bold;
    margin: 4px 0;
    color: #000;
}
.header-text p {
    margin: 0;
    font-size: 16px;
    color: #000;
}

/* ── ROWS & COLS ────────────────────────────────────────────── */
.row-flex {
    display: flex;
    border-bottom: 2px solid #000;
}
.col-flex {
    padding: 10px;
    color: #000;
}
.border-right-black {
    border-right: 2px solid #000;
}

/* ── COLLECTION TABLE ───────────────────────────────────────── */
.table-container {
    width: 100%;
    border-collapse: collapse;
}
.table-container th, .table-container td {
    border: 1px solid #000;
    padding: 8px 12px;
    color: #000;
}
.table-container th {
    border-top: 0;
    border-bottom: 2px solid #000;
    font-weight: normal;
    font-size: 14px;
    text-align: center;
}
.col-nature { width: 50%; border-left: 0; }
.col-account { width: 25%; text-align: center; }
.col-amount { width: 25%; border-right: 0; text-align: right; }
.table-container td {
    height: 35px;
}
.total-row {
    border-top: 2px solid #000 !important;
    font-weight: bold;
}
.total-row td {
    border-top: 2px solid #000 !important;
}

/* ── AMOUNT IN WORDS ────────────────────────────────────────── */
.amount-in-words {
    padding: 8px;
    border-bottom: 2px solid #000;
    color: #000;
}

/* ── PAYMENT TYPE & BANK ────────────────────────────────────── */
.payment-type-bank {
    display: flex;
    border-bottom: 2px solid #000;
}
.payment-checkboxes {
    width: 35%;
    padding: 12px;
    border-right: 2px solid #000;
    color: #000;
}
.bank-details {
    width: 65%;
    display: flex;
    flex-direction: column;
}
.bank-header {
    display: flex;
    border-bottom: 1px solid #000;
}
.bank-header div {
    flex: 1;
    text-align: center;
    padding: 6px;
    font-size: 14px;
    color: #000;
}
.bank-header div:not(:last-child) {
    border-right: 1px solid #000;
}
.bank-inputs {
    display: flex;
    flex: 1;
    min-height: 30px;
}
.bank-inputs div {
    flex: 1;
    border-right: 1px solid #000;
}
.bank-inputs div:last-child {
    border-right: 0;
}

/* ── SIGNATURE ──────────────────────────────────────────────── */
.signature-section {
    padding: 20px;
    color: #000;
}
.collecting-sig-box {
    position: relative;
    height: 62px;
    border-bottom: 1px solid #000;
    display: flex;
    align-items: flex-end;
    justify-content: center;
}
.collecting-sig-box img {
    max-height: 56px;
    max-width: 100%;
    object-fit: contain;
    display: none;
}
.collecting-sig-box #collectingSigPlaceholder {
    font-size: 12px;
    color: #94a3b8;
    font-style: italic;
    padding-bottom: 8px;
}
.collecting-sig-actions {
    margin-top: 10px;
}
.collecting-sig-actions .btn { font-family: 'Inter', sans-serif; }
@media print {
    .collecting-sig-actions { display: none !important; }
}

/* ── FORM INPUTS ────────────────────────────────────────────── */
.receipt-input {
    border: none;
    border-bottom: 1px dashed #000;
    background: transparent;
    font-family: inherit;
    font-size: inherit;
    outline: none;
    width: 100%;
    color: #000;
}
.receipt-input:focus {
    border-bottom: 1px solid #000;
}
.orcr-auto-number {
    width: 155px;
    border: 0;
    border-bottom: 1px dashed #000;
    background: transparent;
    color: #dc2626;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: 2px;
    text-align: center;
    font-family: monospace;
    outline: none;
}
.orcr-auto-number:focus {
    border-bottom-style: solid;
    box-shadow: 0 1px 0 #dc2626;
}
.payment-radio {
    accent-color: #000;
    width: 16px;
    height: 16px;
    margin-right: 8px;
    vertical-align: middle;
    cursor: pointer;
}
.form-remarks {
    padding: 15px;
    border-top: 2px solid #000;
    color: #000;
}
.bank-field {
    border-bottom: none !important;
    width: 100%;
    padding: 4px 6px;
    height: 100%;
}

/* ── FORM ACTIONS ───────────────────────────────────────────── */
.form-actions {
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 0.75rem;
}
.pay-btn-cancel {
    display: inline-flex; align-items: center;
    padding: 0.6rem 1.25rem;
    border-radius: 10px;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    color: #475569;
    font-size: 0.88rem;
    font-weight: 600;
    font-family: 'Inter', sans-serif;
    text-decoration: none;
    transition: all 0.2s ease;
}
.pay-btn-cancel:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
    color: #0f172a;
}
.pay-btn-submit {
    display: inline-flex; align-items: center;
    padding: 0.6rem 1.5rem;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
    font-family: 'Inter', sans-serif;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 3px 10px rgba(22, 163, 74, 0.3);
}
.pay-btn-submit:hover {
    background: linear-gradient(135deg, #15803d, #166534);
    transform: translateY(-1.5px);
    box-shadow: 0 5px 16px rgba(22, 163, 74, 0.35);
}
.pay-btn-submit:active {
    transform: translateY(0);
}

/* ── RESPONSIVE ─────────────────────────────────────────────── */
@media (max-width: 768px) {
    .pay-page { padding: 1rem; }
    .pay-header { flex-direction: column; align-items: flex-start; }
    .pay-btn-back { width: 100%; justify-content: center; }
    .receipt-wrapper { padding: 12px; border-radius: 12px; }
    .receipt-header { flex-direction: column; gap: 10px; }
    .receipt-header img { width: 60px; height: 60px; }
    .header-text h2 { font-size: 20px; }
    .header-text h3 { font-size: 16px; }
    .row-flex { flex-direction: column; }
    .border-right-black { border-right: none; border-bottom: 1px solid #000; }
    .payment-type-bank { flex-direction: column; }
    .payment-checkboxes { width: 100%; border-right: none; border-bottom: 1px solid #000; }
    .bank-details { width: 100%; }
    .form-actions { flex-direction: column; }
    .pay-btn-cancel, .pay-btn-submit { width: 100%; justify-content: center; }
}

/* ── OFFICIAL RECEIPT: ALWAYS PAPER WHITE ───────────────────────────
   An OR is a printed government form, so it must read as clean white
   paper whether the admin shell is in Light, Dark or Auto mode (and
   even if the browser forces dark colours on web content). Every rule
   is scoped to this page so the rest of the app keeps its theme. */
.pay-page {
    color-scheme: light !important; /* stops OS/browser dark form controls */
    background: #ffffff !important;
}

/* Paper surfaces: solid white, no blurred gradient overlays, no shadows. */
.pay-page .receipt-wrapper,
.pay-page .receipt-container,
.pay-page .receipt-container > div,
.pay-page .receipt-container table,
.pay-page .receipt-container table tbody,
.pay-page .receipt-container table tr,
.pay-page .receipt-container table td,
.pay-page .receipt-container table th {
    background-color: #ffffff !important;
    background-image: none !important;
    box-shadow: none !important;
    color: #000000 !important;
    text-shadow: none !important;
    border-color: #000000 !important;
}

/* All OR text stays ink-black in every theme (including inline styles). */
.pay-page .receipt-container,
.pay-page .receipt-container div:not(.btn):not(.collecting-sig-actions),
.pay-page .receipt-container label,
.pay-page .receipt-container h2,
.pay-page .receipt-container h3,
.pay-page .receipt-container p,
.pay-page .receipt-container td,
.pay-page .receipt-container th {
    color: #000000 !important;
    -webkit-text-fill-color: #000000 !important;
}

/* Form fields: invisible box with a dashed ink baseline, like the form. */
.pay-page .receipt-container .receipt-input,
.pay-page .receipt-container textarea.receipt-input,
.pay-page .receipt-container .bank-field {
    background-color: transparent !important;
    background-image: none !important;
    border: 0 !important;
    border-bottom: 1px dashed #000000 !important;
    border-radius: 0 !important;
    box-shadow: none !important;
    color: #000000 !important;
    -webkit-text-fill-color: #000000 !important;
}
.pay-page .receipt-container .receipt-input:focus,
.pay-page .receipt-container textarea.receipt-input:focus {
    border-bottom: 1px solid #000000 !important;
    outline: none !important;
}
.pay-page .receipt-container .bank-field {
    border-bottom: 0 !important;
}
.pay-page .receipt-container ::placeholder {
    color: #94a3b8 !important;
    -webkit-text-fill-color: #94a3b8 !important;
}

/* The OR number is the one red figure on the form, in both themes. */
.pay-page .receipt-container .orcr-auto-number {
    background-color: transparent !important;
    background-image: none !important;
    color: #dc2626 !important;
    -webkit-text-fill-color: #dc2626 !important;
    border-bottom: 1px dashed #000000 !important;
    box-shadow: none !important;
}

/* ── PAYMENT SECTION (MODE OF PAYMENT + BANK DETAILS) ─────────────── */
.pay-page .receipt-container .payment-checkboxes {
    background: #ffffff !important;
    border-right: 2px solid #000000 !important;
}
.pay-page .receipt-container .payment-checkboxes label {
    display: flex;
    align-items: center;
    font-family: 'Times New Roman', Times, serif;
    font-size: 16px;
    color: #000000 !important;
    min-height: 28px;
}
.pay-page .receipt-container .payment-radio {
    -webkit-appearance: radio;
    appearance: radio;
    accent-color: #000000 !important;
    background-color: #ffffff !important;
    border: 1px solid #000000 !important;
    box-shadow: none !important;
    width: 15px;
    height: 15px;
    margin: 0 8px 0 0;
    flex: 0 0 auto;
    cursor: pointer;
}
.pay-page .receipt-container .bank-header {
    border-bottom: 1px solid #000000 !important;
}
.pay-page .receipt-container .bank-header div {
    font-family: 'Times New Roman', Times, serif;
    font-size: 14px;
    color: #000000 !important;
}
.pay-page .receipt-container .bank-header div:not(:last-child),
.pay-page .receipt-container .bank-inputs div {
    border-color: #000000 !important;
}
.pay-page .receipt-container .form-remarks {
    border-top: 2px solid #000000 !important;
}
.pay-page .receipt-container .form-remarks label {
    font-family: 'Times New Roman', Times, serif;
    font-size: 14px;
}
.pay-page .receipt-container .form-remarks textarea {
    resize: vertical;
    line-height: 1.4;
}

/* E-signature box reads as an ink line rather than a dark well. */
.pay-page .receipt-container .collecting-sig-box {
    border-bottom: 1px solid #000000 !important;
}
.pay-page .receipt-container .collecting-sig-box #collectingSigPlaceholder {
    color: #94a3b8 !important;
}
.pay-page .receipt-container .collecting-sig-actions .btn {
    font-family: 'Inter', sans-serif;
    -webkit-text-fill-color: initial;
}

@media print {
    .pay-page,
    .pay-page .receipt-wrapper,
    .pay-page .receipt-container,
    .pay-page .receipt-container * {
        background: #ffffff !important;
        background-image: none !important;
        box-shadow: none !important;
        color: #000000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .pay-page .receipt-container .orcr-auto-number {
        color: #dc2626 !important;
    }
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    // ── Collecting officer e-signature ──────────────────────────────
    const STORAGE_KEY = 'collecting_sig_<?= (int) session()->get('id') ?>';
    const hiddenSig   = document.getElementById('collecting_officer_signature');
    const previewImg  = document.getElementById('collectingSigPreview');
    const placeholder = document.getElementById('collectingSigPlaceholder');
    const clearBtn    = document.getElementById('clearCollectingSigBtn');
    const btnLabel    = document.getElementById('collectingSigBtnLabel');
    const modalEl     = document.getElementById('collectingSignatureModal');
    const canvas      = document.getElementById('collectingSigCanvas');
    const fileInput   = document.getElementById('collectingSigFile');
    const uploadBox   = document.getElementById('collectingUploadPreviewBox');
    const uploadImg   = document.getElementById('collectingUploadPreview');
    let ctx = null, isDrawing = false, hasDrawn = false, uploadedData = '', bsModal = null;

    function applySignature(dataUrl, remember) {
        if (!hiddenSig) return;
        hiddenSig.value = dataUrl;
        if (previewImg) {
            previewImg.src = dataUrl;
            previewImg.style.display = 'block';
        }
        if (placeholder) placeholder.style.display = 'none';
        if (clearBtn) clearBtn.style.display = 'inline-block';
        if (btnLabel) btnLabel.textContent = 'Change Signature';
        if (remember) {
            try { localStorage.setItem(STORAGE_KEY, dataUrl); } catch (e) { /* storage may be unavailable */ }
        }
    }

    function clearSignature() {
        if (hiddenSig) hiddenSig.value = '';
        if (previewImg) {
            previewImg.removeAttribute('src');
            previewImg.style.display = 'none';
        }
        if (placeholder) placeholder.style.display = 'inline';
        if (clearBtn) clearBtn.style.display = 'none';
        if (btnLabel) btnLabel.textContent = 'Sign Here';
        try { localStorage.removeItem(STORAGE_KEY); } catch (e) { /* storage may be unavailable */ }
    }

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        let clientX = e.clientX, clientY = e.clientY;
        if (e.touches && e.touches.length) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }
        return {
            x: (clientX - rect.left) * (canvas.width / rect.width),
            y: (clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function initCanvas() {
        if (!canvas || ctx) return;
        ctx = canvas.getContext('2d');

        function start(e) {
            e.preventDefault();
            isDrawing = true;
            hasDrawn = true;
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.strokeStyle = '#0f172a';
            const p = getPos(e);
            ctx.beginPath();
            ctx.moveTo(p.x, p.y);
        }
        function move(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const p = getPos(e);
            ctx.lineTo(p.x, p.y);
            ctx.stroke();
        }
        function stop() { isDrawing = false; }

        canvas.addEventListener('mousedown', start);
        canvas.addEventListener('mousemove', move);
        canvas.addEventListener('mouseup', stop);
        canvas.addEventListener('mouseleave', stop);
        canvas.addEventListener('touchstart', start, { passive: false });
        canvas.addEventListener('touchmove', move, { passive: false });
        canvas.addEventListener('touchend', stop);
    }

    function openSignatureModal() {
        initCanvas();
        if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
        isDrawing = false;
        hasDrawn = false;
        uploadedData = '';
        if (fileInput) fileInput.value = '';
        if (uploadBox) uploadBox.style.display = 'none';
        if (uploadImg) uploadImg.removeAttribute('src');
        if (modalEl && window.bootstrap) {
            bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();
        }
    }

    document.getElementById('openCollectingSigBtn')?.addEventListener('click', openSignatureModal);
    clearBtn?.addEventListener('click', clearSignature);
    document.getElementById('clearCollectingPadBtn')?.addEventListener('click', function () {
        if (ctx) ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
    });

    fileInput?.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;
        if (!/^image\/(png|jpeg|webp)$/.test(file.type)) {
            Swal.fire({ icon: 'warning', title: 'Unsupported file', text: 'Choose a PNG, JPG, or WebP image.' });
            fileInput.value = '';
            return;
        }
        if (file.size > 3 * 1024 * 1024) {
            Swal.fire({ icon: 'warning', title: 'Image too large', text: 'Choose a signature image smaller than 3 MB.' });
            fileInput.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (evt) {
            uploadedData = evt.target.result;
            if (uploadImg) uploadImg.src = uploadedData;
            if (uploadBox) uploadBox.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('applyCollectingSigBtn')?.addEventListener('click', function () {
        let dataUrl = '';
        const activeTab = document.querySelector('#collectingSigTab .nav-link.active');
        if (activeTab && activeTab.id === 'collectingUploadTab') {
            if (!uploadedData) {
                Swal.fire({ icon: 'warning', title: 'No file selected', text: 'Please choose a signature image first.' });
                return;
            }
            dataUrl = uploadedData;
        } else {
            if (!hasDrawn) {
                Swal.fire({ icon: 'warning', title: 'Empty signature pad', text: 'Please draw your signature before applying.' });
                return;
            }
            dataUrl = canvas.toDataURL('image/png');
        }
        applySignature(dataUrl, true);
        bsModal?.hide();
    });

    // Reuse the officer's saved signature on this device, if any.
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved && hiddenSig && !hiddenSig.value) applySignature(saved, false);
    } catch (e) { /* storage may be unavailable */ }

    // Require a signature before the payment can be recorded.
    document.getElementById('paymentForm')?.addEventListener('submit', function (e) {
        if (!hiddenSig || !hiddenSig.value) {
            e.preventDefault();
            const btn = document.getElementById('submitBtn');
            if (btn) { btn.disabled = false; }
            Swal.fire({ icon: 'warning', title: 'Signature required', text: 'Please apply the collecting officer e-signature before recording the payment.' });
            openSignatureModal();
            return;
        }
        const btn = document.getElementById('submitBtn');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Processing...';
        }
    });
})();

// Auto-calculate TOTAL from amount rows
function recalcTotal() {
    const rows = document.querySelectorAll('.amount-row');
    let sum = 0;
    rows.forEach(r => {
        const v = parseFloat(r.value);
        if (!isNaN(v)) sum += v;
    });
    const display = document.getElementById('totalDisplay');
    if (display) display.textContent = sum.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}
document.querySelectorAll('.amount-row').forEach(input => {
    input.addEventListener('input', recalcTotal);
});
</script>
<?= $this->endSection() ?>
