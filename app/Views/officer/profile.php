<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <?php
        $fullName = trim((string) (($officer['firstname'] ?? '') . ' ' . ($officer['lastname'] ?? '')));
        $displayName = $fullName !== '' ? $fullName : (string) ($officer['username'] ?? 'Officer');
        $initials = strtoupper(substr((string) $displayName, 0, 1));
        $hasProfileFile = !empty($officer['profile_image']) && is_file(FCPATH . 'uploads/profile/' . $officer['profile_image']);
        $profileImage = $hasProfileFile ? base_url('uploads/profile/' . $officer['profile_image']) : null;
        $totalTickets  = count($records);
        $collectionPct = $totalTickets > 0 ? round(($paidCount / $totalTickets) * 100) : 0;
    ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= esc(session()->getFlashdata('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-circle-fill me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ═══ HERO BANNER ════════════════════════════════════════════ -->
    <div class="profile-hero mb-4 premium-reveal" style="--reveal-delay:0.05s">
        <div class="hero-orb hero-orb-1"></div>
        <div class="hero-orb hero-orb-2"></div>
        <div class="hero-orb hero-orb-3"></div>
        <div class="profile-hero-content">
            <!-- Avatar with hover overlay & click to change -->
            <div class="profile-hero-avatar-wrap" id="heroAvatarWrap" title="Click or tap to change photo">
                <div class="avatar-display-container" id="avatarDisplayContainer">
                    <?php if ($profileImage): ?>
                        <img src="<?= esc($profileImage) ?>" alt="" class="profile-hero-avatar-img" id="heroAvatarImg" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <span class="profile-hero-avatar-initials" id="heroAvatarInitials" style="display:none;"><?= esc($initials) ?></span>
                    <?php else: ?>
                        <span class="profile-hero-avatar-initials" id="heroAvatarInitials"><?= esc($initials) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Hover/Touch overlay -->
                <label for="hero_photo_input" class="avatar-hover-overlay" title="Upload new photo">
                    <i class="bi bi-camera-fill fs-5 mb-1"></i>
                    <span>Change</span>
                </label>

                <!-- Loading spinner overlay -->
                <div class="avatar-upload-spinner d-none" id="avatarUploadSpinner">
                    <div class="spinner-border text-light" style="width: 1.8rem; height: 1.8rem;" role="status">
                        <span class="visually-hidden">Uploading...</span>
                    </div>
                </div>

                <span class="hero-status-dot <?= ($officer['status'] ?? '') === 'active' ? 'dot-active' : 'dot-inactive' ?>"></span>
            </div>

            <!-- Info -->
            <div class="profile-hero-info">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h2 class="hero-name mb-0"><?= esc($displayName) ?></h2>
                    <span class="hero-role-badge"><i class="bi bi-shield-check me-1"></i>Traffic Enforcer</span>
                </div>
                <div class="hero-meta">
                    <span><i class="bi bi-person me-1"></i><?= esc($officer['username'] ?? '-') ?></span>
                    <span><i class="bi bi-envelope me-1"></i><?= esc($officer['email'] ?? '-') ?></span>
                    <span>
                        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;vertical-align:middle;color:<?= ($officer['status'] ?? '') === 'active' ? '#4ade80' : '#94a3b8' ?>"></i>
                        <?= ucfirst(esc($officer['status'] ?? 'unknown')) ?>
                    </span>
                </div>
            </div>

            <!-- Photo upload actions -->
            <div class="hero-upload-wrap ms-auto">
                <form id="profilePhotoForm" action="<?= base_url('officer/profile/photo') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="file" id="hero_photo_input" name="profile_photo" class="d-none"
                           accept="image/png,image/jpeg,image/jpg,image/webp">
                </form>

                <div class="d-flex flex-wrap align-items-center gap-2 justify-content-start justify-content-md-end">
                    <label class="btn-hero-upload" for="hero_photo_input" id="btnUploadTrigger">
                        <i class="bi bi-camera-fill me-1"></i><span id="btnUploadText"><?= $profileImage ? 'Change Photo' : 'Add Photo' ?></span>
                    </label>

                    <button type="button" class="btn-hero-remove <?= $profileImage ? '' : 'd-none' ?>" id="btnRemovePhoto" title="Remove Profile Photo">
                        <i class="bi bi-trash3 me-1"></i>Remove
                    </button>
                </div>
                <small class="d-block text-start text-md-end mt-1 text-white-50" style="font-size:0.72rem">
                    JPG, PNG, WEBP &middot; Max 10MB
                </small>
            </div>
        </div>
    </div>

    <!-- ═══ ANALYTICS TOGGLE BAR ════════════════════════════════════ -->
    <div class="analytics-toggle-bar d-flex align-items-center justify-content-between mb-3 premium-reveal" style="--reveal-delay:0.12s">
        <div class="d-flex align-items-center gap-2">
            <span class="analytics-toggle-label">
                <i class="bi bi-bar-chart-line me-1 text-primary"></i>
                <span class="fw-bold" style="font-size:0.92rem;">My Analytics</span>
            </span>
            <span class="analytics-visible-pill" id="analyticsVisiblePill">Visible</span>
        </div>
        <button type="button" id="analyticsToggleBtn" class="btn-analytics-toggle" title="Toggle Analytics">
            <i class="bi bi-eye-slash me-1" id="analyticsToggleIcon"></i>
            <span id="analyticsToggleText">Hide</span>
        </button>
    </div>

    <!-- ═══ ANALYTICS SECTION ═══════════════════════════════════════ -->
    <div id="analyticsSection" class="analytics-section-wrap">


    <!-- ═══ SETTLEMENT EFFICIENCY BANNER ════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4 efficiency-card premium-reveal" style="--reveal-delay:0.15s">
        <div class="card-body py-3 px-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-5">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold d-flex align-items-center text-dark" style="font-size:0.92rem">
                            <i class="bi bi-shield-check me-2 text-primary fs-5"></i>Settlement Efficiency
                        </span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold"><?= $collectionPct ?>%</span>
                    </div>
                    <div class="coll-track">
                        <div class="coll-fill" style="width:<?= $collectionPct ?>%"></div>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-2">
                        <div class="eff-pill">
                            <span class="eff-dot bg-primary"></span>
                            <span class="eff-label">Total Issued:</span>
                            <span class="eff-val"><?= $totalTickets ?></span>
                        </div>
                        <div class="eff-pill">
                            <span class="eff-dot bg-success"></span>
                            <span class="eff-label">Settled:</span>
                            <span class="eff-val text-success"><?= $paidCount ?></span>
                        </div>
                        <div class="eff-pill">
                            <span class="eff-dot bg-warning"></span>
                            <span class="eff-label">Pending:</span>
                            <span class="eff-val text-warning"><?= $pendingCount ?></span>
                        </div>
                        <div class="eff-pill">
                            <span class="eff-dot bg-info"></span>
                            <span class="eff-label">Fines Assessed:</span>
                            <span class="eff-val text-dark">₱<?= number_format((float) $totalAmount, 0) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ CHARTS ROW 1 ═══════════════════════════════════════════ -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6 premium-reveal" style="--reveal-delay:0.25s">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom border-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-week me-2 text-primary"></i>Weekly Trend <small class="fw-normal text-muted">(Last 12 Weeks)</small></h6>
                </div>
                <div class="card-body"><div style="position:relative;height:290px;"><canvas id="weeklyChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-lg-6 premium-reveal" style="--reveal-delay:0.3s">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom border-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-month me-2 text-success"></i>Monthly Breakdown (<?= esc($year) ?>)</h6>
                    <form method="GET" class="m-0">
                        <select name="year" class="form-select form-select-sm border-0 bg-light" style="width:85px;" onchange="this.form.submit()">
                            <?php $currentYear = date('Y'); ?>
                            <?php for($y = $currentYear; $y >= $currentYear - 4; $y--): ?>
                                <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
                <div class="card-body"><div style="position:relative;height:290px;"><canvas id="monthlyChart"></canvas></div></div>
            </div>
        </div>
    </div>

    <!-- ═══ NATURE OF VIOLATIONS ════════════════════════════════════ -->
    <?php
        $natureSorted = $nature_summary ?? [];
        usort($natureSorted, static fn($a, $b) => ($b['count'] ?? 0) <=> ($a['count'] ?? 0));
        $totalNatureIncidents = array_sum(array_column($natureSorted, 'count'));
        $topNature = array_slice($natureSorted, 0, 5);
        $totalNatureFines = array_sum(array_column($natureSorted, 'total_amount'));
    ?>
    <div class="row g-4 premium-reveal" style="--reveal-delay:0.35s">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white border-bottom border-light">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-diagram-3 me-2 text-info"></i>Nature of Violations</h6>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                        <?= $totalNatureIncidents ?> Total Incidents
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-4 align-items-stretch">
                        <div class="col-lg-7">
                            <div style="position:relative; min-height:330px; height:100%;">
                                <canvas id="natureBarChart"></canvas>
                            </div>
                        </div>
                        <div class="col-lg-5">
                            <div class="top-offenses-panel p-3 rounded-4 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="fw-bold text-dark" style="font-size:0.9rem">
                                            <i class="bi bi-fire me-1 text-danger"></i>Top Most Frequent Infractions
                                        </span>
                                        <small class="text-muted fw-semibold">Ranked</small>
                                    </div>
                                    <div class="top-offenses-list">
                                        <?php if (empty($topNature)): ?>
                                            <div class="text-muted text-center py-4 small">No violations recorded yet.</div>
                                        <?php else: ?>
                                            <?php foreach($topNature as $idx => $item): 
                                                $count = (int) ($item['count'] ?? 0);
                                                $pct = $totalNatureIncidents > 0 ? round(($count / $totalNatureIncidents) * 100) : 0;
                                                $rankClass = match($idx) {
                                                    0 => 'rank-badge-1',
                                                    1 => 'rank-badge-2',
                                                    2 => 'rank-badge-3',
                                                    default => 'rank-badge-other'
                                                };
                                                $barClass = match($idx) {
                                                    0 => 'bg-danger',
                                                    1 => 'bg-warning',
                                                    2 => 'bg-info',
                                                    default => 'bg-primary'
                                                };
                                            ?>
                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="text-truncate fw-semibold small pe-2" style="max-width: 210px;" title="<?= esc($item['violation_type']) ?>">
                                                            <span class="rank-badge <?= $rankClass ?> me-1"><?= $idx + 1 ?></span>
                                                            <?= esc($item['violation_type']) ?>
                                                        </span>
                                                        <span class="small fw-bold text-dark">
                                                            <?= $count ?> <span class="text-muted fw-normal" style="font-size:0.75rem">(<?= $pct ?>%)</span>
                                                        </span>
                                                    </div>
                                                    <div class="progress" style="height: 6px; border-radius: 99px; background: rgba(226,232,240,0.8);">
                                                        <div class="progress-bar <?= $barClass ?>" style="width: <?= $pct ?>%; border-radius: 99px;"></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="pt-3 mt-2 border-top border-light-subtle d-flex justify-content-between align-items-center">
                                    <span class="small text-muted">Total Violations Assessed:</span>
                                    <span class="fw-bold text-dark fs-6">₱<?= number_format((float)$totalNatureFines, 0) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div> <!-- End Analytics Section -->
</div>

<style>
/* ── ANALYTICS TOGGLE BAR ───────────────────────────────── */
.analytics-toggle-bar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 0.65rem 1.1rem;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.04);
    transition: all 0.2s ease;
}
.analytics-toggle-label {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    color: #1e293b;
}
.analytics-visible-pill {
    display: inline-flex;
    align-items: center;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 0.2rem 0.55rem;
    border-radius: 20px;
    background: rgba(16, 185, 129, 0.12);
    color: #047857;
    border: 1px solid rgba(16, 185, 129, 0.3);
    transition: all 0.25s ease;
}
.analytics-visible-pill.hidden-state {
    background: rgba(148, 163, 184, 0.12);
    color: #64748b;
    border-color: rgba(148, 163, 184, 0.3);
}
.btn-analytics-toggle {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 0.38rem 0.85rem;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #475569;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
}
.btn-analytics-toggle:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #2563eb;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(37, 99, 235, 0.12);
}
.btn-analytics-toggle.is-hidden {
    background: #f0fdf4;
    border-color: #86efac;
    color: #059669;
}
.btn-analytics-toggle.is-hidden:hover {
    background: #dcfce7;
    border-color: #4ade80;
    color: #047857;
}

