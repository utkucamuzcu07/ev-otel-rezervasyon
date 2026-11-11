<?php
/**
 * @var App\Core\View $view
 * @var int $status
 * @var string $title
 * @var string $message
 * @var string $home
 */
$emoji = match ($status) {
    403 => '🚫',
    404 => '🧭',
    419 => '🔐',
    503 => '⏳',
    default => '⚠️',
};
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm text-center p-4">
            <div style="font-size: 3rem" aria-hidden="true"><?= $emoji ?></div>
            <h1 class="h4 mt-2"><?= e($title) ?> <span class="text-body-secondary">(<?= (int) $status ?>)</span></h1>
            <p class="mb-4"><?= e($message) ?></p>
            <div class="d-flex justify-content-center gap-2">
                <a class="btn btn-outline-secondary" href="javascript:history.back()">‹ Geri dön</a>
                <a class="btn btn-success" href="<?= e($view->url($home)) ?>">Ana sayfa</a>
            </div>
        </div>
    </div>
</div>
