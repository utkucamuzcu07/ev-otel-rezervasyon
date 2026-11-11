<?php
/**
 * Taslak 05: Giriş / Kayıt (FR-01, FR-02).
 *
 * @var App\Core\View $view
 * @var string $returnTo
 * @var array<string, string> $loginErrors
 * @var array<string, string> $registerErrors
 * @var array<string, string> $old
 */
$role = $old['rol'] ?? 'musteri';
?>
<div class="row g-4 justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <h1 class="h3 mb-4">Giriş yap</h1>
                <form method="post" action="<?= e($view->url('/giris')) ?>" novalidate>
                    <?= $view->csrfField() ?>
                    <input type="hidden" name="donus" value="<?= e($returnTo) ?>">
                    <div class="mb-3">
                        <label for="giris-eposta" class="form-label fw-semibold">E-posta</label>
                        <input type="email" class="form-control<?= $view->invalid($loginErrors, 'giris') ?>" id="giris-eposta" name="eposta" placeholder="ornek@mail.com" value="<?= e($old['giris_eposta'] ?? '') ?>" autocomplete="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="giris-parola" class="form-label fw-semibold">Parola</label>
                        <input type="password" class="form-control<?= $view->invalid($loginErrors, 'giris') ?>" id="giris-parola" name="parola" autocomplete="current-password" required>
                        <?= $view->error($loginErrors, 'giris') ?>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Giriş yap</button>
                </form>
                <p class="small mt-3 mb-1"><a href="<?= e($view->url('/parola-sifirla')) ?>">Parolamı unuttum</a> → sıfırlama bağlantısı</p>
                <?php if ($mailInbox ?? false): ?>
                    <p class="small mb-1 text-body-secondary">Prototip: gönderilen e-postalar <a href="<?= e($view->url('/posta-kutusu')) ?>">Posta kutusu</a> sayfasında görünür.</p>
                <?php endif; ?>
                <p class="small mb-0">Hesabın yok mu? <a href="#kayit">Kayıt ol →</a></p>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-lg-5" id="kayit">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <h2 class="h3 mb-4">Kayıt ol</h2>
                <form method="post" action="<?= e($view->url('/kayit')) ?>" novalidate>
                    <?= $view->csrfField() ?>
                    <input type="hidden" name="donus" value="<?= e($returnTo) ?>">
                    <div class="row g-2">
                        <div class="col-6 mb-2">
                            <label for="ad" class="form-label fw-semibold">Ad</label>
                            <input type="text" class="form-control<?= $view->invalid($registerErrors, 'ad') ?>" id="ad" name="ad" value="<?= e($old['ad'] ?? '') ?>" autocomplete="given-name" required>
                            <?= $view->error($registerErrors, 'ad') ?>
                        </div>
                        <div class="col-6 mb-2">
                            <label for="soyad" class="form-label fw-semibold">Soyad</label>
                            <input type="text" class="form-control<?= $view->invalid($registerErrors, 'soyad') ?>" id="soyad" name="soyad" value="<?= e($old['soyad'] ?? '') ?>" autocomplete="family-name" required>
                            <?= $view->error($registerErrors, 'soyad') ?>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="kayit-eposta" class="form-label fw-semibold">E-posta</label>
                        <input type="email" class="form-control<?= $view->invalid($registerErrors, 'eposta') ?>" id="kayit-eposta" name="eposta" value="<?= e($old['eposta'] ?? '') ?>" autocomplete="email" required>
                        <?= $view->error($registerErrors, 'eposta') ?>
                    </div>
                    <div class="mb-2">
                        <label for="kayit-parola" class="form-label fw-semibold">Parola (en az 8 karakter)</label>
                        <input type="password" class="form-control<?= $view->invalid($registerErrors, 'parola') ?>" id="kayit-parola" name="parola" minlength="8" autocomplete="new-password" required>
                        <?= $view->error($registerErrors, 'parola') ?>
                    </div>
                    <fieldset class="mb-3">
                        <legend class="form-label fw-semibold fs-6">Hesap türü</legend>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rol" id="rol-musteri" value="musteri"<?= $view->checked($role !== 'ilan_sahibi') ?>>
                            <label class="form-check-label" for="rol-musteri">Müşteri</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="rol" id="rol-sahip" value="ilan_sahibi"<?= $view->checked($role === 'ilan_sahibi') ?>>
                            <label class="form-check-label" for="rol-sahip">İlan sahibi</label>
                        </div>
                        <?= $view->error($registerErrors, 'rol') ?>
                    </fieldset>
                    <button type="submit" class="btn btn-primary w-100">Hesap oluştur</button>
                </form>
            </div>
        </div>
    </div>
</div>
