<?php
/**
 * FR-27: kullanıcının gönderdiği şikâyetler ve son durumları (Şikâyetlerim).
 *
 * @var App\Core\View $view
 * @var list<array{complaint: App\Model\Complaint, property: string, number: string}> $rows
 */
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 sayfa-baslik">
    <div>
        <h1>Şikâyetlerim</h1>
        <p>Gönderdiğiniz şikâyetler yönetim ekibinin Şikâyetler kutusunda incelenir; durum değişince bildirim alırsınız.</p>
    </div>
    <a class="btn btn-primary" href="<?= e($view->url('/sikayet')) ?>">Yeni şikâyet</a>
</div>

<?php if ($rows === []): ?>
    <div class="panel p-4 text-body-secondary">Henüz şikâyetiniz yok.</div>
<?php else: ?>
    <div class="tablo-panel">
        <table class="table align-top">
            <thead>
            <tr><th>Şikâyet no</th><th>Konu</th><th>Tarih</th><th>Durum</th><th>Yönetici notu</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <?php $c = $row['complaint']; ?>
                <tr>
                    <td class="fw-medium">#<?= (int) $c->id ?></td>
                    <td>
                        <?= e($row['property']) ?><?php if ($row['number'] !== '-'): ?> <span class="text-body-secondary">· <span class="font-monospace"><?= e($row['number']) ?></span></span><?php endif; ?>
                        <div class="small text-body-secondary satir-sinir"><?= e($c->description) ?></div>
                    </td>
                    <td class="text-nowrap"><?= $view->date($c->createdAt, 'd.m.Y') ?></td>
                    <td><span class="rozet rozet-<?= e($c->status->tone()) ?>"><?= e($c->status->label()) ?></span></td>
                    <td class="text-body-secondary"><?= $c->adminNote !== null ? e($c->adminNote) : '—' ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
