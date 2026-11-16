<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Traffic Enforcer Profile - Admin<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php 
    $enfImg = !empty($user['profile_image']) && is_file(FCPATH . 'uploads/profile/' . $user['profile_image'])
        ? base_url('uploads/profile/' . $user['profile_image'])
        : null;
    $enfInitials = strtoupper(substr((string) ($user['username'] ?? 'E'), 0, 1));
    $fullName = trim(($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '')) ?: $user['username'];
    
    // Duty status styling
    $duty = $user['duty_status'] ?? 'Off Duty';
    $dutyBadgeClass = match($duty) {
        'Active Patrol' => 'bg-success text-white',
        'On Break'      => 'bg-warning text-dark',
        default         => 'bg-secondary text-white'
    };
    $dutyDotClass = match($duty) {
        'Active Patrol' => 'bg-success',
        'On Break'      => 'bg-warning',
        default         => 'bg-secondary'
    };

    // Calculate collection rate
    $tickets = $tickets ?? [];
    $totalTickets = $totalTickets ?? count($tickets);
    $totalInfractions = $totalInfractions ?? count($records);

    $paidAmount = 0.0;
    foreach ($records as $r) {
        if (($r['status'] ?? '') === 'Paid') {
            $paidAmount += (float) ($r['penalty_amount'] ?? 0);
        }
    }
    $collectionRate = $totalAssessedAmount > 0 ? round(($paidAmount / $totalAssessedAmount) * 100, 1) : 0;
?>

