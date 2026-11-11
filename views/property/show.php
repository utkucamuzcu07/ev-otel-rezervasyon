<?php
/**
 * Taslak 03: İlan detayı + aylık müsaitlik takvimi + rezervasyon alanı (FR-09, FR-10, FR-44).
 * Düzen (Lovable prototipi): galeri (1 büyük + 2 küçük), solda bölümler, sağda sabit rezervasyon paneli.
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\PropertyDetail $detail
 * @var int $unitId
 * @var array{0: int, 1: int} $month
 * @var array{giris: string, cikis: string, kisi: string} $form
 * @var App\Model\User|null $currentUser
 */
use App\Model\BookingMode;

$p = $detail->property;
$selectedUnit = null;
foreach ($detail->units as $unit) {
    if ($unit->id === $unitId) {
        $selectedUnit = $unit;
    }
}
$selectedUnit ??= $detail->units[0] ?? null;
$ay = sprintf('%04d-%02d', $month[0], $month[1]);
$first = new DateTimeImmutable($ay . '-01');
$prevMonth = $first->modify('-1 month')->format('Y-m');
$nextMonth = $first->modify('+1 month')->format('Y-m');
$keep = ['birim' => $selectedUnit?->id, 'giris' => $form['giris'], 'cikis' => $form['cikis'], 'kisi' => $form['kisi']];
$gallery = array_slice($detail->gallery, 0, 3);
$maxBar = max(1, ...array_values($detail->ratingDistribution));
?>
<a href="<?= e($view->url('/ara')) ?>" class="small text-decoration-none">← Sonuçlara dön</a>

<?php if ($gallery !== []): ?>
    <div class="galeri<?= count($gallery) === 3 ? ' uclu' : '' ?> mt-3">
        <?php foreach ($gallery as $i => $photo): ?>
            <div><img src="<?= e($view->url('/foto/' . $photo->id)) ?>" alt="<?= e($p->name) ?><?= $i > 0 ? ' · fotoğraf ' . ($i + 1) : '' ?>"<?= $i > 0 ? ' loading="lazy"' : '' ?>></div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="galeri mt-3"><div class="emoji-foto" role="img" aria-label="<?= e($p->name) ?>"><?= e($p->type->emoji()) ?></div></div>
<?php endif; ?>

