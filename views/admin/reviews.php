<?php
/**
 * FR-25: yorum denetimi (neden zorunlu, denetim kaydına yazılır).
 *
 * @var App\Core\View $view
 * @var App\Support\Paginator<array{review: App\Model\Review, author: string, property: string, number: string}> $page
 * @var string $status
 * @var array<string, string> $statuses
 */
?>
<?= $view->partial('partials/admin_nav') ?>
<h1 class="h4">Yorum denetimi</h1>
<ul class="nav nav-pills mb-3 flex-wrap">
    <li class="nav-item"><a class="nav-link<?= $status === '' ? ' active' : '' ?>" href="<?= e($view->url('/yonetim/yorumlar')) ?>">Tümü</a></li>
    <?php foreach ($statuses as $value => $label): ?>
        <li class="nav-item"><a class="nav-link<?= $status === $value ? ' active' : '' ?>" href="<?= e($view->url('/yonetim/yorumlar', ['durum' => $value])) ?>"><?= e($label) ?></a></li>
    <?php endforeach; ?>
</ul>
<?php foreach ($page->items as $row): ?>
    <?php $r = $row['review']; ?>
    <div class="card shadow-sm mb-2"><div class="card-body">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <div><span class="text-warning" role="img" aria-label="<?= (int) $r->rating ?> / 5 puan"><?= str_repeat('★', $r->rating) . str_repeat('☆', 5 - $r->rating) ?></span> · <?= e($row['author']) ?> · <?= e($row['property']) ?> · <span class="font-monospace small"><?= e($row['number']) ?></span></div>
            <span class="badge text-bg-secondary align-self-start"><?= e($r->moderationStatus->label()) ?></span>
        </div>
        <p class="my-2"><?= e($r->comment) ?></p>
        <form method="post" action="<?= e($view->url('/yonetim/yorum/' . $r->id)) ?>" class="row g-2">
            <?= $view->csrfField() ?>
            <div class="col-12 col-md-3"><select name="durum" class="form-select form-select-sm" aria-label="Denetim durumu"><?php foreach ($statuses as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($r->moderationStatus->value, $value) ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-md-7"><input type="text" name="neden" class="form-control form-control-sm" placeholder="Neden (zorunlu)" required minlength="5" aria-label="Neden"></div>
            <div class="col-12 col-md-2"><button type="submit" class="btn btn-outline-success btn-sm w-100">Kaydet</button></div>
        </form>
    </div></div>
<?php endforeach; ?>
<?php if ($page->total === 0): ?><p class="text-body-secondary">Yorum yok.</p><?php endif; ?>
<?= $view->partial('partials/pagination', ['paginator' => $page, 'path' => '/yonetim/yorumlar', 'query' => ['durum' => $status]]) ?>
