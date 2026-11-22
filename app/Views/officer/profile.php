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
        // Issued and clearance figures arrive from the controller, which
        // counts one ticket as one citation and treats cancelled citations
        // as void, exactly like the reports.
        $totalTickets  = (int) ($totalTickets ?? 0);
        $collectionPct = (int) ($collectionPct ?? 0);

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

    <!-- ═══ BREAK TIME NOTIFICATION BANNER ══════════════════════ -->
    <div id="breakTimeBanner" class="break-time-banner mb-4 <?= $dutyStatus === 'On Break' ? '' : 'd-none' ?>" role="alert">
        <div class="break-banner-glow"></div>
        <div class="break-banner-body">
            <div class="break-banner-icon-wrap">
                <i class="bi bi-cup-hot-fill"></i>
                <span class="break-banner-pulse"></span>
            </div>
            <div class="break-banner-info flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-2.5 py-1">
                        <i class="bi bi-pause-circle-fill me-1"></i>BREAK TIME ACTIVE
                    </span>
                    <span class="break-timer-badge font-monospace" id="breakElapsedBadge">
                        <i class="bi bi-stopwatch me-1"></i><span id="breakTimerText">00:00:00</span> elapsed
                    </span>
                </div>
                <div class="break-banner-subtext">
                    Field patrol and citations are temporarily paused. Break started at 
                    <strong id="breakStartedText"><?= !empty($breakStartedAt) ? date('h:i A', strtotime($breakStartedAt)) : date('h:i A') ?></strong>.
                    Remember to resume active patrol when finished.
                </div>
            </div>
            <div class="break-banner-action flex-shrink-0">
                <button type="button" class="btn btn-sm btn-break-resume fw-bold rounded-pill shadow-sm px-3" id="btnResumeActiveDuty">
                    <i class="bi bi-play-circle-fill me-1"></i>Resume Active Patrol
                </button>
            </div>
        </div>
    </div>

    <!-- ═══ HERO BANNER WITH REAL-TIME OPERATIONAL HUD ═════════ -->
    <div class="profile-hero mb-4 premium-reveal" style="--reveal-delay:0.05s">
        <div class="hero-orb hero-orb-1" aria-hidden="true"></div>
        <div class="hero-orb hero-orb-2" aria-hidden="true"></div>
        <div class="hero-orb hero-orb-3" aria-hidden="true"></div>
        <div class="profile-hero-content">
            <!-- Avatar with hover overlay & click to change. Focusable because
                 the overlay label alone is not reachable by keyboard. -->
            <div class="profile-hero-avatar-wrap" id="heroAvatarWrap" role="button" tabindex="0"
                 aria-label="Change profile photo" title="Click or tap to change photo">
                <div class="avatar-display-container" id="avatarDisplayContainer">
                    <?php if ($profileImage): ?>
                        <img src="<?= esc($profileImage) ?>" alt="" class="profile-hero-avatar-img" id="heroAvatarImg" onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='flex';">
                        <span class="profile-hero-avatar-initials" id="heroAvatarInitials" style="display:none;"><?= esc($initials) ?></span>
                    <?php else: ?>
                        <span class="profile-hero-avatar-initials" id="heroAvatarInitials"><?= esc($initials) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Hover/Touch overlay -->
                <label for="hero_photo_input" class="avatar-hover-overlay" title="Upload new photo" aria-hidden="true">
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
                    <span class="badge hero-badge-number fw-bold px-2.5 py-1 rounded-pill shadow-sm" style="font-size:0.75rem;">
                        <i class="bi bi-shield-check me-1"></i><?= esc($badgeNumber) ?>
                    </span>
                    <span class="hero-role-badge"><i class="bi bi-person-badge me-1"></i>Traffic Enforcer</span>
                </div>
                <div class="hero-meta">
                    <span><i class="bi bi-person me-1"></i>@<?= esc($officer['username'] ?? '-') ?></span>
                    <span><i class="bi bi-envelope me-1"></i><?= esc($officer['email'] ?? '-') ?></span>
                    <span>
                        <i class="bi bi-circle-fill me-1" style="font-size:0.5rem;vertical-align:middle;color:<?= ($officer['status'] ?? '') === 'active' ? '#4ade80' : 'var(--text-subtle, #5c6b80)' ?>"></i>
                        <?= ucfirst(esc($officer['status'] ?? 'Active')) ?>
                    </span>
                </div>
            </div>

            <!-- Middle: Real-Time Field Status HUD (Responsive on all screen sizes) -->
            <div class="hero-duty-hud ms-lg-auto me-lg-3" id="heroDutyHud">
                <div class="hud-status-badge <?= $dutyStatus === 'On Break' ? 'hud-badge-break' : ($dutyStatus === 'Off Duty' ? 'hud-badge-off' : '') ?>" id="hudStatusBadge">
                    <span class="live-radar-dot <?= $dutyStatus === 'On Break' ? 'radar-amber' : ($dutyStatus === 'Off Duty' ? 'radar-gray' : '') ?>" id="hudRadarDot"></span>
                    <span class="fw-bold tracking-wider" id="hudStatusLabel">
                        <?= $dutyStatus === 'On Break' ? 'BREAK TIME ACTIVE' : ($dutyStatus === 'Off Duty' ? 'OFF DUTY' : 'ON ACTIVE PATROL') ?>
                    </span>
                </div>
                <div class="hud-time-wrap" id="hudTimeWrap">
                    <i class="bi <?= $dutyStatus === 'On Break' ? 'bi-stopwatch text-warning' : 'bi-clock-history text-warning' ?> me-1.5" id="hudTimeIcon"></i>
                    <span id="livePstClock" class="fw-bold font-monospace">--:--:-- PST</span>
                </div>
                <div class="hud-sector-text" id="hudSectorWrap">
                    <i class="bi <?= $dutyStatus === 'On Break' ? 'bi-pause-circle-fill text-warning' : ($dutyStatus === 'Off Duty' ? 'bi-moon-stars-fill text-secondary' : 'bi-geo-alt-fill text-danger') ?> me-1" id="hudSectorIcon"></i>
                    <span id="hudSectorLabel">
                        <?= $dutyStatus === 'On Break' ? 'Patrol Paused &middot; On Break' : ($dutyStatus === 'Off Duty' ? 'Shift Concluded &middot; Standby' : 'CTRAMO &middot; ' . esc($topLocation)) ?>
                    </span>
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
                 aria-haspopup="dialog"
                 aria-label="Field status: <?= esc($dutyStatus) ?>. Activate to change your duty status."
                 title="Click to change your duty status">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="op-icon-box <?= $dutyStatus === 'On Break' ? 'bg-warning-subtle text-warning-emphasis' : ($dutyStatus === 'Off Duty' ? 'bg-secondary-subtle text-secondary' : 'bg-success-subtle text-success') ?>" id="dutyStatusIconBox">
                        <i class="bi <?= $dutyStatus === 'On Break' ? 'bi-cup-hot-fill' : ($dutyStatus === 'Off Duty' ? 'bi-moon-stars' : 'bi-broadcast') ?> fs-4" id="dutyStatusIcon"></i>
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <small class="text-muted text-uppercase d-block fw-semibold" style="font-size:0.68rem; letter-spacing: 0.05em;">Field Status</small>
                        <span class="fw-bold text-dark d-flex align-items-center gap-2" style="font-size:0.95rem;">
                            <span class="live-pulse-indicator" id="dutyStatusDot"></span>
                            <span id="dutyStatusValue"><?= esc($dutyStatus) ?></span>
                        </span>
                        <small class="text-muted d-block text-truncate" id="dutyStatusSubtitle" style="font-size:0.72rem;">
                            <?php if ($dutyStatus === 'On Break'): ?>
                                Break Time: <span id="cardBreakTimer">00:00:00</span>
                            <?php elseif ($dutyStatus === 'Off Duty'): ?>
                                Shift Finished &middot; Standby
                            <?php else: ?>
                                Active
                            <?php endif; ?>
                        </small>
                    </div>
                    <i class="bi bi-chevron-down op-card-caret <?= $dutyStatus === 'On Break' ? 'text-warning' : ($dutyStatus === 'Off Duty' ? 'text-secondary' : 'text-success') ?>" id="dutyStatusCaret"></i>
                </div>
            </div>
        </div>

        <!-- Patrol Jurisdiction (opens the station areas + Google Map modal) -->
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 op-status-card jurisdiction-card"
                 role="button" tabindex="0"
                 data-bs-toggle="modal" data-bs-target="#jurisdictionModal" aria-controls="jurisdictionModal"
                 aria-haspopup="dialog"
                 aria-label="Assigned jurisdiction: <?= esc($cardLocation) ?>. Activate to view station areas and map."
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
                 aria-haspopup="dialog"
                 aria-label="Top offense handled: <?= esc($topOffense) ?>. Activate to see the offense breakdown."
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
                    <div class="jurisdiction-area-head d-flex justify-content-between align-items-center gap-2 mb-2">
                        <small class="text-muted text-uppercase fw-semibold" style="font-size:0.64rem; letter-spacing: 0.05em;">
                            Areas of Station Assigned
                        </small>
                        <?php if ($confirmedArea !== ''): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 fw-bold" style="font-size:0.6rem;">
                                <i class="bi bi-check2-circle me-1"></i>CONFIRMED
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="jurisdiction-area-search mb-2">
                        <label for="jurisdictionAreaSearch" class="visually-hidden">Search assigned station areas</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-light-subtle">
                                <i class="bi bi-search" aria-hidden="true"></i>
                            </span>
                            <input type="search" class="form-control" id="jurisdictionAreaSearch"
                                   placeholder="Search station areas" autocomplete="off">
                        </div>
                        <small class="text-muted d-block mt-2 d-none" id="jurisdictionAreaSearchEmpty" role="status">
                            No matching station areas.
                        </small>
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

                    <!-- The map grows here instead of handing the area off to a
                         separate Google Maps tab, so the officer never loses the
                         profile they were reading. sandbox deliberately omits
                         allow-popups so the embed cannot spawn a tab either. -->
                    <div class="jurisdiction-map-frame rounded-3 border border-light shadow-sm overflow-hidden" style="height: 220px;">
                        <iframe id="jurisdictionMap" title="Assigned jurisdiction map" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                                sandbox="allow-scripts allow-same-origin allow-forms"
                                data-src="<?= esc($mapEmbed) ?>" src="about:blank"></iframe>
                        <button type="button" class="jurisdiction-map-expand" id="jurisdictionMapToggle"
                                aria-expanded="false" aria-controls="jurisdictionMap" title="Expand the map in this window">
                            <i class="bi bi-arrows-angle-expand" aria-hidden="true"></i>
                            <span class="visually-hidden">Expand map</span>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center gap-2 mt-2">
                        <small class="text-muted text-truncate" style="font-size: 0.68rem;" id="jurisdictionMapLabel" title="<?= esc($mapSearch) ?>">
                            <i class="bi bi-pin-angle-fill me-1"></i><?= esc($mapSearch) ?>
                        </small>
                        <button type="button" id="jurisdictionMapLink"
                                class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold flex-shrink-0" style="font-size: 0.68rem;">
                            <i class="bi bi-arrows-angle-expand me-1" aria-hidden="true"></i><span id="jurisdictionMapLinkText">Expand map</span>
                        </button>
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

    <!-- SweetAlert uses this form only for its CSRF token and endpoint. -->
    <form id="dutyStatusForm" action="<?= base_url('officer/profile/duty-status') ?>" method="POST" class="d-none">
        <?= csrf_field() ?>
    </form>
    <template id="dutyStatusOptionsTemplate">
        <?php
        $dutyDescriptions = [
            'Active Patrol' => 'On the road and writing citations now.',
            'On Break'      => 'Paused for a moment — not taking assignments.',
            'Off Duty'      => 'Shift finished for now.',
        ];
        ?>
        <div class="duty-status-swal-options" role="radiogroup" aria-label="Choose your duty status">
            <?php foreach ($dutyStatuses as $dutyOption): ?>
            <button type="button" class="duty-option" role="radio" aria-checked="false" data-status="<?= esc($dutyOption, 'attr') ?>">
                <span class="duty-option-dot duty-dot-<?= strtolower(str_replace(' ', '-', $dutyOption)) ?>"></span>
                <span class="flex-grow-1 min-w-0">
                    <span class="fw-bold text-dark d-block" style="font-size:0.88rem;"><?= esc($dutyOption) ?></span>
                    <small class="text-muted d-block" style="font-size:0.7rem;"><?= esc($dutyDescriptions[$dutyOption] ?? '') ?></small>
                </span>
                <i class="bi bi-check2-circle duty-option-check"></i>
            </button>
            <?php endforeach; ?>
        </div>
        <small class="duty-status-swal-note">
            <i class="bi bi-info-circle me-1"></i>Saved to your officer record, so it survives a sign-out.
        </small>
    </template>

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
                                        <?= $offenseCount ?>
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
                        <button type="button" class="btn btn-sm btn-outline-primary btn-profile-edit rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#editCredentialsModal" title="Edit your credentials or fix errors">
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
                                <i class="bi bi-card-checklist me-2 text-success"></i>Citation Enforcement Record
                            </h6>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">
                                Apprehension logs registered by this officer &middot; <span id="cfrDateNote"><?= esc($enforcementDateNote) ?></span>
                            </small>
                        </div>
                    </div>

                    <!-- Period switch: each window starts from zero on its own -->
                    <div class="cfr-period-switch mt-2" role="group" aria-label="Enforcement record period">
                        <?php
                        $periodOptions = [
                            'week'  => ['label' => 'This Week',  'title' => 'Citations issued this week, Monday to Friday – starts again from zero every Monday'],
                            'month' => ['label' => 'This Month', 'title' => 'Citations issued this month – starts again from zero on the 1st'],
                            'year'  => ['label' => 'This Year',  'title' => 'Citations issued this year – starts again from zero every January'],
                        ];
                        foreach ($periodOptions as $periodKey => $periodOption):
                            $isActive = $enforcementPeriod === $periodKey;
                        ?>
                            <a href="<?= base_url('officer/profile?period=' . $periodKey) ?>"
                               class="cfr-period-btn<?= $isActive ? ' active' : '' ?>"
                               title="<?= esc($periodOption['title']) ?>"
                               data-cfr-period="<?= esc($periodKey) ?>"
                               <?= $isActive ? 'aria-current="page"' : '' ?>><?= esc($periodOption['label']) ?></a>
                        <?php endforeach; ?>
                        <a href="#cfrCustomRange" id="cfrCustomPill"
                           class="cfr-period-btn<?= $enforcementPeriod === 'custom' ? ' active' : '' ?>"
                           data-bs-toggle="collapse"
                           role="button"
                           aria-expanded="<?= $enforcementPeriod === 'custom' ? 'true' : 'false' ?>"
                           aria-controls="cfrCustomRange"
                           title="Pick any two dates — a specific week, a single month, or a whole year"
                           <?= $enforcementPeriod === 'custom' ? 'aria-current="page"' : '' ?>>Custom Range</a>
                    </div>

                    <!-- Custom range: month dropdowns and two exact dates -->
                    <div class="collapse<?= $enforcementPeriod === 'custom' ? ' show' : '' ?> mt-2.5" id="cfrCustomRange">
                        <div class="p-2.5 rounded-3 bg-light border border-light-subtle">
                            <form method="GET" action="<?= base_url('officer/profile') ?>" id="cfrCustomForm" class="d-flex flex-column gap-2">
                                <input type="hidden" name="period" value="custom">

                                <!-- Month & Year pick a whole month in one move -->
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <!-- Dropdown Months -->
                                    <div class="d-inline-flex align-items-center gap-1.5">
                                        <label for="cfrMonthSelect" class="small text-muted fw-semibold mb-0" style="font-size:0.75rem;">
                                            <i class="bi bi-calendar-month text-primary me-1"></i>Month:
                                        </label>
                                        <select id="cfrMonthSelect" class="form-select form-select-sm" style="font-size:0.78rem; padding:0.25rem 0.55rem; width:auto; min-width:125px;">
                                            <option value="">Choose Month</option>
                                            <option value="01">January</option>
                                            <option value="02">February</option>
                                            <option value="03">March</option>
                                            <option value="04">April</option>
                                            <option value="05">May</option>
                                            <option value="06">June</option>
                                            <option value="07">July</option>
                                            <option value="08">August</option>
                                            <option value="09">September</option>
                                            <option value="10">October</option>
                                            <option value="11">November</option>
                                            <option value="12">December</option>
                                        </select>
                                        <select id="cfrYearSelect" class="form-select form-select-sm" style="font-size:0.78rem; padding:0.25rem 0.45rem; width:76px;">
                                            <?php $curYr = (int)date('Y'); for($y = $curYr; $y >= $curYr - 5; $y--): ?>
                                                <option value="<?= $y ?>" <?= ($y === $curYr) ? 'selected' : '' ?>><?= $y ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>

                                </div>

                                <!-- Bottom Row: Exact Date Pickers (From / To) -->
                                <div class="d-flex align-items-center gap-2 flex-wrap pt-2 border-top border-light-subtle">
                                    <span class="small text-muted fw-semibold me-1" style="font-size:0.75rem;">
                                        <i class="bi bi-calendar3 text-secondary me-1"></i>Range:
                                    </span>
                                    <!-- Each label stays welded to its own date box, so a
                                         narrowing card wraps whole pairs instead of
                                         stranding a "To" above the wrong input. -->
                                    <span class="cfr-date-pair">
                                        <label class="small text-muted fw-semibold mb-0" for="cfrStartDate" style="font-size:0.75rem;">From</label>
                                        <input type="date" name="start_date" id="cfrStartDate" class="form-control form-control-sm" style="max-width: 145px; font-size:0.78rem;"
                                               value="<?= esc($enforcementStart ?? '') ?>" required>
                                    </span>
                                    <span class="cfr-date-pair">
                                        <label class="small text-muted fw-semibold mb-0" for="cfrEndDate" style="font-size:0.75rem;">To</label>
                                        <input type="date" name="end_date" id="cfrEndDate" class="form-control form-control-sm" style="max-width: 145px; font-size:0.78rem;"
                                               value="<?= esc($enforcementEnd ?? '') ?>" required>
                                    </span>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-xs" style="font-size:0.78rem;">
                                        <i class="bi bi-funnel me-1"></i>Apply
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <!-- Live KPI tiles: figures count up in place on load and on
                             every period switch — no page reload, no layout shift. -->
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-4">
                                <div class="p-3 rounded-3 bg-light border border-light-subtle cfr-kpi-tile cfr-total-issued-tile">
                                    <div class="cfr-tile-top">
                                        <small class="text-muted text-uppercase d-block fw-semibold cfr-total-issued-label" style="font-size: 0.68rem;">Total Issued</small>
                                        <span class="cfr-tile-icon" aria-hidden="true"><i class="bi bi-receipt-cutoff"></i></span>
                                    </div>
                                    <span class="fs-4 fw-bold text-dark cfr-kpi-number cfr-total-issued-value" id="cfrTotalIssued" data-target="<?= (int) $totalTickets ?>" data-count="0"><?= $totalTickets ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle cfr-kpi-tile cfr-settled-tile">
                                    <div class="cfr-tile-top">
                                        <small class="text-success-emphasis text-uppercase d-block fw-semibold" style="font-size: 0.68rem;">Settled / Paid</small>
                                        <span class="cfr-tile-icon" aria-hidden="true"><i class="bi bi-check2-circle"></i></span>
                                    </div>
                                    <span class="fs-4 fw-bold text-success cfr-kpi-number" id="cfrPaidCount" data-target="<?= (int) $paidCount ?>" data-count="0"><?= $paidCount ?></span>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle cfr-kpi-tile cfr-pending-tile">
                                    <div class="cfr-tile-top">
                                        <small class="text-warning-emphasis text-uppercase d-block fw-semibold" style="font-size: 0.68rem;">Pending Fines</small>
                                        <span class="cfr-tile-icon" aria-hidden="true"><i class="bi bi-hourglass-split"></i></span>
                                    </div>
                                    <span class="fs-4 fw-bold text-warning-emphasis cfr-kpi-number" id="cfrPendingCount" data-target="<?= (int) $pendingCount ?>" data-count="0"><?= $pendingCount ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Settlement Efficiency Progress Gauge -->
                        <div class="p-3 rounded-3 bg-light-subtle border border-light-subtle mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1.5">
                                <span class="fw-semibold text-dark" style="font-size:0.8rem;">
                                    <i class="bi bi-shield-check text-success me-2"></i>Settlement Clearance Rate
                                </span>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-bold cfr-pct-badge" style="font-size:0.75rem;" id="cfrPct" data-target="<?= (int) $collectionPct ?>" data-count="0"><?= $collectionPct ?>%</span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 99px; background: rgba(226,232,240,0.8);">
                                <div class="progress-bar cfr-pct-bar" id="cfrPctBar" style="width: <?= $collectionPct ?>%; border-radius: 99px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Fines & Action Link -->
                    <div class="pt-3 border-top border-light-subtle d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.68rem;">Total Fines</small>
                            <span class="fs-5 fw-bold text-primary cfr-kpi-number" id="cfrTotalAmount" data-target="<?= (float) $totalAmount ?>" data-count="0">₱<?= number_format((float) $totalAmount, 2) ?></span>
                        </div>
                        <a href="<?= base_url('officer/violations') ?>" class="btn btn-outline-primary btn-profile-registry btn-sm px-3.5 py-1.5 rounded-pill fw-semibold">
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
    .btn-profile-edit,
    .btn-profile-registry {
        color: var(--brand-primary) !important;
        border-color: var(--brand-primary) !important;
    }
    .btn-profile-edit:hover,
    .btn-profile-edit:focus-visible,
    .btn-profile-registry:hover,
    .btn-profile-registry:focus-visible {
        color: #ffffff !important;
        background: var(--brand-secondary) !important;
        border-color: var(--brand-secondary) !important;
    }

    /* Citation enforcement period switch (Week / Month / Year / Custom Range) */
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

    /* While a new period is loading the card keeps its current figures and only
       dims very slightly, so the switch never looks like a broken click. */
    .record-card.cfr-refreshing .card-body {
        opacity: .72;
        transition: opacity .15s ease;
    }
    .record-card.cfr-refreshing .cfr-period-btn.active {
        opacity: .7;
    }

    /* A date label travels with its own box, so narrowing the card wraps whole
       pairs instead of stranding a "To" above the wrong input. */
    .cfr-date-pair {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        white-space: nowrap;
    }

    @media (max-width: 575.98px) {
        .cfr-period-switch { width: 100%; }
        .cfr-period-btn { flex: 1 1 auto; text-align: center; }

        /* Phones: the dropdowns and the date boxes each get their own line
           instead of being squeezed against a label. */
        #cfrCustomRange form > div {
            flex-direction: column;
            align-items: stretch !important;
            row-gap: 0.5rem;
        }
        #cfrCustomRange form > div > div {
            width: 100%;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        #cfrCustomRange form > div .form-select { min-width: 0 !important; }
        #cfrCustomRange #cfrMonthSelect { flex: 1 1 auto; width: 100% !important; }
        #cfrCustomRange .cfr-date-pair { width: 100%; }
        #cfrCustomRange .cfr-date-pair .form-control { flex: 1 1 auto; max-width: none !important; }
        #cfrCustomRange form > div > button {
            width: 100%;
            margin-left: 0;
        }
    }
