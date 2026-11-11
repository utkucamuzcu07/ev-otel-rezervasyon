<?php

declare(strict_types=1);

namespace App\Controller;

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Exception\ValidationException;
use App\Service\AuthService;
use App\Service\FavoriteService;
use App\Service\NotificationService;
use App\Service\PropertyService;
use App\Service\UserService;

/** Profil (FR-31), şifre değiştirme (bonus), favoriler (FR-19) ve bildirimler (FR-26). */
final class ProfileController extends Controller
{
    public function __construct(
        View $view,
        Session $session,
        Auth $auth,
        private readonly UserService $users,
        private readonly AuthService $authService,
        private readonly FavoriteService $favorites,
        private readonly PropertyService $properties,
        private readonly NotificationService $notifications,
    ) {
        parent::__construct($view, $session, $auth);
    }

    public function edit(Request $request): Response
    {
        return $this->render('profile/edit', ['title' => 'Profilim', 'errors' => [], 'passwordErrors' => []]);
    }

    public function update(Request $request): Response
    {
        try {
            $this->users->updateProfile($this->userId(), $request->input('ad'), $request->input('soyad'));
        } catch (ValidationException $e) {
            return $this->render('profile/edit', ['title' => 'Profilim', 'errors' => $e->errors(), 'passwordErrors' => []], 422);
        }
        $this->flash('success', 'Profil bilgileriniz güncellendi.');

        return $this->redirect('/profil');
    }

    public function changePassword(Request $request): Response
    {
        try {
            $this->authService->changePassword($this->userId(), $request->input('mevcut'), $request->input('yeni'));
        } catch (ValidationException $e) {
            return $this->render('profile/edit', ['title' => 'Profilim', 'errors' => [], 'passwordErrors' => $e->errors()], 422);
        }
        $this->session->regenerate();
        $this->flash('success', 'Parolanız değiştirildi.');

        return $this->redirect('/profil');
    }

    public function favorites(Request $request): Response
    {
        $userId = $this->userId();

        return $this->render('profile/favorites', [
            'title' => 'Favorilerim',
            'cards' => $this->properties->cards($this->favorites->propertyIds($userId), $userId),
        ]);
    }

    /**
     * Bonus: form sayfa yenilemeden gönderilir. fetch ile gelen istekte yalnızca düğmenin
     * yeni HTML'i döner; JavaScript'siz tarayıcıda normal yönlendirme yapılır.
     */
    public function toggleFavorite(Request $request, string $id): Response
    {
        $isFavorite = $this->favorites->toggle($this->userId(), (int) $id);
        if ($request->header('X-Requested-With') === 'fetch') {
            $this->session->close();

            // Yeni düğme de formun gönderildiği sayfaya dönmeyi bilir (istek adresi /favori/... olduğundan açıkça verilir).
            return $this->fragment('partials/favorite_button', [
                'propertyId' => (int) $id,
                'isFavorite' => $isFavorite,
                'currentPath' => $this->safePath($request->input('donus'), '/favoriler'),
            ]);
        }
        $this->flash('success', $isFavorite ? 'İlan favorilerinize eklendi.' : 'İlan favorilerinizden çıkarıldı.');

        return $this->redirect($this->safePath($request->input('donus'), '/favoriler'));
    }

    public function notifications(Request $request): Response
    {
        return $this->render('profile/notifications', [
            'title' => 'Bildirimler',
            'page' => $this->notifications->listForUser($this->userId(), $request->int('sayfa', 1), 10),
        ]);
    }

    public function markRead(Request $request): Response
    {
        $this->notifications->markAllRead($this->userId());
        $this->flash('success', 'Tüm bildirimler okundu olarak işaretlendi.');

        return $this->redirect('/bildirimler');
    }

    /** Kısmi yenileme: üst menüdeki okunmamış bildirim sayısı. */
    public function badgeFragment(Request $request): Response
    {
        $userId = $this->userId();
        $this->session->close();

        return $this->fragment('partials/notification_badge', ['count' => $this->notifications->unreadCount($userId)]);
    }
}
