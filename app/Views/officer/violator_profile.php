<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Violator Records') ?> - Traffic System<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4" style="font-family: 'Inter', system-ui, sans-serif;">

    <!-- TOP HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3 no-print violator-page-head">
        <div class="d-flex align-items-center gap-3 violator-head-main">
            <a href="<?= base_url('officer/violations') ?>" class="btn btn-outline-secondary btn-sm rounded-3 px-3 shadow-sm violator-back-btn btn-back-electric">
                <i class="bi bi-arrow-left me-1"></i> Back to Violations
            </a>
            <h4 class="mb-0 fw-bold d-flex align-items-center gap-2 violator-page-title" style="color: #0f172a;">
                <i class="bi bi-person-lines-fill text-primary"></i> Violator Records
            </h4>
        </div>
    </div>

    <!-- PRINT ONLY HEADER -->
    <div class="print-header d-none text-center mb-4">
        <h3 class="fw-bold mb-0">CITY TRAFFIC MANAGEMENT OFFICE (CTRAMO)</h3>
        <h5 class="text-secondary mb-1">Kabankalan City, Negros Occidental</h5>
        <h4 class="fw-bold mt-2">OFFICIAL VIOLATOR APPREHENSION &amp; OFFENSE RECORD</h4>
        <p class="small text-muted">Generated on <?= date('F d, Y h:i A') ?></p>
        <hr style="border-top: 2px solid #000;">
    </div>

    <!-- ═══ PROFILE HERO — the same banner the admin violator profile wears,
         carrying this violator's standing: outcome dot, badge and money HUD ═══ -->
    <?php
        // Photo state for this violator: the stored filename only renders when
        // the file is actually on disk, otherwise the initials wordmark stands
        // in. Upload controls appear only for a registered violator.
        $photoViolatorId = (int) ($violatorId ?? ($violator['id'] ?? 0));
        $photoFile       = trim((string) ($violator['profile_image'] ?? ''));
        $hasPhoto        = $photoFile !== '' && is_file(FCPATH . 'uploads/violators/' . $photoFile);
        $photoUrl        = $hasPhoto ? base_url('uploads/violators/' . $photoFile) : '';

        // Two letters (first + last name), matching the ledger rows.
        $heroWords = array_values(array_filter(preg_split('/\s+/', trim((string) ($violator['name'] ?? ''))) ?: []));
        if ($heroWords === []) {
            $heroInitials = 'VS';
        } elseif (count($heroWords) === 1) {
            $heroInitials = mb_strtoupper(mb_substr($heroWords[0], 0, 2));
        } else {
            $heroInitials = mb_strtoupper(
                mb_substr($heroWords[0], 0, 1)
                . mb_substr($heroWords[count($heroWords) - 1], 0, 1)
            );
        }

        // ── Outcome summary ──────────────────────────────────────────────
        // The hero, the dot and the money line are organized around this
        // person's standing on record — Pending, Settled or Cancelled — and
        // read the same totals the controller already gathered for the
        // citation table below.
        $assessed        = (float) ($violator['total_fines'] ?? 0);
        $balance         = (float) ($violator['pending_fines'] ?? 0);
        $paidTotal       = (float) ($violator['paid_fines'] ?? 0);
        $ticketCount     = (int) ($violator['total_tickets'] ?? count($tickets ?? []));
        $infractionCount = (int) ($violator['total_infractions'] ?? 0);

        $anyOpen     = ((int) ($violator['pending_tickets'] ?? 0)) > 0 || $balance > 0;
        $anySettled  = ((int) ($violator['paid_tickets'] ?? 0)) > 0 || $paidTotal > 0;
        $outcomeTone = $anyOpen ? 'pending' : ($anySettled ? 'paid' : 'cancelled');

        $outcomeLabel    = $outcomeTone === 'paid' ? 'Settled' : ($outcomeTone === 'cancelled' ? 'Cancelled' : 'Pending');
        $outcomeHeadline = match ($outcomeTone) {
            'paid'      => 'ALL CITATIONS SETTLED',
            'cancelled' => 'CITATIONS CANCELLED',
            default     => 'PENDING SETTLEMENT',
        };
        $moneyValue = $outcomeTone === 'paid' ? $paidTotal : ($outcomeTone === 'cancelled' ? 0.0 : $balance);
        $moneyLabel = $outcomeTone === 'paid' ? 'settled in full' : ($outcomeTone === 'cancelled' ? 'nothing due' : 'outstanding');

        // Newest citation (tickets are sorted newest first) feeds the "last
        // apprehension" line with a real date and place.
        $lastTicket   = $tickets[0] ?? null;
        $lastDate     = !empty($lastTicket['violation_date']) ? date('M d, Y', strtotime((string) $lastTicket['violation_date'])) : '—';
        $lastLocation = trim((string) ($lastTicket['location'] ?? ''));
    ?>
    <div class="vp-hero premium-reveal" style="--reveal-delay:0s;">
        <div class="vp-hero-orb vp-hero-orb-1"></div>
        <div class="vp-hero-orb vp-hero-orb-2"></div>
        <div class="vp-hero-orb vp-hero-orb-3"></div>
        <div class="vp-hero-content">

            <!-- Photo: the uploaded picture, or the initials wordmark -->
            <div class="vp-hero-avatar-wrap" title="<?= $photoViolatorId > 0 ? 'Click or tap to change the photo' : 'Violator photo' ?>">
                <div class="vp-avatar-display" id="violatorAvatarDisplay">
                    <?php if ($hasPhoto): ?>
                        <img src="<?= esc($photoUrl) ?>" alt="Violator photo" class="vp-avatar-img" id="violatorAvatarImg">
                    <?php else: ?>
                        <span class="vp-avatar-initials" id="violatorAvatarInitials"><?= esc($heroInitials) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($photoViolatorId > 0): ?>
                    <label for="violatorPhotoInput" class="vp-avatar-overlay" title="Upload photo">
                        <i class="bi bi-camera-fill"></i><span>Change</span>
                    </label>
                    <div class="vp-avatar-spinner d-none" id="violatorPhotoSpinner">
                        <div class="spinner-border text-light" style="width:1.6rem;height:1.6rem;" role="status"><span class="visually-hidden">Uploading...</span></div>
                    </div>
                <?php endif; ?>
                <span class="vp-outcome-dot vp-outcome-dot--<?= esc($outcomeTone) ?>" title="<?= esc($outcomeLabel) ?>"></span>
            </div>

            <!-- Identity -->
            <div class="vp-hero-info">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h2 class="vp-hero-name mb-0"><?= esc($violator['name']) ?></h2>
                    <?php if (!empty($violator['license_plate']) && !in_array(strtolower(trim($violator['license_plate'])), ['no', 'none', 'n/a', '-'])): ?>
                        <span class="vp-hero-plate"><?= tricycle_icon('me-1') ?><?= esc(strtoupper($violator['license_plate'])) ?></span>
                    <?php else: ?>
                        <span class="vp-hero-plate"><i class="bi bi-person-vcard me-1"></i>Record #<?= (int) $photoViolatorId ?></span>
                    <?php endif; ?>
                    <span class="vp-hero-outcome vp-hero-outcome--<?= esc($outcomeTone) ?>">
                        <?php if ($outcomeTone === 'paid'): ?><i class="bi bi-check-circle-fill me-1"></i>
                        <?php elseif ($outcomeTone === 'cancelled'): ?><i class="bi bi-x-circle-fill me-1"></i>
                        <?php else: ?><i class="bi bi-hourglass-split me-1"></i><?php endif; ?>
                        <?= esc($outcomeLabel) ?>
                    </span>
                </div>
                <div class="vp-hero-meta">
                    <?php $sanctionAttempts = (int) ($violator['attempts'] ?? $violator['total_tickets'] ?? 0); ?>
                    <span class="badge sanction-level <?= $sanctionAttempts >= 3 ? 'sanction-level--third' : ($sanctionAttempts === 2 ? 'sanction-level--second' : 'sanction-level--first') ?> rounded-pill px-3 py-1 fw-bold" style="font-size: 0.78rem;" title="One sanction per apprehension on record"><i class="bi bi-patch-check me-1"></i><?= esc($violator['sanction_record'] ?? 'No Sanction Record') ?></span>
                    <?php if (!empty($violator['license_number']) && !in_array(strtolower(trim($violator['license_number'])), ['no', 'none', 'n/a', '-'])): ?>
                        <span><i class="bi bi-person-badge me-1"></i>DL: <?= esc($violator['license_number']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($violator['vehicle_type'])): ?>
                        <span><i class="bi bi-truck me-1"></i><?= esc($violator['vehicle_type']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($violator['mtop_number'])): ?>
                        <span><i class="bi bi-hash me-1"></i>MTOP <?= esc($violator['mtop_number']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($violator['address']) && $violator['address'] !== 'Kabankalan City'): ?>
                        <span><i class="bi bi-geo-alt me-1"></i><?= esc($violator['address']) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($violator['age']) && $violator['age'] !== 'N/A'): ?>
                        <span><i class="bi bi-calendar3 me-1"></i><?= (int) $violator['age'] ?> years old</span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Outcome HUD: what the record stands at right now -->
            <div class="vp-hero-hud ms-lg-auto me-lg-3">
                <div class="vp-hud-status vp-hud-status--<?= esc($outcomeTone) ?>">
                    <span class="vp-radar-dot vp-radar-dot--<?= esc($outcomeTone) ?>"></span>
                    <span class="fw-bold"><?= esc($outcomeHeadline) ?></span>
                </div>
                <div class="vp-hud-money">
                    <i class="bi bi-cash-coin me-1"></i>
                    <span class="font-monospace fw-bold">&#8369;<?= number_format($moneyValue, 2) ?></span>
                    <small class="ms-1"><?= esc($moneyLabel) ?></small>
                </div>
                <div class="vp-hud-last">
                    <i class="bi bi-clock-history me-1"></i>
                    Last apprehension &middot; <?= esc($lastDate) ?><?= $lastLocation !== '' ? ' &middot; ' . esc($lastLocation) : '' ?>
                </div>
            </div>

            <!-- Photo actions -->
            <?php if ($photoViolatorId > 0): ?>
                <div class="vp-hero-upload">
                    <form id="violatorPhotoForm" action="<?= base_url('officer/violator/' . $photoViolatorId . '/photo') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <input type="file" id="violatorPhotoInput" name="profile_photo" class="d-none" accept="image/png,image/jpeg,image/jpg,image/webp">
                    </form>
                    <div class="d-flex flex-wrap align-items-center gap-2 justify-content-start justify-content-md-end">
                        <label class="vp-btn-upload" for="violatorPhotoInput">
                            <i class="bi bi-camera-fill me-1"></i><span><?= $hasPhoto ? 'Change Photo' : 'Add Photo' ?></span>
                        </label>
                        <button type="button" class="vp-btn-remove <?= $hasPhoto ? '' : 'd-none' ?>" id="btnRemoveViolatorPhoto" title="Remove violator photo">
                            <i class="bi bi-trash3 me-1"></i>Remove
                        </button>
                    </div>
                    <small class="vp-hero-hint">JPG, PNG, WEBP &middot; Max 10MB</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <style>
    /* ── PROFILE HERO ──
       The same banner language as the admin violator profile: blue gradient,
       floating orbs, a circular portrait with an outcome dot, the identity
       block, an outcome HUD and the photo actions. */
    .vp-hero { position:relative; overflow:hidden; border-radius:20px; padding:1.9rem 2rem; margin-bottom:1rem; background:linear-gradient(135deg,#1e3a8a 0%,#3b82f6 50%,#2563eb 100%); box-shadow:0 12px 32px rgba(30,58,138,.25); border:1px solid rgba(255,255,255,.15); isolation:isolate; }
    .vp-hero-content { position:relative; z-index:2; display:flex; flex-wrap:wrap; align-items:center; gap:1.35rem; }

    .vp-hero-avatar-wrap { position:relative; width:104px; height:104px; flex-shrink:0; border-radius:50%; }
    .vp-avatar-display { width:100%; height:100%; border-radius:50%; overflow:hidden; position:relative; background:rgba(255,255,255,.12); }
    .vp-avatar-img { width:100%; height:100%; border-radius:50%; object-fit:cover; border:4px solid rgba(255,255,255,.4); box-shadow:0 8px 28px rgba(0,0,0,.3); display:block; }
    .vp-avatar-initials { width:100%; height:100%; border-radius:50%; border:4px solid rgba(255,255,255,.4); box-shadow:0 8px 28px rgba(0,0,0,.3); background:rgba(255,255,255,.18); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; font-size:2.4rem; font-weight:800; color:#fff; letter-spacing:-1px; }
    .vp-avatar-overlay { position:absolute; inset:0; border-radius:50%; background:rgba(15,23,42,.68); backdrop-filter:blur(3px); display:flex; flex-direction:column; align-items:center; justify-content:center; color:#fff; font-size:.72rem; font-weight:700; opacity:0; pointer-events:none; transition:opacity .22s ease; cursor:pointer; z-index:3; text-transform:uppercase; letter-spacing:.05em; }
    .vp-hero-avatar-wrap:hover .vp-avatar-overlay { opacity:1; pointer-events:auto; }
    .vp-avatar-spinner { position:absolute; inset:0; border-radius:50%; background:rgba(15,23,42,.78); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; z-index:4; }
    .vp-outcome-dot { position:absolute; bottom:4px; right:4px; width:18px; height:18px; border-radius:50%; border:3px solid #fff; z-index:5; }
    .vp-outcome-dot--paid { background:#4ade80; box-shadow:0 0 10px rgba(74,222,128,.85); }
    .vp-outcome-dot--pending { background:#fbbf24; box-shadow:0 0 10px rgba(251,191,36,.85); }
    .vp-outcome-dot--cancelled { background:#94a3b8; }

    .vp-hero-info { min-width:0; flex:1 1 320px; }
    .vp-hero-name { font-size:1.7rem; font-weight:800; color:#fff; letter-spacing:-.03em; }
    .vp-hero-plate { display:inline-flex; align-items:center; background:#fff; color:#1d4ed8; font-weight:800; font-size:.78rem; letter-spacing:.04em; padding:.32rem .7rem; border-radius:999px; box-shadow:0 2px 8px rgba(0,0,0,.18); font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace; }
    .vp-hero-outcome { display:inline-flex; align-items:center; font-size:.73rem; font-weight:700; padding:.3rem .75rem; border-radius:20px; letter-spacing:.03em; text-transform:uppercase; }
    .vp-hero-outcome--paid { background:rgba(4,47,34,.55); border:1px solid rgba(167,243,208,.45); color:#a7f3d0; }
    .vp-hero-outcome--pending { background:rgba(69,26,3,.5); border:1px solid rgba(253,230,138,.5); color:#fde68a; }
    .vp-hero-outcome--cancelled { background:rgba(15,23,42,.5); border:1px solid rgba(203,213,225,.4); color:#e2e8f0; }
    .vp-hero-meta { display:flex; flex-wrap:wrap; gap:.35rem 1.05rem; color:rgba(255,255,255,.82); font-size:.85rem; margin-top:.4rem; }
    .vp-hero-meta span { display:inline-flex; align-items:center; min-width:0; }
    .vp-hero-meta i { opacity:.85; }

    .vp-hero-hud { background:rgba(15,23,42,.45); backdrop-filter:blur(12px); border:1px solid rgba(255,255,255,.18); border-radius:16px; padding:.75rem 1.25rem; display:flex; flex-direction:column; gap:.35rem; box-shadow:0 8px 24px rgba(0,0,0,.2); min-width:0; }
    .vp-hud-status { display:inline-flex; align-items:center; gap:.45rem; font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; }
    .vp-hud-status--paid { color:#4ade80; }
    .vp-hud-status--pending { color:#fbbf24; }
    .vp-hud-status--cancelled { color:#cbd5e1; }
    .vp-radar-dot { width:9px; height:9px; border-radius:50%; flex-shrink:0; }
    .vp-radar-dot--paid { background:#4ade80; box-shadow:0 0 0 0 rgba(74,222,128,.7); animation:vpRadar 1.8s infinite; }
    .vp-radar-dot--pending { background:#fbbf24; box-shadow:0 0 0 0 rgba(251,191,36,.7); animation:vpRadarAmber 1.8s infinite; }
    .vp-radar-dot--cancelled { background:#94a3b8; }
    @keyframes vpRadar { 0%{transform:scale(.95);box-shadow:0 0 0 0 rgba(74,222,128,.7)} 70%{transform:scale(1.1);box-shadow:0 0 0 8px rgba(74,222,128,0)} 100%{transform:scale(.95);box-shadow:0 0 0 0 rgba(74,222,128,0)} }
    @keyframes vpRadarAmber { 0%{transform:scale(.95);box-shadow:0 0 0 0 rgba(251,191,36,.7)} 70%{transform:scale(1.1);box-shadow:0 0 0 8px rgba(251,191,36,0)} 100%{transform:scale(.95);box-shadow:0 0 0 0 rgba(251,191,36,0)} }
    .vp-hud-money { color:#fff; font-size:.95rem; display:flex; align-items:baseline; }
    .vp-hud-money small { color:rgba(255,255,255,.75); font-size:.72rem; }
    .vp-hud-last { color:rgba(255,255,255,.78); font-size:.72rem; display:flex; align-items:center; flex-wrap:wrap; }

    .vp-hero-upload { display:flex; flex-direction:column; align-items:flex-start; }
    .vp-btn-upload { display:inline-flex; align-items:center; background:rgba(255,255,255,.2); backdrop-filter:blur(6px); border:1px solid rgba(255,255,255,.38); color:#fff; font-size:.85rem; font-weight:600; border-radius:12px; padding:.55rem 1.15rem; cursor:pointer; transition:all .22s ease; white-space:nowrap; box-shadow:0 4px 14px rgba(0,0,0,.15); margin:0; }
    .vp-btn-upload:hover { background:rgba(255,255,255,.32); transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,.22); }
    .vp-btn-remove { display:inline-flex; align-items:center; background:rgba(239,68,68,.22); backdrop-filter:blur(6px); border:1px solid rgba(239,68,68,.45); color:#fee2e2; font-size:.85rem; font-weight:600; border-radius:12px; padding:.55rem 1rem; cursor:pointer; transition:all .22s ease; box-shadow:0 4px 14px rgba(239,68,68,.15); }
    .vp-btn-remove:hover { background:rgba(239,68,68,.45); color:#fff; transform:translateY(-2px); box-shadow:0 6px 20px rgba(239,68,68,.25); }
    .vp-hero-hint { display:block; text-align:left; margin-top:.35rem; color:rgba(255,255,255,.55); font-size:.72rem; }

    .vp-hero-orb { position:absolute; border-radius:50%; pointer-events:none; z-index:1; filter:blur(55px); opacity:.38; }
    .vp-hero-orb-1 { width:240px; height:240px; background:#818cf8; top:-70px; right:8%; animation:vpOrbFloat 14s ease-in-out infinite alternate; }
    .vp-hero-orb-2 { width:160px; height:160px; background:#c084fc; bottom:-55px; right:28%; animation:vpOrbFloat 18s ease-in-out infinite alternate-reverse; }
    .vp-hero-orb-3 { width:120px; height:120px; background:#38bdf8; top:20%; right:4%; animation:vpOrbFloat 11s ease-in-out infinite alternate; }
    @keyframes vpOrbFloat { from { transform:translate(0,0); } to { transform:translate(18px,14px); } }

    /* ── OUTCOME STRIP — the three numbers behind the banner ──────────────── */
    .vp-outcome-card { background:#fff; border:1px solid #f1f5f9; border-radius:16px; box-shadow:0 3px 12px rgba(15,23,42,.05); padding:.9rem 1rem; display:flex; align-items:center; gap:.85rem; height:100%; }
    .vp-outcome-icon { width:46px; height:46px; border-radius:12px; flex-shrink:0; display:inline-flex; align-items:center; justify-content:center; }
    .vp-outcome-icon--pending { background:#fef9c3; color:#a16207; }
    .vp-outcome-icon--paid { background:#dcfce7; color:#15803d; }
    .vp-outcome-icon--cancelled { background:#f1f5f9; color:#475569; }
    .vp-outcome-icon--info { background:#dbeafe; color:#1d4ed8; }
    .vp-outcome-label { display:block; font-size:.66rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#52627a; }
    .vp-outcome-value { display:block; font-size:1.05rem; font-weight:800; color:#0b1730; line-height:1.3; }
    .vp-outcome-value--pending { color:#a16207; }
    .vp-outcome-value--paid { color:#15803d; }
    .vp-outcome-value--cancelled { color:#475569; }
    .vp-outcome-sub { display:block; font-size:.72rem; color:#52627a; margin-top:.1rem; overflow-wrap:anywhere; }

    /* ── RESPONSIVE — same steps as the admin profile's banner ── */
    @media (max-width: 991px) {
        /* The HUD drops below the identity and spans the banner, so the
           outcome stays on screen without squeezing the name on a tablet. */
        .vp-hero-content { align-items:stretch; }
        .vp-hero-hud { width:100%; margin:.75rem 0 0 0 !important; flex-direction:row; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:.75rem; }
        .vp-hero-upload { width:100%; margin-top:.5rem; }
        .vp-hero-upload .d-flex { width:100%; justify-content:flex-start !important; }
        .vp-hero-hint { text-align:left; }
    }
    @media (max-width: 768px) {
        .vp-hero { padding:1.5rem 1.2rem; border-radius:18px; }
        .vp-hero-content { flex-direction:column; align-items:flex-start; gap:1.1rem; }
        /* In column direction the desktop flex-basis (320px) would become a
           minimum height and leave an empty gap under the name. */
        .vp-hero-info { flex:0 0 auto; width:100%; }
        .vp-hero-avatar-wrap { width:90px; height:90px; }
        .vp-avatar-initials { font-size:2rem; }
        .vp-hero-name { font-size:1.35rem; }
        .vp-hero-meta { font-size:.8rem; gap:.3rem .75rem; }
        .vp-hero-upload .d-flex { width:100%; }
        .vp-hero-upload .vp-btn-upload,
        .vp-hero-upload .vp-btn-remove { flex:1 1 auto; justify-content:center; min-height:44px; }
    }
    @media (max-width: 576px) {
        /* The HUD stacks on narrow phones; the money line stays the loud part. */
        .vp-hero-hud { flex-direction:column; align-items:flex-start; gap:.4rem; padding:.75rem 1rem; }
    }

    /* ── PRINT: paper has no gradient, so flatten the banner ── */
    @media print {
        .vp-hero { background:#fff !important; border:1px solid #94a3b8 !important; box-shadow:none !important; border-radius:0 !important; padding:10px 0 !important; margin-bottom:0 !important; }
        .vp-hero-orb,
        .vp-outcome-dot,
        .vp-avatar-overlay,
        .vp-hero-upload,
        .vp-avatar-spinner,
        .vp-radar-dot,
        #violatorPhotoForm { display:none !important; }
        .vp-hero-name,
        .vp-hero-meta,
        .vp-hero-meta *,
        .vp-hero-plate,
        .vp-hero-outcome,
        .vp-hero-outcome *,
        .vp-hud-status,
        .vp-hud-status *,
        .vp-hud-money,
        .vp-hud-money *,
        .vp-hud-last,
        .vp-hud-last *,
        .vp-avatar-initials {
            color:#0f172a !important;
        }
        .vp-hero-outcome,
        .vp-hero-hud,
        .vp-hero-plate {
            background:#fff !important;
            border:1px solid #0f172a !important;
            border-radius:6px !important;
            box-shadow:none !important;
        }
        .vp-outcome-card { border:1px solid #cbd5e1 !important; box-shadow:none !important; }
    }
    </style>

    <!-- CITATION HISTORY LIST -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="citationRecordsCard" style="border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <h5 class="mb-0 fw-bold d-flex align-items-center gap-2" style="color: #0f172a;">
                <i class="bi bi-clock-history text-primary"></i> Complete Citation Records
            </h5>
            <?php $citationCount = count($tickets ?? []); ?>
            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1.5 fw-semibold"
                  style="font-size: 0.78rem;"
                  title="Tickets listed below; the sanction record beside the name counts every apprehension">
                <?= number_format($citationCount) ?> citation<?= $citationCount === 1 ? '' : 's' ?>
            </span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($tickets)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-folder-x fs-1 text-secondary opacity-50 mb-2 d-block"></i>
                    <h6>No recorded violations found for this individual.</h6>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="citationRecordsTable">
                        <thead class="table-light">
                            <tr style="font-size: 0.74rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">
                                <th class="ps-4">Ticket ID</th>
                                <th>Location</th>
                                <th>Violations Infraction</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Total Amount</th>
                                <th>Status</th>
                                <th>Date &amp; Time</th>
                                <th class="text-end pe-4 no-print">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tickets as $t): ?>
                                <?php
                                    $isPaid = strtolower($t['status'] ?? '') === 'paid';
                                    $isCancelled = strtolower($t['status'] ?? '') === 'cancelled';
                                    // Editing, cancelling and deleting only apply to this
                                    // officer's own pending tickets; viewing and printing
                                    // stay available for every citation on record.
                                    $isOwnTicket = (int) ($t['officer_id'] ?? 0) === (int) session('id');
                                ?>
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td class="ps-4">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1.5 rounded-2 fw-bold" style="font-size: 0.82rem;">
                                            <i class="bi bi-ticket-perforated me-1"></i><?= esc($t['ticket_id']) ?>
                                        </span>
                                    </td>
                                    <td style="font-size: 0.85rem; color: #475569;">
                                        <i class="bi bi-geo-alt text-secondary me-1"></i><?= esc($t['location'] ?: 'Kabankalan Proper') ?>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column py-1">
                                            <?php foreach ($t['violations'] as $vItem): ?>
                                                <div class="d-flex align-items-center gap-1.5 vp-violation-row" style="font-size: 0.82rem; color: #1e293b; min-height: 26px; line-height: 26px;">
                                                    <span style="color: #ef4444; font-size: 1.1rem; line-height: 0.7;">&bull;</span>
                                                    <span class="fw-medium"><?= esc($vItem['type']) ?></span>
                                                    <!-- Same figure as the Amount column, shown on phones where the
                                                         column is collapsed so each name keeps its own fine. -->
                                                    <span class="vp-amount-inline font-monospace fw-semibold ms-auto">&#8369;<?= number_format($vItem['amount'], 2) ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <!-- Each amount lines up with its violation on the left -->
                                    <td class="text-end">
                                        <div class="d-flex flex-column align-items-end py-1">
                                            <?php foreach ($t['violations'] as $vItem): ?>
                                                <div class="font-monospace fw-semibold" style="font-size: 0.8rem; color: #334155; min-height: 26px; line-height: 26px;">
                                                    &#8369;<?= number_format($vItem['amount'], 2) ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="font-monospace fw-bold <?= $isPaid ? 'text-success' : 'text-danger' ?>" style="font-size: 0.95rem;">
                                            &#8369;<?= number_format($t['total_amount'], 2) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($isPaid): ?>
                                            <span class="badge ticket-status-pill ticket-status-pill--paid rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="bi bi-check-circle-fill me-1"></i>PAID
                                            </span>
                                            <?php if (!empty($t['receipt_number'])): ?>
                                                <div class="small text-muted font-monospace mt-1" style="font-size: 0.72rem;" title="Official Receipt (OR) number">OR (Official Receipt) #<?= esc($t['receipt_number']) ?></div>
                                            <?php endif; ?>
                                        <?php elseif ($isCancelled): ?>
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                CANCELLED
                                            </span>
                                        <?php else: ?>
                                            <span class="badge ticket-status-pill ticket-status-pill--pending rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.75rem;">
                                                <i class="bi bi-hourglass-split me-1"></i>PENDING
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark" style="font-size: 0.88rem;">
                                            <?= !empty($t['violation_date']) ? date('M d, Y', strtotime($t['violation_date'])) : '-' ?>
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.75rem;">
                                            <?= !empty($t['violation_date']) ? date('h:i A', strtotime($t['violation_date'])) : '' ?>
                                        </div>
                                    </td>
                                    <td class="text-end pe-4 no-print">
                                        <button type="button" class="btn btn-sm btn-white border citation-menu-btn"
                                                data-menu-id="citationMenu<?= (int) $t['id'] ?>"
                                                aria-haspopup="true" aria-expanded="false" title="Citation Actions">
                                            <i class="bi bi-three-dots-vertical text-secondary"></i>
                                        </button>
                                        <ul class="dropdown-menu citation-menu-pop shadow border-0 rounded-3 py-1" id="citationMenu<?= (int) $t['id'] ?>">
                                            <li>
                                                <?php $ticketHref = 'officer/view/' . $t['id'] . '?from=violator-profile&violator=' . (int) ($violatorId ?? 0); ?>
                                                <a class="dropdown-item py-2 text-dark fw-semibold" href="<?= base_url($ticketHref) ?>">
                                                    <i class="bi bi-eye me-2 text-primary"></i> View Citation
                                                </a>
                                            </li>
                                            <li>
                                                <!-- Prints in this tab; the print view returns here afterwards. -->
                                                <a class="dropdown-item py-2 text-dark fw-semibold" href="<?= base_url($ticketHref . '&print=1') ?>">
                                                    <i class="bi bi-printer me-2 text-success"></i> Print Ticket
                                                </a>
                                            </li>
                                            <?php if (! $isPaid && ! $isCancelled && $isOwnTicket): ?>
                                                <li><hr class="dropdown-divider my-1"></li>
                                                <li>
                                                    <!-- Opens the edit modal on this page: the record stays
                                                         behind it and saving lands back here. -->
                                                    <a class="dropdown-item py-2 text-dark fw-semibold" href="<?= base_url('officer/violator-profile/' . (int) ($violatorId ?? 0) . '?edit_ticket=' . (int) $t['id']) ?>">
                                                        <i class="bi bi-pencil me-2 text-warning"></i> Edit Citation
                                                    </a>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item py-2 btn-cancel-ticket text-danger fw-semibold" data-id="<?= (int) $t['id'] ?>">
                                                        <i class="bi bi-x-circle me-2 text-danger"></i> Cancel Ticket
                                                    </button>
                                                </li>
                                                <li>
                                                    <button type="button" class="dropdown-item py-2 btn-delete-ticket text-danger fw-semibold"
                                                            data-id="<?= (int) $t['id'] ?>"
                                                            data-ticket="<?= esc($t['ticket_id']) ?>">
                                                        <i class="bi bi-trash3 me-2 text-danger"></i> Delete Permanently
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Cancel Ticket Modal -->
<div class="modal fade" id="cancelTicketModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle me-2"></i>Cancel Ticket</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="cancelTicketForm" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <p class="fw-bold mb-2">Cancel this citation ticket?</p>
                    <p class="text-muted small mb-3"><i class="bi bi-info-circle me-1"></i>All infractions printed on the ticket are cancelled together.</p>
                    <label class="form-label fw-bold">Reason</label>
                    <textarea class="form-control shadow-sm" name="reason" rows="3" required placeholder="Provide a reason for cancellation..."></textarea>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-link text-muted" data-bs-dismiss="modal">Keep Ticket</button>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm">Confirm Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Deleting is confirmed in a warning dialog instead of a modal, so only the
     CSRF-protected form that posts the deletion is kept here. -->
<form id="deleteTicketForm" method="POST" class="d-none"><?= csrf_field() ?></form>

<?= $this->include('officer/partials/tct_field_options') ?>
<?= $this->include('officer/partials/edit_ticket_modal') ?>

<style>
    /* The citation actions float as a menu anchored to the page itself, so the
       rounded card, the table's scroll box and the blurred backdrop can never
       clip it or shove it off-screen. */
    .citation-menu-pop {
        position: absolute;
        display: block;
        min-width: 235px;
        z-index: 1080;
        opacity: 0;
        transform: translateY(-6px) scale(.97);
        transform-origin: top right;
        pointer-events: none;
        transition: opacity .16s ease, transform .16s ease;
    }
    .citation-menu-pop.show {
        opacity: 1;
        transform: none;
        pointer-events: auto;
    }
    .citation-menu-btn i {
        display: inline-block;
        transition: transform .25s ease;
    }
    .citation-menu-btn[aria-expanded="true"] i {
        transform: rotate(90deg);
    }

@media (max-width: 575.98px) {
    /* Header + records title: one item per row instead of three-line wraps. */
    .violator-page-head {
        gap: 0.75rem !important;
    }
    .violator-head-main {
        flex-wrap: wrap;
        width: 100%;
    }
    .violator-back-btn {
        white-space: nowrap;
    }
    .violator-page-title {
        font-size: 1.05rem;
    }
    .violator-page-head > .d-flex.gap-2,
    .violator-page-head > .d-flex.gap-2 .btn {
        width: 100%;
    }
    #citationRecordsCard > .card-header {
        flex-direction: column;
        align-items: flex-start !important;
        gap: 0.5rem;
    }
}

.vp-amount-inline {
    display: none;
}

/* ── DESKTOP: the same eight columns, tightened so the table fits its card
   The table used to run a few dozen pixels wider than the card on common
   1280/1366 screens, which turned the whole card into a sideways scroll
   pane — a wheel or trackpad nudge moved the records instead of the page.
   Slightly closer cells keep every column and every value while letting
   the table sit inside the card at any desktop width. */
@media (min-width: 1260px) {
    #citationRecordsTable th,
    #citationRecordsTable td {
        padding: 0.36rem 0.45rem;
    }
    #citationRecordsTable td:nth-child(1) .badge {
        font-size: 0.76rem;
        padding: 0.24rem 0.4rem !important;
    }
    #citationRecordsTable td:nth-child(6) {
        font-size: 0.7rem;
    }
    #citationRecordsTable td:nth-child(5) span {
        font-size: 0.88rem;
    }
    /* Long words wrap inside the column instead of widening the table. */
    #citationRecordsTable td:nth-child(3) {
        overflow-wrap: anywhere;
    }
}

/* ── TABLE LEGIBILITY ───────────────────────────────────────────────
   Code, status and date columns hold short fixed-shape values; letting
   them wrap turned "Oct 23, 2026" into three lines and split the
   "Date & Time" header. They stay on one line and the variable-length
   violation text absorbs the difference. */
#citationRecordsTable th:nth-child(1),
#citationRecordsTable td:nth-child(1),
#citationRecordsTable th:nth-child(6),
#citationRecordsTable td:nth-child(6),
#citationRecordsTable th:nth-child(7),
#citationRecordsTable td:nth-child(7) {
    white-space: nowrap;
}
#citationRecordsTable th:nth-child(4),
#citationRecordsTable td:nth-child(4),
#citationRecordsTable th:nth-child(5),
#citationRecordsTable td:nth-child(5) {
    white-space: nowrap;
}
/* Reclaim the outer gutters the cells do not need. */
#citationRecordsTable th:first-child,
#citationRecordsTable td:first-child {
    padding-left: 1rem;
}
#citationRecordsTable th:last-child,
#citationRecordsTable td:last-child {
    padding-right: 1rem;
}

/* Give the amount column a gutter so it never touches the infraction text. */
#citationRecordsTable td:nth-child(4),
#citationRecordsTable th:nth-child(4) {
    padding-left: 1rem;
}
@media (min-width: 768px) {
    #citationRecordsTable th:nth-child(5),
    #citationRecordsTable td:nth-child(5) {
        padding-right: 1.25rem;
    }
    #citationRecordsTable th:nth-child(6),
    #citationRecordsTable td:nth-child(6) {
        padding-left: 1.25rem;
    }
}

/* ── TABLET AND PHONE: the eight-column table becomes one card per ticket
   Eight columns inside a narrow card only worked by swiping the whole row
   sideways, and that sideways pan is what stole every scroll gesture: a
   trackpad or a thumb swiping down would drift the table instead of moving
   the page. Below the desktop width each ticket is a card: ticket + status
   on the first line, location + date on the second, and the amounts sit
   beside their violations so the pairing survives. No record detail
   changes — only the arrangement. */
/* Screen only: paper keeps the full table, so a printed record still shows
   every column side by side. */
@media screen and (max-width: 1259.98px) {
    #citationRecordsTable {
        min-width: 0 !important;
    }
    /* Grid rows still live inside the table box, which keeps its column
       widths even once its rows are cards; blocks on the table and its
       body let each ticket card take the full width. */
    #citationRecordsTable,
    #citationRecordsTable tbody {
        display: block;
        width: 100%;
    }
    #citationRecordsTable tbody tr {
        width: 100%;
        box-sizing: border-box;
    }
    #citationRecordsTable thead {
        display: none;
    }
    #citationRecordsTable tbody tr {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 0.4rem 0.75rem;
        align-items: start;
        margin: 0 0 0.85rem;
        padding: 0.75rem 0.9rem 0.85rem;
        border: 1px solid var(--border-soft, #e2e8f0);
        border-radius: 14px;
        background: var(--surface-card, #fff);
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
    }
    #citationRecordsTable tbody tr:last-child {
        margin-bottom: 0.35rem;
    }
    #citationRecordsTable tbody td {
        display: block;
        width: auto;
        padding: 0 !important;
        border: 0 !important;
    }
    /* Bootstrap paints the hover state per cell; in the card layout that
       shows as grey patches, so the card itself carries the highlight. */
    #citationRecordsTable.table-hover tbody tr:hover > td {
        background-color: transparent !important;
    }
    #citationRecordsTable tbody td:nth-child(1) { grid-area: 1 / 1; }
    #citationRecordsTable tbody td:nth-child(2) { grid-area: 2 / 1; }
    #citationRecordsTable tbody td:nth-child(3) { grid-area: 3 / 1 / 3 / 3; }
    /* The fine now travels inside each violation row, so the separate
       amount column is not repeated in the card. */
    #citationRecordsTable tbody td:nth-child(4) { display: none !important; }
    #citationRecordsTable .vp-violation-row {
        align-items: flex-start !important;
        min-height: 0 !important;
        line-height: 1.35 !important;
        padding: 2px 0;
    }
    #citationRecordsTable .vp-violation-row > span:first-child {
        line-height: 1.2 !important;
        margin-top: 1px;
    }
    #citationRecordsTable .vp-amount-inline {
        display: inline-block;
        margin-left: 0.6rem;
        white-space: nowrap;
        font-size: 0.78rem;
        opacity: 0.85;
    }
    #citationRecordsTable tbody td:nth-child(6) { grid-area: 1 / 2; justify-self: end; }
    #citationRecordsTable tbody td:nth-child(7) { grid-area: 2 / 2; justify-self: end; text-align: right; }
    #citationRecordsTable tbody td:nth-child(5) {
        grid-area: 4 / 1;
        display: flex;
        align-items: baseline;
        gap: 0.4rem;
        margin-top: 0.15rem;
        padding-top: 0.5rem !important;
        border-top: 1px dashed var(--border-soft, #e2e8f0) !important;
    }
    #citationRecordsTable tbody td:nth-child(5)::before {
        content: "TOTAL";
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    #citationRecordsTable tbody td:nth-child(8) {
        grid-area: 4 / 2;
        justify-self: end;
        margin-top: 0.15rem;
        padding-top: 0.4rem !important;
    }
    #citationRecordsTable tbody td:nth-child(6) .badge {
        font-size: 0.72rem !important;
    }
}

@media print {
    .no-print, .sidebar, .navbar, .ht-header, .btn, .breadcrumb, #sidebar-wrapper {
        display: none !important;
    }
    .print-header {
        display: block !important;
    }
    body, .main-content, .container-fluid {
        background: #fff !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
    }
}

:root[data-bs-theme="dark"] .violator-page-title {
    color: #f8fafc !important;
}
:root[data-bs-theme="dark"] .violator-hero-card {
    background: linear-gradient(135deg, #111b2f 0%, #0f172a 100%) !important;
    border-color: #334155 !important;
}
:root[data-bs-theme="dark"] #citationRecordsCard {
    background: #0f172a;
    border-color: #334155 !important;
}
:root[data-bs-theme="dark"] #citationRecordsCard > .card-header {
    background: #111b2f !important;
    border-color: #334155 !important;
}
:root[data-bs-theme="dark"] #citationRecordsCard > .card-header h5 {
    color: #f8fafc !important;
}
:root[data-bs-theme="dark"] #citationRecordsTable thead th {
    background: #111b2f;
    color: #cbd5e1;
    border-color: #334155;
}
:root[data-bs-theme="dark"] #citationRecordsTable tbody tr {
    background: #0f172a;
    border-color: #334155 !important;
}
:root[data-bs-theme="dark"] #citationRecordsTable tbody td {
    color: #cbd5e1;
    border-color: #334155;
}
:root[data-bs-theme="dark"] #citationRecordsTable tbody td:nth-child(2),
:root[data-bs-theme="dark"] #citationRecordsTable .vp-violation-row,
:root[data-bs-theme="dark"] #citationRecordsTable .vp-violation-row .vp-amount-inline,
:root[data-bs-theme="dark"] #citationRecordsTable tbody td:nth-child(4) .font-monospace {
    color: #cbd5e1 !important;
}
:root[data-bs-theme="dark"] #citationRecordsTable tbody tr:hover,
:root[data-bs-theme="dark"] #citationRecordsTable tbody tr:hover td {
    background: #172554;
}
.ticket-status-pill {
    border: 1px solid transparent;
    transition: color .18s ease, background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.ticket-status-pill--paid {
    background: #d1fae5;
    border-color: #a7f3d0;
    color: #047857;
}
.ticket-status-pill--pending {
    background: #fef3c7;
    border-color: #fcd979;
    color: #9a6700;
}
:root[data-bs-theme="dark"] .ticket-status-pill--paid {
    background: rgba(52, 211, 153, .15);
    border-color: rgba(110, 231, 183, .35);
    color: #6ee7b7;
}
:root[data-bs-theme="dark"] .ticket-status-pill--pending {
    background: rgba(245, 196, 66, .13);
    border-color: rgba(252, 217, 121, .32);
    color: #fcd979;
}
.sanction-level {
    border: 1px solid transparent;
    transition: color .18s ease, background-color .18s ease, border-color .18s ease, box-shadow .18s ease;
}
.sanction-level--first {
    background: #d1fae5;
    border-color: #a7f3d0;
    color: #047857;
}
.sanction-level--second {
    background: #fef3c7;
    border-color: #fde68a;
    color: #a16207;
}
.sanction-level--third {
    background: #fee2e2;
    border-color: #fecaca;
    color: #b91c1c;
}
:root[data-bs-theme="dark"] .sanction-level--first {
    background: rgba(52, 211, 153, .14);
    border-color: rgba(52, 211, 153, .32);
    color: #6ee7b7;
}
:root[data-bs-theme="dark"] .sanction-level--second {
    background: rgba(245, 158, 11, .13);
    border-color: rgba(251, 191, 36, .28);
    color: #fcd34d;
}
:root[data-bs-theme="dark"] .sanction-level--third {
    background: rgba(248, 113, 113, .13);
    border-color: rgba(248, 113, 113, .3);
    color: #fca5a5;
}

    /* The citation ticket in the edit modal, styled exactly as it is on the
       ledger so the form looks the same wherever it is opened. */
<?= $this->include('officer/partials/tct_ticket_styles') ?>
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    // Citation actions float over the page: the menu is moved to <body> and
    // positioned with document coordinates, so nothing inside the card can
    // clip it and the blurred backdrop cannot displace it.
    const citationMenus = Array.from(document.querySelectorAll('.citation-menu-pop'));

    const closeCitationMenus = () => {
        citationMenus.forEach(menu => menu.classList.remove('show'));
        document.querySelectorAll('.citation-menu-btn').forEach(btn => btn.setAttribute('aria-expanded', 'false'));
    };

    document.querySelectorAll('.citation-menu-btn').forEach(btn => {
        btn.addEventListener('click', function (event) {
            event.stopPropagation();
            // Look the menu up by id: it lives on <body> once it has been opened,
            // so it is no longer the button's next sibling.
            const menu = document.getElementById(this.dataset.menuId || '');
            if (!menu || !menu.classList.contains('citation-menu-pop')) return;

            const wasOpen = menu.classList.contains('show');
            closeCitationMenus();
            if (wasOpen) return;

            if (menu.parentElement !== document.body) {
                document.body.appendChild(menu);
            }
            menu.classList.add('show');
            this.setAttribute('aria-expanded', 'true');

            // Anchor it under its own button, flipping upward and shifting
            // sideways whenever it would leave the screen.
            const buttonRect = this.getBoundingClientRect();
            const menuWidth  = menu.offsetWidth  || 235;
            const menuHeight = menu.offsetHeight || 210;

            let top = buttonRect.bottom + window.scrollY + 6;
            if (buttonRect.bottom + menuHeight + 12 > window.innerHeight) {
                top = Math.max(window.scrollY + 8, buttonRect.top + window.scrollY - menuHeight - 6);
            }

            let left = buttonRect.right + window.scrollX - menuWidth;
            left = Math.min(Math.max(left, window.scrollX + 8), window.scrollX + document.documentElement.clientWidth - menuWidth - 8);

            menu.style.top  = `${Math.round(top)}px`;
            menu.style.left = `${Math.round(left)}px`;
        });
    });

    document.addEventListener('click', closeCitationMenus);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') closeCitationMenus();
    });
    window.addEventListener('scroll', closeCitationMenus, true);

    const cancelModalEl = document.getElementById('cancelTicketModal');
    const cancelForm    = document.getElementById('cancelTicketForm');
    if (cancelModalEl && cancelForm) {
        const cancelModal = new bootstrap.Modal(cancelModalEl);
        document.querySelectorAll('.btn-cancel-ticket').forEach(btn => {
            btn.addEventListener('click', function () {
                cancelForm.action = `<?= base_url('officer/cancel') ?>/${this.dataset.id}`;
                cancelModal.show();
            });
        });
        cancelForm.addEventListener('submit', function () {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cancelling...';
            }
        });
    }

    // Deleting a ticket is permanent, so it asks in the same warning dialog the
    // rest of the system uses, with the icon and the confirm button in red so
    // the destructive answer is the one that looks dangerous.
    const deleteForm = document.getElementById('deleteTicketForm');

    if (deleteForm) {
        document.querySelectorAll('.btn-delete-ticket').forEach(btn => {
            btn.addEventListener('click', function () {
                const ticketId = this.dataset.ticket || ('#' + this.dataset.id);
                const action   = `<?= base_url('officer/delete') ?>/${this.dataset.id}`;

                const ask = window.AppConfirm
                    ? window.AppConfirm({
                        title: 'Delete this ticket?',
                        html: 'Ticket <strong>' + ticketId + '</strong> and all of its infractions will be removed from the records. This cannot be undone.',
                        icon: 'warning',
                        // The shared warning icon is amber; this dialog is the one
                        // that removes a ticket for good, so it is allowed to be red.
                        customClass: { popup: 'swal-danger' },
                        confirmText: '<i class="bi bi-trash3 me-1"></i> Delete',
                        confirmColor: '#be123c',
                        cancelText: 'Cancel',
                        cancelColor: '#475569'
                    })
                    : Promise.resolve({ isConfirmed: window.confirm('Delete ticket ' + ticketId + '? This cannot be undone.') });

                ask.then(function (result) {
                    if (!result || !result.isConfirmed) return;
                    deleteForm.action = action;
                    deleteForm.submit();
                });
            });
        });
    }
})();
</script>
<script>
<?= $this->include('officer/partials/edit_ticket_modal_script') ?>
</script>
<script>
/* Violator photo: upload, live preview, remove — mirrors the officer profile
   photo flow so both pages behave the same way. */
(function () {
    const form = document.getElementById('violatorPhotoForm');
    if (!form) return;

    const input     = document.getElementById('violatorPhotoInput');
    const display   = document.getElementById('violatorAvatarDisplay');
    const spinner   = document.getElementById('violatorPhotoSpinner');
    const btnRemove = document.getElementById('btnRemoveViolatorPhoto');

    const fallbackInitials = <?= json_encode($heroInitials) ?>;
    const removeUrl        = <?= json_encode(base_url('officer/violator/' . $photoViolatorId . '/photo/remove')) ?>;

    function showToast(icon, title) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true, icon: icon, title: title });
        }
    }

    function showError(title, text) {
        if (window.Swal) {
            Swal.fire({ icon: 'error', title: title, text: text, confirmButtonColor: '#2563eb' });
        } else {
            window.alert(text);
        }
    }

    function refreshCsrf(data) {
        if (!data.csrf_token || !data.csrf_hash) return;
        document.querySelectorAll('input[name="' + data.csrf_token + '"]').forEach(function (field) {
            field.value = data.csrf_hash;
        });
    }

    function setInitials(text) {
        const span = document.createElement('span');
        span.id = 'violatorAvatarInitials';
        span.className = 'vp-avatar-initials';
        span.textContent = text;
        display.replaceChildren(span);
    }

    if (input) {
        input.addEventListener('change', async function () {
            const file = this.files && this.files[0];
            if (!file) return;

            const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!validTypes.includes((file.type || '').toLowerCase())) {
                showError('Unsupported Image Format', 'Please choose a valid JPG, PNG, or WEBP photo.');
                this.value = '';
                return;
            }

            if (file.size > 10 * 1024 * 1024) {
                showError('File Too Large', 'Photo must be under 10MB.');
                this.value = '';
                return;
            }

            const previousHTML = display.innerHTML;
            const previewUrl   = URL.createObjectURL(file);
            display.innerHTML  = '<img src="' + previewUrl + '" alt="Photo preview" id="violatorAvatarImg" class="vp-avatar-img">';
            if (spinner) spinner.classList.remove('d-none');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form)
                });
                const data = await response.json().catch(function () { return {}; });

                if (response.ok && data.status === 'success') {
                    display.innerHTML = '<img src="' + data.photo_url + '?t=' + Date.now() + '" alt="Violator photo" id="violatorAvatarImg" class="vp-avatar-img">';
                    if (btnRemove) btnRemove.classList.remove('d-none');
                    refreshCsrf(data);
                    showToast('success', data.message || 'Violator photo updated.');
                } else {
                    throw new Error(data.message || 'Could not save the photo.');
                }
            } catch (err) {
                display.innerHTML = previousHTML;
                showError('Upload Failed', err.message || 'Failed to upload the photo. Please try again.');
            } finally {
                if (spinner) spinner.classList.add('d-none');
                input.value = '';
                URL.revokeObjectURL(previewUrl);
            }
        });
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', function () {
            const ask = window.AppConfirm
                ? window.AppConfirm({
                    title: 'Remove this photo?',
                    html: 'The photo will be deleted and the record falls back to its initials.',
                    confirmText: '<i class="bi bi-trash3 me-1"></i> Remove',
                    confirmColor: '#be123c',
                    customClass: { popup: 'swal-danger' }
                })
                : Promise.resolve({ isConfirmed: window.confirm('Remove this violator photo?') });

            ask.then(async function (result) {
                if (!result || !result.isConfirmed) return;

                if (spinner) spinner.classList.remove('d-none');

                try {
                    const body      = new FormData();
                    const csrfInput = form.querySelector('input[type="hidden"]');
                    if (csrfInput) body.append(csrfInput.name, csrfInput.value);

                    const response = await fetch(removeUrl, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: body
                    });
                    const data = await response.json().catch(function () { return {}; });

                    if (response.ok && data.status === 'success') {
                        setInitials(data.initials || fallbackInitials);
                        btnRemove.classList.add('d-none');
                        refreshCsrf(data);
                        showToast('success', data.message || 'Violator photo removed.');
                    } else {
                        throw new Error(data.message || 'Could not remove the photo.');
                    }
                } catch (err) {
                    showError('Error', err.message || 'Failed to remove the photo.');
                } finally {
                    if (spinner) spinner.classList.add('d-none');
                }
            });
        });
    }
})();
</script>
<?= $this->endSection() ?>
