<?php

declare(strict_types=1);

use App\Controller\AdminController;
use App\Controller\ApiController;
use App\Controller\AuthController;
use App\Controller\ComplaintController;
use App\Controller\HomeController;
use App\Controller\HostController;
use App\Controller\MailboxController;
use App\Controller\ProfileController;
use App\Controller\PropertyController;
use App\Controller\ReservationController;
use App\Controller\ReviewController;
use App\Model\Role;
use App\Router;

/*
 * Hangi adres hangi controller metoduna gider ve kimler erişebilir?
 * null = herkes, 'auth' = giriş yapmış herkes, [Role...] = yalnızca bu roller.
 * /parca/... adresleri sayfanın bir bölümünü kendiliğinden yenileyen HTML parçalarıdır.
 */
return static function (Router $r): void {
    $guest = [Role::Musteri, Role::IlanSahibi]; // rezervasyon yapabilenler (ilan sahibi kendi ilanı hariç, BR-19)
    $host = [Role::IlanSahibi];
    $admin = [Role::Yonetici];

    // Herkes (ziyaretçi dahil)
    $r->get('/', [HomeController::class, 'index']);
    $r->get('/ara', [PropertyController::class, 'search']);
    $r->get('/parca/arama', [PropertyController::class, 'searchFragment']);            // bonus: canlı arama
    $r->get('/ilan/{id}', [PropertyController::class, 'show']);
    $r->get('/parca/takvim', [PropertyController::class, 'calendarFragment']);         // kısmi yenileme
    $r->get('/api/musaitlik', [ApiController::class, 'availability']);             // JSON döndüren AJAX uç noktası
    $r->get('/foto/{id}', [PropertyController::class, 'photo']);
    $r->get('/giris', [AuthController::class, 'showLogin']);
    $r->post('/giris', [AuthController::class, 'login']);
    $r->post('/kayit', [AuthController::class, 'register']);
    $r->get('/parola-sifirla', [AuthController::class, 'showForgot']);
    $r->post('/parola-sifirla', [AuthController::class, 'forgot']);
    $r->get('/parola-sifirla/{token}', [AuthController::class, 'showReset']);
    $r->post('/parola-sifirla/{token}', [AuthController::class, 'reset']);
    $r->get('/posta-kutusu', [MailboxController::class, 'index']);                   // geliştirme: simüle e-postalar (yalnızca yerelden)
    $r->get('/posta-kutusu/{ileti}', [MailboxController::class, 'show']);
    $r->post('/cikis', [AuthController::class, 'logout'], 'auth');

    // Kayıtlı müşteri
    $r->get('/rezervasyon/onizleme', [ReservationController::class, 'preview'], $guest);
    $r->post('/rezervasyon', [ReservationController::class, 'store'], $guest);
    $r->get('/rezervasyonlarim', [ReservationController::class, 'index'], $guest);
    $r->get('/rezervasyon/{no}', [ReservationController::class, 'show'], 'auth');      // müşteri, ilan sahibi, yönetici (serviste denetlenir)
    $r->get('/parca/rezervasyon/{no}', [ReservationController::class, 'statusFragment'], 'auth');
    $r->post('/rezervasyon/{no}/odeme', [ReservationController::class, 'pay'], $guest);
    $r->get('/rezervasyon/{no}/iptal', [ReservationController::class, 'cancelForm'], $guest);
    $r->post('/rezervasyon/{no}/iptal', [ReservationController::class, 'cancel'], $guest);
    $r->get('/rezervasyon/{no}/yorum', [ReviewController::class, 'create'], $guest);
    $r->post('/rezervasyon/{no}/yorum', [ReviewController::class, 'store'], $guest);
    $r->get('/favoriler', [ProfileController::class, 'favorites'], 'auth');
    $r->post('/favori/{id}', [ProfileController::class, 'toggleFavorite'], 'auth');
    $r->get('/bildirimler', [ProfileController::class, 'notifications'], 'auth');
    $r->post('/bildirimler/okundu', [ProfileController::class, 'markRead'], 'auth');
    $r->get('/parca/bildirim-sayaci', [ProfileController::class, 'badgeFragment'], 'auth');
    $r->get('/profil', [ProfileController::class, 'edit'], 'auth');
    $r->post('/profil', [ProfileController::class, 'update'], 'auth');
    $r->post('/profil/parola', [ProfileController::class, 'changePassword'], 'auth');
    $r->get('/sikayet', [ComplaintController::class, 'create'], 'auth');
    $r->post('/sikayet', [ComplaintController::class, 'store'], 'auth');
    $r->get('/sikayetlerim', [ComplaintController::class, 'index'], 'auth');           // gönderilen şikâyetlerin durumu

    // Yalnızca ilan sahibi
    $r->get('/panel', [HostController::class, 'dashboard'], $host);
    $r->get('/panel/ilanlar', [HostController::class, 'properties'], $host);
    $r->get('/panel/ilan/yeni', [HostController::class, 'createProperty'], $host);
    $r->post('/panel/ilan/yeni', [HostController::class, 'storeProperty'], $host);
    $r->get('/panel/ilan/{id}', [HostController::class, 'editProperty'], $host);
    $r->post('/panel/ilan/{id}', [HostController::class, 'updateProperty'], $host);
    $r->post('/panel/ilan/{id}/sil', [HostController::class, 'deleteProperty'], $host);
    $r->get('/panel/ilan/{id}/odalar', [HostController::class, 'units'], $host);
    $r->post('/panel/ilan/{id}/odalar', [HostController::class, 'saveUnit'], $host);
    $r->get('/panel/ilan/{id}/fiyat', [HostController::class, 'pricing'], $host);
    $r->post('/panel/ilan/{id}/fiyat', [HostController::class, 'savePricing'], $host);
    $r->get('/panel/ilan/{id}/fotograflar', [HostController::class, 'photos'], $host);
    $r->post('/panel/ilan/{id}/fotograflar', [HostController::class, 'savePhoto'], $host);
    $r->get('/panel/takvim', [HostController::class, 'calendar'], $host);
    $r->post('/panel/takvim', [HostController::class, 'saveCalendar'], $host);
    $r->get('/panel/rezervasyonlar', [HostController::class, 'reservations'], $host);
    $r->get('/parca/panel-rezervasyonlar', [HostController::class, 'reservationsFragment'], $host);
    $r->post('/panel/rezervasyon/{no}/onayla', [HostController::class, 'approve'], $host);
    $r->post('/panel/rezervasyon/{no}/reddet', [HostController::class, 'reject'], $host);
    $r->get('/panel/rapor', [HostController::class, 'report'], $host);

    // Yalnızca yönetici
    $r->get('/yonetim', [AdminController::class, 'dashboard'], $admin);
    $r->post('/yonetim/gorev', [AdminController::class, 'runTask'], $admin);
    $r->get('/yonetim/kullanicilar', [AdminController::class, 'users'], $admin);
    $r->get('/parca/kullanicilar', [AdminController::class, 'usersFragment'], $admin);   // bonus: canlı arama
    $r->post('/yonetim/kullanici/{id}/durum', [AdminController::class, 'setUserStatus'], $admin);
    $r->get('/yonetim/ilanlar', [AdminController::class, 'properties'], $admin);
    $r->post('/yonetim/ilan/{id}/kaldir', [AdminController::class, 'removeProperty'], $admin);
    $r->get('/yonetim/sikayetler', [AdminController::class, 'complaints'], $admin);
    $r->post('/yonetim/sikayet/{id}', [AdminController::class, 'updateComplaint'], $admin);
    $r->get('/yonetim/yorumlar', [AdminController::class, 'reviews'], $admin);
    $r->post('/yonetim/yorum/{id}', [AdminController::class, 'moderateReview'], $admin);
    $r->get('/yonetim/rapor', [AdminController::class, 'report'], $admin);
    $r->get('/yonetim/denetim', [AdminController::class, 'audit'], $admin);
};