/* ── HERO ─────────────────────────────────────────────────── */
.profile-hero {
    position: relative;
    border-radius: 22px;
    overflow: hidden;
    padding: 2.2rem 2rem;
    /* Layered light source + on-brand blue ramp: the flat gradient read as a
       solid block, while the soft top-left highlight gives the panel depth
       without adding a moving element. */
    background:
        radial-gradient(900px 340px at 8% -25%, rgba(147, 197, 253, 0.5), transparent 65%),
        linear-gradient(120deg, #1e3a8a 0%, #2563eb 48%, #3b82f6 100%);
    box-shadow: 0 18px 40px -14px rgba(30, 58, 138, 0.55), 0 4px 12px rgba(15, 23, 42, 0.12);
    border: 1px solid rgba(255, 255, 255, 0.18);
    isolation: isolate;
}
.profile-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 0;
    pointer-events: none;
    background: linear-gradient(112deg, rgba(255, 255, 255, 0.10), rgba(255, 255, 255, 0) 38%);
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
    box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.18);
}
.dot-active  {
    background: #34d399;
    box-shadow: 0 0 0 4px rgba(52, 211, 153, 0.25), 0 0 12px rgba(52, 211, 153, 0.9);
}
.dot-inactive { background: var(--text-subtle, #5c6b80); }

/* Keyboard parity for the avatar and the two upload actions. These are
   labels/divs rather than native buttons, so the layout's global
   :focus-visible rule does not cover them on its own. */
.profile-hero-avatar-wrap:focus-visible,
.avatar-hover-overlay:focus-visible,
.btn-hero-upload:focus-visible,
.btn-hero-remove:focus-visible {
    outline: 3px solid #ffffff !important;
    outline-offset: 3px;
}
.hero-name {
    font-size: 1.7rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -0.03em;
}
.hero-badge-number {
    /* Brand-tinted chip: the enforcer colour identity stays visible on the
       banner instead of reading as another white frosted surface. */
    background: var(--brand-tint-100);
    border: 1px solid var(--brand-tint-300);
    color: var(--brand-800);
}
.hero-role-badge {
    display: inline-flex;
    align-items: center;
    white-space: nowrap;
    background: rgba(6, 78, 59, 0.45);
    border: 1px solid rgba(110, 231, 183, 0.55);
    color: #d1fae5;
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
    /* Neutral glass with a red icon keeps the destructive meaning while
       lifting label contrast over the blue banner. */
    background: rgba(255, 255, 255, 0.12);
    backdrop-filter: blur(6px);
    -webkit-backdrop-filter: blur(6px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 12px;
    padding: 0.55rem 1rem;
    cursor: pointer;
    transition: background 0.22s ease, border-color 0.22s ease, transform 0.22s ease, box-shadow 0.22s ease;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
}
.btn-hero-remove i { color: #fecaca; }
.btn-hero-remove:hover,
.btn-hero-remove:focus-visible {
    background: rgba(225, 29, 72, 0.85);
    border-color: rgba(254, 202, 202, 0.6);
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(225, 29, 72, 0.35);
}
.btn-hero-remove:hover i,
.btn-hero-remove:focus-visible i { color: #ffffff; }
.hero-orb {
    position: absolute;
    border-radius: 50%;
    pointer-events: none;
    z-index: 1;
    filter: blur(55px);
    opacity: 0.38;
}
.hero-orb-1 { width:240px;height:240px;background:#60a5fa;top:-70px;right:8%;animation:orbf 14s ease-in-out infinite alternate; }
.hero-orb-2 { width:160px;height:160px;background:#38bdf8;bottom:-55px;right:28%;animation:orbf 18s ease-in-out infinite alternate-reverse; }
.hero-orb-3 { width:120px;height:120px;background:#93c5fd;top:20%;right:4%;animation:orbf 11s ease-in-out infinite alternate; }
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
    background: #ca8a04;
    box-shadow: 0 0 6px rgba(202, 138, 4, 0.8);
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


/* ── BREAK TIME NOTIFICATION BANNER ──────────────────────── */
.break-time-banner {
    position: relative;
    background: linear-gradient(135deg, rgba(254, 243, 199, 0.96) 0%, rgba(253, 230, 138, 0.92) 100%);
    border: 1.5px solid #eab308;
    border-radius: 18px;
    padding: 1.15rem 1.4rem;
    box-shadow: 0 10px 25px -5px rgba(202, 138, 4, 0.28), 0 8px 10px -6px rgba(202, 138, 4, 0.2);
    overflow: hidden;
    animation: slideInDown 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    transition: all 0.3s ease;
}
:root[data-theme="dark"] .break-time-banner {
    background: linear-gradient(135deg, rgba(120, 53, 15, 0.45) 0%, rgba(180, 83, 9, 0.4) 100%);
    border-color: rgba(202, 138, 4, 0.65);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.55);
}
.break-banner-body {
    position: relative;
    z-index: 2;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 1.25rem;
}
.break-banner-icon-wrap {
    position: relative;
    width: 48px;
    height: 48px;
    border-radius: 14px;
    background: #ca8a04;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    box-shadow: 0 4px 14px rgba(202, 138, 4, 0.45);
    flex-shrink: 0;
}
.break-banner-pulse {
    position: absolute;
    inset: -3px;
    border-radius: 16px;
    border: 2px solid #ca8a04;
    opacity: 0.8;
    animation: pingBreak 2s cubic-bezier(0, 0, 0.2, 1) infinite;
}
@keyframes pingBreak {
    75%, 100% {
        transform: scale(1.22);
        opacity: 0;
    }
}
.break-timer-badge {
    background: rgba(15, 23, 42, 0.08);
    border: 1px solid rgba(15, 23, 42, 0.12);
    padding: 0.2rem 0.65rem;
    border-radius: 99px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #92400e;
}
:root[data-theme="dark"] .break-timer-badge {
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(255, 255, 255, 0.2);
    color: #fde68a;
}
.break-banner-subtext {
    font-size: 0.85rem;
    color: #78350f;
    line-height: 1.45;
}
:root[data-theme="dark"] .break-banner-subtext {
    color: #fef3c7;
}
.btn-break-resume {
    background: #ffffff;
    color: #047857;
    border: 1.5px solid #10b981;
    font-size: 0.85rem;
    padding: 0.5rem 1.15rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}
.btn-break-resume:hover {
    background: #10b981;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
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
    transition: all 0.25s ease;
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
.hud-status-badge.hud-badge-break {
    color: #eab308;
}
.hud-status-badge.hud-badge-off {
    color: #94a3b8;
}
.live-radar-dot {
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: #4ade80;
    box-shadow: 0 0 0 0 rgba(74, 222, 128, 0.7);
    animation: radarPulse 1.8s infinite;
}
.live-radar-dot.radar-amber {
    background: #ca8a04;
    box-shadow: 0 0 0 0 rgba(202, 138, 4, 0.7);
    animation: radarPulseAmber 1.8s infinite;
}
@keyframes radarPulseAmber {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(202, 138, 4, 0.7); }
    70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(202, 138, 4, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(202, 138, 4, 0); }
}
.live-radar-dot.radar-gray {
    background: #94a3b8;
    box-shadow: none;
    animation: none;
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
.op-status-card .op-icon-box,
.op-status-card .live-pulse-indicator {
    transition: background-color 0.24s ease, color 0.24s ease, box-shadow 0.24s ease, transform 0.24s ease;
}
.op-status-card.is-status-changing #dutyStatusValue,
.op-status-card.is-status-changing #dutyStatusSubtitle,
.op-status-card.is-status-changing #dutyStatusIconBox {
    animation: dutyStatusChangeIn 0.32s cubic-bezier(0.2, 0.7, 0.2, 1) both;
}
@keyframes dutyStatusChangeIn {
    from { opacity: 0.55; transform: translateY(4px); }
    to { opacity: 1; transform: translateY(0); }
}
.swal2-popup.swal2-toast.duty-status-update-toast.swal2-show {
    animation: dutyStatusToastIn 0.4s cubic-bezier(0.2, 0.7, 0.2, 1) forwards !important;
}
.swal2-popup.swal2-toast.duty-status-update-toast.swal2-hide {
    animation: dutyStatusToastOut 0.24s ease-in forwards !important;
}
@keyframes dutyStatusToastIn {
    from { opacity: 0; transform: translate3d(16px, -5px, 0) scale(0.98); }
    to { opacity: 1; transform: translate3d(0, 0, 0) scale(1); }
}
@keyframes dutyStatusToastOut {
    to { opacity: 0; transform: translate3d(10px, -3px, 0) scale(0.99); }
}
@media (prefers-reduced-motion: reduce) {
    .op-status-card.is-status-changing #dutyStatusValue,
    .op-status-card.is-status-changing #dutyStatusSubtitle,
    .op-status-card.is-status-changing #dutyStatusIconBox,
    .swal2-popup.swal2-toast.duty-status-update-toast.swal2-show,
    .swal2-popup.swal2-toast.duty-status-update-toast.swal2-hide {
        animation: none !important;
    }
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
    width: 9px;
    height: 9px;
    flex: 0 0 9px;
    border-radius: 50%;
    background: #10b981;
    display: inline-block;
    border: 1px solid #047857;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.16), 0 0 7px rgba(16, 185, 129, 0.8);
    animation: radarPulse 1.8s infinite;
}

/* ── JURISDICTION CARD: STATION AREAS + GOOGLE MAP ───── */
.jurisdiction-card {
    cursor: pointer;
}
.jurisdiction-card:focus-visible {
    outline: 2px solid #7c3aed;
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
    outline: 2px solid #7c3aed;
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
.op-status-card.is-saving {
    cursor: progress;
    opacity: 0.8;
}

/* The pulse dot follows the saved duty status */
.op-status-card.duty-on-break .live-pulse-indicator {
    background: #ca8a04;
    box-shadow: 0 0 6px rgba(202, 138, 4, 0.9);
}
.op-status-card.duty-off-duty .live-pulse-indicator {
    background: var(--text-subtle, #5c6b80);
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
    transition: border-color 0.18s ease, background-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
}
button.duty-option {
    font: inherit;
    color: inherit;
    text-align: left;
    background: #ffffff;
}
.duty-option:hover {
    border-color: #ddd6fe;
    background: #f8fafc;
    transform: translateY(-1px);
}
.duty-option.is-selected {
    border-color: #7c3aed;
    background: #f5f3ff;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.12);
}
.duty-option.is-selected .fw-bold {
    color: #6d28d9 !important;
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
    background: #ca8a04;
    box-shadow: 0 0 0 4px rgba(202, 138, 4, 0.15);
}
.duty-dot-off-duty {
    background: var(--text-subtle, #5c6b80);
    box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.18);
}
.duty-option-check {
    color: #7c3aed;
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
    border-color: #eab308;
    background: #fef9c3;
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
    background: #ca8a04;
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
    background: linear-gradient(90deg, #8b5cf6, #7c3aed);
    transition: width 0.4s ease;
}
.offense-breakdown-item.is-top .offense-share-fill {
    background: linear-gradient(90deg, #eab308, #ca8a04);
}

:root[data-theme="dark"] .offense-breakdown-item {
    background: #1e293b;
    border-color: rgba(255, 255, 255, 0.07);
}
:root[data-theme="dark"] .offense-breakdown-item.is-top {
    background: rgba(202, 138, 4, 0.12);
    border-color: rgba(202, 138, 4, 0.35);
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
    background: rgba(124, 58, 237, 0.16);
}
.jurisdiction-dialog {
    max-width: 600px;
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
.swal2-popup.duty-status-swal {
    max-width: 430px !important;
    padding: 1.2rem 1rem 0.9rem !important;
    border-radius: 20px !important;
}
.swal2-popup.duty-status-swal .duty-status-swal-title {
    display: flex !important;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.2rem;
    width: 100%;
    padding: 0 0.1rem 0.8rem !important;
    border-bottom: 1px solid #e2e8f0;
    text-align: left !important;
}
.duty-status-title-kicker {
    color: #64748b;
    font-size: 0.64rem;
    font-weight: 700;
    letter-spacing: 0.06em;
}
.duty-status-title-main {
    color: #0f172a;
    font-size: 0.9rem;
    font-weight: 800;
}
.duty-status-title-main i {
    color: #7c3aed;
    margin-right: 0.2rem;
}
.swal2-popup.duty-status-swal .duty-status-swal-html {
    margin: 0.75rem 0 0 !important;
    text-align: left !important;
}
.duty-status-swal-options {
    display: flex;
    flex-direction: column;
    gap: 0.45rem;
}
.swal2-popup.duty-status-swal .duty-option {
    min-height: 54px;
    padding: 0.6rem 0.7rem;
    border-radius: 12px;
}
.swal2-popup.duty-status-swal .duty-option:focus-visible {
    outline: 3px solid rgba(124, 58, 237, 0.25);
    outline-offset: 2px;
}
.duty-status-swal-note {
    display: block;
    margin-top: 0.8rem;
    color: #64748b;
    font-size: 0.7rem;
    text-align: left;
}
.swal2-popup.duty-status-swal .duty-status-swal-actions {
    width: calc(100% + 2rem) !important;
    margin: 0.9rem -1rem -0.9rem !important;
    padding: 0.7rem 1rem !important;
    justify-content: flex-end !important;
    gap: 0.5rem !important;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}
.swal2-popup.duty-status-swal .duty-status-swal-confirm {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%) !important;
    box-shadow: 0 4px 12px rgba(124, 58, 237, 0.28) !important;
}
.swal2-popup.duty-status-swal .duty-status-swal-confirm:hover {
    box-shadow: 0 6px 16px rgba(124, 58, 237, 0.38) !important;
}
.swal2-popup.duty-status-swal .duty-status-swal-cancel {
    background: #f1f5f9 !important;
    border: 1px solid #cbd5e1 !important;
    color: #334155 !important;
}
:root[data-theme="dark"] .swal2-popup.duty-status-swal .duty-status-swal-title {
    border-color: #334155;
}
:root[data-theme="dark"] .duty-status-title-main {
    color: #f8fafc;
}
:root[data-theme="dark"] .swal2-popup.duty-status-swal .duty-option {
    background: #1e293b;
    border-color: #475569;
}
:root[data-theme="dark"] .swal2-popup.duty-status-swal .duty-option .text-dark {
    color: #f8fafc !important;
}
:root[data-theme="dark"] .swal2-popup.duty-status-swal .duty-option.is-selected {
    background: rgba(124, 58, 237, 0.18);
}
:root[data-theme="dark"] .duty-status-swal-note {
    color: #94a3b8;
}
:root[data-theme="dark"] .swal2-popup.duty-status-swal .duty-status-swal-actions {
    background: #172033;
    border-color: #334155;
}
:root[data-theme="dark"] .swal2-popup.duty-status-swal .duty-status-swal-cancel {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #e2e8f0 !important;
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
    background: #f5f3ff;
    border-color: #7c3aed;
    color: #6d28d9;
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
    color: #6d28d9;
    background: #ede9fe;
}
.jurisdiction-map-frame {
    position: relative;
}
.jurisdiction-map-frame iframe {
    display: block;
    width: 100%;
    height: 100%;
    border: 0;
}
/* Expand control floats over the map's own chrome (its links sit top-left). */
.jurisdiction-map-expand {
    position: absolute;
    top: 8px;
    right: 8px;
    z-index: 2;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    padding: 0;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.96);
    color: #1e293b;
    font-size: 0.78rem;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.16);
    transition: background 0.18s ease, transform 0.18s ease;
}
.jurisdiction-map-expand:hover {
    background: #ffffff;
    transform: translateY(-1px);
}
/* Expanded: the map takes the modal's room and the area list steps aside,
   keeping the officer on this page with a standard-size map. */
.jurisdiction-panel.is-map-expanded .jurisdiction-area-head,
.jurisdiction-panel.is-map-expanded .jurisdiction-area-search,
.jurisdiction-panel.is-map-expanded .jurisdiction-area-list {
    display: none !important;
}
.jurisdiction-panel.is-map-expanded .jurisdiction-map-frame {
    height: min(62vh, 460px) !important;
}
.jurisdiction-panel.is-map-expanded #jurisdictionMapToggle {
    background: #f5f3ff;
    border-color: #ddd6fe;
    color: #6d28d9;
}
:root[data-theme="dark"] .jurisdiction-map-expand {
    background: rgba(30, 41, 59, 0.96);
    border-color: rgba(148, 163, 184, 0.35);
    color: #e2e8f0;
}

/* ── ENFORCER CREDENTIAL CARD ────────────────────────────── */
.enforcer-cred-card {
    border-top: 3px solid var(--brand-primary) !important;
}
.tct-red-no {
    color: #dc2626 !important;
    font-weight: 800;
    letter-spacing: 0.05em;
}

/* ── RESPONSIVE ───────────────────────────────────────────── */
/* Laptop widths with the rail still open: the name block, HUD and upload
   actions used to squeeze into one line and clip the trailing button. Give
   the actions their own row and let the HUD keep its breathing room. */
@media (min-width: 992px) and (max-width: 1199.98px) {
    .profile-hero-content { gap: 1.1rem 1.25rem; }
    .hero-name { font-size: 1.45rem; }
    .hero-meta { gap: 0.7rem; font-size: 0.8rem; }
    .hero-duty-hud { margin-left: auto !important; margin-right: 0 !important; }
    .hero-upload-wrap {
        flex: 0 0 100%;
        margin-top: 0.25rem;
    }
    .hero-upload-wrap .d-flex { justify-content: flex-start !important; }
    .hero-upload-wrap small { text-align: left !important; }

    /* A half-width credentials card cannot fit its title, subtitle, badge
       and Edit button on one row without breaking the title into three
       lines. Stack the actions underneath instead. */
    .enforcer-cred-card > .card-header {
        flex-direction: column;
        align-items: stretch !important;
        gap: 0.75rem;
    }
    .enforcer-cred-card > .card-header > .d-flex:first-child,
    .enforcer-cred-card > .card-header > .d-flex:last-child {
        width: 100%;
        min-width: 0;
    }
    .enforcer-cred-card > .card-header > .d-flex:last-child {
        justify-content: space-between;
    }
}

@media (max-width: 991px) {
    .profile-hero-content {
        align-items: stretch;
    }
    .hero-duty-hud {
        width: 100%;
        margin: 0.75rem 0 0 0 !important;
        flex-direction: row;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 0.75rem;
    }
    .hero-upload-wrap {
        width: 100%;
        margin-top: 0.5rem;
    }
}

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

@media (max-width: 576px) {
    .profile-hero-info > .d-flex {
        row-gap: 0.55rem;
    }
    .hero-role-badge {
        flex: 0 0 auto;
        font-size: 0.7rem;
        padding: 0.3rem 0.65rem;
    }
    .hero-duty-hud {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.4rem;
        padding: 0.75rem 1rem;
    }
    .break-banner-body {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.85rem;
    }
    .break-banner-action {
        width: 100%;
    }
    .btn-break-resume {
        width: 100%;
        justify-content: center;
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
        border: 1px solid var(--text-subtle, #5c6b80) !important;
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
        border: 1px solid var(--text-subtle, #5c6b80) !important;
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
:root[data-bs-theme="dark"] .cfr-total-issued-tile {
    background: #1e293b !important;
    border-color: #475569 !important;
}
:root[data-bs-theme="dark"] .cfr-total-issued-tile small {
    color: #cbd5e1 !important;
}
:root[data-bs-theme="dark"] #cfrTotalIssued {
    color: var(--brand-tint-300) !important;
}
.cfr-total-issued-tile {
    border-color: rgba(var(--brand-rgb), .22) !important;
    box-shadow: 0 2px 8px rgba(var(--brand-rgb), .06);
}
.cfr-kpi-tile {
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
}
.cfr-settled-tile {
    box-shadow: 0 2px 8px rgba(25, 135, 84, .06);
}
.cfr-pending-tile {
    box-shadow: 0 2px 8px rgba(180, 120, 0, .06);
}
.cfr-total-issued-label,
.cfr-total-issued-value {
    color: var(--brand-primary) !important;
    transition: color .2s ease;
}
@media (hover: hover) and (prefers-reduced-motion: no-preference) {
    .cfr-total-issued-tile:hover {
        border-color: rgba(var(--brand-rgb), .45) !important;
        box-shadow: 0 5px 14px rgba(var(--brand-rgb), .12);
        transform: translateY(-1px);
    }
    .cfr-settled-tile:hover {
        border-color: rgba(25, 135, 84, .45) !important;
        box-shadow: 0 5px 14px rgba(25, 135, 84, .12);
        transform: translateY(-1px);
    }
    .cfr-pending-tile:hover {
        border-color: rgba(180, 120, 0, .45) !important;
        box-shadow: 0 5px 14px rgba(180, 120, 0, .12);
        transform: translateY(-1px);
    }
}
@media (prefers-reduced-motion: reduce) {
    .cfr-kpi-tile {
        transition: none;
    }
}
:root[data-bs-theme="dark"] .cfr-total-issued-tile {
    border-color: rgba(var(--brand-rgb), .38) !important;
    box-shadow: 0 2px 8px rgba(var(--brand-rgb), .1);
}
:root[data-bs-theme="dark"] .cfr-total-issued-label,
:root[data-bs-theme="dark"] #cfrTotalIssued {
    color: var(--brand-tint-300) !important;
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
    .enforcer-cred-card > .card-header {
        flex-direction: column;
        align-items: stretch !important;
        gap: 0.75rem;
    }
    .enforcer-cred-card > .card-header > .d-flex:first-child {
        width: 100%;
        min-width: 0;
        align-items: flex-start !important;
    }
    .enforcer-cred-card > .card-header > .d-flex:first-child > div {
        min-width: 0;
    }
    .enforcer-cred-card > .card-header h6 {
        font-size: 0.9rem !important;
        line-height: 1.25;
    }
    .enforcer-cred-card > .card-header > .d-flex:last-child {
        width: 100%;
        justify-content: space-between;
    }
    .enforcer-cred-card > .card-header .badge {
        max-width: calc(100% - 5.5rem);
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .enforcer-cred-card > .card-body {
        padding: 1rem !important;
    }

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

/* ── MOTION & TOUCH SAFETY NET ────────────────────────────────
   The layout covers the shell, but this page adds its own orbs, radar
   pulses and tile transitions. Collapse all of them when the user asks
   for reduced motion, and keep the primary actions finger-sized. */
@media (prefers-reduced-motion: reduce) {
    .profile-hero *,
    .op-status-card *,
    .record-card *,
    .enforcer-cred-card *,
    .break-time-banner,
    .break-time-banner * {
        animation-duration: 0.001ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.001ms !important;
        scroll-behavior: auto !important;
    }
}
@media (pointer: coarse) {
    .btn-hero-upload,
    .btn-hero-remove {
        min-height: 44px;
    }
    /* Keep this icon-only control square while enlarging its hit area. */
    .jurisdiction-map-expand {
        width: 44px;
        height: 44px;
    }
}

/* ── LIVE KPI ENHANCEMENTS ────────────────────────────────────────
   Small, GPU-friendly touches that make the record card feel alive:
   icon chips, a sweeping hover sheen, a pulsing ring on the primary
   metric and a progress bar that fills itself on load. None of these
   change any layout dimension, so loading or switching periods never
   shifts the page or delays interaction. */
.cfr-kpi-tile {
    position: relative;
    overflow: hidden;
}
.cfr-tile-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .5rem;
    margin-bottom: .35rem;
}
.cfr-tile-icon {
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    flex-shrink: 0;
    transition: transform .28s cubic-bezier(.2, .8, .2, 1), box-shadow .28s ease;
}
.cfr-total-issued-tile .cfr-tile-icon {
    position: relative;
    background: rgba(var(--brand-rgb), .12);
    color: var(--brand-primary);
}
.cfr-settled-tile .cfr-tile-icon { background: rgba(16, 185, 129, .14); color: #059669; }
.cfr-pending-tile .cfr-tile-icon { background: rgba(217, 119, 6, .14); color: #b45309; }
.cfr-total-issued-tile .cfr-tile-icon::after {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 12px;
    border: 1.5px solid rgba(var(--brand-rgb), .45);
    animation: cfrLiveRing 2.4s ease-out infinite;
    pointer-events: none;
}
@keyframes cfrLiveRing {
    0%   { transform: scale(.86); opacity: .75; }
    100% { transform: scale(1.2); opacity: 0; }
}
.cfr-kpi-number {
    display: block;
    font-variant-numeric: tabular-nums;
    line-height: 1.15;
}
/* A single light sweep on hover — not a continuous repaint. */
.cfr-kpi-tile::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    border-radius: inherit;
    background: linear-gradient(115deg, transparent 32%, rgba(255, 255, 255, .5) 48%, transparent 64%);
    transform: translateX(-120%);
    opacity: 0;
}
@keyframes cfrSheen {
    0%   { transform: translateX(-120%); opacity: 0; }
    35%  { opacity: 1; }
    100% { transform: translateX(120%); opacity: 0; }
}
@keyframes cfrBadgePop {
    0%   { transform: scale(.82); }
    60%  { transform: scale(1.06); }
    100% { transform: scale(1); }
}
@media (hover: hover) and (prefers-reduced-motion: no-preference) {
    .cfr-kpi-tile:hover .cfr-tile-icon {
        transform: translateY(-2px) rotate(-6deg) scale(1.06);
        box-shadow: 0 6px 14px rgba(15, 23, 42, .12);
    }
    .cfr-kpi-tile:hover::before { animation: cfrSheen .75s ease-out; }
    .cfr-pct-badge { animation: cfrBadgePop .45s ease-out both; }
    .op-status-card:hover .op-icon-box { transform: translateY(-2px) rotate(-4deg); }
}
.cfr-pct-bar {
    background: linear-gradient(90deg, #10b981 0%, #059669 100%) !important;
    box-shadow: 0 0 12px rgba(16, 185, 129, .35);
    transition: width .7s cubic-bezier(.2, .8, .2, 1);
    animation: cfrBarReveal .9s cubic-bezier(.2, .8, .2, 1) both;
}
@keyframes cfrBarReveal {
    from { clip-path: inset(0 100% 0 0); }
    to   { clip-path: inset(0 0 0 0); }
}

/* ── LIVE ACCENT ON THE STATUS STRIP ──────────────────────────────
   A thin coloured line draws itself across the top of each status card.
   It animates with transform only (no height/position change), so it
   cannot shift text or interfere with the reload view transition. */
.op-status-card {
    overflow: hidden;
}
.op-status-card::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 22px 22px 0 0;
    background: linear-gradient(90deg, var(--brand-primary), transparent 85%);
    transform: scaleX(0);
    transform-origin: left center;
    animation: cfrAccentDraw .6s cubic-bezier(.2, .8, .2, 1) .1s forwards;
    pointer-events: none;
}
.op-status-card.duty-on-break::after { background: linear-gradient(90deg, #ca8a04, transparent 85%); }
.op-status-card.duty-off-duty::after { background: linear-gradient(90deg, #94a3b8, transparent 85%); }
.op-status-card.jurisdiction-card::after { background: linear-gradient(90deg, #dc2626, transparent 85%); }
@keyframes cfrAccentDraw {
    to { transform: scaleX(1); }
}
.record-card {
    overflow: hidden;
}
.record-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    border-radius: 22px 22px 0 0;
    background: linear-gradient(90deg, #10b981, transparent 85%);
    pointer-events: none;
}
@media (prefers-reduced-motion: reduce) {
    .cfr-pct-bar,
    .cfr-total-issued-tile .cfr-tile-icon::after,
    .op-status-card::after {
        animation: none !important;
    }
    .op-status-card::after { transform: scaleX(1); }
}
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Every Bootstrap icon on this page sits beside visible text, so none of
    // them should be announced as a standalone glyph. Hide the whole set from
    // the accessibility tree in one pass; keep any icon that was explicitly
    // given a label or a role.
    document.querySelectorAll('i.bi, i[class*="bi-"]').forEach(function (icon) {
        if (!icon.hasAttribute('aria-label') && !icon.hasAttribute('role')) {
            icon.setAttribute('aria-hidden', 'true');
        }
    });

    // The avatar overlay is a <label>, which is not focusable on its own.
    // Give the wrapping element keyboard parity with a mouse click.
    const heroAvatarWrap = document.getElementById('heroAvatarWrap');
    const heroPhotoInput = document.getElementById('hero_photo_input');
    if (heroAvatarWrap && heroPhotoInput) {
        heroAvatarWrap.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                event.preventDefault();
                heroPhotoInput.click();
            }
        });
    }

    // ═══ JURISDICTION AREAS + GOOGLE MAP LOOKUP ══════════════
    const jurMap = document.getElementById('jurisdictionMap');
    // Google Maps is heavy third-party content. Hold its URL in a data
    // attribute and only request it when this panel is first opened, so a
    // normal profile load/reload never pays for the iframe.
    let jurMapLoaded = false;
    const initialMapSrc = jurMap ? jurMap.dataset.src : '';
    const jurMapLink = document.getElementById('jurisdictionMapLink');
    const jurMapLabel = document.getElementById('jurisdictionMapLabel');
    const jurMapToggle = document.getElementById('jurisdictionMapToggle');
    const jurMapLinkText = document.getElementById('jurisdictionMapLinkText');
    const jurPanel = document.querySelector('.jurisdiction-panel');

    // The map widens inside this modal instead of opening Google Maps in a new
    // tab, so the officer keeps their profile (and their place) on one page.
    function setMapExpanded(expand) {
        if (!jurPanel || !jurMapToggle) return;
        jurPanel.classList.toggle('is-map-expanded', expand);
        jurMapToggle.setAttribute('aria-expanded', expand ? 'true' : 'false');
        jurMapToggle.setAttribute('title', expand ? 'Shrink the map back into this window' : 'Expand the map in this window');
        const icon = jurMapToggle.querySelector('i');
        if (icon) {
            icon.className = expand ? 'bi bi-arrows-angle-contract' : 'bi bi-arrows-angle-expand';
        }
        if (jurMapLinkText) {
            jurMapLinkText.textContent = expand ? 'Shrink map' : 'Expand map';
        }
        if (jurMapLink) {
            const linkIcon = jurMapLink.querySelector('i');
            if (linkIcon) {
                linkIcon.className = (expand ? 'bi bi-arrows-angle-contract' : 'bi bi-arrows-angle-expand') + ' me-1';
            }
            jurMapLink.setAttribute('aria-expanded', expand ? 'true' : 'false');
        }
        // Recentre the taller frame so the pin is not left off-screen.
        if (expand && jurPanel.scrollIntoView) {
            jurPanel.scrollIntoView({ block: 'nearest' });
        }
    }

    [jurMapToggle, jurMapLink].forEach(function (control) {
        if (!control) return;
        control.addEventListener('click', function (event) {
            event.preventDefault();
            setMapExpanded(!jurPanel.classList.contains('is-map-expanded'));
        });
    });

    // Escape shrinks the map before it closes the whole modal.
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && jurPanel && jurPanel.classList.contains('is-map-expanded')) {
            event.stopPropagation();
            setMapExpanded(false);
        }
    }, true);

    function googleMapsUrl(path, query) {
        return path + encodeURIComponent(query + ', Philippines');
    }

    const jurisdictionAreaSearch = document.getElementById('jurisdictionAreaSearch');
    const jurisdictionAreaSearchEmpty = document.getElementById('jurisdictionAreaSearchEmpty');
    const jurisdictionAreaItems = Array.from(document.querySelectorAll('.jurisdiction-area-item'));

    function filterJurisdictionAreas() {
        const query = (jurisdictionAreaSearch.value || '').trim().toLowerCase();
        let matches = 0;

        jurisdictionAreaItems.forEach(function (item) {
            const area = (item.getAttribute('data-area') || '').toLowerCase();
            const isMatch = area.includes(query);
            item.classList.toggle('d-none', !isMatch);
            if (isMatch) matches++;
        });

        if (jurisdictionAreaSearchEmpty) {
            jurisdictionAreaSearchEmpty.classList.toggle('d-none', query === '' || matches > 0);
        }
    }

    if (jurisdictionAreaSearch) {
        jurisdictionAreaSearch.addEventListener('input', filterJurisdictionAreas);
    }

    jurisdictionAreaItems.forEach(function (item) {
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
            if (jurMap && !jurMapLoaded && initialMapSrc) {
                jurMap.src = initialMapSrc;
                jurMapLoaded = true;
            }
        });
        jurisdictionModal.addEventListener('hide.bs.modal', function () {
            jurisdictionCard.classList.remove('is-open');
        });
        // A div with data-bs-toggle only responds to pointers. Match the
        // other two metric cards so Enter/Space opens the modal too.
        jurisdictionCard.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                event.preventDefault();
                jurisdictionCard.click();
            }
        });
    }

    // Chevron feedback + keyboard activation for the offense card modal.
    [['offenseBreakdownCard', 'offenseBreakdownModal']].forEach(function (pair) {
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

    // ═══ DUTY STATUS & BREAK TIME MANAGER ═══════════════════════
    let savedDutyStatus = <?= json_encode($dutyStatus) ?>;
    let currentBreakStartedAt = <?= json_encode($breakStartedAt) ?>;
    let breakStartTimeMs = <?= !empty($breakStartedAt) ? (strtotime($breakStartedAt) * 1000) : 'null' ?>;
    const serverTimeMs = <?= time() * 1000 ?>;
    const clientSkewMs = Date.now() - serverTimeMs;
    let breakTimerInterval = null;

    function formatTimeAMPM(date) {
        let hours = date.getHours();
        let minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const mins = minutes < 10 ? '0' + minutes : minutes;
        return hours + ':' + mins + ' ' + ampm;
    }

    function formatElapsed(totalSec) {
        if (totalSec < 0) totalSec = 0;
        const hrs = Math.floor(totalSec / 3600);
        const mins = Math.floor((totalSec % 3600) / 60);
        const secs = Math.floor(totalSec % 60);
        const pad = (n) => String(n).padStart(2, '0');
        return `${pad(hrs)}:${pad(mins)}:${pad(secs)}`;
    }

    function tickBreakTimer() {
        if (!breakStartTimeMs) return;
        const nowServerMs = Date.now() - clientSkewMs;
        const elapsedSec = Math.floor((nowServerMs - breakStartTimeMs) / 1000);
        const formatted = formatElapsed(elapsedSec);

        const bannerTimer = document.getElementById('breakTimerText');
        if (bannerTimer) bannerTimer.textContent = formatted;

        const cardTimer = document.getElementById('cardBreakTimer');
        if (cardTimer) cardTimer.textContent = formatted;
    }

    function startBreakTimer(startTimeMs) {
        if (breakTimerInterval) clearInterval(breakTimerInterval);
        breakStartTimeMs = startTimeMs || (Date.now() - clientSkewMs);
        tickBreakTimer();
        breakTimerInterval = setInterval(tickBreakTimer, 1000);
    }

    function stopBreakTimer() {
        if (breakTimerInterval) {
            clearInterval(breakTimerInterval);
            breakTimerInterval = null;
        }
        breakStartTimeMs = null;
    }

    function applyDutyStatusUI(newStatus, breakStartedAt) {
        const statusChanged = savedDutyStatus !== newStatus;
        savedDutyStatus = newStatus;
        currentBreakStartedAt = breakStartedAt;
        if (statusChanged && dutyStatusCard) {
            dutyStatusCard.classList.remove('is-status-changing');
            requestAnimationFrame(function () {
                dutyStatusCard.classList.add('is-status-changing');
            });
            window.setTimeout(function () {
                dutyStatusCard.classList.remove('is-status-changing');
            }, 450);
        }

        // 1. Break Time Banner
        const banner = document.getElementById('breakTimeBanner');
        const breakStartedText = document.getElementById('breakStartedText');
        if (newStatus === 'On Break') {
            let startMs = null;
            if (breakStartedAt) {
                const parts = breakStartedAt.split(/[- :]/);
                if (parts.length >= 6) {
                    const parsedDate = new Date(parts[0], parts[1] - 1, parts[2], parts[3], parts[4], parts[5]);
                    startMs = parsedDate.getTime();
                    if (breakStartedText) {
                        breakStartedText.textContent = formatTimeAMPM(parsedDate);
                    }
                }
            }
            if (!startMs) {
                startMs = Date.now() - clientSkewMs;
                if (breakStartedText) {
                    breakStartedText.textContent = formatTimeAMPM(new Date());
                }
            }
            if (banner) {
                banner.classList.remove('d-none');
            }
            startBreakTimer(startMs);
        } else {
            if (banner) {
                banner.classList.add('d-none');
            }
            stopBreakTimer();
        }

        // 3. Status Card in metrics strip
        const dutyCard = document.getElementById('dutyStatusCard');
        const dutyIconBox = document.getElementById('dutyStatusIconBox');
        const dutyIcon = document.getElementById('dutyStatusIcon');
        const dutyVal = document.getElementById('dutyStatusValue');
        const dutySub = document.getElementById('dutyStatusSubtitle');
        const dutyCaret = document.getElementById('dutyStatusCaret');

        if (dutyCard) {
            dutyCard.classList.remove('duty-on-break', 'duty-off-duty');
            if (newStatus === 'On Break') {
                dutyCard.classList.add('duty-on-break');
            } else if (newStatus === 'Off Duty') {
                dutyCard.classList.add('duty-off-duty');
            }
        }
        if (dutyVal) dutyVal.textContent = newStatus;

        if (dutyIconBox && dutyIcon && dutyCaret) {
            if (newStatus === 'On Break') {
                dutyIconBox.className = 'op-icon-box bg-warning-subtle text-warning-emphasis';
                dutyIcon.className = 'bi bi-cup-hot-fill fs-4';
                dutyCaret.className = 'bi bi-chevron-down op-card-caret text-warning';
                if (dutySub) dutySub.innerHTML = 'Break Time: <span id="cardBreakTimer">00:00:00</span>';
                tickBreakTimer();
            } else if (newStatus === 'Off Duty') {
                dutyIconBox.className = 'op-icon-box bg-secondary-subtle text-secondary';
                dutyIcon.className = 'bi bi-moon-stars fs-4';
                dutyCaret.className = 'bi bi-chevron-down op-card-caret text-secondary';
                if (dutySub) dutySub.textContent = 'Shift Finished \u00b7 Standby';
            } else {
                dutyIconBox.className = 'op-icon-box bg-success-subtle text-success';
                dutyIcon.className = 'bi bi-broadcast fs-4';
                dutyCaret.className = 'bi bi-chevron-down op-card-caret text-success';
                if (dutySub) dutySub.textContent = 'Active';
            }
        }

        // 4. Hero Duty HUD
        const hudBadge = document.getElementById('hudStatusBadge');
        const hudRadarDot = document.getElementById('hudRadarDot');
        const hudStatusLabel = document.getElementById('hudStatusLabel');
        const hudTimeIcon = document.getElementById('hudTimeIcon');
        const hudSectorIcon = document.getElementById('hudSectorIcon');
        const hudSectorLabel = document.getElementById('hudSectorLabel');

        if (hudBadge && hudRadarDot && hudStatusLabel) {
            hudBadge.classList.remove('hud-badge-break', 'hud-badge-off');
            hudRadarDot.classList.remove('radar-amber', 'radar-gray');

            if (newStatus === 'On Break') {
                hudBadge.classList.add('hud-badge-break');
                hudRadarDot.classList.add('radar-amber');
                hudStatusLabel.textContent = 'BREAK TIME ACTIVE';
                if (hudTimeIcon) hudTimeIcon.className = 'bi bi-stopwatch text-warning me-1.5';
                if (hudSectorIcon) hudSectorIcon.className = 'bi bi-pause-circle-fill text-warning me-1';
                if (hudSectorLabel) hudSectorLabel.innerHTML = 'Patrol Paused &middot; On Break';
            } else if (newStatus === 'Off Duty') {
                hudBadge.classList.add('hud-badge-off');
                hudRadarDot.classList.add('radar-gray');
                hudStatusLabel.textContent = 'OFF DUTY';
                if (hudTimeIcon) hudTimeIcon.className = 'bi bi-clock-history text-secondary me-1.5';
                if (hudSectorIcon) hudSectorIcon.className = 'bi bi-moon-stars-fill text-secondary me-1';
                if (hudSectorLabel) hudSectorLabel.innerHTML = 'Shift Concluded &middot; Standby';
            } else {
                hudStatusLabel.textContent = 'ON ACTIVE PATROL';
                if (hudTimeIcon) hudTimeIcon.className = 'bi bi-clock-history text-warning me-1.5';
                if (hudSectorIcon) hudSectorIcon.className = 'bi bi-geo-alt-fill text-danger me-1';
                if (hudSectorLabel) hudSectorLabel.innerHTML = 'CTRAMO &middot; <?= esc($topLocation) ?>';
            }
        }
    }

    if (savedDutyStatus === 'On Break') {
        startBreakTimer(breakStartTimeMs);
    }

    const dutyStatusForm = document.getElementById('dutyStatusForm');
    const dutyStatusCard = document.getElementById('dutyStatusCard');
    const dutyStatusOptionsTemplate = document.getElementById('dutyStatusOptionsTemplate');
    let isSavingDutyStatus = false;

    async function openDutyStatusDialog() {
        if (isSavingDutyStatus) return;
        if (!dutyStatusForm || !dutyStatusOptionsTemplate) {
            console.error('Duty status dialog is missing its form or options template.');
            return;
        }
        if (typeof Swal === 'undefined') {
            console.error('SweetAlert is required to change duty status.');
            return;
        }

        const dialogResult = await Swal.fire({
            title: '<span class="duty-status-title-kicker">FIELD STATUS</span><span class="duty-status-title-main"><i class="bi bi-broadcast"></i>Set your duty status</span>',
            html: dutyStatusOptionsTemplate.innerHTML,
            customClass: {
                popup: 'duty-status-swal',
                title: 'duty-status-swal-title',
                htmlContainer: 'duty-status-swal-html',
                actions: 'duty-status-swal-actions',
                confirmButton: 'duty-status-swal-confirm',
                cancelButton: 'duty-status-swal-cancel'
            },
            showCancelButton: true,
            reverseButtons: true,
            confirmButtonText: '<i class="bi bi-check2-circle me-1"></i>Save Status',
            cancelButtonText: 'Cancel',
            focusConfirm: false,
            didOpen: function (popup) {
                if (dutyStatusCard) dutyStatusCard.classList.add('is-open');
                const options = Array.from(popup.querySelectorAll('.duty-option'));
                let selectedOption = options.find(option => option.dataset.status === savedDutyStatus) || options[0];
                options.forEach(function (option) {
                    const isSelected = option === selectedOption;
                    option.classList.toggle('is-selected', isSelected);
                    option.setAttribute('aria-checked', String(isSelected));
                    option.tabIndex = isSelected ? 0 : -1;
                    option.addEventListener('click', function () {
                        selectedOption = option;
                        options.forEach(function (other) {
                            const isCurrent = other === selectedOption;
                            other.classList.toggle('is-selected', isCurrent);
                            other.setAttribute('aria-checked', String(isCurrent));
                            other.tabIndex = isCurrent ? 0 : -1;
                        });
                    });
                    option.addEventListener('keydown', function (event) {
                        if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') return;
                        event.preventDefault();
                        const direction = event.key === 'ArrowDown' ? 1 : -1;
                        const nextIndex = (options.indexOf(option) + direction + options.length) % options.length;
                        options[nextIndex].click();
                        options[nextIndex].focus();
                    });
                });
            },
            willClose: function () {
                if (dutyStatusCard) dutyStatusCard.classList.remove('is-open');
            },
            preConfirm: function () {
                const selectedOption = Swal.getPopup()?.querySelector('.duty-option.is-selected');
                const selectedStatus = selectedOption?.dataset.status;
                if (!selectedStatus) {
                    Swal.showValidationMessage('Choose a duty status before saving.');
                    return false;
                }
                return selectedStatus;
            }
        });

        if (!dialogResult.isConfirmed || !dialogResult.value) return;

        const selectedStatus = dialogResult.value;
        const previousStatus = savedDutyStatus;
        const previousBreakStartedAt = currentBreakStartedAt;
        isSavingDutyStatus = true;
        if (dutyStatusCard) {
            dutyStatusCard.setAttribute('aria-busy', 'true');
            dutyStatusCard.classList.add('is-saving');
        }
        applyDutyStatusUI(selectedStatus, null);
        Swal.fire({
            icon: 'info',
            title: 'Saving duty status…',
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
            timer: false,
            customClass: { popup: 'duty-status-update-toast' }
        });

        try {
            const formData = new FormData(dutyStatusForm);
            formData.set('duty_status', selectedStatus);
            const response = await fetch(dutyStatusForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            let res;
            try {
                res = await response.json();
            } catch (error) {
                throw new Error('The server returned an invalid response.');
            }
            if (!response.ok || !res.success) {
                throw new Error(res.message || 'Could not save the duty status. Please try again.');
            }

            applyDutyStatusUI(res.duty_status, res.break_started_at);
            Swal.fire({
                icon: res.duty_status === 'On Break' ? 'warning' : (res.duty_status === 'Active Patrol' ? 'success' : 'info'),
                title: res.duty_status === 'On Break' ? 'Break Time Active' : res.duty_status,
                text: res.duty_status === 'On Break'
                    ? 'Your break has started. Field assignments are temporarily paused.'
                    : (res.duty_status === 'Active Patrol' ? 'Duty status updated to Active Patrol.' : 'Shift finished. You are now Off Duty.'),
                timer: 3000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end',
                customClass: { popup: 'duty-status-update-toast' }
            });
        } catch (error) {
            applyDutyStatusUI(previousStatus, previousBreakStartedAt);
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: error.message || 'Could not update status. Please try again.',
                toast: true,
                position: 'top-end',
                timer: 4500,
                showConfirmButton: false,
                customClass: { popup: 'duty-status-update-toast' }
            });
        } finally {
            isSavingDutyStatus = false;
            if (dutyStatusCard) {
                dutyStatusCard.removeAttribute('aria-busy');
                dutyStatusCard.classList.remove('is-saving');
            }
        }
    }

    if (dutyStatusCard) {
        dutyStatusCard.addEventListener('click', openDutyStatusDialog);
        dutyStatusCard.addEventListener('keydown', function (event) {
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                event.preventDefault();
                openDutyStatusDialog();
            }
        });
    }

    // One-click "Resume Active Patrol" from Break Time Banner
    const btnResumeActiveDuty = document.getElementById('btnResumeActiveDuty');
    if (btnResumeActiveDuty) {
        btnResumeActiveDuty.addEventListener('click', async function () {
            btnResumeActiveDuty.disabled = true;
            const origHtml = btnResumeActiveDuty.innerHTML;
            btnResumeActiveDuty.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Resuming...';

            const csrfInput = dutyStatusForm ? dutyStatusForm.querySelector('input[type="hidden"]') : null;
            const formData = new FormData();
            formData.append('duty_status', 'Active Patrol');
            if (csrfInput) {
                formData.append(csrfInput.name, csrfInput.value);
            }

            try {
                const response = await fetch('<?= base_url('officer/profile/duty-status') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });
                const res = await response.json();
                if (res.success) {
                    applyDutyStatusUI('Active Patrol', null);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Active Patrol Resumed',
                            text: 'Break ended. Welcome back on active duty!',
                            timer: 3000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    }
                }
            } catch (err) {
                window.location.reload();
            } finally {
                btnResumeActiveDuty.disabled = false;
                btnResumeActiveDuty.innerHTML = origHtml;
            }
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
                        confirmButtonColor: '#7c3aed'
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
                confirmButtonColor: '#7c3aed'
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

    /* ── Citation Enforcement Record: switch the period in place ──
       The card used to reload the whole profile page for every pill click.
       Now only the card's figures change, and the previous numbers stay on
       screen until the new ones arrive, so nothing blinks or jumps. */
    (function () {
        const endpoint = '<?= base_url('officer/profile/enforcement-summary') ?>';
        const card     = document.querySelector('.record-card');
        let inFlight = null;

        function setText(id, value) {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        }

        /* Figures animate from their current value to the new one with a single
           requestAnimationFrame loop per value. The server already rendered the
           final number in the markup, so there is no empty flash, no layout
           shift and nothing that delays the page becoming interactive. */
        const reduceMotion = window.matchMedia &&
            window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        function animateCount(id, target, format) {
            const el = document.getElementById(id);
            if (!el) return;
            const to = Number(target) || 0;
            const shown = parseFloat(el.dataset.count);
            const from = Number.isFinite(shown) ? shown : 0;
            el.dataset.count = String(to);
            if (reduceMotion || from === to) {
                el.textContent = format(to);
                return;
            }
            // A new period switch supersedes any count still running so two
            // loops can never fight over the same figure.
            const token = (parseInt(el.dataset.anim, 10) || 0) + 1;
            el.dataset.anim = String(token);
            const started = performance.now();
            const duration = 650;
            function frame(now) {
                if (parseInt(el.dataset.anim, 10) !== token) return;
                const t = Math.min(1, (now - started) / duration);
                const eased = 1 - Math.pow(1 - t, 3);
                el.textContent = format(from + (to - from) * eased);
                if (t < 1) requestAnimationFrame(frame);
            }
            requestAnimationFrame(frame);
        }

        const fmtInt   = (n) => String(Math.round(n));
        const fmtPct   = (n) => Math.round(n) + '%';
        const fmtMoney = (n) => '\u20B1' + n.toLocaleString('en-US', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

        function paint(data) {
            const total = parseInt(data.total, 10) || 0;
            const pct   = parseInt(data.pct, 10) || 0;

            setText('cfrDateNote', data.dateNote || '');
            animateCount('cfrTotalIssued', total, fmtInt);
            animateCount('cfrPaidCount', parseInt(data.paid, 10) || 0, fmtInt);
            animateCount('cfrPendingCount', parseInt(data.pending, 10) || 0, fmtInt);
            animateCount('cfrPct', pct, fmtPct);
            animateCount('cfrTotalAmount', parseFloat(String(data.amount).replace(/,/g, '')) || 0, fmtMoney);

            const bar = document.getElementById('cfrPctBar');
            if (bar) bar.style.width = pct + '%';

            document.querySelectorAll('[data-cfr-period]').forEach(function (pill) {
                const on = pill.getAttribute('data-cfr-period') === data.period;
                pill.classList.toggle('active', on);
                if (on) {
                    pill.setAttribute('aria-current', 'page');
                } else {
                    pill.removeAttribute('aria-current');
                }
            });

            const customPill = document.getElementById('cfrCustomPill');
            if (customPill) customPill.classList.toggle('active', data.period === 'custom');

            const startBox = document.getElementById('cfrStartDate');
            const endBox   = document.getElementById('cfrEndDate');
            if (startBox && data.customStart) startBox.value = data.customStart;
            if (endBox && data.customEnd) endBox.value = data.customEnd;

            if (data.period === 'custom' && window.bootstrap) {
                const panel = document.getElementById('cfrCustomRange');
                if (panel) window.bootstrap.Collapse.getOrCreateInstance(panel).show();
            }
        }

        function load(params) {
            if (inFlight) inFlight.abort();
            inFlight = new AbortController();

            if (card) card.classList.add('cfr-refreshing');

            fetch(endpoint + '?' + new URLSearchParams(params).toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: inFlight.signal
            })
                .then(function (res) { return res.json(); })
                .then(function (data) { if (data && data.ok) paint(data); })
                .catch(function () { /* keep the last good figures rather than blanking the card */ })
                .finally(function () { if (card) card.classList.remove('cfr-refreshing'); });
        }

        document.querySelectorAll('[data-cfr-period]').forEach(function (pill) {
            pill.addEventListener('click', function (event) {
                event.preventDefault();
                load({ period: pill.getAttribute('data-cfr-period') });
            });
        });

        const customForm   = document.getElementById('cfrCustomForm');
        const monthSelect  = document.getElementById('cfrMonthSelect');
        const yearSelect   = document.getElementById('cfrYearSelect');
        const startBox     = document.getElementById('cfrStartDate');
        const endBox       = document.getElementById('cfrEndDate');

        function padZero(num) {
            return num < 10 ? '0' + num : String(num);
        }

        function formatDate(d) {
            return d.getFullYear() + '-' + padZero(d.getMonth() + 1) + '-' + padZero(d.getDate());
        }

        if (monthSelect && yearSelect) {
            function handleMonthDropdown() {
                const m = parseInt(monthSelect.value, 10);
                const y = parseInt(yearSelect.value, 10);
                if (m >= 1 && m <= 12 && y > 2000) {
                    const firstDay = new Date(y, m - 1, 1);
                    const lastDay  = new Date(y, m, 0);
                    const startStr = formatDate(firstDay);
                    const endStr   = formatDate(lastDay);
                    if (startBox) startBox.value = startStr;
                    if (endBox) endBox.value     = endStr;
                    load({
                        period: 'custom',
                        start_date: startStr,
                        end_date: endStr
                    });
                }
            }
            monthSelect.addEventListener('change', handleMonthDropdown);
            yearSelect.addEventListener('change', function () {
                if (monthSelect.value) handleMonthDropdown();
            });
        }

        /* Committing a date answers straight away: the picker fires `change` the
           moment a day is chosen, so the card updates in place with no Apply
           click and no page reload. `input` only clears the dropdowns while the
           officer is still choosing, so nothing fires mid-pick. */
        if (startBox && endBox) {
            let dateTimer = null;

            // A range that happens to be one whole month keeps the Month / Year
            // dropdowns honest instead of leaving them on "Choose Month".
            function syncDropdownsToRange() {
                if (!monthSelect || !yearSelect || !startBox.value || !endBox.value) return;

                const startDate = new Date(startBox.value + 'T00:00:00');
                const endDate   = new Date(endBox.value + 'T00:00:00');
                if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) return;

                const yearExists = Array.prototype.some.call(yearSelect.options, function (option) {
                    return option.value === String(startDate.getFullYear());
                });
                const lastDayOfMonth = new Date(endDate.getFullYear(), endDate.getMonth() + 1, 0).getDate();
                const wholeMonth = yearExists
                    && startDate.getFullYear() === endDate.getFullYear()
                    && startDate.getMonth() === endDate.getMonth()
                    && startDate.getDate() === 1
                    && endDate.getDate() === lastDayOfMonth;

                if (wholeMonth) {
                    monthSelect.value = padZero(startDate.getMonth() + 1);
                    yearSelect.value  = String(startDate.getFullYear());
                } else {
                    monthSelect.value = '';
                }
            }

            function applyPickedRange() {
                if (!startBox.value || !endBox.value) return;
                syncDropdownsToRange();
                load({
                    period: 'custom',
                    start_date: startBox.value,
                    end_date: endBox.value
                });
            }

            [startBox, endBox].forEach(function (box) {
                box.addEventListener('input', function () {
                    if (monthSelect) monthSelect.value = '';
                });
                box.addEventListener('change', function () {
                    // Tabbing straight from From to To settles into one request.
                    if (dateTimer) clearTimeout(dateTimer);
                    dateTimer = setTimeout(applyPickedRange, 180);
                });
            });
        }

        if (customForm) {
            customForm.addEventListener('submit', function (event) {
                event.preventDefault();
                load({
                    period: 'custom',
                    start_date: customForm.elements.start_date.value,
                    end_date: customForm.elements.end_date.value
                });
            });
        }

        // Decorative count-up on first paint. The final figures are already in
        // the markup, so this never holds up rendering or interaction.
        function targetOf(id) {
            const el = document.getElementById(id);
            return el ? el.dataset.target : 0;
        }
        animateCount('cfrTotalIssued', targetOf('cfrTotalIssued'), fmtInt);
        animateCount('cfrPaidCount', targetOf('cfrPaidCount'), fmtInt);
        animateCount('cfrPendingCount', targetOf('cfrPendingCount'), fmtInt);
        animateCount('cfrPct', targetOf('cfrPct'), fmtPct);
        animateCount('cfrTotalAmount', targetOf('cfrTotalAmount'), fmtMoney);
    })();
</script>
<?= $this->endSection() ?>
