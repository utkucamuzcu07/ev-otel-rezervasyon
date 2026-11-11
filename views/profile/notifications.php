<?php
/**
 * FR-26: uygulama içi bildirimler.
 *
 * @var App\Core\View $view
 * @var App\Support\Paginator<App\Model\Notification> $page
 */
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Bildirimler</h1>
    <form method="post" action="<?= e($view->url('/bildirimler/okundu')) ?>">
        <?= $view->csrfField() ?>
        <button type="submit" class="btn btn-outline-secondary btn-sm">Tümünü okundu işaretle</button>
    </form>
</div>
<div class="list-group shadow-sm">
    <?php foreach ($page->items as $n): ?>
        <div class="list-group-item<?= $n->isRead ? '' : ' list-group-item-warning' ?>">
            <div class="d-flex justify-content-between gap-2">
                <span><?= $n->isRead ? '' : '🆕 ' ?><?= e($n->message) ?></span>
                <span class="small text-body-secondary text-nowrap"><?= $view->date($n->createdAt, 'd.m.Y H:i') ?></span>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if ($page->total === 0): ?>
        <div class="list-group-item text-body-secondary">Bildiriminiz yok.</div>
    <?php endif; ?>
</div>
<?= $view->partial('partials/pagination', ['paginator' => $page, 'path' => '/bildirimler', 'query' => []]) ?>
