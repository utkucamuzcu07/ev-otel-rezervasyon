<?php
/**
 * FR-27: ilan veya rezervasyon hakkında şikâyet. Yanında şikâyetin nereye gittiği adım adım anlatılır.
 *
 * @var App\Core\View $view
 * @var array<string, string> $errors
 * @var array<string, string> $old
 * @var array<int, string> $properties
 */
?>
<div class="sayfa-baslik">
    <h1>Şikâyet oluştur</h1>
    <p>Bir ilan veya rezervasyonla ilgili sorun yaşadıysanız yönetim ekibine bildirin.</p>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <form method="post" action="<?= e($view->url('/sikayet')) ?>" class="panel p-4" novalidate>
            <?= $view->csrfField() ?>
            <div class="mb-3">
                <label for="ilan" class="form-label">İlan</label>
                <select id="ilan" name="ilan" class="form-select<?= $view->invalid($errors, 'ilan') ?>">
                    <option value="">İlan seçin (rezervasyon numarası yazarsanız gerekmez)</option>
                    <?php foreach ($properties as $id => $label): ?>
                        <option value="<?= (int) $id ?>"<?= $view->selected($old['ilan'] ?? '', $id) ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?= $view->error($errors, 'ilan') ?>
            </div>
            <div class="mb-3">
                <label for="rezervasyon" class="form-label">Rezervasyon numarası (isteğe bağlı)</label>
                <input type="text" class="form-control<?= $view->invalid($errors, 'rezervasyon') ?>" id="rezervasyon" name="rezervasyon" placeholder="RZ-20261107-AB12CD" value="<?= e($old['rezervasyon'] ?? '') ?>">
                <?= $view->error($errors, 'rezervasyon') ?>
            </div>
            <div class="mb-4">
                <label for="aciklama" class="form-label">Açıklama</label>
                <textarea class="form-control<?= $view->invalid($errors, 'aciklama') ?>" id="aciklama" name="aciklama" rows="6" minlength="10" maxlength="2000" placeholder="Yaşadığınız sorunu kısaca anlatın." required><?= e($old['aciklama'] ?? '') ?></textarea>
                <?= $view->error($errors, 'aciklama') ?>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-3">
                <button type="submit" class="btn btn-primary">Şikâyeti gönder</button>
                <a href="<?= e($view->url('/sikayetlerim')) ?>" class="small">Şikâyetlerim</a>
            </div>
        </form>
    </div>
    <div class="col-12 col-lg-4">
        <aside class="panel p-4">
            <h2 class="h5 mb-3">Şikâyetiniz nereye gider?</h2>
            <ol class="adimlar">
                <li>
                    <span class="adim-no">1</span>
                    <div>
                        <p class="fw-medium mb-0">Şikâyetler kutusuna düşer</p>
                        <p class="small text-body-secondary mb-0">Şikâyetiniz bir numarayla yönetim ekibinin Şikâyetler kutusuna (Yönetim → Şikâyetler) kaydedilir; yöneticilere bildirim gider.</p>
                    </div>
                </li>
                <li>
                    <span class="adim-no">2</span>
                    <div>
                        <p class="fw-medium mb-0">Yönetici inceler</p>
                        <p class="small text-body-secondary mb-0">Yönetici ilanı ve rezervasyonu inceler; şikâyetin durumu "İnceleniyor" olur.</p>
                    </div>
                </li>
                <li>
                    <span class="adim-no">3</span>
                    <div>
                        <p class="fw-medium mb-0">Sonuç size bildirilir</p>
                        <p class="small text-body-secondary mb-0">Sonuç (Çözüldü veya Reddedildi) ve yönetici notu size bildirim olarak gelir; Şikâyetlerim sayfasından da izlersiniz.</p>
                    </div>
                </li>
            </ol>
        </aside>
    </div>
</div>
