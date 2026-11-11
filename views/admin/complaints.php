<?php
/**
 * FR-27: şikâyetlerin incelenmesi. Kullanıcıların gönderdiği şikâyetler buraya düşer;
 * durum veya not güncellenince şikâyet sahibine bildirim gider.
 *
 * @var App\Core\View $view
 * @var App\Support\Paginator<array{complaint: App\Model\Complaint, sender: string, property: string, number: string}> $page
 * @var string $status
 * @var array<string, string> $statuses
 */
?>
<?= $view->partial('partials/admin_nav') ?>
<div class="sayfa-baslik">
    <h1>Şikâyetler</h1>
    <p>Kullanıcıların gönderdiği şikâyetler bu kutuya düşer. Durumu veya notu güncellediğinizde şikâyet sahibine bildirim gider.</p>
</div>
<ul class="nav nav-pills mb-3 flex-wrap">
    <li class="nav-item"><a class="nav-link<?= $status === '' ? ' active' : '' ?>" href="<?= e($view->url('/yonetim/sikayetler')) ?>">Tümü</a></li>
    <?php foreach ($statuses as $value => $label): ?>
        <li class="nav-item"><a class="nav-link<?= $status === $value ? ' active' : '' ?>" href="<?= e($view->url('/yonetim/sikayetler', ['durum' => $value])) ?>"><?= e($label) ?></a></li>
    <?php endforeach; ?>
</ul>
<?php foreach ($page->items as $row): ?>
    <?php $c = $row['complaint']; ?>
    <div class="panel p-3 mb-2">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <div><strong>#<?= (int) $c->id ?></strong> · <?= e($row['sender']) ?> · İlan: <?= e($row['property']) ?><?php if ($row['number'] !== '-'): ?> · Rezervasyon: <span class="font-monospace"><?= e($row['number']) ?></span><?php endif; ?></div>
            <div class="small text-body-secondary"><?= $view->date($c->createdAt, 'd.m.Y H:i') ?> · <span class="rozet rozet-<?= e($c->status->tone()) ?>"><?= e($c->status->label()) ?></span></div>
        </div>
        <p class="my-2"><?= nl2br(e($c->description)) ?></p>
        <?php if ($c->adminNote !== null): ?><p class="small text-body-secondary mb-2">Yönetici notu: <?= e($c->adminNote) ?></p><?php endif; ?>
        <form method="post" action="<?= e($view->url('/yonetim/sikayet/' . $c->id)) ?>" class="row g-2">
            <?= $view->csrfField() ?>
            <div class="col-12 col-md-3"><select name="durum" class="form-select form-select-sm" aria-label="Durum"><?php foreach ($statuses as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($c->status->value, $value) ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
            <div class="col-12 col-md-7"><input type="text" name="not" maxlength="2000" class="form-control form-control-sm" placeholder="Yönetici notu (şikâyet sahibine iletilir)" aria-label="Yönetici notu"></div>
            <div class="col-12 col-md-2"><button type="submit" class="btn btn-outline-primary btn-sm w-100">Kaydet</button></div>
        </form>
    </div>
<?php endforeach; ?>
<?php if ($page->total === 0): ?><p class="text-body-secondary">Şikâyet yok.</p><?php endif; ?>
<?= $view->partial('partials/pagination', ['paginator' => $page, 'path' => '/yonetim/sikayetler', 'query' => ['durum' => $status]]) ?>
