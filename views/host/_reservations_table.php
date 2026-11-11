<?php
/**
 * Gelen rezervasyonlar tablosu (kısmi yenileme ile birkaç saniyede bir güncellenir).
 * Onay tek tıkla yapılır; ret nedeni gerektiği için detay sayfasında yazılır.
 *
 * @var App\Core\View $view
 * @var list<App\Service\Dto\ReservationView> $list
 * @var bool $compact
 */
use App\Model\BookingMode;

$compact ??= false;
?>
<div class="d-flex justify-content-end"><span class="small text-body-secondary">⟳ <?= e(date('H:i:s')) ?></span></div>
<?php if ($list === []): ?>
    <p class="text-body-secondary small mb-0">Gösterilecek rezervasyon yok.</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle bg-white mb-0">
            <thead class="table-light"><tr><th>Rez. No</th><th>Müşteri</th><th>İlan / oda</th><th>Giriş–Çıkış</th><th>Kişi</th><?php if (!$compact): ?><th class="text-end">Tutar</th><th>Ödeme</th><?php endif; ?><th>Durum</th><th>İşlem</th></tr></thead>
            <tbody>
            <?php foreach ($list as $v): ?>
                <?php $res = $v->reservation; ?>
                <tr>
                    <td class="font-monospace small"><a href="<?= e($view->url('/rezervasyon/' . $res->number)) ?>"><?= e($res->number) ?></a></td>
                    <td><?= e($res->contactName) ?></td>
                    <td class="small"><?= e($v->property->name) ?><br><span class="text-body-secondary"><?= e($v->unit->name) ?></span></td>
                    <td class="small text-nowrap"><?= $view->date($res->checkIn, 'd.m') ?>–<?= $view->date($res->checkOut, 'd.m.Y') ?></td>
                    <td><?= (int) $res->guestCount ?></td>
                    <?php if (!$compact): ?>
                        <td class="text-end"><?= $view->money($res->totalPrice) ?></td>
                        <td class="small"><?= $v->paid > 0 ? '✅ alındı' : '— yok' ?></td>
                    <?php endif; ?>
                    <td><?= $view->statusBadge($v->isHold || $res->status->value !== 'beklemede' ? $res->status : App\Model\ReservationStatus::SuresiDoldu) ?></td>
                    <td class="text-nowrap">
                        <?php if ($v->isHold && $v->property->bookingMode === BookingMode::Onayli): ?>
                            <form method="post" action="<?= e($view->url('/panel/rezervasyon/' . $res->number . '/onayla')) ?>" class="d-inline">
                                <?= $view->csrfField() ?>
                                <button type="submit" class="btn btn-success btn-sm"<?= $v->paid > 0 ? '' : ' disabled title="Ödeme bekleniyor"' ?>>Onayla</button>
                            </form>
                            <a class="btn btn-outline-danger btn-sm" href="<?= e($view->url('/rezervasyon/' . $res->number)) ?>">Reddet…</a>
                        <?php else: ?>
                            <a class="btn btn-outline-secondary btn-sm" href="<?= e($view->url('/rezervasyon/' . $res->number)) ?>">Detay</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
