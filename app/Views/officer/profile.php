<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>My Profile<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <?php
        $mi = !empty($officer['middle_initial']) ? trim((string) $officer['middle_initial']) : '';
        if ($mi !== '' && strlen($mi) === 1) {
            $mi .= '.';
        }
        
        $fullName = trim((string) (($officer['firstname'] ?? '') . ' ' . ($mi !== '' ? $mi . ' ' : '') . ($officer['lastname'] ?? '')));
        $displayName = $fullName !== '' ? $fullName : (string) ($officer['username'] ?? 'Officer');
        $initials = strtoupper(substr((string) $displayName, 0, 1));
        $hasProfileFile = !empty($officer['profile_image']) && is_file(FCPATH . 'uploads/profile/' . $officer['profile_image']);
        $profileImage = $hasProfileFile ? base_url('uploads/profile/' . $officer['profile_image']) : null;
        $totalTickets  = count($records);
        $collectionPct = $totalTickets > 0 ? round(($paidCount / $totalTickets) * 100) : 0;

        // Assigned jurisdiction panel: duty station, station areas and Google Map target
        $station = trim((string) ($assignedStation ?? ''));

        $loggedAreas = [];
        foreach (($areas ?? []) as $areaRow) {
            $areaLabel = trim((string) ($areaRow['location'] ?? ''));
            if ($areaLabel !== '') {
                $loggedAreas[] = [
                    'label' => $areaLabel,
                    'count' => (int) ($areaRow['count'] ?? 0),
                ];
            }
        }

        // Station coverage areas this officer has not logged citations in yet
        $coverageAreas = [];
        foreach (kabankalan_areas() as $barangay) {
            $alreadyLogged = false;
            foreach ($loggedAreas as $loggedArea) {
                if (stripos($loggedArea['label'], $barangay) !== false) {
                    $alreadyLogged = true;
                    break;
                }
            }
            if (! $alreadyLogged) {
                $coverageAreas[] = $barangay . ', Kabankalan City';
            }
        }

        // Area the officer confirmed as their assigned jurisdiction; falls back
        // to their most frequent citation area.
        $confirmedArea = trim((string) ($assignedArea ?? ''));
        $cardLocation  = $confirmedArea !== '' ? $confirmedArea : trim((string) $topLocation);

        $mapQuery = $cardLocation;
        if ($mapQuery === '') {
            $mapQuery = $station !== '' ? $station : 'Kabankalan City, Negros Occidental';
        }
        $selectedArea = $confirmedArea !== '' ? $confirmedArea : $mapQuery;
        $mapSearch = $mapQuery . ', Philippines';
        $mapEmbed  = 'https://www.google.com/maps?q=' . rawurlencode($mapSearch) . '&hl=en&output=embed';
        $mapLink   = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($mapSearch);
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

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Update failed:</strong>
            <ul class="mb-0 mt-1 small">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ═══ HERO BANNER WITH REAL-TIME OPERATIONAL HUD ═════════ -->
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

            <!-- Officer Identity -->
            <div class="profile-hero-info">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h2 class="hero-name mb-0"><?= esc($displayName) ?></h2>
                    <span class="badge bg-white text-primary fw-bold px-2.5 py-1 rounded-pill shadow-sm" style="font-size:0.75rem;">
                        <i class="bi bi-shield-check me-1"></i><?= esc($badgeNumber) ?>
                    </span>
                    <span class="hero-role-badge"><i class="bi bi-person-badge me-1"></i>Traffic Enforcer</span>
                </div>
                <div class="hero-meta">
                    <span><i class="bi bi-person me-1"></i>@<?= esc($officer['username'] ?? '-') ?></span>
                    <span><i class="bi bi-envelope me-1"></i><?= esc($officer['email'] ?? '-') ?></span>
                    <span>
                        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;vertical-align:middle;color:<?= ($officer['status'] ?? '') === 'active' ? '#4ade80' : '#94a3b8' ?>"></i>
                        <?= ucfirst(esc($officer['status'] ?? 'Active')) ?>
                    </span>
                </div>
            </div>

            <!-- Middle: Real-Time Field Status HUD -->
            <div class="hero-duty-hud d-none d-lg-flex ms-lg-auto me-lg-3">
                <div class="hud-status-badge">
                    <span class="live-radar-dot"></span>
                    <span class="fw-bold tracking-wider">ON ACTIVE FIELD DUTY</span>
                </div>
                <div class="hud-time-wrap">
                    <i class="bi bi-clock-history me-1.5 text-warning"></i>
                    <span id="livePstClock" class="fw-bold font-monospace">--:--:-- PST</span>
                </div>
                <div class="hud-sector-text">
                    <i class="bi bi-geo-alt-fill me-1 text-danger"></i>
                    <span>CTRAMO &middot; <?= esc($topLocation) ?></span>
                </div>
            </div>

            <!-- Photo upload actions -->
            <div class="hero-upload-wrap">
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

    <!-- ═══ REAL-TIME OPERATIONAL METRICS STRIP ═════════════════════ -->
    <div class="row g-3 mb-4 premium-reveal" style="--reveal-delay:0.1s">
        <!-- Live Duty Status (opens the duty status switcher) -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 op-status-card is-clickable <?= $dutyStatus === 'On Break' ? 'duty-on-break' : ($dutyStatus === 'Off Duty' ? 'duty-off-duty' : '') ?>" id="dutyStatusCard"
                 role="button" tabindex="0"
                 data-bs-toggle="modal" data-bs-target="#dutyStatusModal" aria-controls="dutyStatusModal"
                 title="Click to change your duty status">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="op-icon-box bg-success-subtle text-success">
                        <i class="bi bi-broadcast fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size:0.68rem; letter-spacing: 0.05em;">Field Status</small>
                        <span class="fw-bold text-dark d-flex align-items-center gap-1.5" style="font-size:0.95rem;">
                            <span class="live-pulse-indicator" id="dutyStatusDot"></span>
                            <span id="dutyStatusValue"><?= esc($dutyStatus) ?></span>
                        </span>
                        <small class="text-muted d-block" style="font-size:0.72rem;">Call Sign: Unit #<?= esc($officer['id']) ?></small>
                    </div>
                    <i class="bi bi-chevron-down op-card-caret text-success"></i>
                </div>
            </div>
        </div>

        <!-- Patrol Jurisdiction (opens the station areas + Google Map modal) -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 op-status-card jurisdiction-card"
                 role="button" tabindex="0"
                 data-bs-toggle="modal" data-bs-target="#jurisdictionModal" aria-controls="jurisdictionModal"
                 title="Click to view assigned station areas and map">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="op-icon-box bg-danger-subtle text-danger">
                        <i class="bi bi-geo-alt-fill fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size:0.68rem; letter-spacing: 0.05em;">Assigned Jurisdiction</small>                        <span class="fw-bold text-dark text-truncate d-block" id="jurisdictionCardValue" style="font-size: 0.95rem; max-width: 100%;" title="<?= esc($cardLocation) ?>">
                            <?= esc($cardLocation) ?>
                        </span>
                        <?php if ($confirmedArea !== ''): ?>
                            <small class="text-success d-block fw-semibold" style="font-size:0.7rem;">
                                <i class="bi bi-check2-circle me-1"></i>Confirmed assigned area
                            </small>
                        <?php else: ?>
                            <small class="text-success d-block fw-semibold" style="font-size:0.7rem;">
                                <i class="bi bi-shield-check me-1"></i>Traffic Safety Guide
                            </small>
                        <?php endif; ?>
                        <small class="text-warning fw-semibold d-none" style="font-size:0.7rem;" id="jurisdictionCardPending">
                            <i class="bi bi-hourglass-split me-1"></i>Not yet confirmed
                        </small>
                    </div>
                    <i class="bi bi-chevron-down jurisdiction-caret text-danger"></i>
                </div>
            </div>
        </div>        <!-- Primary Offense Apprehended (opens the offense breakdown) -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 op-status-card is-clickable" id="offenseBreakdownCard"
                 role="button" tabindex="0"
                 data-bs-toggle="modal" data-bs-target="#offenseBreakdownModal" aria-controls="offenseBreakdownModal"
                 title="Click to see every offense you have handled">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="op-icon-box bg-warning-subtle text-warning-emphasis">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size:0.68rem; letter-spacing: 0.05em;">Top Offense Handled</small>
                        <span class="fw-bold text-dark text-truncate d-block" style="font-size: 0.95rem; max-width: 100%;" title="<?= esc($topOffense) ?>"><?= esc($topOffense) ?></span>
                        <small class="text-muted d-block" style="font-size:0.72rem;">Primary enforcement focus</small>
                    </div>
                    <i class="bi bi-chevron-down op-card-caret text-warning-emphasis"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ ASSIGNED JURISDICTION MODAL: STATION AREAS + GOOGLE MAP ════════
         Sits outside the metrics row: premium-reveal leaves a transform on
         that row, which would trap the modal's fixed positioning and z-index. -->
    <div class="modal fade" id="jurisdictionModal" tabindex="-1" aria-labelledby="jurisdictionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable jurisdiction-dialog">
            <div class="modal-content jurisdiction-panel">
                <div class="modal-header border-bottom border-light">
                    <div class="d-flex justify-content-between align-items-start gap-2 w-100">
                        <div class="min-w-0">
                            <small id="jurisdictionModalLabel" class="text-muted text-uppercase d-block fw-semibold" style="font-size:0.64rem; letter-spacing: 0.05em;">Assigned Duty Station</small>
                            <span class="fw-bold text-dark d-block text-truncate" style="font-size: 0.9rem;" title="<?= esc($station !== '' ? $station : 'CTRAMO Kabankalan') ?>">
                                <i class="bi bi-building me-1 text-danger"></i><?= esc($station !== '' ? $station : 'CTRAMO Kabankalan') ?>
                            </span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-shrink-0">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fw-bold" style="font-size:0.62rem;">
                                <i class="bi bi-shield-check me-1"></i>ASSIGNED
                            </span>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size:0.64rem; letter-spacing: 0.05em;">
                            Areas of Station Assigned
                        </small>
                        <?php if ($confirmedArea !== ''): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold" style="font-size:0.6rem;">
                                <i class="bi bi-check2-circle me-1"></i>CONFIRMED
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="jurisdiction-area-list d-flex flex-column gap-1.5 mb-3" role="list">
                        <?php foreach ($loggedAreas as $areaItem): ?>
                            <button type="button" class="jurisdiction-area-item<?= $areaItem['label'] === $selectedArea ? ' is-active' : '' ?>" role="listitem"
                                    data-area="<?= esc($areaItem['label']) ?>">
                                <span class="text-truncate"><i class="bi bi-geo-alt-fill me-1 text-danger"></i><?= esc($areaItem['label']) ?></span>
                                <span class="area-count"><?= $areaItem['count'] ?> citation<?= $areaItem['count'] === 1 ? '' : 's' ?></span>
                            </button>
                        <?php endforeach; ?>
                        <?php foreach ($coverageAreas as $areaItem): ?>
                            <button type="button" class="jurisdiction-area-item" role="listitem" data-area="<?= esc($areaItem) ?>">
                                <span class="text-truncate"><i class="bi bi-pin-map-fill me-1 text-secondary"></i><?= esc($areaItem) ?></span>
                                <span class="area-count">coverage</span>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="jurisdiction-map-frame rounded-3 border border-light shadow-sm overflow-hidden" style="height: 220px;">
                        <iframe id="jurisdictionMap" title="Assigned jurisdiction map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                src="<?= esc($mapEmbed) ?>"></iframe>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2 mt-2">
                        <small class="text-muted text-truncate" style="font-size: 0.68rem;" id="jurisdictionMapLabel" title="<?= esc($mapSearch) ?>">
                            <i class="bi bi-pin-angle-fill me-1"></i><?= esc($mapSearch) ?>
                        </small>                            <a id="jurisdictionMapLink" href="<?= esc($mapLink) ?>" target="_blank" rel="noopener noreferrer"
                           class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold flex-shrink-0" style="font-size: 0.68rem;">
                            <i class="bi bi-box-arrow-up-right me-1"></i>Google Maps 2026
                        </a>
                    </div>
                </div>

                <div class="modal-footer border-top border-light d-flex justify-content-between align-items-center gap-2">
                    <small class="text-muted text-truncate" style="font-size: 0.7rem;">
                        <i class="bi bi-pin-angle-fill me-1"></i>Selected: <span id="selectedAreaLabel"><?= esc($selectedArea !== '' ? $selectedArea . ', Philippines' : 'none') ?></span>
                    </small>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold flex-shrink-0" id="confirmAreaBtn" title="Confirm this area as your assigned jurisdiction">
                        <i class="bi bi-check2-circle me-1"></i><?= $confirmedArea !== '' ? 'Update Assigned Area' : 'Confirm Assigned Area' ?>
                    </button>
                </div>

                <form id="confirmAreaForm" action="<?= base_url('officer/profile/confirm-area') ?>" method="POST" class="d-none">
                    <?= csrf_field() ?>
                    <input type="hidden" name="assigned_area" id="assignedAreaInput" value="<?= esc($confirmedArea) ?>">
                </form>
            </div>
        </div>
    </div>

    <!-- ═══ DUTY STATUS MODAL: SWITCH THE FIELD STATUS ══════════ -->
    <div class="modal fade" id="dutyStatusModal" tabindex="-1" aria-labelledby="dutyStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered jurisdiction-dialog">
            <div class="modal-content jurisdiction-panel">
                <div class="modal-header border-bottom border-light">
                    <div class="d-flex justify-content-between align-items-start gap-2 w-100">
                        <div class="min-w-0">
                            <small id="dutyStatusModalLabel" class="text-muted text-uppercase d-block fw-semibold" style="font-size:0.64rem; letter-spacing: 0.05em;">Field Status</small>
                            <span class="fw-bold text-dark d-block" style="font-size: 0.9rem;">
                                <i class="bi bi-broadcast me-1 text-success"></i>Set your duty status
                            </span>
                        </div>
                        <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <form id="dutyStatusForm" action="<?= base_url('officer/profile/duty-status') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div class="modal-body">
                        <?php
                        $dutyDescriptions = [
                            'Active Patrol' => 'On the road and writing citations now.',
                            'On Break'      => 'Paused for a moment — not taking assignments.',
                            'Off Duty'      => 'Shift finished for now.',
                        ];
                        ?>
                        <div class="d-flex flex-column gap-2" role="radiogroup" aria-labelledby="dutyStatusModalLabel">
                            <?php foreach ($dutyStatuses as $dutyIndex => $dutyOption): ?>
                            <label class="duty-option<?= $dutyOption === $dutyStatus ? ' is-selected' : '' ?>" for="duty_option_<?= $dutyIndex ?>"
                                   data-status="<?= esc($dutyOption) ?>">
                                <input type="radio" class="d-none" name="duty_status" id="duty_option_<?= $dutyIndex ?>"
                                       value="<?= esc($dutyOption) ?>" <?= $dutyOption === $dutyStatus ? 'checked' : '' ?>>
                                <span class="duty-option-dot duty-dot-<?= strtolower(str_replace(' ', '-', $dutyOption)) ?>"></span>
                                <span class="flex-grow-1 min-w-0">
                                    <span class="fw-bold text-dark d-block" style="font-size:0.88rem;"><?= esc($dutyOption) ?></span>
                                    <small class="text-muted d-block" style="font-size:0.7rem;"><?= esc($dutyDescriptions[$dutyOption] ?? '') ?></small>
                                </span>
                                <i class="bi bi-check2-circle duty-option-check"></i>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted d-block mt-3" style="font-size:0.7rem;">
                            <i class="bi bi-info-circle me-1"></i>Saved to your officer record, so it survives a sign-out.
                        </small>
                    </div>
                    <div class="modal-footer border-top border-light">
                        <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i>Save Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══ OFFENSE BREAKDOWN MODAL: EVERY OFFENSE HANDLED ══════ -->
    <div class="modal fade" id="offenseBreakdownModal" tabindex="-1" aria-labelledby="offenseBreakdownModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable jurisdiction-dialog">
            <div class="modal-content jurisdiction-panel">
                <div class="modal-header border-bottom border-light">
                    <div class="d-flex justify-content-between align-items-start gap-2 w-100">
                        <div class="min-w-0">
                            <small id="offenseBreakdownModalLabel" class="text-muted text-uppercase d-block fw-semibold" style="font-size:0.64rem; letter-spacing: 0.05em;">Top Offense Handled</small>
                            <span class="fw-bold text-dark d-block text-truncate" style="font-size: 0.9rem;" title="<?= esc($topOffense) ?>">
                                <i class="bi bi-exclamation-triangle-fill me-1 text-warning-emphasis"></i><?= esc($topOffense) ?>
                            </span>
                        </div>
                        <button type="button" class="btn-close flex-shrink-0" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size:0.64rem; letter-spacing: 0.05em;">
                            Offenses you have handled
                        </small>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-1 fw-bold" style="font-size:0.6rem;">
                            <?= count($offenseBreakdown) ?> type<?= count($offenseBreakdown) === 1 ? '' : 's' ?>
                        </span>
                    </div>

                    <?php if (empty($offenseBreakdown)): ?>
                        <p class="text-muted mb-0" style="font-size:0.8rem;">
                            <i class="bi bi-inbox me-1"></i>No citations recorded yet — your tally appears here after your first ticket.
                        </p>
                    <?php else: ?>
                        <div class="offense-breakdown-list d-flex flex-column gap-2">
                            <?php foreach ($offenseBreakdown as $offenseIndex => $offenseRow):
                                $offenseCount = (int) $offenseRow['count'];
                                $offenseShare  = $offenseTotal > 0 ? round(($offenseCount * 100) / $offenseTotal, 1) : 0;
                            ?>
                            <div class="offense-breakdown-item<?= $offenseIndex === 0 ? ' is-top' : '' ?>">
                                <div class="d-flex justify-content-between align-items-center gap-2 mb-1">
                                    <span class="fw-semibold text-dark text-truncate" style="font-size:0.82rem;" title="<?= esc($offenseRow['violation_type']) ?>">
                                        <span class="offense-rank">#<?= $offenseIndex + 1 ?></span><?= esc($offenseRow['violation_type']) ?>
                                    </span>
                                    <span class="flex-shrink-0 fw-bold" style="font-size:0.78rem;">
                                        <?= $offenseCount ?> <span class="text-muted fw-semibold"><?= $offenseShare ?>%</span>
                                    </span>
                                </div>
                                <div class="offense-share-track">
                                    <div class="offense-share-fill" style="width: <?= min(100, (int) round($offenseShare)) ?>%;"></div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="modal-footer border-top border-light d-flex justify-content-between align-items-center gap-2">
                    <small class="text-muted text-truncate" style="font-size:0.7rem;">
                        <i class="bi bi-clipboard-check me-1"></i><?= number_format($offenseTotal) ?> infraction<?= $offenseTotal === 1 ? '' : 's' ?> logged in total
                    </small>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold flex-shrink-0" data-bs-dismiss="modal">Done</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ OFFICER CREDENTIALS & PERFORMANCE ROW ═══════════════════ -->
    <div class="row g-4 mb-4">
        <!-- Official CTRAMO Deputized Credential Card -->
        <div class="col-md-6 premium-reveal" style="--reveal-delay:0.15s">
            <div class="card border-0 shadow-sm h-100 rounded-4 enforcer-cred-card">
                <div class="card-header bg-white py-3 border-bottom border-light d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <img src="<?= base_url('img/pic-1.png') ?>" alt="City Seal" style="width: 24px; height: 24px; object-fit: contain;">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">
                                CTRAMO Law Enforcement Credentials
                            </h6>
                            <small class="text-muted" style="font-size: 0.7rem;">C.O. # 2023-006 &middot; Office of the City Mayor</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            <?= esc($badgeNumber) ?>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#editCredentialsModal" title="Edit your credentials or fix errors">
                            <i class="bi bi-pencil-square me-1"></i>Edit
                        </button>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Full Legal Name</small>
                            <span class="fw-bold text-dark fs-6"><?= esc($displayName) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Enforcer Badge Number</small>
                            <span class="fw-bold text-primary"><?= esc($badgeNumber) ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Designation / Title</small>
                            <span class="fw-semibold text-dark">Traffic Law Enforcement Officer II</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Deputation Order</small>
                            <span class="text-dark">Kabankalan Ordinance 2023-006</span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">System Username</small>
                            <span class="fw-semibold text-secondary">@<?= esc($officer['username'] ?? '-') ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Official Email</small>
                            <span class="text-dark"><?= esc($officer['email'] ?? '-') ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Age</small>
                            <span class="text-dark"><?= !empty($officer['age']) ? esc($officer['age']) : '-' ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Address</small>
                            <span class="text-dark"><?= esc($officer['address'] ?? '-') ?></span>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.7rem;">Authority Status</small>
                            <span class="badge <?= ($officer['status'] ?? '') === 'active' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-secondary-subtle text-secondary' ?> rounded-pill px-2.5 py-1">
                                <i class="bi bi-shield-fill-check me-1"></i>Active &amp; Deputized
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Citation Enforcement & Performance Record -->
        <div class="col-md-6 premium-reveal" style="--reveal-delay:0.2s">
            <div class="card border-0 shadow-sm h-100 rounded-4 record-card">
                <div class="card-header bg-white py-3 border-bottom border-light">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.92rem;">
                                <i class="bi bi-card-checklist me-1.5 text-success"></i>Citation Enforcement Record
                            </h6>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">
                                Apprehension logs registered by this officer &middot; <?= esc($enforcementDateNote) ?>
                            </small>
                        </div>
                        <span class="badge bg-light text-muted border border-light-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">
                            <?= $totalTickets ?> Record<?= $totalTickets === 1 ? '' : 's' ?>
                        </span>
                    </div>

                    <!-- Period switch: each window starts from zero on its own -->
                    <div class="cfr-period-switch mt-2" role="group" aria-label="Enforcement record period">
                        <?php
                        $periodOptions = [
                            'week'  => ['label' => 'This Week',  'title' => 'Citations issued this week – starts again from zero every Monday'],
                            'month' => ['label' => 'This Month', 'title' => 'Citations issued this month – starts again from zero on the 1st'],
                            'year'  => ['label' => 'This Year',  'title' => 'Citations issued this year – starts again from zero every January'],
                            'all'   => ['label' => 'All Time',   'title' => 'Lifetime total for this officer – never resets'],
                        ];
                        foreach ($periodOptions as $periodKey => $periodOption):
                            $isActive = $enforcementPeriod === $periodKey;
                        ?>
                            <a href="<?= base_url('officer/profile?period=' . $periodKey) ?>"
                               class="cfr-period-btn<?= $isActive ? ' active' : '' ?>"
                               title="<?= esc($periodOption['title']) ?>"
                               <?= $isActive ? 'aria-current="page"' : '' ?>><?= esc($periodOption['label']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- 4 Metric Tiles -->
                        <div class="row g-3 mb-3">
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-light border border-light-subtle">
                                    <small class="text-muted text-uppercase d-block fw-semibold" style="font-size: 0.68rem;">Total Issued</small>
                                    <span class="fs-4 fw-bold text-dark"><?= $totalTickets ?></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle">
                                    <small class="text-success-emphasis text-uppercase d-block fw-semibold" style="font-size: 0.68rem;">Settled / Paid</small>
                                    <span class="fs-4 fw-bold text-success"><?= $paidCount ?></span>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle">
                                    <small class="text-warning-emphasis text-uppercase d-block fw-semibold" style="font-size: 0.68rem;">Pending Fines</small>
                                    <span class="fs-4 fw-bold text-warning-emphasis"><?= $pendingCount ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Settlement Efficiency Progress Gauge -->
                        <div class="p-3 rounded-3 bg-light-subtle border border-light-subtle mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <span class="fw-semibold text-dark" style="font-size:0.8rem;">
                                    <i class="bi bi-shield-check text-success me-1"></i>Settlement Clearance Rate
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-bold" style="font-size:0.75rem;">
                                    <?= $collectionPct ?>%
                                </span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 99px; background: rgba(226,232,240,0.8);">
                                <div class="progress-bar bg-success" style="width: <?= $collectionPct ?>%; border-radius: 99px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Fines & Action Link -->
                    <div class="pt-3 border-top border-light-subtle d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Total Fines Assessed</small>
                            <span class="fs-5 fw-bold text-primary">₱<?= number_format((float) $totalAmount, 2) ?></span>
                        </div>
                        <a href="<?= base_url('officer/violations') ?>" class="btn btn-outline-primary btn-sm px-3.5 py-1.5 rounded-pill fw-semibold">
                            <i class="bi bi-card-checklist me-1"></i>Open Violations Registry
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Credentials Modal -->
<div class="modal fade" id="editCredentialsModal" tabindex="-1" aria-labelledby="editCredentialsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom border-light p-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle p-2 rounded-3 text-primary">
                        <i class="bi bi-pencil-square fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="editCredentialsModalLabel">Update Official Credentials</h5>
                        <small class="text-muted">Modify your public profile and contact information.</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('officer/profile/update') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">First Name</label>
                            <input type="text" name="firstname" class="form-control form-control-lg rounded-3 fs-6" value="<?= esc($officer['firstname'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Last Name</label>
                            <input type="text" name="lastname" class="form-control form-control-lg rounded-3 fs-6" value="<?= esc($officer['lastname'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark small">Middle Initial</label>
                            <input type="text" name="middle_initial" class="form-control form-control-lg rounded-3 fs-6" value="<?= esc($officer['middle_initial'] ?? '') ?>" maxlength="10">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-dark small">Official Email</label>
                            <input type="email" name="email" class="form-control form-control-lg rounded-3 fs-6" value="<?= esc($officer['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-dark small">Age</label>
                            <input type="number" name="age" class="form-control form-control-lg rounded-3 fs-6" value="<?= esc($officer['age'] ?? '') ?>" min="18" max="120">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-dark small">Address</label>
                            <input type="text" name="address" class="form-control form-control-lg rounded-3 fs-6" value="<?= esc($officer['address'] ?? '') ?>" placeholder="Personal address">
                        </div>
                    </div>
                    
                    <div class="mt-4 p-3 bg-light rounded-3 border border-light-subtle">
                        <div class="d-flex gap-2 text-muted">
                            <i class="bi bi-info-circle-fill"></i>
                            <small>Sensitive fields like <strong>Badge Number</strong> and <strong>Username</strong> are managed by the administration and cannot be changed here.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i>Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    /* Citation enforcement period switch (Week / Month / Year / All Time) */
    .cfr-period-switch {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 3px;
        border-radius: 10px;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .cfr-period-btn {
        padding: 4px 12px;
        border-radius: 8px;
        font-size: 0.74rem;
        font-weight: 600;
        color: #64748b;
        text-decoration: none;
        white-space: nowrap;
        transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }
    .cfr-period-btn:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .cfr-period-btn.active {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.15);
    }
    @media (max-width: 575.98px) {
        .cfr-period-switch { width: 100%; }
        .cfr-period-btn { flex: 1 1 auto; text-align: center; }
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
    background: rgba(4, 47, 34, 0.55);
    border: 1px solid rgba(167, 243, 208, 0.45);
    color: #a7f3d0;
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


/* ── OPERATIONAL FIELD HUD ───────────────────────────────── */
.hero-duty-hud {
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    border-radius: 16px;
    padding: 0.75rem 1.25rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
}
.hud-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.72rem;
    letter-spacing: 0.06em;
    color: #4ade80;
    text-transform: uppercase;
}
.live-radar-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
    animation: radarPulse 1.8s infinite;
}
@keyframes radarPulse {
    0% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
    }
    70% {
        transform: scale(1.1);
        box-shadow: 0 0 0 8px rgba(74, 222, 128, 0);
    }
    100% {
        transform: scale(0.95);
        box-shadow: 0 0 0 0 rgba(74, 222, 128, 0);
    }
}
.hud-time-wrap {
    font-size: 0.88rem;
    color: #ffffff;
    display: flex;
    align-items: center;
}
.hud-sector-text {
    font-size: 0.72rem;
    color: rgba(255, 255, 255, 0.75);
    display: flex;
    align-items: center;
}

