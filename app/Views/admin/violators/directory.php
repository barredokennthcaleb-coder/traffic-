<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violator Reports<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4 vdr-page">

    <div class="vdr-head">
        <div class="d-flex align-items-center gap-3">
            <div class="vdr-icon"><i class="bi bi-person-vcard"></i></div>
            <div>
                <h5 class="mb-0 fw-bold">Violator Reports</h5>
                <p class="mb-0 vdr-sub">Open the apprehension and sanction sheet of one person on record.</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= base_url('reports') ?>" class="vdr-back">
                <i class="bi bi-arrow-left"></i><span>Reports</span>
            </a>
            <!-- Report Type: the same four periods the Reports module reads —
                 weekly, monthly, full year and a custom range — so any of
                 them can be asked for straight from the directory. -->
            <form method="get" action="<?= base_url('reports/violators/period') ?>" class="vdr-report" id="vdrReportForm">
                <?php if ($search !== ''): ?>
                    <input type="hidden" name="search" value="<?= esc($search) ?>">
                <?php endif; ?>

                <label for="period_type" class="vdr-report-label">Report Type</label>

                <select name="period_type" id="period_type" class="vdr-select" required
                        onchange="vdrTogglePeriod(this.value)">
                    <option value="" selected disabled>— Choose report type —</option>
                    <option value="weekly">Weekly Report</option>
                    <option value="monthly">Monthly Report</option>
                    <option value="yearly">Full Year Report</option>
                    <option value="custom">Custom Range</option>
                </select>

                <span class="vdr-period" data-period="weekly" style="display:none;">
                    <input type="date" name="start_date" class="vdr-date" aria-label="Week starting date"
                           value="<?= date('Y-m-d') ?>" title="Week starting date (the week runs Monday to Sunday)" disabled>
                </span>

                <span class="vdr-period" data-period="monthly" style="display:none;">
                    <select name="month" class="vdr-mini" aria-label="Month" disabled>
                        <?php for ($mNo = 1; $mNo <= 12; $mNo++): ?>
                            <option value="<?= $mNo ?>" <?= $mNo === (int) date('n') ? 'selected' : '' ?>><?= date('M', mktime(0, 0, 0, $mNo, 1)) ?></option>
                        <?php endfor; ?>
                    </select>
                    <select name="year" class="vdr-mini" aria-label="Year" disabled>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int) $y ?>" <?= (int) $y === (int) date('Y') ? 'selected' : '' ?>><?= (int) $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>

                <span class="vdr-period" data-period="yearly" style="display:none;">
                    <select name="year" class="vdr-mini" aria-label="Year" disabled>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= (int) $y ?>" <?= (int) $y === (int) date('Y') ? 'selected' : '' ?>><?= (int) $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </span>

                <span class="vdr-period" data-period="custom" style="display:none;">
                    <input type="date" name="start_date" class="vdr-date" aria-label="Range start" disabled>
                    <span class="vdr-dash">&ndash;</span>
                    <input type="date" name="end_date" class="vdr-date" aria-label="Range end" disabled>
                </span>

                <button type="submit" class="vdr-generate">
                    <i class="bi bi-file-earmark-bar-graph"></i><span>Generate</span>
                </button>
            </form>
            <form method="get" action="<?= base_url('reports/violators') ?>" class="vdr-search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" value="<?= esc($search) ?>"
                       placeholder="Search name, plate or ticket…" aria-label="Search violators">
                <button type="submit">Go</button>
            </form>
        </div>
    </div>

    <div class="vdr-card">
        <div class="vdr-card-head">
            <span><i class="bi bi-people me-1"></i> <?= number_format((int) $total) ?> person<?= (int) $total === 1 ? '' : 's' ?> on record</span>
            <?php if ($search !== ''): ?>
                <a href="<?= base_url('reports/violators') ?>" class="vdr-clear">
                    <i class="bi bi-x-circle"></i> Clear “<?= esc($search) ?>”
                </a>
            <?php endif; ?>
        </div>

        <div class="vdr-table-wrap">
            <table class="vdr-table">
                <thead>
                    <tr>
                        <th>Violator</th>
                        <th>Plate &amp; Vehicle</th>
                        <th class="text-center">Citations</th>
                        <th>Standing</th>
                        <th>Last Apprehension</th>
                        <th class="text-end">Report</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($violators)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="vdr-empty">
                                    <i class="bi bi-person-x"></i>
                                    <p>
                                        <?= $search !== ''
                                            ? 'Nothing matches “' . esc($search) . '”.'
                                            : 'No violators on record yet.' ?>
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($violators as $v): ?>
                            <?php
                                $name = \App\Libraries\ViolatorIdentity::shortName(
                                    $v['first_name'] ?? '',
                                    $v['middle_name'] ?? '',
                                    $v['last_name'] ?? '',
                                    $v['driver_name'] ?? ''
                                ) ?: 'Unknown Violator';

                                $plate   = trim((string) ($v['license_plate'] ?? ''));
                                $vehicle = trim((string) ($v['vehicle_type'] ?? ''));
                                $license = trim((string) ($v['license_number'] ?? ''));
                                $standing = (string) ($v['overall_status'] ?? 'Pending');
                                $tone     = $standing === 'Paid' ? 'paid' : ($standing === 'Cancelled' ? 'cancelled' : 'pending');
                                $reportUrl = base_url('violators/report/' . (int) $v['id']);
                            ?>
                            <tr>
                                <td data-label="Violator">
                                    <div class="vdr-name"><?= esc($name) ?></div>
                                    <div class="vdr-meta"><?= $license !== '' ? 'License ' . esc($license) : 'No license on file' ?></div>
                                </td>
                                <td data-label="Plate &amp; Vehicle">
                                    <div class="vdr-plate"><?= $plate !== '' ? esc(strtoupper($plate)) : 'No plate on file' ?></div>
                                    <?php if ($vehicle !== ''): ?>
                                        <div class="vdr-meta"><?= esc($vehicle) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Citations" class="text-center">
                                    <?= number_format((int) ($v['attempt_count'] ?? 0)) ?>
                                </td>
                                <td data-label="Standing">
                                    <span class="vdr-pill vdr-pill--<?= esc($tone) ?>">
                                        <?= $tone === 'paid' ? 'Settled' : ($tone === 'cancelled' ? 'Cancelled' : 'Pending') ?>
                                        <?php if ($tone === 'pending' && (float) ($v['balance'] ?? 0) > 0): ?>
                                            &middot; &#8369;<?= number_format((float) $v['balance'], 2) ?>
                                        <?php endif; ?>
                                    </span>
                                </td>
                                <td data-label="Last Apprehension" class="vdr-meta">
                                    <?php if (! empty($v['last_violation_at'])): ?>
                                        <?= esc(date('M d, Y g:i A', strtotime((string) $v['last_violation_at']))) ?>
                                    <?php else: ?>
                                        &mdash;
                                    <?php endif; ?>
                                </td>
                                <td data-label="Report" class="text-end">
                                    <a href="<?= esc($reportUrl) ?>" class="vdr-open">
                                        <i class="bi bi-file-earmark-bar-graph"></i><span>Open report</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (! empty($showPager) && ! empty($pagerLinks)): ?>
            <div class="vdr-pager"><?= $pagerLinks ?></div>
        <?php endif; ?>
    </div>
