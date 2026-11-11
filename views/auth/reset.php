<?php
/**
 * FR-04: yeni parola belirleme.
 *
 * @var App\Core\View $view
 * @var string $token
 * @var array<string, string> $errors
 */
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4">Yeni parola</h1>
                <?php if (isset($errors['jeton'])): ?>
                    <div class="alert alert-danger"><?= e($errors['jeton']) ?> <a href="<?= e($view->url('/parola-sifirla')) ?>">Yeniden talep et</a></div>
                <?php endif; ?>
                <form method="post" action="<?= e($view->url('/parola-sifirla/' . $token)) ?>" novalidate>
                    <?= $view->csrfField() ?>
                    <div class="mb-3">
                        <label for="parola" class="form-label fw-semibold">Yeni parola (en az 8 karakter)</label>
                        <input type="password" class="form-control<?= $view->invalid($errors, 'parola') ?>" id="parola" name="parola" minlength="8" autocomplete="new-password" required>
                        <?= $view->error($errors, 'parola') ?>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Parolayı güncelle</button>
                </form>
            </div>
        </div>
    </div>
</div>
