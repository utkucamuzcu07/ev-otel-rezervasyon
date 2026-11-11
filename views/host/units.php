<?php
/**
 * FR-42: oda/birim yönetimi (ad, oda tipi, kapasite, temel gecelik fiyat).
 *
 * @var App\Core\View $view
 * @var App\Model\Property $property
 * @var list<App\Model\BookableUnit> $units
 * @var App\Model\BookableUnit|null $editing
 * @var array<string, string> $errors
 * @var array<string, mixed> $old
 * @var array<string, string> $roomTypes
 */
use App\Support\Decimal;

$val = function (string $key, string $default) use ($old): string {
    return is_string($old[$key] ?? null) ? $old[$key] : $default;
};
?>
<div class="row g-3">
    <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <div class="col-12 col-lg-9">
        <h1 class="h4">Odalar / birimler · <?= e($property->name) ?></h1>
        <p class="small text-body-secondary">Rezervasyon ve çakışma kontrolü birim bazındadır. Kiralık ev tek birimdir; otelde her oda ayrı birimdir.</p>
        <div class="table-responsive mb-3">
            <table class="table table-sm align-middle bg-white">
                <thead class="table-light"><tr><th>Ad</th><th>Oda tipi</th><th>Kapasite</th><th class="text-end">Temel gecelik</th><th>Durum</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($units as $unit): ?>
                    <tr>
                        <td><?= e($unit->name) ?></td>
                        <td><?= e($unit->roomType->label()) ?></td>
                        <td><?= (int) $unit->capacity ?></td>
                        <td class="text-end"><?= $view->money($unit->basePrice) ?></td>
                        <td><?= e($unit->status->label()) ?></td>
                        <td class="text-end"><a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/panel/ilan/' . $property->id . '/odalar', ['duzenle' => $unit->id])) ?>">Düzenle</a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($units === []): ?><tr><td colspan="6" class="text-body-secondary">Henüz oda yok.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>

        <form method="post" action="<?= e($view->url('/panel/ilan/' . $property->id . '/odalar')) ?>" class="card card-body shadow-sm" novalidate>
            <h2 class="h6 fw-bold"><?= $editing === null ? 'Yeni oda / birim ekle' : 'Odayı düzenle: ' . e($editing->name) ?></h2>
            <?= $view->csrfField() ?>
            <?php if ($editing !== null): ?><input type="hidden" name="birim_id" value="<?= (int) $editing->id ?>"><?php endif; ?>
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <label for="ad" class="form-label">Ad (ilan içinde benzersiz)</label>
                    <input type="text" class="form-control<?= $view->invalid($errors, 'ad') ?>" id="ad" name="ad" value="<?= e($val('ad', $editing->name ?? '')) ?>" required>
                    <?= $view->error($errors, 'ad') ?>
                </div>
                <div class="col-6 col-md-3">
                    <label for="oda_tipi" class="form-label">Oda tipi</label>
                    <select class="form-select<?= $view->invalid($errors, 'oda_tipi') ?>" id="oda_tipi" name="oda_tipi">
                        <?php foreach ($roomTypes as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($val('oda_tipi', $editing->roomType->value ?? ''), $value) ?>><?= e($label) ?></option><?php endforeach; ?>
                    </select>
                    <?= $view->error($errors, 'oda_tipi') ?>
                </div>
                <div class="col-6 col-md-2">
                    <label for="kapasite" class="form-label">Kapasite</label>
                    <input type="number" class="form-control<?= $view->invalid($errors, 'kapasite') ?>" id="kapasite" name="kapasite" min="1" max="20" value="<?= e($val('kapasite', (string) ($editing->capacity ?? 2))) ?>" required>
                    <?= $view->error($errors, 'kapasite') ?>
                </div>
                <div class="col-6 col-md-3">
                    <label for="fiyat" class="form-label">Temel gecelik (TL)</label>
                    <input type="number" step="0.01" min="0" class="form-control<?= $view->invalid($errors, 'fiyat') ?>" id="fiyat" name="fiyat" value="<?= e($val('fiyat', $editing !== null ? Decimal::fromInt($editing->basePrice) : '')) ?>" required>
                    <?= $view->error($errors, 'fiyat') ?>
                </div>
                <div class="col-6 col-md-3">
                    <label for="durum" class="form-label">Durum</label>
                    <select class="form-select" id="durum" name="durum">
                        <option value="aktif"<?= $view->selected($val('durum', $editing->status->value ?? 'aktif'), 'aktif') ?>>Aktif</option>
                        <option value="pasif"<?= $view->selected($val('durum', $editing->status->value ?? 'aktif'), 'pasif') ?>>Pasif</option>
                    </select>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-success"><?= $editing === null ? 'Ekle' : 'Kaydet' ?></button>
                <?php if ($editing !== null): ?><a class="btn btn-outline-secondary" href="<?= e($view->url('/panel/ilan/' . $property->id . '/odalar')) ?>">Vazgeç</a><?php endif; ?>
            </div>
        </form>
    </div>
</div>
