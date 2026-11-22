    /* TCT Modal Document Styling */
    .tct-modal-card {
        background: #fff;
        border: 2px solid #000;
        padding: 20px 24px;
        font-family: 'Times New Roman', Times, serif, Arial, sans-serif;
        color: #000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        /* Always centred inside the modal with even side margins. */
        margin-left: auto;
        margin-right: auto;
        max-width: 100%;
    }
    .tct-modal-header {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 18px;
        min-height: 80px;
    }
    .tct-modal-seal {
        position: absolute;
        left: 0;
        top: 0;
        width: 75px;
        height: 75px;
    }
    .tct-seal-img {
        width: 75px !important;
        height: 75px !important;
        max-width: 75px !important;
        max-height: 75px !important;
        object-fit: contain;
        display: block;
    }
    .tct-header-text {
        text-align: center;
        line-height: 1.25;
    }
    .tct-rep {
        font-size: 1rem;
    }
    .tct-office {
        font-size: 1.15rem;
        font-weight: bold;
    }
    .tct-city {
        font-size: 1rem;
    }
    .tct-doc-title {
        font-size: 1.35rem;
        font-weight: 900;
        margin-top: 8px;
        margin-bottom: 2px;
        letter-spacing: 0.02em;
        font-family: Arial, sans-serif;
    }
    .tct-ord-no {
        font-size: 1.05rem;
        font-weight: bold;
    }
    .tct-doc-section {
        border-bottom: 1.5px solid #000;
        padding-bottom: 14px;
        margin-bottom: 14px;
    }
    .tct-bold-label {
        font-weight: bold;
        font-size: 0.95rem;
        white-space: nowrap;
        font-family: Arial, sans-serif;
    }
    .tct-sub-label {
        font-size: 0.82rem;
        font-style: italic;
        color: #333;
    }
    .tct-sec-heading {
        font-size: 1.1rem;
        font-weight: bold;
        letter-spacing: 0.05em;
        font-family: Arial, sans-serif;
    }
    /* PC layout only: "VIOLATIONS" sits dead-centre in the card while the
       fine badge and the Add Violation button keep their place at the right
       edge. (The narrow-screen media queries below stack them centred.) */
    @media screen and (min-width: 992px) {
        .tct-violations-head {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            gap: 0.5rem 0.85rem;
        }
        .tct-violations-head .tct-sec-heading {
            grid-column: 2;
            flex: none !important;
        }
        .tct-violations-head > .d-flex {
            grid-column: 3;
            justify-self: end;
            justify-content: flex-end !important;
        }
    }
    .tct-sec-notice {
        font-size: 0.88rem;
        font-style: italic;
    }
    .tct-doc-section .btn-xs {
        padding: 0.25rem 0.55rem;
        font-size: 0.72rem;
        font-weight: 600;
        border-radius: 6px;
        white-space: nowrap;
    }
    .infraction-entry {
        padding: 0.35rem 0.45rem;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 8px;
    }
    .infraction-entry .infraction-select {
        border-radius: 6px;
        background-color: #fff;
        min-width: 0;
    }
    .infraction-entry .infraction-remove {
        flex: 0 0 auto;
        border-radius: 6px;
        line-height: 1;
    }
    .infraction-entry .infraction-remove:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }
    @media (max-width: 575.98px) {
        .infraction-entry {
            align-items: stretch !important;
        }
        .infraction-entry .infraction-select {
            font-size: 0.78rem;
        }
    }
    .tct-legal-text {
        font-size: 0.86rem;
        font-style: italic;
        text-align: justify;
        line-height: 1.35;
        border-top: 1px dashed #444;
        padding-top: 8px;
        margin-top: 8px;
    }
    .tct-ack-heading {
        font-size: 0.92rem;
        font-weight: bold;
        font-family: Arial, sans-serif;
    }
    .tct-driver-copy-tag {
        font-size: 0.82rem;
        font-weight: bold;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .tct-row {
        display: flex;
        width: 100%;
    }

    /* ── TCT form field grid (entry forms only, screen only) ─────────────
       The data-entry rows of the new/edit violation form. A grid keeps every
       field's underline on the same baseline and gives the label its own
       column, so a long label ("DL/Permit No.") can no longer push its input
       sideways or run into the label of the next field.

       This stylesheet is shared with pages that also print a ticket, so the
       layout rules below are scoped twice: to the two entry modals (the only
       place the markup exists) and to the screen media type. A narrow window
       therefore restacks the on-screen form only - a printed ticket always
       keeps the wide paper layout, with its seal overlapped as designed. */
    @media screen {
        :where(#recordViolationModal, #editViolationModal) .tct-form-grid {
            display: grid;
            align-items: end;
            gap: 8px 18px;
            width: 100%;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-form-grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        :where(#recordViolationModal, #editViolationModal) .tct-form-grid-2 {
            grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr);
        }
        :where(#recordViolationModal, #editViolationModal) .tct-form-cell {
            display: grid;
            grid-template-columns: auto minmax(0, 1fr);
            align-items: end;
            gap: 6px;
            min-width: 0;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-form-cell > .tct-bold-label {
            line-height: 1.15;
            padding-bottom: 4px;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-form-cell .tct-input,
        :where(#recordViolationModal, #editViolationModal) .tct-form-cell .combo-wrap {
            width: 100%;
            min-width: 0;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-form-cell .tct-input[type="datetime-local"] {
            padding-right: 2px;
        }
    }
    /* A three-up row needs roughly 900px of paper. Below that — every phone
       and tablet — the fields stack one per line at full width, so a label
       never squeezes its underline into a stub or clips its placeholder. */
    @media screen and (max-width: 991.98px) {
        :where(#recordViolationModal, #editViolationModal) .tct-form-grid-3,
        :where(#recordViolationModal, #editViolationModal) .tct-form-grid-2 {
            grid-template-columns: minmax(0, 1fr);
            gap: 10px;
        }
        /* Once the paper is narrow, put the label above its underline so the
           input keeps the full column width instead of a 12px stub. */
        :where(#recordViolationModal, #editViolationModal) .tct-form-cell {
            grid-template-columns: minmax(0, 1fr);
            gap: 2px;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-form-cell > .tct-bold-label {
            padding-bottom: 0;
        }
        /* The seal is absolutely positioned on wide paper; once the paper is
           narrower the centred header text ran underneath it. Stack instead. */
        :where(#recordViolationModal, #editViolationModal) .tct-modal-header {
            flex-direction: column;
            gap: 8px;
            min-height: 0;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-modal-seal {
            position: static;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-modal-header .tct-office {
            font-size: 1rem;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-modal-header .tct-doc-title {
            font-size: 1.15rem;
        }
        /* Violations header, centred: the heading takes its own line, then
           the fine badge and the Add Violation button sit centred beneath
           it instead of being pushed into the screen edge. */
        .tct-violations-head {
            flex-direction: column;
            align-items: center !important;
            justify-content: center !important;
            text-align: center;
            gap: 0.5rem !important;
        }
        .tct-violations-head .tct-sec-heading {
            flex: none !important;
            width: 100%;
        }
        .tct-violations-head > .d-flex {
            justify-content: center !important;
        }
    }
    /* A touch more usable width inside the paper card on phones. */
    @media screen and (max-width: 767.98px) {
        #recordViolationModal .modal-body,
        #editViolationModal .modal-body {
            padding-left: 0.65rem !important;
            padding-right: 0.65rem !important;
        }
    }

    .flex-1 {
        flex: 1;
    }
    .tct-checklist-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 5px 12px;
    }
    .tct-check-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin-bottom: 2px;
        user-select: none;
    }

    /* ── Offenses this driver was cited for before ──────────────────────
       A screen-only memory aid: the shaded rows are the ones already on the
       person's record, so the officer sees the pattern while filling a new
       ticket. Nothing here reaches paper - the print block below clears the
       shade, and the legend is hidden there too. */
    @media screen {
        #recordViolationModal .tct-check-label.is-previous {
            background: #fef9c3;
            border-radius: 6px;
            box-shadow: inset 3px 0 0 #eab308;
            padding-left: 6px;
            padding-right: 6px;
        }
        #recordViolationModal .tct-check-label.is-previous .tct-item-text {
            font-weight: 700;
        }
        #recordViolationModal .tct-check-label.is-previous:hover {
            background: #fef08a;
        }
        .tct-prev-legend {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-family: Arial, sans-serif;
            font-size: 0.74rem;
            font-weight: 600;
            color: #92400e;
            background: #fef9c3;
            border: 1px solid #facc15;
            border-radius: 999px;
            padding: 3px 10px;
            margin-bottom: 8px;
        }
    }
    @media print {
        .tct-check-label.is-previous {
            background: transparent !important;
            box-shadow: none !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
        .tct-check-label.is-previous .tct-item-text { font-weight: inherit !important; }
        .tct-prev-legend { display: none !important; }
    }
    .tct-box {
        width: 18px;
        height: 18px;
        border: 1.5px solid #000;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 0.85rem;
        font-family: monospace;
        line-height: 1;
        margin-right: 10px;
        flex-shrink: 0;
        background: #fff;
        color: #000;
    }
    .tct-box.is-checked {
        background: #000;
        color: #fff;
    }
    .tct-box-x {
        display: inline-block;
        line-height: 1;
    }
    .tct-item-text {
        font-family: Arial, sans-serif;
        font-size: 0.92rem;
    }
    .tct-sig-underline {
        width: 260px;
        border-bottom: 1.5px solid #000;
        height: 35px;
    }
    .tct-officer-box {
        border: 1.5px solid #000;
        height: 60px;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fafafa;
    }
    .officer-printed-name {
        font-weight: bold;
        font-size: 1.05rem;
        text-transform: uppercase;
        font-family: Arial, sans-serif;
        letter-spacing: 0.05em;
    }
    .tct-number-tag {
        font-weight: bold;
        font-size: 1.15rem;
        font-family: Arial, sans-serif;
    }
    .tct-year-prefix {
        font-weight: 700;
        font-size: 1.05rem;
        font-family: Arial, sans-serif;
        color: #000;
    }
    .tct-red-no {
        color: #dc3545;
        font-family: 'Courier New', monospace;
        font-size: 1.25rem;
        font-weight: 900;
        letter-spacing: 0.05em;
    }
    .tct-input {
        border: none;
        border-bottom: 1.5px solid #000;
        border-radius: 0;
        padding: 2px 6px;
        background: transparent;
        font-family: Arial, sans-serif;
        font-weight: bold;
        font-size: 0.95rem;
    }
    .tct-input::placeholder {
        color: var(--text-subtle, #5c6b80) !important;
        font-weight: 400 !important;
        font-style: italic !important;
        opacity: 0.65 !important;
    }
    .tct-input:focus {
        background: #f5f3ff;
        border-bottom-color: #0d6efd;
        box-shadow: none;
    }

    /* ── MOBILE: stack TCT form rows so nothing runs off-screen ──
       Screen only: these rows must never be restacked on a printed ticket. */
    @media screen and (max-width: 767px) {
        .tct-modal-card {
            padding: 14px 10px;
        }
        .tct-row,
        .tct-row > * {
            flex-direction: column !important;
            align-items: stretch !important;
            gap: 0.35rem;
        }
        .tct-row > *,
        .tct-row > * > * {
            flex: none !important;
            width: 100% !important;
            min-width: 0 !important;
            margin-right: 0 !important;
        }
        .tct-modal-card .tct-sig-wrapper {
            width: 100% !important;
            max-width: 280px;
        }
        .tct-driver-signature-area .d-flex {
            flex-wrap: wrap;
        }
        .tct-officer-signature-area > .d-flex {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 0.35rem;
        }
        .tct-officer-signature-area > .d-flex > .d-flex {
            align-self: flex-end;
            justify-content: flex-end;
        }
        .tct-doc-section .d-flex.gap-5 {
            gap: 1rem !important;
        }
        .tct-footer-area {
            flex-wrap: wrap;
            gap: 0.35rem 0.75rem;
        }
        .tct-modal-card .d-flex.justify-content-end.gap-2.border-top {
            flex-direction: column !important;
            gap: 0.5rem !important;
        }
        .tct-modal-card .d-flex.justify-content-end.gap-2.border-top .btn {
            width: 100%;
        }

        /* Checkbox list: roomier tap targets, tighter rows, tidier text. */
        :where(#recordViolationModal, #editViolationModal) .tct-checklist-container {
            gap: 2px;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-check-label {
            align-items: flex-start;
            padding: 7px 6px;
            border-radius: 8px;
            margin-bottom: 0;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-check-label:active {
            background: #eef2f7;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-box {
            margin-top: 1px;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-item-text {
            line-height: 1.32;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-input[type="datetime-local"] {
            width: 100%;
            min-width: 0;
        }

        /* ── MOBILE: streamline the ticket header ──────────────────
           On a phone the seal and five-line letterhead used to occupy a
           quarter of the screen before the first field appeared. Keep the
           TCT format intact, just tighten the type and spacing so the
           officer reaches the name fields sooner. */
        :where(#recordViolationModal, #editViolationModal) .tct-modal-header {
            gap: 0.35rem;
            margin-bottom: 0.75rem;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-modal-seal,
        :where(#recordViolationModal, #editViolationModal) .tct-seal-img {
            width: 54px !important;
            height: 54px !important;
            max-width: 54px !important;
            max-height: 54px !important;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-rep,
        :where(#recordViolationModal, #editViolationModal) .tct-city { font-size: 0.82rem; }
        :where(#recordViolationModal, #editViolationModal) .tct-office { font-size: 0.9rem; }
        :where(#recordViolationModal, #editViolationModal) .tct-doc-title {
            font-size: 1rem;
            margin-top: 0.25rem;
        }
        :where(#recordViolationModal, #editViolationModal) .tct-ord-no { font-size: 0.85rem; }
        :where(#recordViolationModal, #editViolationModal) .tct-doc-section {
            padding-bottom: 0.7rem;
            margin-bottom: 0.7rem;
        }
        /* First / Middle on one line, Last / Suffix on the next: four
           full-width name lines used to push everything else below the fold. */
        :where(#recordViolationModal, #editViolationModal) .tct-row > div.d-flex.flex-1 {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.35rem 0.5rem;
        }

        /* ── MOBILE: one action row, clear of the fields ───────────
           The sticky bar stacked Cancel above a full-width Save button and
           sat on top of the last field. Put both on one row (Cancel small,
           Save wide), keep the 44px tap target, and reserve space after the
           bar so every field can scroll into view above it. */
        .tct-modal-card .d-flex.justify-content-end.gap-2.border-top {
            flex-direction: row !important;
            flex-wrap: nowrap;
            gap: 0.5rem !important;
            padding-top: 0.5rem;
        }
        .tct-modal-card .d-flex.justify-content-end.gap-2.border-top .btn {
            width: auto !important;
            min-height: 44px;
        }
        .tct-modal-card .d-flex.justify-content-end.gap-2.border-top .btn-outline-secondary {
            flex: 0 0 auto;
        }
        .tct-modal-card .d-flex.justify-content-end.gap-2.border-top .btn-primary {
            flex: 1 1 auto;
        }
        #recordViolationModal .tct-modal-card { padding-bottom: 4.5rem; }
    }

    /* ── Combo pickers (Vehicle / Place) ──────────────────────────────
       A tap-friendly suggestion list for fields that used to rely on a
       native <datalist>, which phones cannot open. Typing still filters
       the list and any custom value can be entered as before. */
    .combo-wrap {
        position: relative;
        min-width: 0;
    }
    .combo-wrap .form-control {
        width: 100%;
        padding-right: 2.1rem !important;
    }
    .combo-toggle {
        position: absolute;
        right: 2px;
        top: 2px;
        bottom: 2px;
        width: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 0;
        background: transparent;
        color: #475569;
        cursor: pointer;
        border-radius: 6px;
        font-size: 0.85rem;
    }
    .combo-toggle:hover {
        background: #f5f3ff;
        color: #6d28d9;
    }
    .combo-toggle i {
        pointer-events: none;
    }
    .combo-list {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(100% + 4px);
        z-index: 1080;
        background: #fff;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.18);
        max-height: 15rem;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
        padding: 0.25rem;
        text-align: left;
    }
    .combo-list[hidden] {
        display: none;
    }
    .combo-option {
        display: block;
        width: 100%;
        border: 0;
        background: transparent;
        border-radius: 7px;
        padding: 0.6rem 0.7rem;
        text-align: left;
        font-size: 0.92rem;
        color: #1e293b;
        cursor: pointer;
        line-height: 1.3;
    }
    .combo-option:hover,
    .combo-option:focus,
    .combo-option:active {
        background: #f5f3ff;
        color: #6d28d9;
    }
    .combo-empty {
        padding: 0.6rem 0.7rem;
        font-size: 0.85rem;
        color: #64748b;
        font-style: italic;
    }
