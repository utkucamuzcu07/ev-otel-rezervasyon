<?php
/**
 * Taslak 02: Arama sonuçları, filtreler ve sıralama (FR-06, FR-07, FR-08).
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\SearchResult $result
 * @var list<App\Model\Amenity> $amenities
 * @var array<string, string> $types
 * @var array<string, string> $roomTypes
 * @var array<string, mixed> $q
 */
$c = $result->criteria;
$selectedRoomTypes = array_map(fn ($t) => $t->value, $c->roomTypes);
$summary = ($c->city !== '' ? $c->city : 'Tüm şehirler')
    . ($result->range !== null ? ' · ' . $result->range : '')
    . ' · ' . $c->guests . ' kişi · ' . ($c->type?->label() ?? 'Tümü');
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 sayfa-baslik">
    <div>
        <h1>Arama sonuçları</h1>
        <p><?= e($summary) ?></p>
    </div>
    <button class="btn btn-outline-primary btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#arama-formu" aria-expanded="false">Aramayı değiştir</button>
</div>

<form method="get" action="<?= e($view->url('/ara')) ?>" id="filtre-formu" novalidate>
    <div class="collapse<?= $result->errors !== [] ? ' show' : '' ?> mb-3" id="arama-formu">
        <div class="card card-body shadow-sm">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold" for="konum">Konum / Şehir <span class="text-body-secondary">(yazdıkça sonuçlar güncellenir)</span></label>
                    <input type="text" class="form-control" id="konum" name="konum" value="<?= e($c->city) ?>" autocomplete="off" data-canli-arama>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold" for="giris">Giriş</label>
                    <input type="date" class="form-control<?= $view->invalid($result->errors, 'giris') ?>" id="giris" name="giris" value="<?= e($c->checkIn) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold" for="cikis">Çıkış</label>
                    <input type="date" class="form-control<?= $view->invalid($result->errors, 'cikis') ?>" id="cikis" name="cikis" value="<?= e($c->checkOut) ?>">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold" for="kisi">Kişi</label>
                    <input type="number" class="form-control<?= $view->invalid($result->errors, 'kisi') ?>" id="kisi" name="kisi" min="1" max="20" value="<?= (int) $c->guests ?>">
                </div>
                <div class="col-6 col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Ara</button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <aside class="col-12 col-lg-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h5 mb-3">Filtreler</h2>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Gecelik fiyat aralığı (TL)</label>
                        <div class="input-group input-group-sm">
                            <input type="number" class="form-control<?= $view->invalid($result->errors, 'fiyat') ?>" name="fiyat_min" min="0" placeholder="min" value="<?= e($q['fiyat_min'] ?? '') ?>" aria-label="En düşük fiyat">
                            <span class="input-group-text">–</span>
                            <input type="number" class="form-control" name="fiyat_max" min="0" placeholder="max" value="<?= e($q['fiyat_max'] ?? '') ?>" aria-label="En yüksek fiyat">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="puan">En düşük puan</label>
                        <select class="form-select form-select-sm" id="puan" name="puan">
                            <option value="">Fark etmez</option>
                            <?php foreach (['4.5' => '4,5 ve üzeri', '4' => '4 ve üzeri', '3.5' => '3,5 ve üzeri'] as $value => $label): ?>
                                <option value="<?= e($value) ?>"<?= $view->selected($q['puan'] ?? '', $value) ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="tur">Konaklama türü</label>
                        <select class="form-select form-select-sm" id="tur" name="tur">
                            <option value="">Tümü</option>
                            <?php foreach ($types as $value => $label): ?>
                                <option value="<?= e($value) ?>"<?= $view->selected($c->type?->value ?? '', $value) ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <fieldset class="mb-3">
                        <legend class="form-label small fw-semibold">Oda tipi</legend>
                        <?php foreach ($roomTypes as $value => $label): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="oda_tipi[]" value="<?= e($value) ?>" id="oda-<?= e($value) ?>"<?= $view->checked(in_array($value, $selectedRoomTypes, true)) ?>>
                                <label class="form-check-label small" for="oda-<?= e($value) ?>"><?= e($label) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                    <fieldset class="mb-3">
                        <legend class="form-label small fw-semibold">Olanaklar</legend>
                        <?php foreach ($amenities as $amenity): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="olanak[]" value="<?= (int) $amenity->id ?>" id="olanak-<?= (int) $amenity->id ?>"<?= $view->checked(in_array((int) $amenity->id, $c->amenityIds, true)) ?>>
                                <label class="form-check-label small" for="olanak-<?= (int) $amenity->id ?>"><?= e($amenity->name) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </fieldset>
                    <button type="submit" class="btn btn-primary w-100">Filtreleri uygula</button>
                    <a class="btn btn-link btn-sm w-100 mt-1" href="<?= e($view->url('/ara', ['konum' => $c->city, 'giris' => $c->checkIn, 'cikis' => $c->checkOut, 'kisi' => $c->guests])) ?>">Filtreleri temizle</a>
                </div>
            </div>
        </aside>

        <section class="col-12 col-lg-9">
            <div class="d-flex justify-content-end align-items-center gap-2 mb-2">
                <label for="sirala" class="small fw-semibold">Sırala:</label>
                <select class="form-select form-select-sm w-auto" id="sirala" name="sirala">
                    <option value="fiyat_artan"<?= $view->selected($c->sort, 'fiyat_artan') ?>>Fiyat (artan)</option>
                    <option value="fiyat_azalan"<?= $view->selected($c->sort, 'fiyat_azalan') ?>>Fiyat (azalan)</option>
                    <option value="puan"<?= $view->selected($c->sort, 'puan') ?>>Puan (yüksekten)</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary btn-sm">Uygula</button>
            </div>
            <div id="sonuclar">
                <?= $view->partial('property/_results', ['result' => $result, 'q' => $q]) ?>
            </div>
        </section>
    </div>
</form>

<script>
// Bonus: yazarken sonuç veren canlı arama. PHP listeyi üretir, JS yalnızca yerine koyar.
const canliKutu = document.querySelector('[data-canli-arama]');
let canliZamanlayici = null;
canliKutu.addEventListener('input', function () {
    clearTimeout(canliZamanlayici);
    canliZamanlayici = setTimeout(async function () {
        const parametreler = new URLSearchParams(new FormData(document.getElementById('filtre-formu')));
        const yanit = await fetch('<?= e($view->url('/parca/arama')) ?>?' + parametreler.toString());
        document.getElementById('sonuclar').innerHTML = await yanit.text();
    }, 300);
});
</script>