/* ── OPERATIONAL STATUS STRIP ────────────────────────────── */
.op-status-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #f1f5f9 !important;
}
.op-status-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06) !important;
}
.op-icon-box {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.live-pulse-indicator {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
    box-shadow: 0 0 6px rgba(16, 185, 129, 0.9);
    animation: radarPulse 1.8s infinite;
}

/* ── JURISDICTION CARD: STATION AREAS + GOOGLE MAP ───── */
.jurisdiction-card {
    cursor: pointer;
}
.jurisdiction-card:focus-visible {
    outline: 2px solid #2563eb;
    outline-offset: 2px;
}
.jurisdiction-caret {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
}
.jurisdiction-card.is-open .jurisdiction-caret {
    transform: rotate(180deg);
}

/* ── CLICKABLE STATUS CARDS: DUTY STATUS + OFFENSE BREAKDOWN ─ */
.op-status-card.is-clickable {
    cursor: pointer;
}
.op-status-card.is-clickable:focus-visible {
    outline: 2px solid #2563eb;
    outline-offset: 2px;
}
.op-card-caret {
    font-size: 0.75rem;
    flex-shrink: 0;
    transition: transform 0.2s ease;
}
.op-status-card.is-open .op-card-caret {
    transform: rotate(180deg);
}

/* The pulse dot follows the saved duty status */
.op-status-card.duty-on-break .live-pulse-indicator {
    background: #f59e0b;
    box-shadow: 0 0 6px rgba(245, 158, 11, 0.9);
}
.op-status-card.duty-off-duty .live-pulse-indicator {
    background: #94a3b8;
    box-shadow: none;
    animation: none;
}

/* Duty status choices */
.duty-option {
    display: flex;
    align-items: center;
    gap: 0.7rem;
    width: 100%;
    padding: 0.7rem 0.8rem;
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    margin-bottom: 0;
    transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease;
}
.duty-option:hover {
    border-color: #bfdbfe;
    background: #f8fafc;
}
.duty-option.is-selected {
    border-color: #2563eb;
    background: #eff6ff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}
.duty-option-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    flex-shrink: 0;
    background: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
}
.duty-dot-on-break {
    background: #f59e0b;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.15);
}
.duty-dot-off-duty {
    background: #94a3b8;
    box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.18);
}
.duty-option-check {
    color: #2563eb;
    opacity: 0;
    transform: scale(0.7);
    transition: opacity 0.18s ease, transform 0.18s ease;
}
.duty-option.is-selected .duty-option-check {
    opacity: 1;
    transform: scale(1);
}

