<?php
/**
 * Geliştirme: simüle posta kutusundaki tek ileti; gövdedeki bağlantılar tıklanabilir.
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\MailMessage $message
 */
?>
<p><a href="<?= e($view->url('/posta-kutusu')) ?>">← Posta kutusu</a></p>
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4"><?= e($message->subject) ?></h1>
        <dl class="row small mb-3">
            <dt class="col-sm-2">Kime</dt>
            <dd class="col-sm-10"><?= e($message->to) ?></dd>
            <dt class="col-sm-2">Tarih</dt>
            <dd class="col-sm-10"><?= $view->date($message->sentAt, 'd.m.Y H:i:s') ?></dd>
        </dl>
        <div class="posta-govdesi border rounded p-3 bg-body-tertiary"><?= $view->linkify($message->body) ?></div>
    </div>
</div>
