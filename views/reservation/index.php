<?php
/**
 * FR-16: Rezervasyonlarım (gelecek / geçmiş).
 *
 * @var App\Core\View $view
 * @var array{upcoming: list<App\Service\Dto\ReservationView>, past: list<App\Service\Dto\ReservationView>} $lists
 */
$tables = ['Yaklaşan ve devam eden' => $lists['upcoming'], 'Geçmiş ve kapanan' => $lists['past']];
?>
<h1 class="h4 mb-3">Rezervasyonlarım</h1>
<?php foreach ($tables as $heading => $list): ?>
    <h2 class="h6 fw-bold mt-3"><?= e($heading) ?> <span class="badge text-bg-secondary"><?= count($list) ?></span></h2>
    <?php if ($list === []): ?>
        <p class="text-body-secondary small">Kayıt yok.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle bg-white">
                <thead><tr><th>Rez. No</th><th>İlan / oda</th><th>Tarihler</th><th>Kişi</th><th class="text-end">Tutar</th><th>Durum</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($list as $v): ?>
                    <?php $res = $v->reservation; ?>
                    <tr>
                        <td class="font-monospace small"><?= e($res->number) ?></td>
                        <td><?= e($v->property->name) ?><div class="small text-body-secondary"><?= e($v->unit->name) ?></div></td>
                        <td class="small"><?= e((string) $res->range()) ?></td>
                        <td><?= (int) $res->guestCount ?></td>
                        <td class="text-end"><?= $view->money($res->totalPrice) ?></td>
                        <td><?= $view->statusBadge($res->status) ?></td>
                        <td class="text-end"><a class="btn btn-outline-success btn-sm" href="<?= e($view->url('/rezervasyon/' . $res->number)) ?>">Detay</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