/* Offense breakdown */
.offense-breakdown-item {
    padding: 0.55rem 0.65rem;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    background: #fff;
}
.offense-breakdown-item.is-top {
    border-color: #fde68a;
    background: #fffbeb;
}
.offense-rank {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.5rem;
    height: 1.15rem;
    margin-right: 0.4rem;
    padding: 0 0.35rem;
    border-radius: 6px;
    background: #e2e8f0;
    color: #475569;
    font-size: 0.66rem;
    font-weight: 800;
    vertical-align: middle;
}
.offense-breakdown-item.is-top .offense-rank {
    background: #f59e0b;
    color: #fff;
}
.offense-share-track {
    height: 6px;
    border-radius: 99px;
    background: #eef2f7;
    overflow: hidden;
}
.offense-share-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #3b82f6, #2563eb);
    transition: width 0.4s ease;
}
.offense-breakdown-item.is-top .offense-share-fill {
    background: linear-gradient(90deg, #f59e0b, #d97706);
}

:root[data-theme="dark"] .offense-breakdown-item {
    background: #1e293b;
    border-color: rgba(255, 255, 255, 0.07);
}
:root[data-theme="dark"] .offense-breakdown-item.is-top {
    background: rgba(245, 158, 11, 0.12);
    border-color: rgba(245, 158, 11, 0.35);
}
:root[data-theme="dark"] .offense-share-track {
    background: rgba(255, 255, 255, 0.08);
}
:root[data-theme="dark"] .offense-rank {
    background: rgba(255, 255, 255, 0.1);
    color: #cbd5e1;
}
:root[data-theme="dark"] .duty-option {
    border-color: rgba(255, 255, 255, 0.12);
}
:root[data-theme="dark"] .duty-option:hover {
    background: rgba(255, 255, 255, 0.04);
}
:root[data-theme="dark"] .duty-option.is-selected {
    background: rgba(37, 99, 235, 0.16);
}
.jurisdiction-dialog {
    max-width: 420px;
}
.jurisdiction-panel {
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
}
.jurisdiction-panel .modal-body {
    padding: 1rem 1.1rem 1.1rem;
}
.jurisdiction-area-list {
    max-height: 200px;
    overflow-y: auto;
    overscroll-behavior: contain;
}
.jurisdiction-area-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    width: 100%;
    padding: 0.45rem 0.6rem;
    border: 1px solid #eef2f7;
    border-radius: 10px;
    background: #fff;
    color: #1e293b;
    font-size: 0.76rem;
    text-align: left;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.jurisdiction-area-item:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}
