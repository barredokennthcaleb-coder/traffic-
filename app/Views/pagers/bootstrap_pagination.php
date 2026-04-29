<nav aria-label="Page navigation">
    <div class="d-flex align-items-center justify-content-between">
        <div class="small text-muted">
            Page <strong><?= $pager->getCurrentPageNumber() ?></strong> of <strong><?= $pager->getPageCount() ?></strong>
        </div>
        
        <div class="btn-group shadow-sm">
            <?php if ($pager->hasPrevious()) : ?>
                <a class="btn btn-sm btn-outline-primary px-3" href="<?= $pager->getPreviousPage() ?>" aria-label="Previous">
                    <i class="bi bi-chevron-left me-1"></i> Previous
                </a>
            <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary px-3 opacity-50" disabled>
                    <i class="bi bi-chevron-left me-1"></i> Previous
                </button>
            <?php endif ?>

            <?php if ($pager->hasNext()) : ?>
                <a class="btn btn-sm btn-outline-primary px-3" href="<?= $pager->getNextPage() ?>" aria-label="Next">
                    Next <i class="bi bi-chevron-right ms-1"></i>
                </a>
            <?php else: ?>
                <button class="btn btn-sm btn-outline-secondary px-3 opacity-50" disabled>
                    Next <i class="bi bi-chevron-right ms-1"></i>
                </button>
            <?php endif ?>
        </div>
    </div>
</nav>
