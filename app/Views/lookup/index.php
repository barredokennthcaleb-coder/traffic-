<?php
// Search-term highlighting: wrap each occurrence of what the visitor typed in
// a <mark> so the matched MTOP, plate or ticket value is unmistakable. The
// surrounding text is escaped exactly as before.
$lkHighlight = static function (string $value, string $term): string {
    if ($term === '' || $value === '') {
        return esc($value);
    }

    $needle    = mb_strtolower($term, 'UTF-8');
    $haystack  = mb_strtolower($value, 'UTF-8');
    $needleLen = mb_strlen($needle, 'UTF-8');
    $offset    = 0;
    $html      = '';

    while (($pos = mb_strpos($haystack, $needle, $offset)) !== false) {
        $html .= esc(mb_substr($value, $offset, $pos - $offset, 'UTF-8'));
        $html .= '<mark class="lk-match">' . esc(mb_substr($value, $pos, $needleLen, 'UTF-8')) . '</mark>';
        $offset = $pos + $needleLen;
    }

    return $html . esc(mb_substr($value, $offset, null, 'UTF-8'));
};

$lkIsMatch = static fn (string $value, string $term): bool => $term !== ''
    && mb_stripos($value, $term, 0, 'UTF-8') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Public Citation & Violator Inquiry - Traffic System</title>
    <meta name="description" content="Public verification portal: search active traffic citations and violations, check offense history, and verify payment status instantly.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= base_url('lookup') ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Public Citation & Violation Inquiry">
    <meta property="og:description" content="Search active traffic violations, check offense attempt history, and verify payment status instantly.">
    <meta property="og:url" content="<?= base_url('lookup') ?>">
    <link rel="icon" type="image/png" href="<?= base_url('img/pic-1.png') ?>">
    <?= view('theme/pwa_head', ['pwaThemeDark' => '#0f172a', 'pwaThemeLight' => '#0f172a']) ?>
    <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('vendor/bootstrap-icons/bootstrap-icons.css') ?>">
    <link rel="stylesheet" href="<?= base_url('vendor/inter/inter.css') ?>">

    <style>
        :root {
            --brand-primary: #10b981;
            --surface-card: #ffffff;
            --surface-bg: #f8fafc;
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
            color: #5c6b80;
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
            color: #5c6b80;
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

        /* Fixed columns keep the citation, plate, and name on one line instead
           of letting the browser squeeze them until they break mid-word. */
        .lookup-table {
            table-layout: fixed;
        }

        .lookup-ticket-id,
        .lookup-plate,
        .lookup-mtop {
            white-space: nowrap;
        }

        /* The value the visitor searched for is marked inside the badge, and a
           ring is added to the whole field so the match is easy to spot. The
           portal's emerald brand colour is used instead of yellow: amber is
           reserved for pending/sanction warnings, so a green mark reads as a
           successful match rather than an alert. */
        .lk-match {
            background: #34d399;
            color: #052e2b;
            padding: 0 0.18em;
            border-radius: 4px;
            font-weight: 800;
            box-shadow: 0 0 0 1px rgba(16, 185, 129, 0.65);
        }

        .lk-field-match {
            border-color: rgba(52, 211, 153, 0.8) !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.22);
        }

        .lookup-ticket-id.lk-field-match {
            background: rgba(16, 185, 129, 0.14);
            border-radius: 6px;
        }

        /* MTOP is shown from the ticket/violator record kept by the enforcer
           and admin. Its cool tint keeps it distinct from the plate badge. */
        .lookup-mtop {
            background: rgba(14, 165, 233, 0.14);
            border: 1px solid rgba(14, 165, 233, 0.4);
            color: #7dd3fc;
            letter-spacing: 0.05em;
        }

        .lookup-mtop.lk-field-match {
            border-color: rgba(52, 211, 153, 0.8) !important;
        }

        .table-glass thead th {
            background: rgba(15, 23, 42, 0.9) !important;
            color: #5c6b80 !important;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            white-space: nowrap;
        }

        .table-glass tbody td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
            background-color: transparent !important;
        }

        .table-glass tbody tr:hover td {
            background-color: rgba(255, 255, 255, 0.03) !important;
        }

        /* The name carried by the citation: the owner/driver it was issued to.
           Long names may wrap onto a second line, but never break mid-word. */
        .lookup-person {
            display: inline;
            color: #f1f5f9;
            font-size: 0.95rem;
            font-weight: 600;
            line-height: 1.35;
            overflow-wrap: break-word;
        }

        /* The name column is centred on desktop so the header and the name
           sit together in the middle of the column. Mobile cards keep their
           left-aligned label/value layout. */
        .lookup-name-head,
        .lookup-name-cell {
            text-align: center;
        }

        /* Every offense on a ticket, priced on its own line so the reader can
           see how the Fine Amount column adds up. */
        .violation-breakdown {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .violation-breakdown li {
            display: flex;
            align-items: baseline;
            gap: 0.6rem;
            padding: 0.15rem 0;
            font-size: 0.84rem;
            line-height: 1.4;
        }

        .violation-breakdown .vb-name {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
            flex: 1 1 auto;
            min-width: 0;
            color: #e2e8f0;
        }

        /* A dotted leader runs from the name to its price, so each amount
           reads as belonging to its own offense instead of to the row. It is a
           flex item of the name itself, so it fills the leftover space instead
           of taking a line of its own. */
        .violation-breakdown .vb-name::after {
            content: '';
            flex: 1 1 auto;
            min-width: 1.25rem;
            border-bottom: 1px dotted rgba(255, 255, 255, 0.22);
            transform: translateY(-0.28em);
        }

        .violation-breakdown .vb-amount {
            flex: 0 0 auto;
            font-weight: 700;
            color: #a7f3d0;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        /* The Violation Type header carries both labels: the offense names sit
           on the left, the fines on the right edge of the same column, so each
           price reads as that offense's fine. */
        .lookup-th-pair {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.6rem;
        }

        .lookup-th-pair span:last-child {
            text-align: right;
            white-space: nowrap;
        }

        /* Sanction highlighting: only the sanction badge carries colour. A
           violator on their 2nd sanction gets an amber badge (a warning, not
           an alarm) and 3 or more sanctions turn it red; rows stay
           uncoloured. The badge states the sanction itself ("2nd Sanction",
           "3rd Sanction", ...), so the highlight always explains its count. */
        .lookup-sanction-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            margin-left: 0.45rem;
            padding: 0.05rem 0.5rem;
            border-radius: 999px;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
            vertical-align: 1px;
        }

        .lookup-sanction-chip--second {
            background: rgba(251, 191, 36, 0.15);
            border: 1px solid rgba(251, 191, 36, 0.45);
            color: #fcd34d;
        }

        .lookup-sanction-chip--repeat {
            background: rgba(248, 113, 113, 0.15);
            border: 1px solid rgba(248, 113, 113, 0.45);
            color: #fca5a5;
        }

        /* Per-ticket sanction badge sits under the ticket number. */
        .lookup-ticket-sanction {
            margin-top: 0.35rem;
        }

        .lookup-ticket-sanction .lookup-sanction-chip {
            margin-left: 0;
        }

        .lookup-sanction-legend,
        .lookup-hint-legend {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.6rem 1.25rem;
            background: rgba(15, 23, 42, 0.55);
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            color: #94a3b8;
            font-size: 0.78rem;
        }

        .lookup-sanction-legend i {
            color: #fbbf24;
        }

        .lookup-hint-legend i {
            color: #34d399;
        }

        /* Grand total of everything the search returned. */
        .lookup-total-row td {
            background: rgba(15, 23, 42, 0.75);
            border-top: 1px solid rgba(255, 255, 255, 0.14) !important;
            padding: 1rem 1.25rem !important;
        }

        .lookup-total-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.5rem 1rem;
        }

        .lookup-total-label {
            color: #cbd5e1;
            font-weight: 700;
            font-size: 0.95rem;
        }

        .lookup-total-count {
            display: block;
            color: #64748b;
            font-weight: 500;
            font-size: 0.78rem;
            text-transform: none;
            letter-spacing: normal;
        }

        /* The footer figure is the pending balance only — already-paid
           amounts are never added in — so it reads in the pending amber. */
        .lookup-total-amount {
            color: #fbbf24;
            font-weight: 800;
            font-size: 1.35rem;
            font-variant-numeric: tabular-nums;
            letter-spacing: -0.01em;
        }

        .lookup-total-settled {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            color: #34d399;
            font-weight: 800;
            font-size: 1.05rem;
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
            color: #5c6b80;
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

            /* ── ONE CITATION PER CARD ──
               A wide table cannot be read on a phone without dragging it
               sideways, so each record becomes its own card: ticket number
               and status on top, then the details underneath. Every field is
               still shown, with its column name as the label. */
            .lookup-table-head {
                flex-wrap: wrap;
                gap: 0.5rem 0.75rem;
            }
            .lookup-table-title {
                font-size: 1rem !important;
                line-height: 1.35;
            }
            .lookup-table {
                min-width: 0;
            }
            .lookup-table thead {
                display: none;
            }
            .lookup-table,
            .lookup-table tbody {
                display: block;
            }
            .lookup-table tr {
                display: flex;
                flex-wrap: wrap;
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid rgba(255, 255, 255, 0.08);
                border-radius: 14px;
                padding: 8px 0;
                margin: 0 10px 12px;
            }
            .lookup-table tbody tr:last-child {
                margin-bottom: 0;
            }
            .lookup-table tbody td {
                flex: 1 1 100%;
                border: 0 !important;
                padding: 7px 14px !important;
                text-align: left;
            }
            .lookup-table tbody td::before {
                content: attr(data-label);
                display: block;
                font-size: 0.64rem;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
                color: #5c6b80;
                margin-bottom: 2px;
            }
            /* The offense list keeps its own line: name and price on one
               row each, with the price never wrapping away from its name. */
            .violation-breakdown li {
                font-size: 0.8rem;
                gap: 0.5rem;
            }
            .violation-breakdown .vb-name {
                display: block;
                flex: 1 1 auto;
                min-width: 0;
            }
            .violation-breakdown .vb-name::after { display: none; }
            /* The grand total is not a data column, so it does not take the
               card treatment the record rows do. */
            .lookup-table tfoot,
            .lookup-table tfoot tr {
                display: block;
                width: 100%;
            }
            .lookup-table tfoot tr {
                margin: 0 10px 12px;
                padding: 0;
            }
            .lookup-table tfoot td {
                display: block;
                width: 100%;
                padding: 0.85rem 1rem !important;
                border: 1px solid rgba(255, 255, 255, 0.14) !important;
                border-radius: 14px;
                background: rgba(15, 23, 42, 0.75);
            }
            .lookup-table tfoot td::before { display: none; }
            .lookup-total-amount {
                font-size: 1.2rem;
            }

            .lookup-table td[data-label="Ticket #"]       { order: 1; flex: 1 1 45%; }
            .lookup-table td[data-label="Status"]         { order: 2; flex: 1 1 55%; text-align: right; }
            .lookup-table td[data-label="Name"]           { order: 3; }
            .lookup-table td[data-label="Plate Number"]   { order: 4; }
            .lookup-table td[data-label="MTOP #"]         { order: 5; }
            .lookup-table td[data-label="Violation Type"] { order: 6; }
            .lookup-table td[data-label="Date & Time"]    { order: 7; }
            .lookup-table td[data-label="Status"] .small  { display: block; }
            /* The name is the most human part of the card, so it reads larger
               than a detail line and sits directly under the ticket number. */
            .lookup-person {
                font-size: 0.95rem;
                line-height: 1.4;
            }
        }

        /* Compact stats and header on the smallest phones */
        @media (max-width: 576px) {
            .portal-header {
                padding: 1.4rem 0.85rem 0.9rem;
            }
            .hero-title {
                font-size: 1.35rem;
            }
            .hero-subtitle {
                font-size: 0.84rem;
            }
            .brand-badge {
                width: 60px;
                height: 60px;
                border-radius: 16px;
            }
            .search-card {
                padding: 0.95rem;
                border-radius: 16px;
            }
            .search-card .form-label {
                font-size: 0.84rem !important;
                line-height: 1.35;
                margin-bottom: 0.45rem !important;
            }
            .btn-search {
                min-height: 48px;
            }
            .results-container {
                margin-top: 1.25rem;
            }
            .stat-card {
                padding: 0.9rem 1rem;
                border-radius: 14px;
            }
            .stat-card:hover {
                transform: none;
            }
            .stat-icon {
                width: 38px;
                height: 38px;
                font-size: 1.05rem;
                border-radius: 10px;
            }
            .stat-card .fs-3 {
                font-size: 1.35rem !important;
            }

            .btn-clear-input {
                width: 36px;
                height: 36px;
            }

            .lookup-table tbody td {
                padding: 6px 14px !important;
            }
        }

        /* ══════════════════════════════════════════════════════════════
           SMOOTH DELIVERY LAYER — presentation and feedback only.
           The search process (form → results) is untouched.
           ══════════════════════════════════════════════════════════════ */

        html {
            scroll-behavior: smooth;
        }

        body {
            /* The footer breathes above the home bar on notched phones. */
            padding-bottom: calc(3rem + env(safe-area-inset-bottom));
            -webkit-tap-highlight-color: transparent;
        }

        /* Keyboard focus stays visible everywhere, including on the glass
           cards; a pointer tap does not paint a ring. */
        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        [role="button"]:focus-visible {
            outline: 3px solid #34d399 !important;
            outline-offset: 2px !important;
            border-radius: 10px;
        }

        a:focus:not(:focus-visible),
        button:focus:not(:focus-visible) {
            outline: none;
        }

        .btn-clear-input,
        .btn-search {
            touch-action: manipulation;
        }

        /* Money and counts line up the same way they do in the app. */
        .lookup-table td,
        .badge-status,
        .stat-card .fs-3 {
            font-variant-numeric: tabular-nums;
        }

        /* The three summary cards land one after the other instead of all at
           once, so the eye follows the numbers in order. */
        @keyframes lkRise {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .stat-card {
            animation: lkRise 0.34s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .stat-card:nth-child(1) { animation-delay: 0.04s; }
        .stat-card:nth-child(2) { animation-delay: 0.1s; }
        .stat-card:nth-child(3) { animation-delay: 0.16s; }

        /* The button reports the search while the page is on its way. */
        .lk-spinner {
            display: inline-block;
            width: 0.95em;
            height: 0.95em;
            margin-right: 0.45em;
            vertical-align: -0.1em;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: lkSpin 0.6s linear infinite;
        }

        @keyframes lkSpin {
            to { transform: rotate(360deg); }
        }

        .btn-search.is-searching {
            pointer-events: none;
            opacity: 0.85;
        }

        /* Readers who ask for less motion get the same screens, instantly. */
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after {
                animation-duration: 0.001ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.001ms !important;
            }
            /* Cancelling the reveal must not leave it at opacity 0. */
            .stat-card,
            .results-container {
                opacity: 1 !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Bar Navigation -->
    <div class="container py-3 d-flex justify-content-end align-items-center flex-wrap gap-2 gap-sm-3">
        <span class="text-white-50 small"><i class="bi bi-shield-check text-success me-1"></i> Public Official Verification Portal</span>
        <button type="button" class="pwa-install-inline pwa-install-inline--dark" data-pwa-install hidden>
            <i class="bi bi-download" aria-hidden="true"></i>
            Install app
        </button>
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
                    Search by Plate Number, Ticket #, or MTOP #
                </label>
                <div class="input-group search-input-group">
                    <div class="search-input-wrapper">
                        <input type="text" 
                               id="query" 
                               name="query" 
                               class="form-control" 
                               placeholder="Enter Plate No. (e.g. MYOP09), Ticket #, or MTOP #..."
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
                        <div class="lookup-table-head p-3 px-4 bg-dark bg-opacity-40 d-flex justify-content-between align-items-center border-bottom border-white border-opacity-10">
                            <span class="lookup-table-title fw-bold text-light fs-5">
                                <i class="bi bi-card-checklist text-success me-2"></i>Citation History Results for "<span class="text-success"><?= esc($query) ?></span>"
                            </span>
                            <span class="badge bg-secondary text-light px-3 py-2" style="font-size:0.8rem"><?= count($records) ?> Item<?= count($records) > 1 ? 's' : '' ?> Found</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle lookup-table">
                                <colgroup>
                                    <col style="width: 15%">
                                    <col style="width: 12%">
                                    <col style="width: 11%">
                                    <col style="width: 18%">
                                    <col style="width: 26%">
                                    <col style="width: 10%">
                                    <col style="width: 8%">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Ticket #</th>
                                        <th>Plate Number</th>
                                        <th>MTOP #</th>
                                        <th class="lookup-name-head">Name</th>
                                        <th>
                                            <div class="lookup-th-pair">
                                                <span>Violation Type</span>
                                                <span>Fine Amount</span>
                                            </div>
                                        </th>
                                        <th>Date & Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        // Anyone on their 2nd sanction is flagged amber and
                                        // anyone carrying 3 or more sanctions is flagged red;
                                        // the legend under the table explains both tiers. Only
                                        // the badge carries the colour — names stay plain.
                                        $hasSanctionedDriver = false;
                                        foreach ($records as $sanctionCheck) {
                                            if ((int) ($sanctionCheck['total_citations'] ?? 0) > 1) {
                                                $hasSanctionedDriver = true;
                                                break;
                                            }
                                        }

                                        // Only the outstanding (Pending) amounts are ever
                                        // totaled: an already-paid citation is settled, so
                                        // it never inflates what is still owed.
                                        $pendingTotal     = 0.0;
                                        $pendingCount     = 0;
                                        $settledCount     = 0;
                                        $pendingTicketIds = [];

                                        foreach ($records as $balanceCheck) {
                                            $balanceStatus = strtolower((string) ($balanceCheck['status'] ?? ''));

                                            if ($balanceStatus === 'pending') {
                                                $pendingTotal += (float) ($balanceCheck['penalty_amount'] ?? 0);
                                                $pendingCount++;
                                                $pendingTicketIds[(string) ($balanceCheck['ticket_id'] ?? '')] = true;
                                            } elseif ($balanceStatus === 'paid') {
                                                $settledCount++;
                                            }
                                        }

                                        $pendingTicketCount = count($pendingTicketIds);
                                    ?>
                                    <?php foreach ($records as $index => $r): ?>
                                        <?php
                                            $st = strtolower($r['status'] ?? 'pending');
                                            $badgeClass = $st === 'paid' ? 'badge-paid' : ($st === 'cancelled' ? 'badge-cancelled' : 'badge-pending');
                                            $rowCitations = (int) ($r['total_citations'] ?? 0);
                                            $rowIsSecond  = $rowCitations === 2;
                                            $rowIsRepeat  = $rowCitations >= 3;
                                            // The name printed on the citation; the
                                            // registered owner is the fallback for
                                            // legacy rows without a driver name.
                                            $rowDriver = trim((string) ($r['driver_name'] ?? ''));
                                            $rowOwner  = trim((string) ($r['owner_name'] ?? ''));
                                            $rowName   = $rowDriver !== '' ? $rowDriver : $rowOwner;
                                            $rowName   = preg_replace('/\s+/u', ' ', $rowName) ?? $rowName;

                                            // Names captured in block capitals are
                                            // softened to normal name case for the
                                            // public list; mixed-case names are left
                                            // exactly as the officer typed them.
                                            if ($rowName !== '' && $rowName === mb_strtoupper($rowName)) {
                                                $rowName = mb_convert_case(mb_strtolower($rowName, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
                                            }

                                            // The three fields a visitor can search by.
                                            $rowTicket = (string) ($r['ticket_id'] ?? '');
                                            $rowTicket = $rowTicket !== '' ? $rowTicket : 'N/A';
                                            $rowPlate  = strtoupper(trim((string) ($r['license_plate'] ?? '')));
                                            $rowPlate  = $rowPlate !== '' ? $rowPlate : '-';
                                            $rowMtop   = trim((string) ($r['mtop_number'] ?? ''));

                                            if ($rowMtop !== '' && \App\Libraries\ViolatorIdentity::isPlaceholder($rowMtop)) {
                                                $rowMtop = '';
                                            }
                                        ?>
                                        <tr>
                                            <td class="fw-bold text-success lookup-ticket-id<?= $lkIsMatch($rowTicket, $query) ? ' lk-field-match' : '' ?>" data-label="Ticket #">
                                                <?= $lkHighlight($rowTicket, $query) ?>
                                                <?php if ($rowIsSecond || $rowIsRepeat): ?>
                                                    <div class="lookup-ticket-sanction">
                                                        <span class="lookup-sanction-chip <?= $rowIsSecond ? 'lookup-sanction-chip--second' : 'lookup-sanction-chip--repeat' ?>" title="<?= esc(sanction_label($rowCitations), 'attr') ?> on file — cancelled (void) citations are not counted"><i class="bi <?= $rowIsSecond ? 'bi-exclamation-circle-fill' : 'bi-exclamation-triangle-fill' ?>"></i><?= esc(sanction_label($rowCitations)) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Plate Number">
                                                <span class="badge bg-dark border border-secondary text-light px-2 py-1 lookup-plate<?= $lkIsMatch($rowPlate, $query) ? ' lk-field-match' : '' ?>" style="letter-spacing:0.05em">
                                                    <?= $lkHighlight($rowPlate, $query) ?>
                                                </span>
                                            </td>
                                            <td data-label="MTOP #">
                                                <?php if ($rowMtop !== ''): ?>
                                                    <span class="badge lookup-mtop px-2 py-1<?= $lkIsMatch($rowMtop, $query) ? ' lk-field-match' : '' ?>" title="MTOP number on file">
                                                        <?= $lkHighlight($rowMtop, $query) ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-white-50">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="lookup-name-cell" data-label="Name">
                                                <?php if ($rowName !== ''): ?>
                                                    <span class="lookup-person"><?= esc($rowName) ?></span>
                                                <?php else: ?>
                                                    <span class="text-white-50">&mdash;</span>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Violation Type">
                                                <?php
                                                    // Every offense on this ticket, each with its own fine, so the
                                                    // reader can see how the ticket total was built. The plain list
                                                    // stays as the fallback for a row without the pairing.
                                                    $breakdown = $r['breakdown'] ?? [];
                                                ?>
                                                <?php if ($breakdown !== []): ?>
                                                    <ul class="violation-breakdown">
                                                        <?php foreach ($breakdown as $offense): ?>
                                                            <li>
                                                                <span class="vb-name"><?= esc($offense['type']) ?></span>
                                                                <span class="vb-amount">₱<?= number_format($offense['amount'], 2) ?></span>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <ul class="violation-breakdown">
                                                        <li>
                                                            <span class="vb-name"><?= esc($r['violation_type'] ?? 'Traffic Violation') ?></span>
                                                            <span class="vb-amount">₱<?= number_format((float) ($r['penalty_amount'] ?? 0), 2) ?></span>
                                                        </li>
                                                    </ul>
                                                <?php endif; ?>
                                                <?php if (!empty($r['location'])): ?>
                                                    <small class="text-white-50"><i class="bi bi-geo-alt me-1"></i><?= esc($r['location']) ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td data-label="Date &amp; Time">
                                                <div class="text-light small"><?= esc(date('M d, Y', strtotime($r['violation_date'] ?? $r['created_at']))) ?></div>
                                                <small class="text-white-50"><?= esc(date('h:i A', strtotime($r['violation_date'] ?? $r['created_at']))) ?></small>
                                            </td>
                                            <td data-label="Status">
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
                                                    <div class="small text-white-50 mt-1" style="font-size:0.72rem" title="Official Receipt (OR) number">OR (Official Receipt) #: <?= esc($r['receipt_number']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="lookup-total-row">
                                        <td colspan="7" data-label="Total">
                                            <div class="lookup-total-inner">
                                                <span class="lookup-total-label">
                                                    <i class="bi bi-calculator me-2"></i>Total Pending
                                                    <span class="lookup-total-count">
                                                        <?php if ($pendingCount > 0): ?>
                                                            <?= $pendingCount ?> pending violation<?= $pendingCount === 1 ? '' : 's' ?>
                                                            across <?= $pendingTicketCount ?> ticket<?= $pendingTicketCount === 1 ? '' : 's' ?>
                                                            <?php if ($settledCount > 0): ?>
                                                                &middot; <?= $settledCount ?> already-paid citation<?= $settledCount === 1 ? '' : 's' ?> not included
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <?= count($records) ?> ticket<?= count($records) === 1 ? '' : 's' ?> on record &mdash; no pending balance
                                                        <?php endif; ?>
                                                    </span>
                                                </span>
                                                <?php /* Only pending balances are stated: an already-paid
                                                         citation is settled and never inflates the total. */ ?>
                                                <?php if ($pendingTotal > 0): ?>
                                                    <span class="lookup-total-amount">₱<?= number_format($pendingTotal, 2) ?></span>
                                                <?php else: ?>
                                                    <span class="lookup-total-settled"><i class="bi bi-check-circle-fill"></i>Fully settled</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <?php if ($searched && $query !== ''): ?>
                            <div class="lookup-hint-legend">
                                <i class="bi bi-highlighter"></i>
                                <span>Green marks the <strong style="color:#6ee7b7;">ticket number, plate or MTOP</strong> you searched for. MTOP is shown as recorded on the citation / violator record.</span>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($hasSanctionedDriver)): ?>
                            <div class="lookup-sanction-legend">
                                <i class="bi bi-shield-exclamation"></i>
                                <span>Badges flag the sanction on file: <strong style="color:#fcd34d;">amber</strong> = 2nd sanction, <strong style="color:#f87171;">red</strong> = 3 or more sanctions. Cancelled (void) citations are not counted, and only pending balances are totaled.</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="search-card empty-state text-center">
                        <i class="bi bi-shield-check text-success display-4 d-block mb-3"></i>
                        <h4 class="fw-bold text-light mb-2">No Active Violations Found</h4>
                        <p class="text-white-50 mb-4">
                            No citation records were found for "<strong><?= esc($query) ?></strong>". Please make sure the plate number or ticket number is typed correctly.
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

            // The button reports the trip to the server and ignores any second
            // tap while the first search is still in flight. The form action and
            // its result are unchanged — this is only the wait state.
            const searchForm = document.getElementById('searchForm');
            const searchBtn = searchForm ? searchForm.querySelector('.btn-search') : null;
            let searching = false;

            if (searchForm && searchBtn) {
                searchForm.addEventListener('submit', function (event) {
                    if (!queryInput || queryInput.value.trim() === '') {
                        return;
                    }

                    event.preventDefault();

                    if (searching) {
                        return;
                    }

                    searching = true;
                    searchBtn.classList.add('is-searching');
                    searchBtn.innerHTML = '<span class="lk-spinner" aria-hidden="true"></span> Searching…';

                    window.setTimeout(function () {
                        searchForm.submit();
                    }, 20);
                });

                // Coming back from the results page (or a bfcache restore) puts
                // the button back to normal without a reload.
                window.addEventListener('pageshow', function () {
                    searching = false;
                    searchBtn.classList.remove('is-searching');
                    searchBtn.innerHTML = '<i class="bi bi-search me-1"></i> Search';
                });
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
<?= view('theme/pwa_scripts') ?>
</body>
</html>
