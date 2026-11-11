<?php
/**
 * Taslak 01: Ana sayfa / arama.
 *
 * @var App\Core\View $view
 * @var list<App\Service\Dto\SearchResultItem> $featured
 * @var array<string, string> $types
 */
?>
<section class="kahraman">
    <h1>Konaklamanızı bulun</h1>
    <p class="giris-metni">Oteller ve kiralık evler, tek yerde. Tarihlerinizi seçin, müsaitliği ve gecelik fiyatı hemen görün.</p>

    <form method="get" action="<?= e($view->url('/ara')) ?>" class="arama-paneli" novalidate>
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label for="konum" class="form-label">Konum</label>
                <input type="text" class="form-control" id="konum" name="konum" placeholder="Şehir veya bölge" autocomplete="off">
            </div>
            <div class="col-6 col-lg-2">
                <label for="giris" class="form-label">Giriş tarihi</label>
                <input type="date" class="form-control" id="giris" name="giris">
            </div>
            <div class="col-6 col-lg-2">
                <label for="cikis" class="form-label">Çıkış tarihi</label>
                <input type="date" class="form-control" id="cikis" name="cikis">
            </div>
            <div class="col-6 col-lg-1">
                <label for="kisi" class="form-label">Kişi</label>
                <input type="number" class="form-control" id="kisi" name="kisi" value="2" min="1" max="20">
            </div>
            <div class="col-6 col-lg-2">
                <label for="tur" class="form-label">Tür</label>
                <select class="form-select" id="tur" name="tur">
                    <option value="">Tümü</option>
                    <?php foreach ($types as $value => $label): ?>
                        <option value="<?= e($value) ?>"><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-lg-1 d-grid">
                <button type="submit" class="btn btn-primary">Ara</button>
            </div>
        </div>
    </form>
</section>

<section>
    <div class="d-flex justify-content-between align-items-baseline gap-3 mb-3">
        <h2 class="bolum-baslik">Öne çıkan konaklamalar</h2>
        <a href="<?= e($view->url('/ara')) ?>" class="small text-decoration-none">Tümünü gör →</a>
    </div>
    <div class="row g-4">
        <?php foreach ($featured as $item): ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <?= $view->partial('partials/property_card', ['item' => $item]) ?>
            </div>
        <?php endforeach; ?>
        <?php if ($featured === []): ?>
            <p class="text-body-secondary">Henüz yayında ilan yok.</p>
        <?php endif; ?>
    </div>
</section>