</div>

<style>
.vdr-page { max-width: 1180px; margin: 0 auto; }
.vdr-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 14px; flex-wrap: wrap; margin-bottom: 16px;
}
.vdr-icon {
    width: 44px; height: 44px; border-radius: 13px;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff; font-size: 1.2rem;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: 0 6px 16px rgba(37, 99, 235, .3);
}
.vdr-head h5 { color: var(--text-strong, #0f172a); }
.vdr-sub { font-size: .84rem; color: var(--text-muted, #64748b); }

.vdr-back {
    display: inline-flex; align-items: center; gap: 6px;
    padding: .5rem 1rem; border-radius: 10px;
    border: 1px solid var(--border-soft, #e2e8f0);
    background: var(--surface-card, #fff);
    color: var(--text-body, #334155);
    font-size: .85rem; font-weight: 700; text-decoration: none;
}
.vdr-back:hover { border-color: #93c5fd; color: #1d4ed8; }

.vdr-search {
    display: inline-flex; align-items: center; gap: 6px;
    border: 1px solid var(--border-soft, #e2e8f0);
    background: var(--surface-card, #fff);
    border-radius: 10px; padding: .3rem .35rem .3rem .7rem;
}
.vdr-search i { color: var(--text-subtle, #94a3b8); font-size: .85rem; }
.vdr-search input {
    border: 0; background: transparent; outline: none;
    width: 220px; font-size: .85rem; color: var(--text-body, #334155);
}
.vdr-search button {
    border: 0; border-radius: 7px; padding: .32rem .8rem;
    background: #1d4ed8; color: #fff; font-size: .78rem; font-weight: 700;
}

.vdr-card {
    background: var(--surface-card, #fff);
    border: 1px solid var(--border-soft, #e2e8f0);
    border-radius: 14px; overflow: hidden;
    box-shadow: 0 6px 20px rgba(15, 23, 42, .06);
}
.vdr-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 10px; flex-wrap: wrap;
    padding: 10px 16px; font-size: .78rem; font-weight: 700;
    color: var(--text-muted, #64748b);
    border-bottom: 1px solid var(--border-soft, #e2e8f0);
    background: var(--surface-muted, #f8fafc);
}
.vdr-clear { font-size: .76rem; font-weight: 700; color: #b91c1c; text-decoration: none; }

.vdr-table-wrap { overflow-x: auto; }
.vdr-table { width: 100%; border-collapse: collapse; }
.vdr-table th {
    text-align: left; font-size: .68rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .05em;
    color: var(--text-muted, #64748b);
    padding: 10px 16px; border-bottom: 1px solid var(--border-soft, #e2e8f0);
    white-space: nowrap;
}
.vdr-table td {
    padding: 11px 16px; font-size: .85rem;
    color: var(--text-body, #334155);
    border-bottom: 1px solid rgba(148, 163, 184, .18);
    vertical-align: middle;
}
.vdr-table tbody tr:hover { background: rgba(37, 99, 235, .04); }
.vdr-name { font-weight: 750; color: var(--text-strong, #0f172a); }
.vdr-plate { font-weight: 750; }
.vdr-meta { font-size: .76rem; color: var(--text-muted, #64748b); }

.vdr-pill {
    display: inline-block; padding: 2px 9px; border-radius: 999px;
    font-size: .68rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em;
}
.vdr-pill--paid { background: #dcfce7; color: #166534; }
.vdr-pill--pending { background: #fef3c7; color: #92400e; }
.vdr-pill--cancelled { background: #e2e8f0; color: #475569; }

.vdr-open {
    display: inline-flex; align-items: center; gap: 6px;
    padding: .38rem .85rem; border-radius: 9px;
    border: 1px solid #bfdbfe; background: #eff6ff;
    color: #1d4ed8; font-size: .78rem; font-weight: 750;
    text-decoration: none; white-space: nowrap;
}

.vdr-report {
    display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap;
    border: 1px solid var(--border-soft, #e2e8f0);
    background: var(--surface-card, #fff);
    border-radius: 10px; padding: .3rem .35rem .3rem .7rem;
}
.vdr-report-label {
    margin: 0; font-size: .68rem; font-weight: 800;
    letter-spacing: .05em; text-transform: uppercase;
    color: var(--text-muted, #64748b);
}
.vdr-select {
    border: 0; background: transparent; outline: none;
    font-size: .85rem; font-weight: 700; color: var(--text-body, #334155);
    max-width: 175px;
}
.vdr-select:invalid { color: var(--text-subtle, #94a3b8); font-weight: 600; }
.vdr-period { display: inline-flex; align-items: center; gap: 4px; }
.vdr-date, .vdr-mini {
    border: 1px solid var(--border-soft, #e2e8f0); border-radius: 7px;
    background: var(--surface-muted, #f8fafc);
    font-size: .8rem; color: var(--text-body, #334155);
    padding: .28rem .45rem;
}
.vdr-dash { color: var(--text-subtle, #94a3b8); }
.vdr-generate {
    display: inline-flex; align-items: center; gap: 6px;
    border: 0; border-radius: 7px; padding: .32rem .8rem;
    background: #1d4ed8; color: #fff; font-size: .78rem; font-weight: 700;
}
.vdr-generate:hover { background: #1e40af; }

.vdr-empty { padding: 38px 14px; text-align: center; color: var(--text-muted, #64748b); }
.vdr-empty i { display: block; font-size: 1.5rem; margin-bottom: 6px; color: #94a3b8; }
.vdr-empty p { margin: 0; font-size: .86rem; }

.vdr-pager { padding: 10px 14px; }

@media (max-width: 820px) {
    .vdr-table thead { display: none; }
    .vdr-table, .vdr-table tbody, .vdr-table tr, .vdr-table td { display: block; width: 100%; }
    .vdr-table tr {
        padding: 10px 14px;
        border-bottom: 1px solid rgba(148, 163, 184, .22);
    }
    .vdr-table td { border: 0; padding: 4px 0; }
    .vdr-table td[data-label]::before {
        content: attr(data-label);
        display: block;
        font-size: .62rem; font-weight: 800; letter-spacing: .05em;
        text-transform: uppercase; color: var(--text-muted, #64748b);
    }
    .vdr-table td.text-center, .vdr-table td.text-end { text-align: left; }
    .vdr-table td.text-center::before, .vdr-table td.text-end::before { display: none; }
    .vdr-search input { width: 150px; }

    .vdr-report { width: 100%; }
    .vdr-select { max-width: none; flex: 1; }
}
</style>

<script>
// One period box at a time: only the fields of the chosen report type stay
// enabled, so the GET that generates the report carries just that period.
function vdrTogglePeriod(type) {
    document.querySelectorAll('#vdrReportForm .vdr-period').forEach(function (box) {
        var active = box.getAttribute('data-period') === type;

        box.style.display = active ? 'inline-flex' : 'none';

        box.querySelectorAll('input, select').forEach(function (field) {
            field.disabled = !active;
        });
    });
}
</script>

<?= $this->endSection() ?>
