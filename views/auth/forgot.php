<?php
/**
 * FR-04: parola sıfırlama talebi.
 *
 * @var App\Core\View $view
 * @var array<string, string> $errors
 * @var string $mailMode 'dosya' (simüle) veya 'smtp'
 * @var bool $mailInbox simüle posta kutusu bu bilgisayardan açılabilir mi
 */
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4">Parola sıfırlama</h1>
                <p class="small text-body-secondary">E-posta adresinizi girin. Bu adresle kayıtlı bir hesap varsa 60 dakika geçerli, tek kullanımlık bir sıfırlama bağlantısı gönderilir.
                    Güvenlik nedeniyle adresin kayıtlı olup olmadığı gösterilmez; her adres için aynı yanıt verilir.</p>
                <?php if (($mailMode ?? 'dosya') === 'smtp'): ?>
                    <p class="small text-body-secondary">Bağlantı birkaç dakika içinde gelmezse istenmeyen (spam) klasörünü de kontrol edin.</p>
                <?php elseif ($mailInbox ?? false): ?>
                    <div class="alert alert-info small py-2">Bu prototipte e-posta gerçek adrese gitmez: gönderilen iletiler
                        <a href="<?= e($view->url('/posta-kutusu')) ?>" class="alert-link">Posta kutusu</a> sayfasında görünür.
                        Kendi adresinizle denemek için önce o adresle kayıt olun.</div>
                <?php else: ?>
                    <div class="alert alert-info small py-2">Bu prototipte e-posta gerçek adrese gitmez: ileti sunucudaki <code>storage/mail</code> klasörüne metin dosyası olarak yazılır.
                        Kendi adresinizle denemek için önce o adresle kayıt olun.</div>
                <?php endif; ?>
                <form method="post" action="<?= e($view->url('/parola-sifirla')) ?>" novalidate>
                    <?= $view->csrfField() ?>
                    <div class="mb-3">
                        <label for="eposta" class="form-label fw-semibold">E-posta</label>
                        <input type="email" class="form-control<?= $view->invalid($errors, 'eposta') ?>" id="eposta" name="eposta" required>
                        <?= $view->error($errors, 'eposta') ?>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Bağlantı gönder</button>
                </form>
            </div>
        </div>
    </div>
</div>
