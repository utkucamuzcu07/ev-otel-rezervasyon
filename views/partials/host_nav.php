<?php
/**
 * İlan sahibi paneli yan menüsü (taslak 06).
 *
 * @var App\Core\View $view
 * @var string $currentPath
 */
$items = [
    '/panel' => 'Panel',
    '/panel/ilanlar' => 'İlanlarım',
    '/panel/takvim' => 'Takvim / Fiyat',
    '/panel/rezervasyonlar' => 'Rezervasyonlar',
    '/panel/rapor' => 'Doluluk ve gelir',
    '/panel/ilan/yeni' => '+ Yeni ilan ekle',
];
?>
<div class="list-group mb-3">
    <?php foreach ($items as $path => $label): ?>
        <a class="list-group-item list-group-item-action<?= $currentPath === $path ? ' active' : '' ?>" href="<?= e($view->url($path)) ?>"><?= e($label) ?></a>
    <?php endforeach; ?>
</div>
