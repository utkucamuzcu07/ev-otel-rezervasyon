<?php
/**
 * Rezervasyon durumu (kısmi yenileme): ilan sahibi onaylayınca veya süre dolunca sayfa yenilenmeden değişir.
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\ReservationView $r
 * @var DateTimeImmutable $now
 */
use App\Model\BookingMode;
use App\Model\ReservationStatus;

$res = $r->reservation;
$status = $r->isHold || $res->status !== ReservationStatus::Beklemede ? $res->status : ReservationStatus::SuresiDoldu;
?>
<div class="d-flex flex-wrap align-items-center gap-2">
    <span class="fs-5"><?= $view->statusBadge($status) ?></span>
    <?php if ($r->isHold && $res->expiresAt !== null): ?>
        <?php $left = max(0, $res->expiresAt->getTimestamp() - $now->getTimestamp()); ?>
        <span>⏳ Kalan bekletme süresi: <strong><?= e(sprintf('%02d:%02d:%02d', intdiv($left, 3600), intdiv($left % 3600, 60), $left % 60)) ?></strong></span>
        <span class="small text-body-secondary">(<?= e($res->expiresAt->format('d.m.Y H:i')) ?>'e kadar)</span>
    <?php endif; ?>
    <span class="small text-body-secondary ms-auto" title="Bu bölüm kendiliğinden yenilenir">⟳ <?= e($now->format('H:i:s')) ?></span>
</div>
<p class="small mb-0 mt-1">
    <?php if ($r->isHold && $r->paid === 0): ?>
        Geceleriniz tutuluyor. Ödemeyi bekletme süresi dolmadan tamamlayın; süre dolarsa geceler serbest kalır.
    <?php elseif ($r->isHold && $r->property->bookingMode === BookingMode::Onayli): ?>
        Ödemeniz alındı (simüle). İlan sahibinin onayı bekleniyor.
    <?php elseif ($res->status === ReservationStatus::Onaylandi): ?>
        Rezervasyonunuz kesinleşti. İyi konaklamalar!
    <?php elseif ($res->status === ReservationStatus::Tamamlandi): ?>
        Konaklama tamamlandı.
    <?php elseif ($status === ReservationStatus::SuresiDoldu): ?>
        Bekletme süresi doldu; geceler serbest bırakıldı.
    <?php else: ?>
        <?= e($res->cancelReason ?? '') ?>
    <?php endif; ?>
</p>
