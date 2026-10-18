<nav aria-label="Page navigation">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pag-wrapper">

        <!-- Page info -->
        <div class="pag-info d-none d-sm-flex align-items-center gap-2">
            <span class="pag-info-label">Page</span>
            <span class="pag-current"><?= $pager->getCurrentPageNumber() ?></span>
            <span class="pag-info-label">of</span>
            <span class="pag-total"><?= $pager->getPageCount() ?></span>
        </div>

        <!-- Buttons -->
        <ul class="pagination custom-pagination mb-0">

            <!-- Prev -->
            <li class="page-item <?= $pager->hasPreviousPage() ? '' : 'disabled' ?>">
                <?php if ($pager->hasPreviousPage()): ?>
                    <a class="page-link pag-btn pag-btn--nav" href="<?= $pager->getPreviousPage() ?>" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="page-link pag-btn pag-btn--nav"><i class="bi bi-chevron-left"></i></span>
                <?php endif ?>
            </li>

            <!-- Numbers -->
            <?php foreach ($pager->links() as $link): ?>
                <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                    <a class="page-link pag-btn" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
                </li>
            <?php endforeach ?>

            <!-- Next -->
            <li class="page-item <?= $pager->hasNextPage() ? '' : 'disabled' ?>">
                <?php if ($pager->hasNextPage()): ?>
                    <a class="page-link pag-btn pag-btn--nav" href="<?= $pager->getNextPage() ?>" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="page-link pag-btn pag-btn--nav"><i class="bi bi-chevron-right"></i></span>
                <?php endif ?>
            </li>

        </ul>
    </div>
</nav>


<style>
.pag-wrapper { padding: 0.5rem 0; }

/* Info badges */
.pag-info-label { font-size: 0.72rem !important; font-weight: 700 !important; color: #94a3b8 !important; text-transform: uppercase; letter-spacing: 0.07em; }
.pag-current {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    min-width: 30px; height: 30px; padding: 0 0.55rem; border-radius: 9px !important;
    background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
    color: #fff !important; font-size: 0.84rem !important; font-weight: 800 !important;
    box-shadow: 0 4px 14px rgba(99,102,241,0.35) !important;
}
.pag-total {
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    min-width: 30px; height: 30px; padding: 0 0.55rem; border-radius: 9px !important;
    background: #f1f5f9 !important; color: #64748b !important; font-size: 0.84rem !important; font-weight: 700 !important;
    border: 1.5px solid #e2e8f0 !important;
}

/* Buttons */
.custom-pagination { gap: 5px !important; }
.custom-pagination .page-item .pag-btn {
    width: 38px !important; height: 38px !important; padding: 0 !important;
    display: inline-flex !important; align-items: center !important; justify-content: center !important;
    border-radius: 11px !important;
    border: 1.5px solid #e2e8f0 !important;
    background: #ffffff !important;
    color: #475569 !important;
    font-size: 0.86rem !important; font-weight: 600 !important;
    box-shadow: 0 1px 4px rgba(15,23,42,0.05) !important;
    text-decoration: none !important;
    transition:
        background   0.25s cubic-bezier(0.34,1.56,0.64,1),
        color        0.25s cubic-bezier(0.34,1.56,0.64,1),
        border-color 0.25s cubic-bezier(0.34,1.56,0.64,1),
        transform    0.25s cubic-bezier(0.34,1.56,0.64,1),
        box-shadow   0.25s cubic-bezier(0.34,1.56,0.64,1) !important;
}
.custom-pagination .pag-btn--nav { color: #64748b !important; font-size: 0.79rem !important; }

/* Hover */
.custom-pagination .page-item:not(.active):not(.disabled) .pag-btn:hover {
    background: #eff6ff !important; color: #2563eb !important; border-color: #93c5fd !important;
    transform: translateY(-4px) scale(1.06) !important;
    box-shadow: 0 8px 20px rgba(59,130,246,0.18) !important;
}

/* Active */
.custom-pagination .page-item.active .pag-btn {
    background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%) !important;
    color: #ffffff !important; border-color: transparent !important;
    box-shadow: 0 5px 18px rgba(99,102,241,0.4) !important;
    transform: translateY(-3px) scale(1.08) !important;
    pointer-events: none;
    font-weight: 800 !important;
}

/* Disabled */
.custom-pagination .page-item.disabled .pag-btn {
    background: #f8fafc; color: #cbd5e1; border-color: #f1f5f9;
    box-shadow: none; opacity: 0.55; cursor: not-allowed; pointer-events: none;
}

/* Mobile */
@media (max-width: 576px) {
    .custom-pagination .page-item .pag-btn { width: 34px !important; height: 34px !important; font-size: 0.80rem !important; border-radius: 9px !important; }
    .custom-pagination { gap: 3px !important; }
    .pag-current, .pag-total { min-width: 26px; height: 26px; font-size: 0.78rem !important; }
}
@media (max-width: 360px) {
    .custom-pagination .page-item .pag-btn { width: 30px !important; height: 30px !important; font-size: 0.74rem !important; }
}
</style>

