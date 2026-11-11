<?php
/**
 * Taslak 04: Rezervasyon özeti ve onay (FR-13, FR-14, BR-06, BR-07, AC-08).
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\ReservationPreview $preview
 * @var string $key idempotency anahtarı (FR-35)
 * @var array<string, string> $errors
 * @var array<string, string> $old
 * @var App\Exception\ConflictException|null $conflict
 * @var string|null $notice
 */
use App\Model\BookingMode;
use App\Support\Decimal;

$p = $preview->property;
$u = $preview->unit;
$quote = $preview->quote;
$range = $preview->range;
?>
<h1 class="h3 mb-3">Rezervasyonu onayla</h1>

<?php if ($conflict !== null): ?>
    <div class="alert alert-danger" role="alert">
        <strong>❗ <?= e($conflict->getMessage()) ?></strong> Bu tarihler siz onay ekranındayken başka bir rezervasyonla doldu; hiçbir kayıt oluşturulmadı.
        <?php if ($conflict->alternatives() !== []): ?>
            <div class="mt-2">Aynı gece sayısında yakın müsait tarihler:</div>
            <ul class="mb-0">
                <?php foreach ($conflict->alternatives() as $alt): ?>
                    <li><a href="<?= e($view->url('/rezervasyon/onizleme', ['birim' => $u->id, 'giris' => $alt->checkIn->format('Y-m-d'), 'cikis' => $alt->checkOut->format('Y-m-d'), 'kisi' => $preview->guests])) ?>"><?= e((string) $alt) ?></a></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="mt-2">Yakın tarihlerde müsait aralık bulunamadı; lütfen başka tarih veya oda seçin.</div>
        <?php endif; ?>
    </div>
<?php elseif (!$preview->available): ?>
    <div class="alert alert-warning">Seçtiğiniz tarihler şu anda müsait görünmüyor.
        <?php foreach ($preview->alternatives as $alt): ?>
            <a class="ms-2" href="<?= e($view->url('/rezervasyon/onizleme', ['birim' => $u->id, 'giris' => $alt->checkIn->format('Y-m-d'), 'cikis' => $alt->checkOut->format('Y-m-d'), 'kisi' => $preview->guests])) ?>"><?= e((string) $alt) ?></a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($notice !== null): ?>
    <div class="alert alert-warning">⚠️ <?= e($notice) ?></div>
<?php endif; ?>
<?php if (isset($errors['form'])): ?>
    <div class="alert alert-danger"><?= e($errors['form']) ?></div>
<?php endif; ?>

