<?php
/**
 * FR-19: favoriler.
 *
 * @var App\Core\View $view
 * @var list<App\Service\Dto\SearchResultItem> $cards
 */
?>
<h1 class="h4 mb-3">Favorilerim</h1>
<?php if ($cards === []): ?>
    <p class="text-body-secondary">Henüz favori ilanınız yok. Arama sonuçlarında ♡ düğmesiyle ekleyebilirsiniz.</p>
<?php endif; ?>
<div class="row g-3">
    <?php foreach ($cards as $item): ?>
        <div class="col-12 col-sm-6 col-lg-4"><?= $view->partial('partials/property_card', ['item' => $item]) ?></div>
    <?php endforeach; ?>
</div>
