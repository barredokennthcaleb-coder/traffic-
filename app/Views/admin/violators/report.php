<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violator Apprehension Report<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="container-fluid py-4 vpr-page">

    <!-- ═══ Toolbar (screen only) ═══ -->
    <div class="vpr-toolbar no-print">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="<?= esc($back_url) ?>" class="vpr-back-btn">
                <i class="bi bi-arrow-left"></i><span>Violator Reports</span>
            </a>
            <span class="vpr-crumb">/</span>
            <span class="vpr-crumb-current"><?= esc($fullName) ?></span>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <!-- Month / day switch: the same record, grouped two ways -->
            <div class="btn-group btn-group-sm shadow-sm" role="group" aria-label="Report grouping">
                <a href="?<?= esc($queryBase(['mode' => 'monthly'])) ?>"
                   class="btn fw-semibold <?= $mode === 'monthly' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <i class="bi bi-calendar3 me-1"></i> Monthly
                </a>
                <a href="?<?= esc($queryBase(['mode' => 'daily'])) ?>"
                   class="btn fw-semibold <?= $mode === 'daily' ? 'btn-primary' : 'btn-outline-secondary' ?>">
                    <i class="bi bi-calendar-day me-1"></i> Daily
                </a>
            </div>

            <!-- Period pickers ride on the same page -->
            <form method="get" class="d-flex align-items-center gap-2 flex-wrap" id="periodForm">
                <input type="hidden" name="mode" value="<?= esc($mode) ?>">

                <select name="year" class="form-select form-select-sm vpr-select" aria-label="Year">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= (int) $y ?>" <?= (int) $y === (int) $year ? 'selected' : '' ?>><?= (int) $y ?></option>
                    <?php endforeach; ?>
                </select>

                <?php if ($mode === 'daily'): ?>
                    <select name="month" class="form-select form-select-sm vpr-select" aria-label="Month">
                        <?php foreach ($month_names as $mNo => $mName): ?>
                            <option value="<?= (int) $mNo ?>" <?= (int) $mNo === (int) $month ? 'selected' : '' ?>>
                                <?= esc($mName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>

                <button type="submit" class="btn btn-sm btn-outline-primary fw-semibold">
                    <i class="bi bi-funnel me-1"></i> Apply
                </button>
            </form>

            <a href="<?= base_url('reports/violators/year?year=' . (int) $year) ?>"
               class="btn btn-outline-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3"
               title="Full year of apprehensions for every violator, broken down by month">
                <i class="bi bi-calendar-range"></i> Full-Year Report
            </a>

            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3 shadow-sm">
                <i class="bi bi-printer-fill"></i> Print / Save PDF
            </button>
        </div>
    </div>

    <!-- ═══ Printable document ═══ -->
    <div class="report-document-card" id="violatorReport">
        <?= $this->include('admin/violators/_report_sheet') ?>
    </div>
</div>

<?= $this->include('admin/violators/_report_styles') ?>

<?= $this->endSection() ?>
