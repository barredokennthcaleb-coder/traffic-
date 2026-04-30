<nav aria-label="Page navigation">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="small text-muted">
            Page <strong><?= $pager->getCurrentPageNumber() ?></strong> of <strong><?= $pager->getPageCount() ?></strong>
        </div>

        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
                <?php if ($pager->hasPrevious()) : ?>
                    <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                <?php else : ?>
                    <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
                <?php endif ?>
            </li>

            <?php foreach ($pager->links() as $link) : ?>
                <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
                </li>
            <?php endforeach ?>

            <li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
                <?php if ($pager->hasNext()) : ?>
                    <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                <?php else : ?>
                    <span class="page-link" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
                <?php endif ?>
            </li>
        </ul>
    </div>
</nav>
