<?php

declare(strict_types=1);

/*
 * NFR-09 yedekleme aracı (storage/data altındaki JSON tablolarının yedeği):
 *
 *   php bin/yedek.php                               -> yedek alır (son 7 yedek saklanır)
 *   php bin/yedek.php --liste                       -> yedekleri listeler (en yeni önce)
 *   php bin/yedek.php --geri-yukle=DOSYA --evet     -> yedeği geri yükler (önce mevcut durumun yedeği alınır)
 *
 * Günlük yedek ayrıca bin/gorev.php tarafından otomatik alınır (o gün yedek yoksa).
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/app.php';
$container = App\Core\Container::fromConfig($config);
$backups = $container->backupService();
$options = getopt('', ['liste', 'geri-yukle:', 'evet']);

try {
    if (isset($options['liste'])) {
        $list = $backups->list();
        if ($list === []) {
            echo "Henüz yedek yok.\n";
        }
        foreach ($list as $backup) {
            printf("%s  %s  %s KB\n", $backup['ad'], $backup['tarih']->format('d.m.Y H:i:s'), number_format($backup['boyut'] / 1024, 1, ',', '.'));
        }
        exit(0);
    }

    if (isset($options['geri-yukle'])) {
        $name = (string) $options['geri-yukle'];
        if (!isset($options['evet'])) {
            fwrite(STDERR, "Geri yükleme mevcut verilerin yerine geçer. Onaylamak için --evet ekleyin:\n  php bin/yedek.php --geri-yukle={$name} --evet\n");
            exit(1);
        }
        $result = $backups->restore($name);
        echo "Geri yüklendi: {$name}\nÖnceki durumun yedeği: {$result['oncesi']}\n";
        foreach ($result['satirlar'] as $table => $count) {
            printf("  %-24s %d satır\n", $table, $count);
        }
        exit(0);
    }

    $name = $backups->create();
    echo "Yedek alındı: storage/backups/{$name} (son " . App\Service\BackupService::KEEP . " yedek saklanır)\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'Yedekleme hatası: ' . $e->getMessage() . "\n");
    exit(1);
}
