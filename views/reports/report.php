<?php
/**
 * FR-30: doluluk ve gelir raporu (ilan, birim, oda tipi, ay kırılımı). Bonus: CSV dışa aktarma.
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\Report $report
 * @var array<string, string> $errors
 * @var string $action
 * @var string $scope
 * @var App\Model\User $currentUser
 */
use App\Model\Role;

$pct = fn (int $bp): string => number_format($bp / 100, 2, ',', '.') . '%';
$query = ['baslangic' => $report->from->format('Y-m-d'), 'bitis' => $report->to->format('Y-m-d')];
$table = function (string $heading, array $rows) use ($view, $pct): string {
    ob_start(); ?>
    <h2 class="h6 fw-bold mt-4"><?= e($heading) ?></h2>
    <div class="table-responsive">
        <table class="table table-sm align-middle bg-white">
            <thead class="table-light"><tr><th>Ad</th><th class="text-end">Aktif birim</th><th class="text-end">Dolu / kapasite (gece)</th><th style="min-width:160px">Doluluk</th><th class="text-end">Rezervasyon</th><th class="text-end">Net gelir</th></tr></thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr>
                    <td><?= e($row->label) ?></td>
                    <td class="text-end"><?= (int) $row->activeUnits ?></td>
                    <td class="text-end"><?= (int) $row->occupiedNights ?> / <?= (int) $row->capacityNights ?></td>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress flex-grow-1 doluluk-cubugu" role="progressbar" aria-valuenow="<?= (int) round($row->occupancyBp / 100) ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar bg-success" style="width: <?= min(100, $row->occupancyBp / 100) ?>%"></div></div>
                            <span class="small"><?= e($pct($row->occupancyBp)) ?></span>
                        </div>
                    </td>
                    <td class="text-end"><?= (int) $row->reservationCount ?></td>
                    <td class="text-end"><?= $view->money($row->netRevenue) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php return (string) ob_get_clean();
};
?>
<div class="row g-3">
    <?php if ($currentUser->role === Role::IlanSahibi): ?>
        <div class="col-12 col-lg-3"><?= $view->partial('partials/host_nav') ?></div>
    <?php endif; ?>
    <div class="col-12<?= $currentUser->role === Role::IlanSahibi ? ' col-lg-9' : '' ?>">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <h1 class="h4 mb-0">Doluluk ve gelir raporu <span class="badge text-bg-light border fs-6"><?= e($scope) ?></span></h1>
            <a class="btn btn-outline-success btn-sm" href="<?= e($view->url($action, $query + ['csv' => '1'])) ?>">⬇️ CSV indir</a>
        </div>
        <form method="get" action="<?= e($view->url($action)) ?>" class="row g-2 align-items-end mb-3">
            <div class="col-6 col-md-3"><label for="baslangic" class="form-label small mb-0">Başlangıç</label><input type="date" class="form-control form-control-sm<?= $view->invalid($errors, 'baslangic') ?>" id="baslangic" name="baslangic" value="<?= e($query['baslangic']) ?>"></div>
            <div class="col-6 col-md-3"><label for="bitis" class="form-label small mb-0">Bitiş (dahil)</label><input type="date" class="form-control form-control-sm<?= $view->invalid($errors, 'bitis') ?>" id="bitis" name="bitis" value="<?= e($query['bitis']) ?>"></div>
            <div class="col-12 col-md-2"><button type="submit" class="btn btn-success btn-sm w-100">Göster</button></div>
            <?php foreach ($errors as $message): ?><div class="col-12 text-danger small"><?= e($message) ?></div><?php endforeach; ?>
        </form>

        <div class="row g-3">
            <?php foreach ([
                ['Doluluk oranı', $pct($report->total->occupancyBp)],
                ['Dolu gece / kapasite', $report->total->occupiedNights . ' / ' . $report->total->capacityNights],
                ['Rezervasyon (girişi dönemde)', (string) $report->total->reservationCount],
                ['Net gelir', App\Support\Money::format($report->total->netRevenue)],
            ] as [$label, $value]): ?>
                <div class="col-6 col-md-3"><div class="card shadow-sm h-100"><div class="card-body py-2">
                    <div class="small text-body-secondary"><?= e($label) ?></div><div class="fs-5 fw-bold"><?= e($value) ?></div>
                </div></div></div>
            <?php endforeach; ?>
        </div>
        <p class="small text-body-secondary mt-2 mb-0">
            Dönem: <?= $view->date($report->from) ?> – <?= $view->date($report->to) ?> (<?= (int) $report->periodNights ?> gece).
            Doluluk = Onaylandı ve Tamamlandı rezervasyonların dönemle kesişen gece sayısı ÷ (aktif birim × dönemdeki gece) × 100.
            Net gelir = girişi dönemde olan rezervasyonların başarılı ödemeleri − iadeler (brüt <?= $view->money($report->total->grossRevenue) ?>, iade <?= $view->money($report->total->refunds) ?>).
        </p>

        <h2 class="h6 fw-bold mt-4">Durum dağılımı (girişi dönemde olanlar)</h2>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($report->statusCounts as $label => $count): ?>
                <span class="badge text-bg-light border fs-6 fw-normal"><?= e($label) ?>: <strong><?= (int) $count ?></strong></span>
            <?php endforeach; ?>
        </div>

        <?= $table('İlan bazında', $report->byProperty) ?>
        <?= $table('Oda tipi bazında', $report->byRoomType) ?>
        <?= $table('Ay bazında', $report->byMonth) ?>
        <?= $table('Birim bazında', $report->byUnit) ?>
    </div>
</div>
