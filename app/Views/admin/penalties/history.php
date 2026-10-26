<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Payment History - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="ht-page">

    <!-- PAGE HEADER -->
    <div class="ht-header">
        <div class="ht-header-left">
            <div class="ht-header-icon">
                <i class="bi bi-receipt-cutoff"></i>
            </div>
            <div>
                <h1 class="ht-title">Financial Transactions</h1>
                <p class="ht-subtitle">Payment history &amp; receipt management</p>
            </div>
        </div>
        <div class="ht-header-right">
            <div class="ht-search-wrap">
                <i class="bi bi-search ht-search-icon"></i>
                <input type="text" id="searchInput" class="ht-search-input" placeholder="Search receipt, driver, plate...">
                <button class="ht-search-clear" id="clearSearch" title="Clear search">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
            <a href="<?= base_url('penalties') ?>" class="ht-btn-back">
                <i class="bi bi-arrow-left me-1"></i> Back to Pending
            </a>
        </div>
    </div>

    <!-- STATS STRIP -->
    <?php
        $totalCount = count($payments ?? []);
        $totalRevenue = 0;
        foreach (($payments ?? []) as $pp) {
            $totalRevenue += (float)($pp['total_penalty_sum'] ?? $pp['penalty_amount'] ?? 0);
        }
    ?>
    <div class="ht-stats-strip">
        <div class="ht-stat-card">
            <div class="ht-stat-icon ht-stat-blue"><i class="bi bi-receipt"></i></div>
            <div>
                <div class="ht-stat-value"><?= $totalCount ?></div>
                <div class="ht-stat-label">Total Transactions</div>
            </div>
        </div>
        <div class="ht-stat-card">
            <div class="ht-stat-icon ht-stat-green"><i class="bi bi-cash-coin"></i></div>
            <div>
                <div class="ht-stat-value">&#8369;<?= number_format($totalRevenue, 2) ?></div>
                <div class="ht-stat-label">Total Revenue</div>
            </div>
        </div>
        <div class="ht-stat-card">
            <div class="ht-stat-icon ht-stat-amber"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="ht-stat-value"><?= date('M d, Y') ?></div>
                <div class="ht-stat-label">As of Today</div>
            </div>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="ht-card">
        <div class="ht-card-inner">
            <div class="table-responsive">
                <table class="ht-table" id="paymentTable">
                    <thead>
                        <tr>
                            <th class="ht-th-index">#</th>
                            <th>Date Paid</th>
                            <th>Receipt #</th>
                            <th>Driver / Vehicle</th>
                            <th>Violations</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Total Paid</th>
                            <th class="ht-th-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($payments)): ?>
                            <tr id="noDataRow">
                                <td colspan="9" class="ht-empty-state">
                                    <div class="ht-empty-icon"><i class="bi bi-inbox"></i></div>
                                    <div class="ht-empty-text">No payment history found</div>
                                    <div class="ht-empty-sub">Payments will appear here once recorded</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                                $currentPage = $pager->getCurrentPage('history');
                                $perPage = 10;
                                $i = ($currentPage - 1) * $perPage + 1;
                            ?>
                            <?php foreach ($payments as $p): ?>
                            <?php
                                $fullName  = trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? ''));
                                $dName     = !empty($fullName) ? $fullName : ($p['driver_name'] ?? 'Unknown');
                                $plate     = trim($p['license_plate'] ?? '');
                                $skipPlate = in_array(strtolower($plate), ['no', 'none', 'n/a', '-', '']);
                                $dl        = trim($p['license_number'] ?? '');
                                $skipDl    = in_array(strtolower($dl), ['no', 'none', 'n/a', '-', '']);
                                $totalAmt  = (float)($p['total_penalty_sum'] ?? $p['penalty_amount'] ?? 0);
                                $rawTypes  = !empty($p['concatenated_violations']) ? $p['concatenated_violations'] : ($p['violation_type'] ?? '-');
                                $types     = explode('||', $rawTypes);
                            ?>
                            <tr class="ht-row" data-search="<?= strtolower(esc($dName . ' ' . $plate . ' ' . ($p['receipt_number'] ?? ''))) ?>">
                                <td class="ht-td-index" data-label="#">
                                    <span class="ht-row-num"><?= $i++ ?></span>
                                </td>
                                <td data-label="Date Paid">
                                    <div class="ht-date-cell">
                                        <div class="ht-date-dot"></div>
                                        <div>
                                            <div class="ht-date-main"><?= date('M d, Y', strtotime($p['paid_date'])) ?></div>
                                            <div class="ht-date-time"><?= date('h:i A', strtotime($p['paid_date'])) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td data-label="Receipt #">
                                    <span class="ht-receipt-pill" title="Click to copy" onclick="copyReceipt('<?= esc($p['receipt_number'] ?: '') ?>', this)">
                                        <i class="bi bi-receipt"></i>
                                        <span class="ht-receipt-num"><?= esc($p['receipt_number'] ?: 'N/A') ?></span>
                                        <i class="bi bi-copy ht-copy-ico"></i>
                                    </span>
                                </td>
                                <td data-label="Driver">
                                    <div class="ht-driver-name"><?= esc($dName) ?></div>
                                    <div class="ht-pills-row">
                                        <?php if (!$skipPlate): ?>
                                            <span class="ht-plate-pill"><i class="bi bi-car-front-fill"></i> <?= esc($plate) ?></span>
                                        <?php endif; ?>
                                        <?php if (!$skipDl): ?>
                                            <span class="ht-dl-pill"><i class="bi bi-person-badge"></i> <?= esc($dl) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td data-label="Violations">
                                    <div class="ht-violations">
                                        <?php foreach ($types as $t): ?>
                                            <?php $vName = explode('::', $t)[0]; ?>
                                            <div class="ht-vchip"><span class="ht-vdot"></span><?= esc(trim($vName)) ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td data-label="Amount">
                                    <div class="ht-amounts">
                                        <?php foreach ($types as $t): ?>
                                            <?php
                                                $parts = explode('::', $t);
                                                $vAmt  = isset($parts[1]) && $parts[1] !== '' ? (float)$parts[1] : null;
                                            ?>
                                            <div class="ht-amount-line"><?= $vAmt !== null ? '&#8369;' . number_format($vAmt, 2) : '&#8212;' ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td data-label="Method">
                                    <span class="ht-method-pill">
                                        <i class="bi bi-cash-stack"></i>
                                        <?= esc($p['payment_method']) ?>
                                    </span>
                                </td>
                                <td data-label="Total Paid">
                                    <span class="ht-total-badge">&#8369;<?= number_format($totalAmt, 2) ?></span>
                                </td>
                                <td data-label="Actions" class="ht-td-actions">
                                    <div class="ht-action-group">
                                        <a href="<?= base_url('user/receipt/' . $p['ticket_id']) ?>"
                                           class="ht-action-btn ht-btn-view" title="View Receipt" target="_blank">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <button type="button" class="ht-action-btn ht-btn-reverse btn-reverse"
                                                data-id="<?= $p['id'] ?>" data-receipt="<?= $p['receipt_number'] ?>"
                                                title="Reverse Payment">
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="noResultsMsg" class="ht-no-results" style="display:none;">
            <i class="bi bi-search"></i>
            <div>No transactions match your search</div>
        </div>

        <div class="ht-card-footer">
            <?= $pager->links('history', 'bootstrap_pagination') ?>
        </div>
    </div>
