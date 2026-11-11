    /* TCT Modal Document Styling */
    .tct-modal-card {
        background: #fff;
        border: 2px solid #000;
        padding: 20px 24px;
        font-family: 'Times New Roman', Times, serif, Arial, sans-serif;
        color: #000;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
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
        background: #f0f7ff;
        border-bottom-color: #0d6efd;
        box-shadow: none;
    }

    /* ── MOBILE: stack TCT form rows so nothing runs off-screen ── */
    @media (max-width: 767px) {
        .tct-modal-card {
            padding: 14px 12px;
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
        background: #eff6ff;
        color: #0052cc;
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
        background: #eff6ff;
        color: #0052cc;
    }
    .combo-empty {
        padding: 0.6rem 0.7rem;
        font-size: 0.85rem;
        color: #64748b;
        font-style: italic;
    }
