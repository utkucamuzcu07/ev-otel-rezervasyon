<?php
/**
 * FR-23: ilan sahibinin kendi ilanlarına gelen rezervasyonlar.
 *
 * @var App\Core\View $view
 * @var list<App\Service\Dto\ReservationView> $list
 * @var string $status
 */
use App\Model\ReservationStatus;
?>
<div class="row g-3">
    <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <div class="col-12 col-lg-9">
        <h1 class="h4">Gelen rezervasyonlar</h1>
        <ul class="nav nav-pills mb-3 flex-wrap">
            <li class="nav-item"><a class="nav-link<?= $status === '' ? ' active' : '' ?>" href="<?= e($view->url('/panel/rezervasyonlar')) ?>">Tümü</a></li>
            <?php foreach (ReservationStatus::cases() as $s): ?>
                <li class="nav-item"><a class="nav-link<?= $status === $s->value ? ' active' : '' ?>" href="<?= e($view->url('/panel/rezervasyonlar', ['durum' => $s->value])) ?>"><?= e($s->label()) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <div class="card shadow-sm"><div class="card-body">
            <!-- Kısmi yenileme: tablo 5 saniyede bir yenilenir -->
            <div id="rezervasyon-tablosu"><?= $view->partial('host/_reservations_table', ['list' => $list, 'compact' => false]) ?></div>
            <script>
            async function tabloYenile() {
                const yanit = await fetch('<?= e($view->url('/parca/panel-rezervasyonlar', ['durum' => $status])) ?>');
                document.getElementById('rezervasyon-tablosu').innerHTML = await yanit.text();
            }
            tabloYenile();
            setInterval(tabloYenile, 5000);
            </script>
        </div></div>
    </div>
</div>
