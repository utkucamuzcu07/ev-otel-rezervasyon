<?php
/**
 * Bonus: sayfalama bağlantıları (mevcut arama/filtre değerleri korunur).
 *
 * @var App\Core\View $view
 * @var App\Support\Paginator<mixed> $paginator
 * @var string $path
 * @var array<string, mixed> $query
 */
if (!$paginator->hasPages()) {
    return;
}
$link = fn (int $page): string => $view->url($path, ['sayfa' => $page] + $query);
?>
<nav aria-label="Sayfalar" class="mt-3">
    <ul class="pagination pagination-sm flex-wrap">
        <li class="page-item<?= $paginator->page <= 1 ? ' disabled' : '' ?>">
            <a class="page-link" href="<?= e($link(max(1, $paginator->page - 1))) ?>">‹ Önceki</a>
        </li>
        <?php for ($p = 1; $p <= $paginator->pages; $p++): ?>
            <li class="page-item<?= $p === $paginator->page ? ' active' : '' ?>">
                <a class="page-link" href="<?= e($link($p)) ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>
        <li class="page-item<?= $paginator->page >= $paginator->pages ? ' disabled' : '' ?>">
            <a class="page-link" href="<?= e($link(min($paginator->pages, $paginator->page + 1))) ?>">Sonraki ›</a>
        </li>
    </ul>
    <p class="small text-body-secondary mb-0">Toplam <?= (int) $paginator->total ?> kayıt · Sayfa <?= (int) $paginator->page ?> / <?= (int) $paginator->pages ?></p>
</nav>
