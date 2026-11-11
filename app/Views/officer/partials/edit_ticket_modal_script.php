    // ═══ EDIT VIOLATION CHECKLIST ═════════════════════════════
    // This block is shared by the citation ledger and a violator's record page,
    // so it carries its own helpers instead of relying on either page's script.
    const warnEdit = (message) => {
        if (window.AppToast) {
            AppToast.fire({ icon: 'warning', title: message });
        } else {
            alert(message);
        }
    };

    const markEditBox = (check) => {
        const box = check.closest('label')?.querySelector('.tct-box');
        if (!box) return;
        if (check.checked) {
            box.classList.add('is-checked');
            box.innerHTML = '<span class="tct-box-x">X</span>';
        } else {
            box.classList.remove('is-checked');
            box.innerHTML = '';
        }
    };

    const editModalEl = document.getElementById('editViolationModal');
    const editForm = document.getElementById('editViolationForm');
    const editChecklist = document.getElementById('editViolationChecklist');
    const editAmount = document.getElementById('edit_penalty_amount');

    function getEditChecks() {
        return Array.from(editChecklist?.querySelectorAll('.edit-violation-check') || []);
    }

    function updateEditComputedFields() {
        let totalAmount = 0;
        getEditChecks().forEach(check => {
            if (check.checked) {
                totalAmount += parseFloat(check.dataset.amount || 0);
            }
        });

        if (editAmount) editAmount.value = totalAmount.toFixed(2);

        const editAmountDisplay = document.getElementById('edit_penalty_amount_display');
        if (editAmountDisplay) {
            editAmountDisplay.textContent = totalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }

    // An infraction whose type is no longer active is re-added to the list so
    // saving the ticket does not silently drop it.
    function addEditChecklistEntry(id, name, amount, checked) {
        if (!editChecklist || !id) return null;

        const existing = getEditChecks().find(check => check.value === String(id));
        if (existing) {
            existing.checked = !!checked;
            markEditBox(existing);
            return existing;
        }

        const wrap = document.createElement('label');
        wrap.className = 'tct-check-label';

        const check = document.createElement('input');
        check.type = 'checkbox';
        check.name = 'violation_type_id[]';
        check.className = 'edit-violation-check d-none';
        check.value = String(id);
        check.dataset.amount = amount ?? '';
        check.dataset.name = name || '';
        check.checked = !!checked;

        const box = document.createElement('div');
        box.className = 'tct-box';
        const boxX = document.createElement('span');
        boxX.className = 'tct-box-x';
        box.appendChild(boxX);

        const text = document.createElement('span');
        text.className = 'tct-item-text';
        text.textContent = name || ('Violation #' + id);

        wrap.append(check, box, text);
        editChecklist.appendChild(wrap);
        markEditBox(check);

        return check;
    }

    function setEditChecklist(typeIds) {
        const wanted = (Array.isArray(typeIds) ? typeIds : []).map(String).filter(Boolean);
        getEditChecks().forEach(check => {
            check.checked = wanted.includes(check.value);
            markEditBox(check);
        });
    }

    editChecklist?.addEventListener('change', function (event) {
        if (!event.target.classList.contains('edit-violation-check')) return;
        markEditBox(event.target);
        updateEditComputedFields();
    });

    updateEditComputedFields();

    if (editModalEl && editForm) {
        const editModal = new bootstrap.Modal(editModalEl);
        const openEditTicketModal = (data, infractions) => {
            const id = data.id;
            editForm.action = `<?= base_url('officer/update') ?>/${id}`;

            if (document.getElementById('edit_ticket_no_display')) {
                document.getElementById('edit_ticket_no_display').textContent = data.ticketId || data.ticket_id || ('#' + id);
            }

            document.getElementById('edit_first_name').value = data.firstName || data.first_name || '';
            if(document.getElementById('edit_middle_name')) {
                document.getElementById('edit_middle_name').value = data.middleName || data.middle_name || '';
            }
            document.getElementById('edit_last_name').value = data.lastName || data.last_name || '';
            if(document.getElementById('edit_license_number')) {
                document.getElementById('edit_license_number').value = data.licenseNumber || data.license_number || '';
            }
            if(document.getElementById('edit_mtop_number')) {
                document.getElementById('edit_mtop_number').value = data.mtopNumber || data.mtop_number || '';
            }
            if(document.getElementById('edit_vehicle_type')) {
                document.getElementById('edit_vehicle_type').value = data.vehicleType || data.vehicle_type || '';
            }
            if(document.getElementById('edit_owner_name')) {
                document.getElementById('edit_owner_name').value = data.ownerName || data.owner_name || '';
            }
            if(document.getElementById('edit_owner_address')) {
                document.getElementById('edit_owner_address').value = data.ownerAddress || data.owner_address || '';
            }
            
            const ack = data.acknowledgment || '';
            const setAck = (inputId, boxId, active) => {
                const input = document.getElementById(inputId);
                const box   = document.getElementById(boxId);
                if (input) input.checked = active;
                if (box) {
                    box.classList.toggle('is-checked', active);
                    box.innerHTML = active ? '<span class="tct-box-x">X</span>' : '';
                }
            };
            setAck('edit_ackAdmitted', 'edit_ackAdmittedBox', ack === 'Admitted');
            setAck('edit_ackProtest', 'edit_ackProtestBox', ack === 'Under Protest');

            document.getElementById('edit_age').value = data.age || '';
            document.getElementById('edit_address').value = data.address || '';
            document.getElementById('edit_license_plate').value = data.licensePlate || data.license_plate || '';
            document.getElementById('edit_location').value = data.location || '';
            document.getElementById('edit_notes').value = data.notes || '';

            // Mark the violations already saved on this ticket. The checklist is
            // formatted exactly like the record form.
            const saved = Array.isArray(infractions) ? infractions : [];
            setEditChecklist(saved.map(item => item.violation_type_id));

            saved.forEach(item => {
                const typeId = String(item.violation_type_id || '');
                if (!typeId) return;
                const isKnown = getEditChecks().some(check => check.value === typeId);
                if (!isKnown) {
                    addEditChecklistEntry(typeId, item.violation_type || '', item.penalty_amount ?? '', true);
                }
            });

            updateEditComputedFields();
            editModal.show();
        };

        document.querySelectorAll('.btn-edit-violation').forEach(btn => {
            btn.addEventListener('click', function() {
                let rowInfractions = [];
                try {
                    rowInfractions = JSON.parse(this.closest('.officer-violation-row')?.dataset.infractions || '[]');
                } catch (e) {
                    rowInfractions = [];
                }
                openEditTicketModal(this.dataset, rowInfractions);
            });
        });

        // A ticket opened from a violator's history page comes back as
        // ?edit_ticket=<violation id>; re-open the modal for that citation.
        const editTicketSeed = <?= json_encode(($editTicket ?? null) ? array_merge([
            'id'          => (int) $editTicket['id'],
            'ticket_id'   => $editTicket['ticket_id'] ?? '',
            'first_name'  => $editTicket['first_name'] ?? '',
            'middle_name' => $editTicket['middle_name'] ?? '',
            'last_name'   => $editTicket['last_name'] ?? '',
            'age'         => (string) ($editTicket['age'] ?? ''),
            'address'     => $editTicket['address'] ?? '',
            'license_number' => $editTicket['license_number'] ?? '',
            'vehicle_type'   => $editTicket['vehicle_type'] ?? '',
            'mtop_number'    => $editTicket['mtop_number'] ?? '',
            'owner_name'     => $editTicket['owner_name'] ?? '',
            'owner_address'  => $editTicket['owner_address'] ?? '',
            'acknowledgment' => $editTicket['acknowledgment'] ?? '',
            'license_plate'  => $editTicket['license_plate'] ?? '',
            'location'       => $editTicket['location'] ?? '',
            'notes'          => $editTicket['notes'] ?? '',
        ], ['infractions' => $ticketInfractions[$editTicket['ticket_id']] ?? []]) : null) ?>;
        if (editTicketSeed && editTicketSeed.id) {
            const seedInfractions = editTicketSeed.infractions || [];
            delete editTicketSeed.infractions;
            openEditTicketModal(editTicketSeed, seedInfractions);
        }
        editForm.addEventListener('submit', function(e) {
            const checked = getEditChecks().filter(check => check.checked);

            if (checked.length === 0) {
                e.preventDefault();
                warnEdit('Please mark at least one violation for this ticket.');
                return;
            }

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
            }
        });
    }

    // Closing the form (Cancel or the X) leaves ?edit_ticket behind, so a
    // reload would open it again. Dropping the parameter keeps the page where
    // the reader actually is: the record they were reading.
    editModalEl?.addEventListener('hidden.bs.modal', function () {
        const url = new URL(window.location.href);
        if (!url.searchParams.has('edit_ticket')) return;
        url.searchParams.delete('edit_ticket');
        window.history.replaceState({}, '', url.pathname + url.search + url.hash);
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

        document.querySelectorAll('input[data-combo-source]').forEach(input => {
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
