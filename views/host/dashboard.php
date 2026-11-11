<?php
/**
 * İlan sahibi paneli özeti.
 *
 * @var App\Core\View $view
 * @var list<array{property: App\Model\Property, units: int, photos: int}> $propertyRows
 * @var list<App\Service\Dto\ReservationView> $pending
 * @var list<App\Service\Dto\ReservationView> $upcoming
 * @var App\Service\Dto\Report $report
 */
?>
<div class="row g-3">
    <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <div class="col-12 col-lg-9">
        <h1 class="h4 mb-3">İlan sahibi paneli</h1>
        <div class="row g-3 mb-3">
            <?php
            $kpis = [
                ['İlan', (string) count($propertyRows), '🏠'],
                ['Bekleyen talep', (string) count($pending), '⏳'],
                ['Yaklaşan konaklama', (string) count($upcoming), '🧳'],
                ['Bu ay doluluk', number_format($report->total->occupancyBp / 100, 1, ',', '') . '%', '📈'],
                ['Bu ay net gelir', App\Support\Money::format($report->total->netRevenue), '💰'],
            ];
            ?>
            <?php foreach ($kpis as [$label, $value, $emoji]): ?>
                <div class="col-6 col-md-4 col-xl">
                    <div class="card shadow-sm h-100"><div class="card-body py-2">
                        <div class="small text-body-secondary"><?= $emoji ?> <?= e($label) ?></div>
                        <div class="fs-5 fw-bold"><?= e($value) ?></div>
                    </div></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6 fw-bold">Gelen rezervasyonlar</h2>
                <!-- Kısmi yenileme: tablo 5 saniyede bir yenilenir; yeni talep sayfa yenilenmeden görünür -->
                <div id="gelen-rezervasyonlar">Yükleniyor...</div>
                <script>
                async function gelenYenile() {
                    const yanit = await fetch('<?= e($view->url('/parca/panel-rezervasyonlar', ['kisa' => '1'])) ?>');
                    document.getElementById('gelen-rezervasyonlar').innerHTML = await yanit.text();
                }
                gelenYenile();
                setInterval(gelenYenile, 5000);
                </script>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-success" href="<?= e($view->url('/panel/ilan/yeni')) ?>">➕ Yeni ilan</a>
            <a class="btn btn-outline-success" href="<?= e($view->url('/panel/takvim')) ?>">📅 Takvimi yönet</a>
            <a class="btn btn-outline-success" href="<?= e($view->url('/panel/rapor')) ?>">📊 Raporu gör</a>
        </div>
    </div>
</div>
