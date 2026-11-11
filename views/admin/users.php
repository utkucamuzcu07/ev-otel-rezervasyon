<?php
/**
 * FR-29: kullanıcı yönetimi. Bonus: yazarken sonuç veren canlı arama.
 *
 * @var App\Core\View $view
 * @var App\Support\Paginator<App\Model\User> $page
 * @var array<string, mixed> $q
 * @var array<string, string> $roles
 * @var array<string, string> $statuses
 */
?>
<?= $view->partial('partials/admin_nav') ?>
<h1 class="h4">Kullanıcılar</h1>
<form method="get" action="<?= e($view->url('/yonetim/kullanicilar')) ?>" id="kullanici-filtre" class="row g-2 align-items-end mb-3">
    <div class="col-12 col-md-5">
        <label for="q" class="form-label small mb-0">Ara (ad, soyad, e-posta) — yazdıkça sonuç gelir</label>
        <input type="search" class="form-control" id="q" name="q" value="<?= e($q['q'] ?? '') ?>" autocomplete="off">
    </div>
    <div class="col-6 col-md-3">
        <label for="rol" class="form-label small mb-0">Rol</label>
        <select class="form-select" id="rol" name="rol"><option value="">Tümü</option><?php foreach ($roles as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($q['rol'] ?? '', $value) ?>><?= e($label) ?></option><?php endforeach; ?></select>
    </div>
    <div class="col-6 col-md-2">
        <label for="durum" class="form-label small mb-0">Durum</label>
        <select class="form-select" id="durum" name="durum"><option value="">Tümü</option><?php foreach ($statuses as $value => $label): ?><option value="<?= e($value) ?>"<?= $view->selected($q['durum'] ?? '', $value) ?>><?= e($label) ?></option><?php endforeach; ?></select>
    </div>
    <div class="col-12 col-md-2"><button type="submit" class="btn btn-success w-100">Filtrele</button></div>
</form>
<div id="kullanici-tablosu"><?= $view->partial('admin/_users_table', ['page' => $page, 'q' => $q]) ?></div>
<script>
let kullaniciZamanlayici = null;
document.getElementById('q').addEventListener('input', function () {
    clearTimeout(kullaniciZamanlayici);
    kullaniciZamanlayici = setTimeout(async function () {
        const parametreler = new URLSearchParams(new FormData(document.getElementById('kullanici-filtre')));
        const yanit = await fetch('<?= e($view->url('/parca/kullanicilar')) ?>?' + parametreler.toString());
        document.getElementById('kullanici-tablosu').innerHTML = await yanit.text();
    }, 300);
});
</script>
