<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Violators - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
    // Keep the current search and enforcer filter when switching chips, and
    // keep the chosen status when searching.
    $chipUrl = static function (string $newStatus) use ($search, $enforcerId): string {
        $params = array_filter(
            ['status' => $newStatus, 'search' => $search, 'enforcer' => $enforcerId > 0 ? $enforcerId : ''],
            static fn ($v) => $v !== '' && $v !== 0 && $v !== null
        );

        return base_url('violators') . ($params === [] ? '' : '?' . http_build_query($params));
    };

?>

<div class="container-fluid py-4 vd-page">

    <!-- Header -->
    <div class="vd-header">
        <div class="vd-header-left">
            <div class="vd-header-icon"><i class="bi bi-person-vcard"></i></div>
            <div>
                <h1 class="vd-title">Violators</h1>
                <p class="vd-subtitle">Every person on record, with the enforcer who logged them</p>
            </div>
        </div>

        <div class="vd-header-right">
            <form method="GET" action="<?= base_url('violators') ?>" id="vdSearchForm" class="vd-search-form">
                <input type="hidden" name="status" value="<?= esc($status) ?>">
                <input type="hidden" name="enforcer" value="<?= $enforcerId > 0 ? (int) $enforcerId : '' ?>">
                <div class="vd-search-box">
                    <i class="bi bi-search vd-search-icon"></i>
                    <input type="text" id="vdSearchInput" name="search"
                           class="vd-search-input"
                           placeholder="Search name, plate or ticket…"
                           value="<?= esc($search) ?>"
                           autocomplete="off">
                    <?php if ($search !== ''): ?>
                        <a href="<?= esc($chipUrl($status)) ?>" class="vd-search-clear" title="Clear search">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="vd-filter-select-wrap">
                <i class="bi bi-person-badge"></i>
                <select id="vdEnforcerFilter" class="vd-select" aria-label="Filter by enforcer">
                    <option value="">All enforcers</option>
                    <?php foreach ($enforcers as $officer): ?>
                        <?php
                            $officerName = trim(($officer['firstname'] ?? '') . ' ' . ($officer['lastname'] ?? ''));
                            $officerName = $officerName !== '' ? $officerName : (string) $officer['username'];
                        ?>
                        <option value="<?= (int) $officer['id'] ?>" <?= $enforcerId === (int) $officer['id'] ? 'selected' : '' ?>>
                            <?= esc($officerName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Starts the citation here, in a popup, instead of sending the
                 admin to the officer ledger: the directory can still issue a
                 ticket even when the enforcer side is unavailable. -->
            <button type="button" class="vd-btn-new" data-bs-toggle="modal" data-bs-target="#adminRecordViolationModal" title="Record a new violation">
                <i class="bi bi-plus-lg"></i> New Violation
            </button>
        </div>
    </div>


    <!-- Directory card -->
    <div class="vd-card">

        <div class="vd-card-head">
            <div class="vd-chips">
                <a href="<?= esc($chipUrl('')) ?>" class="vd-chip <?= $status === '' ? 'active' : '' ?>">
                    All <span class="vd-chip-count"><?= (int) ($counts['all'] ?? 0) ?></span>
                </a>
                <a href="<?= esc($chipUrl('Pending')) ?>"
                   class="vd-chip vd-chip-pending <?= $status === 'Pending' ? 'active' : '' ?>"
                   title="Everyone with at least one unpaid ticket — even when they have already paid other tickets">
                    Pending <span class="vd-chip-count"><?= (int) ($counts['pending'] ?? 0) ?></span>
                </a>
                <a href="<?= esc($chipUrl('Paid')) ?>"
                   class="vd-chip vd-chip-paid <?= $status === 'Paid' ? 'active' : '' ?>"
                   title="Everyone with at least one settled ticket — even when an unpaid ticket remains">
                    Paid <span class="vd-chip-count"><?= (int) ($counts['paid'] ?? 0) ?></span>
                </a>
                <?php
                    // Cancelled violators are listed under "All" too, so without
                    // their own chip the three counts never added up to the total.
                    // The chip only appears when at least one exists.
                ?>
                <?php if ((int) ($counts['cancelled'] ?? 0) > 0): ?>
                    <a href="<?= esc($chipUrl('Cancelled')) ?>" class="vd-chip vd-chip-cancelled <?= $status === 'Cancelled' ? 'active' : '' ?>">
                        Cancelled <span class="vd-chip-count"><?= (int) $counts['cancelled'] ?></span>
                    </a>
                <?php endif; ?>
            </div>

        </div>

        <div class="vd-banner" id="vdBanner">
            <i class="bi bi-info-circle"></i>
            <span>
                One row per person. Tickets, fines and balances are summed from their linked
                citations, so a settled apprehension stays in the record instead of disappearing
                from it. Pending and Paid are sections, not a single standing: Pending lists
                everyone with at least one unpaid ticket even when they have already paid others,
                and Paid does the same for everyone with at least one settled ticket.
            </span>
            <button type="button" class="vd-banner-close" id="vdBannerClose"
                    aria-label="Hide this note" title="Hide this note">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="vd-table-wrap">
            <table class="vd-table">
                <thead>
                    <tr>
                        <!-- The body packs four groups into one cell, the way the
                             enforcer ledger does, so the header repeats that same
                             grid: each label sits over the column it names. -->
                        <th class="vd-col-violator">
                            <div class="vd-ledger vd-ledger-head">
                                <span>Violator</span>
                                <span>Plate &amp; Vehicle</span>
                                <span>Status &amp; Offense</span>
                                <span>Recorded</span>
                            </div>
                        </th>
                        <th class="text-end vd-col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($violators)): ?>
                        <tr>
                            <td colspan="2">
                                <div class="vd-empty">
                                    <div class="vd-empty-icon"><i class="bi bi-person-x"></i></div>
                                    <h6>No violators found</h6>
                                    <p>
                                        <?= $search !== ''
                                            ? 'Nothing matches “' . esc($search) . '”. Try another name, or clear the search.'
                                            : 'This filter has no records yet. Apprehensions recorded by enforcers appear here.' ?>
                                    </p>
                                    <?php if ($search !== '' || $enforcerId > 0): ?>
                                        <a href="<?= base_url('violators') ?>" class="vd-clear-btn">
                                            <i class="bi bi-arrow-counterclockwise"></i> Reset filters
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($violators as $v): ?>
                            <?php
                                $profileUrl = base_url('penalties/violator/' . (int) $v['id']) . '?from=violators';
                                $name       = \App\Libraries\ViolatorIdentity::shortName(
                                    $v['first_name'] ?? '',
                                    $v['middle_name'] ?? '',
                                    $v['last_name'] ?? '',
                                    $v['driver_name'] ?? ''
                                ) ?: 'Unknown Violator';

                                $license = trim((string) ($v['license_number'] ?? ''));
                                $plate   = trim((string) ($v['license_plate'] ?? ''));
                                $vehicle = trim((string) ($v['vehicle_type'] ?? ''));
                                $mtop    = trim((string) ($v['mtop_number'] ?? ''));
                                $isBlank = static fn (string $value): bool => in_array(strtolower($value), ['', '-', 'n/a', 'none', 'no'], true);

                                $officers    = $v['officers'] ?? [];
                                $statusValue = (string) ($v['overall_status'] ?? 'Pending');

                                // Money on the row reads the same way the enforcer
                                // ledger reads it: what is still owed, and what was
                                // already settled, as two separate amounts.
                                $pendingAmount = (float) ($v['balance'] ?? 0);
                                $paidAmount    = (float) ($v['total_paid'] ?? 0);
                                $hasPending    = $pendingAmount > 0;
                                $hasPaid       = $paidAmount > 0;

                                // One sanction per apprehension on the whole record — not
                                // on the slice a section shows — because the three-strike
                                // mark is a fact about the driver. The third sanction puts
                                // a warning sign on the row and turns its action buttons
                                // red, visible at rest instead of behind a hover state.
                                $sanctionCount   = (int) ($v['sanction_ticket_count'] ?? $v['attempt_count'] ?? 0);
                                $isThirdSanction = $sanctionCount >= 3;

                                // Newest offense first, then a count of the others on
                                // the record, so the row reads "Overloading +2 more".
                                $typeList = array_values(array_unique(array_filter(array_map(
                                    'trim',
                                    explode('||', (string) ($v['violation_type_list'] ?? ''))
                                ), static fn ($type) => $type !== '')));
                                $topOffense   = $typeList[0] ?? '';
                                $moreOffenses = max(0, count($typeList) - 1);

                                $nameWords = array_values(array_filter(preg_split('/\s+/', $name) ?: []));
                                if ($nameWords === []) {
                                    $initials = 'VS';
                                } elseif (count($nameWords) === 1) {
                                    $initials = mb_strtoupper(mb_substr($nameWords[0], 0, 2));
                                } else {
                                    $initials = mb_strtoupper(
                                        mb_substr($nameWords[0], 0, 1)
                                        . mb_substr($nameWords[count($nameWords) - 1], 0, 1)
                                    );
                                }
                            ?>
                            <tr class="vd-row <?= $isThirdSanction ? 'vd-row--alert' : '' ?>"
                                onclick="window.location='<?= $profileUrl ?>'"
                                tabindex="0"
                                role="link"
                                title="Open the record of <?= esc($name) ?>"
                                aria-label="Open the record of <?= esc($name) ?>">
                                <td data-label="Violator" class="vd-cell-violator">
                                    <!-- Same three groups as the enforcer ledger: who the
                                         person is, what they drive, and where the record
                                         stands. -->
                                    <div class="vd-ledger">

                                        <div class="vd-identity">
                                            <span class="vd-avatar" aria-hidden="true"><?= esc($initials) ?></span>
                                            <div class="vd-id-text">
                                                <div class="vd-name">
                                                    <span class="vd-name-text"><?= esc($name) ?></span>
                                                    <?php if ($isThirdSanction): ?>
                                                        <?php /* Three strikes: the third sanction can take the license, so the warning sign sits in the row itself instead of behind a hover. */ ?>
                                                        <span class="vd-sanction-warn" tabindex="0"
                                                              title="<?= esc(sanction_label($sanctionCount), 'attr') ?> on record — one sanction per apprehension; the three-strike rule can revoke the driver's license.">
                                                            <span class="vd-sanction-icon" aria-hidden="true">!</span><?= esc(sanction_label($sanctionCount)) ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (! $isBlank($license)): ?>
                                                    <div class="vd-license">License <?= esc($license) ?></div>
                                                <?php else: ?>
                                                    <div class="vd-license">No license on file</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="vd-vehicle-info">
                                            <span class="vd-plate<?= $isBlank($plate) ? ' vd-plate--none' : '' ?>">
                                                <?= tricycle_icon() ?><?= esc($isBlank($plate) ? 'No plate' : strtoupper($plate)) ?>
                                            </span>
                                            <?php if (! $isBlank($vehicle) || ! $isBlank($mtop)): ?>
                                                <div class="vd-vehicle-meta">
                                                    <?php if (! $isBlank($vehicle)): ?>
                                                        <span class="vd-vehicle-name"><?= esc($vehicle) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (! $isBlank($mtop)): ?>
                                                        <span class="vd-mtop">MTOP <?= esc(strtoupper($mtop)) ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="vd-state">
                                            <!-- Each pill opens the directory filtered to
                                                 that state, so "Pending" here shows
                                                 everyone still owing, not just this
                                                 person. The click is stopped at the
                                                 pill: the row itself opens this
                                                 violator's record. -->
                                            <div class="vd-state-chips">
                                                <?php if ($statusValue === 'Cancelled'): ?>
                                                    <a href="<?= esc($chipUrl('Cancelled')) ?>"
                                                       class="vd-state-chip vd-state-chip--cancelled"
                                                       onclick="event.stopPropagation();"
                                                       title="Show every record whose tickets were all cancelled">
                                                        <i class="bi bi-slash-circle"></i>Cancelled
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($hasPending): ?>
                                                    <a href="<?= esc($chipUrl('Pending')) ?>"
                                                       class="vd-state-chip vd-state-chip--pending"
                                                       onclick="event.stopPropagation();"
                                                       title="Show everyone who still owes a fine">
                                                        <i class="bi bi-hourglass-split"></i>Pending &#8369;<?= number_format($pendingAmount, 2) ?>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($hasPaid): ?>
                                                    <a href="<?= esc($chipUrl('Paid')) ?>"
                                                       class="vd-state-chip vd-state-chip--paid"
                                                       onclick="event.stopPropagation();"
                                                       title="Show everyone settled at the cashier">
                                                        <i class="bi bi-check-circle-fill"></i>Paid &#8369;<?= number_format($paidAmount, 2) ?>
                                                    </a>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($topOffense !== ''): ?>
                                                <div class="vd-offense">
                                                    <i class="bi bi-exclamation-diamond"></i>
                                                    <span><?= esc($topOffense) ?></span>
                                                    <?php if ($moreOffenses > 0): ?>
                                                        <span class="vd-more">+<?= $moreOffenses ?> more</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- How many citations stand on this record. Who
                                                 recorded them has its own column. -->
                                            <div class="vd-row-meta">
                                                <span title="Tickets on record">
                                                    <i class="bi bi-receipt"></i><?= (int) $v['attempt_count'] ?> <?= ((int) $v['attempt_count'] === 1) ? 'ticket' : 'tickets' ?>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- RECORDED: every enforcer who logged a citation
                                             on this person, named rather than counted. -->
                                        <div class="vd-recorded">
                                            <?php if ($officers !== []): ?>
                                                <div class="vd-recorded-by" title="Recorded by <?= esc(implode(', ', $officers)) ?>">
                                                    <i class="bi bi-person-badge"></i>
                                                    <span><?= esc(implode(', ', $officers)) ?></span>
                                                </div>
                                            <?php else: ?>
                                                <div class="vd-recorded-by vd-recorded-by--none">
                                                    <i class="bi bi-person-badge"></i>
                                                    <span>No enforcer on file</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (! empty($v['last_violation_at'])): ?>
                                                <?php $issuedTs = strtotime((string) $v['last_violation_at']); ?>
                                                <div class="vd-recorded-date">
                                                    <i class="bi bi-clock-history"></i><?= esc(date('M d, Y', $issuedTs)) ?>, <?= esc(date('g:i A', $issuedTs)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                </td>

                                <td data-label="Actions" class="text-end vd-actions-cell" onclick="event.stopPropagation();">
                                    <a href="<?= $profileUrl ?>"
                                       class="vd-btn-view <?= $isThirdSanction ? 'vd-btn-view--danger' : '' ?>"
                                       title="<?= $isThirdSanction
                                           ? 'View the record of ' . esc($name, 'attr') . ' — ' . esc(sanction_label($sanctionCount), 'attr') . ' on file'
                                           : 'View the record of ' . esc($name, 'attr') ?>">
                                        <i class="bi bi-eye"></i> View
                                        <?php if ($isThirdSanction): ?>
                                            <i class="bi bi-exclamation-triangle-fill vd-btn-warn-sign" aria-hidden="true"></i>
                                        <?php endif; ?>
                                    </a>
                                    <a href="<?= base_url('officer/violations?violator_id=' . (int) $v['id']) ?>"
                                       class="vd-btn-add <?= $isThirdSanction ? 'vd-btn-add--danger' : '' ?>"
                                       title="<?= $isThirdSanction
                                           ? esc($name, 'attr') . ' carries ' . esc(sanction_label($sanctionCount), 'attr') . ' — record the next apprehension carefully'
                                           : 'Record another violation for ' . esc($name, 'attr') ?>"
                                       aria-label="Record another violation for <?= esc($name) ?>">
                                        <i class="bi bi-plus-lg"></i>
                                        <?php if ($isThirdSanction): ?>
                                            <span class="vd-add-warn" aria-hidden="true"><i class="bi bi-exclamation-triangle-fill"></i></span>
                                        <?php endif; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (! empty($showPager) && ! empty($pagerLinks)): ?>
            <!-- Ten people per page, with the search, status and enforcer
                 filters carried on every page link. -->
            <div class="vd-pager">
                <?= $pagerLinks ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<style>
.vd-page { --vd-line: var(--border-soft, #e2e8f0); }

/* Header */
.vd-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin-bottom: 18px;
}
.vd-header-left { display: flex; align-items: center; gap: 14px; min-width: 0; }
.vd-header-icon {
    width: 48px; height: 48px; flex-shrink: 0;
    border-radius: 14px;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
    color: #fff; font-size: 1.35rem;
    box-shadow: 0 6px 18px rgba(37, 99, 235, 0.25);
}
.vd-title { font-size: 1.35rem; font-weight: 800; margin: 0; color: var(--text-strong); letter-spacing: -0.02em; }
.vd-subtitle { margin: 2px 0 0; font-size: 0.82rem; color: var(--text-subtle); }

.vd-header-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.vd-search-form { margin: 0; }
.vd-search-box { position: relative; display: flex; align-items: center; }
.vd-search-icon {
    position: absolute; left: 13px; font-size: 0.85rem;
    color: var(--text-subtle); pointer-events: none;
}
.vd-search-input {
    width: 290px; height: 40px;
    padding: 0 38px 0 36px;
    border-radius: 12px;
    border: 1px solid var(--vd-line);
    background: var(--surface-card);
    color: var(--text-strong);
    font-size: 0.86rem;
    transition: border-color .2s ease, box-shadow .2s ease;
}
.vd-search-input::placeholder { color: var(--text-subtle); opacity: .85; }
.vd-search-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, .16);
}
.vd-search-clear {
    position: absolute; right: 11px;
    color: var(--text-subtle); font-size: 0.95rem; line-height: 1;
}
.vd-search-clear:hover { color: #ef4444; }

/* Busy state while the debounced request is in flight: the rows, KPIs and
   the caret in the box all stay on screen, and the spinner says the search
   is working instead of the page going blank and coming back. */
.vd-search-box.is-searching::after {
    content: "";
    position: absolute; right: 12px; top: 50%; margin-top: -7px;
    width: 14px; height: 14px;
    border: 2px solid rgba(59, 130, 246, .25);
    border-top-color: #3b82f6;
    border-radius: 50%;
    animation: vd-search-spin .7s linear infinite;
    pointer-events: none;
}
.vd-search-box.is-searching .vd-search-clear { opacity: 0; }
@keyframes vd-search-spin { to { transform: rotate(360deg); } }

.vd-filter-select-wrap {
    position: relative;
    display: inline-flex; align-items: center;
    gap: 8px;
    height: 40px;
    padding: 0 12px;
    border-radius: 12px;
    border: 1px solid var(--vd-line);
    background: var(--surface-card);
    color: var(--text-subtle);
    font-size: 0.85rem;
}
.vd-select {
    border: 0;
    background: transparent;
    color: var(--text-strong);
    font-size: 0.85rem;
    font-weight: 600;
    max-width: 190px;
    padding-right: 4px;
    cursor: pointer;
}
.vd-select:focus { outline: none; }
.vd-filter-select-wrap:focus-within { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, .16); }
/* Header action: opens the blank citation form, matching the officer ledger. */
.vd-btn-new {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    height: 40px;
    padding: 0 16px;
    border-radius: 12px;
    background: #2563eb;
    border: 1px solid #1d4ed8;
    color: #fff;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
    transition: background-color .18s ease, box-shadow .18s ease, transform .18s ease;
}
.vd-btn-new:hover {
    background: #1d4ed8;
    color: #fff;
    box-shadow: 0 6px 16px rgba(37, 99, 235, .28);
    transform: translateY(-1px);
}
.vd-btn-new:active { transform: translateY(0); }
.vd-btn-new i { font-size: 0.9rem; }
[data-bs-theme="dark"] .vd-select option { background: #132f4c; color: #e3f2fd; }

/* Card */
.vd-card {
    border-radius: 18px;
    border: 1px solid var(--vd-line);
    background: var(--surface-card);
    box-shadow: 0 10px 34px rgba(15, 23, 42, .05);
    overflow: hidden;
}

.vd-card-head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap;
    padding: 14px 18px;
    border-bottom: 1px solid var(--vd-line);
}
.vd-chips { display: flex; flex-wrap: wrap; gap: 8px; }
/* Same build as the enforcer ledger's filter buttons: 20px radius, 600
   weight, a tinted count badge per status, and the small hover lift. The
   active fills follow this page's emerald palette. */
.vd-chip {
    display: inline-flex; align-items: center; gap: .35rem;
    padding: .35rem .85rem;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 0.82rem; font-weight: 600;
    text-decoration: none;
    transition: all .2s cubic-bezier(.16, 1, .3, 1);
    white-space: nowrap;
}
.vd-chip:hover {
    background: #f8fafc; border-color: #cbd5e1; color: #1e293b;
    transform: translateY(-1px);
}
.vd-chip-count {
    font-size: 0.76rem; font-weight: 700;
    min-width: 22px; height: 20px;
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0 6px; border-radius: 10px;
    background: #e2e8f0; color: #475569;
    transition: background .2s ease, color .2s ease;
}
/* Each badge wears the colour of the status it counts. Pending is red: the
   money is still owed, so the section reads as the alarm it is rather than
   as a warm "in progress" tint. */
.vd-chip-pending .vd-chip-count   { background: #fee2e2; color: #b91c1c; border: 1px solid #ef4444; }
.vd-chip-paid .vd-chip-count      { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.vd-chip-cancelled .vd-chip-count { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

.vd-chip-pending:hover   { border-color: #ef4444; color: #b91c1c; background: #fee2e2; }
.vd-chip-paid:hover      { border-color: #a7f3d0; color: #065f46; background: #f0fdf4; }
.vd-chip-cancelled:hover { border-color: #cbd5e1; color: #334155; background: #f8fafc; }

.vd-chip.active { color: #fff; border-color: transparent; }
.vd-chip.active .vd-chip-count {
    background: rgba(255, 255, 255, .28) !important;
    color: #fff !important;
    border: none !important;
}
.vd-chip.active:not(.vd-chip-pending):not(.vd-chip-paid):not(.vd-chip-cancelled) {
    background: linear-gradient(135deg, #059669, #047857);
    box-shadow: 0 2px 10px rgba(5, 150, 105, .32);
}
.vd-chip-pending.active {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 2px 10px rgba(220, 38, 38, .35);
}
.vd-chip-paid.active {
    background: #10b981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, .35);
}
.vd-chip-cancelled.active {
    background: #475569;
    box-shadow: 0 2px 10px rgba(71, 85, 105, .3);
}

.vd-banner {
    display: flex; align-items: flex-start; gap: 9px;
    padding: 9px 18px;
    font-size: 0.76rem;
    color: var(--text-subtle);
    background: var(--surface-muted);
    border-bottom: 1px solid var(--vd-line);
}
.vd-banner i { color: #2563eb; margin-top: 2px; }

/* Table */
.vd-table-wrap { overflow-x: auto; }
/* Pager under the last row, inside the card, so the pages it changes are
   obviously the table above it. */
.vd-pager { padding: 12px 14px; border-top: 1px solid var(--vd-line); background: var(--surface-card); }
.vd-table { width: 100%; border-collapse: collapse; margin: 0; }
.vd-table thead th {
    font-size: 0.68rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--text-subtle);
    padding: 11px 14px;
    background: var(--surface-muted);
    white-space: nowrap;
    border-bottom: 1px solid var(--vd-line);
}
.vd-table tbody td {
    padding: 12px 14px;
    font-size: 0.85rem;
    color: var(--text-strong);
    border-bottom: 1px solid var(--vd-line);
    vertical-align: middle;
}
.vd-table tbody tr:last-child td { border-bottom: 0; }
.vd-row { cursor: pointer; transition: background .18s ease; }
.vd-row:hover { background: var(--surface-muted); }
/* ── Ledger row ──────────────────────────────────────────────────────
   Organised the way the enforcer ledger organises it: three groups on one
   line — who the person is, what they drive, and where the record stands —
   with the actions on the right. */
.vd-cell-violator { min-width: 0; }
.vd-ledger {
    display: grid;
    /* The middle group is the widest because it carries the amounts and the
       offense, plus the ticket/enforcer/date line; the plate column only ever
       holds a pill and two short values. */
    grid-template-columns: minmax(0, 0.9fr) minmax(0, 0.62fr) minmax(0, 1.2fr) minmax(0, 0.72fr);
    align-items: center;
    gap: 18px;
}
/* The header shares the body's grid, so each label sits over the column it
   names instead of all three reading under one "Violator" heading. */
.vd-ledger-head > span {
    font-size: 0.68rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .06em;
    color: var(--text-subtle);
    white-space: normal;
}
.vd-identity { display: flex; align-items: center; gap: 11px; min-width: 0; }
.vd-avatar {
    width: 40px; height: 40px; flex-shrink: 0;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: #e0f2fe; color: #0066ff;
    font-size: 0.84rem; font-weight: 800; letter-spacing: .02em;
}
[data-bs-theme="dark"] .vd-avatar { background: #1e3a8a; color: #dbeafe; }
.vd-id-text { min-width: 0; }
.vd-name {
    display: flex; flex-wrap: wrap; align-items: center; gap: 6px;
    font-weight: 700; color: var(--text-strong); line-height: 1.25;
}
.vd-name-text { min-width: 0; overflow-wrap: anywhere; }

/* ── Third sanction: the visible warning sign ────────────────────────
   Three sanctions on record (one per apprehension) is the three-strike
   mark, so the sign stays visible beside the name instead of hiding until
   hover. Hovering or focusing it nudges the triangle and lifts the pill,
   so the warning answers the pointer on the way to the row's buttons. */
.vd-sanction-warn {
    display: inline-flex; align-items: center; gap: 5px;
    padding: .16rem .55rem .16rem .2rem;
    border-radius: 999px;
    background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;
    font-size: 0.68rem; font-weight: 800; letter-spacing: .03em;
    line-height: 1.3; white-space: nowrap; cursor: help;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, .08);
    transition: background-color .16s ease, border-color .16s ease, transform .16s ease, box-shadow .16s ease;
}
.vd-sanction-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 18px; height: 18px; border-radius: 50%;
    background: #dc2626; color: #ffffff;
    font-size: .68rem; font-weight: 900; line-height: 1;
    box-shadow: 0 0 0 2px rgba(220, 38, 38, .16);
}
.vd-sanction-warn:hover,
.vd-sanction-warn:focus-visible {
    background: #fee2e2; border-color: #fca5a5; color: #7f1d1d;
    transform: translateY(-1px) scale(1.03);
    box-shadow: 0 0 0 4px rgba(220, 38, 38, .12), 0 4px 12px rgba(220, 38, 38, .22);
    outline: 2px solid rgba(220, 38, 38, .35); outline-offset: 1px;
}
@keyframes vdWarnNudge {
    0%, 100% { transform: rotate(0deg); }
    25% { transform: rotate(-14deg); }
    60% { transform: rotate(12deg); }
}
/* The row itself carries a red spine, so a repeat offender reads as one at
   a glance even before the warning sign is parsed. */
.vd-row--alert .vd-cell-violator { box-shadow: inset 3px 0 0 #dc2626; }
.vd-license {
    margin-top: 2px;
    font-size: 0.74rem; color: var(--text-subtle);
    word-break: break-word;
}
.vd-vehicle-info {
    display: flex; flex-direction: column; align-items: flex-start;
    gap: 5px; min-width: 0;
}
/* Plate pill, matching the enforcer ledger so the same tricycle reads the
   same way on either screen. */
.vd-plate {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 7px;
    border-radius: 7px;
    background: #fef3c7; color: #92400e; border: 1px solid #fde68a;
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    font-size: 0.74rem; font-weight: 800; letter-spacing: .05em;
    line-height: 1.4;
    white-space: nowrap;
}
.vd-plate i, .vd-plate .tricycle-icon { font-size: 0.66rem; opacity: .7; }
.vd-plate--none {
    background: var(--surface-muted); color: var(--text-subtle);
    border-color: var(--vd-line);
    font-family: inherit; font-weight: 700; letter-spacing: 0;
}
[data-bs-theme="dark"] .vd-plate {
    background: #422006; color: #fcd34d; border-color: #78350f;
}
.vd-vehicle-meta {
    display: flex; flex-direction: column; gap: 1px;
    font-size: 0.74rem; line-height: 1.35; min-width: 0;
}
.vd-vehicle-name { color: var(--text-body); font-weight: 600; word-break: break-word; }
.vd-mtop {
    color: var(--text-subtle);
    font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    letter-spacing: .03em;
}

/* ── State: the money chips, then what the person was caught for ───── */
.vd-state {
    display: flex; flex-direction: column; align-items: flex-start;
    gap: 5px; min-width: 0;
}
.vd-state-chips { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
.vd-state-chip {
    display: inline-flex; align-items: center; gap: 5px;
    padding: .26rem .58rem;
    border-radius: 999px;
    font-size: 0.72rem; font-weight: 700; line-height: 1;
    white-space: nowrap;
    border: 1px solid transparent;
    font-variant-numeric: tabular-nums;
    text-decoration: none;
}
/* The pills are links to the matching filter, so they carry a pointer and a
   small lift on hover without changing how the row reads at rest. */
a.vd-state-chip {
    cursor: pointer;
    transition: box-shadow 0.2s var(--ease-premium), transform 0.2s var(--ease-premium);
}
a.vd-state-chip:hover {
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.14);
}
a.vd-state-chip:focus-visible {
    outline: 2px solid #0066ff;
    outline-offset: 2px;
}
@media (hover: none) {
    a.vd-state-chip:hover { transform: none; box-shadow: none; }
}
.vd-state-chip--pending   { background: #fee2e2; color: #b91c1c; border-color: #ef4444; }
.vd-state-chip--paid      { background: #ecfdf5; color: #065f46; border-color: #10b981; }
.vd-state-chip--cancelled { background: #f1f5f9; color: #475569; border-color: #cbd5e1; }
[data-bs-theme="dark"] .vd-state-chip--pending { background: rgba(220, 38, 38, .16); color: #fca5a5; border-color: rgba(248, 113, 113, .45); }
[data-bs-theme="dark"] .vd-state-chip--paid    { background: #052e2b; color: #6ee7b7; border-color: #047857; }
/* Dark theme: the pending chip and the third-sanction warning keep their red
   fills (white letters on solid red), so they never sink into the dark card. */
[data-bs-theme="dark"] .vd-chip-pending .vd-chip-count {
    background: rgba(220, 38, 38, .2); color: #fca5a5; border-color: rgba(248, 113, 113, .45);
}
[data-bs-theme="dark"] .vd-chip-pending:hover {
    background: rgba(220, 38, 38, .14); border-color: rgba(248, 113, 113, .45); color: #fca5a5;
}
[data-bs-theme="dark"] .vd-chip-pending.active {
    background: linear-gradient(135deg, #ef4444, #b91c1c); color: #ffffff;
}
[data-bs-theme="dark"] .vd-sanction-warn {
    background: rgba(220, 38, 38, .16); border-color: rgba(248, 113, 113, .45); color: #fecaca;
}
[data-bs-theme="dark"] .vd-sanction-warn:hover,
[data-bs-theme="dark"] .vd-sanction-warn:focus-visible {
    background: rgba(220, 38, 38, .24); border-color: rgba(248, 113, 113, .6); color: #fee2e2;
}
[data-bs-theme="dark"] .vd-btn-view--danger {
    background: rgba(220, 38, 38, .14); border-color: rgba(248, 113, 113, .45); color: #fca5a5;
}
[data-bs-theme="dark"] .vd-btn-view--danger:hover {
    background: rgba(220, 38, 38, .24); border-color: #ef4444; color: #fecaca;
}
[data-bs-theme="dark"] .vd-add-warn { background: #0f172a; border-color: #7f1d1d; }
.vd-offense {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.78rem; color: var(--text-body);
    min-width: 0;
}
.vd-offense i { color: #ca8a04; font-size: 0.74rem; }
.vd-more { color: var(--text-subtle); font-weight: 700; white-space: nowrap; }
/* Tickets, the enforcer who logged them and the date issued: the facts this
   registry has always shown, on one quiet line under the offense. */
.vd-row-meta {
    display: flex; flex-wrap: wrap; align-items: center;
    gap: 3px 10px;
    font-size: 0.7rem; color: var(--text-subtle);
}
/* Each fact stays whole: if the column is too narrow the line breaks between
   facts, never inside "Oct 27, 2026" or a person's name. */
.vd-row-meta span {
    display: inline-flex; align-items: center; gap: 4px;
    min-width: 0; white-space: nowrap;
}
.vd-row-meta i { font-size: 0.7rem; opacity: .85; flex-shrink: 0; }

/* Who recorded the citations: a name reads in full, wrapping rather than
   truncating, with the date of the latest one under it. */
.vd-recorded { font-size: 0.7rem; color: var(--text-subtle); min-width: 0; }
.vd-recorded-by {
    display: flex; align-items: flex-start; gap: 5px;
    font-weight: 600; color: var(--text-strong);
}
.vd-recorded-by i { font-size: 0.72rem; opacity: .85; margin-top: 2px; flex-shrink: 0; }
.vd-recorded-by span { min-width: 0; overflow-wrap: anywhere; }
.vd-recorded-by--none { font-weight: 500; color: var(--text-subtle); }
.vd-recorded-date {
    display: inline-flex; align-items: center; gap: 4px;
    margin-top: 3px; white-space: nowrap;
}
.vd-recorded-date i { font-size: 0.7rem; opacity: .85; }

.vd-actions-cell { white-space: nowrap; }
.vd-btn-view {
    display: inline-flex; align-items: center; gap: 5px;
    padding: .34rem .7rem;
    border-radius: 9px;
    border: 1px solid var(--vd-line);
    background: var(--surface-card);
    color: var(--text-body);
    font-size: 0.78rem; font-weight: 700;
    text-decoration: none;
    transition: all .2s ease;
}
.vd-btn-view:hover { border-color: #93c5fd; color: #1d4ed8; transform: translateY(-1px); }
.vd-btn-add {
    position: relative;
    display: inline-flex; align-items: center; justify-content: center;
    width: 30px; height: 30px;
    margin-left: 6px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #fff; font-size: 0.8rem;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(37, 99, 235, .28);
    transition: all .2s ease;
}
.vd-btn-add:hover { transform: translateY(-1px); color: #fff; box-shadow: 0 5px 14px rgba(37, 99, 235, .36); }

/* Third-sanction rows swap the action colours for the red the warning sign
   wears: the button is red at rest (visible without hovering) and the sign
   on it answers the pointer. */
.vd-btn-view--danger {
    border-color: #fca5a5;
    background: #fef2f2;
    color: #b91c1c;
}
.vd-btn-view--danger:hover {
    border-color: #ef4444; background: #fee2e2; color: #991b1b;
    box-shadow: 0 3px 10px rgba(220, 38, 38, .22);
}
.vd-btn-warn-sign { font-size: 0.68rem; margin-left: 2px; }
.vd-btn-view--danger:hover .vd-btn-warn-sign { animation: vdWarnNudge .5s ease; }
.vd-btn-add--danger {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    box-shadow: 0 2px 8px rgba(220, 38, 38, .32);
}
.vd-btn-add--danger:hover {
    color: #fff;
    box-shadow: 0 5px 14px rgba(220, 38, 38, .45);
}
/* The sign rides the + button's shoulder at rest, nudging on hover so the
   "record the next one carefully" warning is seen before the click. */
.vd-add-warn {
    position: absolute; top: -5px; right: -5px;
    width: 15px; height: 15px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: #ffffff; color: #dc2626; font-size: 0.58rem; line-height: 1;
    border: 1px solid #fca5a5;
    box-shadow: 0 1px 4px rgba(15, 23, 42, .18);
    transition: transform .16s ease, color .16s ease, border-color .16s ease;
}
.vd-btn-add--danger:hover .vd-add-warn,
.vd-btn-add--danger:focus-visible .vd-add-warn {
    color: #991b1b; border-color: #ef4444;
    transform: scale(1.18) rotate(-8deg);
}
.vd-btn-add--danger:focus-visible { outline: 2px solid rgba(220, 38, 38, .5); outline-offset: 2px; }
.vd-btn-view--danger:focus-visible { outline: 2px solid rgba(220, 38, 38, .5); outline-offset: 2px; }

.vd-empty { padding: 46px 18px; text-align: center; }
.vd-empty-icon {
    width: 56px; height: 56px; margin: 0 auto 12px;
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--surface-muted); color: #2563eb; font-size: 1.5rem;
}
.vd-empty h6 { font-weight: 800; color: var(--text-strong); margin-bottom: 4px; }
.vd-empty p { font-size: 0.82rem; color: var(--text-subtle); margin-bottom: 12px; }
.vd-clear-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: .4rem .9rem;
    border-radius: 999px;
    border: 1px solid var(--vd-line);
    background: var(--surface-card); color: var(--text-body);
    font-size: 0.8rem; font-weight: 700; text-decoration: none;
}
.vd-clear-btn:hover { color: #1d4ed8; border-color: #93c5fd; }


/* Tablet */
@media (max-width: 1100px) {
    .vd-search-input { width: 240px; }
}

/* Phone: one card per violator, no sideways scrolling */
@media (max-width: 860px) {
    .vd-header { flex-direction: column; align-items: stretch; }
    .vd-header-right { flex-direction: column; align-items: stretch; }
    .vd-search-box { width: 100%; }
    .vd-search-input { width: 100%; }
    .vd-filter-select-wrap { width: 100%; justify-content: space-between; }
    .vd-select { flex: 1; max-width: none; }

    .vd-card-head { padding: 12px 14px; }
    .vd-chips { width: 100%; }
    .vd-chip { flex: 1 1 calc(50% - 8px); justify-content: center; }
    .vd-banner { padding: 8px 14px; }

    .vd-table-wrap { overflow-x: visible; }
    .vd-table, .vd-table tbody, .vd-table tr, .vd-table td { display: block; width: 100%; }
    .vd-table thead { display: none; }
    .vd-table tbody tr {
        border-bottom: 1px solid var(--vd-line);
        padding: 6px 0;
    }
    .vd-table tbody tr:last-child { border-bottom: 0; }
    .vd-row:hover { background: transparent; }
    .vd-table tbody td {
        display: flex; align-items: center; justify-content: space-between;
        gap: 14px;
        padding: 7px 14px;
        border-bottom: 0;
        text-align: right;
        font-size: 0.84rem;
    }
    .vd-table tbody td::before {
        content: attr(data-label);
        flex-shrink: 0;
        font-size: 0.68rem; font-weight: 800;
        text-transform: uppercase; letter-spacing: .05em;
        color: var(--text-subtle);
        text-align: left;
    }
    .vd-table tbody td.vd-cell-violator {
        display: block;
        text-align: left;
        padding-bottom: 10px;
    }
    .vd-table tbody td.vd-cell-violator::before { display: none; }
    /* One card per violator: the three ledger groups stack instead of
       squeezing onto a single line on a phone, separated by a hairline so
       "who they are" and "what they drive" do not read as one paragraph. */
    .vd-ledger { grid-template-columns: minmax(0, 1fr); gap: 0; }
    .vd-ledger > * { padding: 8px 0; }
    .vd-ledger > *:first-child { padding-top: 0; }
    .vd-ledger > *:last-child { padding-bottom: 0; }
    .vd-ledger > * + * { border-top: 1px solid rgba(148, 163, 184, .22); }
    /* The name is the violator and the buttons are the actions; repeating that
       above every card only made each one taller. */
    .vd-table tbody td.vd-actions-cell::before { display: none; }
    .vd-avatar { width: 36px; height: 36px; font-size: 0.78rem; }
    .vd-name { font-size: 0.98rem; }
    .vd-row-meta { gap: 2px 10px; }
    .vd-table tbody td.vd-actions-cell { justify-content: flex-end; gap: 8px; }
    .vd-btn-view { padding: .4rem .9rem; }
    .vd-btn-add { width: 34px; height: 34px; }
}

/* ── Dismissible note ────────────────────────────────────────────────
   The explanation is worth reading once; after that it is a row of the
   page the reader never asked for again. */
.vd-banner-close {
    margin-left: auto;
    border: 0; background: transparent;
    color: var(--text-subtle);
    font-size: 0.72rem; line-height: 1;
    padding: 2px 4px; border-radius: 6px;
    cursor: pointer;
    transition: color .18s ease, background .18s ease;
}
.vd-banner-close:hover { color: #dc2626; background: rgba(220, 38, 38, .08); }
.vd-banner.is-hidden { display: none; }

/* ── Alignment + swap motion ──────────────────────────────────────── */
/* Digits keep a fixed width, so a column of money reads as a column. */
.vd-state-chip,
.vd-chip-count { font-variant-numeric: tabular-nums; }

/* While a debounced search is in flight the answer on screen stays put and
   fades back a little, then eases in when it is replaced. Nothing blanks. */
.vd-table-wrap { transition: opacity .18s ease; }
.vd-table-wrap.is-updating { opacity: .45; }
.vd-table-wrap.is-fresh { animation: vd-swap-in .24s ease both; }
@keyframes vd-swap-in {
    from { opacity: .55; transform: translateY(4px); }
    to   { opacity: 1; transform: none; }
}

/* Touch screens keep :hover after a tap, which left cards lifted and chips
   nudged. Only devices with a real pointer get the movement. */
@media (hover: none) {
    .vd-chip:hover,
    .vd-btn-view:hover,
    .vd-btn-add:hover,
    .vd-sanction-warn:hover { transform: none; }
    .vd-btn-add--danger:hover .vd-add-warn,
    .vd-btn-add--danger:focus-visible .vd-add-warn { transform: none; }
}

/* ── Phone refinements ────────────────────────────────────────────── */
@media (max-width: 860px) {
    /* iOS Safari zooms the whole page when a field below 16px takes focus,
       and never quite zooms back. Both controls sit at 16px here so tapping
       the search or the enforcer picker leaves the layout alone. */
    .vd-search-input,
    .vd-select { font-size: 16px; }
    .vd-search-input { height: 44px; padding-right: 40px; }
    .vd-filter-select-wrap { height: 44px; }

    /* Three chips used to wrap as two plus a lonely full-width one. One
       scrollable row keeps the whole filter set on a single line. */
    .vd-chips {
        flex-wrap: nowrap;
        overflow-x: auto;
        scrollbar-width: none;
        -ms-overflow-style: none;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 2px;
    }
    .vd-chips::-webkit-scrollbar { display: none; }
    .vd-chip { flex: 0 0 auto; }

    .vd-table tbody tr { padding: 4px 0; }
}

/* ── Reduced motion ───────────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .vd-chip,
    .vd-row,
    .vd-btn-view,
    .vd-btn-add,
    .vd-sanction-warn,
    .vd-add-warn,
    .vd-table-wrap { transition: none !important; }
    .vd-chip:hover,
    .vd-btn-view:hover,
    .vd-btn-add:hover,
    .vd-sanction-warn:hover { transform: none !important; }
    .vd-sanction-warn:hover i,
    .vd-sanction-warn:focus-visible i,
    .vd-btn-view--danger:hover .vd-btn-warn-sign,
    .vd-btn-add--danger:hover .vd-add-warn,
    .vd-btn-add--danger:focus-visible .vd-add-warn { animation: none !important; }
    .vd-table-wrap.is-fresh { animation: none; }
}

/* Citation popup launched from the directory header. */
.aiv-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.15rem 0.9rem;
}
.aiv-grid .form-check {
    margin: 0;
    padding: 0.32rem 0.5rem 0.32rem 2rem;
    border-radius: 8px;
    line-height: 1.3;
}
.aiv-grid .form-check:hover { background: #f1f5f9; }
[data-bs-theme="dark"] .aiv-grid .form-check:hover { background: #1e293b; }
.aiv-grid .aiv-fine { color: var(--text-subtle, #64748b); font-size: 0.76rem; white-space: nowrap; }
@media (max-width: 575.98px) {
    .aiv-grid { grid-template-columns: 1fr; }
}
</style>

<?= $this->include('officer/partials/tct_field_options') ?>

<!-- Record a citation without leaving the directory. Posts to the same
     officer/store endpoint the enforcer form uses (an admin session is
     redirected straight back here on success), so nothing is duplicated and
     no data path differs from a normal ticket. -->
<div class="modal fade" id="adminRecordViolationModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="adminRecordViolationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold" id="adminRecordViolationModalLabel">
                    <i class="bi bi-file-earmark-text me-2"></i>Record New Violation
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('officer/store') ?>" method="POST" id="adminCitationForm">
                <?= csrf_field() ?>
                <input type="hidden" name="submission_token" value="<?= esc($submissionToken) ?>">
                <input type="hidden" name="age" value="<?= esc(old('age', '25')) ?>">

                <div class="modal-body p-3">
                    <div class="row g-2 mb-2">
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1" for="aiv_first_name">First name</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_first_name" name="first_name" required value="<?= esc(old('first_name')) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1" for="aiv_middle_name">Middle</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_middle_name" name="middle_name" value="<?= esc(old('middle_name')) ?>">
                        </div>
                        <div class="col-8 col-md-4">
                            <label class="form-label small fw-bold mb-1" for="aiv_last_name">Last name</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_last_name" name="last_name" required value="<?= esc(old('last_name')) ?>">
                        </div>
                        <div class="col-4 col-md-2">
                            <label class="form-label small fw-bold mb-1" for="aiv_name_suffix">Suffix</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_name_suffix" name="name_suffix" maxlength="20" placeholder="Jr." value="<?= esc(old('name_suffix')) ?>">
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold mb-1" for="aiv_address">Address</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_address" name="address" required value="<?= esc(old('address')) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1" for="aiv_license_number">DL/Permit No.</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_license_number" name="license_number" value="<?= esc(old('license_number')) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1" for="aiv_license_plate">Plate#</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_license_plate" name="license_plate" value="<?= esc(old('license_plate')) ?>">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1" for="aiv_mtop_number">MTOP#</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_mtop_number" name="mtop_number" value="<?= esc(old('mtop_number')) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1" for="aiv_vehicle_type">Vehicle</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_vehicle_type" name="vehicle_type" list="vehicleTypeOptions" value="<?= esc(old('vehicle_type')) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1" for="aiv_owner_name">Owner</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_owner_name" name="owner_name" value="<?= esc(old('owner_name')) ?>">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label small fw-bold mb-1" for="aiv_owner_address">Owner address</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_owner_address" name="owner_address" value="<?= esc(old('owner_address')) ?>">
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between border-top pt-3 mb-2">
                        <span class="fw-bold small text-uppercase" style="letter-spacing: .04em;">Violations</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold">
                            Fine: &#8369;<span id="aivFineTotal">0.00</span>
                        </span>
                    </div>

                    <div class="aiv-grid">
                        <?php foreach ($violationTypes as $type): ?>
                            <label class="form-check">
                                <input class="form-check-input aiv-violation-check" type="checkbox"
                                       name="violation_type_id[]" value="<?= (int) $type['id'] ?>"
                                       data-amount="<?= esc((string) $type['fine_amount'], 'attr') ?>"
                                       <?= in_array((string) $type['id'], (array) old('violation_type_id', []), true) ? 'checked' : '' ?>>
                                <span>
                                    <?= esc($type['violation_name']) ?>
                                    <span class="aiv-fine">&#8369;<?= number_format((float) $type['fine_amount'], 2) ?></span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <div class="row g-2 mt-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold mb-1" for="aiv_location">Place</label>
                            <input type="text" class="form-control form-control-sm" id="aiv_location" name="location" list="locationOptions" placeholder="e.g. Ceres Terminal, Kabankalan City" value="<?= esc(old('location')) ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold mb-1" for="aiv_violation_datetime">Date &amp; time</label>
                            <input type="datetime-local" class="form-control form-control-sm" id="aiv_violation_datetime" name="violation_datetime" value="<?= esc(old('violation_datetime', date('Y-m-d\TH:i'))) ?>">
                        </div>
                    </div>

                    <div class="alert alert-danger py-2 px-3 mt-3 mb-0 d-none" id="aivError">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>Select at least one violation before recording the citation.
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-check2-circle me-1"></i>Record Citation
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    const form     = document.getElementById('vdSearchForm');
    const input    = document.getElementById('vdSearchInput');
    const enforcer = document.getElementById('vdEnforcerFilter');
    const box      = document.querySelector('.vd-search-box');
    let timer      = null;
    let inFlight   = null;

    // The swap replaces the table itself, so the wrap is looked up fresh each
    // time instead of being held in a variable that goes stale.
    const markBusy = function (busy) {
        document.querySelectorAll('.vd-table-wrap').forEach(function (el) {
            el.classList.toggle('is-updating', busy);
        });
    };

    const markFresh = function () {
        document.querySelectorAll('.vd-table-wrap').forEach(function (el) {
            el.classList.remove('is-fresh');
            void el.offsetWidth; // restart the animation on a replaced node
            el.classList.add('is-fresh');
        });
    };
    // Kept in the outer scope so the enforcer filter can reuse the same
    // in-place swap the search uses.
    let runSearch  = null;

    // Live search: one request per pause, not per keystroke, and the answer
    // is swapped into the page rather than reloaded, so the rows, the KPI
    // strip and the caret in the box stay visible while you type.
    if (form && input) {
        // Swap the result count, chips, and table rows without a page reload.
        const swapped = ['.vd-chips', '.vd-table-wrap', '.vd-pager'];
        // The pager only exists when there is more than one page, so a search
        // that narrows the answer to a single page drops the stale links.
        const dropPager = function (doc) {
            const now = document.querySelector('.vd-pager');
            if (now && !doc.querySelector('.vd-pager')) { now.remove(); }
        };

        runSearch = function () {
            if (inFlight) inFlight.abort();

            const url = new URL(form.action, window.location.href);

            new URLSearchParams(new FormData(form)).forEach(function (value, key) {
                if (value !== '') {
                    url.searchParams.set(key, value);
                } else {
                    url.searchParams.delete(key);
                }
            });

            const controller = new AbortController();
            inFlight         = controller;
            box && box.classList.add('is-searching');
            markBusy(true);

            fetch(url.toString(), { signal: controller.signal })
                .then(function (response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.text();
                })
                .then(function (html) {
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    let hits  = 0;

                    swapped.forEach(function (selector) {
                        const next = doc.querySelector(selector);
                        const now  = document.querySelector(selector);
                        if (next && now) { now.replaceWith(next); hits++; }
                    });

                    // The clear button only exists once there is a search to clear.
                    const nextClear = doc.querySelector('.vd-search-clear');
                    const nowClear  = document.querySelector('.vd-search-clear');
                    if (nextClear && !nowClear && box) { box.appendChild(nextClear); hits++; }
                    if (!nextClear && nowClear) nowClear.remove();
                    if (nextClear && nowClear) nowClear.setAttribute('href', nextClear.getAttribute('href'));

                    if (hits === 0) {
                        // Login page or an unexpected body: fall back to a real submit.
                        window.location.assign(url.toString());
                        return;
                    }

                    dropPager(doc);

                    markFresh();
                    history.pushState({ vdSearch: true }, '', url.toString());
                })
                .catch(function (error) {
                    if (error && error.name === 'AbortError') return;
                    form.requestSubmit();
                })
                .finally(function () {
                    if (inFlight === controller) {
                        inFlight = null;
                        box && box.classList.remove('is-searching');
                        markBusy(false);
                    }
                });
        };

        input.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(runSearch, 400);
        });

        input.addEventListener('keydown', function (event) {
            // Enter searches now instead of waiting out the debounce.
            if (event.key === 'Enter') clearTimeout(timer);
        });

        // Clearing is the same search with an empty term: keep the page in
        // place instead of bouncing through a reload.
        if (box) {
            box.addEventListener('click', function (event) {
                const clear = event.target.closest('.vd-search-clear');
                if (!clear) return;
                event.preventDefault();
                clearTimeout(timer);
                input.value = '';
                runSearch();
                input.focus();
            });
        }

        window.addEventListener('popstate', function () {
            window.location.reload();
        });
    }

    // Rows are clickable, so they answer the keyboard too: Enter or Space on a
    // focused row opens the same record the pointer opens.
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') return;

        const row = event.target.closest && event.target.closest('.vd-row');
        if (!row) return;

        // A status pill inside the row is its own link (it opens the matching
        // filter), so the keyboard has to reach it instead of the row.
        const control = event.target.closest && event.target.closest('a, button');
        if (control && control !== row) return;

        event.preventDefault();
        row.click();
    });

    // Dismissing the note keeps it dismissed, so it does not come back on
    // every search or visit.
    const banner      = document.getElementById('vdBanner');
    const bannerClose = document.getElementById('vdBannerClose');
    const noteKey     = 'vd_hide_note';

    if (banner && bannerClose) {
        try {
            if (window.localStorage.getItem(noteKey) === '1') {
                banner.classList.add('is-hidden');
            }
        } catch (error) { /* private mode: the note simply stays */ }

        bannerClose.addEventListener('click', function () {
            banner.classList.add('is-hidden');
            try { window.localStorage.setItem(noteKey, '1'); } catch (error) { /* ignore */ }
        });
    }

    // Status chips (All / Pending / Paid / Cancelled) reuse the search's in-place
    // swap: the chip only moves the status parameter, so the rows, counts and
    // pager update without a page reload and the search text is kept.
    document.addEventListener('click', function (event) {
        const chip = event.target.closest && event.target.closest('.vd-chips a.vd-chip');
        if (!chip) return;

        const target = new URL(chip.getAttribute('href'), window.location.href);
        const status = target.searchParams.get('status') || '';
        const hidden = form && form.querySelector('input[name="status"]');
        const isPlainClick = event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey;

        // Middle-click, modifier clicks and a page without the live machinery
        // keep the normal browser behaviour.
        if (!isPlainClick || !runSearch || !hidden) return;

        event.preventDefault();
        hidden.value = status;
        clearTimeout(timer);
        runSearch();
    });

    // The enforcer filter is part of the same query string the chips keep.
    // Picking an officer runs the same in-place swap as the search, so the
    // rows, KPIs and chips update on the spot instead of flashing a reload.
    if (enforcer) {
        enforcer.addEventListener('change', function () {
            const hidden = form && form.querySelector('input[name="enforcer"]');

            if (hidden) {
                hidden.value = this.value;
            }

            if (form && input) {
                clearTimeout(timer);
                runSearch();
                return;
            }

            const url = new URL(window.location.href);

            if (this.value) {
                url.searchParams.set('enforcer', this.value);
            } else {
                url.searchParams.delete('enforcer');
            }

            window.location.href = url.toString();
        });
    }
})();
</script>

<script>
// Citation popup: live fine total and an in-place check that at least one
// offense is ticked, so a rejected submission never looks like a dead button.
(function () {
    const modalEl = document.getElementById('adminRecordViolationModal');
    if (!modalEl) return;

    const form   = document.getElementById('adminCitationForm');
    const checks = Array.from(modalEl.querySelectorAll('.aiv-violation-check'));
    const total  = document.getElementById('aivFineTotal');
    const error  = document.getElementById('aivError');

    const recalc = function () {
        let sum = 0;
        checks.forEach(function (check) {
            if (check.checked) sum += parseFloat(check.dataset.amount || '0') || 0;
        });
        if (total) total.textContent = sum.toFixed(2);
        if (error && checks.some(function (c) { return c.checked; })) error.classList.add('d-none');
    };

    checks.forEach(function (check) { check.addEventListener('change', recalc); });
    recalc();

    if (form) {
        form.addEventListener('submit', function (event) {
            if (!checks.some(function (c) { return c.checked; })) {
                event.preventDefault();
                if (error) error.classList.remove('d-none');
                modalEl.querySelector('.modal-body').scrollTop = 0;
            }
        });
    }

    <?php if (session()->getFlashdata('errors') || session()->getFlashdata('error')): ?>
    // The server bounced the submission back: reopen the form with the values
    // the browser kept so the admin can fix the field that was rejected.
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
    <?php endif; ?>
})();
</script>
<?= $this->endSection() ?>
