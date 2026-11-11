<?php
/**
 * Aylık müsaitlik takvimi (FR-10, AC-32). Dolu ve kapalı geceler ayırt edilir.
 * Bu parça kısmi yenileme ile birkaç saniyede bir yeniden üretilir.
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\MonthCalendar $calendar
 * @var bool $showPrices ilan sahibi görünümü (özel fiyat ve kilit işaretleri)
 */
use App\Support\Dates;

$showPrices ??= false;
$today = (new DateTimeImmutable('today'))->format('Y-m-d');
?>
<div class="d-flex justify-content-between align-items-center mb-2">
    <strong><?= e($calendar->title) ?></strong>
    <span class="small text-body-secondary" title="Bu bölüm kendiliğinden yenilenir">⟳ <?= e(date('H:i:s')) ?></span>
</div>
<div class="takvim" role="grid" aria-label="<?= e($calendar->title) ?> müsaitlik takvimi">
    <?php foreach (Dates::shortDayNames() as $name): ?>
        <div class="gun-adi" role="columnheader"><?= e($name) ?></div>
    <?php endforeach; ?>
    <?php foreach ($calendar->weeks as $week): ?>
        <?php foreach ($week as $day): ?>
            <?php if ($day === null): ?>
                <div class="gun bos" aria-hidden="true"></div>
            <?php else: ?>
                <?php
                $key = $day['date']->format('Y-m-d');
                $label = match ($day['status']) {
                    'dolu' => 'dolu',
                    'kapali' => 'kapalı',
                    'gecmis' => 'geçmiş',
                    default => 'müsait',
                };
                ?>
                <div class="gun <?= e($day['status']) ?><?= $key === $today ? ' bugun' : '' ?>" role="gridcell" title="<?= e($day['date']->format('d.m.Y') . ': ' . $label) ?>">
                    <span class="no"><?= (int) $day['date']->format('j') ?></span>
                    <?php if ($day['status'] === 'dolu'): ?>
                        <span class="etiket"><?= $showPrices ? '🔒 dolu' : 'dolu' ?></span>
                    <?php elseif ($day['status'] === 'kapali'): ?>
                        <span class="etiket">⛔ kapalı</span>
                    <?php elseif ($showPrices && $day['price'] !== null && $day['status'] === 'musait'): ?>
                        <span class="etiket text-success">₺<?= e(number_format(intdiv($day['price'], 100), 0, ',', '.')) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>
<div class="lejant small mt-2 d-flex flex-wrap gap-3">
    <span><span class="kutu"></span>müsait</span>
    <span><span class="kutu dolu"></span>dolu<?= $showPrices ? ' (kilitli, kapatılamaz)' : '' ?></span>
    <span><span class="kutu kapali"></span>kapalı</span>
    <?php if ($showPrices): ?><span class="text-success">₺ özel gece fiyatı</span><?php endif; ?>
</div>
