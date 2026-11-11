<?php
/**
 * FR-31: profil (ad, soyad) ve bonus şifre değiştirme. Rol ve e-posta değiştirilemez.
 *
 * @var App\Core\View $view
 * @var App\Model\User $currentUser
 * @var array<string, string> $errors
 * @var array<string, string> $passwordErrors
 */
?>
<h1 class="h4 mb-3">Profilim</h1>
<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-bold">Kişisel bilgiler</h2>
                <form method="post" action="<?= e($view->url('/profil')) ?>" novalidate>
                    <?= $view->csrfField() ?>
                    <div class="row g-2">
                        <div class="col-6">
                            <label for="ad" class="form-label">Ad</label>
                            <input type="text" class="form-control<?= $view->invalid($errors, 'ad') ?>" id="ad" name="ad" value="<?= e($currentUser->firstName) ?>" required>
                            <?= $view->error($errors, 'ad') ?>
                        </div>
                        <div class="col-6">
                            <label for="soyad" class="form-label">Soyad</label>
                            <input type="text" class="form-control<?= $view->invalid($errors, 'soyad') ?>" id="soyad" name="soyad" value="<?= e($currentUser->lastName) ?>" required>
                            <?= $view->error($errors, 'soyad') ?>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="eposta">E-posta (değiştirilemez)</label>
                            <input type="email" class="form-control" id="eposta" value="<?= e($currentUser->email) ?>" disabled>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="rol">Hesap türü (değiştirilemez)</label>
                            <input type="text" class="form-control" id="rol" value="<?= e($currentUser->role->label()) ?>" disabled>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-success mt-3">Kaydet</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-bold">Şifre değiştir</h2>
                <form method="post" action="<?= e($view->url('/profil/parola')) ?>" novalidate>
                    <?= $view->csrfField() ?>
                    <div class="mb-2">
                        <label for="mevcut" class="form-label">Mevcut parola</label>
                        <input type="password" class="form-control<?= $view->invalid($passwordErrors, 'mevcut') ?>" id="mevcut" name="mevcut" autocomplete="current-password" required>
                        <?= $view->error($passwordErrors, 'mevcut') ?>
                    </div>
                    <div class="mb-2">
                        <label for="yeni" class="form-label">Yeni parola (en az 8 karakter)</label>
                        <input type="password" class="form-control<?= $view->invalid($passwordErrors, 'yeni') ?>" id="yeni" name="yeni" minlength="8" autocomplete="new-password" required>
                        <?= $view->error($passwordErrors, 'yeni') ?>
                    </div>
                    <button type="submit" class="btn btn-outline-success mt-2">Parolayı değiştir</button>
                </form>
            </div>
        </div>
    </div>
</div>
