<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Record Penalty Payment<?= $this->endSection() ?>

<?= $this->section('content') ?>

<style>
    .receipt-wrapper {
        font-family: 'Times New Roman', Times, serif;
        width: 100%;
        max-width: 750px;
        margin: 0 auto;
        background: #fff;
        color: #000;
        padding: 20px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    .receipt-container {
        border: 2px solid #000;
        padding: 0;
        background: #fff;
        position: relative;
    }
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
    }
    .header-text h3 {
        font-size: 20px;
        font-weight: bold;
        margin: 4px 0;
    }
    .header-text p {
        margin: 0;
        font-size: 16px;
    }
    .row-flex {
        display: flex;
        border-bottom: 2px solid #000;
    }
    .col-flex {
        padding: 10px;
    }
    .border-right-black {
        border-right: 2px solid #000;
    }
    
    .table-container {
        width: 100%;
        border-collapse: collapse;
    }
    .table-container th, .table-container td {
        border: 1px solid #000;
        padding: 8px 12px;
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
        height: 35px; /* Empty rows */
    }
    
    .total-row {
        border-top: 2px solid #000 !important;
        font-weight: bold;
    }
    .total-row td {
        border-top: 2px solid #000 !important;
    }
    .amount-in-words {
        padding: 8px;
        border-bottom: 2px solid #000;
    }
    .payment-type-bank {
        display: flex;
        border-bottom: 2px solid #000;
    }
    .payment-checkboxes {
        width: 35%;
        padding: 12px;
        border-right: 2px solid #000;
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
    
    .signature-section {
        padding: 20px;
    }

    /* Form specific styles overrides */
    .receipt-input {
        border: none;
        border-bottom: 1px dashed #000;
        background: transparent;
        font-family: inherit;
        font-size: inherit;
        outline: none;
        width: 100%;
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
        border: 1px solid #ccc;
        border-radius: 4px;
        padding: 2px 10px;
        width: 150px;
        background: #fdfdfd;
    }
    .orcr-input:focus {
        border-color: red;
        outline: none;
        box-shadow: 0 0 5px rgba(255, 0, 0, 0.2);
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
    }

    .form-actions {
        margin-top: 20px;
        text-align: right;
    }
</style>

<div class="row justify-content-center">
    <div class="col-md-10">
        <form action="<?= base_url('penalties/store') ?>" method="POST" id="paymentForm">
            <?= csrf_field() ?>
            <input type="hidden" name="violation_id" value="<?= $violation['id'] ?>">

            <div class="receipt-wrapper">
                <div class="receipt-container">
                    <!-- Header -->
                    <div class="receipt-header">
                        <img src="<?= base_url('img/pic 1.png') ?>" alt="Logo">
                        <div class="header-text">
                            <h2>OFFICIAL RECEIPT</h2>
                            <p>Republic of the Philippines</p>
                            <h3>OFFICE OF THE TREASURER</h3>
                            <p>City of Kabankalan</p>
                        </div>
                        <img src="<?= base_url('img/pic 1.png') ?>" alt="Logo">
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
                            <div style="flex: 1; text-align: center; border-bottom: 1px solid #000; font-size: 18px;">
                                <?= date('M d, Y') ?>
                            </div>
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
                            <div style="flex: 1; border-bottom: 1px solid #000; padding-left: 10px; font-size: 18px; text-transform: uppercase;">
                                <?= esc($violation['driver_name']) ?>
                            </div>
                        </div>
                        <div class="col-flex" style="width: 25%; text-align: center; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>FUND</div>
                            <div style="border-top: 1px solid #000; margin-top: 10px;"></div>
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
                            <tr>
                                <td class="col-nature"><?= esc($violation['violation_type']) ?> - Ticket #<?= esc($violation['ticket_id']) ?></td>
                                <td class="col-account"></td>
                                <td class="col-amount">
                                    <div class="d-flex align-items-center justify-content-end">
                                        <span class="me-1">₱</span>
                                        <input type="number" step="0.01" name="amount_paid" class="receipt-input text-end" style="width: 100px; padding: 0;" required value="<?= $violation['penalty_amount'] ?>">
                                    </div>
                                </td>
                            </tr>
                            <?php for($i=0; $i<6; $i++): ?>
                            <tr>
                                <td class="col-nature"></td>
                                <td class="col-account"></td>
                                <td class="col-amount"></td>
                            </tr>
                            <?php endfor; ?>
                            <tr class="total-row">
                                <td class="col-nature" style="letter-spacing: 5px; text-align: center;">T O T A L</td>
                                <td class="col-account"></td>
                                <td class="col-amount" style="position: relative;">
                                    <span style="position: absolute; left: 12px; font-weight: normal;">₱</span>
                                    <?= number_format($violation['penalty_amount'], 2) ?>
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
                        <div style="border-bottom: 1px solid #000; min-height: 25px; padding-left: 10px; text-transform: uppercase; font-size: 16px;">
                            <?= $amountWords ?>
                        </div>
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
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                            <div class="bank-inputs" style="border-top: 1px solid #000;">
                                <div></div>
                                <div></div>
                                <div></div>
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
                                    <div style="border-bottom: 1px solid #000; height: 30px; text-align: center; font-size: 18px; font-family: cursive;">
                                        <?= session()->get('username') ?? 'Admin' ?>
                                    </div>
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
                    <a href="<?= base_url('penalties') ?>" class="btn btn-outline-secondary px-4 me-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-check-circle me-1"></i> Confirm & Record Payment
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
