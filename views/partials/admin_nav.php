<?php
/**
 * Yönetici paneli menüsü.
 *
 * @var App\Core\View $view
 * @var string $currentPath
 */
$items = [
    '/yonetim' => 'Özet',
    '/yonetim/kullanicilar' => 'Kullanıcılar',
    '/yonetim/ilanlar' => 'İlanlar',
    '/yonetim/sikayetler' => 'Şikâyetler',
    '/yonetim/yorumlar' => 'Yorum denetimi',
    '/yonetim/rapor' => 'Doluluk ve gelir',
    '/yonetim/denetim' => 'Denetim kaydı',
];
?>
<ul class="nav nav-pills flex-wrap mb-4">
    <?php foreach ($items as $path => $label): ?>
        <li class="nav-item"><a class="nav-link<?= $currentPath === $path ? ' active' : '' ?>" href="<?= e($view->url($path)) ?>"><?= e($label) ?></a></li>
    <?php endforeach; ?>
</ul>
