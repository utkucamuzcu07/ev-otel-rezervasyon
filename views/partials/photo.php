<?php
/**
 * İlan görseli: yüklenmiş fotoğraf yoksa emoji (ders kuralı: resim dosyası hazırlanmaz).
 *
 * @var App\Core\View $view
 * @var int|null $photoId
 * @var App\Model\PropertyType $type
 * @var string $alt
 */
?>
<?php if ($photoId !== null): ?>
    <img src="<?= e($view->url('/foto/' . $photoId)) ?>" class="ilan-foto" alt="<?= e($alt) ?>" loading="lazy">
<?php else: ?>
    <div class="emoji-foto" role="img" aria-label="<?= e($alt) ?>"><?= e($type->emoji()) ?></div>
<?php endif; ?>
