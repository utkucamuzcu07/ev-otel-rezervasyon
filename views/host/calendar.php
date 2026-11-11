<?php
/**
 * Taslak 06: Takvim ve fiyat yönetimi (FR-22). Rezervasyonlu geceler kilitlidir (kapatılamaz).
 *
 * @var App\Core\View $view
 * @var list<array{property: App\Model\Property, units: int, photos: int}> $rows
 * @var int $propertyId
 * @var list<App\Model\BookableUnit> $units
 * @var int $unitId
 * @var string $month
 * @var App\Service\Dto\MonthCalendar|null $calendar
 * @var array<string, string> $errors
 * @var array<string, mixed> $old
 */
$val = fn (string $key): string => is_string($old[$key] ?? null) ? $old[$key] : '';
$first = new DateTimeImmutable($month . '-01');
?>
<div class="row g-3">
    <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <div class="col-12 col-lg-9">
        <h1 class="h4">Takvim ve fiyat yönetimi</h1>
        <?php if ($rows === []): ?>
            <p>Önce bir ilan oluşturun.</p>
        <?php else: ?>
            <form method="get" action="<?= e($view->url('/panel/takvim')) ?>" class="row g-2 align-items-end mb-3">
                <div class="col-12 col-md-6">
                    <label for="ilan" class="form-label small fw-semibold mb-1">İlan</label>
                    <select class="form-select" id="ilan" name="ilan">
                        <?php foreach ($rows as $row): ?><option value="<?= (int) $row['property']->id ?>"<?= $view->selected($row['property']->id, $propertyId) ?>><?= e($row['property']->name . ' — ' . $row['property']->city) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label for="birim" class="form-label small fw-semibold mb-1">Oda / birim</label>
                    <select class="form-select" id="birim" name="birim">
                        <?php foreach ($units as $unit): ?><option value="<?= (int) $unit->id ?>"<?= $view->selected($unit->id, $unitId) ?>><?= e($unit->name) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="ay" value="<?= e($month) ?>">
                <div class="col-12 col-md-2"><button type="submit" class="btn btn-outline-secondary w-100">Göster</button></div>
            </form>

            <?php if ($calendar !== null): ?>
                <div class="row g-3">
                    <div class="col-12 col-xl-7">
                        <div class="card shadow-sm"><div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/panel/takvim', ['ilan' => $propertyId, 'birim' => $unitId, 'ay' => $first->modify('-1 month')->format('Y-m')])) ?>">‹ Önceki ay</a>
                                <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/panel/takvim', ['ilan' => $propertyId, 'birim' => $unitId, 'ay' => $first->modify('+1 month')->format('Y-m')])) ?>">Sonraki ay ›</a>
                            </div>
                            <!-- Kısmi yenileme: müşteri rezervasyon yapınca dolu geceler kendiliğinden görünür -->
                            <div id="panel-takvim"><?= $view->partial('property/_calendar', ['calendar' => $calendar, 'showPrices' => true]) ?></div>
                            <script>
                            async function panelTakvimYenile() {
                                const yanit = await fetch('<?= e($view->url('/parca/takvim', ['birim' => $unitId, 'ay' => $month, 'fiyat' => '1'])) ?>');
                                document.getElementById('panel-takvim').innerHTML = await yanit.text();
                            }
                            panelTakvimYenile();
                            setInterval(panelTakvimYenile, 5000);
                            </script>
                        </div></div>
                    </div>
                    <div class="col-12 col-xl-5">
                        <form method="post" action="<?= e($view->url('/panel/takvim')) ?>" class="card card-body shadow-sm" novalidate>
                            <h2 class="h6 fw-bold">Tarih aralığı için fiyat / müsaitlik tanımla</h2>
                            <?= $view->csrfField() ?>
                            <input type="hidden" name="ilan" value="<?= (int) $propertyId ?>">
                            <input type="hidden" name="birim" value="<?= (int) $unitId ?>">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label for="baslangic" class="form-label small mb-0">Başlangıç</label>
                                    <input type="date" class="form-control<?= $view->invalid($errors, 'baslangic') ?>" id="baslangic" name="baslangic" value="<?= e($val('baslangic')) ?>" required>
                                </div>
                                <div class="col-6">
                                    <label for="bitis" class="form-label small mb-0">Bitiş (dahil)</label>
                                    <input type="date" class="form-control<?= $view->invalid($errors, 'bitis') ?>" id="bitis" name="bitis" value="<?= e($val('bitis')) ?>" required>
                                </div>
                                <div class="col-6">
                                    <label for="fiyat" class="form-label small mb-0">Gecelik fiyat (₺, boş = normal)</label>
                                    <input type="number" step="0.01" min="0" class="form-control<?= $view->invalid($errors, 'fiyat') ?>" id="fiyat" name="fiyat" value="<?= e($val('fiyat')) ?>">
                                </div>
                                <div class="col-6">
                                    <label for="durum" class="form-label small mb-0">Durum</label>
                                    <select class="form-select" id="durum" name="durum">
                                        <option value="acik"<?= $view->selected($val('durum'), 'acik') ?>>Müsait</option>
                                        <option value="kapali"<?= $view->selected($val('durum'), 'kapali') ?>>Kapalı</option>
                                    </select>
                                </div>
                            </div>
                            <?php foreach (['baslangic', 'bitis', 'fiyat'] as $field): ?><?= $view->error($errors, $field) ?><?php endforeach; ?>
                            <button type="submit" class="btn btn-success mt-3">KAYDET</button>
                            <div class="form-text">Beklemede veya Onaylandı rezervasyonu olan geceler kapatılamaz. Özel fiyat girilen gecede sezon ve hafta sonu farkı uygulanmaz.</div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm mt-3"><div class="card-body">
                <h2 class="h6 fw-bold">Gelen rezervasyonlar</h2>
                <div id="takvim-rezervasyonlar">Yükleniyor...</div>
                <script>
                async function takvimRezervasyonYenile() {
                    const yanit = await fetch('<?= e($view->url('/parca/panel-rezervasyonlar', ['kisa' => '1'])) ?>');
                    document.getElementById('takvim-rezervasyonlar').innerHTML = await yanit.text();
                }
                takvimRezervasyonYenile();
                setInterval(takvimRezervasyonYenile, 5000);
                </script>
            </div></div>
        <?php endif; ?>
    </div>
</div>
