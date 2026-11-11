<?php
/**
 * Ortak sayfa yerleşimi: resmi ve minimalist tema (tasarım kaynağı Lovable prototipi, uygulama Bootstrap 5).
 *
 * @var App\Core\View $view
 * @var string $content
 * @var string $title
 * @var App\Model\User|null $currentUser
 * @var list<array{type: string, message: string}> $flashes
 * @var bool $mailInbox
 */

use App\Model\Role;

$currentUser ??= null;
$currentPath ??= '/';
$role = $currentUser?->role;
$nav = function (string $path, string $label) use ($view, $currentPath): string {
    $active = $currentPath === $path || ($path !== '/' && str_starts_with($currentPath, $path . '/'));

    return '<li class="nav-item"><a class="nav-link' . ($active ? ' active' : '') . '"' . ($active ? ' aria-current="page"' : '')
        . ' href="' . e($view->url($path)) . '">' . e($label) . '</a></li>';
};
?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'EvOtel') ?> · <?= e($appName ?? 'EvOtel') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&amp;family=Source+Serif+4:opsz,wght@8..60,400;8..60,600&amp;display=swap">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="<?= e($view->url('/assets/app.css')) ?>">
</head>
<body>
<header class="ust-bar">
    <nav class="navbar navbar-expand-lg" aria-label="Ana menü">
        <div class="container">
            <a class="navbar-brand" href="<?= e($view->url('/')) ?>">EvOtel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ust-menu" aria-controls="ust-menu" aria-expanded="false" aria-label="Menüyü aç/kapat">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="ust-menu">
                <ul class="navbar-nav ms-lg-auto align-items-lg-center">
                    <?= $nav('/ara', 'Ara') ?>
                    <?php if ($role === Role::Musteri): ?>
                        <?= $nav('/favoriler', 'Favorilerim') ?>
                        <?= $nav('/rezervasyonlarim', 'Rezervasyonlarım') ?>
                    <?php elseif ($role === Role::IlanSahibi): ?>
                        <?= $nav('/panel', 'Panelim') ?>
                        <?= $nav('/panel/takvim', 'Takvim') ?>
                        <?= $nav('/panel/rezervasyonlar', 'Rezervasyonlar') ?>
                        <?= $nav('/panel/rapor', 'Rapor') ?>
                    <?php elseif ($role === Role::Yonetici): ?>
                        <?= $nav('/yonetim', 'Yönetim') ?>
                        <?= $nav('/yonetim/sikayetler', 'Şikâyetler') ?>
                        <?= $nav('/yonetim/kullanicilar', 'Kullanıcılar') ?>
                        <?= $nav('/yonetim/rapor', 'Rapor') ?>
                    <?php endif; ?>
                    <?php if ($currentUser === null): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e($view->url('/giris', ['rol' => 'ilan_sahibi'])) ?>#kayit">Ev sahibi ol</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e($view->url('/giris')) ?>">Giriş yap</a></li>
                        <li class="nav-item ms-lg-2"><a class="btn btn-primary btn-sm" href="<?= e($view->url('/giris')) ?>#kayit">Kayıt ol</a></li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= e($view->url('/bildirimler')) ?>">Bildirimler <span id="bildirim-sayaci"></span></a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <?= e($currentUser->fullName()) ?> <span class="rozet ms-1"><?= e($currentUser->role->label()) ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= e($view->url('/profil')) ?>">Profilim</a></li>
                                <?php if ($role === Role::IlanSahibi): ?>
                                    <li><a class="dropdown-item" href="<?= e($view->url('/rezervasyonlarim')) ?>">Konakladığım yerler</a></li>
                                <?php endif; ?>
                                <?php if ($role !== Role::Yonetici): ?>
                                    <li><a class="dropdown-item" href="<?= e($view->url('/sikayetlerim')) ?>">Şikâyetlerim</a></li>
                                    <li><a class="dropdown-item" href="<?= e($view->url('/sikayet')) ?>">Şikâyet oluştur</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="post" action="<?= e($view->url('/cikis')) ?>" class="px-3">
                                        <?= $view->csrfField() ?>
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Çıkış yap</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<main class="container sayfa">
    <?php foreach ($flashes ?? [] as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<footer class="alt-bar">
    <div class="container d-flex flex-wrap justify-content-between gap-2">
        <span>© EvOtel · Aşama 2 prototip: veriler JSON dosyalarında, ödemeler simüledir; kart bilgisi alınmaz.</span>
        <span class="d-flex flex-wrap gap-3">
            <?php if ($currentUser !== null && $role !== Role::Yonetici): ?>
                <a href="<?= e($view->url('/sikayet')) ?>">Şikâyet oluştur</a>
                <a href="<?= e($view->url('/sikayetlerim')) ?>">Şikâyetlerim</a>
            <?php endif; ?>
            <?php if ($mailInbox ?? false): ?>
                <a href="<?= e($view->url('/posta-kutusu')) ?>">Posta kutusu</a>
            <?php endif; ?>
        </span>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script>
// Oturum sona erdiyse (zaman aşımı, başka sekmede çıkış) arka plan isteği 401 alır; giriş sayfasına dönülür.
const girisAdresi = '<?= e($view->url('/giris')) ?>';
// Bonus: favori formu sayfa yenilenmeden gönderilir; PHP düğmenin yeni HTML'ini döndürür.
document.addEventListener('submit', async function (olay) {
    const form = olay.target;
    if (!form.matches('[data-favori]')) {
        return;
    }
    olay.preventDefault();
    const yanit = await fetch(form.action, {method: 'POST', body: new FormData(form), headers: {'X-Requested-With': 'fetch'}});
    if (yanit.ok) {
        form.outerHTML = await yanit.text();
    } else if (yanit.status === 401) {
        window.location.href = girisAdresi;
    } else {
        form.submit();
    }
});
// Çift tıklamayı önlemek için gönderilen formun düğmesi kilitlenir (asıl güvence sunucudaki idempotency anahtarıdır).
document.addEventListener('submit', function (olay) {
    const dugme = olay.target.querySelector('[data-tek-tik]');
    if (dugme) {
        setTimeout(function () { dugme.disabled = true; }, 0);
    }
});
</script>
<?php if ($currentUser !== null): ?>
<script>
// Kısmi yenileme (ders kalıbı, "birkaç saniyede bir"): okunmamış bildirim sayısı 5 saniyede bir güncellenir.
async function bildirimYenile() {
    const yanit = await fetch('<?= e($view->url('/parca/bildirim-sayaci')) ?>');
    if (yanit.ok) {
        document.getElementById('bildirim-sayaci').innerHTML = await yanit.text();
    } else if (yanit.status === 401) {
        window.location.href = girisAdresi;
    }
}
bildirimYenile();
setInterval(bildirimYenile, 5000);
</script>
<?php endif; ?>
</body>
</html>