.jurisdiction-area-item.is-active {
    background: #eff6ff;
    border-color: #2563eb;
    color: #1d4ed8;
    font-weight: 600;
}
.jurisdiction-area-item .area-count {
    flex-shrink: 0;
    font-size: 0.63rem;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    border-radius: 999px;
    padding: 0.1rem 0.45rem;
    white-space: nowrap;
}
.jurisdiction-area-item.is-active .area-count {
    color: #1d4ed8;
    background: #dbeafe;
}
.jurisdiction-map-frame iframe {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
}

/* ── ENFORCER CREDENTIAL CARD ────────────────────────────── */
.enforcer-cred-card {
    border-top: 3px solid #2563eb !important;
}
.tct-red-no {
    color: #dc2626 !important;
    font-weight: 800;
    letter-spacing: 0.05em;
}

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

/* ── PRINT ────────────────────────────────────────────────────────
   The banner is a blue gradient carrying white ink. Paper has no
   gradient, so the officer's name, e-mail and duty HUD all printed
   white-on-white — the sheet looked empty. Flatten it for print. */
@media print {
    .sidebar,.sidebar-toggle,.btn,.alert { display:none !important; }
    .main-content { margin-left:0 !important; width:100% !important; padding:0 !important; }

    .profile-hero {
        background: #fff !important;
        border: 1px solid #94a3b8 !important;
        box-shadow: none !important;
        border-radius: 0 !important;
        padding: 10px 0 !important;
    }
    .profile-hero-content {
        gap: 1rem;
    }
    .hero-orb,
    .hero-status-dot,
    .avatar-hover-overlay,
    .hero-upload-wrap,
    .live-pulse-indicator,
    .avatar-upload-spinner,
    .hud-status-badge .live-radar-dot {
        display: none !important;
    }
    .hero-name,
    .hero-meta,
    .hero-meta *,
    .hero-role-badge,
    .hud-status-badge,
    .hud-status-badge *,
    .hud-time-wrap,
    .hud-time-wrap *,
    .hud-sector-text,
    .hud-sector-text *,
    .profile-hero-avatar-initials {
        color: #0f172a !important;
    }
    .hero-role-badge,
    .hero-duty-hud {
        background: #fff !important;
        border: 1px solid #0f172a !important;
        border-radius: 6px !important;
    }
    .avatar-display-container {
        background: #e2e8f0 !important;
        border: 1px solid #94a3b8 !important;
    }
    .op-status-card,
    .record-card {
        border: 1px solid #cbd5e1 !important;
    }
}

