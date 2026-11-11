<?php
/**
 * Geliştirme: simüle posta kutusu (FileMailer'ın storage/mail altına yazdığı iletiler).
 *
 * @var App\Core\View $view
 * @var list<App\Service\Dto\MailMessage> $messages
 */
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h3 mb-0">Posta kutusu</h1>
    <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/posta-kutusu')) ?>">↻ Yenile</a>
</div>
<div class="alert alert-warning small">
    <strong>Geliştirme ortamı.</strong> Bu prototipte e-posta gerçek adrese gitmez; uygulamanın gönderdiği iletiler (ör. parola sıfırlama) burada görünür.
    Sayfa yalnızca bu bilgisayardan açılır. Gerçek e-posta için <code>config/yerel.php</code> ile SMTP ayarlayın (README);
    bir sunucuya kurulursa <code>EVOTEL_POSTA_KUTUSU=0</code> ile kapatın.
</div>
<?php if ($messages === []): ?>
    <p class="text-body-secondary">Henüz ileti yok. <a href="<?= e($view->url('/parola-sifirla')) ?>">Parola sıfırlama</a> isteyince ileti burada görünür.</p>
<?php else: ?>
    <div class="list-group shadow-sm">
        <?php foreach ($messages as $message): ?>
            <a class="list-group-item list-group-item-action" href="<?= e($view->url('/posta-kutusu/' . $message->id)) ?>">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <strong><?= e($message->subject) ?></strong>
                    <small class="text-body-secondary"><?= $view->date($message->sentAt, 'd.m.Y H:i:s') ?></small>
                </div>
                <small class="text-body-secondary">Kime: <?= e($message->to) ?></small>
            </a>
        <?php endforeach; ?>
    </div>
    <p class="small text-body-secondary mt-2">En yeni <?= count($messages) ?> ileti gösteriliyor.</p>
<?php endif; ?>
