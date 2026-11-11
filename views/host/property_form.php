<?php
/**
 * FR-20, FR-21, FR-38: ilan oluşturma / düzenleme.
 *
 * @var App\Core\View $view
 * @var int|null $propertyId
 * @var array<string, string> $errors
 * @var array<string, mixed> $old
 * @var list<App\Model\Amenity> $amenities
 * @var array<string, string> $types
 * @var array<string, string> $modes
 * @var array<string, string> $policies
 * @var array<string, string> $statuses
 */
$val = fn (string $key): string => is_string($old[$key] ?? null) ? $old[$key] : '';
$selectedAmenities = is_array($old['olanak'] ?? null) ? $old['olanak'] : [];
?>
<div class="row g-3">
    <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <div class="col-12 col-lg-9">
        <h1 class="h4 mb-3"><?= $propertyId === null ? 'Yeni ilan' : 'İlanı düzenle' ?></h1>
        <form method="post" action="<?= e($view->url($propertyId === null ? '/panel/ilan/yeni' : '/panel/ilan/' . $propertyId)) ?>" class="card shadow-sm card-body" novalidate>
            <?= $view->csrfField() ?>
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label for="ad" class="form-label">İlan adı</label>
                    <input type="text" class="form-control<?= $view->invalid($errors, 'ad') ?>" id="ad" name="ad" maxlength="120" value="<?= e($val('ad')) ?>" required>
                    <?= $view->error($errors, 'ad') ?>
                </div>
                <div class="col-12 col-md-4">
                    <label for="tur" class="form-label">Tür</label>
                    <select class="form-select<?= $view->invalid($errors, 'tur') ?>" id="tur" name="tur">
                        <?php foreach ($types as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($val('tur'), $value) ?>><?= e($label) ?></option><?php endforeach; ?>
                    </select>
                    <?= $view->error($errors, 'tur') ?>
                </div>
                <div class="col-12">
                    <label for="aciklama" class="form-label">Açıklama</label>
                    <textarea class="form-control<?= $view->invalid($errors, 'aciklama') ?>" id="aciklama" name="aciklama" rows="4" required><?= e($val('aciklama')) ?></textarea>
                    <?= $view->error($errors, 'aciklama') ?>
                </div>
                <div class="col-12 col-md-8">
                    <label for="adres" class="form-label">Adres</label>
                    <input type="text" class="form-control<?= $view->invalid($errors, 'adres') ?>" id="adres" name="adres" value="<?= e($val('adres')) ?>" required>
                    <?= $view->error($errors, 'adres') ?>
                </div>
                <div class="col-12 col-md-4">
                    <label for="sehir" class="form-label">Şehir</label>
                    <input type="text" class="form-control<?= $view->invalid($errors, 'sehir') ?>" id="sehir" name="sehir" value="<?= e($val('sehir')) ?>" required>
                    <?= $view->error($errors, 'sehir') ?>
                </div>
                <div class="col-12 col-md-4">
                    <label for="mod" class="form-label">Rezervasyon modu</label>
                    <select class="form-select<?= $view->invalid($errors, 'mod') ?>" id="mod" name="mod">
                        <?php foreach ($modes as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($val('mod'), $value) ?>><?= e($label) ?></option><?php endforeach; ?>
                    </select>
                    <?= $view->error($errors, 'mod') ?>
                </div>
                <div class="col-12 col-md-4">
                    <label for="politika" class="form-label">İptal politikası</label>
                    <select class="form-select<?= $view->invalid($errors, 'politika') ?>" id="politika" name="politika">
                        <?php foreach ($policies as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($val('politika'), $value) ?>><?= e($label) ?></option><?php endforeach; ?>
                    </select>
                    <?= $view->error($errors, 'politika') ?>
                    <div class="form-text">Değişiklik yalnızca yeni rezervasyonlara uygulanır.</div>
                </div>
                <div class="col-12 col-md-4">
                    <label for="durum" class="form-label">Durum</label>
                    <select class="form-select<?= $view->invalid($errors, 'durum') ?>" id="durum" name="durum">
                        <?php foreach ($statuses as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($val('durum'), $value) ?>><?= e($label) ?></option><?php endforeach; ?>
                    </select>
                    <?= $view->error($errors, 'durum') ?>
                    <div class="form-text">Pasif ilan yeni rezervasyon almaz; mevcutlar değişmez.</div>
                </div>
                <fieldset class="col-12">
                    <legend class="form-label fs-6">Olanaklar</legend>
                    <?php foreach ($amenities as $amenity): ?>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" name="olanak[]" id="olanak-<?= (int) $amenity->id ?>" value="<?= (int) $amenity->id ?>"<?= $view->checked(in_array((string) $amenity->id, $selectedAmenities, true)) ?>>
                            <label class="form-check-label" for="olanak-<?= (int) $amenity->id ?>"><?= e($amenity->name) ?></label>
                        </div>
                    <?php endforeach; ?>
                    <?= $view->error($errors, 'olanaklar') ?>
                </fieldset>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-success">Kaydet</button>
                <a class="btn btn-outline-secondary" href="<?= e($view->url('/panel/ilanlar')) ?>">Vazgeç</a>
            </div>
        </form>
    </div>
</div>
