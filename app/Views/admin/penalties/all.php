<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>All Violators - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    // Keep the current search when switching chips, and vice versa.
    $chipUrl = static function (string $status) use ($search): string {
        $params = array_filter(['status' => $status, 'search' => $search], static fn($v) => $v !== '');

        return base_url('penalties/all') . ($params === [] ? '' : '?' . http_build_query($params));
    };
    $activeCount  = (int) ($counts['active'] ?? 0);
    $pendingCount = (int) ($counts['pending'] ?? 0);
?>

<div class="container-fluid py-4 av-page">

    <div class="card border-0 shadow-sm av-card premium-reveal">

        <!-- Card header: title, status chips, search and the record action -->
        <div class="card-header py-3 px-4 av-header d-flex justify-content-between align-items-center gap-3 flex-wrap">

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="fw-bold text-dark me-2" style="font-size: 0.95rem;">
                    <i class="bi bi-person-vcard me-1 text-primary"></i>All Violators
                </span>
                <div class="av-divider mx-1 d-none d-md-block"></div>
                <a href="<?= esc($chipUrl('')) ?>"
                   class="btn btn-sm btn-filter-chip btn-filter-all <?= $status === '' ? 'active' : '' ?>">
                    All <span class="badge ms-1 rounded-pill"><?= $activeCount ?></span>
                </a>
                <a href="<?= esc($chipUrl('Pending')) ?>"
                   class="btn btn-sm btn-filter-chip btn-filter-pending <?= $status === 'Pending' ? 'active' : '' ?>">
                    Pending <span class="badge filter-badge-pending ms-1 rounded-pill"><?= $pendingCount ?></span>
                </a>
            </div>

            <div class="d-flex gap-2 flex-wrap align-items-center ms-auto">
                <form method="GET" action="<?= base_url('penalties/all') ?>" id="violatorSearchForm" class="m-0">
                    <input type="hidden" name="status" value="<?= esc($status) ?>">
                    <div class="modern-search-box position-relative">
                        <i class="bi bi-search search-icon"></i>
                        <input type="text" id="vlSearchInput" name="search"
                               class="form-control form-control-sm modern-search-input"
                               placeholder="Search violator name..."
                               value="<?= esc($search) ?>"
                               autocomplete="off">
                    </div>
                </form>
                <a href="<?= base_url('officer/violations') ?>"
                   class="btn btn-sm btn-new-violation rounded-pill px-3 shadow-sm d-inline-flex align-items-center">
                    <span class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle me-1" style="width: 18px; height: 18px; font-size: 11px; flex-shrink: 0;">
                        <i class="bi bi-plus-lg fw-bold" style="-webkit-text-stroke: 0.5px currentColor;"></i>
                    </span>
                    <span>New Violation</span>
                </a>
            </div>
        </div>

        <div class="px-4 py-2 border-top d-flex align-items-start gap-2 av-banner">
            <i class="bi bi-info-circle text-primary mt-1"></i>
            <span class="text-muted">
                One row per violator. A repeat apprehension is added to that person's history instead of
                creating a duplicate row. Settled tickets are kept in the administrator's
                <a href="<?= base_url('penalties/history') ?>" class="fw-semibold text-decoration-none">Payment History</a>.
            </span>
        </div>

        <!-- Table content -->
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 av-table" id="violatorTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4" style="min-width: 300px;">Violator</th>
                            <th class="text-end pe-4 av-actions-col" style="width: 210px; min-width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($violators)): ?>
                            <tr id="noDataRow">
                                <td colspan="2">
                                    <div class="av-empty py-5 text-center">
                                        <div class="av-empty-icon mb-3 mx-auto">
                                            <i class="bi bi-people fs-1 text-primary"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark">No violators found</h6>
                                        <p class="text-muted small mb-3">
                                            <?= $search !== ''
                                                ? 'No violator matches “' . esc($search) . '”. Try another name, or clear the search.'
                                                : 'Nobody has an outstanding citation. Settled tickets are kept in Payment History.' ?>
                                        </p>
                                        <?php if ($search !== ''): ?>
                                            <a href="<?= esc($chipUrl($status)) ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                                <i class="bi bi-x-lg me-1"></i> Clear Search
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= base_url('officer/violations') ?>" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                                <i class="bi bi-plus-circle me-1"></i> Record First Violation
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($violators as $v): ?>
                                <?php
                                    $profileUrl = base_url('penalties/violator/' . (int) $v['id']);
                                    // Ledger names stay compact: the middle name is an initial
                                    // here and complete on the violator's record.
                                    $name       = \App\Libraries\ViolatorIdentity::shortName(
                                        $v['first_name'] ?? '',
                                        $v['middle_name'] ?? '',
                                        $v['last_name'] ?? '',
                                        $v['driver_name'] ?? ''
                                    ) ?: 'Unknown Violator';
                                ?>
                                <tr class="av-row" onclick="window.location='<?= $profileUrl ?>'" title="Open violator history">
                                    <!-- One row per violator: the name is the whole record on this ledger -->
                                    <td class="ps-4" data-label="Violator">
                                        <div class="driver-name-text fw-semibold lh-sm"><?= esc($name) ?></div>
                                    </td>

                                    <td class="text-end pe-4 av-actions-col" data-label="Actions" onclick="event.stopPropagation();">
                                        <div class="d-inline-flex align-items-center gap-2 justify-content-end">
                                            <a href="<?= $profileUrl ?>"
                                               class="btn btn-sm btn-white border shadow-sm"
                                               title="View the records of <?= esc($name) ?>">
                                                <i class="bi bi-eye text-primary me-1"></i> View
                                            </a>
                                            <a href="<?= base_url('officer/violations?violator_id=' . (int) $v['id']) ?>"
                                               class="btn btn-sm btn-primary rounded-circle shadow-sm"
                                               style="width: 32px; height: 32px;"
                                               title="Record another violation for <?= esc($name) ?>">
                                                <i class="bi bi-plus-lg fw-bold"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if (! empty($pagerHtml)): ?>
            <div class="card-footer bg-white border-top-0 py-3 d-flex justify-content-center">
                <?= $pagerHtml ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.av-card {
    border-radius: 18px;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 8px 30px rgba(15, 23, 42, 0.04);
    overflow: hidden;
}
.av-header { background: #fff; border-bottom: 1px solid #f1f5f9; }
.av-divider { width: 1px; height: 20px; background: #e2e8f0; }
.av-banner { background: #f8fafc; font-size: .78rem; }

/* Status chips */
.btn-filter-chip {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    color: #475569;
    font-weight: 600;
    font-size: 0.82rem;
    border-radius: 20px;
    padding: 0.35rem 0.85rem;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    text-decoration: none;
}
.btn-filter-chip:hover { background: #f8fafc; border-color: #cbd5e1; color: #1e293b; transform: translateY(-1px); }
.btn-filter-all.active { background: #2563eb; border-color: #2563eb; color: #ffffff; box-shadow: 0 2px 8px rgba(37, 99, 235, 0.28); }
.btn-filter-all.active:hover { background: #1d4ed8; border-color: #1d4ed8; color: #ffffff; }
.btn-filter-all .badge { background: #e2e8f0; color: #475569; }
.btn-filter-chip .badge {
    font-size: 0.76rem;
    font-weight: 700;
    min-width: 22px;
    height: 20px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 6px;
    border-radius: 10px;
}
.btn-filter-chip.active .badge { background: rgba(255, 255, 255, 0.28) !important; color: #ffffff !important; border: none !important; }
.filter-badge-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.btn-filter-pending:hover { border-color: #fde68a; color: #92400e; background: #fffbeb; }
.btn-filter-pending.active { background: #f59e0b; border-color: #f59e0b; color: #ffffff; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.28); }

/* Record action */
.btn-new-violation {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    border: none;
    color: #ffffff;
    font-weight: 600;
    font-size: 0.85rem;
    padding: 0.45rem 1rem;
    border-radius: 999px;
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    text-decoration: none;
    white-space: nowrap;
}
.btn-new-violation:hover { background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%); color: #ffffff; transform: translateY(-1px); box-shadow: 0 5px 14px rgba(37, 99, 235, 0.35); }

/* Search */
.modern-search-box { min-width: 260px; }
.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.85rem;
    pointer-events: none;
}
.modern-search-input {
    padding-left: 34px !important;
    border-radius: 20px !important;
    border: 1px solid #cbd5e1 !important;
    background: #fff !important;
    font-size: 0.85rem !important;
    height: 36px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.modern-search-input:focus { border-color: #3b82f6 !important; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important; }

/* Ledger rows */
.av-table thead th {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-weight: 800;
    color: #475569;
    padding: 12px;
    white-space: nowrap;
}
.av-table .driver-name-text {
    color: #0f172a;
    font-weight: 700;
    font-size: 0.93rem;
    letter-spacing: -0.01em;
}
.av-table tbody tr.av-row { cursor: pointer; transition: background 0.18s ease; }
.av-table tbody tr.av-row:hover { background: #f8fafc; }
.av-table td { padding: 14px 12px; vertical-align: middle; color: #0f172a; }
.av-actions-col a { text-decoration: none; }

.av-empty-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #eff6ff;
    border: 1.5px solid #dbeafe;
    color: #2563eb;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .av-header { padding: 1rem !important; }
    .modern-search-box { min-width: 0; width: 100%; }
    .av-header form { flex: 1 1 100%; }
    .av-table td, .av-table th { padding: 12px; }
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const form  = document.getElementById('violatorSearchForm');
    const input = document.getElementById('vlSearchInput');
    let timer   = null;

    if (!form || !input) return;

    // Debounced submit keeps the live-search feel without breaking pagination.
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { form.requestSubmit(); }, 550);
    });

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') clearTimeout(timer);
    });
})();
</script>
<?= $this->endSection() ?>
