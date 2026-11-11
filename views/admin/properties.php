<?php
/**
 * FR-28 + FR-39: ilan inceleme ve kaldırma (neden zorunlu; gelecek rezervasyonlar iptal + %100 iade).
 *
 * @var App\Core\View $view
 * @var App\Support\Paginator<App\Model\Property> $page
 * @var array<int, string> $owners
 * @var array<string, mixed> $q
 * @var array<string, string> $statuses
 */
?>
<?= $view->partial('partials/admin_nav') ?>
<h1 class="h4">İlanlar</h1>
<form method="get" action="<?= e($view->url('/yonetim/ilanlar')) ?>" class="row g-2 align-items-end mb-3">
    <div class="col-12 col-md-6"><label for="q" class="form-label small mb-0">Ara (ad, şehir)</label><input type="search" class="form-control" id="q" name="q" value="<?= e($q['q'] ?? '') ?>"></div>
    <div class="col-6 col-md-3"><label for="durum" class="form-label small mb-0">Durum</label>
        <select class="form-select" id="durum" name="durum"><option value="">Tümü</option><?php foreach ($statuses as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($q['durum'] ?? '', $value) ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
    <div class="col-6 col-md-3"><button type="submit" class="btn btn-success w-100">Filtrele</button></div>
</form>
<div class="table-responsive">
    <table class="table table-sm align-middle bg-white">
        <thead class="table-light"><tr><th>#</th><th>İlan</th><th>Sahibi</th><th>Şehir</th><th>Durum</th><th style="min-width: 320px">Yayından kaldır</th></tr></thead>
        <tbody>
        <?php foreach ($page->items as $p): ?>
            <tr>
                <td><?= (int) $p->id ?></td>
                <td><a href="<?= e($view->url('/ilan/' . $p->id)) ?>"><?= e($p->type->emoji() . ' ' . $p->name) ?></a></td>
                <td class="small"><?= e($owners[$p->ownerId] ?? '-') ?></td>
                <td><?= e($p->city) ?></td>
                <td><span class="badge text-bg-<?= $p->status->value === 'aktif' ? 'success' : ($p->status->value === 'kaldirildi' ? 'danger' : 'secondary') ?>"><?= e($p->status->label()) ?></span></td>
                <td>
                    <?php if ($p->status->value !== 'kaldirildi'): ?>
                        <form method="post" action="<?= e($view->url('/yonetim/ilan/' . $p->id . '/kaldir')) ?>" class="input-group input-group-sm" onsubmit="return confirm('İlan kaldırılsın mı? Gelecekteki rezervasyonları iptal edilip tamamı iade edilir.')">
                            <?= $view->csrfField() ?>
                            <input type="text" name="neden" class="form-control" placeholder="Kaldırma nedeni" required minlength="5" aria-label="Kaldırma nedeni">
                            <button type="submit" class="btn btn-outline-danger">Kaldır</button>
                        </form>
                    <?php else: ?>
                        <span class="small text-body-secondary">Kaldırıldı</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $view->partial('partials/pagination', ['paginator' => $page, 'path' => '/yonetim/ilanlar', 'query' => array_diff_key($q, ['sayfa' => true])]) ?>
