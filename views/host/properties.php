<?php
/**
 * FR-20, FR-21: ilan listesi ve işlemler.
 *
 * @var App\Core\View $view
 * @var list<array{property: App\Model\Property, units: int, photos: int}> $rows
 */
?>
<div class="row g-3">
    <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <div class="col-12 col-lg-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">İlanlarım</h1>
            <a class="btn btn-success btn-sm" href="<?= e($view->url('/panel/ilan/yeni')) ?>">➕ Yeni ilan</a>
        </div>
        <?php if ($rows === []): ?>
            <p class="text-body-secondary">Henüz ilanınız yok.</p>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
            <?php $p = $row['property']; ?>
            <div class="card shadow-sm mb-2">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <strong><?= e($p->type->emoji() . ' ' . $p->name) ?></strong>
                        <span class="badge text-bg-<?= $p->status->value === 'aktif' ? 'success' : 'secondary' ?>"><?= e($p->status->label()) ?></span>
                        <div class="small text-body-secondary"><?= e($p->city) ?> · <?= (int) $row['units'] ?> oda/birim · <?= (int) $row['photos'] ?> fotoğraf · <?= e($p->bookingMode->label()) ?> · <?= e($p->cancellationPolicy->label()) ?> iptal</div>
                    </div>
                    <div class="d-flex flex-wrap gap-1">
                        <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/panel/ilan/' . $p->id)) ?>">Düzenle</a>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/panel/ilan/' . $p->id . '/odalar')) ?>">Odalar</a>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/panel/ilan/' . $p->id . '/fiyat')) ?>">Fiyat kuralları</a>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/panel/ilan/' . $p->id . '/fotograflar')) ?>">Fotoğraflar</a>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/panel/takvim', ['ilan' => $p->id])) ?>">Takvim</a>
                        <a class="btn btn-outline-success btn-sm" href="<?= e($view->url('/ilan/' . $p->id)) ?>">Görüntüle</a>
                        <form method="post" action="<?= e($view->url('/panel/ilan/' . $p->id . '/sil')) ?>" onsubmit="return confirm('İlan silinsin mi? Gelecekte rezervasyonu varsa silinmez.')">
                            <?= $view->csrfField() ?>
                            <button type="submit" class="btn btn-outline-danger btn-sm">Sil</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
