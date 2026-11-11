<?php
/**
 * Yönetici özeti, tutarlılık denetimi (T-11) ve zamanlanmış görev.
 *
 * @var App\Core\View $view
 * @var int $userCount
 * @var int $propertyCount
 * @var int $newComplaints
 * @var App\Service\Dto\Report $report
 * @var list<string> $violations
 */
?>
<?= $view->partial('partials/admin_nav') ?>
<h1 class="h4 mb-3">Yönetici paneli</h1>
<div class="row g-3 mb-3">
    <?php foreach ([
        ['👥 Kullanıcı', (string) $userCount],
        ['🏨 Yayındaki ilan', (string) $propertyCount],
        ['⚠️ Yeni şikâyet', (string) $newComplaints],
        ['📈 Bu ay doluluk', number_format($report->total->occupancyBp / 100, 1, ',', '') . '%'],
        ['💰 Bu ay net gelir', App\Support\Money::format($report->total->netRevenue)],
    ] as [$label, $value]): ?>
        <div class="col-6 col-md-4 col-xl">
            <div class="card shadow-sm h-100"><div class="card-body py-2">
                <div class="small text-body-secondary"><?= e($label) ?></div>
                <div class="fs-5 fw-bold"><?= e($value) ?></div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h2 class="h6 fw-bold">Çift rezervasyon tutarlılık denetimi</h2>
            <p class="small text-body-secondary">Aynı birim ve gece için birden fazla engelleyen rezervasyon, eksik/fazla gece kaydı ve yetim gece kaydı aranır. Sağlıklı veride sonuç boştur.</p>
            <?php if ($violations === []): ?>
                <div class="alert alert-success mb-0">✅ Sorun bulunamadı: her birim ve gece için en fazla bir engelleyen rezervasyon var.</div>
            <?php else: ?>
                <div class="alert alert-danger mb-0"><ul class="mb-0"><?php foreach ($violations as $v): ?><li><?= e($v) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm h-100"><div class="card-body">
            <h2 class="h6 fw-bold">Zamanlanmış görev</h2>
            <p class="small text-body-secondary">Normalde dakikada bir <code>php bin/gorev.php</code> ile çalışır: süresi dolan bekletmeleri Süresi Doldu, çıkış tarihi gelen onaylı rezervasyonları Tamamlandı yapar.</p>
            <form method="post" action="<?= e($view->url('/yonetim/gorev')) ?>">
                <?= $view->csrfField() ?>
                <button type="submit" class="btn btn-outline-success">▶ Şimdi çalıştır</button>
            </form>
        </div></div>
    </div>
</div>