<div class="row g-4 g-lg-5">
    <div class="col-12 col-lg-8">
        <header class="detay-ust">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div class="d-flex flex-wrap gap-2">
                    <span class="rozet"><?= e($p->type->label()) ?></span>
                    <?php if (!$p->isBookable()): ?><span class="rozet rozet-uyari">İlan durumu: <?= e($p->status->label()) ?></span><?php endif; ?>
                </div>
                <?php if ($currentUser !== null): ?>
                    <?= $view->partial('partials/favorite_button', ['propertyId' => (int) $p->id, 'isFavorite' => $detail->isFavorite]) ?>
                <?php endif; ?>
            </div>
            <h1><?= e($p->name) ?></h1>
            <p class="meta"><?= e($p->city) ?> · <?= $view->stars($detail->rating, $detail->reviewCount) ?></p>
        </header>

        <section class="detay-bolum">
            <h2>Açıklama</h2>
            <p class="aciklama mb-0"><?= nl2br(e($p->description)) ?></p>
        </section>

        <section class="detay-bolum">
            <h2>Olanaklar</h2>
            <?php if ($detail->amenities === []): ?>
                <p class="text-body-secondary mb-0">Belirtilmemiş.</p>
            <?php else: ?>
                <ul class="olanaklar">
                    <?php foreach ($detail->amenities as $amenity): ?><li><?= e($amenity->name) ?></li><?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section class="detay-bolum">
            <h2>Odalar ve birimler</h2>
            <div class="tablo-panel">
                <table class="table align-middle">
                    <thead><tr><th>Oda</th><th>Tip</th><th>Kapasite</th><th class="text-end">Temel gecelik</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($detail->units as $unit): ?>
                        <tr class="<?= $unit->id === $selectedUnit?->id ? 'table-success' : '' ?>">
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (isset($detail->unitPhotos[(int) $unit->id])): ?>
                                        <img src="<?= e($view->url('/foto/' . $detail->unitPhotos[(int) $unit->id])) ?>" class="oda-foto" alt="<?= e($unit->name) ?>" loading="lazy">
                                    <?php endif; ?>
                                    <span><?= e($unit->name) ?></span>
                                </div>
                            </td>
                            <td><?= e($unit->roomType->label()) ?></td>
                            <td><?= (int) $unit->capacity ?> kişi</td>
                            <td class="text-end"><?= $view->money($unit->basePrice) ?></td>
                            <td class="text-end"><a class="btn btn-outline-primary btn-sm" href="<?= e($view->url('/ilan/' . $p->id, ['birim' => $unit->id] + $keep + ['ay' => $ay])) ?>">Seç</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php if ($selectedUnit !== null): ?>
            <section class="detay-bolum">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <h2 class="mb-0">Müsaitlik takvimi</h2>
                    <span class="d-flex align-items-center gap-1">
                        <span class="small text-body-secondary me-1"><?= e($selectedUnit->name) ?></span>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/ilan/' . $p->id, ['ay' => $prevMonth] + $keep)) ?>" aria-label="Önceki ay">‹</a>
                        <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/ilan/' . $p->id, ['ay' => $nextMonth] + $keep)) ?>" aria-label="Sonraki ay">›</a>
                    </span>
                </div>
                <!-- Kısmi yenileme: takvim bölümü 5 saniyede bir PHP'den yeniden alınır -->
                <div id="takvim-bolumu" class="panel p-3">Yükleniyor...</div>
                <script>
                async function takvimYenile() {
                    const yanit = await fetch('<?= e($view->url('/parca/takvim', ['birim' => $selectedUnit->id, 'ay' => $ay])) ?>');
                    document.getElementById('takvim-bolumu').innerHTML = await yanit.text();
                }
                takvimYenile();
                setInterval(takvimYenile, 5000);
                </script>
            </section>
        <?php endif; ?>

        <section class="detay-bolum">
            <h2>Rezervasyon ve iptal koşulları</h2>
            <dl class="kosullar">
                <div>
                    <dt>Rezervasyon modu: <?= e($p->bookingMode->label()) ?></dt>
                    <dd><?= $p->bookingMode === BookingMode::Aninda ? 'Ödeme ile hemen kesinleşir; ödeme için 10 dakika bekletilir.' : 'İlan sahibi talebi 24 saat içinde onaylar.' ?></dd>
                </div>
                <div>
                    <dt>İptal politikası: <?= e($p->cancellationPolicy->label()) ?></dt>
                    <dd><?= e($detail->policyText) ?></dd>
                </div>
            </dl>
        </section>

        <section class="detay-bolum">
            <h2>Konum</h2>
            <p class="mb-1"><?= e($p->address) ?>, <?= e($p->city) ?></p>
            <p class="small text-body-secondary mb-0">İlk sürümde harita yerine adres metni gösterilir.<?php if ($detail->host !== null): ?>
                Ev sahibi: <?= e($detail->host->firstName . ' ' . mb_substr($detail->host->lastName, 0, 1) . '.') ?><?php endif; ?></p>
        </section>

        <section class="detay-bolum" id="yorumlar">
            <h2>Yorumlar</h2>
            <?php if ($detail->reviews === []): ?>
                <p class="text-body-secondary mb-0">Henüz yorum yok.</p>
            <?php else: ?>
                <div class="puan-ozeti">
                    <div>
                        <p class="puan-buyuk"><?= e(number_format((float) $detail->rating, 1, ',', '')) ?></p>
                        <p class="small text-body-secondary mb-0"><?= (int) $detail->reviewCount ?> değerlendirme</p>
                    </div>
                    <div>
                        <?php foreach ($detail->ratingDistribution as $stars => $count): ?>
                            <div class="puan-satiri">
                                <span class="etiket"><?= (int) $stars ?> ★</span>
                                <span class="cubuk" role="img" aria-label="<?= (int) $stars ?> yıldız: <?= (int) $count ?> yorum"><span style="width: <?= (int) round($count / $maxBar * 100) ?>%"></span></span>
                                <span class="adet"><?= (int) $count ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php foreach ($detail->reviews as $review): ?>
                    <article class="yorum">
                        <div class="yorum-ust"><strong><?= e($review['author']) ?></strong><span class="text-body-secondary"><?= $view->date($review['date']) ?></span></div>
                        <p class="yorum-yildiz" aria-label="<?= (int) $review['rating'] ?> / 5 puan"><?= str_repeat('★', $review['rating']) ?><span class="bos"><?= str_repeat('★', 5 - $review['rating']) ?></span></p>
                        <p class="yorum-metni"><?= e($review['comment']) ?></p>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>

    <div class="col-12 col-lg-4">
        <aside class="rezervasyon-paneli sabit-panel">
            <?php if ($selectedUnit === null): ?>
                <p class="text-body-secondary mb-0">Bu ilanda rezervasyona açık oda yok.</p>
            <?php else: ?>
                <p class="mb-3"><span class="fiyat"><?= $view->money($selectedUnit->basePrice) ?></span> <span class="small text-body-secondary">/ gece'den</span></p>
                <form method="get" action="<?= e($view->url('/ilan/' . $p->id)) ?>" class="mb-3">
                    <label for="birim-sec" class="form-label">Oda / birim</label>
                    <div class="input-group">
                    <select id="birim-sec" name="birim" class="form-select">
                        <?php foreach ($detail->units as $unit): ?>
                            <option value="<?= (int) $unit->id ?>"<?= $view->selected($unit->id, $selectedUnit->id) ?>><?= e($unit->name . ' · ' . $unit->roomType->label() . ' · ' . $unit->capacity . ' kişi') ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-outline-secondary">Göster</button>
                    </div>
                    <input type="hidden" name="giris" value="<?= e($form['giris']) ?>">
                    <input type="hidden" name="cikis" value="<?= e($form['cikis']) ?>">
                    <input type="hidden" name="kisi" value="<?= e($form['kisi']) ?>">
                    <input type="hidden" name="ay" value="<?= e($ay) ?>">
                </form>

                <form method="get" action="<?= e($view->url('/rezervasyon/onizleme')) ?>" id="rezervasyon-formu" novalidate>
                    <input type="hidden" name="birim" value="<?= (int) $selectedUnit->id ?>">
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="giris" class="form-label">Giriş</label>
                            <input type="date" class="form-control" id="giris" name="giris" value="<?= e($form['giris']) ?>" required>
                        </div>
                        <div class="col-6">
                            <label for="cikis" class="form-label">Çıkış</label>
                            <input type="date" class="form-control" id="cikis" name="cikis" value="<?= e($form['cikis']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label for="kisi" class="form-label">Kişi sayısı</label>
                            <input type="number" class="form-control" id="kisi" name="kisi" min="1" max="<?= (int) $selectedUnit->capacity ?>" value="<?= e($form['kisi']) ?>" required>
                            <div class="form-text">Bu oda en çok <?= (int) $selectedUnit->capacity ?> kişiliktir.</div>
                        </div>
                    </div>
                    <div id="fiyat-onizleme" class="my-3"></div>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary" id="fiyat-goster">Müsaitlik ve fiyatı gör</button>
                        <button type="submit" class="btn btn-primary btn-lg">Rezervasyon yap</button>
                    </div>
                    <?php if ($currentUser === null): ?>
                        <p class="small text-body-secondary mt-2 mb-0">Giriş yapmadıysanız giriş sayfasına yönlendirilirsiniz; seçimleriniz korunur.</p>
                    <?php endif; ?>
                </form>
                <script>
                // Etkileşim standardı: JSON döndüren AJAX uç noktası (/api/musaitlik). Form sayfa yenilenmeden gönderilir.
                // Gelen metinler textContent ile yazılır; HTML olarak yorumlanmaz (XSS koruması).
                const ILAN_ADRESI = '<?= e($view->url('/ilan/' . $p->id)) ?>';
                function oge(etiket, sinif, metin) {
                    const el = document.createElement(etiket);
                    if (sinif) { el.className = sinif; }
                    if (metin !== undefined) { el.textContent = metin; }
                    return el;
                }
                function satir(govde, sol, sag, sinif) {
                    const tr = oge('tr', sinif || '');
                    tr.append(oge('td', 'small', sol), oge('td', '', sag));
                    govde.append(tr);
                }
                async function fiyatGetir() {
                    const alan = document.getElementById('fiyat-onizleme');
                    const parametreler = new URLSearchParams(new FormData(document.getElementById('rezervasyon-formu')));
                    const yanit = await fetch('<?= e($view->url('/api/musaitlik')) ?>?' + parametreler.toString(), {headers: {'Accept': 'application/json'}});
                    const veri = await yanit.json();
                    alan.replaceChildren();
                    if (!veri.basarili) {
                        const kutu = oge('div', 'alert alert-warning py-2 mb-0');
                        Object.values(veri.hatalar || {hata: veri.hata}).forEach(m => kutu.append(oge('div', '', m)));
                        alan.append(kutu);
                        return;
                    }
                    const durum = oge('div', 'alert py-2 mb-2 ' + (veri.musait ? 'alert-success' : 'alert-danger'),
                        veri.musait ? 'Bu tarihler şu an müsait. Kesin karar onay anında verilir.' : 'Seçtiğiniz tarihler müsait değil.');
                    if (!veri.musait && veri.alternatifler.length > 0) {
                        const liste = oge('ul', 'small mb-0 mt-1');
                        veri.alternatifler.forEach(function (alt) {
                            const q = new URLSearchParams({birim: veri.birim.id, giris: alt.giris, cikis: alt.cikis, kisi: veri.kisi});
                            const a = oge('a', '', alt.metin);
                            a.href = ILAN_ADRESI + '?' + q.toString();
                            const li = oge('li');
                            li.append(a);
                            liste.append(li);
                        });
                        durum.append(oge('div', 'small mt-1', 'Yakın müsait tarihler:'), liste);
                    }
                    const tablo = oge('table', 'table table-sm dokum mb-1');
                    const govde = oge('tbody');
                    veri.geceler.forEach(g => satir(govde, g.aciklama, g.metin));
                    satir(govde, veri.gece + ' gece konaklama', veri.konaklama_toplami.metin, 'fw-semibold');
                    if (veri.indirim) { satir(govde, veri.indirim.aciklama, veri.indirim.metin, 'text-success'); }
                    veri.ek_kalemler.forEach(k => satir(govde, k.aciklama, k.metin));
                    satir(govde, 'Toplam', veri.toplam.metin, 'table-light fw-bold');
                    tablo.append(govde);
                    alan.append(durum, tablo, oge('p', 'small text-body-secondary mb-0', veri.iptal_kosulu));
                }
                document.getElementById('fiyat-goster').addEventListener('click', fiyatGetir);
                <?php if ($form['giris'] !== '' && $form['cikis'] !== ''): ?>fiyatGetir();<?php endif; ?>
                </script>
            <?php endif; ?>
            <?php if ($currentUser !== null): ?>
                <p class="text-center small mt-3 mb-0"><a class="text-body-secondary" href="<?= e($view->url('/sikayet', ['ilan' => $p->id])) ?>">Bu ilanı şikâyet et</a></p>
            <?php endif; ?>
        </aside>
    </div>
</div>
