<?php
/**
 * FR-46: resim yükleme (JPEG/PNG/WebP, en çok 2 MB), sıralama ve silme.
 *
 * @var App\Core\View $view
 * @var App\Model\Property $property
 * @var list<App\Model\Photo> $photos
 * @var list<App\Model\BookableUnit> $units
 * @var array<string, string> $errors
 */
$action = $view->url('/panel/ilan/' . $property->id . '/fotograflar');
$unitNames = [];
foreach ($units as $unit) {
    $unitNames[(int) $unit->id] = $unit->name;
}
?>
<div class="row g-3">
    <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <div class="col-12 col-lg-9">
        <h1 class="h4">Fotoğraflar · <?= e($property->name) ?></h1>
        <form method="post" action="<?= e($action) ?>" enctype="multipart/form-data" class="card card-body shadow-sm mb-3" novalidate>
            <?= $view->csrfField() ?>
            <input type="hidden" name="islem" value="yukle">
            <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
            <label for="foto" class="form-label fw-semibold">Resim seç (JPEG, PNG veya WebP · en çok 2 MB)</label>
            <div class="input-group">
                <input type="file" class="form-control<?= $view->invalid($errors, 'foto') ?>" id="foto" name="foto" accept="image/jpeg,image/png,image/webp" required>
                <button type="submit" class="btn btn-success">Yükle</button>
            </div>
            <?= $view->error($errors, 'foto') ?>
            <label for="birim" class="form-label fw-semibold mt-2 mb-1">Ait olduğu oda (isteğe bağlı)</label>
            <select class="form-select<?= $view->invalid($errors, 'birim') ?>" id="birim" name="birim">
                <option value="">İlanın geneli</option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?= (int) $unit->id ?>"><?= e($unit->name . ' · ' . $unit->roomType->label()) ?></option>
                <?php endforeach; ?>
            </select>
            <?= $view->error($errors, 'birim') ?>
            <div class="form-text">Dosya adı sunucuda rastgele üretilir; dosyalar web klasörünün dışında saklanır.</div>
        </form>

        <div class="row g-3">
            <?php foreach ($photos as $i => $photo): ?>
                <div class="col-6 col-md-4">
                    <div class="card shadow-sm h-100">
                        <img src="<?= e($view->url('/foto/' . $photo->id)) ?>" class="kucuk-foto card-img-top" alt="Fotoğraf <?= $i + 1 ?>" loading="lazy">
                        <div class="card-body p-2 d-flex justify-content-between align-items-center">
                            <span class="small text-body-secondary">#<?= $i + 1 ?> · <?= e($photo->unitId !== null ? ($unitNames[$photo->unitId] ?? 'oda') : 'genel') ?> · <?= e(number_format($photo->sizeBytes / 1024, 0, ',', '.')) ?> KB</span>
                            <span class="d-flex gap-1">
                                <?php foreach (['yukari' => '↑', 'asagi' => '↓', 'sil' => '✕'] as $op => $label): ?>
                                    <form method="post" action="<?= e($action) ?>"<?= $op === 'sil' ? ' onsubmit="return confirm(\'Fotoğraf silinsin mi?\')"' : '' ?>>
                                        <?= $view->csrfField() ?>
                                        <input type="hidden" name="islem" value="<?= $op ?>">
                                        <input type="hidden" name="foto_id" value="<?= (int) $photo->id ?>">
                                        <button type="submit" class="btn btn-sm <?= $op === 'sil' ? 'btn-outline-danger' : 'btn-outline-secondary' ?>" aria-label="<?= e($op) ?>"><?= $label ?></button>
                                    </form>
                                <?php endforeach; ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if ($photos === []): ?><p class="text-body-secondary">Henüz fotoğraf yok; ilan kartlarında emoji gösterilir.</p><?php endif; ?>
        </div>
    </div>
</div>
