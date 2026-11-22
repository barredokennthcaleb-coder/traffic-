<?php
/**
 * Behavior for the shared TCT "Record New Violation" form. Included inside a
 * <script> tag by both the enforcer ledger and the admin violators directory.
 */
$tctNumberEndpoint = (string) ($tctNumberEndpoint ?? base_url('officer/next-tct-number'));
$hasPrefillViolator = ! empty($prefillViolator ?? null);
$reopenFormOnError  = (bool) (session()->getFlashdata('error') || session()->getFlashdata('errors'));
?>
(function () {
    'use strict';

    // ═══ NEW VIOLATION INFRACTIONS (CHECKLIST + ADDED ENTRIES) ═══
    const newInfractionRows = document.getElementById('newInfractionRows');
    const newInfractionRowTemplate = document.getElementById('newInfractionRowTemplate');
    const addNewInfractionBtn = document.getElementById('addNewInfractionBtn');

    function showInfractionWarning(message) {
        if (window.AppToast) {
            AppToast.fire({ icon: 'warning', title: message });
        } else {
            alert(message);
        }
    }

    function getNewInfractionRows() {
        return Array.from(newInfractionRows?.querySelectorAll('.infraction-entry') || []);
    }

    function getSelectedNewInfractionIds() {
        const ids = Array.from(document.querySelectorAll('#violationChecklist .violation-check:checked')).map(check => check.value);
        getNewInfractionRows().forEach(row => {
            const select = row.querySelector('.infraction-select');
            if (select?.value) ids.push(select.value);
        });
        return ids;
    }

    function setCheckboxVisual(check) {
        const box = check.closest('label')?.querySelector('.tct-box');
        if (!box) return;
        if (check.checked) {
            box.classList.add('is-checked');
            box.innerHTML = '<span class="tct-box-x">X</span>';
        } else {
            box.classList.remove('is-checked');
            box.innerHTML = '';
        }
    }

    function syncNewInfractionOptions() {
        const rows = getNewInfractionRows();
        const checkedValues = Array.from(document.querySelectorAll('#violationChecklist .violation-check:checked')).map(check => check.value);

        rows.forEach(row => {
            const select = row.querySelector('.infraction-select');
            if (!select) return;
            const ownValue = select.value;
            Array.from(select.options).forEach(option => {
                if (!option.value) return;
                if (option.value === ownValue) {
                    option.disabled = false;
                    return;
                }
                const usedByOtherRow = rows.some(otherRow => {
                    if (otherRow === row) return false;
                    return otherRow.querySelector('.infraction-select')?.value === option.value;
                });
                option.disabled = usedByOtherRow || checkedValues.includes(option.value);
            });
        });
    }

    function clearMatchingCheckbox(value) {
        document.querySelectorAll('#violationChecklist .violation-check:checked').forEach(check => {
            if (check.value !== value) return;
            check.checked = false;
            setCheckboxVisual(check);
        });
    }

    function clearMatchingInfractionRows(value, exceptRow = null) {
        getNewInfractionRows().forEach(row => {
            if (row === exceptRow) return;
            const select = row.querySelector('.infraction-select');
            if (select && select.value === value) {
                select.value = '';
            }
        });
    }

    function updateFineAmount() {
        let totalAmount = 0;
        document.querySelectorAll('#violationChecklist .violation-check:checked').forEach(check => {
            totalAmount += parseFloat(check.dataset.amount || 0);
        });
        getNewInfractionRows().forEach(row => {
            const select = row.querySelector('.infraction-select');
            const selectedOption = select?.options[select.selectedIndex];
            if (select?.value && selectedOption) {
                totalAmount += parseFloat(selectedOption.dataset.amount || 0);
            }
        });

        const penaltyInput = document.getElementById('penalty_amount');
        if (penaltyInput) penaltyInput.value = totalAmount.toFixed(2);

        const disp = document.getElementById('penalty_amount_display');
        if (disp) disp.textContent = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function addNewInfractionRow(selectedId = '') {
        if (!newInfractionRows || !newInfractionRowTemplate) return null;
        const row = newInfractionRowTemplate.content.firstElementChild.cloneNode(true);
        const select = row.querySelector('.infraction-select');
        const removeBtn = row.querySelector('.infraction-remove');

        if (select) {
            select.required = true;
            if (selectedId) {
                select.value = String(selectedId);
            }
            select.addEventListener('change', function () {
                if (this.value) {
                    clearMatchingCheckbox(this.value);
                    clearMatchingInfractionRows(this.value, row);
                }
                syncNewInfractionOptions();
                updateFineAmount();
            });
        }

        removeBtn?.addEventListener('click', function () {
            row.remove();
            syncNewInfractionOptions();
            updateFineAmount();
        });

        newInfractionRows.appendChild(row);
        syncNewInfractionOptions();
        updateFineAmount();
        return row;
    }

    function handleViolationCheckChange() {
        if (this.checked) {
            clearMatchingInfractionRows(this.value);
        }
        setCheckboxVisual(this);
        syncNewInfractionOptions();
        updateFineAmount();
    }

    // Checkbox violations logic & authentic [X] toggle
    document.querySelectorAll('#violationChecklist .violation-check').forEach(check => {
        check.addEventListener('change', handleViolationCheckChange);
    });

    addNewInfractionBtn?.addEventListener('click', function () {
        const row = addNewInfractionRow();
        row?.querySelector('.infraction-select')?.focus();
    });

    updateFineAmount();

    // Acknowledgment [X] toggle for Admitted / Under Protest
    document.querySelectorAll('input[name="acknowledgment"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.tct-ack-box').forEach(b => {
                b.classList.remove('is-checked');
                b.innerHTML = '';
            });
            const curBox = this.closest('label')?.querySelector('.tct-ack-box');
            if (curBox && this.checked) {
                curBox.classList.add('is-checked');
                curBox.innerHTML = '<span class="tct-box-x">X</span>';
            }
        });
    });

    // The first / middle / last fields are entered directly now (each under its
    // own label), so nothing needs to be split out of a combined name input.

    // Signature capture: the same draw/upload e-signature dialog used by the
    // report sign-off, wired to the driver and apprehending-officer fields.
    const tctSigState = { role: 'driver', drawing: false, hasDrawn: false, uploaded: '' };
    let tctSigCanvas = null;
    let tctSigCtx = null;

    function tctSigParts(role) {
        const cap = role.charAt(0).toUpperCase() + role.slice(1);
        return {
            hidden: document.getElementById(role + '_signature'),
            preview: document.getElementById(role + 'SigPreview'),
            label: document.getElementById(role + 'SigBtnLabel'),
            clearBtn: document.getElementById('clear' + cap + 'SigBtn'),
            storageKey: 'tct_sig_' + role
        };
    }

    function setTctSignature(role, dataUrl) {
        const parts = tctSigParts(role);
        const signed = !!dataUrl;
        if (parts.preview) {
            parts.preview.src = signed ? dataUrl : '';
            parts.preview.style.display = signed ? 'block' : 'none';
        }
        if (parts.hidden) parts.hidden.value = signed ? dataUrl : '';
        if (parts.label) parts.label.innerText = signed ? 'Change Signature' : 'Sign Here';
        if (parts.clearBtn) parts.clearBtn.style.display = signed ? 'inline-block' : 'none';
    }

    function applyTctSignature(role, dataUrl) {
        setTctSignature(role, dataUrl);
        if (role === 'officer') {
            // The officer's own signature is reused across tickets; the driver's
            // is never stored because it belongs to whoever is being cited.
            localStorage.setItem('tct_sig_officer', dataUrl);
        }
    }

    function clearTctSignature(role) {
        setTctSignature(role, '');
        localStorage.removeItem('tct_sig_' + role);
    }

    function restoreSavedOfficerSignature() {
        const saved = localStorage.getItem('tct_sig_officer');
        if (saved) setTctSignature('officer', saved);
    }

    function initTctSigCanvas() {
        tctSigCanvas = document.getElementById('tctSigCanvas');
        if (!tctSigCanvas) return;
        tctSigCtx = tctSigCanvas.getContext('2d');

        function getPos(e) {
            const rect = tctSigCanvas.getBoundingClientRect();
            let clientX = e.clientX;
            let clientY = e.clientY;
            if (e.touches && e.touches.length > 0) {
                clientX = e.touches[0].clientX;
                clientY = e.touches[0].clientY;
            }
            return {
                x: (clientX - rect.left) * (tctSigCanvas.width / rect.width),
                y: (clientY - rect.top) * (tctSigCanvas.height / rect.height)
            };
        }

        function startDrawing(e) {
            e.preventDefault();
            tctSigState.drawing = true;
            tctSigState.hasDrawn = true;
            tctSigCtx.lineWidth = 2.5;
            tctSigCtx.lineCap = 'round';
            tctSigCtx.lineJoin = 'round';
            tctSigCtx.strokeStyle = '#0f172a';
            const pos = getPos(e);
            tctSigCtx.beginPath();
            tctSigCtx.moveTo(pos.x, pos.y);
        }

        function draw(e) {
            if (!tctSigState.drawing) return;
            e.preventDefault();
            const pos = getPos(e);
            tctSigCtx.lineTo(pos.x, pos.y);
            tctSigCtx.stroke();
        }

        function stopDrawing() {
            tctSigState.drawing = false;
        }

        tctSigCanvas.addEventListener('mousedown', startDrawing);
        tctSigCanvas.addEventListener('mousemove', draw);
        tctSigCanvas.addEventListener('mouseup', stopDrawing);
        tctSigCanvas.addEventListener('mouseleave', stopDrawing);

        tctSigCanvas.addEventListener('touchstart', startDrawing, { passive: false });
        tctSigCanvas.addEventListener('touchmove', draw, { passive: false });
        tctSigCanvas.addEventListener('touchend', stopDrawing);

        document.getElementById('tctSigClearPad')?.addEventListener('click', function() {
            tctSigCtx.clearRect(0, 0, tctSigCanvas.width, tctSigCanvas.height);
            tctSigState.hasDrawn = false;
        });

        document.getElementById('tctSigFileInput')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function(evt) {
                tctSigState.uploaded = evt.target.result;
                document.getElementById('tctSigUploadPreviewImg').src = tctSigState.uploaded;
                document.getElementById('tctSigUploadPreviewBox').style.display = 'block';
            };
            reader.readAsDataURL(file);
        });

        document.getElementById('tctSigApplyBtn')?.addEventListener('click', function() {
            const activeTab = document.querySelector('#tctSigTab .nav-link.active')?.id;
            let sigData = '';

            if (activeTab === 'tctUploadTab') {
                if (!tctSigState.uploaded) {
                    Swal.fire({ icon: 'warning', title: 'No File Selected', text: 'Please select an image file first.' });
                    return;
                }
                sigData = tctSigState.uploaded;
            } else {
                if (!tctSigState.hasDrawn) {
                    Swal.fire({ icon: 'warning', title: 'Empty Signature Pad', text: 'Please draw your signature before applying.' });
                    return;
                }
                sigData = tctSigCanvas.toDataURL('image/png');
            }

            applyTctSignature(tctSigState.role, sigData);
            bootstrap.Modal.getInstance(document.getElementById('tctSignatureModal'))?.hide();
        });
    }

    function openTctSignatureModal(role) {
        tctSigState.role = role;
        const titleEl = document.getElementById('tctSignatureModalLabel');
        if (titleEl) {
            const title = role === 'driver' ? 'Driver Signature' : 'Apprehending Officer Signature';
            titleEl.innerHTML = `<i class="bi bi-pen-fill text-danger me-2"></i>Sign: ${title}`;
        }

        if (!tctSigCanvas) initTctSigCanvas();
        if (tctSigCtx) tctSigCtx.clearRect(0, 0, tctSigCanvas.width, tctSigCanvas.height);
        tctSigState.hasDrawn = false;
        tctSigState.uploaded = '';
        const fileInput = document.getElementById('tctSigFileInput');
        if (fileInput) fileInput.value = '';
        const previewBox = document.getElementById('tctSigUploadPreviewBox');
        if (previewBox) previewBox.style.display = 'none';

        // Always land on the draw tab, exactly like the report sign-off.
        const drawTab = document.getElementById('tctDrawTab');
        if (drawTab) new bootstrap.Tab(drawTab).show();

        new bootstrap.Modal(document.getElementById('tctSignatureModal')).show();
    }

    document.querySelectorAll('[data-open-signature]').forEach(btn => {
        btn.addEventListener('click', () => openTctSignatureModal(btn.dataset.openSignature));
    });
    document.getElementById('clearDriverSigBtn')?.addEventListener('click', () => clearTctSignature('driver'));
    document.getElementById('clearOfficerSigBtn')?.addEventListener('click', () => clearTctSignature('officer'));
    restoreSavedOfficerSignature();

    function syncTctNumberFields() {
        const yearPrefix = document.getElementById('tctYearPrefix');
        const serialInput = document.getElementById('tctSerialInput');
        const hiddenInput = document.getElementById('custom_ticket_no');
        if (!yearPrefix || !serialInput || !hiddenInput) return;

        const year = (yearPrefix.textContent || '').replace(/[^\d]/g, '').slice(0, 4) || String(new Date().getFullYear());
        const serial = String(serialInput.value || '').replace(/\D/g, '').slice(0, 7);
        serialInput.value = serial;
        hiddenInput.value = serial ? `${year}-${serial.padStart(7, '0')}` : '';
    }

    async function refreshNextTctNumber() {
        const yearPrefix = document.getElementById('tctYearPrefix');
        const serialInput = document.getElementById('tctSerialInput');
        const hiddenInput = document.getElementById('custom_ticket_no');
        if (!yearPrefix || !serialInput || !hiddenInput || hiddenInput.dataset.manual === '1' || hiddenInput.dataset.fromOld === '1') {
            return;
        }

        try {
            const res = await fetch(<?= json_encode($tctNumberEndpoint) ?>, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            if (!data.success || !data.number) return;

            const parts = String(data.number).split('-');
            if (parts.length === 2) {
                yearPrefix.textContent = `${parts[0]}-`;
                serialInput.value = parts[1].padStart(7, '0');
                hiddenInput.value = data.number;
            }
        } catch (err) {
            // Keep the server-rendered preview if refresh fails.
        }
    }

    document.getElementById('tctSerialInput')?.addEventListener('input', function () {
        const hiddenInput = document.getElementById('custom_ticket_no');
        if (hiddenInput) hiddenInput.dataset.manual = '1';
        syncTctNumberFields();
    });

    // Initialize & Reset Form when Modal Opens for a fresh, blank record.
    // When the page opens with a violator prefill, preserve the server-rendered
    // values the first time and clear everything on later "New Violation" clicks.
    const hasPrefillViolator = <?= $hasPrefillViolator ? 'true' : 'false' ?>;
    let preservePrefillOnOpen = hasPrefillViolator;

    document.getElementById('recordViolationModal')?.addEventListener('show.bs.modal', function () {
        <?php if (! $reopenFormOnError): ?>
            if (preservePrefillOnOpen) {
                preservePrefillOnOpen = false;
                return;
            }

            const form = document.getElementById('violationForm');
            if (form) {
                // Clear driver, vehicle, and location inputs
                ['address', 'license_number', 'license_plate', 'mtop_number', 'vehicle_type', 'owner_name', 'owner_address', 'location', 'notes'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) input.value = '';
                });
                ['first_name', 'middle_name', 'last_name', 'driver_signature', 'officer_signature', 'violator_id'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) input.value = '';
                });
                // Reset acknowledgment radio buttons to default (Admitted)
                const ackAdmitted = document.getElementById('ackAdmitted');
                if (ackAdmitted) ackAdmitted.checked = true;

                // Uncheck all violations and reset total fine
                document.querySelectorAll('#recordViolationModal .violation-check').forEach(chk => {
                    chk.checked = false;
                    setCheckboxVisual(chk);
                });
                if (newInfractionRows) {
                    newInfractionRows.innerHTML = '';
                }
                syncNewInfractionOptions();
                updateFineAmount();

                // A fresh ticket never inherits the previous driver's signature;
                // the apprehending officer's own saved signature stays applied.
                clearTctSignature('driver');
                restoreSavedOfficerSignature();

                // Set violation datetime to accurate real-time (browser local time)
                const dtInput = document.getElementById('violation_datetime');
                if (dtInput) {
                    const now = new Date();
                    const y = now.getFullYear();
                    const m = String(now.getMonth() + 1).padStart(2, '0');
                    const d = String(now.getDate()).padStart(2, '0');
                    const h = String(now.getHours()).padStart(2, '0');
                    const min = String(now.getMinutes()).padStart(2, '0');
                    dtInput.value = `${y}-${m}-${d}T${h}:${min}`;
                }
            }
        <?php endif; ?>
    });

    // Refresh the TCT sequence when the modal opens; signatures are captured
    // in their own draw/upload dialog instead of pads inside the form.
    document.getElementById('recordViolationModal')?.addEventListener('shown.bs.modal', function () {
        refreshNextTctNumber();
    });

    document.getElementById('recordViolationModal')?.addEventListener('hidden.bs.modal', function () {
        const hiddenInput = document.getElementById('custom_ticket_no');
        if (hiddenInput) {
            hiddenInput.dataset.manual = '0';
            hiddenInput.dataset.fromOld = '0';
        }
    });

    document.getElementById('violationForm')?.addEventListener('submit', function(e) {
        syncTctNumberFields();
        const selectedIds = getSelectedNewInfractionIds();

        if (selectedIds.length === 0) {
            e.preventDefault();
            showInfractionWarning('Please select at least one violation infraction.');
            return;
        }

        if (new Set(selectedIds).size !== selectedIds.length) {
            e.preventDefault();
            showInfractionWarning('Duplicate infractions are not allowed. Please remove the duplicate entry.');
            return;
        }

        const printBtn = document.getElementById('submitPrintBtn');
        if (printBtn) {
            printBtn.disabled = true;
            printBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing Ticket...';
        }
    });

    const firstNameInput = document.getElementById('first_name');
    const middleNameInput = document.getElementById('middle_name');
    const lastNameInput = document.getElementById('last_name');
    const plateInput = document.getElementById('license_plate');
    const ageInput = document.getElementById('age');

    const sanitizeName = (value) => value.replace(/[^A-Za-z\s\-']/g, '');
    const sanitizePlate = (value) => value.toUpperCase().replace(/[^A-Z0-9\s\-]/g, '').trimStart();

    firstNameInput?.addEventListener('input', function() {
        this.value = sanitizeName(this.value);
    });
    middleNameInput?.addEventListener('input', function() {
        this.value = sanitizeName(this.value);
    });
    lastNameInput?.addEventListener('input', function() {
        this.value = sanitizeName(this.value);
    });
    plateInput?.addEventListener('input', function() {
        this.value = sanitizePlate(this.value);
    });
    ageInput?.addEventListener('input', function() {
        const v = parseInt(this.value || '0', 10);
        if (v > 120) this.value = '120';
        if (v > 0 && v < 16) this.setCustomValidity('Minimum age is 16.');
        else this.setCustomValidity('');
    });

    // ═══ VEHICLE / PLACE COMBO PICKERS ════════════════════════
    // Native <datalist> pickers cannot be opened on phones, so these fields
    // get a tap-to-open suggestion list that also filters while typing. The
    // values still come from the shared datalists on the page, and the field
    // stays free-text for anything that is not on the list.
    (function () {
        const combos = [];

        const closeCombos = (except) => {
            combos.forEach(c => { if (c !== except) c.close(); });
        };

        // Scope to this modal: the edit-modal script binds its own combo fields
        // on the same page. A global selector would give every field two click
        // handlers, so one opens the list and the other immediately closes it.
        document.querySelectorAll('#recordViolationModal input[data-combo-source]').forEach(input => {
            const wrap = input.closest('.combo-wrap');
            const list = wrap && wrap.querySelector('.combo-list');
            const toggle = wrap && wrap.querySelector('.combo-toggle');
            const source = document.getElementById(input.dataset.comboSource);
            if (!wrap || !list || !toggle || !source) return;

            const options = Array.from(source.options).map(o => o.value).filter(Boolean);

            const render = () => {
                const term = input.value.trim().toLowerCase();
                const matches = term === ''
                    ? options
                    : options.filter(value => value.toLowerCase().includes(term));
                list.innerHTML = '';
                if (matches.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'combo-empty';
                    empty.textContent = 'No match — keep typing to enter your own value.';
                    list.appendChild(empty);
                    return;
                }
                matches.forEach(value => {
                    const option = document.createElement('button');
                    option.type = 'button';
                    option.className = 'combo-option';
                    option.textContent = value;
                    option.addEventListener('click', () => {
                        input.value = value;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        api.close();
                        input.blur();
                    });
                    list.appendChild(option);
                });
            };

            const api = {
                close() {
                    list.hidden = true;
                    toggle.setAttribute('aria-expanded', 'false');
                },
                open() {
                    closeCombos(api);
                    render();
                    list.hidden = false;
                    toggle.setAttribute('aria-expanded', 'true');
                    // Centre the field so the list is never clipped by the
                    // edge of the scrollable modal on small screens.
                    if (window.matchMedia('(max-width: 767.98px)').matches) {
                        input.scrollIntoView({ block: 'center', behavior: 'smooth' });
                    }
                }
            };
            combos.push(api);

            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                if (list.hidden) api.open(); else api.close();
            });

            input.addEventListener('input', () => { if (!list.hidden) render(); });
            input.addEventListener('keydown', (event) => { if (event.key === 'Escape') api.close(); });

            // Keep the field from blurring on iOS before the tap on an option
            // registers as a click.
            list.addEventListener('mousedown', (event) => event.preventDefault());
        });

        document.addEventListener('click', (event) => {
            if (!event.target.closest('.combo-wrap')) closeCombos();
        });
    })();

    // Put the caret in the first field the moment the dialog opens.
    document.getElementById('recordViolationModal')?.addEventListener('shown.bs.modal', function () {
        document.getElementById('first_name')?.focus();
    });

    <?php if ($reopenFormOnError): ?>
    // The server bounced the submission back: reopen the form with the values
    // the browser kept so the driver can fix the field that was rejected.
    if (window.bootstrap && bootstrap.Modal) {
        bootstrap.Modal.getOrCreateInstance(document.getElementById('recordViolationModal'))?.show();
    }
    <?php endif; ?>
})();