/* ── SMOOTH ANALYTICS SECTION COLLAPSE ─────────────────── */
.analytics-section-wrap {
    overflow: hidden;
    transition: max-height 0.45s cubic-bezier(0.16, 1, 0.3, 1),
                opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1),
                transform 0.45s cubic-bezier(0.16, 1, 0.3, 1);
    max-height: 4000px;
    opacity: 1;
    transform: translateY(0);
    transform-origin: top center;
}
.analytics-section-wrap.analytics-collapsed {
    max-height: 0 !important;
    opacity: 0;
    transform: translateY(-8px);
    pointer-events: none;
}

/* ── HERO ─────────────────────────────────────────────────── */
.profile-hero {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    padding: 2.2rem 2rem;
    background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #2563eb 100%);
    box-shadow: 0 12px 32px rgba(30, 58, 138, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.15);
    isolation: isolate;
}
.profile-hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1.5rem;
}
.profile-hero-avatar-wrap {
    position: relative;
    width: 104px;
    height: 104px;
    flex-shrink: 0;
    border-radius: 50%;
    cursor: pointer;
    transition: transform 0.25s ease;
}
.profile-hero-avatar-wrap:hover {
    transform: scale(1.02);
}
.avatar-display-container {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    overflow: hidden;
    position: relative;
    background: rgba(255, 255, 255, 0.12);
}
.profile-hero-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
    border: 4px solid rgba(255,255,255,0.4);
    box-shadow: 0 8px 28px rgba(0,0,0,0.3);
    display: block;
}
.profile-hero-avatar-initials {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    border: 4px solid rgba(255,255,255,0.4);
    box-shadow: 0 8px 28px rgba(0,0,0,0.3);
    background: rgba(255,255,255,0.18);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.4rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -1px;
}
.avatar-hover-overlay {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.68);
    backdrop-filter: blur(3px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.72rem;
    font-weight: 700;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.22s ease;
    cursor: pointer;
    z-index: 3;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.profile-hero-avatar-wrap:hover .avatar-hover-overlay {
    opacity: 1;
    pointer-events: auto;
}
.avatar-upload-spinner {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.78);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 4;
}
.hero-status-dot {
    position: absolute;
    bottom: 4px;
    right: 4px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 3px solid white;
    z-index: 5;
}
.dot-active  { background: #4ade80; box-shadow: 0 0 10px rgba(74,222,128,0.85); }
.dot-inactive { background: #94a3b8; }
.hero-name {
    font-size: 1.7rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.03em;
}
.hero-role-badge {
    background: rgba(16, 185, 129, 0.2);
    border: 1px solid rgba(16, 185, 129, 0.4);
    color: #34d399;
    font-size: 0.73rem;
    font-weight: 700;
    padding: 0.3rem 0.75rem;
    border-radius: 20px;
    letter-spacing: 0.03em;
}
.hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 1.1rem;
    color: rgba(255,255,255,0.8);
    font-size: 0.86rem;
    margin-top: 0.35rem;
}
.btn-hero-upload {
    display: inline-flex;
    align-items: center;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.38);
    color: #fff;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 12px;
    padding: 0.55rem 1.15rem;
    cursor: pointer;
    transition: all 0.22s ease;
    white-space: nowrap;
    box-shadow: 0 4px 14px rgba(0,0,0,0.15);
}
.btn-hero-upload:hover {
    background: rgba(255,255,255,0.32);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.22);
}
.btn-hero-remove {
    display: inline-flex;
    align-items: center;
    background: rgba(239, 68, 68, 0.22);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(239, 68, 68, 0.45);
    color: #fee2e2;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 12px;
    padding: 0.55rem 1rem;
    cursor: pointer;
    transition: all 0.22s ease;
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.15);
}
.btn-hero-remove:hover {
    background: rgba(239, 68, 68, 0.45);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(239, 68, 68, 0.25);
}
.hero-orb {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
    filter: blur(55px);
    opacity: 0.38;
}
.hero-orb-1 { width:240px;height:240px;background:#818cf8;top:-70px;right:8%;animation:orbf 14s ease-in-out infinite alternate; }
.hero-orb-2 { width:160px;height:160px;background:#c084fc;bottom:-55px;right:28%;animation:orbf 18s ease-in-out infinite alternate-reverse; }
.hero-orb-3 { width:120px;height:120px;background:#38bdf8;top:20%;right:4%;animation:orbf 11s ease-in-out infinite alternate; }
@keyframes orbf { from{transform:translate(0,0)} to{transform:translate(18px,14px)} }

/* ── STAT KPI GLASS CARDS ──────────────────────────────────── */
.stat-glass-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
    transition: all 0.25s ease;
    overflow: hidden;
}
.stat-glass-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
    border-color: #cbd5e1;
}
.stat-card-body {
    padding: 1.25rem 1.25rem 1rem;
}
.stat-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
}
.stat-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
}
.stat-counter-value {
    font-size: 1.9rem;
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.02em;
    color: #0f172a;
    margin-bottom: 0.35rem;
}
.stat-footer-text {
    font-size: 0.75rem;
}
.pulse-dot-amber {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #f59e0b;
    box-shadow: 0 0 6px rgba(245, 158, 11, 0.8);
    animation: pulseAmber 2s infinite;
}
@keyframes pulseAmber {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(0.85); }
}