/* ── DARK THEME LEGIBILITY ─────────────────────────────────────
   Labels on this page hardcode the light-theme ink colour (.text-dark),
   which disappears on the dark surface, so they follow the layout tokens
   instead. Light theme renders exactly as before. */
:root[data-theme="dark"] .text-dark {
    color: var(--text-strong) !important;
}
/* Long jurisdiction / offence names shrink and ellipsis inside the card
   instead of pushing past its edge. */
.op-status-card .flex-grow-1 {
    min-width: 0;
}
.op-status-card .text-truncate {
    max-width: 100%;
    white-space: normal;
    display: -webkit-box !important;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.25;
}

/* ── MOBILE SPACING: the record card ──────────────────────────────
   On a phone the header badge crowded the title and the registry
   button sat on top of the total-fines figure. Spacing only: each
   part gets its own line and the tiles get a little more room. */
@media (max-width: 575.98px) {
    .record-card > .card-header {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .record-card > .card-body {
        padding: 1rem !important;
    }
    .record-card .row.g-3 {
        --bs-gutter-x: 0.6rem;
        --bs-gutter-y: 0.6rem;
    }
    .record-card .row.g-3 > [class*="col-"] > div {
        padding: 0.65rem 0.5rem !important;
    }
    .record-card .row.g-3 small {
        line-height: 1.2;
    }
    .record-card .row.g-3 .fs-4 {
        font-size: 1.35rem !important;
    }
    .record-card > .card-body > .pt-3.border-top {
        flex-wrap: wrap;
        gap: 0.65rem;
    }
    .record-card > .card-body > .pt-3.border-top .btn {
        width: 100%;
    }
}
/* …but the light tiles on this page stay light, so their ink stays dark. */
:root[data-theme="dark"] .bg-light .text-dark,
:root[data-theme="dark"] .bg-light-subtle .text-dark {
    color: #212529 !important;
}
:root[data-theme="dark"] .op-status-card {
    border-color: var(--border-soft) !important;
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ═══ JURISDICTION AREAS + GOOGLE MAP LOOKUP ══════════════
    const jurMap = document.getElementById('jurisdictionMap');
    const jurMapLink = document.getElementById('jurisdictionMapLink');
    const jurMapLabel = document.getElementById('jurisdictionMapLabel');

    function googleMapsUrl(path, query) {
        return path + encodeURIComponent(query + ', Philippines');
    }

    document.querySelectorAll('.jurisdiction-area-item').forEach(function (item) {
        item.addEventListener('click', function () {
            const area = (item.getAttribute('data-area') || '').trim();
            if (!area) return;

            document.querySelectorAll('.jurisdiction-area-item.is-active').forEach(function (active) {
                active.classList.remove('is-active');
            });
            item.classList.add('is-active');

            if (jurMap) {
                jurMap.src = googleMapsUrl('https://www.google.com/maps?q=', area) + '&hl=en&output=embed';
            }
            if (jurMapLink) {
                jurMapLink.href = googleMapsUrl('https://www.google.com/maps/search/?api=1&query=', area);
            }
            if (jurMapLabel) {
                jurMapLabel.innerHTML = '<i class="bi bi-pin-angle-fill me-1"></i>';
                jurMapLabel.appendChild(document.createTextNode(area + ', Philippines'));
                jurMapLabel.title = area + ', Philippines';
            }
        });
    });

    // Reflect the open modal on the card's chevron
    const jurisdictionModal = document.getElementById('jurisdictionModal');
    const jurisdictionCard = document.querySelector('.jurisdiction-card');
    if (jurisdictionModal && jurisdictionCard) {
        jurisdictionModal.addEventListener('show.bs.modal', function () {
            jurisdictionCard.classList.add('is-open');
        });
        jurisdictionModal.addEventListener('hide.bs.modal', function () {
            jurisdictionCard.classList.remove('is-open');
        });
    }

    // Chevron feedback + keyboard activation for the two clickable status cards
    [['dutyStatusCard', 'dutyStatusModal'], ['offenseBreakdownCard', 'offenseBreakdownModal']].forEach(function (pair) {
        const card  = document.getElementById(pair[0]);
        const modal = document.getElementById(pair[1]);
        if (!card || !modal) return;

        modal.addEventListener('show.bs.modal', function () { card.classList.add('is-open'); });
        modal.addEventListener('hide.bs.modal', function () { card.classList.remove('is-open'); });

        card.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                event.preventDefault();
                card.click();
            }
        });
    });

    // Reopening the duty modal starts from the status actually saved on the
    // record, so a choice made but never saved never sticks around.
    const savedDutyStatus = <?= json_encode($dutyStatus) ?>;
    const dutyStatusModalEl = document.getElementById('dutyStatusModal');
    if (dutyStatusModalEl) {
        dutyStatusModalEl.addEventListener('show.bs.modal', function () {
            document.querySelectorAll('#dutyStatusForm .duty-option').forEach(function (option) {
                const input = option.querySelector('input[type="radio"]');
                const selected = !!input && input.value === savedDutyStatus;
                if (input) input.checked = selected;
                option.classList.toggle('is-selected', selected);
            });
        });

        document.querySelectorAll('#dutyStatusForm .duty-option').forEach(function (option) {
            option.addEventListener('click', function () {
                document.querySelectorAll('#dutyStatusForm .duty-option').forEach(function (other) {
                    other.classList.toggle('is-selected', other === option);
                });
            });
        });
    }

    // ═══ CONFIRM ASSIGNED AREA ══════════════════════════════════
    const initialJurisdictionValue = <?= json_encode($cardLocation) ?>;
    const confirmAreaBtn = document.getElementById('confirmAreaBtn');
    const confirmAreaForm = document.getElementById('confirmAreaForm');
    const assignedAreaInput = document.getElementById('assignedAreaInput');
    const selectedAreaLabel = document.getElementById('selectedAreaLabel');
    const jurisdictionCardValue = document.getElementById('jurisdictionCardValue');
    const jurisdictionCardPending = document.getElementById('jurisdictionCardPending');

    function selectedJurisdictionArea() {
        const active = document.querySelector('#jurisdictionModal .jurisdiction-area-item.is-active');
        return active ? (active.getAttribute('data-area') || '').trim() : '';
    }

    function escapeJurisdictionText(value) {
        return value.replace(/[&<>"']/g, function (ch) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ch];
        });
    }

    // Picking an area immediately reflects it in the Assigned Jurisdiction card;
    // Confirm is what makes it permanent.
    document.querySelectorAll('.jurisdiction-area-item').forEach(function (item) {
        item.addEventListener('click', function () {
            const area = (item.getAttribute('data-area') || '').trim();
            if (!area) return;

            if (selectedAreaLabel) {
                selectedAreaLabel.textContent = area + ', Philippines';
            }

            if (jurisdictionCardValue) {
                jurisdictionCardValue.textContent = area;
                jurisdictionCardValue.title = area;
            }

            if (jurisdictionCardPending) {
                jurisdictionCardPending.classList.toggle('d-none', area === initialJurisdictionValue);
            }
        });
    });

    if (confirmAreaBtn) {
        confirmAreaBtn.addEventListener('click', function () {
            const area = selectedJurisdictionArea();

            if (!area) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No area selected',
                        text: 'Pick an area from the list first.',
                        confirmButtonColor: '#2563eb'
                    });
                } else {
                    window.alert('Please select an area from the list first.');
                }
                return;
            }

            const submitChoice = function () {
                if (!assignedAreaInput || !confirmAreaForm) return;
                assignedAreaInput.value = area;
                confirmAreaForm.submit();
            };

            if (typeof Swal === 'undefined') {
                if (window.confirm('Set "' + area + '" as your assigned jurisdiction?')) {
                    submitChoice();
                }
                return;
            }

            Swal.fire({
                icon: 'question',
                title: 'Confirm assigned area',
                html: 'Set <strong>' + escapeJurisdictionText(area) + '</strong> as your assigned jurisdiction?',
                showCancelButton: true,
                confirmButtonText: 'Yes, confirm area',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#2563eb'
            }).then(function (result) {
                if (result.isConfirmed) {
                    submitChoice();
                }
            });
        });
    }

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

    // ═══ LIVE PHILIPPINE STANDARD TIME (PST) TICKER ═══════════
    function updateLiveClock() {
        const el = document.getElementById('livePstClock');
        if (!el) return;
        const now = new Date();
        const options = {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        try {
            el.textContent = now.toLocaleTimeString('en-US', options) + ' PST';
        } catch (e) {
            el.textContent = now.toLocaleTimeString() + ' PST';
        }
    }
    updateLiveClock();
    setInterval(updateLiveClock, 1000);

});
</script>
<?= $this->endSection() ?>
