<?php
/**
 * Favori düğmesi. data-favori: form sayfa yenilenmeden gönderilir (bonus).
 *
 * @var App\Core\View $view
 * @var int $propertyId
 * @var bool $isFavorite
 */
?>
<form method="post" action="<?= e($view->url('/favori/' . $propertyId)) ?>" class="d-inline" data-favori>
    <?= $view->csrfField() ?>
    <input type="hidden" name="donus" value="<?= e($currentPath ?? '/') ?>">
    <button type="submit" class="btn btn-sm <?= $isFavorite ? 'btn-danger' : 'btn-outline-danger' ?>" aria-pressed="<?= $isFavorite ? 'true' : 'false' ?>">
        <?= $isFavorite ? '♥ Favoride' : '♡ Favori' ?>
    </button>
</form>
