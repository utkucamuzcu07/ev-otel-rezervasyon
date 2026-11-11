<?php
/**
 * UC-04 adım 2: iptal önizlemesi (kalan gün, oran, iade, kesinti) ve onay (FR-17, FR-45).
 *
 * @var App\Core\View $view
 * @var App\Service\Dto\ReservationView $r
 * @var App\Service\Refund\RefundQuote $quote
 */
$res = $r->reservation;
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4">Rezervasyon iptali</h1>
                <p class="mb-2"><span class="font-monospace"><?= e($res->number) ?></span> · <?= e($r->property->name) ?> · <?= e((string) $res->range()) ?></p>
                <?php if (!$quote->allowed): ?>
                    <div class="alert alert-danger"><?= e($quote->message) ?></div>
                    <a class="btn btn-outline-secondary" href="<?= e($view->url('/rezervasyon/' . $res->number)) ?>">‹ Geri dön</a>
                <?php else: ?>
                    <table class="table table-sm">
                        <tbody>
                        <tr><th>İptal politikası</th><td><?= e($res->cancellationPolicy->label()) ?> (rezervasyon anındaki)</td></tr>
                        <tr><th>Girişe kalan gün</th><td><?= (int) $quote->daysLeft ?></td></tr>
                        <tr><th>Ödenen toplam</th><td><?= $view->money($quote->paid) ?></td></tr>
                        <tr><th>İade oranı</th><td>%<?= (int) $quote->percent ?></td></tr>
                        <tr class="table-success"><th>İade tutarı</th><td><strong><?= $view->money($quote->refund) ?></strong></td></tr>
                        <tr class="table-danger"><th>Kesinti</th><td><?= $view->money($quote->deduction) ?></td></tr>
                        </tbody>
                    </table>
                    <p class="small text-body-secondary"><?= e($quote->message) ?> İade simüledir. İptalden sonra geceler hemen yeniden müsait olur.</p>
                    <form method="post" action="<?= e($view->url('/rezervasyon/' . $res->number . '/iptal')) ?>">
                        <?= $view->csrfField() ?>
                        <div class="mb-3">
                            <label for="neden" class="form-label">İptal nedeni (isteğe bağlı)</label>
                            <input type="text" class="form-control" id="neden" name="neden" maxlength="200">
                        </div>
                        <div class="d-flex gap-2">
                            <a class="btn btn-outline-secondary" href="<?= e($view->url('/rezervasyon/' . $res->number)) ?>">Vazgeç</a>
                            <button type="submit" class="btn btn-danger" data-tek-tik>İptali onayla</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
