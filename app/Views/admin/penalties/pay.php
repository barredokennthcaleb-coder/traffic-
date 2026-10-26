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
                                <input type="text" name="transaction_id" class="orcr-input" placeholder="3852351" required title="Enter the OR/CR Number">
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
                        <div class="signature-section">
                            <div style="font-size: 16px;">Received the amount stated above</div>
                            <div style="margin-top: 30px; padding-left: 50%;">
                                <div style="display:flex; align-items: flex-end;">
                                    <div style="margin-right: 15px; font-size: 18px;">By:</div>
                                    <div style="flex:1;">
                                        <input type="text" class="receipt-input" style="height: 30px; text-align: center; font-size: 18px; font-family: cursive;" value="<?= session()->get('username') ?? 'Admin' ?>">
                                        <div style="text-align: center; font-size: 14px; margin-top: 5px;">COLLECTING OFFICER</div>
                                    </div>
                                </div>
                            </div>
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

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

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
.orcr-input {
    color: red;
    font-size: 24px;
    font-weight: bold;
    letter-spacing: 2px;
    font-family: monospace;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 4px 12px;
    width: 160px;
    background: #fff;
    transition: all 0.2s ease;
    outline: none;
}
.orcr-input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Prevent double submit
document.getElementById('paymentForm')?.addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Processing...';
    }
});

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
