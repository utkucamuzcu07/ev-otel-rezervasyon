<?php
/**
 * Kullanıcı tablosu (canlı aramada da kullanılır).
 *
 * @var App\Core\View $view
 * @var App\Support\Paginator<App\Model\User> $page
 * @var array<string, mixed> $q
 */
use App\Model\Role;
?>
<div class="table-responsive">
    <table class="table table-sm align-middle bg-white">
        <thead class="table-light"><tr><th>#</th><th>Ad soyad</th><th>E-posta</th><th>Rol</th><th>Durum</th><th>Kayıt</th><th style="min-width: 300px">Durum değiştir (neden zorunlu)</th></tr></thead>
        <tbody>
        <?php foreach ($page->items as $u): ?>
            <tr>
                <td><?= (int) $u->id ?></td>
                <td><?= e($u->fullName()) ?></td>
                <td class="small"><?= e($u->email) ?></td>
                <td><?= e($u->role->label()) ?></td>
                <td><span class="badge text-bg-<?= $u->status->value === 'aktif' ? 'success' : 'danger' ?>"><?= e($u->status->label()) ?></span></td>
                <td class="small"><?= $view->date($u->createdAt) ?></td>
                <td>
                    <?php if ($u->role !== Role::Yonetici): ?>
                        <form method="post" action="<?= e($view->url('/yonetim/kullanici/' . $u->id . '/durum')) ?>" class="input-group input-group-sm">
                            <?= $view->csrfField() ?>
                            <select name="durum" class="form-select" aria-label="Yeni durum">
                                <option value="aktif"<?= $view->selected($u->status->value, 'aktif') ?>>Aktif</option>
                                <option value="kisitli"<?= $view->selected($u->status->value, 'kisitli') ?>>Kısıtlı</option>
                            </select>
                            <input type="text" name="neden" class="form-control" placeholder="Neden" required minlength="5" aria-label="Neden">
                            <button type="submit" class="btn btn-outline-secondary">Kaydet</button>
                        </form>
                    <?php else: ?>
                        <span class="small text-body-secondary">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if ($page->total === 0): ?><tr><td colspan="7" class="text-body-secondary">Kayıt bulunamadı.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?= $view->partial('partials/pagination', ['paginator' => $page, 'path' => '/yonetim/kullanicilar', 'query' => array_diff_key($q, ['sayfa' => true])]) ?>
