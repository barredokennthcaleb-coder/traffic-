<style>
/* ═══ Screen ═══ */
.vpr-toolbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap; max-width: 960px; margin: 0 auto 16px;
}
.vpr-back-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .5rem 1rem; border-radius: 10px;
    border: 1px solid var(--border-soft, #e2e8f0);
    background: var(--surface-card, #fff);
    color: var(--text-body, #334155);
    font-size: .88rem; font-weight: 700; text-decoration: none;
}
.vpr-crumb { color: var(--text-subtle, #94a3b8); }
.vpr-crumb-current { font-weight: 700; color: var(--text-strong, #0f172a); font-size: .9rem; }
.vpr-select { width: auto; min-width: 96px; }

.vpr-page .report-document-card {
    max-width: 960px; margin: 0 auto;
    background: #fff; border: 2px solid #1e293b;
    padding: 36px 44px;
    font-family: 'Times New Roman', Times, serif;
    color: #0f172a;
    box-shadow: 0 14px 35px rgba(0,0,0,.1);
    border-radius: 6px;
}

/* ── Screen: the same sheet, lit up ────────────────────────────────
   Soft tints and one accent per block keep the document alive without
   turning it into a dashboard. Paper is unaffected: print stays black. */
.vpr-page .report-document-card .table-responsive,
.vpr-page .report-document-card .vsum-matrix-scroll { overflow: visible !important; }

@media screen {
    .vpr-page .report-document-card {
        background: #fff; border: 1px solid #dde5ef; border-radius: 14px;
        box-shadow: 0 24px 50px -30px rgba(15, 23, 42, .45), 0 2px 6px rgba(15, 23, 42, .04);
        -webkit-font-smoothing: antialiased;
    }
    .vpr-page .rep-sub,
    .vpr-page .rep-city,
    .vpr-page .rep-period { color: #64748b; }
    .vpr-page .report-document-card .rep-main-title {
        text-decoration-color: #2563eb; text-underline-offset: 4px;
    }

    .vpr-page .report-meta-bar {
        background: #f8fafc; border: 1px solid #e6ecf4; border-left: 3px solid #2563eb;
        border-radius: 8px; color: #334155;
    }

    .vpr-page .vsum-kpi { border-radius: 12px; padding: 12px 14px; }
    .vpr-page .vsum-kpi:nth-child(1) { background: #eff6ff; border-color: #dbeafe; border-left: 3px solid #2563eb; }
    .vpr-page .vsum-kpi:nth-child(2) { background: #f5f3ff; border-color: #ede9fe; border-left: 3px solid #7c3aed; }
    .vpr-page .vsum-kpi:nth-child(3) { background: #ecfdf5; border-color: #d1fae5; border-left: 3px solid #0d9488; }
    .vpr-page .vsum-kpi:nth-child(4) { background: #fffbeb; border-color: #fef3c7; border-left: 3px solid #d97706; }
    .vpr-page .vsum-kpi-label,
    .vpr-page .vsum-kpi-sub { color: #64748b; }
    .vpr-page .vsum-kpi-value { color: #0f172a; }

    .vpr-page .vsum-recon {
        background: #f5f9ff; border: 1px solid #dbeafe; border-left: 3px solid #2563eb;
        border-radius: 8px; color: #1e3a8a;
    }
    .vpr-page .vsum-recon i { color: #2563eb; }

    .vpr-page .report-sec-title { border-bottom: 2px solid #e6ecf4; }
    .vpr-page .report-sec-title i { color: #2563eb; }
    .vpr-page .vsum-sec-note,
    .vpr-page .vpr-time { color: #64748b; }

    /* The app shell paints tables in the dark theme, so these need to win. */
    .vpr-page .report-table th {
        background: #f2f6fb !important; color: #334155 !important; border-color: #e3eaf3 !important;
    }
    .vpr-page .report-table td {
        background: #fff !important; color: #1f2937 !important; border-color: #edf1f7 !important;
    }
    .vpr-page .report-table tbody tr:nth-child(even) td { background: #fbfcfe !important; }
    .vpr-page .report-total-row td {
        background: #eef4ff !important; color: #0f172a !important; border-color: #dbe6f7 !important;
    }
    .vpr-page .vpr-mx-total { background: #f7fafd !important; }
    .vpr-page .vpr-matrix tr.vpr-mx-idle td { color: #94a3b8 !important; }

    .vpr-page .vpr-idblock { background: #fbfdff; border: 1px solid #e6ecf4; border-radius: 12px; }
    .vpr-page .vpr-id-avatar { background: linear-gradient(135deg, #2563eb, #4f46e5); color: #fff; }
    .vpr-page .vpr-id-sub { color: #64748b; }
    .vpr-page .report-sign-block .text-muted { color: #64748b !important; }

    /* Rows stay put: no lift, no tint, no fade while reading across. */
    .vpr-page .report-document-card .table tbody tr,
    .vpr-page .report-document-card .table tbody tr:hover {
        background: transparent !important;
        transform: none !important;
        transition: none !important;
    }
}
/* The seal sits inside the left margin, so the letterhead keeps a matching
   right margin and its lines wrap instead of running under the seal. */
.vpr-page .report-header { position: relative; text-align: center; border-bottom: 2px solid #000; padding: 0 96px 14px; margin-bottom: 18px; }
.vpr-page .report-header-seal { position: absolute; left: 4px; top: 0; }
.vpr-page .report-seal-img { width: 82px; height: 82px; object-fit: contain; }
.vpr-page .report-header-title { display: inline-block; max-width: 700px; }
.vpr-page .rep-sub { font-size: .85rem; letter-spacing: .05em; text-transform: uppercase; }
.vpr-page .rep-office { font-size: .95rem; font-weight: bold; letter-spacing: .04em; }
.vpr-page .rep-city { font-size: .85rem; }
.vpr-page .rep-dept { font-size: .95rem; font-weight: bold; margin-top: 4px; letter-spacing: .03em; }
.vpr-page .rep-main-title { font-size: 1.2rem; font-weight: 900; letter-spacing: .05em; margin: 10px 0 4px; text-decoration: underline; }
.vpr-page .rep-period { font-size: .85rem; }
.vpr-page .report-meta-bar {
    font-size: .82rem; padding: 6px 12px; border: 1px solid #cbd5e1;
    background: #f8fafc; border-radius: 4px; margin-bottom: 20px;
    font-family: system-ui, -apple-system, sans-serif;
}

/* Identity */
.vpr-idblock {
    display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap;
    border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px 14px; margin-bottom: 16px;
}
.vpr-id-name { display: flex; align-items: center; gap: 12px; }
.vpr-id-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: #1d4ed8; color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    font-family: system-ui, sans-serif; font-weight: 800; font-size: 1.05rem;
}
.vpr-id-fullname { font-size: 1.02rem; font-weight: 800; }
.vpr-id-sub { font-size: .8rem; color: #475569; }
.vpr-dot { margin: 0 4px; }
.vpr-standing { text-align: right; }
.vpr-standing-label { display: block; font-size: .68rem; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #64748b; }
.vpr-standing-value { font-size: .88rem; font-weight: 800; }
.vpr-standing--paid .vpr-standing-value { color: #166534; }
.vpr-standing--pending .vpr-standing-value { color: #b45309; }
.vpr-standing--cancelled .vpr-standing-value { color: #475569; }

/* Key figures — same strip the summary report prints */
.vsum-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 12px; }
.vsum-kpi { border: 1px solid #cbd5e1; border-left: 3px solid #1d4ed8; border-radius: 5px; padding: 9px 11px; }
.vsum-kpi-label { font-size: .68rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #475569; }
.vsum-kpi-value { font-size: 1.16rem; font-weight: 800; margin: 2px 0; }
.vsum-kpi-sub { font-size: .7rem; color: #475569; font-weight: 600; }
.vsum-recon {
    display: flex; align-items: flex-start; gap: 8px;
    font-family: system-ui, sans-serif; font-size: .78rem; line-height: 1.45;
    background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px;
    padding: 8px 11px; color: #334155; margin-bottom: 18px;
}
.vsum-recon i { color: #1d4ed8; margin-top: 2px; }

.report-section { margin-bottom: 20px; }
.vpr-page .report-sec-title {
    font-size: .96rem; font-weight: 800; color: #0f172a;
    border-bottom: 1.5px solid #0f172a; padding-bottom: 4px; margin-bottom: 6px;
}
.vsum-sec-note { font-size: .78rem; color: #475569; margin-bottom: 8px; }

/* Tables */
.vpr-page .report-table { font-size: .82rem; background: #fff; margin-bottom: 0; }
.vpr-page .report-table th {
    background: #e2e8f0; font-size: .68rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .03em; color: #1e293b;
    border-color: #94a3b8; vertical-align: middle;
}
.vpr-page .report-table td { border-color: #cbd5e1; vertical-align: middle; color: #0f172a; }
.vpr-page .report-total-row td { background: #f1f5f9; font-weight: 800; font-size: .74rem; }
.vpr-time { display: block; font-size: .7rem; color: #475569; font-weight: 700; }
.vpr-mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: .74rem; }
.vpr-none { color: #64748b; font-style: italic; }
.vpr-pill {
    display: inline-block; padding: 1px 7px; border-radius: 999px;
    font-family: system-ui, sans-serif; font-size: .64rem; font-weight: 800;
    text-transform: uppercase; letter-spacing: .04em;
}
.vpr-pill--paid { background: #dcfce7; color: #166534; }
.vpr-pill--pending { background: #fef3c7; color: #92400e; }
.vpr-pill--cancelled { background: #e2e8f0; color: #475569; }
.vpr-pill-note { display: block; font-size: .64rem; color: #475569; margin-top: 2px; }
.vpr-empty {
    border: 1px dashed #cbd5e1; border-radius: 6px; padding: 22px 14px;
    text-align: center; color: #475569; font-size: .84rem;
}
.vpr-empty i { display: block; font-size: 1.3rem; margin-bottom: 6px; color: #94a3b8; }

/* The tally grid: months down the rows, days of the month across */
.vsum-matrix-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
.vpr-matrix { table-layout: fixed; width: 100%; margin-bottom: 0; }
.vpr-matrix th, .vpr-matrix td { padding: 3px 0 !important; }
.vpr-mx-name {
    width: 11%; text-align: left !important; padding-left: 6px !important;
    font-size: .68rem !important; font-weight: 800 !important; white-space: nowrap;
}
.vpr-mx-day { width: auto; min-width: 0; text-align: center !important; font-size: .62rem !important; }
.vpr-mx-total {
    width: 7%; text-align: center !important; font-weight: 800 !important;
    font-size: .68rem !important; background: #f1f5f9;
}
.vpr-matrix .vpr-mx-hit { font-weight: 800 !important; color: #b91c1c; }
.vpr-matrix tr.vpr-mx-idle td { color: #64748b; }
.vpr-matrix tfoot td { font-weight: 800; font-size: .62rem !important; }
.vpr-mx-foot .vpr-mx-day { color: #b91c1c; }

/* Sign-off */
.report-sign-block { margin-top: 18px; }
.report-sign-block .row { display: flex; flex-wrap: nowrap; }
.report-sign-block .sig-container { min-height: 55px; }
.sig-line { width: 220px; border-bottom: 1.5px solid #000; margin-top: 34px; margin-bottom: 6px; }
.report-sign-block .fw-bold { font-size: .88rem; }
.report-sign-block .text-muted { font-size: .76rem; color: #000 !important; }

@media (max-width: 991px) {
    .vpr-page .report-document-card { padding: 22px 18px; }
    .vsum-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .vpr-standing { text-align: left; }
}

/* ═══ Print ═══ */
@page { margin: 8mm 10mm; size: A4 portrait; }

@media print {
    .no-print, .sidebar, .sidebar-toggle, .mobile-topbar,
    .desktop-header, .breadcrumb, .btn, .btn-group,
    .alert, nav, header, footer {
        display: none !important;
    }
    .main-content { margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
    .container-fluid { padding: 0 !important; }
    body { background: #fff !important; color: #0f172a !important; }

    .vpr-page .report-document-card {
        max-width: 100% !important; width: 100% !important;
        border: 2px solid #000 !important; box-shadow: none !important;
        padding: 14px 18px !important; margin: 0 !important;
        background: #fff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Every letter prints solid black on paper. */
    .report-document-card * { color: #000 !important; }
    .report-document-card .vpr-id-avatar {
        background: #fff !important; color: #000 !important;
        border: 1.5px solid #000 !important;
    }
    .report-document-card .vpr-pill {
        background: #fff !important; color: #000 !important;
        border: 1px solid #000 !important;
    }

    /* Colour is a screen effect: on paper every rule, tint and icon is black. */
    .report-document-card .vsum-kpi,
    .report-document-card .vsum-kpi:nth-child(1),
    .report-document-card .vsum-kpi:nth-child(2),
    .report-document-card .vsum-kpi:nth-child(3),
    .report-document-card .vsum-kpi:nth-child(4) {
        background: #fff !important; border-color: #000 !important; border-left-color: #000 !important;
    }
    .report-document-card .vsum-recon,
    .report-document-card .report-meta-bar,
    .report-document-card .vpr-idblock { background: #fff !important; border-color: #000 !important; }
    .report-document-card .report-sec-title,
    .report-document-card .report-sign-block .text-muted { border-bottom-color: #000 !important; }
    .report-document-card .report-sec-title i,
    .report-document-card .vsum-recon i { color: #000 !important; }
    .report-document-card .report-table tbody tr:nth-child(even) td { background: #fff !important; }
    .report-document-card .rep-main-title { text-decoration-color: #000 !important; }

    .report-header {
        min-height: auto !important; padding-bottom: 10px !important;
        margin-bottom: 10px !important; border-bottom: 2px solid #000 !important;
        page-break-inside: avoid !important; break-inside: avoid !important;
    }
    .report-seal-img { width: 68px !important; height: 68px !important; }
    .rep-sub { font-size: .84rem !important; }
    .rep-office { font-size: 1rem !important; font-weight: bold !important; }
    .rep-city { font-size: .82rem !important; }
    .rep-dept { font-size: .78rem !important; font-weight: bold !important; }
    .rep-main-title { font-size: 1.1rem !important; font-weight: 900 !important; margin: 5px 0 3px !important; }
    .rep-period { font-size: .82rem !important; }

    .report-meta-bar {
        background: #f8fafc !important; border: 1px solid #000 !important;
        padding: 5px 12px !important; font-size: .8rem !important;
        margin-bottom: 10px !important;
        page-break-inside: avoid !important; break-inside: avoid !important;
    }

    .vpr-idblock {
        border: 1px solid #000 !important; padding: 7px 10px !important;
        margin-bottom: 10px !important;
        page-break-inside: avoid !important; break-inside: avoid !important;
    }
    .vpr-id-fullname { font-size: .95rem !important; }
    .vpr-id-sub { font-size: .76rem !important; }

    .vsum-kpi-grid {
        display: grid !important; grid-template-columns: repeat(4, 1fr) !important;
        gap: 6px !important; margin-bottom: 8px !important;
        page-break-inside: avoid !important; break-inside: avoid !important;
    }
    .vsum-kpi {
        background: #fff !important; border: 1px solid #000 !important;
        border-left: 3px solid #000 !important; padding: 5px 8px !important;
    }
    .vsum-kpi-label { font-size: .64rem !important; font-weight: 800 !important; }
    .vsum-kpi-value { font-size: 1rem !important; margin: 1px 0 !important; }
    .vsum-kpi-sub { font-size: .62rem !important; font-weight: 600 !important; }

    .vsum-recon {
        background: #fff !important; border: 1px solid #000 !important;
        font-size: .7rem !important; padding: 5px 9px !important;
        margin-bottom: 10px !important;
        page-break-inside: avoid !important; break-inside: avoid !important;
    }

    .report-section { margin-bottom: 10px !important; }
    .report-sec-title {
        font-size: .9rem !important; font-weight: bold !important;
        border-bottom: 1.5px solid #000 !important;
        padding-bottom: 3px !important; margin-bottom: 5px !important;
        page-break-after: avoid !important; break-after: avoid-page !important;
        page-break-inside: avoid !important; break-inside: avoid !important;
    }
    .vsum-sec-note { font-size: .7rem !important; margin-bottom: 5px !important; }

    .table-responsive { overflow: visible !important; }
    .report-table {
        table-layout: fixed !important;
        width: 100% !important; max-width: 100% !important;
        border: 1.5px solid #000 !important;
        font-size: .76rem !important;
        margin-bottom: 6px !important;
    }
    .report-table th, .report-table td {
        overflow-wrap: break-word !important;
        padding: 4px 5px !important;
        border: 1px solid #000 !important;
        font-size: .72rem !important;
        line-height: 1.25 !important;
    }
    .report-table thead { display: table-header-group !important; }
    .report-table tr { page-break-inside: avoid !important; break-inside: avoid !important; }
    .report-table th {
        background: #e2e8f0 !important; font-size: .64rem !important;
        font-weight: 800 !important;
        -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
    }
    .report-total-row td {
        background: #f1f5f9 !important; font-weight: 800 !important;
        -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important;
    }
    .vpr-mono { font-size: .64rem !important; letter-spacing: -.01em !important; }

    /* The tally grid has to land on A4 as one whole month: fixed layout and
       hairline borders keep all 31 day columns plus TOTAL on the sheet. */
    .vsum-matrix-scroll { overflow: visible !important; }
    .vpr-matrix {
        table-layout: fixed !important;
        width: 100% !important; max-width: 100% !important;
        margin-bottom: 4px !important;
    }
    .vpr-matrix th, .vpr-matrix td {
        font-size: .54rem !important;
        padding: 1.5px 0 !important;
        border: 1px solid #000 !important;
        line-height: 1.2 !important;
    }
    .vpr-matrix thead { display: table-header-group !important; }
    .vpr-matrix .vpr-mx-name { width: 10% !important; font-size: .58rem !important; }
    .vpr-matrix .vpr-mx-total { width: 6% !important; font-size: .58rem !important; }
    .vpr-matrix .vpr-mx-day { width: auto !important; min-width: 0 !important; }
    .vpr-matrix .vpr-mx-hit { font-weight: 800 !important; }
    .vpr-matrix tr.vpr-mx-idle td { color: #000 !important; }
    .vpr-matrix tfoot td { font-size: .54rem !important; font-weight: 800 !important; }
    .vpr-time { font-size: .64rem !important; }
    .vpr-pill-note { font-size: .6rem !important; }

    /* Column shares keep every table inside the printable width. */
    .vpr-daily-table th:nth-child(1), .vpr-daily-table td:nth-child(1) { width: 4% !important; }
    .vpr-daily-table th:nth-child(2), .vpr-daily-table td:nth-child(2) { width: 11% !important; }
    .vpr-daily-table th:nth-child(3), .vpr-daily-table td:nth-child(3) { width: 9% !important; }
    .vpr-daily-table th:nth-child(4), .vpr-daily-table td:nth-child(4) { width: 12% !important; }
    .vpr-daily-table th:nth-child(5), .vpr-daily-table td:nth-child(5) { width: 20% !important; }
    .vpr-daily-table th:nth-child(6), .vpr-daily-table td:nth-child(6) { width: 15% !important; }
    .vpr-daily-table th:nth-child(7), .vpr-daily-table td:nth-child(7) { width: 13% !important; }
    .vpr-daily-table th:nth-child(8), .vpr-daily-table td:nth-child(8) { width: 8% !important; }
    .vpr-daily-table th:nth-child(9), .vpr-daily-table td:nth-child(9) { width: 8% !important; }

    .vpr-record-table th:nth-child(1), .vpr-record-table td:nth-child(1) { width: 4% !important; }
    .vpr-record-table th:nth-child(2), .vpr-record-table td:nth-child(2) { width: 13% !important; }
    .vpr-record-table th:nth-child(3), .vpr-record-table td:nth-child(3) { width: 13% !important; }
    .vpr-record-table th:nth-child(4), .vpr-record-table td:nth-child(4) { width: 22% !important; }
    .vpr-record-table th:nth-child(5), .vpr-record-table td:nth-child(5) { width: 13% !important; }
    .vpr-record-table th:nth-child(6), .vpr-record-table td:nth-child(6) { width: 12% !important; }
    .vpr-record-table th:nth-child(7), .vpr-record-table td:nth-child(7) { width: 8% !important; }
    .vpr-record-table th:nth-child(8), .vpr-record-table td:nth-child(8) { width: 15% !important; }

    .vpr-month-table th:nth-child(1), .vpr-month-table td:nth-child(1) { width: 12% !important; }
    .vpr-month-table th:nth-child(2), .vpr-month-table td:nth-child(2) { width: 10% !important; }
    .vpr-month-table th:nth-child(3), .vpr-month-table td:nth-child(3) { width: 9% !important; }
    .vpr-month-table th:nth-child(4), .vpr-month-table td:nth-child(4) { width: 33% !important; }
    .vpr-month-table th:nth-child(5), .vpr-month-table td:nth-child(5) { width: 16% !important; }
    .vpr-month-table th:nth-child(6), .vpr-month-table td:nth-child(6) { width: 10% !important; }
    .vpr-month-table th:nth-child(7), .vpr-month-table td:nth-child(7) { width: 10% !important; }

    .vpr-sanction-table th:nth-child(1), .vpr-sanction-table td:nth-child(1) { width: 16% !important; }
    .vpr-sanction-table th:nth-child(2), .vpr-sanction-table td:nth-child(2) { width: 10% !important; }
    .vpr-sanction-table th:nth-child(3), .vpr-sanction-table td:nth-child(3) { width: 16% !important; }
    .vpr-sanction-table th:nth-child(4), .vpr-sanction-table td:nth-child(4) { width: 58% !important; }

    .report-sign-block {
        margin-top: 14px !important;
        page-break-inside: avoid !important; break-inside: avoid !important;
    }
    .report-sign-block .row { display: flex !important; flex-wrap: nowrap !important; }
    .report-sign-block .sig-line { width: 220px !important; margin-top: 24px !important; border-bottom: 1.5px solid #000 !important; }
    .report-sign-block .text-muted { color: #000 !important; font-size: .72rem !important; }
}
</style>
