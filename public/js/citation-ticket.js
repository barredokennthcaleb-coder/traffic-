/**
 * Citation TCT popup.
 *
 * The trigger buttons use Bootstrap's data API (data-bs-toggle="modal"), so
 * opening the dialog never depends on an inline onclick attribute. This file
 * only relocates the one real #tctCard into the dialog while it is open and
 * returns it to its print container afterwards — printing therefore always
 * sees exactly one copy of the official document.
 */
(function () {
    'use strict';

    function init() {
        var shell     = document.getElementById('ticketCollapse');
        var card      = document.getElementById('tctCard');
        var modalEl   = document.getElementById('citationTicketModal');
        var modalBody = document.getElementById('citationTicketModalBody');

        if (!shell || !card || !modalEl || !modalBody) return;

        function placeCard() {
            modalBody.appendChild(card);
        }

        function restoreCard() {
            shell.appendChild(card);
            shell.classList.remove('is-open');
        }

        // Fires for the data-bs-toggle buttons and for programmatic show().
        modalEl.addEventListener('show.bs.modal', placeCard);
        modalEl.addEventListener('hidden.bs.modal', restoreCard);

        // Kept for older callers that still invoke these names directly.
        window.openCitationTicketModal = function () {
            if (window.bootstrap && window.bootstrap.Modal) {
                window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                // Bootstrap missing: keep the document reachable inline.
                placeCard();
                shell.classList.add('is-open');
                shell.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };
        window.showCitationTicket = window.openCitationTicketModal;
        window.toggleCitationTicket = window.openCitationTicketModal;
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
