<?php

declare(strict_types=1);

/*
 * Örnek veri yükleyici: php bin/seed.php --force
 * Tarihler bugüne göre üretilir; sunumdan önce yeniden çalıştırılması önerilir.
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/app.php';
$container = App\Core\Container::fromConfig($config);

$force = in_array('--force', $argv, true);
if (!$force && !$container->needsDemoData()) {
    fwrite(STDERR, "Veri zaten var. Silip yeniden yüklemek için: php bin/seed.php --force\n");
    exit(1);
}

// Eski yüklemeler yeni veride hiçbir kayda bağlı olmayacağı için temizlenir. Örnek veri fotoğraf
// üretmez (ders kuralı: resim, ikon veya çizim dosyası hazırlanmaz); fotoğrafı ilan sahibi yükler.
foreach (glob($container->settings->uploadDir . '/*') ?: [] as $file) {
    if (preg_match('/\.(jpe?g|png|webp)$/i', $file) === 1) {
        @unlink($file);
    }
}

$summary = (new App\Seed\DemoSeeder($container))->run();

echo "Örnek veri yüklendi:\n";
foreach ($summary as $label => $count) {
    echo '  ' . $label . str_repeat(' ', max(1, 14 - mb_strlen($label))) . $count . "\n";
}
echo "Demo hesapları ve parolası README.md dosyasında.\n";