/* ── COLLECTION BAR ───────────────────────────────────────── */
.coll-track {
    height: 10px;
    background: rgba(16, 185, 129, 0.12);
    border-radius: 99px;
    overflow: hidden;
}
.coll-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #10b981 0%, #059669 100%);
    transition: width 1.4s cubic-bezier(0.16,1,0.3,1);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.4);
}

/* ── EFFICIENCY BANNER ────────────────────────────────────── */
.efficiency-card {
    border-radius: 18px;
    background: #ffffff;
    border: 1px solid #e2e8f0 !important;
}
.eff-pill {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0.42rem 0.9rem;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 99px;
    font-size: 0.82rem;
    color: #475569;
}
.eff-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.eff-label {
    font-weight: 500;
}
.eff-val {
    font-weight: 700;
}

/* ── TOP OFFENSES RANKING ─────────────────────────────────── */
.top-offenses-panel {
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
}
.rank-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 0.72rem;
    font-weight: 800;
}
.rank-badge-1 { background: #fee2e2; color: #ef4444; }
.rank-badge-2 { background: #fef3c7; color: #d97706; }
.rank-badge-3 { background: #e0f2fe; color: #0284c7; }
.rank-badge-other { background: #f1f5f9; color: #64748b; }

/* ── RESPONSIVE ───────────────────────────────────────────── */
@media (max-width: 768px) {
    .profile-hero {
        padding: 1.5rem 1.2rem;
        border-radius: 18px;
    }
    .profile-hero-content {
        flex-direction: column;
        align-items: flex-start;
        gap: 1.1rem;
    }
    .profile-hero-avatar-wrap {
        width: 90px;
        height: 90px;
    }
    .profile-hero-avatar-initials {
        font-size: 2rem;
    }
    .hero-name {
        font-size: 1.35rem;
    }
    .hero-meta {
        font-size: 0.8rem;
        gap: 0.75rem;
    }
    .pkpi-value { font-size: 1.9rem; }
    .pkpi-value-money { font-size: 1.3rem; }
    .hero-upload-wrap {
        width: 100%;
        margin-top: 0.5rem;
    }
}

@media print {
    .sidebar,.sidebar-toggle,.btn,.alert { display:none !important; }
    .main-content { margin-left:0 !important; width:100% !important; padding:0 !important; }
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ═══ AUTOMATIC PROFILE PHOTO UPLOAD & PREVIEW ══════════════
    const photoInput = document.getElementById('hero_photo_input');
    const photoForm = document.getElementById('profilePhotoForm');
    const avatarContainer = document.getElementById('avatarDisplayContainer');
    const uploadSpinner = document.getElementById('avatarUploadSpinner');
    const btnUploadText = document.getElementById('btnUploadText');
    const btnRemove = document.getElementById('btnRemovePhoto');
    const fallbackInitials = <?= json_encode($initials) ?>;

    if (photoInput && photoForm && avatarContainer) {
        photoInput.addEventListener('change', async function () {
            const file = this.files && this.files[0];
            if (!file) return;

            // Validate file type
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!validTypes.includes(file.type.toLowerCase())) {
                Swal.fire({
                    icon: 'error',
                    title: 'Unsupported Image Format',
                    text: 'Please choose a valid JPG, PNG, or WEBP photo.',
                    confirmButtonColor: '#059669'
                });
                this.value = '';
                return;
            }

            // Validate file size (max 10MB)
            if (file.size > 10 * 1024 * 1024) {
                Swal.fire({
                    icon: 'error',
                    title: 'File Too Large',
                    text: 'Photo must be under 10MB.',
                    confirmButtonColor: '#059669'
                });
                this.value = '';
                return;
            }

            // Save previous markup in case upload fails
            const previousHTML = avatarContainer.innerHTML;

            // Instant local preview
            const previewUrl = URL.createObjectURL(file);
            avatarContainer.innerHTML = `<img src="${previewUrl}" alt="Profile preview" class="profile-hero-avatar-img" id="heroAvatarImg">`;

            // Show spinner overlay
            if (uploadSpinner) uploadSpinner.classList.remove('d-none');

            // Send via AJAX FormData
            const formData = new FormData(photoForm);

            try {
                const response = await fetch(photoForm.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    // Update main avatar with server response URL (cache-busting query)
                    const freshUrl = data.photo_url + '?t=' + Date.now();
                    avatarContainer.innerHTML = `<img src="${freshUrl}" alt="Profile photo" class="profile-hero-avatar-img" id="heroAvatarImg">`;

                    // Update topbar header avatar in layout
                    const topbarAvatar = document.querySelector('.topbar-user-avatar');
                    if (topbarAvatar) {
                        topbarAvatar.innerHTML = `<img src="${freshUrl}" alt="User Avatar" class="topbar-avatar-img">`;
                    }

                    // Update sidebar user avatar
                    const sidebarAvatar = document.querySelector('.sidebar-user-avatar');
                    if (sidebarAvatar) {
                        const existingDot = sidebarAvatar.querySelector('.user-online-dot');
                        sidebarAvatar.innerHTML = `<img src="${freshUrl}" alt="Avatar" class="w-100 h-100 rounded-circle" style="object-fit: cover;">`;
                        if (existingDot) sidebarAvatar.appendChild(existingDot);
                    }

                    // Update CSRF token if returned
                    if (data.csrf_token && data.csrf_hash) {
                        const csrfInputs = document.querySelectorAll(`input[name="${data.csrf_token}"]`);
                        csrfInputs.forEach(input => input.value = data.csrf_hash);
                    }

                    if (btnUploadText) btnUploadText.textContent = 'Change Photo';
                    if (btnRemove) btnRemove.classList.remove('d-none');

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Profile photo updated successfully!'
                    });
                } else {
                    throw new Error(data.message || 'Could not save profile photo.');
                }
            } catch (err) {
                // Revert on failure
                avatarContainer.innerHTML = previousHTML;
                Swal.fire({
                    icon: 'error',
                    title: 'Upload Failed',
                    text: err.message || 'Failed to upload profile photo. Please try again.',
                    confirmButtonColor: '#059669'
                });
            } finally {
                if (uploadSpinner) uploadSpinner.classList.add('d-none');
                photoInput.value = '';
                URL.revokeObjectURL(previewUrl);
            }
        });
    }

    // ═══ REMOVE PHOTO HANDLER ══════════════════════════════════
    if (btnRemove) {
        btnRemove.addEventListener('click', async function () {
            const confirmRes = await Swal.fire({
                title: 'Remove Profile Photo?',
                text: 'Your current photo will be deleted and reset to your initials.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, remove photo'
            });

            if (!confirmRes.isConfirmed) return;

            if (uploadSpinner) uploadSpinner.classList.remove('d-none');

            try {
                const csrfInput = photoForm ? photoForm.querySelector('input[type="hidden"]') : null;
                const formData = new FormData();
                if (csrfInput) {
                    formData.append(csrfInput.name, csrfInput.value);
                }

                const response = await fetch('<?= base_url('officer/profile/photo/remove') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.status === 'success') {
                    const initials = data.initials || fallbackInitials;
                    avatarContainer.innerHTML = `<span class="profile-hero-avatar-initials" id="heroAvatarInitials">${initials}</span>`;

                    // Update topbar avatar
                    const topbarAvatar = document.querySelector('.topbar-user-avatar');
                    if (topbarAvatar) {
                        topbarAvatar.innerHTML = `<span class="topbar-avatar-initials">${initials}</span>`;
                    }

                    // Update sidebar avatar back to initials
                    const sidebarAvatar = document.querySelector('.sidebar-user-avatar');
                    if (sidebarAvatar) {
                        const existingDot = sidebarAvatar.querySelector('.user-online-dot');
                        sidebarAvatar.innerHTML = `<div class="w-100 h-100 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="font-size: 0.85rem;">${initials}</div>`;
                        if (existingDot) sidebarAvatar.appendChild(existingDot);
                    }

                    if (btnUploadText) btnUploadText.textContent = 'Add Photo';
                    btnRemove.classList.add('d-none');

                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Profile photo removed.'
                    });
                } else {
                    throw new Error(data.message || 'Could not remove photo.');
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.message || 'Failed to remove photo.',
                    confirmButtonColor: '#059669'
                });
            } finally {
                if (uploadSpinner) uploadSpinner.classList.add('d-none');
            }
        });
    }

    // ═══ ANIMATED KPI COUNTERS ═════════════════════════════════
    document.querySelectorAll('.pkpi-value[data-count]').forEach(el => {
        const target = parseInt(el.getAttribute('data-count'), 10);
        if (isNaN(target)) return;
        let start = null;
        const step = ts => {
            if (!start) start = ts;
            const p = Math.min((ts - start) / 1000, 1);
            el.textContent = Math.round((1 - Math.pow(1 - p, 3)) * target);
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    });

    // Animate progress bar
    setTimeout(() => {
        const f = document.querySelector('.coll-fill');
        if (f) { const w = f.style.width; f.style.width='0%'; requestAnimationFrame(() => requestAnimationFrame(() => { f.style.width = w; })); }
    }, 400);

    // Shared defaults
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';
    Chart.defaults.plugins.tooltip.backgroundColor = 'rgba(15,23,42,0.92)';
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 10;
    const grid = { color: 'rgba(226,232,240,0.5)', drawBorder: false };

    // Weekly Trend
    const wd = <?= json_encode($weekly_trend) ?>;
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: wd.map(w => w.label),
            datasets: [
                { 
                    label: 'Violations', 
                    data: wd.map(w => w.count), 
                    backgroundColor: 'rgba(59, 130, 246, 0.85)', 
                    hoverBackgroundColor: '#2563eb', 
                    borderRadius: 6, 
                    borderSkipped: false, 
                    order: 2, 
                    yAxisID: 'y' 
                },
                { 
                    label: 'Revenue (₱)', 
                    data: wd.map(w => w.revenue), 
                    type: 'line', 
                    borderColor: '#10b981', 
                    backgroundColor: 'rgba(16, 185, 129, 0.08)', 
                    fill: true, 
                    borderWidth: 2.5, 
                    tension: 0.28, 
                    pointBackgroundColor: '#10b981', 
                    pointRadius: 4, 
                    pointHoverRadius: 6, 
                    order: 1, 
                    yAxisID: 'y1' 
                }
            ]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'top', labels: { usePointStyle: true, pointStyleWidth: 10 } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            if (ctx.dataset.yAxisID === 'y1') {
                                return ' Revenue: ₱' + Number(ctx.raw || 0).toLocaleString();
                            }
                            return ' Tickets: ' + Number(ctx.raw || 0);
                        }
                    }
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: { 
                    beginAtZero: true, 
                    grid, 
                    ticks: { 
                        precision: 0, 
                        stepSize: 1 
                    }, 
                    title: { display: true, text: 'Tickets Count' } 
                },
                y1: { 
                    beginAtZero: true, 
                    position: 'right', 
                    grid: { drawOnChartArea: false }, 
                    ticks: { 
                        callback: v => '₱' + Number(v).toLocaleString() 
                    }, 
                    title: { display: true, text: 'Revenue (₱)' } 
                }
            }
        }
    });

    // Monthly Breakdown
    const md = <?= json_encode($monthly_breakdown) ?>;
    const mnames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: mnames,
            datasets: [
                { 
                    label: 'Total Issued', 
                    data: Object.values(md).map(m=>m.count), 
                    borderColor:'#3b82f6', 
                    backgroundColor:'rgba(59,130,246,0.08)', 
                    fill:true, 
                    tension: 0.28, 
                    borderWidth:3, 
                    pointRadius:4, 
                    pointHoverRadius:7, 
                    pointBackgroundColor: '#3b82f6' 
                },
                { 
                    label: 'Paid',         
                    data: Object.values(md).map(m=>m.paid),  
                    borderColor:'#10b981', 
                    borderDash:[6,4], 
                    tension: 0.28, 
                    borderWidth:2.5, 
                    pointRadius:3, 
                    pointBackgroundColor: '#10b981' 
                },
                { 
                    label: 'Pending',      
                    data: Object.values(md).map(m=>m.pending),
                    borderColor:'#f59e0b', 
                    borderDash:[3,5], 
                    tension: 0.28, 
                    borderWidth:2.5, 
                    pointRadius:3, 
                    pointBackgroundColor: '#f59e0b' 
                }
            ]
        },
        options: {
            responsive: true, 
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { 
                legend: { position: 'top', labels: { usePointStyle: true, pointStyleWidth: 10 } },
                tooltip: {
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${Number(ctx.raw || 0)} tickets`
                    }
                }
            },
            scales: { 
                x: { grid: { display: false } }, 
                y: { 
                    beginAtZero: true, 
                    grid,
                    ticks: {
                        precision: 0,
                        stepSize: 1
                    },
                    title: { display: true, text: 'Tickets Count' }
                } 
            }
        }
    });

    // Nature of Violation Horizontal Bar Chart
    const nd = <?= json_encode($natureSorted) ?>;
    const nlabels = nd.map(n => n.violation_type);
    const ncounts  = nd.map(n => n.count);
    const palette  = ['#3b82f6','#0ea5e9','#06b6d4','#10b981','#14b8a6','#6366f1','#8b5cf6','#f59e0b','#ec4899','#f97316'];

    new Chart(document.getElementById('natureBarChart'), {
        type: 'bar',
        data: {
            labels: nlabels,
            datasets: [{ 
                label: 'Incidents', 
                data: ncounts, 
                backgroundColor: ncounts.map((_, i) => palette[i % palette.length] + 'cc'),
                hoverBackgroundColor: ncounts.map((_, i) => palette[i % palette.length]),
                borderRadius: 6, 
                borderSkipped: false 
            }]
        },
        options: {
            indexAxis: 'y', 
            responsive: true, 
            maintainAspectRatio: false,
            scales: { 
                x: { 
                    beginAtZero: true, 
                    grid,
                    ticks: {
                        precision: 0,
                        stepSize: 1
                    },
                    title: { display: true, text: 'Incidents Count' }
                }, 
                y: { 
                    grid: { display: false },
                    ticks: { font: { weight: '500' } }
                } 
            },
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ` Incidents: ${ctx.raw}`
                    }
                }
            }
        }
    });
});
</script>

<script>
// ═══ ANALYTICS SECTION TOGGLE ════════════════════════════════
(function () {
    const STORAGE_KEY = 'officer_analytics_hidden';
    const section     = document.getElementById('analyticsSection');
    const btn         = document.getElementById('analyticsToggleBtn');
    const icon        = document.getElementById('analyticsToggleIcon');
    const text        = document.getElementById('analyticsToggleText');
    const pill        = document.getElementById('analyticsVisiblePill');

    if (!section || !btn) return;

    function setHidden(hidden, animate) {
        if (hidden) {
            if (animate) {
                section.classList.add('analytics-collapsed');
            } else {
                // Instant — no transition on first load
                section.style.transition = 'none';
                section.classList.add('analytics-collapsed');
                // Re-enable transition after one frame
                requestAnimationFrame(() => {
                    section.style.transition = '';
                });
            }
            icon.className  = 'bi bi-eye me-1';
            text.textContent = 'Show';
            pill.textContent = 'Hidden';
            pill.classList.add('hidden-state');
            btn.classList.add('is-hidden');
            localStorage.setItem(STORAGE_KEY, '1');
        } else {
            section.classList.remove('analytics-collapsed');
            icon.className  = 'bi bi-eye-slash me-1';
            text.textContent = 'Hide';
            pill.textContent = 'Visible';
            pill.classList.remove('hidden-state');
            btn.classList.remove('is-hidden');
            localStorage.removeItem(STORAGE_KEY);
        }
    }

    // Init on page load — instant (no animation)
    const startHidden = localStorage.getItem(STORAGE_KEY) === '1';
    setHidden(startHidden, false);

    // Click handler — animated
    btn.addEventListener('click', function () {
        const isCurrentlyCollapsed = section.classList.contains('analytics-collapsed');
        setHidden(!isCurrentlyCollapsed, true);
    });
})();
</script>
<?= $this->endSection() ?>