<form method="post" action="<?= e($view->url('/rezervasyon')) ?>" novalidate>
    <?= $view->csrfField() ?>
    <input type="hidden" name="anahtar" value="<?= e($key) ?>">
    <input type="hidden" name="birim" value="<?= (int) $u->id ?>">
    <input type="hidden" name="giris" value="<?= e($range->checkIn->format('Y-m-d')) ?>">
    <input type="hidden" name="cikis" value="<?= e($range->checkOut->format('Y-m-d')) ?>">
    <input type="hidden" name="kisi" value="<?= (int) $preview->guests ?>">
    <input type="hidden" name="beklenen_toplam" value="<?= e(Decimal::fromInt($quote->total)) ?>">

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h5"><?= e($p->name) ?> — <?= e($p->city) ?></h2>
                    <p class="mb-1">Giriş: <strong><?= $view->date($range->checkIn) ?></strong> · Çıkış: <strong><?= $view->date($range->checkOut) ?></strong> (<?= (int) $range->nights() ?> gece)</p>
                    <p class="mb-1">Kişi sayısı: <?= (int) $preview->guests ?> · Oda: <?= e($u->name . ' (' . $u->roomType->label() . ')') ?> · Tür: <?= e($p->type->label()) ?></p>
                    <p class="small text-body-secondary mb-2">Ev sahibi: <?= e($preview->host !== null ? $preview->host->firstName . ' ' . mb_substr($preview->host->lastName, 0, 1) . '.' : '-') ?> · Onay tipi: <?= e($p->bookingMode->label()) ?></p>
                    <p class="fw-semibold mb-0">İptal koşulu: <?= e($preview->policyText) ?></p>
                </div>
            </div>

            <h2 class="h6 fw-bold">İletişim bilgileri</h2>
            <div class="row g-2">
                <div class="col-12 col-md-6">
                    <label for="iletisim_ad" class="form-label">Ad Soyad</label>
                    <input type="text" class="form-control<?= $view->invalid($errors, 'iletisim_ad') ?>" id="iletisim_ad" name="iletisim_ad" value="<?= e($old['iletisim_ad'] ?? '') ?>" autocomplete="name" required>
                    <?= $view->error($errors, 'iletisim_ad') ?>
                </div>
                <div class="col-12 col-md-6">
                    <label for="iletisim_telefon" class="form-label">Telefon</label>
                    <input type="tel" class="form-control<?= $view->invalid($errors, 'iletisim_telefon') ?>" id="iletisim_telefon" name="iletisim_telefon" value="<?= e($old['iletisim_telefon'] ?? '') ?>" placeholder="0532 123 45 67" autocomplete="tel" required>
                    <?= $view->error($errors, 'iletisim_telefon') ?>
                </div>
                <div class="col-12">
                    <label for="not" class="form-label">Not (isteğe bağlı)</label>
                    <input type="text" class="form-control<?= $view->invalid($errors, 'not') ?>" id="not" name="not" maxlength="500" value="<?= e($old['not'] ?? '') ?>">
                    <?= $view->error($errors, 'not') ?>
                </div>
            </div>
            <a class="btn btn-outline-secondary mt-3" href="<?= e($view->url('/ilan/' . $p->id, ['birim' => $u->id, 'giris' => $range->checkIn->format('Y-m-d'), 'cikis' => $range->checkOut->format('Y-m-d'), 'kisi' => $preview->guests])) ?>">‹ Geri dön</a>
        </div>

        <div class="col-12 col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-bold">ÜCRET DÖKÜMÜ</h2>
                    <table class="table table-sm dokum">
                        <tbody>
                        <?php foreach ($quote->nights as $night): ?>
                            <tr><td class="small"><?= e($night->description()) ?></td><td><?= $view->money($night->price) ?></td></tr>
                        <?php endforeach; ?>
                        <tr class="fw-semibold"><td><?= (int) $quote->nightCount() ?> gece konaklama</td><td><?= $view->money($quote->stayTotal) ?></td></tr>
                        <?php if ($quote->discount !== null): ?>
                            <tr class="text-success"><td><?= e($quote->discount->description) ?></td><td><?= $view->money($quote->discount->amount) ?></td></tr>
                        <?php endif; ?>
                        <?php foreach ($quote->extras as $line): ?>
                            <tr><td><?= e($line->description) ?></td><td><?= $view->money($line->amount) ?></td></tr>
                        <?php endforeach; ?>
                        <tr class="table-light fs-5 fw-bold"><td>TOPLAM</td><td><?= $view->money($quote->total) ?></td></tr>
                        </tbody>
                    </table>

                    <p class="small text-body-secondary mb-2">Ödeme simüledir; kart bilgisi istenmez ve saklanmaz.
                        <?= $p->bookingMode === BookingMode::Aninda ? 'Başarılı ödemeyle rezervasyon hemen onaylanır.' : 'Ödeme alındıktan sonra ilan sahibinin onayı beklenir (24 saat).' ?></p>
                    <div class="mb-2">
                        <label for="odeme_sonucu" class="form-label small fw-semibold">Simüle ödeme sonucu (test)</label>
                        <select class="form-select form-select-sm" id="odeme_sonucu" name="odeme_sonucu">
                            <option value="basarili">Başarılı</option>
                            <option value="basarisiz">Başarısız (yeniden deneme senaryosu)</option>
                        </select>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input<?= $view->invalid($errors, 'kosullar') ?>" type="checkbox" value="1" id="kosullar" name="kosullar" required>
                        <label class="form-check-label" for="kosullar">İptal koşullarını okudum</label>
                        <?= $view->error($errors, 'kosullar') ?>
                    </div>
                    <button type="submit" class="btn btn-success w-100 fw-semibold py-2" data-tek-tik>ONAYLA VE REZERVE ET</button>
                    <p class="small text-body-secondary mt-2 mb-0">Onay anında müsaitlik ve fiyat yeniden doğrulanır; geceler ancak o zaman sizin için tutulur.</p>
                </div>
            </div>
        </div>
    </div>
</form>
