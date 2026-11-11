<?php

declare(strict_types=1);

/*
 * Zamanlanmış görev (SRS 2.1, FR-34, FR-40). Dakikada bir çalıştırılır:
 *   php bin/gorev.php
 * Windows Görev Zamanlayıcı veya Linux cron ile tetiklenebilir (README'ye bakın).
 */

require dirname(__DIR__) . '/vendor/autoload.php';

$config = require dirname(__DIR__) . '/config/app.php';
$container = App\Core\Container::fromConfig($config);

$result = $container->scheduledTaskService()->run();

printf(
    "[%s] Süresi dolan bekletme: %d, tamamlanan konaklama: %d\n",
    $container->clock->now()->format('Y-m-d H:i:s'),
    $result['expired'],
    $result['completed']
);

// NFR-09: günde en az bir yedek (o gün yedek alınmamışsa alınır; son 7 yedek saklanır)
try {
    $backup = $container->backupService()->createDailyIfMissing();
    if ($backup !== null) {
        echo 'Günlük yedek alındı: storage/backups/' . $backup . PHP_EOL;
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Günlük yedek alınamadı: ' . $e->getMessage() . PHP_EOL);
    exit(1);
}
