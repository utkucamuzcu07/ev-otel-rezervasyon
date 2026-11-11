<?php
/**
 * Rezervasyon detayı: durum, gecelik döküm (anlık görüntü), iletişim ve işlemler.
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\ReservationView $r
 * @var App\Model\User $currentUser
 */
use App\Model\BookingMode;

$res = $r->reservation;
$isCustomer = $currentUser->id === $res->customerId;
$isHost = $currentUser->id === $r->property->ownerId;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h1 class="h4 mb-0">Rezervasyon <span class="font-monospace"><?= e($res->number) ?></span></h1>
    <a href="<?= e($view->url($isHost ? '/panel/rezervasyonlar' : '/rezervasyonlarim')) ?>" class="small">‹ Listeye dön</a>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <!-- Kısmi yenileme: durum bölümü 5 saniyede bir yenilenir -->
        <div id="durum-bolumu">Yükleniyor...</div>
        <script>
        async function durumYenile() {
            const yanit = await fetch('<?= e($view->url('/parca/rezervasyon/' . $res->number)) ?>');
            document.getElementById('durum-bolumu').innerHTML = await yanit.text();
        }
        durumYenile();
        setInterval(durumYenile, 5000);
        </script>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h5"><?= e($r->property->name) ?> — <?= e($r->property->city) ?></h2>
                <p class="mb-1">Oda: <?= e($r->unit->name . ' (' . $r->unit->roomType->label() . ')') ?></p>
                <p class="mb-1">Giriş: <strong><?= $view->date($res->checkIn) ?></strong> (14:00) · Çıkış: <strong><?= $view->date($res->checkOut) ?></strong> (12:00) · <?= (int) $res->nights() ?> gece · <?= (int) $res->guestCount ?> kişi</p>
                <p class="mb-1">İptal politikası (rezervasyon anındaki): <strong><?= e($res->cancellationPolicy->label()) ?></strong></p>
                <p class="small text-body-secondary mb-0">Oluşturulma: <?= $view->date($res->createdAt, 'd.m.Y H:i') ?><?= $res->cancelledAt !== null ? ' · İptal: ' . $view->date($res->cancelledAt, 'd.m.Y H:i') : '' ?></p>
                <?php if ($res->cancelReason !== null): ?><p class="small mb-0">Neden: <?= e($res->cancelReason) ?></p><?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h2 class="h6 fw-bold">İletişim</h2>
                <?php if ($isCustomer): ?>
                    <p class="mb-1">Ev sahibi: <?= e($r->host?->fullName() ?? '-') ?> · <?= e($r->host?->email ?? '') ?></p>
                <?php endif; ?>
                <?php if (!$isCustomer): ?>
                    <p class="mb-1">Misafir: <?= e($res->contactName) ?> · <?= e($res->contactPhone) ?> · <?= e($r->customer?->email ?? '') ?></p>
                <?php endif; ?>
                <?php if ($res->note !== null): ?><p class="mb-0 small">Not: <?= e($res->note) ?></p><?php endif; ?>
                <p class="small text-body-secondary mb-0">İletişim bilgileri yalnızca rezervasyonun müşterisine ve ilgili ilan sahibine gösterilir.</p>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php if ($r->canCancel): ?>
                <a class="btn btn-outline-danger" href="<?= e($view->url('/rezervasyon/' . $res->number . '/iptal')) ?>">İptal et (iade önizlemesi)</a>
            <?php endif; ?>
            <?php if ($r->canReview): ?>
                <a class="btn btn-outline-success" href="<?= e($view->url('/rezervasyon/' . $res->number . '/yorum')) ?>">⭐ Yorum yaz</a>
            <?php endif; ?>
            <?php if ($isCustomer): ?>
                <a class="btn btn-link text-danger" href="<?= e($view->url('/sikayet', ['rezervasyon' => $res->number])) ?>">⚠️ Şikâyet et</a>
            <?php endif; ?>
        </div>

        <?php if ($r->canPay): ?>
            <div class="card border-warning shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Ödemeyi tamamla (simüle)</h2>
                    <form method="post" action="<?= e($view->url('/rezervasyon/' . $res->number . '/odeme')) ?>" class="d-flex flex-wrap gap-2 align-items-end">
                        <?= $view->csrfField() ?>
                        <div>
                            <label for="odeme_sonucu" class="form-label small mb-1">Simüle sonuç</label>
                            <select id="odeme_sonucu" name="odeme_sonucu" class="form-select form-select-sm">
                                <option value="basarili">Başarılı</option>
                                <option value="basarisiz">Başarısız</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-warning" data-tek-tik>Öde: <?= $view->money($res->totalPrice) ?></button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($isHost && $r->isHold && $r->property->bookingMode === BookingMode::Onayli): ?>
            <div class="card border-success shadow-sm mb-3">
                <div class="card-body">
                    <h2 class="h6 fw-bold">Talebi yanıtla</h2>
                    <form method="post" action="<?= e($view->url('/panel/rezervasyon/' . $res->number . '/onayla')) ?>" class="d-inline">
                        <?= $view->csrfField() ?>
                        <input type="hidden" name="donus" value="/rezervasyon/<?= e($res->number) ?>">
                        <button type="submit" class="btn btn-success">Onayla</button>
                    </form>
                    <form method="post" action="<?= e($view->url('/panel/rezervasyon/' . $res->number . '/reddet')) ?>" class="mt-2">
                        <?= $view->csrfField() ?>
                        <input type="hidden" name="donus" value="/rezervasyon/<?= e($res->number) ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" name="neden" placeholder="Ret nedeni" required minlength="3" aria-label="Ret nedeni">
                            <button type="submit" class="btn btn-outline-danger">Reddet</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h2 class="h6 fw-bold">ÜCRET DÖKÜMÜ <span class="fw-normal small text-body-secondary">(rezervasyon anındaki fiyat)</span></h2>
                <table class="table table-sm dokum">
                    <tbody>
                    <?php foreach ($r->charges as $charge): ?>
                        <tr><td class="small"><?= e($charge->description) ?></td><td><?= $view->money($charge->amount) ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="table-light fw-bold"><td>TOPLAM</td><td><?= $view->money($res->totalPrice) ?></td></tr>
                    </tbody>
                </table>
                <p class="mb-1">Ödenen: <strong><?= $view->money($r->paid) ?></strong></p>
                <p class="mb-0">İade (simüle): <strong><?= $view->money($r->refunded) ?></strong></p>
            </div>
        </div>
    </div>
</div>
