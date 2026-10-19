<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Citation & Violator Inquiry - Traffic System</title>
    <link rel="icon" type="image/png" href="<?= base_url('img/pic-1.png') ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary: #10b981;
            --brand-dark: #0f172a;
            --surface-card: #ffffff;
            --surface-bg: #f8fafc;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            min-height: 100vh;
            color: #f8fafc;
            padding-bottom: 3rem;
        }

        .portal-header {
            padding: 2.5rem 1rem 1.5rem;
            text-align: center;
        }

        .brand-badge {
            width: 72px;
            height: 72px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 20px;
            padding: 10px;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .brand-badge img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .hero-title {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: #ffffff;
        }

        .hero-subtitle {
            color: #94a3b8;
            font-size: 0.98rem;
            max-width: 580px;
            margin: 0.5rem auto 0;
        }

        .search-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 22px;
            padding: 1.75rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            max-width: 760px;
            margin: 0 auto;
        }

        .search-input-wrapper {
            position: relative;
            flex: 1 1 auto;
        }

        .search-input-group .form-control {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 1.05rem;
            padding: 0.9rem 2.8rem 0.9rem 1.2rem;
            border-radius: 14px 0 0 14px;
            transition: all 0.2s ease;
        }

        .search-input-group .form-control::placeholder {
            color: #64748b;
        }

        .search-input-group .form-control:focus {
            background: rgba(15, 23, 42, 0.95);
            border-color: #10b981;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25);
            color: #ffffff;
        }

        .btn-clear-input {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: #94a3b8;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            z-index: 5;
        }

        .btn-clear-input:hover {
            color: #f1f5f9;
            transform: translateY(-50%) scale(1.15);
        }

        .btn-search {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: #ffffff;
            font-weight: 700;
            font-size: 1rem;
            padding: 0 1.8rem;
            border-radius: 0 14px 14px 0;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-search:hover {
            filter: brightness(1.1);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(16, 185, 129, 0.35);
            color: #ffffff;
        }

        .btn-search:active {
            transform: translateY(0) scale(0.98);
        }

        .results-container {
            max-width: 1080px;
            margin: 2rem auto 0;
            animation: fadeInSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes fadeInSlideUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .stat-card {
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 1.25rem;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255, 255, 255, 0.18);
            box-shadow: 0 12px 25px -6px rgba(0, 0, 0, 0.35);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .table-glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.3);
        }

        .table-glass table {
            color: #e2e8f0;
            margin-bottom: 0;
            --bs-table-bg: transparent;
            --bs-table-color: #e2e8f0;
        }

        .table-glass thead th {
            background: rgba(15, 23, 42, 0.9) !important;
            color: #94a3b8 !important;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .table-glass tbody td {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
            background-color: transparent !important;
        }

        .table-glass tbody tr:hover td {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        .badge-status {
            font-weight: 700;
            padding: 0.4em 0.8em;
            border-radius: 8px;
            font-size: 0.78rem;
            letter-spacing: 0.03em;
        }

        .badge-pending {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }

        .badge-paid {
            background: rgba(16, 185, 129, 0.15);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .badge-cancelled {
            background: rgba(148, 163, 184, 0.15);
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .empty-state {
            padding: 3.5rem 1.5rem;
            text-align: center;
        }

        .empty-state i {
            font-size: 3rem;
            color: #64748b;
            margin-bottom: 1rem;
        }

        .nav-back-btn {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #cbd5e1;
            padding: 0.5rem 1.2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.25s ease;
        }

        .nav-back-btn:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .portal-header {
                padding: 1.5rem 1rem 1rem;
            }
            .hero-title {
                font-size: 1.55rem;
            }
            .hero-subtitle {
                font-size: 0.88rem;
            }
            .search-card {
                padding: 1.25rem;
                border-radius: 18px;
            }
            .search-input-group {
                flex-direction: column;
                gap: 0.6rem;
            }
            .search-input-wrapper .form-control {
                border-radius: 12px !important;
            }
            .btn-search {
                border-radius: 12px !important;
                padding: 0.8rem 1.5rem;
                width: 100%;
            }
            .table-glass thead th,
            .table-glass tbody td {
                padding: 0.75rem 0.85rem;
                font-size: 0.82rem;
            }
        }
    </style>
</head>
<body>

    <!-- Top Bar Navigation -->
    <div class="container py-3 d-flex justify-content-between align-items-center">
        <a href="<?= base_url('login') ?>" class="nav-back-btn">
            <i class="bi bi-arrow-left me-1"></i> Back to System Sign In
        </a>
        <span class="text-white-50 small"><i class="bi bi-shield-check text-success me-1"></i> Public Official Verification Portal</span>
    </div>

    <!-- Header Section -->
    <div class="portal-header">
        <div class="brand-badge">
            <img src="<?= base_url('img/pic-1.png') ?>" alt="Traffic System Logo">
        </div>
        <h1 class="hero-title">Public Citation & Violation Inquiry</h1>
        <p class="hero-subtitle">
            Search active traffic violations, check offense attempt history, and verify payment status instantly.
        </p>
    </div>

    <div class="container">
        <!-- Search Card -->
        <div class="search-card">
            <form action="<?= base_url('lookup') ?>" method="GET" id="searchForm" autocomplete="off">
                <label for="query" class="form-label text-white-50 fw-semibold mb-2" style="font-size: 0.9rem;">
                    Search by Plate Number, Ticket #, or Violator's Name
                </label>
                <div class="input-group search-input-group">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               id="query" 
                               name="query" 
                               class="form-control" 
                               placeholder="Enter Plate No. (e.g. MYOP09) or Name (e.g. Kenneth Barredo)..."
                               value="<?= esc($query) ?>"
                               autocomplete="off"
                               required
                               autofocus>
                        <button type="button" 
                                id="clearQueryBtn" 
                                class="btn-clear-input" 
                                title="Clear search" 
                                aria-label="Clear search"
                                style="<?= empty($query) ? 'display: none;' : '' ?>">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>
                    <button type="submit" class="btn btn-search">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
            </form>
            <div class="d-flex flex-wrap gap-2 mt-3 text-white-50 small">
                <span class="fw-bold text-light me-1">Quick Search Examples:</span>
                <a href="<?= base_url('lookup?query=MYOP09') ?>" class="badge bg-secondary bg-opacity-25 text-light text-decoration-none px-2 py-1">MYOP09</a>
                <a href="<?= base_url('lookup?query=Janrey') ?>" class="badge bg-secondary bg-opacity-25 text-light text-decoration-none px-2 py-1">Janrey</a>
                <a href="<?= base_url('lookup?query=7890') ?>" class="badge bg-secondary bg-opacity-25 text-light text-decoration-none px-2 py-1">7890</a>
            </div>
        </div>

        <!-- Search Results Section -->
        <?php if ($searched): ?>
            <div class="results-container">
                <?php if (count($records) > 0): ?>
                    <!-- KPI Summary Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="stat-card d-flex align-items-center gap-3">
                                <div class="stat-icon bg-primary bg-opacity-20 text-primary">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>
                                <div>
                                    <div class="text-white-50 small fw-semibold">Total Violation Offenses</div>
                                    <div class="fs-3 fw-bold text-white"><?= $stats['total'] ?> Record<?= $stats['total'] > 1 ? 's' : '' ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="stat-card d-flex align-items-center gap-3">
                                <div class="stat-icon bg-warning bg-opacity-20 text-warning">
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div>
                                    <div class="text-white-50 small fw-semibold">Pending Citations</div>
                                    <div class="fs-3 fw-bold text-warning">
                                        <?= $stats['pending_count'] ?> <span class="fs-6 text-white-50 fw-normal">(₱<?= number_format($stats['pending_amount'], 2) ?>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="stat-card d-flex align-items-center gap-3">
                                <div class="stat-icon bg-success bg-opacity-20 text-success">
                                    <i class="bi bi-check-circle-fill"></i>
                                </div>
                                <div>
                                    <div class="text-white-50 small fw-semibold">Paid / Cleared</div>
                                    <div class="fs-3 fw-bold text-success">
                                        <?= $stats['paid_count'] ?> <span class="fs-6 text-white-50 fw-normal">(₱<?= number_format($stats['paid_amount'], 2) ?>)</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Records Table -->
                    <div class="table-glass mb-4">
                        <div class="p-3 px-4 bg-dark bg-opacity-40 d-flex justify-content-between align-items-center border-bottom border-white border-opacity-10">
                            <span class="fw-bold text-light fs-5">
                                <i class="bi bi-card-checklist text-success me-2"></i>Citation History Results for "<span class="text-success"><?= esc($query) ?></span>"
                            </span>
                            <span class="badge bg-secondary text-light px-3 py-2" style="font-size:0.8rem"><?= count($records) ?> Item<?= count($records) > 1 ? 's' : '' ?> Found</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Violator / Driver</th>
                                        <th>Plate Number</th>
                                        <th>Violation Type</th>
                                        <th>Date & Time</th>
                                        <th>Fine Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($records as $index => $r): ?>
                                        <?php 
                                            $st = strtolower($r['status'] ?? 'pending');
                                            $badgeClass = $st === 'paid' ? 'badge-paid' : ($st === 'cancelled' ? 'badge-cancelled' : 'badge-pending');
                                        ?>
                                        <tr>
                                            <td class="fw-bold text-success">
                                                <?= esc($r['ticket_id'] ?? 'N/A') ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-light"><?= esc($r['driver_name'] ?? ($r['first_name'] . ' ' . $r['last_name'])) ?></div>
                                                <small class="text-white-50"><?= esc($r['address'] ?? 'No Address') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark border border-secondary text-light px-2 py-1" style="letter-spacing:0.05em">
                                                    <?= esc(strtoupper($r['license_plate'] ?? '-')) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-light fw-medium"><?= esc($r['violation_type'] ?? 'Traffic Violation') ?></div>
                                                <?php if (!empty($r['location'])): ?>
                                                    <small class="text-white-50"><i class="bi bi-geo-alt me-1"></i><?= esc($r['location']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="text-light small"><?= esc(date('M d, Y', strtotime($r['violation_date'] ?? $r['created_at']))) ?></div>
                                                <small class="text-white-50"><?= esc(date('h:i A', strtotime($r['violation_date'] ?? $r['created_at']))) ?></small>
                                            </td>
                                            <td class="fw-bold text-light">
                                                ₱<?= number_format((float)($r['penalty_amount'] ?? 0), 2) ?>
                                            </td>
                                            <td>
                                                <span class="badge-status <?= $badgeClass ?>">
                                                    <?php if ($st === 'paid'): ?>
                                                        <i class="bi bi-check-circle-fill me-1"></i>PAID
                                                    <?php elseif ($st === 'cancelled'): ?>
                                                        <i class="bi bi-x-circle-fill me-1"></i>CANCELLED
                                                    <?php else: ?>
                                                        <i class="bi bi-hourglass-split me-1"></i>PENDING
                                                    <?php endif; ?>
                                                </span>
                                                <?php if ($st === 'paid' && !empty($r['receipt_number'])): ?>
                                                    <div class="small text-white-50 mt-1" style="font-size:0.72rem">OR #: <?= esc($r['receipt_number']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="search-card empty-state text-center">
                        <i class="bi bi-shield-check text-success display-4 d-block mb-3"></i>
                        <h4 class="fw-bold text-light mb-2">No Active Violations Found</h4>
                        <p class="text-white-50 mb-4">
                            No citation records were found for "<strong><?= esc($query) ?></strong>". Please make sure the plate number or driver name is typed correctly.
                        </p>
                        <a href="<?= base_url('lookup') ?>" class="btn btn-outline-light rounded-pill px-4">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Try Another Search
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const queryInput = document.getElementById('query');
            const clearBtn = document.getElementById('clearQueryBtn');

            // Toggle clear button on user typing
            if (queryInput && clearBtn) {
                queryInput.addEventListener('input', function () {
                    clearBtn.style.display = this.value.trim().length > 0 ? 'flex' : 'none';
                });

                // Clear button click handler
                clearBtn.addEventListener('click', function () {
                    queryInput.value = '';
                    clearBtn.style.display = 'none';
                    queryInput.focus();
                    <?php if ($searched): ?>
                    // If results are currently showing, return to the fresh blank search page
                    window.location.href = "<?= base_url('lookup') ?>";
                    <?php endif; ?>
                });
            }

            // Clean the browser address bar back to base /lookup without reloading.
            // This ensures that when the user presses Browser Refresh (F5 / Ctrl+R / Reload button),
            // the browser reloads clean /lookup with an empty search box and no stale search query!
            if (window.history && window.history.replaceState && window.location.search) {
                window.history.replaceState(null, document.title, window.location.pathname);
            }
        });

        // Safeguard for browser reload / refresh:
        // Detects if the page was refreshed. If any query string was still in the reload request,
        // it immediately cleans to base pathname, wipes the input to blank, and sets focus to type again.
        window.addEventListener('pageshow', function () {
            let isReload = false;
            try {
                const nav = performance.getEntriesByType && performance.getEntriesByType('navigation');
                if (nav && nav.length > 0 && nav[0].type === 'reload') {
                    isReload = true;
                } else if (window.performance && window.performance.navigation && window.performance.navigation.type === 1) {
                    isReload = true;
                }
            } catch (e) {}

            if (isReload) {
                if (window.location.search) {
                    window.location.replace(window.location.pathname);
                    return;
                }
                const queryInput = document.getElementById('query');
                if (queryInput) {
                    queryInput.value = '';
                    queryInput.focus();
                }
                const clearBtn = document.getElementById('clearQueryBtn');
                if (clearBtn) {
                    clearBtn.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