</div>

<!-- REVERSE MODAL -->
<div class="modal fade" id="reverseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ht-modal-content">
            <div class="ht-modal-header">
                <div class="ht-modal-title-wrap">
                    <div class="ht-modal-icon"><i class="bi bi-arrow-counterclockwise"></i></div>
                    <h5 class="ht-modal-title">Reverse Payment</h5>
                </div>
                <button type="button" class="ht-modal-close" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="ht-modal-body">
                <p class="ht-modal-desc">
                    Are you sure you want to reverse payment for receipt
                    <strong id="receiptId" class="ht-modal-receipt"></strong>?
                </p>
                <div class="ht-modal-alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <span>This will set the violation status back to <strong>Pending</strong> and clear all payment data. This action cannot be undone.</span>
                </div>
            </div>
            <div class="ht-modal-footer">
                <button type="button" class="ht-modal-cancel" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmReverseBtn" class="ht-modal-confirm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Confirm Reverse
                </a>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
.ht-page{font-family:'Inter',system-ui,sans-serif;padding:1.5rem 1.75rem 2rem;background:#f8fafc;min-height:100vh;}
.ht-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;}
.ht-header-left{display:flex;align-items:center;gap:.85rem;}
.ht-header-icon{width:46px;height:46px;background:linear-gradient(135deg,#2563eb,#1d4ed8);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.25rem;box-shadow:0 4px 12px rgba(37,99,235,.3);flex-shrink:0;}
.ht-title{font-size:1.3rem;font-weight:800;color:#0f172a;margin:0;letter-spacing:-.02em;}
.ht-subtitle{font-size:.8rem;color:#64748b;margin:0;font-weight:500;}
.ht-header-right{display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;}
.ht-search-wrap{position:relative;display:flex;align-items:center;}
.ht-search-icon{position:absolute;left:.9rem;color:#94a3b8;font-size:.88rem;pointer-events:none;}
.ht-search-input{padding:.55rem 2.4rem .55rem 2.25rem;border:1.5px solid #e2e8f0;border-radius:10px;background:#fff;font-size:.87rem;color:#0f172a;font-family:inherit;width:280px;transition:all .2s ease;outline:none;}
.ht-search-input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,.1);}
.ht-search-input::placeholder{color:#94a3b8;}
.ht-search-clear{position:absolute;right:.6rem;background:none;border:none;color:#94a3b8;cursor:pointer;font-size:.9rem;padding:0;display:none;transition:color .15s;}
.ht-search-clear:hover{color:#475569;}
.ht-search-clear.visible{display:block;}
.ht-btn-back{display:inline-flex;align-items:center;background:#0f172a;color:#fff;border:none;border-radius:10px;padding:.55rem 1.1rem;font-size:.87rem;font-weight:600;font-family:inherit;text-decoration:none;white-space:nowrap;transition:all .2s cubic-bezier(.16,1,.3,1);box-shadow:0 2px 8px rgba(15,23,42,.2);}
.ht-btn-back:hover{background:#1e293b;color:#fff;transform:translateY(-1px);box-shadow:0 4px 14px rgba(15,23,42,.25);}
.ht-stats-strip{display:flex;gap:1rem;margin-bottom:1.25rem;flex-wrap:wrap;}
.ht-stat-card{flex:1;min-width:180px;background:#fff;border:1px solid #e9eef5;border-radius:14px;padding:1rem 1.25rem;display:flex;align-items:center;gap:.85rem;box-shadow:0 1px 4px rgba(15,23,42,.04);transition:all .2s ease;}
.ht-stat-card:hover{border-color:#c7d7f7;box-shadow:0 4px 12px rgba(15,23,42,.07);transform:translateY(-1px);}
.ht-stat-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ht-stat-blue{background:#eff6ff;color:#2563eb;}.ht-stat-green{background:#f0fdf4;color:#16a34a;}.ht-stat-amber{background:#fffbeb;color:#d97706;}
.ht-stat-value{font-size:1.05rem;font-weight:800;color:#0f172a;letter-spacing:-.02em;line-height:1.2;}
.ht-stat-label{font-size:.73rem;color:#64748b;font-weight:500;text-transform:uppercase;letter-spacing:.04em;}
.ht-card{background:#fff;border:1px solid #e9eef5;border-radius:16px;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.05);}
.ht-card-inner{overflow-x:auto;}
.ht-table{width:100%;min-width:1100px;border-collapse:collapse;font-family:'Inter',sans-serif;table-layout:auto;}
.ht-table thead tr{background:#f8fafc;border-bottom:2px solid #edf2f7;}
.ht-table thead th{padding:.85rem .75rem;font-size:.72rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;border:none;}
.ht-th-index{padding-left:1.25rem !important;width:44px;}.ht-th-actions{text-align:right;padding-right:1.25rem !important;}
.ht-row{background:#fff;transition:all .2s cubic-bezier(.16,1,.3,1);border-bottom:1px solid #f1f5f9;}
.ht-row:last-child{border-bottom:none;}
.ht-row:hover{background:#fafbfc;transform:translateY(-1px);box-shadow:0 3px 10px rgba(15,23,42,.05);position:relative;z-index:1;}
.ht-row:hover .ht-total-badge{transform:scale(1.04);}
.ht-table tbody td{padding:.75rem .75rem;vertical-align:middle;font-size:.87rem;color:#1e293b;border:none;}
.ht-td-index{padding-left:1.25rem !important;}.ht-td-actions{text-align:right;padding-right:1.25rem !important;}
.ht-row-num{width:24px;height:24px;background:#f1f5f9;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:.75rem;font-weight:700;color:#64748b;}
.ht-date-cell{display:flex;align-items:flex-start;gap:.45rem;}
.ht-date-dot{width:7px;height:7px;background:#2563eb;border-radius:50%;margin-top:5px;flex-shrink:0;}
.ht-date-main{font-size:.85rem;font-weight:600;color:#0f172a;white-space:nowrap;}
.ht-date-time{font-size:.72rem;color:#64748b;font-weight:500;}
.ht-receipt-pill{display:inline-flex;align-items:center;gap:.3rem;background:#f0fdf4;border:1px solid #86efac;color:#15803d;border-radius:7px;padding:.22rem .55rem;font-size:.78rem;font-weight:700;letter-spacing:.02em;font-family:'ui-monospace',monospace;white-space:nowrap;cursor:pointer;user-select:none;transition:all .2s cubic-bezier(.16,1,.3,1);}
.ht-receipt-pill:hover{background:#dcfce7;border-color:#4ade80;transform:translateY(-1px);box-shadow:0 3px 10px rgba(22,163,74,.18);}
.ht-receipt-pill.copied{background:#dcfce7;border-color:#22c55e;color:#166534;}
.ht-receipt-num{font-size:.78rem;}.ht-copy-ico{font-size:.68rem;opacity:.55;}
.ht-driver-name{font-size:.9rem;font-weight:700;color:#0f172a;letter-spacing:-.01em;line-height:1.3;white-space:nowrap;}
.ht-pills-row{display:flex;align-items:center;flex-wrap:wrap;gap:.25rem;margin-top:.25rem;}
.ht-plate-pill{display:inline-flex;align-items:center;gap:.25rem;background:#ffffff !important;color:#0f172a !important;border:1.5px solid #cbd5e1 !important;border-radius:6px;padding:.15rem .45rem;font-size:.72rem;font-weight:700;letter-spacing:.04em;font-family:'ui-monospace',monospace;white-space:nowrap;box-shadow:0 1px 2px rgba(15,23,42,.05);transition:all .2s ease;}
.ht-plate-pill i{color:#2563eb !important;}
.ht-plate-pill:hover{background:#f1f5f9 !important;border-color:#94a3b8 !important;}
.ht-dl-pill{display:inline-flex;align-items:center;gap:.25rem;background:#f8fafc;color:#0f172a;border:1.5px solid #cbd5e1;border-radius:6px;padding:.15rem .45rem;font-size:.72rem;font-weight:600;letter-spacing:.02em;white-space:nowrap;}
.ht-dl-pill i{color:#64748b;}
.ht-violations{display:flex;flex-direction:column;gap:.2rem;}
.ht-vchip{display:inline-flex;align-items:center;font-size:.8rem;color:#1e293b;font-weight:500;line-height:1.35;gap:.3rem;}
.ht-vdot{width:5px;height:5px;background:#ef4444;border-radius:50%;flex-shrink:0;}
.ht-amounts{display:flex;flex-direction:column;gap:.2rem;}
.ht-amount-line{font-size:.82rem;font-weight:600;color:#1e293b;font-family:'ui-monospace',monospace;white-space:nowrap;}
.ht-method-pill{display:inline-flex;align-items:center;gap:.3rem;background:#f8fafc;border:1px solid #e2e8f0;color:#1e293b;border-radius:7px;padding:.22rem .55rem;font-size:.78rem;font-weight:600;white-space:nowrap;}
.ht-method-pill i{color:#16a34a;}
.ht-total-badge{display:inline-block;background:#f0fdf4;border:1.5px solid #86efac;color:#15803d;border-radius:20px;padding:.22rem .65rem;font-size:.85rem;font-weight:800;font-family:'ui-monospace',monospace;white-space:nowrap;transition:transform .2s ease;}
.ht-action-group{display:flex;gap:.4rem;justify-content:flex-end;align-items:center;}
.ht-action-btn{width:34px;height:34px;border-radius:9px;display:inline-flex;align-items:center;justify-content:center;font-size:.88rem;border:1.5px solid;cursor:pointer;background:#fff;transition:all .2s cubic-bezier(.16,1,.3,1);text-decoration:none;}
.ht-action-btn:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(15,23,42,.12);}
.ht-btn-view{color:#0369a1;border-color:#bae6fd;}.ht-btn-view:hover{background:#f0f9ff;border-color:#38bdf8;color:#0284c7;}
.ht-btn-reverse{color:#dc2626;border-color:#fca5a5;}.ht-btn-reverse:hover{background:#fef2f2;border-color:#f87171;color:#b91c1c;}
.ht-empty-state{text-align:center;padding:4rem 1rem;}
.ht-empty-icon{font-size:2.5rem;color:#cbd5e1;margin-bottom:.75rem;}
.ht-empty-text{font-size:1rem;font-weight:700;color:#475569;}
.ht-empty-sub{font-size:.83rem;color:#94a3b8;margin-top:.25rem;}
.ht-no-results{text-align:center;padding:3rem 1rem;color:#94a3b8;font-size:1.5rem;display:flex;flex-direction:column;align-items:center;gap:.5rem;}
.ht-no-results div{font-size:.92rem;font-weight:600;color:#64748b;}
.ht-card-footer{padding:.85rem 1.5rem;border-top:1px solid #f1f5f9;background:#fafbfc;}
.ht-modal-content{border:none;border-radius:16px;overflow:hidden;box-shadow:0 20px 60px rgba(15,23,42,.18);}
.ht-modal-header{display:flex;align-items:center;justify-content:space-between;padding:1.2rem 1.5rem;border-bottom:1px solid #f1f5f9;background:#fff;}
.ht-modal-title-wrap{display:flex;align-items:center;gap:.75rem;}
.ht-modal-icon{width:38px;height:38px;background:#fef2f2;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#dc2626;font-size:1.1rem;}
.ht-modal-title{margin:0;font-size:1rem;font-weight:700;color:#0f172a;}
.ht-modal-close{width:32px;height:32px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#475569;display:flex;align-items:center;justify-content:center;font-size:.85rem;cursor:pointer;transition:all .18s ease;}
.ht-modal-close:hover{background:#f8fafc;border-color:#cbd5e1;color:#0f172a;}
.ht-modal-body{padding:1.5rem;background:#fff;}
.ht-modal-desc{font-size:.92rem;color:#334155;margin-bottom:1rem;font-weight:500;}
.ht-modal-receipt{color:#2563eb;font-weight:800;}
.ht-modal-alert{display:flex;align-items:flex-start;gap:.65rem;background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:.85rem 1rem;font-size:.85rem;color:#7f1d1d;font-weight:500;}
.ht-modal-alert i{color:#dc2626;font-size:1rem;flex-shrink:0;margin-top:1px;}
.ht-modal-footer{display:flex;align-items:center;justify-content:flex-end;gap:.65rem;padding:1rem 1.5rem;border-top:1px solid #f1f5f9;background:#fafbfc;}
.ht-modal-cancel{padding:.55rem 1.25rem;border-radius:9px;border:1.5px solid #e2e8f0;background:#fff;color:#475569;font-size:.88rem;font-weight:600;font-family:inherit;cursor:pointer;transition:all .18s ease;}
.ht-modal-cancel:hover{border-color:#cbd5e1;background:#f8fafc;color:#0f172a;}
.ht-modal-confirm{display:inline-flex;align-items:center;padding:.55rem 1.25rem;border-radius:9px;background:#dc2626;color:#fff;font-size:.88rem;font-weight:700;font-family:inherit;text-decoration:none;transition:all .2s cubic-bezier(.16,1,.3,1);box-shadow:0 2px 8px rgba(220,38,38,.3);}
.ht-modal-confirm:hover{background:#b91c1c;color:#fff;transform:translateY(-1px);box-shadow:0 4px 14px rgba(220,38,38,.35);}
@media(max-width:767px){
.ht-page{padding:1rem;}
.ht-header{flex-direction:column;align-items:flex-start;}
.ht-header-right{width:100%;}
.ht-search-input{width:100%;}
.ht-search-wrap{width:100%;}
.ht-btn-back{width:100%;justify-content:center;}
.ht-stats-strip{flex-direction:column;}
.ht-stat-card{min-width:unset;}
.ht-table thead{display:none !important;}
.ht-table tbody tr{display:block !important;margin-bottom:1rem !important;border:1px solid #e2e8f0 !important;border-radius:14px !important;overflow:hidden !important;box-shadow:0 2px 8px rgba(15,23,42,.05) !important;background:#fff !important;transform:none !important;}
.ht-table tbody td{display:flex !important;justify-content:space-between !important;align-items:center !important;padding:.65rem 1rem !important;border-bottom:1px solid #f1f5f9 !important;font-size:.85rem !important;}
.ht-table tbody td:last-child{border-bottom:none !important;}
.ht-td-index{display:none !important;}
.ht-table tbody td::before{content:attr(data-label);font-weight:700;font-size:.71rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;flex-shrink:0;margin-right:.75rem;}
.ht-td-actions{justify-content:flex-end !important;}
.ht-td-actions::before{display:none !important;}
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
window.copyReceipt = function(receiptNum, el) {
    if (!receiptNum || receiptNum === 'N/A') return;
    navigator.clipboard.writeText(receiptNum).then(() => {
        el.classList.add('copied');
        const prev = el.innerHTML;
        el.innerHTML = '<i class="bi bi-check2-circle" style="color:#16a34a;"></i> <span class="ht-receipt-num">Copied!</span>';
        setTimeout(() => { el.innerHTML = prev; el.classList.remove('copied'); }, 1800);
    }).catch(() => {
        const ta = document.createElement('textarea');
        ta.value = receiptNum; document.body.appendChild(ta); ta.select();
        document.execCommand('copy'); document.body.removeChild(ta);
    });
};
const searchInput = document.getElementById('searchInput');
const clearBtn = document.getElementById('clearSearch');
const noResultsMsg = document.getElementById('noResultsMsg');
function filterTable(kw) {
    const keyword = kw.toLowerCase().trim();
    const rows = document.querySelectorAll('.ht-row');
    let hits = 0;
    rows.forEach(row => {
        const text = (row.dataset.search || row.textContent).toLowerCase();
        const show = !keyword || text.includes(keyword);
        row.style.display = show ? '' : 'none';
        if (show) hits++;
    });
    noResultsMsg.style.display = (keyword && hits === 0) ? 'flex' : 'none';
    clearBtn.classList.toggle('visible', keyword.length > 0);
}
if (searchInput) {
    searchInput.addEventListener('input', () => filterTable(searchInput.value));
    clearBtn.addEventListener('click', () => { searchInput.value = ''; filterTable(''); searchInput.focus(); });
}
const reverseModal = new bootstrap.Modal(document.getElementById('reverseModal'));
const receiptIdSpan = document.getElementById('receiptId');
const confirmReverseBtn = document.getElementById('confirmReverseBtn');
document.querySelectorAll('.btn-reverse').forEach(btn => {
    btn.addEventListener('click', function() {
        receiptIdSpan.textContent = this.dataset.receipt;
        confirmReverseBtn.href = `<?= base_url('penalties/reverse') ?>/${this.dataset.id}`;
        reverseModal.show();
    });
});
</script>
<?= $this->endSection() ?>
