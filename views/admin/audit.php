<?php
/**
 * FR-41: denetim kaydı (kim, ne zaman, eski/yeni değer, neden). Sistem işlemlerinde kişi boştur.
 *
 * @var App\Core\View $view
 * @var App\Support\Paginator<App\Model\AuditLog> $page
 * @var string $type
 */
$types = ['' => 'Tümü', 'reservation' => 'Rezervasyon', 'property' => 'İlan', 'unit' => 'Oda', 'user' => 'Kullanıcı', 'review' => 'Yorum', 'complaint' => 'Şikâyet'];
?>
<?= $view->partial('partials/admin_nav') ?>
<h1 class="h4">Denetim kaydı</h1>
<ul class="nav nav-pills mb-3 flex-wrap">
    <?php foreach ($types as $value => $label): ?>
        <li class="nav-item"><a class="nav-link<?= $type === $value ? ' active' : '' ?>" href="<?= e($view->url('/yonetim/denetim', ['tur' => $value])) ?>"><?= e($label) ?></a></li>
    <?php endforeach; ?>
</ul>
<div class="table-responsive">
    <table class="table table-sm align-middle bg-white small">
        <thead class="table-light"><tr><th>Zaman</th><th>Kim</th><th>Varlık</th><th>İşlem</th><th>Eski</th><th>Yeni</th><th>Neden</th></tr></thead>
        <tbody>
        <?php foreach ($page->items as $log): ?>
            <tr class="<?= $log->action === 'gecersiz_gecis' ? 'table-danger' : '' ?>">
                <td class="text-nowrap"><?= $view->date($log->createdAt, 'd.m.Y H:i:s') ?></td>
                <td><?= $log->actorId === null ? '<span class="text-body-secondary">sistem</span>' : '#' . (int) $log->actorId ?></td>
                <td><?= e($log->entityType) ?> #<?= (int) $log->entityId ?></td>
                <td><?= e($log->action) ?></td>
                <td><?= e($log->oldValue ?? '') ?></td>
                <td><?= e($log->newValue ?? '') ?></td>
                <td><?= e($log->reason ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $view->partial('partials/pagination', ['paginator' => $page, 'path' => '/yonetim/denetim', 'query' => ['tur' => $type]]) ?>