<div class="container-fluid py-4 enforcer-profile-page">
    <!-- Header / Actions -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 no-print">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="<?= base_url('users?role=enforcer') ?>" class="text-decoration-none text-muted small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i>Enforcers
                </a>
                <span class="text-muted small">/</span>
                <span class="text-primary small fw-semibold">Profile</span>
            </div>
            <h4 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-circle p-2 d-inline-flex">
                    <i class="bi bi-shield-shaded fs-5"></i>
                </span>
                <span>Enforcer: <?= esc($user['username']) ?></span>
            </h4>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button onclick="window.print()" class="btn btn-outline-primary shadow-sm px-3">
                <i class="bi bi-printer me-1.5"></i> Print Profile
            </button>
            <a href="<?= base_url('users?role=enforcer') ?>" class="btn btn-secondary shadow-sm px-3">
                <i class="bi bi-arrow-left me-1.5"></i> Back to Enforcers
            </a>
        </div>
    </div>

    <!-- Main Grid: Profile Info (Left) + Performance Metrics (Right) -->
    <div class="row g-4 mb-4">
        <!-- Left: Profile Summary Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden enforcer-card enforcer-rail">
                <div class="card-header bg-white border-bottom border-light-subtle py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-person-badge text-primary"></i>
                        <span>Enforcer Profile</span>
                    </h6>
                    <?php if (($user['status'] ?? '') === 'active'): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small">Active</span>
                    <?php elseif (($user['status'] ?? '') === 'inactive'): ?>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill small">Inactive</span>
                    <?php else: ?>
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill small"><?= ucfirst(esc($user['status'] ?? 'unknown')) ?></span>
                    <?php endif; ?>
                </div>

                <div class="card-body p-4">
                    <!-- Avatar & Identity -->
                    <div class="text-center mb-4 pb-3 border-bottom border-light-subtle">
                        <div class="position-relative mx-auto mb-3" style="width: 100px; height: 100px;">
                            <div class="rounded-circle overflow-hidden border border-3 border-primary-subtle shadow-sm" style="width: 100px; height: 100px; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                <?php if ($enfImg): ?>
                                    <img src="<?= esc($enfImg) ?>" alt="<?= esc($user['username']) ?>" style="width: 100%; height: 100%; object-fit: cover; display: block;" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                                    <div class="w-100 h-100 rounded-circle bg-primary text-white align-items-center justify-content-center fw-bold fs-2 text-uppercase" style="display: none;">
                                        <?= esc($enfInitials) ?>
                                    </div>
                                <?php else: ?>
                                    <div class="w-100 h-100 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-2 text-uppercase">
                                        <?= esc($enfInitials) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <!-- Duty Status Indicator Dot -->
                            <span class="position-absolute bottom-0 end-0 p-1 <?= $dutyDotClass ?> border border-2 border-white rounded-circle" style="width: 20px; height: 20px;" title="Duty: <?= esc($duty) ?>"></span>
                        </div>

                        <h5 class="mb-1 fw-bold text-dark"><?= esc($fullName) ?></h5>
                        <div class="text-muted small font-monospace mb-2">@<?= esc($user['username']) ?></div>
                        
                        <div class="d-flex flex-wrap justify-content-center gap-1.5 mt-2">
                            <!-- Enforcer identity badge: violet, matching the enforcer badge on ticket cards. -->
                            <span class="badge px-2.5 py-1 rounded-pill text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.04em; background: #f5f3ff; color: #6d28d9; border: 1px solid #c4b5fd;">
                                Traffic Enforcer
                            </span>
                            <span class="badge <?= $dutyBadgeClass ?> px-2.5 py-1 rounded-pill" style="font-size: 0.72rem; letter-spacing: 0.04em;">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem; vertical-align: middle;"></i><?= esc($duty) ?>
                            </span>
                        </div>
                    </div>

                    <!-- Detail Fields -->
                    <div class="enforcer-info-list d-flex flex-column gap-3">
                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-envelope text-primary"></i>Email
                            </span>
                            <span class="fw-semibold text-dark small text-end text-break"><?= esc($user['email'] ?? '-') ?></span>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-person text-primary"></i>Full Name
                            </span>
                            <span class="fw-semibold text-dark small text-end"><?= esc($fullName) ?></span>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-calendar-event text-primary"></i>Age
                            </span>
                            <span class="fw-semibold text-dark small"><?= esc((string) ($user['age'] ?? '-')) ?> years old</span>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-geo-alt text-primary"></i>Address
                            </span>
                            <span class="fw-semibold text-dark small text-end"><?= esc($user['address'] ?? '-') ?></span>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-building text-primary"></i>Duty Station
                            </span>
                            <span class="fw-bold text-primary small text-end">
                                <i class="bi bi-pin-map-fill text-danger me-1"></i><?= esc($user['assigned_station'] ?? 'Not Assigned') ?>
                            </span>
                        </div>

                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-compass text-primary"></i>Coverage Area
                            </span>
                            <span class="fw-semibold text-dark small text-end"><?= esc($user['assigned_area'] ?? 'General Jurisdiction') ?></span>
                        </div>

                        <?php if (!empty($user['license_number'])): ?>
                        <div class="info-row d-flex justify-content-between align-items-start py-1 border-bottom border-light-subtle">
                            <span class="text-muted small d-flex align-items-center gap-1.5">
                                <i class="bi bi-card-heading text-primary"></i>License / Badge
                            </span>
                            <span class="fw-semibold font-monospace text-dark small"><?= esc($user['license_number']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Enforcement Activity & Performance Overview -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden enforcer-card">
                <div class="card-header bg-white border-bottom border-light-subtle py-3 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2 text-primary"></i>
                        <span>Enforcement Activity & Performance</span>
                    </h6>
                    <span class="badge bg-light text-muted border border-light-subtle px-2.5 py-1 small">
                        Updated Live
                    </span>
                </div>

                <div class="card-body p-4">
                    <!-- KPI Cards Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-4">
                            <div class="kpi-card p-3 rounded-3 bg-light border border-light-subtle text-center h-100 cursor-pointer" onclick="filterCitations('all')" role="button" title="Click to view all citations">
                                <div class="text-muted small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Citations</div>
                                <div class="fs-3 fw-bold text-dark mb-0"><?= number_format($totalTickets) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;"><?= number_format($totalInfractions) ?> Violations</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <div class="kpi-card p-3 rounded-3 bg-danger-subtle border border-danger-subtle text-center h-100 cursor-pointer" onclick="filterCitations('pending')" role="button" title="Click to view pending citations only">
                                <div class="text-danger small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Pending</div>
                                <div class="fs-3 fw-bold text-danger mb-0"><?= number_format($pendingCount) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Awaiting Payment</div>
                            </div>
                        </div>

                        <div class="col-6 col-md-4">
                            <div class="kpi-card p-3 rounded-3 bg-success-subtle border border-success-subtle text-center h-100 cursor-pointer" onclick="filterCitations('paid')" role="button" title="Click to view paid citations only">
                                <div class="text-success small text-uppercase fw-semibold mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">Settled</div>
                                <div class="fs-3 fw-bold text-success mb-0"><?= number_format($paidCount) ?></div>
                                <div class="text-muted" style="font-size: 0.72rem;">Fines Paid</div>
                            </div>
                        </div>
                    </div>

                    <!-- Fines & Financial Metrics Box -->
                    <div class="p-3.5 p-md-4 rounded-4 bg-light border border-light-subtle mb-3">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6 border-end-md">
                                <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.72rem; letter-spacing: 0.05em;">
                                    Total Assessed Penalties
                                </small>
                                <h3 class="fw-extrabold text-danger mb-1 font-monospace">
                                    ₱<?= number_format((float) $totalAssessedAmount, 2) ?>
                                </h3>
                                <p class="text-muted small mb-0" style="font-size: 0.75rem;">
                                    Gross citation value (Paid: ₱<?= number_format($paidAmount, 2) ?> + Pending: ₱<?= number_format(max(0, $totalAssessedAmount - $paidAmount), 2) ?>). Cancelled tickets carry no amount.
                                </p>
                            </div>

                            <div class="col-md-6 ps-md-4">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="small fw-semibold text-dark">Collection Settlement Rate</span>
                                    <span class="small fw-bold text-success font-monospace"><?= $collectionRate ?>%</span>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 999px;">
                                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?= min(100, max(0, $collectionRate)) ?>%;" aria-valuenow="<?= $collectionRate ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2 text-muted" style="font-size: 0.72rem;">
                                    <span>Settled: <strong><?= number_format($paidCount) ?></strong></span>
                                    <span>Pending: <strong><?= number_format($pendingCount) ?></strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Issued Violation Records — lives in the same column as the
                 metrics, so the activity side is filled to the same height as
                 the profile rail instead of ending in a dead white block. -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden enforcer-card mt-4">
        <div class="card-header bg-white border-bottom border-light-subtle py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-ruled text-primary"></i>
                    <span>Issued Violation Citations</span>
                </h6>
                <small class="text-muted">All citations authored or apprehended by <?= esc($user['username']) ?></small>
            </div>

            <!-- Section Filter Buttons: All, Pending, Paid -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <div class="btn-group citation-filter-group p-1 rounded-pill" role="group" aria-label="Filter Citations">
                    <button type="button" class="btn btn-sm rounded-pill px-3 filter-btn active" data-filter="all" onclick="filterCitations('all')">
                        All <span class="badge bg-secondary-subtle text-dark rounded-pill ms-1"><?= number_format($totalTickets) ?></span>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 filter-btn filter-pending-btn" data-filter="pending" onclick="filterCitations('pending')">
                        <span class="filter-dot bg-danger me-1"></span>Pending <span class="badge bg-danger text-white rounded-pill ms-1"><?= number_format($pendingCount) ?></span>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3 filter-btn filter-paid-btn" data-filter="paid" onclick="filterCitations('paid')">
                        <span class="filter-dot bg-success me-1"></span>Paid <span class="badge bg-success text-white rounded-pill ms-1"><?= number_format($paidCount) ?></span>
                    </button>
                    <?php if ($cancelledCount > 0): ?>
                    <button type="button" class="btn btn-sm rounded-pill px-3 filter-btn text-secondary" data-filter="cancelled" onclick="filterCitations('cancelled')">
                        Cancelled <span class="badge bg-secondary text-white rounded-pill ms-1"><?= number_format($cancelledCount) ?></span>
                    </button>
                    <?php endif; ?>
                </div>

                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 rounded-pill fw-semibold" id="visibleRecordsBadge">
                    <?= number_format($totalTickets) ?> total citations
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 enforcer-violations-table">
                    <thead>
                        <tr>
                            <th class="ps-4">Ticket ID</th>
                            <th>Violator</th>
                            <th>Status</th>
                            <th>Citation Date</th>
                            <th class="text-end pe-4 no-print">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="emptyFilterRow" class="d-none">
                            <td colspan="5" class="text-center py-5 text-muted">
                                <div class="py-4">
                                    <i class="bi bi-funnel fs-1 d-block text-muted opacity-50 mb-2"></i>
                                    <h6 class="fw-semibold text-dark mb-1">No Matching Citations</h6>
                                    <p class="small text-muted mb-0" id="emptyFilterText">There are no citations matching the selected filter.</p>
                                </div>
                            </td>
                        </tr>
                        <?php if (empty($tickets)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <div class="py-4">
                                        <i class="bi bi-inbox fs-1 d-block text-muted opacity-50 mb-2"></i>
                                        <h6 class="fw-semibold text-dark mb-1">No Citation Records Found</h6>
                                        <p class="small text-muted mb-0">This enforcer has not recorded any traffic infractions yet.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($tickets as $ticket): ?>
                                <?php
                                    $vStatus = strtolower($ticket['status'] ?? 'pending');
                                    $isPaid  = $vStatus === 'paid';
                                    $isCancelled = $vStatus === 'cancelled';
                                    $driverDisplayName = trim(($ticket['first_name'] ?? '') . ' ' . ($ticket['last_name'] ?? '')) ?: ($ticket['driver_name'] ?? '-');
                                ?>
                                <tr class="enforcer-record-row" data-status="<?= esc($vStatus) ?>">
                                    <td class="ps-4" data-label="Ticket ID">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <span class="ticket-id-pill font-monospace" title="Click to copy ticket number" onclick="copyTicketId('<?= esc($ticket['ticket_id'] ?? '') ?>', this)">
                                                <i class="bi bi-ticket-perforated me-1"></i><?= esc($ticket['ticket_id'] ?? 'N/A') ?>
                                                <i class="bi bi-clipboard ms-1 opacity-75 copy-icon" style="font-size: 0.72rem;"></i>
                                            </span>
                                        </div>
                                    </td>
                                    <td data-label="Violator">
                                        <div class="driver-name-text lh-sm">
                                            <?= esc($driverDisplayName) ?>
                                        </div>
                                        <?php if (!empty($ticket['license_plate'])): ?>
                                            <div class="mt-1">
                                                <span class="plate-pill font-monospace">
                                                    <?= tricycle_icon('me-1') ?><?= esc($ticket['license_plate']) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Status">
                                        <?php if ($vStatus === 'pending'): ?>
                                            <span class="status-glow-badge status-pending">
                                                <span class="status-dot"></span>Pending
                                            </span>
                                        <?php elseif ($isPaid): ?>
                                            <span class="status-glow-badge status-paid">
                                                <i class="bi bi-check-circle-fill me-1"></i>Paid
                                            </span>
                                        <?php else: ?>
                                            <span class="status-glow-badge status-cancelled">
                                                <i class="bi bi-x-circle-fill me-1"></i>Cancelled
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Citation Date" class="small text-muted" style="white-space: nowrap;">
                                        <div class="d-flex align-items-center gap-1.5">
                                            <i class="bi bi-calendar3 text-primary" style="font-size: 0.8rem; flex-shrink: 0;"></i>
                                            <span><?= isset($ticket['violation_date']) ? date('M d, Y h:i A', strtotime($ticket['violation_date'])) : '-' ?></span>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 no-print col-actions" data-label="Action">
                                        <a href="<?= base_url('officer/view/' . $ticket['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-2xs">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Card Styles */
    .enforcer-card {
        border-radius: 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    /* The profile card is the shorter column, so it follows the scroll
       instead of ending in blank space beside the activity list. */
    @media (min-width: 992px) {
        .enforcer-rail { position: sticky; top: 1rem; }
    }
    .kpi-card {
        transition: transform 0.2s ease;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
    }

    /* Table & Row Hover */
    .enforcer-violations-table thead th {
        background: #f8fafc;
        color: #475569;
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.85rem 1rem;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    .enforcer-record-row {
        background: #ffffff;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .enforcer-record-row:hover {
        background: #f8fafc;
        transform: translateY(-1.5px);
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.07);
    }

    /* Driver & Badges */
    .driver-name-text {
        color: #0f172a;
        font-weight: 700;
        font-size: 0.92rem;
        letter-spacing: -0.01em;
    }
    .ticket-id-pill {
        background: #f0f7ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        padding: 0.25rem 0.65rem;
        border-radius: 8px;
        font-size: 0.82rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        letter-spacing: 0.02em;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        user-select: none;
    }
    .ticket-id-pill:hover {
        background: #dbeafe;
        border-color: #93c5fd;
        color: #1e40af;
        transform: translateY(-1px);
        box-shadow: 0 3px 8px rgba(37, 99, 235, 0.15);
    }
    .ticket-id-pill.copied {
        background: #dcfce7 !important;
        border-color: #86efac !important;
        color: #15803d !important;
        box-shadow: 0 0 10px rgba(22, 163, 74, 0.2) !important;
    }
    .plate-pill {
        background: #ffffff !important;
        color: #0f172a !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 6px;
        padding: 0.15rem 0.5rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
    }
    .plate-pill i,
    .plate-pill .tricycle-icon {
        color: #2563eb !important;
    }
    .violation-chip {
        font-size: 0.81rem;
        color: #1e293b;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 0.22rem 0.55rem;
        display: inline-flex;
        align-items: center;
        line-height: 1.35;
        font-weight: 500;
        word-break: break-word;
    }
    .violation-chip .violation-dot {
        color: #ef4444;
        font-size: 1.15rem;
        line-height: 0.9;
        margin-right: 5px;
        font-weight: bold;
    }
    .total-fine-badge {
        font-size: 0.88rem;
        font-weight: 800;
        font-family: 'ui-monospace', 'SFMono-Regular', Menlo, Monaco, Consolas, monospace;
        padding: 0.25rem 0.7rem;
        border-radius: 999px;
        display: inline-block;
        transition: transform 0.2s ease;
    }
    /* Filter Buttons */
    .citation-filter-group {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .filter-btn {
        font-weight: 600;
        font-size: 0.8rem;
        border: none !important;
        color: #475569;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 0.32rem 0.85rem;
    }
    .filter-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
    }
    .filter-btn.active {
        background: #ffffff !important;
        color: #0f172a !important;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
    }
    .filter-btn.filter-pending-btn.active {
        color: #dc2626 !important;
        font-weight: 700;
        background: #ffffff !important;
    }
    .filter-btn.filter-paid-btn.active {
        color: #16a34a !important;
        font-weight: 700;
        background: #ffffff !important;
    }
    /* Each filter answers the pointer in its own colour — blue for All,
       red for Pending, green for Paid. These rules sit after the active
       states so the selected chip responds to hover too. */
    .filter-btn[data-filter="all"]:hover {
        background: #dbeafe !important;
        color: #1d4ed8 !important;
    }
    .filter-btn.filter-pending-btn:hover {
        background: #fee2e2 !important;
        color: #b91c1c !important;
    }
    .filter-btn.filter-paid-btn:hover {
        background: #dcfce7 !important;
        color: #15803d !important;
    }
    .filter-btn[data-filter="cancelled"]:hover {
        background: #e2e8f0 !important;
        color: #334155 !important;
    }
    .filter-dot {
        display: inline-block;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        vertical-align: middle;
    }
    .cursor-pointer {
        cursor: pointer;
    }

    .total-fine-pending {
        color: #dc2626;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }
    .total-fine-paid {
        color: #065f46;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
    }
    .status-glow-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.32rem 0.8rem;
        border-radius: 999px;
        font-size: 0.74rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .status-pending {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #f87171;
    }
    .status-pending .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #ef4444;
        margin-right: 6px;
        box-shadow: 0 0 6px rgba(239, 68, 68, 0.6);
        animation: pulseRed 2s infinite ease-in-out;
        flex-shrink: 0;
    }
    .status-paid {
        background: #ecfdf5;
        color: #15803d;
        border: 1px solid #86efac;
    }
    .status-cancelled {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    @keyframes pulseRed {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%      { transform: scale(0.85); opacity: 0.55; }
    }

    /* Mobile Responsive Card Transformation */
    @media (max-width: 768px) {
        .enforcer-violations-table thead { display: none !important; }
        .enforcer-violations-table tbody tr {
            display: block !important;
            margin-bottom: 0.85rem !important;
            border-radius: 12px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 2px 8px rgba(15,23,42,0.06) !important;
            transform: none !important;
            padding: 0.4rem 0 !important;
            background: #ffffff !important;
        }
        .enforcer-violations-table tbody td {
            display: flex !important;
            justify-content: space-between !important;
            align-items: flex-start !important;
            padding: 0.55rem 1rem !important;
            border-bottom: 1px solid #f1f5f9 !important;
            font-size: 0.84rem !important;
            color: #1e293b !important;
        }
        .enforcer-violations-table tbody td:last-child { border-bottom: none !important; }
        .enforcer-violations-table tbody td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            flex-shrink: 0;
            margin-right: 0.75rem;
            margin-top: 2px;
        }
        .enforcer-violations-table tbody td.col-actions {
            justify-content: flex-end !important;
        }
        .enforcer-violations-table,
        .enforcer-violations-table tbody {
            display: block !important;
            width: 100% !important;
            min-width: 0 !important;
        }
        .enforcer-violations-table tbody tr,
        .enforcer-violations-table tbody td {
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .enforcer-violations-table tbody td {
            row-gap: 0.35rem;
            flex-wrap: wrap;
            justify-content: flex-end !important;
            text-align: right;
        }
        .enforcer-violations-table tbody td::before {
            max-width: 45%;
            text-align: left;
            margin-right: auto !important;
        }
        .enforcer-violations-table tbody td > * {
            min-width: 0;
            max-width: 100%;
        }
        .enforcer-violations-table tbody td[data-label="Violator"] {
            display: block !important;
            text-align: left;
        }
        .enforcer-violations-table tbody td[data-label="Violator"]::before {
            display: block;
            margin: 0 0 0.15rem;
        }
    }

    @media print {
        .sidebar, .sidebar-toggle, .btn, .alert, .main-content h1, .breadcrumb, .no-print {
            display: none !important;
        }
        .main-content {
            margin-left: 0 !important;
            width: 100% !important;
            padding: 0 !important;
        }
        .container-fluid {
            padding: 0 !important;
        }
    }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.copyTicketId = function(ticketId, el) {
        if (!ticketId || ticketId === 'N/A') return;
        navigator.clipboard.writeText(ticketId).then(() => {
            el.classList.add('copied');
            const originalHTML = el.innerHTML;
            el.innerHTML = `<i class="bi bi-check2 text-success me-1"></i>Copied!`;
            setTimeout(() => {
                el.innerHTML = originalHTML;
                el.classList.remove('copied');
            }, 1800);
        }).catch(() => {});
    };

    window.filterCitations = function(statusFilter) {
        const filter = (statusFilter || 'all').toLowerCase().trim();
        
        // Update active class on filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            const btnFilter = (btn.getAttribute('data-filter') || '').toLowerCase().trim();
            if (btnFilter === filter) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Filter individual transaction rows
        const rows = document.querySelectorAll('.enforcer-record-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowStatus = (row.getAttribute('data-status') || '').toLowerCase().trim();
            if (filter === 'all' || rowStatus === filter) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update records counter badge
        const badge = document.getElementById('visibleRecordsBadge');
        if (badge) {
            if (filter === 'all') {
                badge.textContent = `${rows.length} total records`;
            } else {
                const label = filter.charAt(0).toUpperCase() + filter.slice(1);
                badge.textContent = `${visibleCount} ${label} records`;
            }
        }

        // Empty state message when no rows match the filter
        const emptyRow = document.getElementById('emptyFilterRow');
        if (emptyRow) {
            if (visibleCount === 0 && rows.length > 0) {
                emptyRow.classList.remove('d-none');
                const text = document.getElementById('emptyFilterText');
                if (text) {
                    text.textContent = `No ${filter} citations found for this enforcer.`;
                }
            } else {
                emptyRow.classList.add('d-none');
            }
        }
    };
</script>
<?= $this->endSection() ?>
